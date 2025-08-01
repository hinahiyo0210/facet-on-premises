<?php

/* dev founder feihan */

class DeviceMaintenanceController extends UserBaseController{
	
	public $devices;
	
	public $groups;
	
	public $groupsDisplay;
	
	public $contractor;
	
	public $deviceTypes;
	
	// add-start version3.0 founder feihan
	public $deviceRoles;
	// add-end version3.0 founder feihan
	
	// @Override
	public function prepare(&$form)
	{
		// sessionからプルダウンを取得。
		parent::prepare($form);
		
		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);
		
		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);
		
		// 表示用グループ。(キーはID)
		$noGroupDeviceIds = array_filter(array_keys($this->devices),function ($device_id)  {
			foreach ($this->groups as $gid=>["deviceIds"=>$deviceIds]){
				if (in_array($device_id,$deviceIds)){
					return false;
				}
			}
			return true;
		});
		$this->groupsDisplay = array();
		foreach ($this->groups as $gid=>["deviceIds"=>$deviceIds,"group_name"=>$group_name]) {
			$this->groupsDisplay[$gid] = array("deviceIds" => $deviceIds, "group_name" => $group_name);
		}
		$this->groupsDisplay["-1"] = array("deviceIds"=>$noGroupDeviceIds,"group_name"=>"未登録");
		
		// 契約者情報を取得。
		$this->contractor = DB::selectRow("select * from m_contractor where contractor_id = {value}", $this->contractor_id);
		
		// 型番情報を取得。
		$this->deviceTypes = DB::selectKeyRow("select device_type_id, device_type from m_device_type",null,"device_type_id");
		
		// 初期表示時、グループとカメラは全て選択に設定される。
		if (empty($form["connectionInit_search_init"])) {
			$form["connectionInit_area_cd"] = null;
			$form["connectionInit_group_ids"] = array_keys($this->groupsDisplay);
			$form["connectionInit_device_ids"] = array_keys($this->devices);
		}
		if (empty($form["delete_search_init"])) {
			$form["delete_area_cd"] = null;
			$form["delete_group_ids"] = array_keys($this->groupsDisplay);
			$form["delete_device_ids"] = array_keys($this->devices);
		}
		// add-start version3.0 founder feihan
		if($this->contractor["enter_exit_mode_flag"] == 1) {
			$this->deviceRoles[1] = array("device_role" => "1", "device_role_name" => "入室");
			$this->deviceRoles[2] = array("device_role" => "2", "device_role_name" => "退室");
		}
		// add-end version3.0 founder feihan
	}
	
	// 配列キーのプレフィックスを除去する。
	private function arrayExcludePrefix($arr, $prefix) {
		
		$ret = [];
		foreach ($arr as $k=>$v) {
			$ret[excludePrefix($k, $prefix)] = $v;
		}
		
		return $ret;
	}
	
	// 画面初期表示。
	public function indexAction(&$form) {
		
		Filters::ref($form)->at("tab")->values(["connectionInit","new", "delete"]);
		
		$this->indexConnectionInit($form);
		$this->indexNew($form);
		$this->indexDelete($form);
		
		return "deviceMaintenance.tpl";
	}
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：カメラ接続初期化。
	// ---------------------------------------------------------------------------------------------------------------------------
	// 初期表示のためのformへの値セット。
	public function indexConnectionInit(&$form) {
	}
	
	// 一覧の検索。
	public function connectionInitSearchAction(&$form){
		$form["tab"] = "connectionInit";
		// 検索。
		$filtedData = UiDeviceMaintenanceService::getListSearchFilter($form, "connectionInit_", $this->devices, $this->groups)->getFilteredData();
		$data = $this->arrayExcludePrefix($filtedData, "connectionInit_");
		
		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = UiDeviceMaintenanceService::getList($this->contractor_id, $data, $pageInfo);
		
		// 初期化完了後に戻るためのパラメータ。
		$form["connectionInit_init_back"] = Session::setData(createQueryStringExcludePulldown($filtedData)."&tab=connectionInit&_p=".urlencode($form["_p"]));
		
		$this->assign("connectionInit_list", $list);
		$this->assign("connectionInit_pageInfo", $pageInfo);
		// 新規登録初期化
		$this->indexNew($form);
		return "deviceMaintenance.tpl";
	}
	
	// カメラ初期化
	public function initDeviceAction (&$form){
		
		$data = Validators::set($form)
			->at("connectionInit_device_id", "カメラID"  )->required()->dataExists(0, "select 1 from m_device where device_id = {value}")
			->getValidatedData();
		
		if (Errors::isErrored()) return "deviceMaintenance.tpl";
		
		$Device = DB::selectRow("select * from m_device where device_id = {value}", $data["connectionInit_device_id"]);
		if (empty($Device)) response400();
		
		// update
		$updateSql ="update m_device set device_token = null, last_ws_access = null, last_push_access = null, last_recog = null where device_id = {value}";
		DB::update($updateSql,$data["connectionInit_device_id"]);
		
		$query = Session::getData($form["connectionInit_init_back"]);
		$url = "./connectionInitSearch".$query;
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"対象カメラ名"=>$Device["description"],
				"対象シリアル番号"=>$Device["serial_no"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect($url, "シリアル番号：".$Device["serial_no"]."、型番：".$Device["device_type"]."を初期化しました。カメラを再セットアップしてください。");
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：カメラ新規登録。
	// ---------------------------------------------------------------------------------------------------------------------------
	public function indexNew(&$form) {
		// カメラ新規登録できるのフラグ。
		$newFlag = true;
		$allowDeviceNum = $this->contractor["allow_device_num"];
		// 契約者のallow_device_numがNULLの場合。
		if(!empty($allowDeviceNum)){
			$devices = DB::selectArray("select * from m_device where contractor_id = {value} and contract_state = 10 ", $this->contractor_id);
			// カメラ登録台数(m_device内の該当contractor_idの台数)が、 allow_device_numと同じ場合は、登録できない。
			if($allowDeviceNum <= count($devices)){
				$newFlag = false;
			}
		}
		$form["newFlag"] = $newFlag;
		if($newFlag){
			// 登録できるの場合、画面の型番リストの取得する。
			$form["device_type_ids"] = array_keys($this->deviceTypes);
			$this->assign("deviceTypeList", $this->deviceTypes);
			$this->assign("deviceGroupList", $this->groups);
		}
		$this->assign("allowDeviceNum", $allowDeviceNum);
		// add-start version3.0 founder feihan
		$this->assign("deviceRoles", $this->deviceRoles);
		// add-end version3.0 founder feihan
	}
	
	// カメラを登録
	public function insertDeviceAction(&$form){
		// mod-start version3.0 founder feihan
		// 入力チェック。
		$v = Validators::set($form)
			->at("new_serialNo", "シリアルNo")->required()->half()->fixlength(11)->dataExists(1, "select 1 from m_device where contractor_id = {$this->contractor_id} and serial_no = {value}");

		// 別contractorでも同デバイスの登録がある場合、エラーではじく（文言を変える必要があるため別記載）
		$otherContractorDevice = DB::selectRow("select * from m_device where serial_no = {value}", $form['new_serialNo']);
		if (!(Errors::isErrored()) && !empty($otherContractorDevice)) Errors::add("シリアルNo","入力されたシリアルNoは別契約環境で使用されています。");

		$v = $v->at("new_device_type_id", "型番")->required()->ifNotEquals("")->inArray(array_keys($this->deviceTypes))->ifEnd()
			->at("new_deviceName", "カメラ名称")->maxlength(100)
			->at("device_group_id", "カメラグループ")->ifNotEquals("")->inArray(array_keys($this->groups))->ifEnd();
		if($this->contractor["enter_exit_mode_flag"] == 1){
			if (empty($form['device_group_id']) && !empty($form['new_device_role'])) Errors::add("カメラ機能","カメラ機能をご利用の場合はグループを指定してください。");
			$v = $v ->at("new_device_role","カメラ機能")->ifNotEquals("")->inArray(array_keys($this->deviceRoles))->ifEnd();
		}
		$data = $v->getValidatedData();
		$this->assign("deviceRoles", $this->deviceRoles);
		// mod-end version3.0 founder feihan
		$this->assign("deviceTypeList", $this->deviceTypes);
		$this->assign("deviceGroupList", $this->groups);
		if (Errors::isErrored())	return "deviceMaintenance.tpl";
		
		// データを登録
		if(UiDeviceMaintenanceService::insertDevice($data, $this->contractor_id, $this->deviceTypes)){
			
			// ログ出力
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"シリアル番号"=>$data["new_serialNo"],
					"型番"=>$this->deviceTypes[$data["new_device_type_id"]]["device_type"]
				],JSON_UNESCAPED_UNICODE)
			];
			$this->facetOperateLog($loginfo);
			
			sendCompleteRedirect("./?tab=new", "シリアル番号：".$data["new_serialNo"]."、型番：".$this->deviceTypes[$data["new_device_type_id"]]["device_type"]."の登録が完了しました。");
		}
	}
	
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：カメラ削除。
	// ---------------------------------------------------------------------------------------------------------------------------
	public function indexDelete(&$form) {
	}
	
	// 一覧の検索。
	public function deleteSearchAction(&$form){
		// 検索。
		$filtedData = UiDeviceMaintenanceService::getListSearchFilter($form, "delete_", $this->devices, $this->groups)->getFilteredData();
		$data = $this->arrayExcludePrefix($filtedData, "delete_");
		
		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = UiDeviceMaintenanceService::getList($this->contractor_id, $data, $pageInfo);
		
		// 削除完了後に戻るためのパラメータ。
		$form["delete_back"] = Session::setData(createQueryStringExcludePulldown($filtedData)."&tab=delete&_p=".urlencode($form["_p"]));
		
		$this->assign("delete_list", $list);
		$this->assign("delete_pageInfo", $pageInfo);
		// 新規登録初期化
		$this->indexNew($form);
		return "deviceMaintenance.tpl";
	}
	
	// カメラ削除
	public function deleteDeviceAction (&$form){
		
		$data = Validators::set($form)
			->at("delete_device_id", "カメラID"  )->required()->dataExists(0, "select 1 from m_device where device_id = {value}")
			->getValidatedData();
		
		if (Errors::isErrored()) return "deviceMaintenance.tpl";
		
		$Device = DB::selectRow("select * from m_device where device_id = {value}", $data["delete_device_id"]);
		if (empty($Device)) response400();
		
		// delete
		$deleteSql ="delete from m_device where device_id = {value}";
		DB::delete($deleteSql,$data["delete_device_id"]);
		
		// 2021年12月29日追記：device_idがあるテーブルレコードも併せて削除
		$haveDeviceIdTables = DB::selectArray("select table_name from information_schema.columns where column_name = {value}", 'device_id');
		foreach($haveDeviceIdTables as $haveDeviceIdTable) {
			$selectDeviceId = DB::selectRow("select device_id from {$haveDeviceIdTable['table_name']} where device_id = {value}", $data["delete_device_id"]);
			if(empty($selectDeviceId)) {
				continue;
			} else {
				DB::delete("delete from {$haveDeviceIdTable['table_name']} where device_id = {value}",$data["delete_device_id"]);
			}
		}
		//ここまで追記

    // オンプレの場合画像容量があるので削除デバイスの認証画像も削除する
    if (!ENABLE_AWS) {
        $deletePath = LOCAL_PICTURE_DIR . "/" . $Device["s3_path_prefix"] . "/";
        exec("rm -rf ${deletePath}", $output, $ret);
        infoLog("デバイス削除による画像ディレクトリの削除：".json_encode($output));
    }
		
		$query = Session::getData($form["delete_back"]);
		$url = "./deleteSearch".$query;
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"対象カメラ名"=>$Device["description"],
				"対象シリアル番号"=>$Device["serial_no"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect($url, "シリアル番号：".$Device["serial_no"]."、型番：".$Device["device_type"]."を削除しました。");
		
	}
}