<?php

class LogController extends UserBaseController {
	
	public $devices;

	public $groups;
	
	// add-start founder feihan
	public $groupsDisplay;
	public $contractor;
	// add-start founder feihan
	
	public $recogPassFlags;
	
	public $personTypes;
	
	public $enterExitTypes;
	
	// @Override
	public function prepare(&$form) {
		
		// sessionからプルダウンを取得
		parent::prepare($form);
		
		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);
		
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
		
		// 勤怠区分情報 (キーはPassFlag)
		$this->recogPassFlags = UiRecogLogService::getPassFlags($this->contractor_id);
		
		// 契約者情報を取得。
		$this->contractor = DB::selectRow("select * from m_contractor where contractor_id = {value}", $this->contractor_id);
		
		// ユーザー区分情報を取得。
		$this->personTypes = DB::selectKeyValue("select * from m_person_type where contractor_id = {value}", $this->contractor_id, "person_type_code", "person_type_name");
		
		$this->enterExitTypes = ["1"=>"入室", "2"=>"退室", "9"=>"エラー"];
	}
	
	// トップ
	public function indexAction(&$form) {

		if (empty($form["log_searchType"])) $form["log_searchType"] = 1;

		if (empty($form["searchInit"])) {
			$form["date_from"] = date("Y/m/d H:i", strtotime("now -1 month"));
			$form["date_to"] = date("Y/m/d H:i", strtotime("now"));
		}
		$data = $this->filterData($form);

		// 検索。
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
		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);

		// 詳細情報含む場合は通常検索、含まない場合は期間とデバイス条件のみ
		if ($form["log_searchType"] == 2) {
			$list = UiRecogLogService::getRecogLogList($pageInfo, $data, $this->devices, $this->groups ,false,$this->recogPassFlags);
		} else {
			$list = UiRecogLogService::getRecogLogList($pageInfo, $data, $this->devices, $this->groups ,false,$this->recogPassFlags, false);
		}

		// 画像参照用のCookieを作成し、設定。
		UiRecogLogService::setCloudFrontCookie($this->contractor_id);

		// add-start founder feihan
		// 検索条件はSessionに保存する。
		Session::clearData("export_searchFormKey");
		$data["csvCount"] = $pageInfo->getRowCount();
		$form["export_searchFormKey"] = Session::setData($data,"export_searchFormKey");
		// add-end founder feihan

		$this->assign("pagerLimit", Enums::pagerLimit());
		$this->assign("pageInfo", $pageInfo);
		$this->assign("list", $list);
		
		// ログ出力
		if ($form["log_searchType"] == 2) {
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"カメラグループ"=>$form["group_ids"],
					"カメラ"=>$form["device_ids"],
					"選択期間"=>$form["date_from"]."〜".$form["date_to"],
					"ID"=>$form["personCode"],
					"氏名"=>$form["personName"],
					"ICカード番号"=>$form["cardID"],
					"マスク"=>$form["mask_type"],
					"PASS"=>$form["pass_type"],
					"登録者"=>$form["guest_type"],
					"温度"=>$form["temperature_from"]."〜".$form["temperature_to"],
					"勤怠区分"=>$form["pass_flags"],
					"SCORE"=>$form["score_from"]."〜".$form["score_to"]
					],JSON_UNESCAPED_UNICODE )
			];
		} else {
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>json_encode([
					"カメラグループ"=>$form["group_ids"],
					"カメラ"=>$form["device_ids"],
					"選択期間"=>$form["date_from"]."〜".$form["date_to"]
					],JSON_UNESCAPED_UNICODE )
			];
		}
		$this->facetOperateLog($loginfo);

		return "log.tpl";
	}
	
	// add-start founder feihan
	// ダウンロード。
	public function exportDownloadAction(&$form){

		$data = Filters::ref($form)
			->at("csvImgType")->digit(0, 1)
			->at("export_searchFormKey")->len(3)
			->getFilteredData();

		// 再検索。
		$searchForm = Session::getData($data["export_searchFormKey"], false, "export_searchFormKey");
		$recogLogIds = $searchForm["csvCount"];

		if (empty($recogLogIds)) $recogLogIds = [];
		
		// add-start version3.0 founder feihan
		if($data["csvImgType"]==1 && !empty($this->contractor["output_csv_image_limit"])){
			$imageLimt = $this->contractor["output_csv_image_limit"];
			if($imageLimt < $searchForm["csvCount"]){
				Errors::add("", "出力件数が".$imageLimt."件を超えています。条件を指定して再度出力してください。");
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
        fputs($fp, "ログのエクスポート");
        fclose($fp);

				register_shutdown_function(function() use ($exclusivePath) {
					if (file_exists($exclusivePath)) unlink($exclusivePath);
				});
      }
    }

		if (Errors::isErrored()) {
			// 画面項目保持
			$form = $searchForm;
			$pageInfo = new PageInfo($form["pageNo"], $form["limit"]);
			$list = UiRecogLogService::getRecogLogList($pageInfo, $form, $this->devices, $this->groups ,false,$this->recogPassFlags);
			
			// 画像参照用のCookieを作成し、設定。
			UiRecogLogService::setCloudFrontCookie($this->contractor_id);
			
			// 検索条件はSessionに保存する。
			Session::clearData("export_searchFormKey");
			$form["export_searchFormKey"] = Session::setData($form,"export_searchFormKey");
			
			$this->assign("pagerLimit", Enums::pagerLimit());
			$this->assign("pageInfo", $pageInfo);
			$this->assign("list", $list);
			return "log.tpl";
		}
		// add-end version3.0 founder feihan
		
		// この処理の進捗状況ファイルを作成。
		$rowCount = $recogLogIds * 4;
		$downloadProgress = ["rowCount"=>$rowCount, "processed"=>"0"];
		$downloadProgressFile = createTmpFile(json_encode($downloadProgress), "/download-progress");

		register_shutdown_function(function() use ($downloadProgressFile) {
			// 進捗状況ファイルを削除。
			if (file_exists($downloadProgressFile)) unlink($downloadProgressFile);
		});

		Session::set("export_downloadProgressFile", $downloadProgressFile);

		DB::commitAll();		// 最終アクセス日時のupdateをコミット。
		session_write_close();	// セッションの排他ロック解除。

		// ダウンロード実施。
		UiRecogLogService::downloadLogs($searchForm, $downloadProgress, $downloadProgressFile, $this->devices, $this->groups, $this->contractor, $this->recogPassFlags, $data["csvImgType"]);

		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"件数"=>$searchForm["csvCount"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
	}

	// ダウロードの進捗状況を取得。
	public function exportDownloadCheckProgressAction(&$form) {
		
		$data = "null";
		$downloadProgressFile = Session::get("export_downloadProgressFile");
		if ($downloadProgressFile != null && file_exists($downloadProgressFile)) {
			$progressContent = file_get_contents($downloadProgressFile);
			if(!$progressContent){
				$data = json_encode(["fileReadContinue"=>"ture"]);
			}else{
				$data = $progressContent;
			}
		}
		
		setJsonHeader();
		echo $data;
	}
	// add-end founder feihan
	
	// 検索用データフィルター。
	private function filterData(&$form) {
		
		$filtered = Filters::ref($form)
			// add-start founder feihan
			->at("searchInit")->flag()
			// add-end founder feihan
			->at("view", 1)->digit(1, 2)
			->at("limit", 20)->enum(Enums::pagerLimit())
			->at("pageNo")->digit()
			// mod-start founder feihan
			// 未登録 ＝ -1
			->at("group_ids")->enumArray($this->groupsDisplay)
			// mod-end founder feihan
			->at("device_ids")->enumArray($this->devices)
			->at("date_from")->datetime()
			->at("date_to")->datetime();
		
		if ($this->contractor["enter_exit_mode_flag"] == 1) {
			// 入退モードの場合、入退室状態・検索時在館者フラグ・認識ユーザーの区分もフィルター対象
			$filtered = $filtered
				->at("personType")->enum($this->personTypes)
				->at("presentFlag")->flag()
				->at("enterExitType")->enum($this->enterExitTypes)
				->at("personDescription1")->len(100)
				->at("personDescription2")->len(100);
		}
		
		return $filtered
      ->at("log_searchType", "1")->values(["1", "2"])
			// add-start founder feihan
			->at("personCode")->len(100)
			->at("personName")->len(100)
			->at("cardID")->len(20)
			// add-end founder feihan
			->at("pass_type", "all")->values(["all", "yes", "no"])
			->at("mask_type", "all")->values(["all", "yes", "no"])
			->at("guest_type", "all")->values(["all", "yes", "no"])
			// add-start founder feihan
			->at("noTempOnly")->flag()
			->at("temperature_from")->cDigit()
			->at("temperature_to")->cDigit()
			->at("score_from")->cDigit()
			->at("score_to")->cDigit()
			// add-end founder feihan
			->at("pass_flags")->enumArray($this->recogPassFlags)
			->getFilteredData();
	}
}
