<?php 

class DeviceController extends UserBaseController {
	
	public $contractor;
	public $devices;
	public $inDevices;
	public $outDevices;
	
	public $groups;
	// add-start founder feihan
	public $groupsDisplay;
	// add-end founder feihan
	
	public $recogConfigSets;
	public $systemConfigSets;
	
	public $firmwareVeresionNames;
	
	public $alertList;
	
	// add-start founder yaozhengbang
	public $deviceTopMenuFlag;
	// add-end founder yaozhengbang
	
	// add-start version3.0 founder feihan
	public $deviceTypes;
	
	public $deviceRoles;
	// add-end version3.0 founder feihan

  public $apbGroups;

  public $apbTypes;
	
	// @Override
	public function prepare(&$form) {
		
		// sessionからプルダウンを取得。
		parent::prepare($form);
		
		// ENABLE_AWSの値を取得
		$form["enableAws"] = ENABLE_AWS;

		// 契約者情報を取得。
		$this->contractor = DB::selectRow("select * from m_contractor where contractor_id = {value}", $this->contractor_id);
		
		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);

		// 一時的な対応：APBグループは常に一つだけ存在する状況にする。2021-06-11
		if (!empty(Session::getLoginUser("apb_mode_flag"))) {
			if (!DB::exists("select 1 from t_apb_group where contractor_id = {value}", $this->contractor_id)) {
				DB::insert("
					insert into 
						t_apb_group 
					set 
						contractor_id 	   = ".$this->contractor_id."
						, apb_group_name   = 'default'
						, sort_order       = 1
						, create_time	   = now()
						, create_user_id   = -1
						, update_time      = now()
						, update_user_id   = -1
				");
			}
		}
		
		
		// 入室用と退室用で仕分ける。
		$this->inDevices = [];
		$this->outDevices = [];
		foreach ($this->devices as $id=>$d) {
			if ($d["apb_type"] == 1) $this->inDevices[] = $d; 
			if ($d["apb_type"] == 2) $this->outDevices[] = $d;
			if ($d["apb_type"] == 3) $this->inDevices[] = $d; 
		}
		
		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);
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
		
		// APBグループ設定を取得。（キーはID)
		$this->apbGroups = ApbService::getApbGroups($this->contractor_id);
		
		// APB状態
		$this->apbTypes = [1=>"入室用",2=>"退室用",3=>"入室用(認証時APB制御なし)"];
		
		// 認識関連の設定セットを取得。 (キーはID)
		$this->recogConfigSets = ConfigSetService::getRecogConfigSets($this->contractor_id);
		
		// 認識関連の設定セットを取得。 (キーはID)
		$this->systemConfigSets = ConfigSetService::getSystemConfigSets($this->contractor_id);
		
		// add-start version3.0 founder feihan
		// 型番情報を取得。
		$this->deviceTypes = DB::selectKeyRow("select device_type_id, device_type from m_device_type",null,"device_type_id");
		// add-end version3.0 founder feihan

		// ファームウェアのバージョンを取得。
		// mod-start version3.0 founder feihan
		$this->firmwareVeresionNames = DB::selectKeyRow("select version_name, device_type_flag from m_firmware order by create_time desc", null, "version_name");
		// mod-start version3.0 founder feihan
		
		// アラート設定を取得。(キーはNo)
		$this->alertList = DB::selectKeyRow("select alert_no, alert_name from t_alert where contractor_id = {value} order by alert_no", $this->contractor_id, "alert_no");
		
		// add-start founder yaozhengbang
		// ユーザ権限設定を取得。
		$this->deviceTopMenuFlag = self::functionAccessFlag();
		// add-end founder yaozhengbang
		
		// 初期表示時、グループとカメラは全て選択に設定される。
		if (empty($form["device_search_search_init"])) {
			$form["device_search_area_cd"] = null;
			$form["device_search_group_ids"] = array_keys($this->groupsDisplay);
			$form["device_search_device_ids"] = array_keys($this->devices);
		}
		// add-start version3.0 founder feihan
		if($this->contractor["enter_exit_mode_flag"] == 1) {
			$this->deviceRoles[1] = array("device_role" => "1", "device_role_name" => "入室");
			$this->deviceRoles[2] = array("device_role" => "2", "device_role_name" => "退室");
		}
		// add-end version3.0 founder feihan
	}
	
	private function completeRedirect($msg, $appendParam = "") {
		
		// スクロール位置。
		$p = Filter::len(req("_p"), 5);
		sendCompleteRedirect("./?_p=".urlencode($p).$appendParam, $msg);
	}
	
	// 画面初期表示。
	public function indexAction(&$form) {
		
		$this->indexCameraGroup($form);
		$this->indexCamera($form);
		$this->indexRecog($form);
		$this->indexSystem($form);
		$this->indexAlerm($form);
		
		return "device.tpl";
	}

	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- 共通。
	// --------------------------------------------------------------------------------------------------------------------------- 
	// デバイスから設定情報を取得する。(ajax)
	public function getConfigByDeviceAction(&$form) {
		setJsonHeader();
		
		$data = Validators::set($form)
			->at("device_id", "device_id")->required()->inArray(array_keys($this->devices))
			->getValidatedData();
		
		if (Errors::isErrored()) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
		}
		
		// 取得。
		$device = $this->devices[$data["device_id"]];
		$define = ConfigService::getBasicConfigDefine();
		try {
			$config = ConfigService::getConfig($device, $define);
		} catch (SystemException $e) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
		} catch (DeviceWsException $e) {
			echo json_encode(["error"=>$e->getMessage()]);
			return;
		}
		
		// UI向けに加工。
		$config = ConfigSetService::convertToUi($config);
		
		echo json_encode($config);
		return;
	}
	
	// デバイスへ設定情報を反映する。(ajax)
	public function registConfigForDeviceAction(&$form) {
		setJsonHeader();
		
		$data = Validators::set($form)
			->at("device_id"			, "device_id"		    )->required()->inArray(array_keys($this->devices))
			->at("type"					, "type"				)->required()->inArray(["recogConfig", "systemConfig", "fw"])
			->at("recog_config_set_id"	, "recog_config_set_id" )->ifRequired("type", "recogConfig") ->inArray(array_keys($this->recogConfigSets))
			->at("system_config_set_id" , "system_config_set_id")->ifRequired("type", "systemConfig")->inArray(array_keys($this->systemConfigSets))
			->at("version_name" 		, "version_name"		)->ifRequired("type", "fw")			 ->inArray(array_keys($this->firmwareVeresionNames))
			->getValidatedData();

		if (Errors::isErrored()) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
		}
		
		// 登録。
		try {
			$device = $this->devices[$data["device_id"]];
			if ($data["type"] == "fw") {
				// ファームウェアアップデート。
				SystemService::updateFirmware($device, $data["version_name"]);
				echo json_encode(["result"=>"OK"]);
				
				// ログ出力
				$detail_json = [
					"変更カメラ名"=>$device["description"],
					"変更シリアル番号"=>$device["serial_no"],
					"FWバージョン"=>$data["version_name"]
				];
				$action_sub_type = 2;
				$loginfo = [
					"action_sub_type"=>$action_sub_type,
					"detail_json"=>json_encode($detail_json,JSON_UNESCAPED_UNICODE)
				];
				$this->facetOperateLog($loginfo);
				
				return;
				
			} else {
				// 設定の反映。
				$define = ConfigService::getBasicConfigDefine();
				
				if($data["type"] == "recogConfig") {
					$configData = $this->recogConfigSets[$data["recog_config_set_id"]];
					// API向けに加工。
					$configData = ConfigSetService::recogConvertToApi($configData);
					
				} else {
					$configData = $this->systemConfigSets[$data["system_config_set_id"]];
				}
				
				
				
				$ret = ConfigService::setConfig($device, $define, $configData, "UI_DeviceController_registConfigForDeviceAction");
				
			}
				
			// 返却。
			if (empty($ret["errored"])) {
				echo json_encode(["result"=>"OK"]);
				
				// ログ出力
				$detail_json = [
					"変更カメラ名"=>$device["description"],
					"変更シリアル番号"=>$device["serial_no"]
				];
				if ($data["type"] === "recogConfig") {
					$action_sub_type = 0;
					$detail_json["登録名"] = $configData["recog_config_set_name"];
				}else {
					$action_sub_type = 1;
					$detail_json["登録名"] = $configData["system_config_set_name"];
				}
				$loginfo = [
					"action_sub_type"=>$action_sub_type,
					"detail_json"=>json_encode($detail_json,JSON_UNESCAPED_UNICODE)
				];
				$this->facetOperateLog($loginfo);
				
				return;
			}
			
			echo json_encode(["error"=>"設定を登録する事が出来ませんでした。[".join(" / ", $ret["errored"])."]"]);
			return;
			
		} catch (SystemException $e) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
		} catch (DeviceWsException $e) {
			echo json_encode(["error"=>$e->getMessage()]);
			return;
		}
		
	}
	

	
	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- タブ：カメラグループ設定。
	// ---------------------------------------------------------------------------------------------------------------------------
	// 初期表示のためのformへの値セット。
	public function indexCameraGroup(&$form) {
		
		// カメラグループの設定
		$form["group_ids"] = [];
		$form["group_names"] = [];
		foreach ($this->groups as $group_id=>$group) {
			$form["group_ids"][]   = $group_id;
			$form["group_names"][] = $group["group_name"];
			
		}
		
		// APBグループ
		$form["apb_group_ids"] = [];
		$form["apb_group_names"] = [];
		foreach ($this->apbGroups as $apb_group_id=>$apb_group) {
			$form["apb_group_ids"][]   = $apb_group_id;
			$form["apb_group_names"][] = $apb_group["apb_group_name"];
		}
		
	}
	
	// グループを登録
	public function registGroupAction(&$form) {
		
		$data = Validators::set($form)
			->at("group_ids"      , "更新対象のグループID")->arrayValue()->digit()->inArray(array_keys($this->groups))
			->at("group_names"    , "更新対象のグループ名")->arrayValue()->maxlength(100)
			->at("new_group_names", "追加対象のグループ名")->arrayValue()->maxlength(100)
			->getValidatedData();

		if (Errors::isErrored()) return "device.tpl";
		
    if (ENABLE_AWS) {
      $groupLimit = 100;
    } else {
      $groupLimit = ($this->contractor["allow_device_num"]) ? $this->contractor["allow_device_num"] : 100;
    }

		if (!empty($data["new_group_names"][0]) && (count($data["group_ids"]) + count($data["new_group_names"]) > $groupLimit)) {
			Errors::add("", "グループは${groupLimit}件までとして下さい。");
			return "device.tpl";
		}
		
		// 名称重複チェック
		$check = $data["group_names"];
		foreach ($data["new_group_names"] as $v) $check[] = $v;
		$existed = [];
		foreach ($check as $group_name) {
			if (empty($group_name)) continue;
			
			if (isset($existed[$group_name])) {
				Errors::add("group_names", "グループ名「".h($group_name)."」が重複しています。");
				return "device.tpl";
			}
			$existed[$group_name] = 1;
		}
		
		
		// 更新登録。
		$sortOrder = 10;
		foreach ($data["group_ids"] as $idx=>$group_id) {
			$group_name = $data["group_names"][$idx];
			
			if (empty($group_name)) {
				// 名称が空になっている場合には削除。
				DB::delete("delete from t_device_group_device where device_group_id = {value}", $group_id);
				DB::delete("delete from t_device_group        where device_group_id = {value}", $group_id);
				
				// ログ出力
				$loginfo = [
					"action_sub_type"=>2,
					"detail_json"=>json_encode([
						"グループ名"=>$this->groups[$group_id]["group_name"]
					],JSON_UNESCAPED_UNICODE)
				];
				$this->facetOperateLog($loginfo);
				
			} else {
				// 名称と並び順をupdate
				DB::update("update t_device_group set group_name = {group_name}, sort_order = {sort_order}, update_time = now(), update_user_id = {login_user_id} where device_group_id = {device_group_id}", [
					"group_name"=>$group_name, "sort_order"=>$sortOrder, "device_group_id"=>$group_id
				]);
				
				// ログ出力
				if ($this->groups[$group_id]["group_name"]!=$group_name) {
					$loginfo = [
						"action_sub_type"=>1,
						"detail_json"=>json_encode([
							"変更前グループ名"=>$this->groups[$group_id]["group_name"],
							"変更後グループ名"=>$group_name
						],JSON_UNESCAPED_UNICODE)
					];
					$this->facetOperateLog($loginfo);
				}
				
				$sortOrder += 10;
			}
			
		}
		
		// 新規登録。
		foreach ($data["new_group_names"] as $group_name) {
			if (empty($group_name)) continue;
			
			DB::insert("insert into t_device_group set contractor_id = {contractor_id}, group_name = {group_name}, sort_order = {sort_order}, create_time = now(), create_user_id = {login_user_id}, update_time = now(), update_user_id = {login_user_id}", [
				"contractor_id"=>$this->contractor_id, "group_name"=>$group_name, "sort_order"=>$sortOrder
			]);
			
			// ログ出力
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"グループ名"=>$group_name
				],JSON_UNESCAPED_UNICODE)
			];
			$this->facetOperateLog($loginfo);
			
			$sortOrder += 10;
		}
		
		$this->completeRedirect("グループの登録を完了しました。");
	}

	
	// APBグループを登録
	public function registApbGroupAction(&$form) {
		
		if (!Session::getLoginUser("apb_mode_flag")) response400();
		
		$data = Validators::set($form)
			->at("apb_group_ids"      , "更新対象のグループID")->arrayValue()->digit()->inArray(array_keys($this->apbGroups))
			->at("apb_group_names"    , "更新対象のグループ名")->arrayValue()->maxlength(100)
			->at("new_apb_group_names", "追加対象のグループ名")->arrayValue()->maxlength(100)
			->getValidatedData();

		if (Errors::isErrored()) return "device.tpl";
		
		if (count($data["apb_group_ids"]) + count($data["new_apb_group_names"]) > 100) {
			Errors::add("", "カメラグループは最大100件までしか登録できません。");
			return "device.tpl";
		}
		
		// 名称重複チェック
		$check = $data["apb_group_names"];
		foreach ($data["new_apb_group_names"] as $v) $check[] = $v;
		$existed = [];
		foreach ($check as $apb_group_name) {
			if (empty($apb_group_name)) continue;
			
			if (isset($existed[$apb_group_name])) {
				Errors::add("apb_group_names", "グループ名「".h($apb_group_name)."」が重複しています。");
				return "device.tpl";
			}
			$existed[$apb_group_name] = 1;
		}
		
		
		// 更新登録。
		$sortOrder = 10;
		foreach ($data["apb_group_ids"] as $idx=>$apb_group_id) {
			$apb_group_name = $data["apb_group_names"][$idx];
			
			if (empty($apb_group_name)) {
				// 名称が空になっている場合には削除。
				DB::delete("delete from t_apb_group_device where apb_group_id = {value}", $apb_group_id);
				DB::delete("delete from t_apb_group        where apb_group_id = {value}", $apb_group_id);
				
			} else {
				// 名称と並び順をupdate
				DB::update("update t_apb_group set apb_group_name = {apb_group_name}, sort_order = {sort_order}, update_time = now(), update_user_id = {login_user_id} where apb_group_id = {apb_group_id}", [
					"apb_group_name"=>$apb_group_name, "sort_order"=>$sortOrder, "apb_group_id"=>$apb_group_id
				]);
				
				$sortOrder += 10;
			}
			
		}
		
		// 新規登録。
		foreach ($data["new_apb_group_names"] as $apb_group_name) {
			if (empty($apb_group_name)) continue;
			
			DB::insert("insert into t_apb_group set contractor_id = {contractor_id}, apb_group_name = {apb_group_name}, sort_order = {sort_order}, create_time = now(), create_user_id = {login_user_id}, update_time = now(), update_user_id = {login_user_id}", [
				"contractor_id"=>$this->contractor_id, "apb_group_name"=>$apb_group_name, "sort_order"=>$sortOrder
			]);
			
			$sortOrder += 10;
		}
		
		$this->completeRedirect("APBグループの登録を完了しました。");
	}
	
	
	// グループに属するデバイスを登録。
	public function registGroupDeviceAction(&$form) {
		
		$v = Validators::set($form);
		
		foreach ($this->groups as $group_id=>$group) {
			$deviceIds = array_map(function($v) { return $v["device_id"]; }, $this->devices);
			$v->at("group_devices_".$group_id, "グループ[".$group["group_name"]."]の選択カメラ")->arrayValue()->inArray($deviceIds);
		}
		
		if (Errors::isErrored()) return "device.tpl";
		
		$data = $v->getValidatedData();
		
		foreach ($this->groups as $group_id=>$group) {
			$deviceIds = $data["group_devices_".$group_id];
			DB::delete("delete from t_device_group_device where device_group_id = {value}", $group_id);
			
			foreach ($deviceIds as $device_id) {
				DB::insert("insert into t_device_group_device set device_group_id = {device_group_id}, device_id = {device_id}, create_time = now(), create_user_id = {login_user_id}", [
					"device_group_id"=>$group_id, "device_id"=>$device_id
				]);
			}
			
		}
		
		$this->completeRedirect("カメラグループへのカメラ登録を完了しました。");
	}
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：カメラ設定。
	// ---------------------------------------------------------------------------------------------------------------------------
	// 初期表示のためのformへの値セット。
	public function indexCamera(&$form) {
		$form["device_search_group_ids"] = array_keys($this->groupsDisplay);
		$form["device_search_device_ids"] = array_keys($this->devices);
	}
	
	// 一覧の検索。
	public function listSearchAction(&$form, $isInlucde = false) {
		// 検索。
		$data = Filters::ref($form)
			->at("_form_session_key")->len(3)
			->at("device_search_serial_no"	)->len(11)->narrow()
			->at("device_search_group_ids"	)->enumArray($this->groups+array('-1'=>true))
			->at("device_search_device_ids"	)->enumArray($this->devices)
			->at("device_search_search_init"		)->digit(1)
			->at("device_search_pageNo", 1)->digit()
			->at("device_search_limit", 20)->enum(Enums::pagerLimit())
			->getFilteredData();
		
		// filter
		$list = [];
		foreach ($this->devices as $device_id=>$device) {
			$predicate = false;
			$listSn = $data["device_search_serial_no"];
			if (!empty($listSn)){
				$predicate = mb_strpos($device["serial_no"], $listSn) !== false;
			} else {
				$listDeviceIds = $data["device_search_device_ids"];
				if (empty($listDeviceIds)) {
					break;
				} else {
					$predicate = in_array($device_id,$listDeviceIds);
				}
			}
			if (!$predicate) {
				continue;
			}

			foreach ($this->groups as $group_id=>$group) {
				if (in_array($device_id,$group["deviceIds"])){
					$device["device_group_name"] = $group["group_name"];
					break;
				}
			}
			foreach ($this->apbGroups as $apb_group_id=>$apb_group) {
				if (in_array($device_id,$apb_group["deviceIds"])) {
					if (!isset($device["apb_group_id"])){
						$device["apb_group_id"] = [];
					}
					$device["apb_group_id"][] = $apb_group_id;
				}
			}
			$list[$device_id] = $device;
		}
		
		// pager
		$pageInfo = new PageInfo($data["device_search_pageNo"], $data["device_search_limit"]);
		$count = count($list);
		$pageInfo->setRowCount($count);
		$list = array_slice($list,$pageInfo->getOffset(),$pageInfo->getLimit());
		
		// 削除完了後に戻るためのパラメータ。
		if (!$isInlucde) {
			$form_p = (isset($form["_p"])) ? $form["_p"] : "";
			$form["list_del_back"] = Session::setData(createQueryStringExcludePulldown($data)."&tab=modify&_p=".urlencode($form_p));
		}
		
		$this->assign("list_list", $list);
		$this->assign("list_pageInfo", $pageInfo);
		
		return "device.tpl";
	}
	
	// カメラの更新の開始。
	function modDeviceInitAction(&$form) {
		// add-start version3.0 founder feihan
		$this->assign("deviceRoles", $this->deviceRoles);
		// add-end version3.0 founder feihan
		
		// 一覧の再検索。
		$this->listSearchAction($form, true);
		
		// 対象データの取得。
		$data = Filters::ref($form)
			->at("list_mod_init_device_id")->digit()
			->at("_form_session_key")->len(3)
			->getFilteredData();
		
		if (!empty($data["list_mod_init_device_id"])) {
			$modDevice = $this->devices[$data["list_mod_init_device_id"]];
			if(!empty($modDevice)){
				// 完了後に戻るためのパラメータ。
				$form["list_mod_back"] = Session::setData(createQueryStringExcludePulldown($form, ["list_mod_init_device_id"]));
				
				// グループ及びapbグループinfo
				foreach ($this->groups as $group_id=>$group) {
					if (in_array($modDevice["device_id"],$group["deviceIds"])){
						$modDevice["group_id"] = $group_id;
						break;
					}
				}
				foreach ($this->apbGroups as $apb_group_id=>$apb_group) {
					if (in_array($modDevice["device_id"],$apb_group["deviceIds"])) {
						if (!isset($modDevice["apb_group_id"])){
							$modDevice["apb_group_id"] = [];
						}
						$modDevice["apb_group_id"][] = $apb_group_id;
					}
				}
				
				foreach ($modDevice as $k=>$v) {
					$form["list_mod_".$k] = $v;
				}
				
				$this->assign("list_modDevice", true);
			}
		}
		
		return "device.tpl";
	}
	// カメラの更新。
	function modDeviceAction(&$form) {
		
		// 一覧の再検索。
		$this->listSearchAction($form, true);

		$this->assign("list_modDevice", true);
		
		// 入力チェック。
		$v = Validators::set($form);
		$v	->at("list_mod_group_id", "カメラグループ")->ifNotEquals("")->inArray(array_keys($this->groups))->ifEnd()
			->at("list_mod_device_id", "カメラ番号")->required()->maxlength(100)->digit()
			->at("list_mod_description", "カメラ名称")->maxlength(100)
			->at("list_mod_sort_order", "カメラNo")->required()->digit()->maxlength(100)->dataExists(1, "select 1 from m_device where contractor_id = {$this->contractor_id} and contract_state = 10 and device_id != {$form['list_mod_device_id']} and sort_order = {value}")
			->at("list_mod_push_url", "PUSH転送先")->maxlength(100)
			->at("list_mod_picture_check_device_flag", "画像チェックデバイス")->flag();
		// add-start version3.0 founder feihan
		if($this->contractor["enter_exit_mode_flag"] == 1){
			if (empty($form['list_mod_group_id']) && !empty($form['list_mod_device_role'])) Errors::add("カメラ機能","カメラ機能をご利用の場合はグループを指定してください。");
			$v = $v ->at("list_mod_device_role","カメラ機能")->ifNotEquals("")->inArray(array_keys($this->deviceRoles))->ifEnd();
		}
		// add-end version3.0 founder feihan
		if (Session::getLoginUser("apb_mode_flag")) {
			$v = $v	->at("list_mod_apb_group_id", "APBグループ")->arrayValue()->digit()->inArray(array_keys($this->apbGroups))
					->at("list_mod_apb_type", "APB設定")->digit(1, 3);
		}
		if (Errors::isErrored()) return "device.tpl";
		
		$data = $v->getValidatedData();
		$device = array_filter($this->devices,fn ($d) => $d["device_id"]==$data["list_mod_device_id"]);
		$data["list_mod_picture_check_device_flag"] = ($data["list_mod_picture_check_device_flag"]) ? 1 : 0;
		if (empty($device)) response400();
		// mod-start version3.0 founder feihan
		$updateSql = "update m_device set description = {description}, sort_order = {sort_order}, apb_type = {apb_type}, push_url = {push_url}, picture_check_device_flag = {picture_check_device_flag}";
		if($this->contractor["enter_exit_mode_flag"] == 1){
			$updateSql = $updateSql.", device_role = {device_role}";
		}
		DB::update($updateSql." where device_id = {device_id}", [
			"description"=>$data["list_mod_description"]
			, "sort_order"=>$data["list_mod_sort_order"]
			, "push_url"=>$data["list_mod_push_url"]
			, "apb_type"=>isset($data["list_mod_apb_type"]) ? $data["list_mod_apb_type"] : null
			, "device_role"=>isset($data["list_mod_device_role"]) ? $data["list_mod_device_role"] : null
			, "device_id"=>$data["list_mod_device_id"]
			, "picture_check_device_flag"=>$data["list_mod_picture_check_device_flag"]
		]);
		// mod-end version3.0 founder feihan
		
		DB::delete("delete from t_device_group_device where device_id = {value}", $data["list_mod_device_id"]);
		if (!empty($data["list_mod_group_id"])) {
			DB::insert("insert into t_device_group_device set device_group_id = {device_group_id}, device_id = {device_id}, create_time = now(), create_user_id = {login_user_id}", [
				"device_group_id"=>$data["list_mod_group_id"]
				, "device_id"=>$data["list_mod_device_id"]
			]);
		}
		DB::delete("delete from t_apb_group_device where device_id = {value}", $data["list_mod_device_id"]);
		if (!empty($data["list_mod_apb_group_id"])) {
			
			foreach ($data["list_mod_apb_group_id"] as $apb_group_id) {
				
				DB::insert("insert into t_apb_group_device set apb_group_id = {apb_group_id}, device_id = {device_id}, create_time = now(), create_user_id = {login_user_id}", [
					"apb_group_id"=>$apb_group_id
					, "device_id"=>$data["list_mod_device_id"]
				]);
				
			}
		}
		
		$query = Session::getData($form["list_mod_back"]);
		$url = "./listSearch".$query;
		
		// ログ出力
		$existedDevice = $this->devices[$data["list_mod_device_id"]];
		$existedGroup = array_values(array_filter($this->groups,fn($g)=>in_array($existedDevice["device_id"],$g["deviceIds"])))[0] ?? [];
		$currentGroup = isset($data["list_mod_group_id"]) ? $this->groups[$data["list_mod_group_id"]] : [];
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"変更対象"=>["シリアル番号"=>$existedDevice["serial_no"]],
				"変更前"=>
					["グループ名"=>isset($existedGroup["group_name"]) ? $existedGroup["group_name"] : "",
						"カメラ名"=>$existedDevice["description"]],
				"変更後"=>
					["グループ名"=>isset($currentGroup["group_name"]) ? $currentGroup["group_name"] : "",
						"カメラ名"=>$data["list_mod_description"]]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect($url, "カメラ情報の変更を行いました。");
	}
	
	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- タブ：認証関連基本設定・更新
	// --------------------------------------------------------------------------------------------------------------------------- 
	// 初期表示のためのformへの値セット。
	public function indexRecog(&$form) {
	
		// 入力のためのデフォルト値をセット。
		ConfigSetService::setRecogConfigSetDefaultValue($form);
	}
	
	// 認識設定セットを登録。
	public function registRecogConfigSetAction(&$form) {

    // オンプレの場合は登録数チェック
    if (!ENABLE_AWS) {
      $recogConfigSetLimit = ($this->contractor["allow_device_num"]) ? $this->contractor["allow_device_num"] : 100;
      $recogConfigSetCount = DB::selectOne("SELECT COUNT(*) FROM t_recog_config_set WHERE contractor_id = {value}", $this->contractor_id);
      if ($recogConfigSetCount + 1 > $recogConfigSetLimit) {
        Errors::add("", "認証関連基本設定は最大${recogConfigSetLimit}件までしか登録できません。");
        return "device.tpl";
      }
    }
		
		// 入力チェック。
		$v = ConfigSetService::getRecogConfigSetRegistValidator($form, $this->recogConfigSets);
		$data = $v->getValidatedData();
		if (Errors::isErrored()) return "device.tpl";
		
		// 登録。
		$id = ConfigSetService::registRecogConfigSet($this->contractor_id, $data);
		
		// ログ出力
		if ($data["recog_regist_type"] == "add") {
			$action_sub_type = 0;
			$config_set_name = $data["recog_config_set_name"];
		} else {
			$action_sub_type = 1;
			$config_set_name = $this->recogConfigSets[$data["recog_config_set_id"]]["recog_config_set_name"];
		}
		$loginfo = [
			"action_sub_type"=>$action_sub_type,
			"detail_json"=>json_encode([
				"登録名"=>$config_set_name,
				"設定内容"=>ConfigSetService::getRecogConfigLogDetail($data)
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		$this->completeRedirect("認識関連設定セットの登録を完了しました。", "&tab=recog1&upd_rrs=".base_convert($id, 10, 36));
	}
	
	// 認識設定セットを削除。
	public function deleteRecogConfigSetAction(&$form) {
		
		// 入力チェック。
		$v = ConfigSetService::getRecogConfigSetDeleteValidator($form, $this->recogConfigSets);
		$data = $v->getValidatedData();
		if (Errors::isErrored()) return "device.tpl";
		
		// 削除。
		ConfigSetService::deleteRecogConfigSet($this->contractor_id, $data);
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"登録名"=>$this->recogConfigSets[$data["recog_config_set_id"]]["recog_config_set_name"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		$this->completeRedirect("認識関連設定セットの削除を完了しました。", "&tab=recog1");
	}
	

	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- タブ：システム関連基本設定・更新
	// --------------------------------------------------------------------------------------------------------------------------- 
	// 初期表示のためのformへの値セット。
	public function indexSystem(&$form) {
	
		// 入力のためのデフォルト値をセット。
		ConfigSetService::setSystemConfigSetDefaultValue($form);
	}
	
	// 認識設定セットを登録。
	public function registSystemConfigSetAction(&$form) {

    // オンプレの場合は登録数チェック
    if (!ENABLE_AWS) {
      $systemConfigSetLimit = ($this->contractor["allow_device_num"]) ? $this->contractor["allow_device_num"] : 100;
      $systemConfigSetCount = DB::selectOne("SELECT COUNT(*) FROM t_system_config_set WHERE contractor_id = {value}", $this->contractor_id);
      if ($systemConfigSetCount + 1 > $systemConfigSetLimit) {
        Errors::add("", "システム基本設定は最大${systemConfigSetLimit}件までしか登録できません。");
        return "device.tpl";
      }
    }
		
		// 入力チェック。
		$v = ConfigSetService::getSystemConfigSetRegistValidator($form, $this->systemConfigSets);
		$data = $v->getValidatedData();
		if (Errors::isErrored()) return "device.tpl";
		
		// 登録。
		$id = ConfigSetService::registSystemConfigSet($this->contractor_id, $data);
		
		// ログ出力
		if ($data["system_regist_type"] == "add") {
			$action_sub_type = 0;
			$config_set_name = $data["system_config_set_name"];
		} else {
			$action_sub_type = 1;
			$config_set_name = $this->systemConfigSets[$data["system_config_set_id"]]["system_config_set_name"];
		}
		$loginfo = [
			"action_sub_type"=>$action_sub_type,
			"detail_json"=>json_encode([
				"登録名"=>$config_set_name,
				"設定内容"=>ConfigSetService::getSystemConfigLogDetail($data)
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		
		$this->completeRedirect("システム関連設定セットの登録を完了しました。", "&tab=system1&upd_srs=".base_convert($id, 10, 36));
	}
	
	// 認識設定セットを削除。
	public function deleteSystemConfigSetAction(&$form) {
		
		// 入力チェック。
		$v = ConfigSetService::getSystemConfigSetDeleteValidator($form, $this->systemConfigSets);
		$data = $v->getValidatedData();
		if (Errors::isErrored()) return "device.tpl";
		
		// 削除。
		ConfigSetService::deleteSystemConfigSet($this->contractor_id, $data);
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"登録名"=>$this->systemConfigSets[$data["system_config_set_id"]]["system_config_set_name"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		$this->completeRedirect("認識関連設定セットの削除を完了しました。", "&tab=system1");
	}
	
	
	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- アラーム設定
	// --------------------------------------------------------------------------------------------------------------------------- 
	
	public $alertMailDefault =
"顔認証デバイスにおいて、下記の通りの異常が検知されました。

シリアルNo　：【シリアルNo】
カメラ　　　：【カメラ名称】
認識日時　　：【認識日時】

PASS　　　　：【PASS結果】
温度判定　　：【温度判定結果】
温度測定結果：【温度測定結果】
マスク　　　：【マスク判定結果】
登録有無　　：【登録者判定結果】
ユーザーID　：【ユーザーID】
ユーザー氏名：【ユーザー氏名】";

public $alertConnectMailDefault =
"顔認証デバイスの接続が切断されました。

シリアルNo　：【シリアルNo】
カメラ　　　：【カメラ名称】
接続確認日時：【確認日時】";
public $alertReConnectMailDefault =
"顔認証デバイスの接続が復旧されました。

シリアルNo　：【シリアルNo】
カメラ　　　：【カメラ名称】
接続確認日時：【確認日時】";
	
	public function indexAlerm(&$form) {
		
	}

	public function selectAlermAction(&$form) {
		
		$data = Filters::ref($form)
			->at("alert_no")->digit(1, 6)
			->getFilteredData();

		// カメラグループ登録の表示用
		self::indexCameraGroup($form);
		// 認証関連設定のデフォルト値をセット。
		ConfigSetService::setRecogConfigSetDefaultValue($form);
		// システム基本設定のデフォルト値をセット。
		ConfigSetService::setSystemConfigSetDefaultValue($form);
		
		if ($data["alert_no"]) {
			$alert = DB::selectRow("select * from t_alert where contractor_id = {contractor_id} and alert_no = {alert_no}", ["contractor_id"=>$this->contractor_id, "alert_no"=>$data["alert_no"]]);
			
			// 未登録の場合は初期値を設定。
			if (empty($alert)) {
				$alert["alert_name"]  = "アラーム設定".$data["alert_no"];
				$alert["alert_no"]    = $data["alert_no"];
				$alert["enable_flag"] = 0;
				$alert["nopass_flag"] = 1;
				if ($data["alert_no"] != 6) {
					$alert["mail_subject"]   = "[facet Cloud] 異常が検知されました。";
					$alert["mail_body"]      = $this->alertMailDefault;
				} else {
					$alert["mail_subject"]   = "[facet Cloud] デバイス接続が切断されました。";
					$alert["mail_body"]      = $this->alertConnectMailDefault;
					$alert["mail_subject_r"] = "[facet Cloud] デバイス接続が復旧しました。";
					$alert["mail_body_r"]    = $this->alertReConnectMailDefault;
				}
			} elseif ($alert["alert_no"] == 6) {
				$alertSub  = explode("【以降は復旧】",$alert["mail_subject"]);
				$alertbody = explode("【以降は復旧】",$alert["mail_body"]);
				$alert["mail_subject"]   = $alertSub[0];
				$alert["mail_body"]      = $alertbody[0];
				$alert["mail_subject_r"] = $alertSub[1];
				$alert["mail_body_r"]    = $alertbody[1];
			}
			
			foreach ($alert as $k=>$v) {
				$form["alert_".$k] = $v;
			}
			
			if (!empty($alert["alert_id"])) {
				$form["alert_device_ids"] = DB::selectOneArray("select device_id from t_alert_device where alert_id = {value}", $alert["alert_id"]);
				if(!empty($form["alert_device_ids"])){
					$form["alert_group_ids"] = [];
					foreach ($this->groupsDisplay as $gid=>$group){
						if(in_array($gid,$form["alert_group_ids"])) continue;
						foreach ($form["alert_device_ids"] as $device_id){
							if(in_array($device_id,$group["deviceIds"])) array_push($form["alert_group_ids"],$gid);
						}
					}
				}
			}
			
		}
		
		return "device.tpl";
	}
	
	public function registAlermAction(&$form) {
		
		$data = Validators::set($form)
				->at("alert_no"				, "alert_no"				)->required()->digit(1, 6)
				->at("alert_alert_name"		, "設定名"					)->required()->maxlength(100)
				->at("alert_enable_flag"	, "メール発砲有無"			)->required()->flag()
				->at("alert_nopass_flag"	, "NO PASS"					)->flag()
				->at("alert_guest_flag"		, "未登録者"				)->flag()
				->at("alert_temp_flag"		, "温度異常"				)->flag()
				->at("alert_mask_flag"		, "マスク未装着"			)->flag()
				->at("alert_mail_1"			, "送信先メールアドレス1"	)->required()->mail()->maxlength(100)
				->at("alert_mail_2"			, "送信先メールアドレス2"	)->mail()->maxlength(100)
				->at("alert_mail_3"			, "送信先メールアドレス3"	)->mail()->maxlength(100)
				->at("alert_mail_subject"	, "メールタイトル"			)->required()->maxlength(100)
				->at("alert_mail_body"		, "メール本文"				)->required()->maxlength(5000);
				if ($form['alert_no'] == 6) {
					$data = $data->at("alert_mail_subject_r"	, "メールタイトル"			)->required()->maxlength(100);
					$data = $data->at("alert_mail_body_r"		, "メール本文"				)->required()->maxlength(5000);
				}
				$data = $data->at("alert_device_ids", "対象カメラ")->arrayValue()->required()->inArray(array_keys($this->devices))
				->getValidatedData();
		
		if (Errors::isErrored()) return "device.tpl";
		
		
		$param = $data;
		$param["contractor_id"] = $this->contractor_id;
		
		$alertId = DB::selectOne("select alert_id from t_alert where contractor_id = {contractor_id} and alert_no = {alert_no}", $param);
		
		if ($param['alert_no'] == 6) {
			$param["alert_mail_subject"] = $param["alert_mail_subject"]."【以降は復旧】".$param["alert_mail_subject_r"];
			$param["alert_mail_body"] = $param["alert_mail_body"]."【以降は復旧】".$param["alert_mail_body_r"];
		}

		if (!empty($alertId)) {
			
			DB::update("
				update 
					t_alert
				set
					update_time 		= now()
					, update_user_id 	= {login_user_id}
					, enable_flag 		= {flag alert_enable_flag}
					, alert_name		= {alert_alert_name}
					, mail_1			= {alert_mail_1}
					, mail_2			= {alert_mail_2}
					, mail_3			= {alert_mail_3}
					, mail_subject		= {alert_mail_subject}
					, mail_body			= {alert_mail_body}
					, nopass_flag		= {flag alert_nopass_flag}
					, guest_flag		= {flag alert_guest_flag}
					, temp_flag			= {flag alert_temp_flag}
					, mask_flag			= {flag alert_mask_flag}
				where 
					contractor_id 		= {contractor_id}
					and alert_no 		= {alert_no}
			", $param);
			
		} else {
			
			$alertId = DB::insert("
				insert into
					t_alert
				set
					contractor_id 		= {contractor_id}
					, alert_no 			= {alert_no}
					, create_time		= now()
					, create_user_id 	= {login_user_id}
					, update_time 		= now()
					, update_user_id 	= {login_user_id}
					, enable_flag 		= {flag alert_enable_flag}
					, alert_name		= {alert_alert_name}
					, mail_1			= {alert_mail_1}
					, mail_2			= {alert_mail_2}
					, mail_3			= {alert_mail_3}
					, mail_subject		= {alert_mail_subject}
					, mail_body			= {alert_mail_body}
					, nopass_flag		= {flag alert_nopass_flag}
					, guest_flag		= {flag alert_guest_flag}
					, temp_flag			= {flag alert_temp_flag}
					, mask_flag			= {flag alert_mask_flag}
			", $param);
			
		}
		
		DB::delete("delete from t_alert_device where alert_id = {value}", $alertId);
		
		foreach ($data["alert_device_ids"] as $deviceId) {
			DB::insert("insert into t_alert_device set alert_id = {alert_id}, device_id = {device_id}", ["alert_id"=>$alertId, "device_id"=>$deviceId]);
		}
		
		sendCompleteRedirect("./selectAlerm?tab=alerm&alert_no=".$data["alert_no"], "アラーム設定の登録を完了しました。");
	}
	
	public function sendAlertTestMailAction(&$form) {

		$data = Validators::set($form)
				->at("alert_mail_1"			, "送信先メールアドレス1"	)->mail()->maxlength(100)
				->at("alert_mail_2"			, "送信先メールアドレス2"	)->mail()->maxlength(100)
				->at("alert_mail_3"			, "送信先メールアドレス3"	)->mail()->maxlength(100)
				->getValidatedData();
		
		setJsonHeader();
				
		if (Errors::isErrored()) {
			echo json_encode(["error"=>join(" / ", Errors::getMessagesArray())]);
			return;
		}
		
		// テストメール送信。
		if (!empty($data["alert_mail_1"])) execSendMail("alert_test_mail.php", $data["alert_mail_1"]);  
		if (!empty($data["alert_mail_2"])) execSendMail("alert_test_mail.php", $data["alert_mail_2"]);  
		if (!empty($data["alert_mail_3"])) execSendMail("alert_test_mail.php", $data["alert_mail_3"]);  
		
		echo json_encode(["result"=>"OK"]);
	}
	
	//add-start founder yaozhengbang
	private function functionAccessFlag() {
		$funcName =	Session::getUserFunctionAccess("function_name");
		$flag[0] = (Session::getLoginUser("user_flag") == 1); // カメラ設定
		$flag[1] = (array_search("認証関連基本設定・更新" , $funcName)>-1) || (Session::getLoginUser("user_flag") == 1);
		$flag[2] = (array_search("認証関連設定割当" , $funcName)>-1) || (Session::getLoginUser("user_flag") == 1);
		$flag[3] = (array_search("システム基本設定・更新" , $funcName)>-1) || (Session::getLoginUser("user_flag") == 1);
		$flag[4] = (array_search("システム設定割当" , $funcName)>-1) || (Session::getLoginUser("user_flag") == 1);
		$flag[5] = (empty(Session::getLoginUser("group_id")) || Session::getLoginUser("user_flag") == 1); // アラーム設定
		if(Session::getLoginUser("user_flag") != 1){
			$flag[5]?$flag[5] = (array_search("アラーム設定" , $funcName)>-1):"";
		}
		return $flag;
	}
	//add-end founder yaozhengbang
}
