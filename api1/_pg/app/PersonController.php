<?php 

class PersonController extends ApiBaseController {
	
	// 人物をクラウドサーバに登録。
	public function registPersonForCloudAction(&$form) {
		
		$data = Validators::set($form)
			->at("personCode" 	   , "personCode"  		)->required()->maxlength(12)
			->at("personName" 	   , "personName"	 	)->maxlength(32)
			->at("sex" 	           , "sex"		 		)->inArray(["male", "female"])
			->at("birthday" 	   , "birthday"	 		)->date()			
			->at("memo" 	 	   , "memo"	     		)->maxlength(500)
			->at("picture" 	 	   , "picture"	 		)->byteMaxlength(1024 * 1024  * 5)	// Base64後で5MB
			->at("recogLogSerialNo", "recogLogSerialNo"	)->inArray($this->serialNos)
			->at("recogLogId" 	   , "recogLogId"	 	)->digit(1, 90000000000);
		
		if ($this->contractor["enter_exit_mode_flag"]) {
			$data = $data->at("personTypeCode"    , "personTypeCode"   )->digit(1)->dataExists(false, "select 1 from m_person_type where contractor_id = {$this->contractor['contractor_id']} and person_type_code = {value}")
						->at("personDescription1" , "personDescription1")->maxlength(30)
						->at("personDescription2" , "personDescription2")->maxlength(30);
			$data = $data->getValidatedData();
			$data["person_type_code"]    = $data['personTypeCode'];
			$data["person_description1"] = $data['personDescription1'];
			$data["person_description2"] = $data['personDescription2'];
		} else {
			$data = $data->getValidatedData();
			$data["person_type_code"]    = NULL;
			$data["person_description1"] = NULL;
			$data["person_description2"] = NULL;
		}
		
		// 登録。
		if (!PersonService::registPerson($this->contractor, $data)) {
			$this->responseError();
		}
		
		$this->responseJson(["result"=>true]);
	}
	
	// 人物を検索。
	public function getPersonFromCloudAction(&$form) {
		
		$data = Validators::set($form)
			->at("personCode" 	 , "personCode"     )->maxlength(12)
			->at("personName" 	 , "personName"	    )->maxlength(32)
			->at("personTypeCode", "personTypeCode"	)->digit(1)
			->at("personDescription1", "personDescription1")->maxlength(30)
			->at("personDescription2", "personDescription2")->maxlength(30)
			->at("registSerialNo", "registSerialNo"	)->inArray($this->serialNos)
			->at("pageNo"        , "pageNo"         )->digit(1)
			->at("pictureExpires", "pictureExpires" )->digit(10, 300)
			->at("pictureAllowIp", "pictureAllowIp" )->maxlength(100)
			->getValidatedData();
			
		// 検索条件
		$where = "";
		$data["contractor_id"] = $this->contractor["contractor_id"];
		PersonService::createLikeParam($data, "personCode", $where, "p.person_code");
		PersonService::createLikeParam($data, "personName", $where, "p.person_name");
		
		if ($data["registSerialNo"]) {
			$where .= " and d.serial_no = {registSerialNo}";
		}
		
		if ($this->contractor["enter_exit_mode_flag"]) {
			if (!empty($data["personTypeCode"])) $where .= " and p.person_type_code = {personTypeCode}";
			$personTypeC = ", p.person_type_code";

			// 備考
			if (($data["personDescription1"] === "0") || !empty($data["personDescription1"])) PersonService::createLikeParam($data, "personDescription1", $where, "p.person_description1");
			if (($data["personDescription2"] === "0") || !empty($data["personDescription2"])) PersonService::createLikeParam($data, "personDescription2", $where, "p.person_description2");
			$personDescription = ", p.person_description1, p.person_description2";

		} else {
			$personTypeC = "";

			// 備考
			$personDescription = "";
		}
		
		$pageInfo = new PageInfo($data["pageNo"], 100);
		
		$sql = "
			select 
				p.person_id
				, p.contractor_id
				, p.create_time
				, p.person_code
				, p.person_name
				, p.sex
				, p.birthday
				, p.memo
				, p.s3_object_path
				$personTypeC
				$personDescription
				, group_concat(d.serial_no) as serial_nos 
			from
				t_person p

				left outer join t_device_person dp on
				p.person_id = dp.person_id

				left outer join m_device d on
				dp.device_id = d.device_id

			where
				p.contractor_id = {contractor_id}
				$where

			group by 
				p.person_id
				, p.contractor_id
				, p.create_time
				, p.person_code
				, p.person_name
				, p.sex
				, p.birthday
				, p.memo
				, p.s3_object_path				
				$personTypeC
				$personDescription

		"; 
		$order = "
			order by
				person_id desc 
		";
		
		$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);
		
		// 画像用のCookie作成。
		$pictureCookie = AwsService::createS3SignedCookie($this->contractor["s3_path_prefix"]."/*", $data["pictureExpires"], $data["pictureAllowIp"]);
		
		$ret = [];
		$retList = [];
		foreach ($list as $item) {
			// カードID
			$item["card_ids"] = DB::selectOne("select group_concat(card_id SEPARATOR '/') from t_person_card_info where person_id = {value}", $item["person_id"]);

			$formatted = PersonService::convertApiFormat($this->contractor, $item);
			$formatted["serialNos"] = [];
			if ($this->contractor["enter_exit_mode_flag"]) {
				// 区分
				$personTypeParam = [];
				$personTypeParam["person_type_code"] = $item["person_type_code"];
				$personTypeParam["contractor_id"]    = $this->contractor["contractor_id"];
				$formatted["personTypeName"] = DB::selectOne("select person_type_name from m_person_type where contractor_id = {contractor_id} and person_type_code = {person_type_code}", $personTypeParam);

				// 備考
				$formatted["personDescription1"] = $item["person_description1"];
				$formatted["personDescription2"] = $item["person_description2"];
			}
			if (!is_null($item["serial_nos"])) {
				foreach (explode(",", $item["serial_nos"]) as $s) {
					if (!empty($s)) $formatted["serialNos"][] = $s;
				}
			}
			$retList[] = $formatted;
		}

		$ret["rows"]  = $pageInfo->getRowCount();
		$ret["pages"] = $pageInfo->getPageCount();
		$ret["pictureCookie"] = [];
		foreach ($pictureCookie as $k=>$v) $ret["pictureCookie"][$k] = $v;
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
		
	// 人物をデバイスに登録。
	public function toDeviceAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		// 2022年2月21日 削除から登録の処理を追加するためにoverrideのバリデーションをflagからdigitへ変更
		$data = Validators::set($form)
			->at("personCode" , "personCode" )->required()->maxlength(12)
			->at("override"   , "override"   )->digit(0,2)
			->at("noImage"    , "noImage"   )->flag()
			->getValidatedData();
			
		$ret = PersonService::toDevice($this->contractor, $device, $data["personCode"], $data["override"], $data["noImage"]);
		
		$this->responseJson($ret);
	}
	
	
	// 一件の人物をクラウドサーバから削除。
	public function deletePersonFromCloudAction(&$form) {
		
		$data = Validators::set($form)
			->at("personCode" , "personCode" )->required()->maxlength(12)
			->getValidatedData();
			
		$ret = PersonService::deletePersonFromCloud($this->contractor, $data["personCode"]);
		
		$this->responseJson([
			"result"=>$ret
		]);
	}
	
	// 一件の人物をデバイスから削除。
	public function deletePersonFromDeviceAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("personCode" , "personCode" )->required()->maxlength(12)
			->getValidatedData();
			
		$ret = PersonService::deletePersonFromDevice($device, $data["personCode"]);
		
		$this->responseJson($ret);
	}

	// 全件の人物をデバイスから削除。
	public function clearPersonFromDeviceAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$ret = PersonService::clearPersonFromDevice($device);
		
		$this->responseJson($ret);		
	}
	

	// 人物をクラウドに登録。
	public function toCloudAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("personCode" , "personCode" )->required()->maxlength(12)
			->at("override"   , "override"   )->flag()
			->getValidatedData();
		
		$ret = PersonService::toCloud($this->contractor, $device, $data["personCode"], $data["override"]);
		
		$this->responseJson($ret);
	}
	

	// 人物をクラウドから取得。
	public function getPersonAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("personCode"     , "personCode" 	 )->required()->maxlength(12)
			->at("includePicture" , "includePicture" )->flag()
			->getValidatedData();
			
		$ret = PersonService::getPerson($this->contractor, $device, $data["personCode"], empty($data["includePicture"]) ? false : true);
		
		$this->responseJson($ret);
	}
	

	// デバイスに登録された人物データの検索。
	public function getPersonFromDeviceAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("personCode" , "personCode" )->maxlength(12)
			->at("personName" , "personName" )->maxlength(32)
			->at("pageNo"     , "pageNo"     )->digit(1)
		->getValidatedData();
		
		// AIカメラと分岐
		if (($device['device_type'] === "AIカメラ") || (mb_substr(explode('-', $device['device_type'])[1], 0, 1) == 8)) {
			$ret = PersonService::getPersonListFromAiDevice($this->contractor, $device, $data);
		} else {
			$ret = PersonService::getPersonListFromDevice($this->contractor, $device, $data);
		}
		
		$this->responseJson($ret);
	}
	
	// 人物画像チェック。
	public function checkPersonPictureAction(&$form) {
		
		$data = Validators::set($form)
			->at("picture", "picture"	 		)->byteMaxlength(1024 * 1024  * 5)	// base64後で5MBまで
		->getValidatedData();
		
		$ret = PersonService::checkPersonPicture($data["picture"]);
		
		$this->responseJson($ret);
	}

	// 人物とデバイスの関連付けを削除する。
	public function deletePersonAssociationAction(&$form) {
		
		$data = Validators::set($form)
			->at("personCode", "personCode" )->required()->maxlength(12)
			->at("serialNo"  , "serialNo"   )->inArray($this->serialNos)
			->getValidatedData();
		
		$device = false;
		if (!empty($data["serialNo"])) {
			$device = $this->contractor["deviceList"][$data["serialNo"]];
		}
			
		$deleted = PersonService::deletePersonAssociation($this->contractor, $data["personCode"], $device);
		
		$this->responseJson(["deleted"=>$deleted]);
	}

	
	// クラウドサーバへ人物の通行可能時間帯を登録。
	public function registPersonAcessTimeForCloudAction(&$form) {
		
 		$data = Validators::set($form)
			->at("personCode", "personCode")->required()->maxlength(12)
			->at("serialNo"  , "serialNo"  )->required()->inArray($this->serialNos)
			->getValidatedData();

		// デバイスを取得。
		$serialNo = $data["serialNo"];
		$device = $this->contractor["deviceList"][$serialNo];
		$deviceId = $device["device_id"];			
		
		// 人物データを取得。
		$person = PersonService::getPersonByCode($this->contractor, $data["personCode"]);
		
		$accessTimes = [];
			
		for ($i = 1; $i <= 10; $i++) {
			if (isset($form["accessFlag_$i"]) && $form["accessFlag_$i"] === "1" || $form["accessFlag_$i"] === "0") {
				
				$accessData = Validators::set($form)
					   ->at("accessFlag_$i"    , "accessFlag_$i"    )->flag()
					   ->at("accessTimeFrom_$i", "accessTimeFrom_$i")->required()->dateTime(2000, 2099)
					   ->at("accessTimeTo_$i"  , "accessTimeTo_$i"  )->required()->dateTime(2000, 2099)->compNotSame("accessTimeFrom_$i", "accessTimeFrom_$i")
					   ->getValidatedData();
			
				$time = [
					  "accessFlag"	   => $accessData["accessFlag_$i"]
					, "accessTimeFrom" => $accessData["accessTimeFrom_$i"]
					, "accessTimeTo"   => $accessData["accessTimeTo_$i"]
				];
				
				if (strtotime($time["accessTimeFrom"]) > strtotime($time["accessTimeTo"])) {
					throw new ApiParameterException("accessTimeFrom_$i", "accessTimeTo_{$i}よりも過去の日時を指定して下さい。");
				}
					
				$accessTimes[] = $time;
			}
		}
		
		ApbService::registPersonAcessTimes($person["person_id"], $deviceId, $accessTimes);
		$this->responseJson(["result"=>true]);
	}
	
	// クラウドサーバへ人物のICカード情報を登録。
	public function registPersonCardInfoForCloudAction(&$form) {
		
		$data = Validators::set($form)
			->at("personCode", "personCode")->required()->maxlength(12)
			->getValidatedData();
		
		// 人物データを取得。
		$person = PersonService::getPersonByCode($this->contractor, $data["personCode"]);
		
		$cardInfos = [];
		
		for ($i = 1; $i <= 3; $i++) {
			// if ($i === 1 || !empty($form["card_no_$i"])) {
			if (!empty($form["card_no_$i"])) {
				
				$accessData = Validators::set($form)
					->at("card_no_$i"    , "card_no_$i"    )->required()->alphaNum()
					->at("validityDateFrom_$i", "validityDateFrom_$i")->required()->date(2000, 2099)
					->at("validityDateTo_$i"  , "validityDateTo_$i"  )->required()->date(2000, 2099)->compFuture("validityDateFrom_$i", "validityDateFrom_$i")
					->getValidatedData();
				
				if (!empty($accessData["card_no_$i"])) {
					if (preg_match("/^[0-9]+$/", $accessData["card_no_$i"])) {
						if (strlen($accessData["card_no_$i"]) > 20) {
							Errors::add("card_no_$i", "20桁以下のカード番号(10進数)を指定してください。");
						} else if ("18446744073709551615" < $accessData["card_no_$i"]) { //TODO UINT_MAX  compare
							Errors::add("card_no_$i", "18446744073709551615以下のカード番号(10進数)を指定してください。");
						}
					} else if (preg_match("/^[a-fA-F0-9]+$/", $accessData["card_no_$i"])) {
						if (strlen($accessData["card_no_$i"]) > 16) {
							Errors::add("card_no_$i", "16桁以下のカード番号(16進数)を指定してください。");
						} else {
							// 入力文字内にa～fがあった場合は、自動でA～Fに変換してDBに登録
							$accessData["card_no_$i"] = mb_strtoupper($accessData["card_no_$i"], 'UTF-8');
						}
					} else {
						Errors::add("card_no_$i", "「0～9,A～F,a～f」の文字を入力してください。");
					}
				}
				if (Errors::isErrored()) {
					throw new ApiParameterException();
				}
				
				$card = [
					"cardID"	   => $accessData["card_no_$i"]
					, "dateFrom" => $accessData["validityDateFrom_$i"]
					, "dateTo"   => $accessData["validityDateTo_$i"]
				];
				
				$cardInfos[] = $card;
			}
		}
		
		PersonService::registPersonCardInfo($person["person_id"], $cardInfos);
		$this->responseJson(["result"=>true]);
	}

	// クラウドサーバに登録された人物の通行可能時間帯の取得。
	public function getPersonAcessTimeFromCloudAction(&$form) {
		
 		$data = Validators::set($form)
			->at("personCode", "personCode")->required()->maxlength(12)
			->getValidatedData();
		
		// 人物データを取得。
		$person = PersonService::getPersonByCode($this->contractor, $data["personCode"]);
		
		$list = ApbService::getPersonAcessTimes($person["person_id"]);
		$this->responseJson($list);
	}

	// クラウドサーバに登録された人物のカード情報の取得。
	public function getPersonCardInfoFromCloudAction(&$form) {
	
		$data = Validators::set($form)
			->at("personCode", "personCode")->required()->maxlength(12)
			->getValidatedData();
		
		// 人物データを取得。
		$person = PersonService::getPersonByCode($this->contractor, $data["personCode"]);
		
		//$person["cardInfo"] = PersonService::getPersonCardInfo($personId);
		$list = PersonService::getPersonCardInfo($person["person_id"]);
		$this->responseJson($list);
	}

	// キャプチャモード
	public function capturePersonPictureAction(&$form) {

		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
		->at("getCount"  , "getCount"  )->digit(1, 30)
		->at("personCode", "personCode")->maxlength(12)
		->at("override"  , "override"  )->digit(0,1)
		->getValidatedData();

		$data['getCount'] = (!empty($data['getCount'])) ? $data['getCount'] : 30;

		// personCodeが入力された場合の処理
		if (!empty($data['personCode'])) {
			$person = DB::selectRow("select * from t_person where person_code = {value}",$data['personCode']);
			// 既にpersonが存在する場合はエラーもしくは配列格納
			if (!is_null($person)) {
				// overrideが1以外（上書きしない）の場合はエラー
				if (!($data['override']==1)) throw new ApiParameterException("personCode", "既にそのpersonCodeは存在します。");
				$data['personName']  = $person['person_name'];
				$data["apb_in_flag"] = $person['apb_in_flag'];
				$data["birthday"]    = $person['birthday'];
				$data["sex"]		 = $person['sex'];
				$data["memo"]		 = $person['memo'];
			}
		}
		infoLog("capturePersonPictureAction:form:".print_r(json_encode($form),true));
		infoLog("capturePersonPictureAction:device:".print_r(json_encode($device),true));
		$ret = PersonService::capturePersonPicture($device, $data, $this->contractor);
		if (!empty($ret["message"])) {
			$this->responseJson($ret);
		} else {
			$this->response(function() use ($ret) {
				
				header("Content-type: image/jpeg");
				header("Content-Length: ".strlen($ret));		
				echo $ret;
				
			});
		}		
	}
	
	public function checkSimilarityInDeviceAction(&$form) {

		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("picture", "picture")->required()->byteMaxlength(1024 * 1024  * 5)	// base64後で5MBまで
			->getValidatedData();

		$picture = $data['picture'];
		
		$ret = PersonService::checkSimilarityInDevice($device, $picture);
		
		$this->responseJson($ret);
	}

	public function getPersonPictureFromDeviceAction(&$form) {

		$device = $this->getDeiveBySerialNo($form);
		
		$data = Validators::set($form)
			->at("personCode", "personCode")->required()->maxlength(12)
			->getValidatedData();

		$personCode = $data['personCode'];
		
		$ret = PersonService::getPersonPictureFromDevice($device, $personCode);
		
		if (is_array($ret)) {
			$this->responseJson($ret);
		} else {
			$this->response(function() use ($ret) {
				
				header("Content-type: image/jpeg");
				header("Content-Length: ".strlen($ret));		
				echo $ret;
				
			});
		}
	}

	public function getPersonTypeAction(&$form) {

		// 入退管理モードではない場合はエラー
		if (!$this->contractor["enter_exit_mode_flag"]) throw new ApiParameterException("enter_exit_mode_flag","入退管理モード以外ではこのAPIは実行できません。");
		
		$ret = DB::selectArray("select person_type_code, person_type_name from m_person_type where contractor_id = {value} order by person_type_code", $this->contractor["contractor_id"]);

		$this->responseJson($ret);
	}

	public function registPersonTypeAction(&$form) {

		// 登録上限数の取得＆ハンドリング
		$registLimit     = 10;
		$countPersonType = DB::selectOne("SELECT COUNT(person_type_id) FROM m_person_type WHERE contractor_id = {value}", $this->contractor['contractor_id']);
		if ($countPersonType >= $registLimit) {
			$this->responseJson(["result"=>false,"message"=>"登録上限数「{$registLimit}」に達しています。"]);
			exit;
		}
		
		// 入退管理モードではない場合はエラー
		if (!$this->contractor["enter_exit_mode_flag"]) throw new ApiParameterException("enter_exit_mode_flag","入退管理モード以外ではこのAPIは実行できません。");

		$data = Validators::set($form)
			->at("personTypeCode", "personTypeCode")->digit(1)
			->at("personTypeName", "personTypeName")->required()->maxlength(35)
			->at("override"      , "override"	   )->flag()
			->getValidatedData();

		$data['person_type_code'] = $data['personTypeCode'];
		$data['person_type_name'] = $data['personTypeName'];
		
		if (!PersonService::registPersonType($this->contractor, $data)) $this->responseError();
		
		$this->responseJson(["result"=>true]);
	}

	public function deletePersonTypeAction(&$form) {
		// code指定で削除、削除したcodeが付与されたpersonはNULLへ戻す、recog_logもNULL
		
		// 入退管理モードではない場合はエラー
		if (!$this->contractor["enter_exit_mode_flag"]) throw new ApiParameterException("enter_exit_mode_flag","入退管理モード以外ではこのAPIは実行できません。");

		$data = Validators::set($form)
			->at("personTypeCode", "personTypeCode")->required()->digit(1,100000)
			->getValidatedData();

		if (!PersonService::deletePersonType($this->contractor, $data["personTypeCode"])) $this->responseError();
		
		$this->responseJson(["result"=>true]);
	}
}
