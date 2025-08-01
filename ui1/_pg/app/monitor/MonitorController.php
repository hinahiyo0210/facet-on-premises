<?php 

class MonitorController extends UserBaseController {
		
	public $devices;

	public $groups;
	
	// add-start founder feihan
	public $groupsDisplay;
	public $contractor;
	// add-start founder feihan
	
	// @Override
	public function prepare(&$form) {
		
		// sessionからプルダウンを取得
		parent::prepare($form);
		
		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);
		
		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);

		// 契約者情報を取得。
		$this->contractor = DB::selectRow("select * from m_contractor where contractor_id = {value}", $this->contractor_id);

		// add-start founder feihan
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
		// add-end founder feihan
	}
	
	// トップ
	public function indexAction(&$form) {
		
		$data = Filters::ref($form)
			// add-start founder feihan
			->at("searchInit")->digit()
			// add-end founder feihan
			->at("view"  	, 1   )->digit(1, 2)
			->at("limit" 	, 20  )->enum(Enums::monitorPagerLimit())
			->at("pageNo" 		  )->digit()
			// mod-start founder feihan
			// 未登録 ＝ -1
			->at("group_ids"	  )->enumArray($this->groups+array('-1'=>true))
			// mod-end founder feihan
			->at("device_ids"	  )->enumArray($this->devices)
			->getFilteredData();
		
		// add-start founder feihan
		if(empty($data["searchInit"])){
			$groupIds=array_keys($this->groups);
			// 未登録カメラのグループIDは-1をセット
			array_push($groupIds,-1);
			// 初期表示時、グループとカメラは全て選択に設定される。
			$form["group_ids"] = $groupIds;
			$form["device_ids"] = array_keys($this->devices);
			$data["device_ids"] = array_keys($this->devices);
		}
		// add-end founder feihan
		
		// 表示対象の抽出。
		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		
		$targetDevice = [];
		// mod-start founder feihan
//		if (empty($data["device_ids"])) {
//			$data["device_ids"] = array_map(function($v) { return $v["device_id"]; }, $this->devices);
//		}
		// mod-end founder feihan
		
		foreach ($data["device_ids"] as $device_id) {
			$targetDevice[] = $this->devices[$device_id];
		}
		
		$pageInfo->setRowCount(count($targetDevice));
		
		$targetDevice = array_slice($targetDevice, $pageInfo->getOffset(), $pageInfo->getLimit(), true);
		
		$list = [];
		foreach ($targetDevice as $device) {
			$list[] = [
				"device_id"=>$device["device_id"]
				, "deviceName"=>$device["name"]
				, "monitor"=>true
				, "recog_log_id"=>"dummy".$device["device_id"]
			];
			
		}
		
		// 監視WSアドレスを取得。
		$wsAddr = UiRecogLogService::beginMonitor($this->contractor_id, $this->user_id, $data["device_ids"]);
		
		$this->assign("pagerLimit", Enums::monitorPagerLimit());
		$this->assign("wsAddr", $wsAddr);
		$this->assign("pageInfo", $pageInfo);
		$this->assign("list", $list);
		return "monitor.tpl";
	}
	
	// 最新ログを取得(ajax)。
	public function getLatestLogAction(&$form) {
		
		$data = Filters::ref($form)
			->at("view"  	, 1   )->digit(1, 2)
			->at("device_id"	   )->enum($this->devices)
			->getFilteredData();

		if (empty($data["device_id"])) response400();
		
		// 最新一件のみを検索。
		$pageInfo = new PageInfo(1, 1);
		$searchData = [];
		$searchData["device_ids"] = [$data["device_id"]];
		$list = UiRecogLogService::getLogForMonitor($this->devices, $data["device_id"]);
		
		// 画像参照用のCookieを作成し、設定。
		UiRecogLogService::setCloudFrontCookie($this->contractor_id);
		
		// 出力。
		if (empty($list)) {
			$item = [];
			$item["device_id"]  = $data["device_id"];
			$item["deviceName"] = $this->devices[$data["device_id"]]["name"];
			$item["monitor"]    = true;
			$this->assign("item", $item);
		} else {
			$item = $list[0];
			$item["monitor"] = true;
			$this->assign("item", $item);
		}
		
		$ret = [];
		if ($data["view"] == "1") {
			// mod-start founder zouzhiyuan
			$ret["view_1"] = $this->fetch("../_inc/log_item_view_1_monitor.tpl");
			// mod-end founder zouzhiyuan
		} else {
			$ret["view_2_li"] = $this->fetch("../_inc/log_item_view_2_li.tpl");
			$ret["view_2_tr"] = $this->fetch("../_inc/log_item_view_2_tr.tpl");
		}
		
		setJsonHeader();
		echo json_encode($ret);
	}
	
	// add-start founder zouzhiyuan
	// デバイスへドアの一時開錠を行う。(ajax)
	public function doDeviceOpenOnceAction(&$form) {
		setJsonHeader();
		
		$data = Validators::set($form)
			->at("device_id", "device_id")->required()->inArray(array_keys($this->devices))
			->getValidatedData();
		
		if (Errors::isErrored()) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
		}
		
		// 開錠を行う。
		$device = $this->devices[$data["device_id"]];
		$ret = SystemService::openOnce($device);
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"カメラ名"=>$device["description"],
				"シリアル番号"=>$device["serial_no"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		// 成功終了の場合 $ret["result"] = true
		echo json_encode(["error"=>($ret["result"]!=true)]);
		return;
	}
	// add-start founder zouzhiyuan
	
}
