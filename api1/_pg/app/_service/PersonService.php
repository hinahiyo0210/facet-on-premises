<?php 

class PersonService {
	
	const MESSAGE_FACE_REGISTED = "既に保存されている人物の顔写真です。";
		
	// 検索用ワイルドカードに対応する。
	public static function createLikeParam(&$data, $paramName, &$where, $columnName) {
		
		if (empty($data[$paramName])) return;
		
		if (startsWith($data[$paramName], "*") && endsWith($data[$paramName], "*")) {
			$data[$paramName] = excludeSuffix(excludePrefix($data[$paramName], "*"), "*");
			$where .= " and {$columnName} like {like_LR {$paramName}}";
			
		} else if (startsWith($data[$paramName], "*")) {
			$data[$paramName] = excludePrefix($data[$paramName], "*");
			$where .= " and {$columnName} like {like_L {$paramName}}";

		} else if (endsWith($data[$paramName], "*")) {
			$data[$paramName] = excludeSuffix($data[$paramName], "*");
			$where .= " and {$columnName} like {like_R {$paramName}}";
			
		} else {
			$where .= " and {$columnName} = {{$paramName}}";
			
		}
		
		
	}
	
	// DBから取得した人物データについて、APIで返却するフォーマットに変換する。
	public static function convertApiFormat($contractor, $personLogRow, $pictureParam = false, $replace = true) {
		
		if ($replace) {
			$row = [];
		} else {
			$row = $personLogRow;
		}
		
		$row["personCode"] = $personLogRow["person_code"];
		$row["personName"] = $personLogRow["person_name"];
		$row["cardIDs"] = $personLogRow["card_ids"];
		
		if (empty($personLogRow["s3_object_path"])) {
			$row["pictureUrl"] = null;
		} else {
			$row["pictureUrl"] = CLOUDFRONT_URL."/".$contractor["s3_path_prefix"].$personLogRow["s3_object_path"];
			if (!empty($pictureParam)) {
				$row["pictureUrl"] .= "?".$pictureParam;
			}
		}
		
		$row["sex"]         = $personLogRow["sex"];
		$row["memo"]        = $personLogRow["memo"];
		$row["birthday"]    = formatDate($personLogRow["birthday"]);
		// add-start version3.0  founder feihan
		$row["personTypeName"]  = (isset($personLogRow["person_type_name"])) ? $personLogRow["person_type_name"] : "";
		// add-end version3.0  founder feihan
		
		if (!empty($contractor["apb_mode_flag"])) {
			$row["apb_in_flag"] = empty($personLogRow["apb_in_flag"]) ? 0 : 1;
		}
		
		return $row;
	}
	
	
	// DBに人物データを保存。
	public static function registPerson($contractor, $data) {

    // オンプレの場合は登録数チェック
    if (!ENABLE_AWS) {
      $contractor_id = $contractor["contractor_id"];
      $personLimit = 10000;
      $personCount = DB::selectOne("SELECT COUNT(*) FROM t_person WHERE contractor_id = {$contractor_id}");
      if ($personCount + 1 > $personLimit) {
        throw new ApiParameterException("personCode", "登録者は最大{$personLimit}人までしか登録できません。");
      }
    }
		
		// pictureとrecogLogIdのどちらかは必須。
		$byLogId   = (!empty($data["recogLogSerialNo"]) && !empty($data["recogLogId"]));
		$byPicture = !empty($data["picture"]); 
		
		if (!$byLogId && !$byPicture) {
			throw new ApiParameterException("picture", "「recogLogSerialNo及びrecogLogId」もしくは「picture」のどちらか一方は必須です。");
		}
		
		$picBin = null;
		if ($byPicture) {
			// パラメータで画像を指定された場合にはjpegかどうかをチェックする。
			$picBin = base64_decode($data["picture"]);
			if (!isJpegImage($picBin)) {
				throw new ApiParameterException("picture", "画像ファイルはjpeg形式のものを指定して下さい。");
			}
			
		} else {
			// recogLogIdが指定されているのであれば、S3から画像を取得する。
			$device = $contractor["deviceList"][$data["recogLogSerialNo"]];
			
			$param = ["device_id"=>$device["device_id"], "device_recog_log_id"=>$data["recogLogId"]];
			$recogLog = DB::selectRow("select * from t_recog_log where device_id = {device_id} and device_recog_log_id = {device_recog_log_id}", $param);
			if (empty($recogLog)) {
				throw new ApiParameterException("recogLogId", "指定されたデバイスのrecogLogIdに該当する認識ログは存在していません。");
			}
			if (empty($recogLog["s3_object_path"])) {
				throw new ApiParameterException("recogLogId", "指定されたデバイスのrecogLogIdに該当する認識ログには写真画像が登録されていません。");
			}
			
			// ファイルを取得。
			$picBin = AwsService::downloadS3RecogPicture($device, $recogLog["recog_log_id"], $recogLog["recog_time"]);
			if (empty($picBin)) {
				throw new SystemException("recogLogId", "写真を取得しようと試みましたが、エラーにより取得出来ませんでした。");
			}
			
		}

		$param = [];
		$param["person_code"]   = $data["personCode"];
		$param["person_name"]   = $data["personName"];
		$param["apb_in_flag"]   = (isset($data["apb_in_flag"])) ? $data["apb_in_flag"] : "";
		if (empty($contractor["save_person_name_flag"])) { 
			$param["person_name"] = "***";
		}
		$param["birthday"]      = (isset($data["birthday"])) ? $data["birthday"] : "";
		$param["sex"]           = (isset($data["sex"])) ? $data["sex"] : "";
		$param["memo"]          = (isset($data["memo"])) ? $data["memo"] : "";
		$param["contractor_id"] = $contractor["contractor_id"];
		$param["user_id"]       = empty($data["user_id"]) ? -1 : $data["user_id"];
		// add-start version3.0  founder feihan
		$param["person_type_code"] = (isset($data["person_type_code"])) ? $data["person_type_code"] : "";
		// add-end version3.0  founder feihan
		$param["person_description1"] = (isset($data["person_description1"])) ? $data["person_description1"] : "";
		$param["person_description2"] = (isset($data["person_description2"])) ? $data["person_description2"] : "";
		
		// データが存在する場合にはupdateを行う。
		$existedPerson = DB::selectRow("select * from t_person where contractor_id = {contractor_id} and person_code = {person_code} for update", $param);  
		if (!empty($existedPerson)) {
			$param["person_id"] = $existedPerson["person_id"];
			$sql = "
				update 
					t_person
				set
					person_name      = {person_name}
					, sex            = {sex}
					, birthday	     = {birthday}
					, memo		     = {memo}
					, update_time    = now()
				 	, update_user_id = {user_id}
					, apb_in_flag    = {flag apb_in_flag}
				    , person_type_code = {person_type_code}
				    , person_description1 = {person_description1}
				    , person_description2 = {person_description2}
				where
					person_id        = {person_id}
			";
			DB::update($sql, $param);
			
			// S3のファイルを削除する。
			if (!empty($existedPerson["s3_object_path"])) {
				if (!AwsService::deleteS3PersonPicture($contractor, $existedPerson["person_id"], $existedPerson["s3_object_path"], $existedPerson["create_time"])) {
					throw new SystemException("picture", "画像ファイルの削除に失敗しました。");
				}
			}
			
		} else {
			// personIdを得るために先にDBに保存する。
			$sql = "
				insert into 
					t_person 
				set
					contractor_id    = {contractor_id}
					, create_time    = now()
				 	, create_user_id = {user_id}
					, update_time    = now()
				 	, update_user_id = {user_id}
					, person_code    = {person_code}
					, person_name    = {person_name}
					, sex            = {sex}
					, birthday	     = {birthday}
					, memo		     = {memo}
					, apb_in_flag    = {flag apb_in_flag}
					, person_type_code = {person_type_code}
					, person_description1 = {person_description1}
					, person_description2 = {person_description2}
			";
			// add-start version3.0  founder feihan
			if($contractor["enter_exit_mode_flag"] != 1) {
				$param["person_type_code"] = null;
				$param["person_description1"] = null;
				$param["person_description2"] = null;
			}
			// add-end version3.0  founder feihan
			$param["person_id"] = DB::insert($sql, $param);
		}
		
		// S3にファイルをアップする。
		$personCreateTime = DB::selectOne("select create_time from t_person where person_id = {person_id}", $param);
		if (!AwsService::uploadS3PersonPicture($contractor, $param["person_id"], $personCreateTime, $picBin)) {
			throw new SystemException("picture", "クラウドサーバ上に画像が正常に保存されませんでした。");
		}
		
		return true;
	}
	
	// 人物のICカード情報を保存。
	public static function registPersonCardInfo($personId, $cardInfo, $userId = -1) {
		// 仕組み上、デッドロックが発生する恐れがある。発生した場合にはもう一度行う。
		DB::commit();
		DB::begin();
		
		$loop = 0;
		while (true) {
			if ($loop++ > 10) trigger_error("無限ループ防止　registPersonCardInfo()");
			try {
				
				// 既存データ削除。
				$sql = "delete from t_person_card_info where person_id = {person_id}";
				DB::delete($sql, ["person_id"=>$personId]);
				
				
				// 登録。
				foreach ($cardInfo as $card) {
					$param = [
						"person_id"		=> $personId
						, "card_id"	=> $card["cardID"]
						, "time_from"	=> empty($card["dateFrom"]) ? '2000/01/01' : $card["dateFrom"]
						, "time_to"		=> empty($card["dateTo"]) ? '2037/12/31' : $card["dateTo"]
						, "user_id"		=> $userId
					];
					
					// 登録。
					DB::insert("
						insert into
							t_person_card_info
						set
							person_id		 = {person_id}
							, card_id    = {card_id}
							, create_time	 = now()
							, create_user_id = {user_id}
							, update_time    = now()
							, update_user_id = {user_id}
							, time_from      = {time_from}
							, time_to        = {time_to}
					", $param);
				}
				
				DB::commit();
				DB::begin();
				break;
				
			} catch (Exception $e) {
				if (exists($e->getMessage(), "Deadlock")) {
					warnLog("[Deadlock] registPersonCardInfo");
					DB::rollback();
					DB::begin();
					sleep(1);
					continue;
				}
				throw $e;
			}
			
		}
	}
	
	// 人物をデバイスに登録。
	public static function toDevice($contractor, $device, $personCode, $override, $noImage = false) {
		
		infoLog("[toDevice] device_id:".$device["device_id"].", personCode:".$personCode);
		
		// 排他チェック。
		SyncService::checkExclusion($device["device_id"]);
		
		// 人物を検索。
		$param = [];
		$param["contractor_id"] = $contractor["contractor_id"];
		$param["person_code"]   = $personCode;
		$person = DB::selectRow("select * from t_person where contractor_id = {contractor_id} and person_code = {person_code}", $param);
		
		if (empty($person)) {
			throw new ApiParameterException("personCode", "指定されたpersonCodeに該当する人物データはクラウドに登録されていません。");
		} elseif (!isset($person["person_name"])) {
			throw new ApiParameterException("personCode", "指定されたpersonCodeに該当する人物データにpersonNameが登録されていません。");
		}
		
		// 画像を取得。
		$pictureBin = false;
		if (!$noImage) {
			$pictureBin = AwsService::downloadS3PersonPicture($contractor, $person["s3_object_path"], $person["create_time"]);
			if (empty($pictureBin)) { 
				throw new SystemException("personCode", "指定されたpersonCodeに該当する人物の画像ファイルが正常に取得出来ませんでした。");
			}
		}
		
		// 通行可能時間帯を取得。
		$person["accessTimes"] = ApbService::getPersonAcessTimes($person["person_id"], $device["device_id"]);
		
		// 開始ログ
		SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_toDevice");
		
		if (($override."") === "0") {
			// $overrideで意図的に0が指定されている場合：データが存在している場合には上書きを行わない。
			$ret = PersonService::getPersonFromDevice($device, $personCode);
			
			if (!empty($ret)) {
				
	 			$ret = [];
	 			$ret["result"] = true;
	 			$ret["regist"] = false;
 				$ret["message"] = "指定されたpersonCodeに該当する人物データがデバイスに登録されており、尚且つoverrideで0が指定されているため、登録を取りやめました。";
				
		 		// 終了ログ
				SyncService::updateEndLog(20, json_encode($ret, JSON_UNESCAPED_UNICODE));
				
				return $ret; 
			}
			
		}

		if (($override."") === "2") {
			// $overrideで意図的に2が指定されている場合：デバイスのPersonを削除してから登録する。
			$isCheckExclusion = false;
			PersonService::deletePersonFromDevice($device, $personCode, $isCheckExclusion);
		}
		
		// デバイスへ登録。（AIカメラとの分岐）
		if (($device['device_type'] === "AIカメラ") || (mb_substr(explode('-', $device['device_type'])[1], 0, 1) == 8)) {
			// AIカメラの場合
			if ($noImage) {
				throw new ApiParameterException("noImage", "指定されたデバイスがAIカメラのため画像なしでの登録を取りやめました。");
			} else {
				// AIカメラは上書き処理がないためデフォルトは削除登録処理を行う
				if (!empty(PersonService::getPersonFromDevice($device, $personCode))) PersonService::deletePersonFromDevice($device, $personCode, false);
				$ret = PersonService::registPersonDataForAiDevice($device, $person, $pictureBin);
			}
		} else {
			// AIカメラではない場合
			$ret = PersonService::registPersonDataForDevice($device, $person, $pictureBin);
		}
		
 		if ($ret["result"] && $ret["regist"]) {
			// 関連付けを登録。
			PersonService::registDevicePerson($device["device_id"], $person["person_id"]);
			
			// AIカメラではない場合はICカード情報の登録を行う
			if (($device['device_type'] !== "AIカメラ") && (mb_substr(explode('-', $device['device_type'])[1], 0, 1) != 8)) {

				// ICカード情報登録。
				$cardInfo = DB::selectArray("select * from t_person_card_info where person_id = {person_id}", $person);
				
				// if (isset($cardInfo) && !empty($cardInfo)){
	//				$cardRet = PersonService::registCardDataForDevice($device, $person, $cardInfo);
				PersonService::registCardDataForDevice($device, $person, $cardInfo);
				// }

			}
		 
		}
 		
 		// 終了ログ
		SyncService::updateEndLog(20, json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		return $ret;
	}
	
	// デバイスへ人物データ登録。
	private static function registPersonDataForDevice(array $device, $person, $pictureBin) {
		
		$Person = [
  			"Type"		=> 1,												// TODO: 固定値でセット。　1-社内従業員,  2-訪問者, 3-ブラックリスト
  			"Code"		=> $person["person_code"],
  			"GroupName" => "default",										// TODO: 固定値でセット。
  			"Name"		=> $person["person_name"],
  			"Sex"		=> (empty($person["sex"]) ? "male" : $person["sex"]),
  			"Birthday"	=> (empty($person["birthday"]) ? "1970-01-01" : formatDate($person["birthday"], "Y-m-d"))
  		];
		
		if (!empty($pictureBin)) {
			$Person["Images"] = [base64_encode($pictureBin)];
		}
		
		if (isset($person["accessTimes"])) {
			
			$Person["AccessTimes"] = [];

			foreach ($person["accessTimes"] as $time) {
				$AccessTime = [];
				$AccessTime["AccessType"] = !empty($time["accessFlag"]) ? 1 : 0;
				$AccessTime["TimeSection"] = [strtotime($time["accessTimeFrom"]), strtotime($time["accessTimeTo"])];
				$Person["AccessTimes"][] = $AccessTime; 
			}
			
		}
		
		$apiParam = [
  			"method"=>"personnelData.savePersons",
  			"id"=>WsApiService::genId(),
  			"params"=>[[
  				"Person"=>$Person
  			]]
 		];
		
		$logParam = $apiParam;
		if (!empty($logParam["params"][0]["Person"]["Images"][0])) {
			$logParam["params"][0]["Person"]["Images"][0] = "<".strlen($logParam["params"][0]["Person"]["Images"][0])." bytes>";
		}
		infoLog("端末に人物を登録。".json_encode($logParam, JSON_UNESCAPED_UNICODE));
		
		// 端末にデータを送信。
		set_time_limit(60 * 3);
 		$apiRet = WsApiService::accessWsApi($device, $apiParam);
			
	 	infoLog("人物登録返却。".json_encode($apiRet, JSON_UNESCAPED_UNICODE));
		
 		// メッセージの定義。
 		$messags = [
			589840 => PersonService::MESSAGE_FACE_REGISTED,	
 			589825 => "不明なエラーです。",
			589826 => "顔データベースグループ名が一意ではありません。",
			589827 => "フェイスデータベースグループエイリアスが一意ではありません。",
			589828 => "顔のベースグループのGUIDが一意ではありません。",
			589829 => "顔データベースグループの数が上限に達しました。",
			589830 => "倉庫保管人の名前は一意ではありません。",
			589831 => "倉庫担当者のIDとID番号は一意ではありません。",
			589832 => "顔の画像トークンは一意ではありません。",
			589833 => "顔画像の繰り返しバインド。",
			589834 => "データベース内の登録数が上限に達しました。",
			589835 => "データベース内の顔の数の上限に達しました。",
			589836 => "保存する人物の顔の数が上限を超えています。",
			589837 => "顔の画像データが空です。",
			589838 => "人物が存在しません。",
			589839 => "顔写真は存在しません。",
			589841 => "顔として認識出来ない写真です。",
			589842 => "顔の画質が低すぎます。",
			589843 => "フェイスベースデータベースグループが存在しません。",
			589844 => "顔の画像データが大きすぎます。",
			589845 => "システムはビジー状態です。",
			589846 => "アルゴリズム不明のエラーです。顔として認識出来ない写真の可能性があります。別の顔写真を指定して下さい。",
			589847 => "アルゴリズムモデルのバージョンが一致しません。別の顔写真を指定して下さい。",
			589848 => "フェイスデータベースタイプの競合です。",
			589849 => "顔写真が多すぎます。",
			589850 => "顔画像フォーマットエラーです。別の顔写真を指定して下さい。",
			589851 => "顔写真の角度が間違っています。正面から撮影された写真を利用して下さい。",
			589852 => "顔写真の顔フレームのサイズが間違っています。別の顔写真を指定して下さい。",
			589853 => "顔の写真がぼやけすぎています。別の顔写真を指定して下さい。",
			589854 => "顔画像にてマスクが検出されました。マスクをしていない顔写真を指定してください。",
			655361 => "不明なエラー",
			655362 => "権限グループが見つかりません",
			655363 => "FaceOwnerの作成に失敗しました",
			655364 => "FaceOwnerのクリアに失敗しました",
			655365 => "ユーザーIDはすでに存在します",
			655366 => "制限がないか、最大値を超えています",
		];
		
 		// 返却値を確認。
 		$params = $apiRet["params"][0];
 		
    $ErrorCodePic = (isset($params["ErrorCodePic"])) ? $params["ErrorCodePic"]: [];
 		if (arr($ErrorCodePic, 0) == 0 && !empty($params["Result"])) {
 			// エラー無し。正常に登録。
 			$ret = [];
			$ret["result"] = true;
	 		$ret["regist"] = true;
			$ret["message"] = "人物データがデバイスに登録されました。";
 			
			
 		} else {
 			// エラー有り。
	 		$code = $params["ErrorCode"];
	 		$codePics = $ErrorCodePic;
	 			
	 		$ret = [];
 			$ret["result"] = false;
	 		$ret["regist"] = false;
	 		
 			if (!empty($code)) {
 				$ret["message"] = arr($messags, $code);
 				
 			} else {
 				if (!empty($codePics)) {
	 				foreach ($codePics as $codePic) {
		 				$ret["message"] = arr($messags, $codePic);
	 					break;
	 				}
 				}
 			}
 			
 			if (empty($ret["message"])) {
 				if (!empty($apiRet["ErrorMessage"])) {
	 				$ret["message"] = "{$apiRet["ErrorMessage"]} {$code}";
 				} else {
	 				$ret["message"] = "不明なエラーが発生しています。{$code}";	
 				}
 			}
 		}
 		
 		
 		return $ret;

	}

	// デバイスへ人物データ登録。（AIカメラ用）
	private static function registPersonDataForAiDevice(array $device, $person, $pictureBin) {

		// AIカメラ用の人物登録処理
		$apiParam = [
				"method"=>"faceInfoUpdate.wsAddFace",
				"id"=>WsApiService::genId(),
				"params"=>[
					"GroupID"	=> 1, // 1で固定
					"Images"	=> [base64_encode($pictureBin)],
					"PersonInfo"=>[
						"ID"			 => $person["person_code"],
						"Name"			 => $person["person_name"],
						"Birthday"		 => (empty($person["birthday"]) ? "1970-01-01" : formatDate($person["birthday"], "Y-m-d")),
						"Sex"	  		 => (empty($person["sex"]) ? "male" : $person["sex"]),
						"Country" 		 => "Country",
						"Province"		 => "Province",
						"City"	  		 => "City",
						"CertificateType"=> "IC",
					]
				]
		];
	   
		$logParam = $apiParam;
		infoLog("端末に人物を登録。".json_encode($logParam, JSON_UNESCAPED_UNICODE));
	   
	   	// 端末にデータを送信。
		set_time_limit(60 * 3);
		$apiRet = WsApiService::accessWsApi($device, $apiParam);
			
	 	infoLog("人物登録返却。".json_encode($apiRet, JSON_UNESCAPED_UNICODE));
		
 		// メッセージの定義。
 		$messags = [
			589840 => PersonService::MESSAGE_FACE_REGISTED,	
 			589825 => "不明なエラーです。",
			589826 => "顔データベースグループ名が一意ではありません。",
			589827 => "フェイスデータベースグループエイリアスが一意ではありません。",
			589828 => "顔のベースグループのGUIDが一意ではありません。",
			589829 => "顔データベースグループの数が上限に達しました。",
			589830 => "倉庫保管人の名前は一意ではありません。",
			589831 => "倉庫担当者のIDとID番号は一意ではありません。",
			589832 => "顔の画像トークンは一意ではありません。",
			589833 => "顔画像の繰り返しバインド。",
			589834 => "データベース内の登録数が上限に達しました。",
			589835 => "データベース内の顔の数の上限に達しました。",
			589836 => "保存する人物の顔の数が上限を超えています。",
			589837 => "顔の画像データが空です。",
			589838 => "人物が存在しません。",
			589839 => "顔写真は存在しません。",
			589841 => "顔として認識出来ない写真です。",
			589842 => "顔の画質が低すぎます。",
			589843 => "フェイスベースデータベースグループが存在しません。",
			589844 => "顔の画像データが大きすぎます。",
			589845 => "システムはビジー状態です。",
			589846 => "アルゴリズム不明のエラーです。顔として認識出来ない写真の可能性があります。別の顔写真を指定して下さい。",
			589847 => "アルゴリズムモデルのバージョンが一致しません。別の顔写真を指定して下さい。",
			589848 => "フェイスデータベースタイプの競合です。",
			589849 => "顔写真が多すぎます。",
			589850 => "顔画像フォーマットエラーです。別の顔写真を指定して下さい。",
			589851 => "顔写真の角度が間違っています。正面から撮影された写真を利用して下さい。",
			589852 => "顔写真の顔フレームのサイズが間違っています。別の顔写真を指定して下さい。",
			589853 => "顔の写真がぼやけすぎています。別の顔写真を指定して下さい。",
			655361 => "不明なエラー",
			655362 => "権限グループが見つかりません",
			655363 => "FaceOwnerの作成に失敗しました",
			655364 => "FaceOwnerのクリアに失敗しました",
			655365 => "ユーザーIDはすでに存在します",
			655366 => "制限がないか、最大値を超えています",
		];
		
		if ($apiRet["result"]) {
			// エラー無し。正常に登録。
			$ret = [];
		   $ret["result"] = true;
			$ret["regist"] = true;
		   $ret["message"] = "人物データがデバイスに登録されました。";
			
		   
		} else {
			// エラー有り。
			$code = $apiRet["error"]["code"];
				
			$ret = [];
			$ret["result"] = false;
			$ret["regist"] = false;
			
			if (!empty($code)) {
				$ret["message"] = arr($messags, $code);	
			}
			
			if (empty($ret["message"])) {
				if (!empty($apiRet["ErrorMessage"])) {
					$ret["message"] = "{$apiRet["ErrorMessage"]} {$code}";
				} else {
					$ret["message"] = "不明なエラーが発生しています。{$code}";	
				}
			}
		}
		
		
		return $ret;

	}
	
	// デバイスへカード情報登録。
	private static function registCardDataForDevice(array $device, $person, $cardInfo) {
		
		// 端末にICカード情報を検索
		$apiParam = [
			"method"=>"cardManager.getCards",
			"id"=>WsApiService::genId(),
			"params"=>[
				"Condition"=>[
					"PersonCode"		=> $person["person_code"],
					"Offset"=> 0,
					"Limit"=> 100
				]
			]
		];
		$logParam = $apiParam;
		
		infoLog("端末にICカード情報を検索。".json_encode($logParam, JSON_UNESCAPED_UNICODE));
		
		// 端末にデータを送信。
		set_time_limit(60 * 3);
		$apiRet = WsApiService::accessWsApi($device, $apiParam);
		infoLog("ICカード情報検索返却。".json_encode($apiRet, JSON_UNESCAPED_UNICODE));
		
		// 返却値を確認。
		$queryCards = $apiRet["params"]["Cards"]??[];
		
		// 端末にICカード情報を削除
		foreach ($queryCards as $queryCard) {
			$apiParam = [
				"method"=>"cardManager.removeCard",//ICカード削除
				"id"=>WsApiService::genId(),
				"params"=>[
					"ID"=>$queryCard["ID"]
					]
			];
			$logParam = $apiParam;
			
			infoLog("端末にICカード情報を削除。".json_encode($logParam, JSON_UNESCAPED_UNICODE));
			
			// 端末にデータを送信。
			set_time_limit(60 * 3);
			$apiRet = WsApiService::accessWsApi($device, $apiParam);
			infoLog("ICカード情報削除返却。".json_encode($apiRet, JSON_UNESCAPED_UNICODE));
			
		}
		
		$ret = [];
		foreach ($cardInfo as $cardEntity){
		
			$Card = [
				"PersonCode"		=> $person["person_code"],
				"Type"		=> 1,												// TODO: 固定値でセット。　1-普通卡, 2-胁迫卡；デフォルトは１
				"Validity" => [
					0 => formatDate($cardEntity["time_from"],"Y-m-d"),
					1 => formatDate($cardEntity["time_to"],"Y-m-d")
				],
				"Memo"		=> [""=>""]
			];
			
			
			$apiParam = [
				"method"=>"cardManager.updateCard",
				"id"=>WsApiService::genId(),
				"params"=>[
					"ID"=>$cardEntity["card_id"],
					"Card"=>$Card
				]
			];
			
			$logParam = $apiParam;
	
			infoLog("端末にICカード情報を登録。".json_encode($logParam, JSON_UNESCAPED_UNICODE));
			
			// 端末にデータを送信。
			set_time_limit(60 * 3);
			$apiRet = WsApiService::accessWsApi($device, $apiParam);
			
			infoLog("ICカード情報登録返却。".json_encode($apiRet, JSON_UNESCAPED_UNICODE));
			
			$ret[]=$apiRet;
		}
		return $ret;
	}
	
	// 人物をデバイスから取得。
	public static function getPersonFromDevice($device, $personCode) {
		
		$apiParam = [
  			"method"=>"personManager.getPersons",
  			"id"=>WsApiService::genId(),
  			"params"=>[
  				"Condition"=>[
  					"Code"	  => $personCode,
  					"Offset"  => 0,
					"Limit"	  => 1,  						
  				]
  			]
 		];

		// AIカメラ時の人物確認処理
		if (($device['device_type'] === "AIカメラ") || (mb_substr(explode('-', $device['device_type'])[1], 0, 1) == 8)) {
			$apiParam["method"] = "faceInfoFind.getPersonInfoByID";
			$apiParam["params"] = [];
			$apiParam["params"] = ["CertificateType"=>"IC", "ID"=>$personCode];

			try {
				infoLog("端末から人物を取得。".json_encode($apiParam, JSON_UNESCAPED_UNICODE));
				$apiRet = WsApiService::accessWsApi($device, $apiParam);
				infoLog(json_encode($apiRet, JSON_UNESCAPED_UNICODE));
			} catch (Exception $e) {
				// 人物がいない場合エラー返却となるのでここでキャッチしてnullを返す
				return null;
			}

			// 問題なければ返却内容を返す
			return $apiRet["params"]["PersonInfo"];

		}
		
		infoLog("端末から人物を取得。".json_encode($apiParam, JSON_UNESCAPED_UNICODE));
		$apiRet = WsApiService::accessWsApi($device, $apiParam);
		infoLog(json_encode($apiRet, JSON_UNESCAPED_UNICODE));

		if (!isset($apiRet["params"]["Persons"])) return null;
		
		$persons = $apiRet["params"]["Persons"];
	
		if (empty($persons)) return null;

		/*
			{
			    "AccessTimes": null,
			    "Birthday": "2018-12-12",
			    "Code": "uuui",
			    "Custom": "",
			    "CustomInfo": "",
			    "HealthCode": 0,
			    "Name": "仲間由紀恵",
			    "Sex": "female",
			    "Status": 1,
			    "Type": 1
			}
		 */
		return $persons[0];
	}
	
	
	// 人物の登録件数をデバイスから取得。
	public static function getPersonCountFromDevice($device) {
		
		// ヒット件数を取得。
		$apiRet = WsApiService::accessWsApi($device, [
  			"method"=>"personManager.getCount",
  			"id"=>WsApiService::genId(),
  			"params"=>[
  				"Condition"=>[]
  			]
 		]);
		
		return $apiRet["params"]["Count"];
	}
	
	
	// 人物の一覧をデバイスから取得。
	public static function getPersonListFromDevice($contractor, $device, $data) {
		
		$condition = [];
		if ($data["personCode"]) $condition["CodeLike"] = $data["personCode"]; 
		if ($data["personName"]) $condition["NameLike"] = $data["personName"]; 
		
		// ヒット件数を取得。
		$apiRet = WsApiService::accessWsApi($device, [
  			"method"=>"personManager.getCount",
  			"id"=>WsApiService::genId(),
  			"params"=>[
  				"Condition"=>$condition
  			]
 		]);
		
		$pageInfo = new PageInfo($data["pageNo"], 100);
		$pageInfo->setRowCount($apiRet["params"]["Count"]);
		
		// データを取得。
		$condition["Offset"] = $pageInfo->getOffset();
		$condition["Limit"]  = $pageInfo->getLimit();
		$apiRet = WsApiService::accessWsApi($device, [
  			"method"=>"personManager.getPersons",
  			"id"=>WsApiService::genId(),
  			"params"=>[
  				"Condition"=>$condition
  			]
 		]);
		
		$list = [];
		$personCodes = [];
		if (!empty($apiRet["params"]["Persons"])) {
			foreach ($apiRet["params"]["Persons"] as $devicePerson) {
				$personCodes[] = $devicePerson["Code"];
				$person["personCode"]   = $devicePerson["Code"];
				$person["personName"]   = $devicePerson["Name"];
				if (empty($contractor["save_person_name_flag"])) $person["personName"] = "***";
				$person["birthday"]      = formatDate($devicePerson["Birthday"]);
				$person["sex"]           = $devicePerson["Sex"];
				$person["accessTimes"] = [];
				if (!empty($devicePerson["AccessTimes"])) {
					foreach ($devicePerson["AccessTimes"] as $time) {
						$person["accessTimes"][] = [
							"accessFlag"		=> $time["AccessType"] == 1 ? 1 : 0
							, "accessTimeFrom"	=> date("Y/m/d H:i:s", $time["TimeSection"][0])
							, "accessTimeTo"	=> date("Y/m/d H:i:s", $time["TimeSection"][1])
						];					
					}
				}
				
				$list[] = $person;
			}
				
		}
		
		return [
			"rows"  => $pageInfo->getRowCount(),
			"pages" => $pageInfo->getPageCount(),
			"list  "=> $list	
		];
	}

	// 人物の一覧をAIカメラから取得。
	public static function getPersonListFromAiDevice($contractor, $device, $data) {
		
		// 取得時の絞り込み
		$condition = [];
		if (($data["personCode"] == 0) || $data["personCode"]) $condition["ID"] = "*".$data["personCode"]."*"; 
		if (($data["personName"] == 0) || $data["personName"]) $condition["Name"] = "*".$data["personName"]."*";

		// パラメータの格納
		$getParams = [];
		$getParams["GroupID"] = 1; // グループは「1」で固定
		if (!empty($condition)) $getParams["Condition"] = $condition;

		// 接続を作成
		$param = [
			"id"=> WsApiService::genId(),
			"method"=> "faceInfoFind.create"
		];
		$stepCreate = WsApiService::accessWsApi($device, $param);

		// 存在する人物のカウントを取得（グループは1で固定）
		$param = [
			"id"	=> WsApiService::genId(),
			"method"=> "faceInfoFind.query",
			"object"=> $stepCreate["result"],
			"params"=> $getParams
		];
		$stepQuery = WsApiService::accessWsApi($device, $param);
		
		$pageInfo = new PageInfo($data["pageNo"], 100);
		$pageInfo->setRowCount($stepQuery["params"]["Count"]);

		// データを取得。
		$param = [
			"id"	=> WsApiService::genId(),
			"method"=> "faceInfoFind.getQueryResult",
			"object"=> $stepCreate["result"],
			"params"=> [
				"Offset"=> $pageInfo->getOffset(),
				"Count" => $pageInfo->getLimit()
			]
		];
		$stepQueryResult = WsApiService::accessWsApi($device, $param);

		// 作成した接続を終了する
		$param = [
			"id"	=> WsApiService::genId(),
			"method"=> "faceInfoFind.close",
			"object"=> $stepCreate["result"]
		];
		$stepDestroy = WsApiService::accessWsApi($device, $param);

		$list = [];
		if (!empty($stepQueryResult["params"]["Found"])) {
			foreach ($stepQueryResult["params"]["PersonFaceInfos"] as $devicePerson) {
	
				$person["personCode"]   = $devicePerson["ID"];
				$person["personName"]   = $devicePerson["Name"];
				if (empty($contractor["save_person_name_flag"])) $person["personName"] = "***";
				$person["birthday"]      = formatDate($devicePerson["Birthday"]);
				$person["sex"]           = $devicePerson["Sex"];
				
				$list[] = $person;
	
			}
		}
		
		return [
			"rows"  => $pageInfo->getRowCount(),
			"pages" => $pageInfo->getPageCount(),
			"list  "=> $list	
		];

	}

	// 人物の顔写真を端末から取得する。
	// 顔写真が未登録の場合はfalseを返却する。
	public static function getFacePictureFromDevice($device, $personCode) {

		infoLog("========>".__LINE__);
		set_time_limit(60); // 時間を延長。
		$ret = WsApiService::accessWsApi($device, [
  			"method"=>"faceInfoFind.create",
  			"id"=>WsApiService::genId(),
 		]);
		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		$faceInfoId = $ret["result"];
		
		infoLog("========>".__LINE__);
		$ret = WsApiService::accessWsApi($device, [
  			"method"=>"faceInfoFind.getFaceInfoById",
  			"id"=>WsApiService::genId(),
			"object"=>$faceInfoId,
			"params"=>[
				"CertificateType"=>"IC", 
				"ID"=>$personCode
			]
 		]);
		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		if (empty($ret["params"]["FaceImageInfos"][0]["FaceToken"])) {
			return false;	// 顔写真が未登録
		}
		
		$faceToken = $ret["params"]["FaceImageInfos"][0]["FaceToken"];
		
		infoLog("========>".__LINE__);
		$ret = WsApiService::accessWsApi($device, [
  			"method"=>"faceInfoFind.getFace",
  			"id"=>WsApiService::genId(),
			"object"=>$faceInfoId,
			"params"=>[
				"FaceToken"=>$faceToken
			]
 		]);
		if (empty($ret["data"])) return false;	// 顔写真が未登録
		
		$bin = base64_decode($ret["data"]);
		infoLog(strlen($bin)." bytes");
		
		return $bin;
	}
	
	// 人物をクラウドサーバから削除。
	public static function deletePersonFromCloud($contractor, $personCode, &$delPerson = []) {
	
		// データを取得。
		$param = ["contractor_id"=>$contractor["contractor_id"], "person_code"=>$personCode];
		$person = DB::selectRow("select * from t_person where contractor_id = {contractor_id} and person_code = {person_code}", $param);
		if (empty($person)) {
			throw new ApiParameterException("personCode", "指定されたpersonCodeに該当する人物データはクラウドサーバ上に登録されていません。");
		}
		
		// S3のファイルを削除
		if (!AwsService::deleteS3PersonPicture($contractor, $person["person_id"], $person["s3_object_path"], $person["create_time"])) {
			throw new SystemException("picture", "画像ファイルの削除に失敗しました。");
		}
		
		$person_id = $person["person_id"];
		
		// データを削除。
		DB::delete("delete from t_person where person_id = {value}", $person_id);
		
		// 関連付けを削除。
		DB::delete("delete from t_device_person where person_id = {value}", $person_id);
		
		$delPerson = $person;
		
		return true;
	}
	
	
	
	// 人物をデバイスから削除。
	public static function deletePersonFromDevice($device, $personCode, $isCheckExclusion = true, $ignoreNotFound = false) {
	
		// データを取得し、存在チェックを行う。
		$devicePerson = PersonService::getPersonFromDevice($device, $personCode);
		if (empty($devicePerson)) {
			if ($ignoreNotFound) {
				warnLog("存在チェックの結果を無視し、関連付けのみを削除。{$device["device_id"]} $personCode");
				// 存在チェックの結果を無視する：関連付けを削除。
				PersonService::deleteDevicePersonByPersonCode($device, $personCode);
				return 	["result"=>true];
			}
			throw new ApiParameterException("personCode", "指定されたpersonCodeに該当する人物データはデバイス内に登録されていません。");
		}
		
		if ($isCheckExclusion) {
			// 排他チェック。
			SyncService::checkExclusion($device["device_id"]);
			
			// 開始ログ
			SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_toDevice");
			
		}
		
		$apiParam = [
  			"method"=>"personnelData.removePersons",
  			"id"=>WsApiService::genId(),
  			"params"=>[
  				[
  					"Code"	=> $personCode,
  				]
  			]
 		];
		
		infoLog("端末から人物を削除。".json_encode($apiParam, JSON_UNESCAPED_UNICODE));
		$apiRet = WsApiService::accessWsApi($device, $apiParam);
		infoLog(json_encode($apiRet, JSON_UNESCAPED_UNICODE));
	
		$ret = [
			"result"=>$apiRet["params"][0]["Result"]
		];
		
		if ($ret["result"]) {
			// 関連付けを削除。
			PersonService::deleteDevicePersonByPersonCode($device, $personCode);
		}
		
		if ($isCheckExclusion) {
			// 終了ログ
			SyncService::updateEndLog(20, json_encode($ret, JSON_UNESCAPED_UNICODE));
		}
		
		return $ret;
	}
	

	// 全ての顔データを端末から削除する。
	public static function deleteDeviceFaceInfoAll($device) {

		infoLog("========>".__LINE__);
		set_time_limit(60); // 時間を延長。
		$ret = WsApiService::accessWsApi($device, [
  			"method"=>"faceInfoFind.create",
  			"id"=>WsApiService::genId(),
 		]);
		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		$faceInfoId = $ret["result"];
		
		infoLog("========>".__LINE__);
		set_time_limit(60); // 時間を延長。
		$ret = WsApiService::accessWsApi($device, [
  			"method"=>"faceInfoUpdate.create",
  			"id"=>WsApiService::genId(),
 		]);
		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		$faceUpdateId = $ret["result"];
		
		
// 		$ret = WsApiService::accessWsApi($device, [
//   			"method"=>"faceInfoFind.getFaceInfoById",
//   			"id"=>WsApiService::genId(),
// 			"object"=>$faceInfoId,
// 			"params"=>[
// 				"CertificateType"=>"IC", 
// 				"ID"=>$personCode
// 			]
//  		]);
// 		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));

		infoLog("========>".__LINE__);
		$ret = WsApiService::accessWsApi($device, [
  			"method"=>"faceInfoFind.query",
  			"id"=>WsApiService::genId(),
			"object"=>$faceInfoId,
			"params"=>[
				"GroupID"=>1,
			]
 		]);
		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		$deleted = 0;
		$total = $ret["params"]["Count"];
		if ($ret["params"]["Count"] != 0) {
			infoLog("========>".__LINE__);
			$ret = WsApiService::accessWsApi($device, [
	  			"method"=>"faceInfoFind.getQueryResult",
	  			"id"=>WsApiService::genId(),
				"object"=>$faceInfoId,
				"params"=>[
					"Offset"=>0,
					"Count"=>100,
				]
	 		]);
			infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
			
			infoLog("========>".__LINE__);
			foreach ($ret["params"]["PersonFaceInfos"] as $info) {
				set_time_limit(60); // 時間を延長。
				
				$images = $info["FaceImageInfos"];
				foreach ($images as $image) {
					$id = $image["PersonID"];
					
					infoLog("======$id\n");
					$ret = WsApiService::accessWsApi($device, [
			  			"method"=>"faceInfoUpdate.deletePerson",
			  			"id"=>WsApiService::genId(),
						"object"=>$faceUpdateId,
						"params"=>[
							"PersonID"=>$id
						]
			 		]);
					infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));					
					
					if (!empty($ret["result"])) {
						$deleted++;
					}
				}
				
				
			}
			
				
			
		}

		set_time_limit(60); // 時間を延長。
		
		infoLog("========>".__LINE__);
		$ret = WsApiService::accessWsApi($device, [
			"method"=>"faceInfoFind.destroy",
  			"id"=>WsApiService::genId(),
			"object"=>$faceInfoId,
 		]);
		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		infoLog("========>".__LINE__);
		$ret = WsApiService::accessWsApi($device, [
			"method"=>"faceInfoUpdate.destroy",
  			"id"=>WsApiService::genId(),
			"object"=>$faceUpdateId,
 		]);
		infoLog(json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		return ["total"=>$total, "deleted"=>$deleted];
	}
	
	
	// 全件の人物をデバイスから削除。
	public static function clearPersonFromDevice(array $device) {
		
		// 事前の通信チェック。
		DeviceService::checkDevice($device);
		
		// 排他チェック。
		SyncService::checkExclusion($device["device_id"]);
		
		// 開始ログを登録する。
		SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_clearPersonFromDevice");
		
		$deleted = 0;
		$errorMsg = "";
		$loop = 0;
		
		// 100件づつ処理する。
		while (true) {
			if ($loop++ > 1000) trigger_error("無限ループ防止");	// 10万件以上は処理出来ない。
			
			set_time_limit(60);	// 時間延長
			
			try {
				$apiRet = WsApiService::accessWsApi($device, [
		  			"method"=>"personManager.getPersons",
		  			"id"=>WsApiService::genId(),
		  			"params"=>[
		  				"Condition"=>[
		  					"Offset"  => 0,
							"Limit"	  => 100,  						
		  				]
		  			]
		 		]);
			} catch (DeviceWsException $e) {
				$errorMsg = $deleted."件まで削除されましたが、デバイスとの通信異常により検索処理に失敗し、処理を中断しました。";
				break;
			}
			
			if (empty($apiRet["params"]["Persons"])) {
				// 全て削除した。
				break;
			}
			
			foreach ($apiRet["params"]["Persons"] as $person) {
				set_time_limit(20);	// 時間延長
				
				$personCode = $person["Code"];
						
				$apiParam = [
		  			"method"=>"personnelData.removePersons",
		  			"id"=>WsApiService::genId(),
		  			"params"=>[
		  				[
		  					"Code"	=> $personCode,
		  				]
		  			]
		 		];
				
				try {
					infoLog("端末から人物を削除。".json_encode($apiParam, JSON_UNESCAPED_UNICODE));
					$apiRet = WsApiService::accessWsApi($device, $apiParam, 10000);
					infoLog(json_encode($apiRet, JSON_UNESCAPED_UNICODE));
					
				} catch (DeviceWsException $e) {
					$errorMsg = $deleted."件まで削除されましたが、デバイスとの通信異常により削除処理に失敗し、処理を中断しました。";
					break;
				}
				
				// 関連付けを削除。
				PersonService::deleteDevicePersonByPersonCode($device, $personCode);
					
				$deleted++;
					
			}
			
		}
		
		set_time_limit(60);	// 時間延長
		
		// 更に、ゴミデータとして顔データが残る場合があるため、クリアする。
		try {
			$deleteDeviceFaceInfoResult = PersonService::deleteDeviceFaceInfoAll($device);
			infoLog("deleteDeviceFaceInfoResult: ".json_encode($deleteDeviceFaceInfoResult, JSON_UNESCAPED_UNICODE));
		} catch (DeviceWsException $e) {
			$errorMsg = $deleted."件まで削除されましたが、デバイスとの通信異常により削除処理に失敗し、処理を中断しました。";
		}
		
		// 結果情報。
		$ret = [];
		$ret["deleted"] = $deleted;
		
		if (empty($errorMsg)) {
			$ret["result"] = true;
		} else {
			$ret["result"] = false;
			$ret["message"] = $errorMsg;
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
		
		return $ret;
	}
	
	// 人物をクラウドに登録。
	public static function toCloud($contractor, $device, $personCode, $override) {

		// 人物を取得。
		$devicePerson = PersonService::getPersonFromDevice($device, $personCode);
		
		// データが登録されていない。
		if ($devicePerson === null) {
 			$ret = [];
 			$ret["result"] = false;
 			$ret["regist"] = false;
			$ret["message"] = "指定されたpersonCodeに該当する人物データがデバイス側に登録されていません。";
			return $ret;
		}
		
		// 顔写真を取得。
		$pictureBin = PersonService::getFacePictureFromDevice($device, $personCode);
		if (empty($pictureBin)) {
			$ret = [];
			$ret["result"] = false;
 			$ret["regist"] = false;
			$ret["message"] = "デバイスから顔写真画像を取得する事が出来ませんでした。";
			return $ret;
		}

    // オンプレの場合は登録数チェック
    if (!ENABLE_AWS) {
      $contractor_id = $contractor["contractor_id"];
      $personLimit = 10000;
      $personCount = DB::selectOne("SELECT COUNT(*) FROM t_person WHERE contractor_id = {$contractor_id}");
      if ($personCount + 1 > $personLimit) {
        $ret = [];
        $ret["result"] = false;
        $ret["regist"] = false;
        $ret["message"] = "登録者は最大{$personLimit}人までしか登録できません。";
        return $ret;
      }
    }		
		
		// DBに登録。
		$param = [];
		$param["person_code"]   = $devicePerson["Code"];
		$param["person_name"]   = $devicePerson["Name"];
		if (empty($contractor["save_person_name_flag"])) { 
			$param["person_name"] = "***";
		}
		$param["birthday"]      = $devicePerson["Birthday"];
		$param["sex"]           = $devicePerson["Sex"];
		$param["contractor_id"] = $contractor["contractor_id"];
		
		// 既存データ検索。
		$person = DB::selectRow("select * from t_person where contractor_id = {contractor_id} and person_code = {person_code} for update", $param);
		if (empty($person)) {
			// まだ未登録なのであればinsertする。
			
			$param["memo"] = $device["serial_no"]." から登録。".date("Y/m/d H:i:s");
			
			$sql = "
				insert into 
					t_person 
				set
					contractor_id 	 = {contractor_id}
					, create_time 	 = now()
				 	, create_user_id = -1
					, update_time    = now()
				 	, update_user_id = -1
					, person_code 	 = {person_code}
					, person_name    = {person_name}
					, sex            = {sex}
					, birthday	     = {birthday}
					, memo		     = {memo}
			";
			$param["person_id"] = DB::insert($sql, $param);
			
		} else {

			// $overrideで意図的に0が指定されている場合：データが存在している場合には上書きを行わない。
			if (($override."") === "0") {
				$ret = [];
				$ret["result"] = true;
				$ret["regist"] = false;
				$ret["message"] = "指定されたpersonCodeに該当する人物データがクラウドに登録されており、尚且つoverrideで0が指定されているため、登録を取りやめました。";
				
				return $ret; 	
			}

			// 登録済みなのであればupする。
			$param["person_id"] = $person["person_id"];
			$param["memo"] 		= $person["memo"];
			
			$sql = "
				update 
					t_person
				set
					person_name      = {person_name}
					, sex            = {sex}
					, birthday	     = {birthday}
					, memo		     = {memo}
					, update_time    = now()
				 	, update_user_id = -1
				where
					person_id     = {person_id}
			";
			DB::update($sql, $param);		
			
		}
		
		// S3にアップする。
		$personCreateTime = DB::selectOne("select create_time from t_person where person_id = {person_id}", $param);
		if (!AwsService::uploadS3PersonPicture($contractor, $param["person_id"], $personCreateTime, $pictureBin)) {
			$ret = [];
			$ret["result"] = false;
 			$ret["regist"] = false;
			$ret["message"] = "クラウドサーバ上に画像が正常に保存されませんでした。";
			DB::rollback();	// 先にDB登録を行っているので、画像保存失敗時にはロールバックする。
			DB::begin();
			return $ret;
		}
		
		// 関連付けを登録する。
		PersonService::registDevicePerson($device["device_id"], $param["person_id"]);
		
		$ret = [];
		$ret["result"] = true;
		$ret["regist"] = true;
		$ret["message"] = "人物がクラウドに登録されました。";
		
		return $ret;
	}
	
	// 人物をデバイスとクラウドサーバの両方から取得する。
	public static function getPerson($contractor, $device, $personCode, $includePicture) {
		
		// デバイスからデータを取得。
		$devicePerson = PersonService::getPersonFromDevice($device, $personCode);
		
		$devicePicture = null;
		if ($includePicture && !empty($devicePerson)) {
			$devicePicture = PersonService::getFacePictureFromDevice($device, $personCode);
		}
		
		// DBからデータを取得。
		$param = ["contractor_id"=>$device["contractor_id"], "person_code"=>$personCode];
		$cloudPerson = DB::selectRow("select * from t_person where contractor_id = {contractor_id} and person_code = {person_code}", $param);
		$cloudPicture = null;
		if ($includePicture && !empty($cloudPerson)) {
			$cloudPicture = AwsService::downloadS3PersonPicture($contractor, $cloudPerson["s3_object_path"], $cloudPerson["create_time"]);
			if ($cloudPicture === false) {
				throw new SystemException("picture", "クラウドサーバから画像データが取得出来ませんでした。");
			}
		}

		// 関連付けを登録。
		if (!empty($cloudPerson)) {
			if (empty($devicePerson)) {
				PersonService::deleteDevicePerson($device["device_id"], $cloudPerson["person_id"]);
			} else {
				PersonService::registDevicePerson($device["device_id"], $cloudPerson["person_id"]);
			}
		}
		
		// 結果を作成。
		$deviceRet = null;
		if (!empty($devicePerson)) {
			$deviceRet["personCode"]   = (($device['device_type'] === "AIカメラ") || (mb_substr(explode('-', $device['device_type'])[1], 0, 1) == 8)) ? $devicePerson["ID"] : $devicePerson["Code"];
			$deviceRet["personName"]   = $devicePerson["Name"];
			if (empty($contractor["save_person_name_flag"])) $deviceRet["personName"] = "***";
			$deviceRet["birthday"]      = formatDate($devicePerson["Birthday"]);
			$deviceRet["sex"]           = $devicePerson["Sex"];
			$deviceRet["picture"]   	= $includePicture ? base64_encode($devicePicture) : null;
			
			$deviceRet["accessTimes"] = [];
			if (!empty($devicePerson["AccessTimes"])) {
				foreach ($devicePerson["AccessTimes"] as $time) {
					$deviceRet["accessTimes"][] = [
						"accessFlag"		=> $time["AccessType"] == 1 ? 1 : 0
						, "accessTimeFrom"	=> date("Y/m/d H:i:s", $time["TimeSection"][0])
						, "accessTimeTo"	=> date("Y/m/d H:i:s", $time["TimeSection"][1])
					];					
				}
			}
		}
		
		$cloud = null;
		if (!empty($cloudPerson)) {
			// カードID
			$cloudPerson["card_ids"] = DB::selectOne("select group_concat(card_id SEPARATOR '/') from t_person_card_info where person_id = {value}", $cloudPerson["person_id"]);
			
			$cloud = PersonService::convertApiFormat($contractor, $cloudPerson);

			if ($contractor["enter_exit_mode_flag"] == 1) {
				// 区分名
				$personTypeParam = [];
				$personTypeParam["person_type_code"] = $cloudPerson["person_type_code"];
				$personTypeParam["contractor_id"]    = $device["contractor_id"];
				$cloud["personTypeName"] = DB::selectOne("select person_type_name from m_person_type where contractor_id = {contractor_id} and person_type_code = {person_type_code}", $personTypeParam);
	
				// 備考
				$cloud["personDescription1"] = $cloudPerson["person_description1"];
				$cloud["personDescription2"] = $cloudPerson["person_description2"];
			}
			unset($cloud["pictureUrl"]);
			$cloud["picture"] = $includePicture ? base64_encode($cloudPicture) : null;
			$cloud["accessTimes"] = ApbService::getPersonAcessTimes($cloudPerson["person_id"]);
			
		}
		
		return ["device"=>$deviceRet, "cloud"=>$cloud];
	}
	
	// デバイスと人物の関連を登録。
	public static function registDevicePerson($device_id, $person_id) {
		
		$param = ["device_id"=>$device_id, "person_id"=>$person_id];
		DB::delete("delete from t_device_person where device_id = {device_id} and person_id = {person_id}", $param);
		DB::insert("insert into t_device_person set device_id = {device_id}, person_id = {person_id}, create_time = now()", $param);
		
	}

	// デバイスと人物の関連を登録。
	public static function registDevicePersonByPersonCode(array $device, $person_code) {
		
		$param = ["contractor_id"=>$device["contractor_id"], "person_code"=>$person_code];
		$person_id = DB::selectOne("select person_id from t_person where contractor_id = {contractor_id} and person_code = {person_code}",$param);
		
		if (!empty($person_id)) {
			PersonService::registDevicePerson($device["device_id"], $person_id);
		}
	
	}
	
	
	// デバイスと人物の関連を削除。
	public static function deleteDevicePerson($device_id, $person_id) {
		
		$param = ["device_id"=>$device_id, "person_id"=>$person_id];
		DB::delete("delete from t_device_person where device_id = {device_id} and person_id = {person_id}", $param);
		
	}
	
	// デバイスと人物の関連を削除。
	public static function deleteDevicePersonByPersonCode(array $device, $person_code) {
		
		$param = ["contractor_id"=>$device["contractor_id"], "person_code"=>$person_code];
		
		$person_id = DB::selectOne("select person_id from t_person where contractor_id = {contractor_id} and person_code = {person_code}",$param);
		if (!empty($person_id)) {
			PersonService::deleteDevicePerson($device["device_id"], $person_id);
		}
		
	}
	
	// 人物の写真の事前チェック
	public static function checkPersonPicture($base64picture) {
	
		// パラメータで画像を指定された場合にはjpegかどうかをチェックする。
		$picBin = base64_decode($base64picture);
		if (!isJpegImage($picBin)) {
			throw new ApiParameterException("picture", "画像ファイルはjpeg形式のものを指定して下さい。");
		}
		
		$ret_msg = [
			"589825" => "アルゴリズム不明のエラーです。顔として認識出来ない写真の可能性があります。別の顔写真を指定して下さい。",
			"589841" => "アルゴリズム不明のエラーです。顔として認識出来ない写真の可能性があります。別の顔写真を指定して下さい。",
			"589849" => "複数の顔画像が検出されました。１人しか映っていない顔画像を指定してください。",
			"589850" => "画像ファイルはjpeg形式のものを指定して下さい。",
			"589851" => "顔写真の角度が間違っています。正面から撮影された写真を利用して下さい。",
			"589852" => "顔写真の顔フレームのサイズが間違っています。別の顔写真を指定して下さい。",
			"589853" => "アルゴリズム不明のエラーです。顔として認識出来ない写真の可能性があります。別の顔写真を指定して下さい。",
			"589854" => "顔画像にてマスクが検出されました。マスクをしていない顔写真を指定してください。",
			"589855" => "アルゴリズム不明のエラーです。顔として認識出来ない写真の可能性があります。別の顔写真を指定して下さい。",
		];

		// AWSならチェック用サーバーで、違う場合はデバイスでのチェックとなる分岐
		if (ENABLE_AWS) {
			// check server url
			$check_server = "http://ec2-52-69-61-24.ap-northeast-1.compute.amazonaws.com/face/picCheck";
			$post_data = array('base64Image'=>$base64picture,'devType'=>'0','filterDegree'=>'0');
			$curl = curl_init($check_server);
			curl_setopt($curl,CURLOPT_PORT, 9000);
			curl_setopt($curl,CURLOPT_POST, TRUE);
			curl_setopt($curl,CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
			curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($post_data));
			curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl,CURLOPT_TIMEOUT_MS, 20000);
			$output = curl_exec($curl);
			$result = json_decode($output,true);

			// タイムアウトなどで何も返却がない場合は利用できないエラーで返却
			if (empty($output)) throw new DeviceWsException("現在、画像チェック機能をご利用いただけません。少し時間を置いてから再度お試し下さい。", true);
	
			// to do: add message in Japanese
	
			if($result["result"]=="true"){ // image is valid
				return ["result"=>true];
			} else {
				return ["result"=>false,"message"=>$ret_msg[@$result["code"]]];
			}
			exit();
	
			// ignore sources below
		} else  {

			// 稼働中のチェック用デバイスを探す。
			$deviceList = DB::selectArray("select * from m_device where picture_check_device_flag = 1");
			
			// 並び順をシャッフルする。
			PersonService::mt_shuffle($deviceList);
			
			$ret = false;
			
			foreach ($deviceList as $device) {
				
				try {
					// 接続中かどうかをチェック。
					try {
						DeviceService::checkDevice($device);
					} catch (DeviceWsException $e) {
						infoLog("checkPersonPicture:チェック用デバイスが接続されていません ".$device["serial_no"]);
						continue;
					}
					
					$personCode = "_CLOUD_CHECK";
		
					// 排他チェック（10回まで試行する）。
					$canUse = false;
					for ($i = 1; $i <= 10; $i++) {
						try {
							SyncService::checkExclusion($device["device_id"]);
							
							// 利用可能（抜ける）
							$canUse = true;
							break;
							
						} catch (DeviceExclusionException $e) {
							// 端末は現在、処理稼働中。
							infoLog("checkPersonPicture:稼働中のため待機してリトライ。".$device["serial_no"]);
							sleep(1);	// 1秒待機。
						}
					}
		
					if (!$canUse) {
						warnLog("checkPersonPicture:リトライ終了で次のデバイスへ。".$device["serial_no"]);
						continue;
					}
					
					// 利用可能：開始ログ
					SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_checkPerson");
					
					// 人物コードがもしあれば、削除する。
					try {
						PersonService::deletePersonFromDevice($device, $personCode, false);
					} catch (ApiParameterException $e) {
						// この人物コードは登録されていない。（無視して続行）
						Errors::clear();
					}
					
			 		// デバイスへ登録。
			 		$person = [];
			 		$person["person_code"] = $personCode;
			 		$person["person_name"] = "TEST_BY_CLOUD_API_SERVER";
			 		$ret = PersonService::registPersonDataForDevice($device, $person, $picBin);
	
					
					// 検証のために登録したデータを削除する。
					try {
						PersonService::deletePersonFromDevice($device, $personCode, false);
					} catch (ApiParameterException $e) {
						// この人物コードは登録されていない。（無視して続行）
						Errors::clear();
					}
					
					// 終了ログ
					SyncService::updateEndLog(20, json_encode($ret, JSON_UNESCAPED_UNICODE));
						
					// 最終WebSocket通信時刻を更新。
					DeviceService::updateWsLastTime($device["serial_no"], time());
					
					// 処理を行う事が出来たので終了。
					break;
				
				} catch (DeviceWsException $e) {
					warnLog("checkPersonPicture:通信エラーのため、次のデバイスへ。".$device["serial_no"]." ".$e->getMessage());
					continue;	
				}
			}
			
			if ($ret === false) {
				// 全てのデバイスが利用出来る状態ではなかった
				throw new DeviceWsException("現在、画像チェック機能をご利用いただけません。少し時間を置いてから再度お試し下さい。", true);
			}
	
			// 顔登録済みは正常とみなす。
			if (!$ret["result"] && $ret["message"] == PersonService::MESSAGE_FACE_REGISTED)  {
				return ["result"=>true];
			}
			
			// 問題の無い画像。
			if ($ret["regist"]) {
				return ["result"=>true];
			}
			
			// 問題のある画像。
			return ["result"=>false, "message"=>$ret["message"]];
		}
	}
	
	private static function mt_shuffle(&$array) {
	    $array = array_values($array);
	    for ($i = count($array) - 1; $i > 0; --$i) {
	        $j = mt_rand(0, $i);
	        $tmp = $array[$i];
	        $array[$i] = $array[$j];
	        $array[$j] = $tmp;
	    }
	}
	
	// 関連付けを削除。
	public static function deletePersonAssociation($contractor, $personCode, $device) {
		
		// データを取得。
		$person = PersonService::getPersonByCode($contractor, $personCode);
		
		if (empty($device)) {
			// 全ての関連付けを削除。
			$deleted = DB::delete("delete from t_device_person where person_id = {person_id}", ["person_id"=>$person["person_id"]]);
			
		} else {
			// 指定デバイスの関連付けを削除。
			$deleted = DB::delete("delete from t_device_person where person_id = {person_id} and device_id = {device_id}", ["person_id"=>$person["person_id"], "device_id"=>$device["device_id"]]);
			
		}
		
		return $deleted;
	}
	
	
	public static function getPersonByCode($contractor, $personCode) {
		
		$param = ["contractor_id"=>$contractor["contractor_id"], "person_code"=>$personCode];
		$person = DB::selectRow("select * from t_person where contractor_id = {contractor_id} and person_code = {person_code}", $param);
		if (empty($person)) {
			throw new ApiParameterException("personCode", "指定されたpersonCodeに該当する人物データはクラウドサーバ上に登録されていません。");
		}
		return $person;
		
	}
	
	// personIdからICカード情報を取得する。
	public static function getPersonCardInfo($personId) {
		
		$ret = [];
		
		$sql = "
			select
				tpci.person_id
				, tpci.card_id
				, tpci.time_from
				, tpci.time_to
			from
				t_person_card_info tpci

			where
				tpci.person_id = {person_id}
			order by
				tpci.update_time
			";
		
		$param = ["person_id"=>$personId];
		
		foreach (DB::selectArray($sql, $param) as $item) {
			
			$ret[] = [
				"cardID"	   => $item["card_id"]
				, "dateFrom" => formatDate($item["time_from"], "Y/m/d H:i:s")
				, "dateTo"   => formatDate($item["time_to"], "Y/m/d H:i:s")
			];
			
		}
		
		return $ret;
		
	}

	// キャプチャモード 2022年2月14日
	public static function capturePersonPicture($device, $data, $contractor = NULL) {
		
		// 排他チェック。
		SyncService::checkExclusion($device["device_id"]);

		infoLog("[capturePersonPicture] device_id:".$device["device_id"]);

		// 開始ログ
		SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_capturePersonPicture");

		// 開始前のデバイスステータス取得
		$status = WsApiService::accessWsApi($device, [
			"method"=>"commDS.getDormancyState"
			, "id"=>WsApiService::genId()
			, "params"=> NULL
		]);
		// オブジェクトの作成
		$object = WsApiService::accessWsApi($device, [
			 "method"=>"videoAnalyse.factory.instance"
			 , "id"=>WsApiService::genId()
			 , "params"=> [
					"channel"=>0
			 ]
		]);
		// キャプチャモードの開始（true）
		WsApiService::accessWsApi($device, [
			"method"=>"videoAnalyse.setEnterFaceMode"
			, "id"=>WsApiService::genId()
			, "params"=> [
					"EnterFace"=>true
			]
			, "object"=>$object['result']
		]);
		// デバイスステータスが休止だった場合は運用指示をする
		if ($status['params']['State'] == 1) {
			SystemService::setOperationMode($device);
		}

		// 画像取得を指定回数分を1秒間隔で
		for ($i=0; $i < $data['getCount']; $i++) {
			$picture = WsApiService::accessWsApi($device, [
				 "method"=>"videoAnalyse.getEnterFaceImage"
				 , "id"=>WsApiService::genId()
				 , "params"=> NULL
				 , "object"=>$object['result']
			]);
			if (!empty($picture['data'])) {
				break;
			}
			sleep(1);
		}
		// forループ後スタンバイモードへ戻す
		if ($status['params']['State'] == 1) {
			SystemService::setHibernateMode($device);
		}
		// キャプチャモードの解除
		WsApiService::accessWsApi($device, [
			"method"=>"videoAnalyse.setEnterFaceMode"
			, "id"=>WsApiService::genId()
			, "params"=> [
					"EnterFace"=>false
			]
			, "object"=>$object['result']
		]);
		WsApiService::accessWsApi($device, [
			"method"=>"videoAnalyse.destroy"
			, "id"=>WsApiService::genId()
			, "params"=> NULL
			, "object"=>$object['result']
		]);

		// 画像キャプチャができた場合の処理
		if (!empty($picture['data'])) {
			// person_codeが入力されている場合の処理
			if (!empty($data['personCode'])) {
				$data['picture'] = $picture['data'];
				$data['personName'] = $data['personCode'];
				if (!PersonService::registPerson($contractor, $data)) {
					$this->responseError();
				}
			}

			// 終了ログ
			SyncService::updateEndLog(20, json_encode("取得完了", JSON_UNESCAPED_UNICODE));

			return base64_decode($picture['data']);

		} else {

			// 終了ログ
			SyncService::updateEndLog(20, json_encode("取得失敗", JSON_UNESCAPED_UNICODE));
			
			return [
				"result"  => false,
				"message" => "顔画像が取得出来ませんでした。"
			];
		}
	}

	public static function checkSimilarityInDevice($device,$picture) {

		infoLog("[checkSimilarityInDevice] device_id:".$device["device_id"]);
	
		// パラメータで画像を指定された場合にはjpegかどうかをチェックする。
		$picBin = base64_decode($picture);
		if (!isJpegImage($picBin)) {
			throw new ApiParameterException("picture", "画像ファイルはjpeg形式のものを指定して下さい。");
		}

		$ret = WsApiService::accessWsApi($device, [
			"method"=>"faceRecognize.recognize"
			, "id"=>WsApiService::genId()
			, "params"=> [
						   "GroupID"   => 1,
						   "FaceImage" => $picture
					]
		]);

		if ($ret['result']) {
			return [
				"result"     => $ret["result"],
				"personCode" => $ret["params"]["Details"][0]["PersonInfo"]["ID"],
				"score"      => round($ret["params"]["Details"][0]["SearchScore"],1),
			];
		} else {
			return [
				"result"     => $ret["result"],	
				"message"    => $ret["message"]
			];
		}
	}

	public static function getPersonPictureFromDevice($device, $personCode) {

		infoLog("[getPersonPictureFromDevice] device_id:".$device["device_id"]."person_code:".$personCode);

		$object = WsApiService::accessWsApi($device, [
			"method"=>"faceInfoFind.create"
			, "id"=>WsApiService::genId()
		]);

		$faceToken = WsApiService::accessWsApi($device, [
			"method"=>"faceInfoFind.getFaceInfoById"
			, "id"=>WsApiService::genId()
			, "object"=>$object["result"]
			, "params"=> [
					"CertificateType" => "IC",
					"ID"              => $personCode
		 		]
		]);

		if (empty($faceToken)) {
			return [
				"result"  => false,
				"message" => "該当のpersonCodeが存在しません。"
			];
		}

		$ret = WsApiService::accessWsApi($device, [
			"method"=>"faceInfoFind.getFace"
			, "id"=>WsApiService::genId()
			, "object"=>$object["result"]
			, "params"=> [
						   "FaceToken"   => $faceToken["params"]["FaceImageInfos"][0]["FaceToken"],
					]
		]);

		WsApiService::accessWsApi($device, [
			"method"=>"faceInfoFind.destroy"
			, "id"=>WsApiService::genId()
			, "object"=>$object["result"]
		]);

		return base64_decode($ret["data"]);

	}

	public static function registPersonType($contractor, $data) {

		// SQLパラメーター格納
		$params = [];
		$params["user_id"]          = empty($data["user_id"]) ? -1 : $data["user_id"];
		$params["contractor_id"]    = $contractor["contractor_id"];
		$params["person_type_code"] = !empty($data["person_type_code"]) ? $data["person_type_code"] : DB::selectOne("select MAX(person_type_code) from m_person_type") + 1;
		$params["person_type_name"] = $data["person_type_name"];
		
		// 存在する場合は格納
		$existedPersonType = DB::selectRow("select * from m_person_type where contractor_id = {contractor_id} and person_type_code = {person_type_code} for update", $params);

		if (!empty($existedPersonType)) {
			
			// 既に存在している場合で、overrideが未指定の場合にエラー
			if (!$data["override"]) throw new ApiParameterException("personTypeCode", "指定されたpersonTypeCodeは既に存在します。");

			// 存在していてoverrideが1で指定されている場合は上書き
			$sql = "
				update 
					m_person_type
				set
					person_type_name = {person_type_name}
					, update_time    = now()
				 	, update_user_id = {user_id}
				where
					person_type_code   = {person_type_code}
					and contractor_id  = {contractor_id}
			";
			
			DB::update($sql, $params);

		} else {

			$sql = "
			insert into 
				m_person_type 
			set
				contractor_id      = {contractor_id}
				, person_type_code = {person_type_code}
				, person_type_name = {person_type_name}
				, create_time    = now()
				, create_user_id = {user_id}
				, update_time    = now()
				, update_user_id = {user_id}
			";

			DB::insert($sql, $params);

		}
		
		return true;
	}

	public static function deletePersonType($contractor, $personTypeCode) {

		// SQLパラメーター格納
		$params = ["contractor_id"=>$contractor["contractor_id"], "person_type_code"=>$personTypeCode];

		// 存在確認
		$existedPersonType = DB::selectRow("select * from m_person_type where contractor_id = {contractor_id} and person_type_code = {person_type_code} for update", $params);
		if (empty($existedPersonType)) throw new ApiParameterException("personTypeCode", "指定されたpersonTypeCodeは登録されていません。");

		DB::commit();
		DB::begin();
		try {
			// データを削除。
			DB::delete("delete from m_person_type where contractor_id = {contractor_id} and person_type_code = {person_type_code}", $params);
			
			// 関連付けを削除（t_person）
			DB::update("update t_person set person_type_code = NULL where contractor_id = {contractor_id} and person_type_code = {person_type_code}", $params);

			// 関連付けを削除（t_recog_log）
			foreach ($contractor["deviceList"] as $device) {
				$params["device_id"] = $device["device_id"];
				DB::update("update t_recog_log set person_type_code = NULL where device_id = {device_id} and person_type_code = {person_type_code}", $params);
			}
		} catch (Exception $e) {
			DB::rollback();
			throw new Exception("指定されたpersonTypeCodeの削除に失敗しました。");
		}

		return true;
	}
	
}
