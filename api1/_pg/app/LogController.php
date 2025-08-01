<?php 

class LogController extends ApiBaseController {
	
	// 認識ログを取得。
	public function getRecogLogAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("latest" 		 , "latest"  		)->flag()
			->at("recogTimeFrom" , "recogTimeFrom"  )->dateTime()
			->at("recogTimeTo"   , "recogTimeTo"    )->dateTime()
			->at("personCode"    , "personCode"     )->maxlength(12)
			->at("personDescription1", "personDescription1")->maxlength(30)
			->at("personDescription2", "personDescription2")->maxlength(30)
			->at("pageNo"        , "pageNo"         )->digit(1)
			->at("pictureExpires", "pictureExpires" )->digit(10, 300)
			->at("pictureAllowIp", "pictureAllowIp" )->maxlength(100)
			->getValidatedData();
		
		// 検索条件
		$where = "";
		$data["device_id"] = $device["device_id"];
		
		$data["contractor_id"] = $this->contractor["contractor_id"];
		
		// 最新取得が指定されている場合には、検索条件は無視する。
		if ($data["latest"] != 1) {
			if ($data["recogTimeFrom"]) $where .= " and recog_time >= {recogTimeFrom}";
			if ($data["recogTimeTo"])   $where .= " and recog_time <= {recogTimeTo}";
			if ($data["personCode"])    {
			
				if (mb_strlen($data["personCode"]) < 4 && Filter::digit($data["personCode"]) != null) {
					$data["personCode_int"] = intval($data["personCode"]);
					$where .= " and (person_code = {personCode} or person_code = {personCode_int}) ";
				} else {
					$where .= " and person_code = {personCode}";
				}
				
			}

			if ($this->contractor["enter_exit_mode_flag"] == 1) {
				if (($data["personDescription1"] === "0") || !empty($data["personDescription1"])) $where .= "and person_description1 like {like_LR personDescription1}";
				if (($data["personDescription2"] === "0") || !empty($data["personDescription2"])) $where .= "and person_description2 like {like_LR personDescription2}";
			}
			
			$pageInfo = new PageInfo($data["pageNo"], 100);
		}
		
		$sql = "
			select 
				trl.device_recog_log_id
				, trl.recog_time
				, trl.person_code
				, trl.person_name
				, trl.access_type
				, trl.mask
				, trl.temperature
				, trl.card_bind_person_code
				, trl.card_bind_person_name
				, trl.card_no
				, trl.card_type
				, trl.s3_object_path
				, trl.pass
				, trl.pass_flag
 				, trl.search_score
				, trl.person_description1
				, trl.person_description2
				, mrpf.flag_name
			from
				t_recog_log trl
				left join m_recog_pass_flag mrpf
				on mrpf.contractor_id = {contractor_id}
				and mrpf.pass_flag = trl.pass_flag

			where
				trl.device_id = {device_id}
				$where
		"; 
		$order = "
			order by
				recog_time desc
				, recog_log_id desc		
		";
		
		if ($data["latest"] != 1) {
			$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);
		} else {
			$list = DB::selectArray($sql.$order." limit 1", $data);
		}
		
		// 画像用のCookie作成。
		$pictureCookie = AwsService::createS3SignedCookie($device["s3_path_prefix"]."/recog/*", $data["pictureExpires"], $data["pictureAllowIp"]);
		
		$ret = [];
		$retList = [];
		foreach ($list as $index => $item) {
			$retList[] = RecogLogService::convertApiFormat($device, $item);

			// 入退管理モードの場合は備考も入れる
			if ($this->contractor["enter_exit_mode_flag"] == 1) {
				$retList[$index]["person_description1"] = $item["person_description1"];
				$retList[$index]["person_description2"] = $item["person_description2"];
			}
		}

		if ($data["latest"] != 1) {
			$ret["rows"]  = $pageInfo->getRowCount();
			$ret["pages"] = $pageInfo->getPageCount();
		}
		
		$ret["pictureCookie"] = [];
		foreach ($pictureCookie as $k=>$v) {
			$ret["pictureCookie"][$k] = $v;
		}
		
		$ret["list"] = $retList;
	
		// ブラウザからのアクセスの場合、set-cookieを行う。
		if ($this->isJsonp) {
			foreach ($pictureCookie as $k=>$v) {
	
				setcookie($k, $v, [
					"path" => "/",
	                "domain" => CLOUDFRONT_COOKIE_DOMAIN, 
	                "secure" => ENABLE_SSL,     
	                "httponly" => true,   
	                "samesite" => ENABLE_SSL ? "None" : ""
				]);
			
			}
		}
			
		$this->responseJson($ret);
	}
	
	
	// ログの削除。
	public function deleteRecogLogAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("ids" 		 	 , "ids"  	    	)->exlopdeArrayValue(",")->digit()->arrayMaxCount(100)
			->at("recogTimeFrom" , "recogTimeFrom"  )->dateTime()
			->at("recogTimeTo"   , "recogTimeTo"    )->dateTime()
			->at("personCode"    , "personCode"     )->maxlength(12)
			->getValidatedData();
		
		$data["device_id"] = $device["device_id"];
		
		$where = "";
		if (!empty($data["ids"]))   $where .= " and device_recog_log_id in {in ids}";
		if ($data["recogTimeFrom"]) $where .= " and recog_time >= {recogTimeFrom}";
		if ($data["recogTimeTo"])   $where .= " and recog_time <= {recogTimeTo}";
		if ($data["personCode"])    $where .= " and person_code = {personCode}";
	
		// どれか一つの検索条件は必須。
		if (empty($where)) {
			throw new ApiParameterException("condition", "削除対象ログの検索条件を一つ以上、指定して下さい。");
		}
		
		// 接続状態の事前確認。
		DeviceService::checkDevice($device);
		
		// 排他チェック。
		SyncService::checkExclusion($device["device_id"]);
		
		// 開始ログを登録する。
		SyncService::insertBeginLog($device["device_id"], basename(__FILE__));
		
		// まずは全体件数を取得する。
		$sql = "select count(*) from t_recog_log where device_id = {device_id} $where "; 
		$total = (int) DB::selectOne($sql, $data);
		
		// 削除対象の情報を最大で100件取得する。
		$sql = "select recog_log_id, device_recog_log_id, recog_time from t_recog_log where device_id = {device_id} $where order by recog_log_id asc limit 100";
		$list = DB::selectArray($sql, $data);
		$deleted = 0;
		$errorMsg = null;
		foreach ($list as $item) {
			
			set_time_limit(60);	// 制限時間延長。
				
			$sec = strtotime($item["recog_time"]);
			
			// 端末のデータを削除する。
			try {
				RecogLogService::deleteAccessRecord($device, $sec, $sec);
			} catch (DeviceWsException $e) {
				$errorMsg = "ログID[{$item["device_recog_log_id"]}]について、デバイスとの通信異常により削除に失敗し、処理を中断しました。";
				break;
			}
			
			// S3のファイルを削除する。
			if (!AwsService::deleteS3RecogPicture($device, $item["recog_log_id"], $item["recog_time"])) {
				$errorMsg = "ログID[{$item["device_recog_log_id"]}]について、クラウド上の画像ファイルの削除が正常に行われなかったため、処理を中断しました。";
				break;
			}
			
			// DBのデータを物理削除する。
			DB::delete("delete from t_recog_log where recog_log_id = {recog_log_id}", $item);
			
			// 一件ごとにコミットする。
			DB::commit();
			
			$deleted++;
		}
		
		// 結果情報。
		$ret = [];
		$ret["total"] = $total;	
		$ret["deleted"] = $deleted;
		
		if (empty($errorMsg)) {
			$ret["result"] = true;
		} else {
			$ret["result"] = false;
			$ret["message "] = $errorMsg;
		}
		
		
		// 終了ログを登録する。
		$logState = 20;	// 成功。
		if (!empty($errorMsg)) {
			if ($deleted == 0) {
				$logState = 30;		// 30: 継続出来ないエラーが発生し、中断した。データは一切登録されていない。
			} else {
				$logState = 40;		// 40: 継続出来ないエラーが発生し、中断した。一部のデータは登録されている。
			}
		}
		SyncService::updateEndLog($logState, json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		// 結果をレスポンス。
		$this->responseJson($ret);
	}
	
	
	// 操作ログを取得。
	public function getOperateLogAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
	
		$data = Validators::set($form)
			->at("operateTimeFrom" , "operateTimeFrom")->dateTime()
			->at("operateTimeTo"   , "operateTimeTo"  )->dateTime()
			->at("operateUser"     , "operateUser"    )->maxlength(100)
			->at("mainType"		   , "mainType" 	  )->maxlength(100)
			->at("subType"		   , "subType" 		  )->maxlength(100)
			->at("pageNo"          , "pageNo"         )->digit(1)
			->getValidatedData();
		
		// 検索条件
		$where = "";
		$data["device_id"] = $device["device_id"];
		
		if ($data["operateTimeFrom"]) $where .= " and operate_time >= {operateTimeFrom}";
		if ($data["operateTimeTo"])   $where .= " and operate_time <= {operateTimeTo}";
		if ($data["operateUser"])     $where .= " and operate_user = {operateUser}";
		if ($data["mainType"])        $where .= " and main_type = {mainType}";
		if ($data["subType"])         $where .= " and sub_type = {subType}";
		
		$pageInfo = new PageInfo($data["pageNo"], 100);
		
		$sql = "
			select 
				operate_time
				, operate_user
				, main_type
				, sub_type
				, detail_json
			from
				t_operate_log

			where
				device_id = {device_id}
				$where
		"; 
		$order = "
			order by
				operate_time desc
				, operate_log_id desc		
		";
		
	
		$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);
		
		$retList = [];
		foreach ($list as $item) {
			$retList[] = OperateLogService::convertApiFormat($item);
		}
		
		$ret = [];
		$ret["rows"]  = $pageInfo->getRowCount();
		$ret["pages"] = $pageInfo->getPageCount();
		$ret["list"] = $retList;
		
		$this->responseJson($ret);
	}
	
	
	
}
