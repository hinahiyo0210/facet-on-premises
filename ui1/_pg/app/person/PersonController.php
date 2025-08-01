<?php 

class PersonController extends UserBaseController {
	
	public $devices;

	public $modDevice;
	
	public $groups;
	
	// add-start founder zouzhiyuan
	public $groupsDisplay;
	// add-start founder zouzhiyuan
	
	public $contractor;
	
	// add-start founder yaozhengbang
	public $personTopMenuFlag;
	// add-end founder yaozhengbang
	
	// add-start version3.0  founder feihan
	public $personTypes;
	// add-end version3.0  founder feihan
	
	const ZIP_LIMIT = 1024 * 1024 * 1024;		// zipアップロード制限：1GBまで
		
	// @Override
	public function prepare(&$form) {
		
		// sessionからプルダウンを取得。
		parent::prepare($form);

		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);
		
		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);
		
		// add-start founder zouzhiyuan
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
		// add-end founder zouzhiyuan
		
		// 契約者情報を取得。
		$this->contractor = DB::selectRow("select * from m_contractor where contractor_id = {value}", $this->contractor_id);
		
		$this->assign("bulk_zip_limit", self::ZIP_LIMIT);
		
		// ユーザ権限設定を取得。
		$this->personTopMenuFlag = self::gettopMenuFlag();
		
		// 初期表示時、グループとカメラは全て選択に設定される。
		if (empty($form["list_search_init"])) {
			$form["list_group_ids"] = array_keys($this->groupsDisplay);
			$form["list_device_ids"] = array_keys($this->devices);
		}
		if (empty($form["export_search_init"])) {
			$form["export_group_ids"] = array_keys($this->groupsDisplay);
			$form["export_device_ids"] = array_keys($this->devices);
		}
		if (empty($form["trans_search_init"])) {
			$form["trans_group_ids"] = array_keys($this->groupsDisplay);
			$form["trans_device_ids"] = array_keys($this->devices);
		}
		// add-start version3.0  founder feihan
		if($this->contractor["enter_exit_mode_flag"] == 1){
			// ユーザー区分情報を取得。
			$this->personTypes = DB::selectKeyRow("select person_type_code, person_type_name from m_person_type where contractor_id = {value}", $this->contractor_id, "person_type_code");
		}
		// add-end version3.0  founder feihan

		$this->assign("personTypeList", $this->personTypes);
	}
	
	private function completeRedirect($msg, $appendParam = "") {
		
		// スクロール位置。
		$p = Filter::len(req("_p"), 5);
		sendCompleteRedirect("./?_p=".urlencode($p).$appendParam, $msg);
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
		
		Filters::ref($form)->at("tab")->values(["list", "bulk", "export", "trans"]);
		
		$this->indexNew($form);
		$this->indexList($form);
		$this->indexBulk($form);
		$this->indexExport($form);
		$this->indexTrans($form);
		
		return "person.tpl";
	}

	// ---------------------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- 共通。
	// --------------------------------------------------------------------------------------------------------------------------- 
	// デバイスへ人物を反映する。(ajax)
	public function registPersonForDeviceAction(&$form, $noJsonHeader = false) {
		
		if (empty($noJsonHeader)) {
			setJsonHeader();
		}
		
		$v = Validators::set($form)
			->at("device_id", "device_id")->required()->inArray(array_keys($this->devices))
			->at("type"		, "type"	 )->required()->inArray(["new", "mod", "del", "trans"])
			->at("override"	, "override" )->flag();
		
		if ($form["type"] == "new" || $form["type"] == "mod" || $form["type"] == "trans") {
			$v = $v->at("person_code", "person_code" )->dataExists(0, "select 1 from t_person where contractor_id = {$this->contractor_id} and person_code = {value}");
		} else if ($form["type"] == "del") {
			$v = $v->at("person_code", "person_code" )->required()->maxlength(12)->half();
		}
			
		$data = $v->getValidatedData();
		
		if (Errors::isErrored()) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
		}
		
		// 登録。
		try {
			$device = $this->devices[$data["device_id"]];
			if ($data["type"] == "new" || $data["type"] == "mod" || $data["type"] == "trans") {
				// 人物登録
				if (!empty($data["override"]) || $data["type"] == "mod" || $data["type"] == "new") {
					$override = "1";
				} else {
					$override = "0";
				}

 				$ret = PersonService::toDevice($this->contractor, $device, $data["person_code"], $override);
 				
 				if (empty($ret["result"])) {
 					echo json_encode(["error"=>$ret["message"]]);
 					return;
 				}
 				
 				echo json_encode(["result"=>"OK"]);
				
				// ログ出力
				$loginfo = [
					"action_sub_type"=>0,
					"detail_json"=>json_encode([
						"カメラ名"=>$device["description"],
						"シリアル番号"=>$device["serial_no"],
						"ID"=>$data["person_code"]
					],JSON_UNESCAPED_UNICODE)
				];
				$this->facetOperateLog($loginfo);
				
				return;
			}
			
			if ($data["type"] == "del") {
				// 人物削除
 				$ret = PersonService::deletePersonFromDevice($device, $data["person_code"], true, true);

 				if (empty($ret["result"])) {
 					echo json_encode(["error"=>"削除が正常に行われませんでした。"]);
 					return;
 				}
 				
 				echo json_encode(["result"=>"OK"]);
				
				// ログ出力
				$loginfo = [
					"action_sub_type"=>1,
					"detail_json"=>json_encode([
						"カメラ名"=>$device["description"],
						"シリアル番号"=>$device["serial_no"],
						"ID"=>$data["person_code"]
					],JSON_UNESCAPED_UNICODE)
				];
				$this->facetOperateLog($loginfo);
				
				return;
			}
			
		} catch (ApiParameterException $e) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
			
		} catch (DeviceExclusionException $e) {
			echo json_encode(["error"=>$e->getMessage()]);
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
	// ------------------------------------------------- タブ：新規ユーザ登録。
	// --------------------------------------------------------------------------------------------------------------------------- 
	// 初期表示のためのformへの値セット。
	public function indexNew(&$form) {

		$newReg = (isset($form["newReg"])) ? Session::getData($form["newReg"]) : null;
		
		if (!empty($newReg)) {
			// デバイスへの登録へ。
			$registTargetDevices = [];
			foreach ($newReg["new_device_ids"] as $id) {
				$registTargetDevices[] = ["id"=>$id, "name"=>$this->devices[$id]["name"]];
			}
			
			$this->assign("new_registDeviceTargets", $registTargetDevices);
			$this->assign("new_registDevicePersonCode", $newReg["personCode"]);
		}
		
	}
	
	// 人物を登録
	public function registPersonAction(&$form) {
		
		$v = Validators::set($form)
			->at("new_personCode", "ID"    			)->required()->maxlength(12)->half()->dataExists(1, "select 1 from t_person where contractor_id = {$this->contractor_id} and person_code = {value}")
			->at("new_personName", "氏名"  	 		)->required()->maxlength(32)
			->at("new_birthday"  , "誕生日"			)->date()
			->at("new_picture" 	 , "写真画像"		)->required()->byteMaxlength(1024 * 1024  * 5)	// Base64後で5MB
			->at("new_device_ids", "登録対象カメラ"	)->arrayValue()->enum($this->devices)
			->at("new_card_id"	  , "カードID" )
			->arrayValue()->lineRequired("new_date_from")->lineRequired("new_date_to")->half()
			->at("new_date_from", "カード有効期間From" )
			->arrayValue()->date()
			->at("new_date_to"  , "カード有効期間To"   )
			->arrayValue()->date()
			->compFuture("new_date_from", "カード有効期間From");
		
		// add-start version3.0  founder feihan
		if($this->contractor["enter_exit_mode_flag"] == 1){
			$v = $v->at("new_person_type_code", "区分")->ifNotEquals("")->inArray(array_keys($this->personTypes))->ifEnd()
				->at("new_description1", $this->contractor["enter_exit_description_name1"])->maxlength(30)
				->at("new_description2", $this->contractor["enter_exit_description_name2"])->maxlength(30);
		}
		$this->assign("personTypeList", $this->personTypes);
		// add-end version3.0  founder feihan
		$data = $v->getValidatedData();
		
		if (!Errors::isErrored("new_card_id")) {
			for($i=0;$i<3;$i++) {
				if (empty($data["new_card_id"][$i])) {
					continue;
				} else if (preg_match("/^[0-9]+$/", $data["new_card_id"][$i])) {
					if (strlen($data["new_card_id"][$i]) > 20) {
						Errors::add("new_card_id[$i]", "20桁以下のカード番号(10進数)を指定してください。");
					}else if ("18446744073709551615" < $data["new_card_id"][$i]) { //TODO UINT_MAX  compare
						Errors::add("new_card_id[$i]", "18446744073709551615以下のカード番号(10進数)を指定してください。");
					}
				} else if (preg_match("/^[a-fA-F0-9]+$/", $data["new_card_id"][$i])) {
					if (strlen($data["new_card_id"][$i]) > 16) {
						Errors::add("new_card_id[$i]", "16桁以下のカード番号(16進数)を指定してください。");
					} else {
						// 入力文字内にa～fがあった場合は、自動でA～Fに変換してDBに登録
						$data["new_card_id"][$i] = mb_strtoupper($data["new_card_id"][$i], 'UTF-8');
					}
				} else {
					Errors::add("new_card_id[$i]", "「0～9,A～F,a～f」の文字を入力してください。");
				}
			}
		}
		
		if (Errors::isErrored()) return "person.tpl";
		
		try {
			// クラウドへデータを登録
			$serviceParam = [];
			$serviceParam["personCode"] = $data["new_personCode"];
			$serviceParam["personName"] = $data["new_personName"];
			$serviceParam["birthday"]   = $data["new_birthday"];
			$serviceParam["picture"]    = $data["new_picture"];
			$serviceParam["user_id"]    = $this->user_id;
			// add-start version3.0  founder feihan
			$serviceParam["person_type_code"]= (isset($data["new_person_type_code"])) ? $data["new_person_type_code"] : "";
			// add-end version3.0  founder feihan
			$serviceParam["person_description1"] = isset($data["new_description1"]) ? $data["new_description1"] : "";
			$serviceParam["person_description2"] = isset($data["new_description2"]) ? $data["new_description2"] : "";

			PersonService::registPerson($this->contractor, $serviceParam);
			
			// ICカード情報の登録。
			$person = PersonService::getPersonByCode($this->contractor, $data["new_personCode"]);	// 人物データを取得。
			
			$cardInfos = [];
			foreach ($data["new_card_id"] as $idx=>$cardID) {
				if (Validator::isEmpty($cardID)) continue;
				$cardInfos[] = [
					"cardID"		=> $cardID
					, "dateFrom"	=> $data["new_date_from"][$idx]
					, "dateTo"	=> $data["new_date_to"][$idx]
				];
			}
			
			// 登録。
			PersonService::registPersonCardInfo($person["person_id"], $cardInfos, $this->user_id);
			
		} catch (ApiParameterException $e) {
			// Errorsにメッセージ格納済み
			return "person.tpl";
		}
		
		if (empty($data["new_device_ids"])) {
			
			// ログ出力
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"ID"=>$data["new_personCode"],
					"氏名"=>$data["new_personName"],
					"生年月日"=>$data["new_birthday"]
				],JSON_UNESCAPED_UNICODE)
			];
			$this->facetOperateLog($loginfo);
			
			// カメラ登録無し。
			$this->completeRedirect("クラウドへのユーザーの登録を完了しました。");
		} else {
			// ログ出力
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"ID"=>$data["new_personCode"],
					"氏名"=>$data["new_personName"],
					"生年月日"=>$data["new_birthday"]
				],JSON_UNESCAPED_UNICODE)
			];
			$this->facetOperateLog($loginfo);
			
			// カメラ登録有り。
			$key = Session::setData(["personCode"=>$serviceParam["personCode"], "new_device_ids"=>$data["new_device_ids"]]);
			$this->completeRedirect("クラウドへのユーザーの登録を完了しました。続いてカメラへの登録を行います。", "&newReg={$key}");
			
		}
		
	}
	
	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- タブ：ユーザ情報一覧・変更
	// --------------------------------------------------------------------------------------------------------------------------- 
	// 初期表示のためのformへの値セット。
	public function indexList(&$form) {
		$form["list_searchType"] = (!empty(Session::getLoginUser("group_id"))) ? 2 : 1;
	}
	
	// 一覧の検索。
	public function listSearchAction(&$form, $isInlucde = false) {

		// 検索。
		$filtedData = UiPersonService::getListSearchFilter($form, "list_", $this->devices, $this->groups)->getFilteredData();
		$data = $this->arrayExcludePrefix($filtedData, "list_");

		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = UiPersonService::getList($this->contractor, $this->devices, $this->groups, $data, $pageInfo);
		
		// 画像用のCookie作成。
		UiPersonService::setCloudFrontCookie($this->contractor_id, 60 * 10 /* 10分有効 */);
		
		// ---- データ更新時：デバイスへの登録へ。
		$modReg = (isset($form["modReg"])) ? Session::getData($form["modReg"]) : "";
		if (!empty($modReg)) {
			$registTargetDevices = [];
			foreach ($modReg["device_ids"] as $id) $registTargetDevices[] = ["id"=>$id, "name"=>$this->devices[$id]["name"]];
			$this->assign("mod_registDeviceTargets", $registTargetDevices);
			$this->assign("mod_registDevicePersonCode", $modReg["personCode"]);
		}
		
		// ---- データ削除時：デバイスへの登録へ。
		$delReg = (isset($form["delReg"])) ? Session::getData($form["delReg"]) : "";
		if (!empty($delReg)) {
			$registTargetDevices = [];
			foreach ($delReg["device_ids"] as $id) $registTargetDevices[] = ["id"=>$id, "name"=>$this->devices[$id]["name"]];
			$this->assign("del_registDeviceTargets", $registTargetDevices);
			$this->assign("del_registDevicePersonCode", $delReg["personCode"]);
		}
		
		// 削除完了後に戻るためのパラメータ。
		if (!$isInlucde) {
			$form["list_del_back"] = Session::setData(createQueryStringExcludePulldown($filtedData)."&tab=list&_p=".urlencode($form["_p"]));
		}

		$this->assign("list_list", $list);
		$this->assign("list_pageInfo", $pageInfo);
		// add-start version3.0  founder feihan
		$this->assign("personTypeList", $this->personTypes);
		// add-end version3.0  founder feihan
		return "person.tpl";
	}
	
	// 人物の更新の開始。
	public function modPersonInitAction(&$form) {
		
		// 一覧の再検索。
		$this->listSearchAction($form, true);
		
		// 対象データの取得。
		$data = Filters::ref($form)
			->at("list_modPersonId")->digit()
			->getFilteredData();
			
		if (!empty($data["list_modPersonId"])) {
			$modPerson = UiPersonService::get($this->contractor, $this->devices, $this->groups, $data["list_modPersonId"]);

			// 編集する人物の関連するデバイスを取得
			$this->modDevice = DeviceGroupService::getModPersonDevices($this->contractor['contractor_id'], $modPerson);

			if (!empty($modPerson)) {

				// 完了後に戻るためのパラメータ。
				$filtedData = UiPersonService::getListSearchFilter($form, "list_", $this->devices, $this->groups)->getFilteredData();
				$form["list_mod_back"] = Session::setData(createQueryStringExcludePulldown($filtedData)."&tab=list&_p=".urlencode($form["_p"]));
 				
				// 画像ファイル取得し、base64に。			
				$modPerson["picture"] = base64_encode(AwsService::downloadS3PersonPicture($this->contractor, $modPerson["s3_object_path"], $modPerson["create_time"]));
				
				foreach ($modPerson as $k=>$v) {
					$form["list_mod_".$k] = $v;
				}
				
				// 通行可能時間帯を取得し、セット。
				if (!empty($modPerson["accessTimes"])) {
					
					foreach ($modPerson["accessTimes"] as $serialNo=>$accessTimes) {
						
						$deviceId = null;
						foreach ($this->devices as $d) {
							if ($d["serial_no"] == $serialNo) {
								$deviceId = $d["device_id"];
							}
						}
						if (empty($deviceId)) continue;
						
						foreach ($accessTimes as $idx=>$time) {
							$form["list_mod_access_flag_{$deviceId}"][] 	 = $time["accessFlag"]."";
							$form["list_mod_access_time_from_{$deviceId}"][] = $time["accessTimeFrom"];
							$form["list_mod_access_time_to_{$deviceId}"][]   = $time["accessTimeTo"];
						}
						
					}
					
				}
				
				// ICカード情報を取得し、セット。
				if (!empty($modPerson["cardInfo"])) {
					
					foreach ($modPerson["cardInfo"] as $idx=>$cardInfo) {

						$form["list_mod_card_id"][] 	 = $cardInfo["cardID"]."";
						$form["list_mod_date_from"][] = $cardInfo["dateFrom"];
						$form["list_mod_date_to"][]   = $cardInfo["dateTo"];

					}
					
				}
				
				$this->assign("list_modPerson", true);
			}
		}
		// add-start version3.0  founder feihan
		$this->assign("personTypeList", $this->personTypes);
		// add-end version3.0  founder feihan
		return "person.tpl";
	}
	
	// 人物の更新
	public function modPersonAction(&$form) {
		
		// 一覧の再検索。
		$this->listSearchAction($form, true);
		
		$this->assign("list_modPerson", true);
		
		// 入力チェック。		
		$v = Validators::set($form)
			->at("list_mod_personCode" 		, "ID"    		)->required()->maxlength(12)->half()->dataExists(0, "select 1 from t_person where contractor_id = {$this->contractor_id} and person_code = {value}")
			->at("list_mod_personName"		, "氏名"  		)->required()->maxlength(32)
			->at("list_mod_birthday"  		, "誕生日"		)->date()
			->at("list_mod_picture"   		, "写真画像"	)->required()->byteMaxlength(1024 * 1024  * 5)	// Base64後で5MB
			->at("list_mod_card_id"	  , "カードID" )
			->arrayValue()->lineRequired("list_mod_date_from")->lineRequired("list_mod_date_to")->half()
			->at("list_mod_date_from", "カード有効期間From" )
			->arrayValue()->date()
			->at("list_mod_date_to"  , "カード有効期間To"   )
			->arrayValue()->date()
			->compFuture("list_mod_date_from", "カード有効期間From");
		// add-start verson3.0 founder feihan
		if($this->contractor["enter_exit_mode_flag"] == 1){
			$v = $v->at("list_mod_person_type_code", "区分")->ifNotEquals("")->inArray(array_keys($this->personTypes))->ifEnd()
				->at("list_mod_person_description1" , $this->contractor["enter_exit_description_name1"])->maxlength(30)
				->at("list_mod_person_description2" , $this->contractor["enter_exit_description_name2"])->maxlength(30);
		}
		// add-end verson3.0 founder feihan
		if (Session::getLoginUser("apb_mode_flag")) {
			$v = $v->at("list_mod_apb_in_flag"		, "APB状態"		)->flag();
		}

		foreach ($this->devices as $deviceId=>$device) {
			
			$v = $v
				->at("list_mod_access_flag_$deviceId"	  , "「".h($device["name"])."」の通行可能時間帯指定" )->arrayValue()->flag()
				->at("list_mod_access_time_from_$deviceId", "「".h($device["name"])."」の通行可能時間帯From" )
					->arrayValue()->lineRequired("list_mod_access_flag_$deviceId")->append(":00")->datetime(2000, 2099)
				->at("list_mod_access_time_to_$deviceId"  , "「".h($device["name"])."」の通行可能時間帯To"   )
					->arrayValue()->lineRequired("list_mod_access_flag_$deviceId")->append(":00")->datetime(2000, 2099)
					->compFuture("list_mod_access_time_from_$deviceId", "「".h($device["name"])."」の通行可能時間帯From");
			
		}
		
		$data = $v->getValidatedData();
		
		if (!Errors::isErrored("list_mod_card_id")) {
			for($i=0;$i<3;$i++) {
				if (empty( $data["list_mod_card_id"][$i])) {
					continue;
				} else if (preg_match("/^[0-9]+$/", $data["list_mod_card_id"][$i])) {
					if (strlen($data["list_mod_card_id"][$i]) > 20) {
						Errors::add("list_mod_card_id[$i]", "20桁以下のカード番号(10進数)を指定してください。");
					} else if ("18446744073709551615" < $data["list_mod_card_id"][$i]) { //TODO UINT_MAX  compare
						Errors::add("list_mod_card_id[$i]", "18446744073709551615以下のカード番号(10進数)を指定してください。");
					}
				} else if (preg_match("/^[a-fA-F0-9]+$/", $data["list_mod_card_id"][$i])) {
					if (strlen($data["list_mod_card_id"][$i]) > 16) {
						Errors::add("list_mod_card_id[$i]", "16桁以下のカード番号(16進数)を指定してください。");
					} else {
						// 入力文字内にa～fがあった場合は、自動でA～Fに変換してDBに登録
						$data["list_mod_card_id"][$i] = mb_strtoupper($data["list_mod_card_id"][$i], 'UTF-8');
					}
				} else {
					Errors::add("list_mod_card_id[$i]", "「0～9,A～F,a～f」の文字を入力してください。");
				}
			}
		}
		
		if (Errors::isErrored()) {
			// エラーの場合、編集する人物の関連するデバイスを再度取得
			$modPersonId["person_id"] = DB::selectOne("select person_id from t_person where contractor_id = {$this->contractor['contractor_id']} and person_code = {value}",$form["list_mod_personCode"]);
			$this->modDevice = DeviceGroupService::getModPersonDevices($this->contractor['contractor_id'], $modPersonId);
			return "person.tpl";
		}
		
		$existedPerson = DB::selectRow("select * from t_person where contractor_id = {$this->contractor_id} and person_code = {value}", $data["list_mod_personCode"]);
		if (empty($existedPerson)) response400();
		// 変更前のユーザー情報
		$existedPerson["picture"] = base64_encode(AwsService::downloadS3PersonPicture($this->contractor, $existedPerson["s3_object_path"], $existedPerson["create_time"]));
		try {
			// ---------------------------- クラウドへデータを登録
			$serviceParam = [];
			$serviceParam["personCode"]  = $data["list_mod_personCode"];
			$serviceParam["personName"]  = $data["list_mod_personName"];
			$serviceParam["birthday"]    = $data["list_mod_birthday"];
			$serviceParam["picture"]     = $data["list_mod_picture"];
			$serviceParam["apb_in_flag"] = isset($data["list_mod_apb_in_flag"]) ? $data["list_mod_apb_in_flag"] : "";
			$serviceParam["user_id"]     = $this->user_id;
			// add-start version3.0  founder feihan
			$serviceParam["person_type_code"]    = (isset($data["list_mod_person_type_code"])) ? $data["list_mod_person_type_code"] : "";
			$serviceParam["person_description1"] = isset($data["list_mod_person_description1"]) ? $data["list_mod_person_description1"] : "";
			$serviceParam["person_description2"] = isset($data["list_mod_person_description2"]) ? $data["list_mod_person_description2"] : "";
			// add-end version3.0  founder feihan
			PersonService::registPerson($this->contractor, $serviceParam);
			
			// ---------------------------- 通行許可設定を登録。
			$person = PersonService::getPersonByCode($this->contractor, $data["list_mod_personCode"]);	// 人物データを取得。
			
			foreach ($this->devices as $deviceId=>$device) {
	 			$accessTimes = [];
	 			foreach ($data["list_mod_access_flag_$deviceId"] as $idx=>$accessFlag) {
	 				if (Validator::isEmpty($accessFlag)) continue;
	 				$accessTimes[] = [
	 					"accessFlag"		=> $accessFlag
	 					, "accessTimeFrom"	=> $data["list_mod_access_time_from_$deviceId"][$idx]
	 					, "accessTimeTo"	=> $data["list_mod_access_time_to_$deviceId"][$idx]
	 				];
	 			}
	 			
				// 登録。
	 			ApbService::registPersonAcessTimes($person["person_id"], $deviceId, $accessTimes);
			}
			
			// -------------------
			
			// ICカード情報の登録。
			$cardInfos = [];
			foreach ($data["list_mod_card_id"] as $idx=>$cardID) {
				if (Validator::isEmpty($cardID)) continue;
				$cardInfos[] = [
					"cardID"		=> $cardID
					, "dateFrom"	=> $data["list_mod_date_from"][$idx]
					, "dateTo"	=> $data["list_mod_date_to"][$idx]
				];
			}
			
			// 登録。
			PersonService::registPersonCardInfo($person["person_id"], $cardInfos, $this->user_id);
			
			
		} catch (ApiParameterException $e) {
			// エラーの場合、編集する人物の関連するデバイスを再度取得
			$modPersonId["person_id"] = DB::selectOne("select person_id from t_person where contractor_id = {$this->contractor['contractor_id']} and person_code = {value}",$form["list_mod_personCode"]);
			$this->modDevice = DeviceGroupService::getModPersonDevices($this->contractor['contractor_id'], $modPersonId);
			// Errorsにメッセージ格納済み
			return "person.tpl";
		}
		
		// 関連付けられたデバイスが存在しているのであれば、デバイス登録へ。
		$deviceIds = DB::selectOneArray("select device_id from t_device_person where person_id = {value}", $existedPerson["person_id"]);
		
		$query = Session::getData($form["list_mod_back"]);
		$url = "./listSearch".$query;
		
		if (empty($deviceIds)) {
			// カメラ登録無し。
	 		$msg = "クラウドへのユーザーの登録を完了しました。";
		} else {
			// カメラ登録有り。
			$msg = "クラウドへのユーザーの登録を完了しました。続いてカメラへの登録を行います。";
			$key = Session::setData(["personCode"=>$serviceParam["personCode"], "device_ids"=>$deviceIds]);
			$url .= "&modReg={$key}";
		}
		
		// ログ出力
		// ファイルサイズ比較
		if (!($picChange = (strlen($serviceParam["picture"])!==strlen($existedPerson["picture"])))) {
			// ハッシュ比較
			$picChange = md5($serviceParam["picture"])!==md5($existedPerson["picture"]);
		}
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"画像変更"=>$picChange?"あり":"なし",
				"変更前"=>
					["ID"=>$existedPerson["person_code"],
					"氏名"=>$existedPerson["person_name"],
					"生年月日"=>$existedPerson["birthday"]],
				"変更後"=>
					["ID"=>$data["list_mod_personCode"],
					"氏名"=>$data["list_mod_personName"],
					"生年月日"=>$data["list_mod_birthday"]]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		// エクスポートとカメラデータ移行・当て変え画面の検索タイプ保持
		$exp_src_key = isset($form["export_searchType"]) ? $form["export_searchType"] : "1";
		$trs_src_key = isset($form["trans_searchType"]) ? $form["trans_searchType"] : "1";
		$url = $url."&export_searchType=".$exp_src_key."&trans_searchType=".$trs_src_key;
		sendCompleteRedirect($url, $msg);
	}
	
	// 人物の削除の開始。
	public function startDelPersonAction(&$form) {
		
		// 一覧の再検索。
		$this->listSearchAction($form, true);
		
		// 入力チェック。		
		$data = Validators::set($form)
			->at("list_del_personCode", "ID"  )->required()->maxlength(12)->half()->dataExists(0, "select 1 from t_person where contractor_id = {$this->contractor_id} and person_code = {value}")
			->getValidatedData();

		if (Errors::isErrored()) return "person.tpl";
		
		$personId = DB::selectOne("select person_id from t_person where contractor_id = {$this->contractor_id} and person_code = {value}", $data["list_del_personCode"]);
		if (empty($personId)) response400();

		// 先に関連デバイスを取得しておく。
		$deviceIds = DB::selectOneArray("select device_id from t_device_person where person_id = {value}", $personId);
		
		// 関連付けられたデバイスが存在しているのであれば、デバイス登録へ。
		$query = Session::getData($form["list_del_back"]);
		$url = "./listSearch".$query;

		// エクスポートとカメラデータ移行・当て変え画面の検索タイプ保持
		$exp_src_key = isset($form["export_searchType"]) ? $form["export_searchType"] : "1";
		$trs_src_key = isset($form["trans_searchType"]) ? $form["trans_searchType"] : "1";
		
		if (empty($deviceIds)) {
			// カメラ登録無し(この場で削除)。
				
			try {
				// 変更前のユーザー情報
				$delPerson = [];
				// クラウドからデータを削除
				PersonService::deletePersonFromCloud($this->contractor, $data["list_del_personCode"],$delPerson);
				
				// ログ出力
				$loginfo = [
					"action_sub_type"=>0,
					"detail_json"=>json_encode([
						"ID"=>$data["list_del_personCode"],
						"氏名"=>$delPerson["person_name"],
						"生年月日"=>$delPerson["birthday"]
					],JSON_UNESCAPED_UNICODE)
				];
				$this->facetOperateLog($loginfo);
				
				$url .= "&export_searchType=".$exp_src_key."&trans_searchType=".$trs_src_key;
				sendCompleteRedirect($url, "クラウドサーバ上からユーザー情報の削除を行いました。");
				
			} catch (ApiParameterException $e) {
				// Errorsにメッセージ格納済み
				return "person.tpl";
			}
			
			
		} else {
			// カメラ登録有。
			$key = Session::setData(["personCode"=>$data["list_del_personCode"], "device_ids"=>$deviceIds]);
			$url .= "&delReg={$key}";
			$url .= "&export_searchType=".$exp_src_key."&trans_searchType=".$trs_src_key;
		}
		
		sendRedirect($url);
	}
		
	// クラウドサーバ上の人物を削除。
	public function delPersonFromCloudAction(&$form) {
		
		// 入力チェック。		
		$data = Validators::set($form)
			->at("list_del_personCode", "ID"  )->required()->maxlength(12)->half()->dataExists(0, "select 1 from t_person where contractor_id = {$this->contractor_id} and person_code = {value}")
			->getValidatedData();

		if (Errors::isErrored()) {
			echo json_encode(["result"=>"ERROR", "msg"=>join(" / ", Errors::getMessagesArray())]);
			return;
		}
		$delPerson = [];
		try {
			// クラウドからデータを削除
			PersonService::deletePersonFromCloud($this->contractor, $data["list_del_personCode"],$delPerson);
			
			// ログ出力
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"ID"=>$data["list_del_personCode"],
					"氏名"=>$delPerson["person_name"],
					"生年月日"=>$delPerson["birthday"]
				],JSON_UNESCAPED_UNICODE)
			];
			$this->facetOperateLog($loginfo);
			
			echo json_encode(["result"=>"OK"]);
		} catch (ApiParameterException $e) {
			// Errorsにメッセージ格納済み
			echo json_encode(["result"=>"ERROR", "msg"=>join(" / ", Errors::getMessagesArray())]);
		}
		
	}

	
	
	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- タブ：一括ユーザ登録
	// --------------------------------------------------------------------------------------------------------------------------- 
	
	
	// 初期表示のためのformへの値セット。
	public function indexBulk(&$form) {
	
		
	}
	
	// テンプレートをダウンロード。
	public function downloadBulkTemplateAction(&$form) {
		UiPersonService::downloadTemplate($this->contractor, $form["format"]);
	}

	// ファイルをアップロード。
	public function bulkFileUploadAction(&$form) {
		
		// アップロードファイルを取得。
		$fileInputName     = "bulkFile";
		$fileInputLabel    = "一括登録ファイル";
		$tmpDir            = DIR_TMP."/bulk_upload";
		$tmpDirPermission  = 0777;
		$tmpFilePermission = 0777;
		$tmpExpiration     = "-3 hour";				 	  // 3時間経過で自動削除。
		$tmpMaxSize        = 1024 * 1024 * 1024 * 6;	  // 6GB超過で自動削除。
		$exts              = ["zip"=>self::ZIP_LIMIT];	  // 500MBまで

		require_once(DIR_PROCEDURAL_PHP."/util/FileUploadProcessor.class.php");
		$uploadProcessor = new FileUploadProcessor();
		$uploadProcessor->settingUpload($fileInputName, true, $fileInputLabel, $exts);
		$uploadProcessor->settingMoveTmp($tmpDir, $tmpDirPermission, $tmpFilePermission, $tmpExpiration, $tmpMaxSize);
		$tmpFileName = $uploadProcessor->moveUploadFileToTmp();
		$zipPath = $tmpDir."/".$tmpFileName;

		if (Errors::isErrored()) return "./person.tpl";
		
		// この処理の進捗状況ファイルを作成。
		$progress = ["rowCount"=>100, "processed"=>"0"];		// ファイルを読み込まないと進捗分母が不明なので、完了は100とする。
		$progressFile = createTmpFile(json_encode($progress), "/upload-progress");

    // 入出力処理の排他確認
    $exclusivePath = createExclusivePath($this->contractor["contractor_id"]);
    if (file_exists($exclusivePath)) {
      Errors::add($fileInputName, "同じ契約で入出力処理がされています。処理が終了してから再度入出力をお試しください。");
    } else {
      // 同じ契約で出力処理がされていない場合はファイル作成してそのまま進行する
      $fp = fopen($exclusivePath, "w");
      fputs($fp, "ユーザーのインポート");
      fclose($fp);

			register_shutdown_function(function() use ($exclusivePath) {
				if (file_exists($exclusivePath)) unlink($exclusivePath);
			});
    }
		
		register_shutdown_function(function() use ($progressFile, $zipPath) {
			// zipファイルを削除
			if (file_exists($zipPath)) unlink($zipPath);
			// 進捗状況ファイルを削除。
			if (file_exists($progressFile)) unlink($progressFile);
		});

    if (Errors::isErrored()) return "./person.tpl";
		
		Session::set("bulk_uploadProgressFile", $progressFile);
		
		DB::commitAll();		// 最終アクセス日時のupdateをコミット。
		session_write_close();	// セッションの排他ロック解除。

		// 登録処理を行う。
		$ret = UiPersonService::processBulkFile(
				$this->contractor
				, $fileInputName
				, Session::getLoginUser("user_id")
				, $zipPath
				, $progress
				, $progressFile
				, $this->personTypes
				);
		
		if ($ret === false) return "./person.tpl";
		
		// 完了。
		// session_write_closeを行っているため、メッセージをセッションに書き込む事が出来ないため、別Actionを一度経由する。
		sendRedirect("./bulkUploadComplete?cnt=$ret");
	}
	
	// 完了リダイレクト用。
	public function bulkUploadCompleteAction(&$form) {
		$this->completeRedirect(formatNumber(Filter::digit($form["cnt"]))."件のユーザデータの登録を完了しました。", "&tab=bulk");
	}
	
	// アップロードの進捗状況を取得。
	public function bulkUploadCheckProgressAction(&$form) {
		
		$data = "";
		$uploadProgressFile = Session::get("bulk_uploadProgressFile");
		if ($uploadProgressFile != null && file_exists($uploadProgressFile)) {
			$data = file_get_contents($uploadProgressFile);
		}
		
		if (empty($data)) {
			$data = "null";
		}
		
		setJsonHeader();
		echo $data;
	}
	
	

		
	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- タブ：ユーザーデータのエクスポート
	// --------------------------------------------------------------------------------------------------------------------------- 
	// 初期表示のためのformへの値セット。
	public function indexExport(&$form) {

		$form["export_searchType"] = (!empty(Session::getLoginUser("group_id"))) ? 2 : 1;
		$form["export_group_ids"] = array_keys($this->groupsDisplay);
		$form["export_device_ids"] = array_keys($this->devices);
		
	}
	
	
	// 検索。
	public function exportSearchAction(&$form) {

		$filtedData = UiPersonService::getListSearchFilter($form, "export_", $this->devices, $this->groups)->getFilteredData();
		$data = $this->arrayExcludePrefix($filtedData, "export_");

		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = UiPersonService::getList($this->contractor, $this->devices, $this->groups, $data, $pageInfo);
		
		// 画像用のCookie作成。
		UiPersonService::setCloudFrontCookie($this->contractor_id, 60 * 10); // 10分有効
	
		if (empty($_REQUEST["export_pageNo"])) {
			
			// 選択されているキー。
			Session::clearData("export_checkIds");
			$form["export_checkIdsKey"] = Session::setData([], "export_checkIds");
			
			// 検索条件。
			Session::clearData("export_searchForm");
			$form["export_searchFormKey"] = Session::setData($filtedData, "export_searchForm");
			
		} 
	
		// 選択されたID。
		$checkIds = Session::getData($form["export_checkIdsKey"], false, "export_checkIds");
		
		$this->assign("checkIds", $checkIds);
		$this->assign("export_list", $list);
		$this->assign("export_pageInfo", $pageInfo);
		// add-start version3.0  founder feihan
		$this->assign("personTypeList", $this->personTypes);
		// add-end version3.0  founder feihan
		return "person.tpl";
	}
	
	// 全てチェック切り替え。
	public function exportCheckAction(&$form) {
		
		$data = Filters::ref($form)
			->at("isLocalOnly")->flag()
			->at("isCheckOn")->flag()
			->at("checkIds")->func(function($v) { return explode(",", $v); } )->digitArray()
			->at("export_searchFormKey")->len(3)
			->at("export_checkIdsKey")->len(3)
			->getFilteredData();

		// 選択されているIDリスト。
		$checkIds = Session::getData($data["export_checkIdsKey"], false, "export_checkIds");
		
		if (empty($checkIds)) {
			$checkIds = [];
		}
		
		if ($data["isLocalOnly"]) {
			// 画面に表示されている範囲のみを処理。
			
			foreach ($data["checkIds"] as $id) {
				if ($data["isCheckOn"]) {
					$checkIds[$id] = true;
				} else {
					unset($checkIds[$id]);
				}
			}
			
		} else {
			// 検索結果に対して処理。

			// 再検索。
			$filtedData = Session::getData($form["export_searchFormKey"], false, "export_searchForm");
			$searchData = $this->arrayExcludePrefix($filtedData, "export_");
			
			// 少しづつIDのみを検索。
			set_time_limit(60); 	// 無限ループ防止用。
			$cnt = 0;
			$pageNo = 1;			
			do {
				$pageInfo = new PageInfo($pageNo, 200);
				$idList = UiPersonService::getList($this->contractor, $this->devices, $this->groups, $searchData, $pageInfo, true);
				$pageNo++;
				if ($cnt++ >= 100000) trigger_error("無限ループ防止");
				
				foreach ($idList as $id) {
					$id = $id["person_id"];
					if ($data["isCheckOn"]) {
						$checkIds[$id] = true;
					} else {
						unset($checkIds[$id]);
					}
				}
					
			} while ($pageInfo->isEnableNextPage());
			
		}
		
		// 入れ替え。
		Session::replaceData($data["export_checkIdsKey"], $checkIds, "export_checkIds");
		
		setJsonHeader();
		echo json_encode(["export_checkExists"=>empty($checkIds) ? 0 : 1]);
	
	}
	
	// ダウンロード。
	public function exportDownloadAction(&$form) {
		$data = Filters::ref($form)
			->at("export_checkIdsKey")->len(3)
			->at("export_searchFormKey")->len(3)
			->at("export_format")->values(["csv", "excel"])
			->getFilteredData();
		
		// 選択されているIDリスト。
		$checkIds = Session::getData($data["export_checkIdsKey"], false, "export_checkIds");
		if (empty($checkIds)) $checkIds = [];

		// この処理の進捗状況ファイルを作成。
		$rowCount = count($checkIds) * 4;	// S3のダウンロードに時間が掛かるため、75%をデータ部、25%をzip部とする。
		$downloadProgress = ["rowCount"=>$rowCount, "processed"=>"0"];
    
    $downloadProgressFile = createTmpFile(json_encode($downloadProgress), "/download-progress");
    
    register_shutdown_function(function() use ($downloadProgressFile) {
      // 進捗状況ファイルを削除。
      if (file_exists($downloadProgressFile)) unlink($downloadProgressFile);
    });
    
    Session::set("export_downloadProgressFile", $downloadProgressFile);
    
    DB::commitAll();		// 最終アクセス日時のupdateをコミット。
    session_write_close();	// セッションの排他ロック解除。

    // 制限件数が入っている場合はエクスポート件数を制限する
    if (!empty($this->contractor["output_csv_image_limit"])) {
      $exportLimit = $this->contractor["output_csv_image_limit"];
      if($exportLimit < count($checkIds)) {
        Errors::add("", "出力件数が".$exportLimit."件を超えています。条件を指定して再度出力してください。");
      }
    }

    // 入出力処理の排他確認
    if (!Errors::isErrored()) {
      $exclusivePath = createExclusivePath($this->contractor["contractor_id"]);
      if (file_exists($exclusivePath)) {
        Errors::add("", "同じ契約で入出力処理がされています。処理が終了してから再度入出力をお試しください。");
      } else {
        // 同じ契約で出力処理がされていない場合はファイル作成してそのまま進行する
        $fp = fopen($exclusivePath, "w");
        fputs($fp, "ユーザーのエクスポート");
        fclose($fp);

				register_shutdown_function(function() use ($exclusivePath) {
					// 排他処理ファイルの削除
					if (file_exists($exclusivePath)) unlink($exclusivePath);
				});
      }
    }

    if (Errors::isErrored()) {
      // リストの再検索
      $filtedData = Session::getData($form["export_searchFormKey"], false, "export_searchForm");
      $data = $this->arrayExcludePrefix($filtedData, "export_");
      $pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
      $list = UiPersonService::getList($this->contractor, $this->devices, $this->groups, $data, $pageInfo);
      
      // 画像用のCookie作成。
      UiPersonService::setCloudFrontCookie($this->contractor_id, 60 * 10); // 10分有効

      // パラメーター補完
      $filtedData = UiPersonService::setDefaultSearchParams($filtedData, $form);
      $filtedData["export_checkIdsKey"] = $form["export_checkIdsKey"];
      $filtedData["export_format"] = $form["export_format"];
      $filtedData["export_searchFormKey"] = $form["export_searchFormKey"];
      $filtedData["tab"] = "export";
      $form = $filtedData;
      
      $this->assign("checkIds", $checkIds);
      $this->assign("export_list", $list);
      $this->assign("export_pageInfo", $pageInfo);
      $this->assign("personTypeList", $this->personTypes);

			return "person.tpl";
		}
    
    // ダウンロード実施。
    UiPersonService::downloadPersons($this->contractor, $checkIds, $data["export_format"], $downloadProgress, $downloadProgressFile, $this->devices, $this->groups);
		
	}
	
	// ダウロードの進捗状況を取得。
	public function exportDownloadCheckProgressAction(&$form) {
	
		$data = "null";
		$downloadProgressFile = Session::get("export_downloadProgressFile");
		if ($downloadProgressFile != null && file_exists($downloadProgressFile)) {
			$progressContent = file_get_contents($downloadProgressFile);
			// mod-start founder feihan
			if(!$progressContent){
				$data = json_encode(["fileReadContinue"=>"ture"]);
			}else{
				$data = $progressContent;
			}
			// mod-end founder feihan
		}
		
		setJsonHeader();
		echo $data;
	}
	
	// --------------------------------------------------------------------------------------------------------------------------- 
	// --------------------------------------------------------------------------------------------------------------------------- 
	// ------------------------------------------------- タブ：カメラデータ以降・当て変え
	// --------------------------------------------------------------------------------------------------------------------------- 
	// 初期表示のためのformへの値セット。
	public function indexTrans(&$form) {
		
		$form["trans_searchType"] = (!empty(Session::getLoginUser("group_id"))) ? 2 : 1;
		$form["trans_group_ids"] = array_keys($this->groupsDisplay);
		$form["trans_device_ids"] = array_keys($this->devices);
	}
	
	// 人物を検索。
	public function transSearchAction(&$form) {
		
		// 検索。
		$filtedData = UiPersonService::getListSearchFilter($form, "trans_", $this->devices, $this->groups)->getFilteredData();
		$data = $this->arrayExcludePrefix($filtedData, "trans_");

		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = UiPersonService::getList($this->contractor, $this->devices, $this->groups, $data, $pageInfo);
		
		// 画像用のCookie作成。
		UiPersonService::setCloudFrontCookie($this->contractor_id, 60 * 10); // 10分有効
		
		if (empty($_REQUEST["trans_pageNo"])) {
			
			// 選択されているキー。
			Session::clearData("trans_checkIds");
			$form["trans_checkIdsKey"] = Session::setData([], "trans_checkIds");
			
			// 検索条件。
			Session::clearData("trans_searchForm");
			$form["trans_searchFormKey"] = Session::setData($filtedData, "trans_searchForm");
			
		} 
	
		// 選択されたID。
		$checkIds = Session::getData($form["trans_checkIdsKey"], false, "trans_checkIds");
		
		$this->assign("trans_checkIds", $checkIds);
		$this->assign("trans_list", $list);
		$this->assign("trans_pageInfo", $pageInfo);
		// add-start version3.0  founder feihan
		$this->assign("personTypeList", $this->personTypes);
		$this->assign("enterExitModeFlag", $this->contractor["enter_exit_mode_flag"]);
		// add-end version3.0  founder feihan
		return "person.tpl";
	}
		
	// 全てチェック切り替え。
	public function transCheckAction(&$form) {
		
		$data = Filters::ref($form)
			->at("isLocalOnly")->flag()
			->at("isCheckOn")->flag()
			->at("checkIds")->func(function($v) { return explode(",", (isset($v) ? $v : "")); } )->digitArray()
			->at("trans_searchFormKey")->len(3)
			->at("trans_checkIdsKey")->len(3)
			->getFilteredData();

		// 選択されているIDリスト。
		$checkIds = Session::getData($data["trans_checkIdsKey"], false, "trans_checkIds");
		
		if (empty($checkIds)) {
			$checkIds = [];
		}
		
		if ($data["isLocalOnly"]) {
			// 画面に表示されている範囲のみを処理。
			
			foreach ($data["checkIds"] as $id) {
				if ($data["isCheckOn"]) {
					$checkIds[$id] = true;
				} else {
					unset($checkIds[$id]);
				}
			}
			
		} else {
			// 検索結果に対して処理。

			// 再検索。
			$filtedData = Session::getData($form["trans_searchFormKey"], false, "trans_searchForm");
			$searchData = $this->arrayExcludePrefix($filtedData, "trans_");
			
			// 少しづつIDのみを検索。
			set_time_limit(60); 	// 無限ループ防止用。
			$cnt = 0;
			$pageNo = 1;			
			do {
				$pageInfo = new PageInfo($pageNo, 200);
				$idList = UiPersonService::getList($this->contractor, $this->devices, $this->groups, $searchData, $pageInfo, true);
				$pageNo++;
				if ($cnt++ >= 100000) trigger_error("無限ループ防止");
				
				foreach ($idList as $id) {
					$id = $id["person_id"];
					if ($data["isCheckOn"]) {
						$checkIds[$id] = true;
					} else {
						unset($checkIds[$id]);
					}
				}
					
			} while ($pageInfo->isEnableNextPage());
			
		}
		
		// 入れ替え。
		Session::replaceData($data["trans_checkIdsKey"], $checkIds, "trans_checkIds");
		
		setJsonHeader();
		echo json_encode(["trans_checkCount"=>count($checkIds)]);
		
	}
	

	// デバイスの人物を全て削除。
	public function transClearDeviceAction(&$form) {
	
		$data = Validators::set($form)
			->at("device_id"		, "対象デバイス"		)->required()->required()->inArray(array_keys($this->devices))
			->getValidatedData();
		
		setJsonHeader();
		if (Errors::isErrored()) {
			echo json_encode(["error"=>join("/", Errors::getMessagesArray())]);
			return;
		}
		
		$device = $this->devices[$data["device_id"]];
		try {
			PersonService::clearPersonFromDevice($device);
			echo json_encode(["result"=>"OK"]);

			// ログ出力
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"削除対象シリアル"=>$device["serial_no"]
				],JSON_UNESCAPED_UNICODE)
			];
			$this->facetOperateLog($loginfo);

			return;
			
		} catch (ApiParameterException $e) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
			
		} catch (DeviceExclusionException $e) {
			echo json_encode(["error"=>$e->getMessage()]);
			return;
			
		} catch (SystemException $e) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
			
				
		} catch (DeviceWsException $e) {
			echo json_encode(["error"=>$e->getMessage()]);
			return;
				
		}
		
	}
	
	// デバイスへ登録
	public function transPersonToDeviceAction(&$form) {
		
		$data = Validators::set($form)
			->at("trans_checkIdsKey", "対象キー"			)->required()->fixlength(3)->alphaNum()
			->at("personIdx"		, "対象インデックス"	)->required()->digit()
			->getValidatedData();
		
		setJsonHeader();
		if (Errors::isErrored()) {
			echo json_encode(["error"=>join("/", Errors::getMessagesArray())]);
			return;
		}
		
		// 選択されたID。
		$checkIds = Session::getData($data["trans_checkIdsKey"], false, "trans_checkIds");
		
		// 対象のデータ。
		$ids = array_keys($checkIds);
		$personId = arr($ids, $data["personIdx"]);
		
		$personCode = DB::selectOne("select person_code from t_person where person_id = {person_id} and contractor_id = {contractor_id}", ["person_id"=>$personId, "contractor_id"=>$this->contractor_id]);
		
		// 登録。
		$form["type"] = "trans";
		$form["person_code"] = $personCode;

		$this->action = "registPersonForDeviceAction";
		
		$this->registPersonForDeviceAction($form);
	}
	
	
	// 人物の個別当てかえの開始。
	public function modTransInitAction(&$form, $isInlucde = false) {
		
		// 一覧の再検索。
		$this->transSearchAction($form, true);
		
		// 対象データの取得。
		$data = Filters::ref($form)
			->at("trans_modPersonId")->digit()
			->getFilteredData();
			
		if (!empty($data["trans_modPersonId"])) {
			$modPerson = UiPersonService::get($this->contractor, $this->devices, $this->groups, $data["trans_modPersonId"]);
			
			if (!empty($modPerson)) {

				// 完了後に戻るためのパラメータ。
				if (!$isInlucde) {
 					$form["trans_mod_back"] = Session::setData(createQueryStringExcludePulldown($form, ["trans_modPersonId"]));
				}
				
				foreach ($modPerson as $k=>$v) {
					$form["trans_mod_".$k] = $v;
				}
				$form["trans_mod_new_device_ids"] = $form["trans_mod_registed_device_ids"] = explode(",", $modPerson["device_ids"]);
				
				$this->assign("trans_modPerson", true);
			}
		}
		
		return "person.tpl";
	}
	
	// 人物の個別当て変えの実行。
	public function modTransAction(&$form) {
		
		// 一覧の再検索。
		$this->transSearchAction($form, true);		
		
		// 対象データの取得。
		$data = Filters::ref($form)
			->at("trans_modPersonId")->digit()
			->at("trans_mod_deleteDevices")->digitArray()
			->at("trans_mod_addDevices")->digitArray()
			->getFilteredData();
			
		if (!empty($data["trans_modPersonId"]) && (!empty($data["trans_mod_deleteDevices"]) || !empty($data["trans_mod_addDevices"]))) {
			$modPerson = UiPersonService::get($this->contractor, $this->devices, $this->groups, $data["trans_modPersonId"]);
			
			// デバイスへの登録へ。
			if (!empty($modPerson)) {

				$deleteDeviceTargets = [];
				foreach ($data["trans_mod_deleteDevices"] as $id) $deleteDeviceTargets[] = ["id"=>$id, "name"=>$this->devices[$id]["name"]];
				
				$addDeviceTargets = [];
				foreach ($data["trans_mod_addDevices"] as $id) $addDeviceTargets[] = ["id"=>$id, "name"=>$this->devices[$id]["name"]];
				
				$this->assign("transMod_deleteDeviceTargets"   , $deleteDeviceTargets);
				$this->assign("transMod_addDeviceTargets"      , $addDeviceTargets);
				$this->assign("transMod_registDevicePersonCode", $modPerson["personCode"]);
			}
			
		}
		
		return "person.tpl";
	}
	
	// 人物の個別当て変えの完了。
	public function transModCompleteAction(&$form) {
		
		$query = Session::getData($form["trans_mod_back"]);
		$url = "./transSearch".$query;
		
		sendRedirect($url);
	}
	
	// add-start founder yaozhengbang
	private function gettopMenuFlag() {
		$funcName =	Session::getUserFunctionAccess("function_name");
		$flag[0] = (empty(Session::getLoginUser("group_id")) || Session::getLoginUser("user_flag") == 1);
		$flag[1] = (array_search("ユーザー情報一覧・変更" , $funcName)>-1) || (Session::getLoginUser("user_flag") == 1);
		$flag[2] = (empty(Session::getLoginUser("group_id")) || Session::getLoginUser("user_flag") == 1);
		$flag[3] = (array_search("ユーザーデータのエクスポート" , $funcName)>-1) || (Session::getLoginUser("user_flag") == 1);
		$flag[4] = (empty(Session::getLoginUser("group_id")) || Session::getLoginUser("user_flag") == 1);
		if(Session::getLoginUser("user_flag") != 1){
			$flag[0]?$flag[0] = (array_search("新規ユーザー登録" , $funcName)>-1):"";
			$flag[2]?$flag[2] = (array_search("一括ユーザー登録" , $funcName)>-1):"";
			$flag[4]?$flag[4] = (array_search("カメラデータ移行・当て変え" , $funcName)>-1):"";
		}
		return $flag;
	}
	// add-end founder yaozhengbang
}
