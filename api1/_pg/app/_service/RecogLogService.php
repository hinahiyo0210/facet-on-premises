<?php 
require_once __DIR__.'/TeamSpirit/oauth.php';

class RecogLogService {
	
	private static $cloudFrontPictureSignedParam;
	
	
	// DBから取得した認識ログについて、APIで返却するフォーマットに変換する。
	public static function convertApiFormat($device, $recogLogRow, $pictureParam = false, $replace = true, $logFlag = false) {
		
		if ($replace) {
			$row = [];
		} else {
			$row = $recogLogRow;
		}
		
		if (empty($recogLogRow["device_recog_log_id"])) {
			$row["id"]  =  null;
		} else {
			$row["id"]  =  (int) $recogLogRow["device_recog_log_id"];
		}
		$row["time"]    = formatTime($recogLogRow["recog_time"]);

		// カード情報のPUSH転送の実装のため追記（デバイスシリアル）2022年3月31日
		$row["serialNo"] = DB::selectOne("select serial_no from m_device where device_id = {device_id}", $recogLogRow);
		// 追記終了
		
		// 通知の場合は"1"で届くが、ログ検索の場合は"001"で届くため、3桁ゼロ埋めで統一させる。
// 		if (mb_strlen($recogLogRow["person_code"]) < 4 && Filter::digit($recogLogRow["person_code"]) != null) {
// 			$row["personCode"] = zpad($recogLogRow["person_code"], 3);
// 		} else {
			$row["personCode"] = $recogLogRow["person_code"];
// 		}
		
		if (empty($device["save_recog_name_flag"])) {
			$row["personName"] = "***";
			
		} else {
			$row["personName"] = $recogLogRow["person_name"];
		}
		
		if (empty($device["save_recog_picture_flag"]) || empty($recogLogRow["s3_object_path"])) {
			$row["pictureUrl"] = null;
		} else {
			$row["pictureUrl"] = CLOUDFRONT_URL."/".$device["s3_path_prefix"]."/recog".$recogLogRow["s3_object_path"];
			if (!empty($pictureParam)) {
				$row["pictureUrl"] .= "?".$pictureParam;
			}
		}
		
		
		$row["mask"] = (int) $recogLogRow["mask"];
		
		if (empty($recogLogRow["temperature"])) {
			$row["temp"] = null;
		} else {
			$row["temp"] = (double) $recogLogRow["temperature"];
		}
		
		$row["pass"] = $recogLogRow["pass"] == 1 ? true : false;
		if ($recogLogRow["search_score"] == null) {
			$row["searchScore"] = null;
		} else {
			$row["searchScore"] = (double) $recogLogRow["search_score"];
		}
		// add-start founder zouzhiyuan
		// pass_flag
		if (isset($recogLogRow["flag_name"]) && ($recogLogRow["flag_name"] != null)) {
			$row["passFlagName"] = $recogLogRow["flag_name"];
		} else {
			$row["passFlagName"] = null;
		}
		// add-end founder zouzhiyuan

		// add pass_flag for MOT/勤怠
		if ($recogLogRow["pass_flag"] !== null) {
			$row["passFlag"] = $recogLogRow["pass_flag"];
		}
		
		// access_type
		if ($recogLogRow["access_type"] == null) {
			$row["accessType"] = null;
		} else {
			$row["accessType"] = $recogLogRow["access_type"];
		}
		// card_type
		if ($recogLogRow["card_no"] == null) {
			$row["cardNo"] = null;
		} else {
			$row["cardNo"] = $recogLogRow["card_no"];
		}
		// card_type
		if ($recogLogRow["card_type"] == null) {
			$row["cardType"] = null;
		} else {
			$row["cardType"] = $recogLogRow["card_type"];
		}

		// カード情報のPUSH転送の実装のため追記（カードバインド情報）
		$row["cardBindPersonCode"] = (!empty($recogLogRow["card_bind_person_code"])) ? $recogLogRow["card_bind_person_code"] : NULL;
		$row["cardBindPersonName"] = (!empty($recogLogRow["card_bind_person_name"])) ? $recogLogRow["card_bind_person_name"] : NULL;
		// 追記終了
		
		// カード認証時に特定画像にする修正
		if ($logFlag) {
			$tempArr = [NULL, -1, -1.1];
			if (($recogLogRow["access_type"] === "Card") && in_array($recogLogRow["temperature"], $tempArr)) {
				$row["cardPicture"] = ($recogLogRow["card_type"] === "6") ? "/ui1/static/images/qr.png" : "/ui1/static/images/card.png";
			} else {
				$row["cardPicture"] = false;
			}
		}
		
		return $row;
	}
			

	// httpリバースの受信データから認識ログを登録。
	public static function registRecogLogByPush($device, $data, $picture) {
		
		if (empty($device) || empty($data["Events"][0])) {
			warnLog("pushデータ不正。device=".json_encode($device, JSON_UNESCAPED_UNICODE).", json=".json_encode($data, JSON_UNESCAPED_UNICODE).", picture=<".strlen($picture)." bytes>");
			return false;
		}
		if (empty($picture)) {
			infoLog("画像なしPUSHの登録。device=".json_encode($device, JSON_UNESCAPED_UNICODE).", json=".json_encode($data, JSON_UNESCAPED_UNICODE));
		}

		$param = [];
		$param["device_id"] = $device["device_id"];
		$param["enter_exit_type_flag"] = $device["device_role"];
		
		$ev = $data["Events"][0];
		$param["recog_time"] = date("Y/m/d H:i:s" , $ev["UTC"] - 60 * 60 * 9);	// UTC-9時間
//		$param["recog_time"] = date("Y/m/d H:i:s" , $ev["Timestamp"]);
		$param["action"]     = $ev["Action"];
		
		if (!empty($ev["AccessInfo"])) {
			$param["pass_result"]	= $ev["AccessInfo"]["PassResult"];
			// add-start founder luyi
			$param["pass_flag"]		= isset($ev["AccessInfo"]["PassFlag"]) ? $ev["AccessInfo"]["PassFlag"] : "";
			// add-end founder luyi
		}
		
		$rr = false;
		if (!empty($ev["RecognizeResults"][0])) {
			$rr = $ev["RecognizeResults"][0];
			$param["liveness_result"]    = $rr["LivenessResult"];
			$param["liveness_score"]     = $rr["LivenessScore"];
			$param["liveness_threshold"] = $rr["LivenessThreshold"];
			
			$param["search_score"]     = $rr["SearchScore"];
			$param["search_threshold"] = $rr["SearchThreshold"];
			
			// 人物特定に成功した場合には SearchScore >= SearchThresholdとなる。  
			if ($rr["SearchScore"] >= $rr["SearchThreshold"]) {
				$param["person_code"] = $rr["PersonInfo"]["ID"];
				$param["person_name"] = $rr["PersonInfo"]["Name"];
				$param["person_type"] = $rr["PersonInfo"]["PersonType"];

				// AIカメラの場合、個人特定ができればpassは「1」で登録する
				if (($device['device_type'] === "AIカメラ") || (mb_substr(explode('-', $device['device_type'])[1], 0, 1) == 8)) $param["pass_result"] = 1;
			}
			
		}
		
		if (!empty($rr)) {
			$param["mask"] = $rr["Mouthocc"];
			$param["custom"] = (isset($rr["Custom"])) ? $rr["Custom"] : "";
		}
		$param["pass"] = isset($ev["Pass"]) ? $ev["Pass"] : "";
		
		if (!empty($ev["Object"])) {
			$ob = $ev["Object"];
			$param["age"]         		  = isset($ob["Age"]) ? $ob["Age"] : "";
			$param["sex"]         		  = isset($ob["Sex"]) ? $ob["Sex"] : "";
			if (isset($ob["Temperature"])) {
				$param["temperature"]         = ($ob["Temperature"] >= 43) ? 0 : $ob["Temperature"];
				$param["temperature_alarm"]   = ($ob["Temperature"] >= 43 || $ob["Temperature"] == 0) ? 1 : $ob["TemperatureAlarm"];
				$param["temperature_surface"] = $ob["TemperatureSurface"];
			} else {
				$param["temperature"]         = "";
				$param["temperature_alarm"]   = "";
				$param["temperature_surface"] = "";
			}
			
		}
		
		// 認証方式
		$Code = $ev['Code'];
		$Card = $ev["Card"] ?? false;
		$CardTypeMap = ["IC"=>1,"QR"=>6];
		if ($Code == "EventFaceRecognizeCutout") {
			
			$param["access_type"] = "Face";
			
			$param["card_bind_person_code"] = null;
			$param["card_bind_person_name"] = null;
			
			$param["card_no"] = null;
			$param["card_type"] = null;
			
		} elseif ($Code == "EventCardDetectSnapshot") {
			
			$param["person_code"] = $Card["Code"];
			$param["person_name"] = $Card["Name"];
			
			$param["access_type"] = "Card";
			
			$param["card_bind_person_code"] = $Card["Code"];
			$param["card_bind_person_name"] = $Card["Name"];
			
			$param["card_no"] = $Card["CardNo"];
			$param["card_type"] = arr($CardTypeMap, $Card["CardType"]);
			
		} elseif ($Code == "EventFaceRecognizeAndCardCutout") {
			
			$param["access_type"] = "FaceAndCard";
			
			$param["card_bind_person_code"] = $Card["Code"];
			$param["card_bind_person_name"] = $Card["Name"];
			
			$param["card_no"] = $Card["CardNo"];
			$param["card_type"] = arr($CardTypeMap, $Card["CardType"]);
			
		} elseif ($Code == "EventFirstCardAndsFaceRecognizeCutout") {
			
			$param["access_type"] = "FirstCardAndFace";
			
			$param["card_bind_person_code"] = $Card["Code"];
			$param["card_bind_person_name"] = $Card["Name"];
			
			$param["card_no"] = $Card["CardNo"];
			$param["card_type"] = arr($CardTypeMap, $Card["CardType"]);
			
		}
		
		// 人物特定に成功した場合、対象者区分と備考群を取得
		if (isset($param["person_code"])) {
			$param["contractor_id"] = $device["contractor_id"];
			$sql = "
				select
					person_type_code
					, person_description1
					, person_description2
				from
					t_person
				where
					person_code = {person_code}
					and contractor_id = {contractor_id}
				order by
					person_id
				limit 1
			";
			$enter_exit_contents = DB::selectRow($sql, $param);
			if ($enter_exit_contents) {
				$param["person_type_code"]    = $enter_exit_contents["person_type_code"];
				$param["person_description1"] = $enter_exit_contents["person_description1"];
				$param["person_description2"] = $enter_exit_contents["person_description2"];
			}
		}
		
		$param["credential_no"] = null;
		$param["credential_type"] = null;
		$param["detail"] = null;
		$param["id_card"] = null;
		$param["picture_flag"] = 0;
		
		// 既に同一日時でデータが存在したら削除する。
		// $deleted = RecogLogService::deleteRecogLogByRecogTime($device, $param["recog_time"]);
		
		// ログデータに登録。
		$sql = "
			insert into 
				t_recog_log
			set 
				device_id 				= {device_id}
				, create_time           = now()
				, sync_log_id 			= null 
				, sync_type 			= 9
				, device_recog_log_id 	= null
				, recog_time 			= {recog_time} 
				, person_code			= {person_code}
				, person_name 			= {person_name}
				, person_type 			= {person_type}
				, access_type 			= {access_type}
				, mask 					= {mask}
				, pass 					= {pass_result}
				/* add-start founder luyi */
				, pass_flag				= {pass_flag}
				/* add-end founder luyi */
				, temperature 			= {temperature}
				, card_bind_person_code = {card_bind_person_code}
				, card_bind_person_name	= {card_bind_person_name}
				, card_no 				= {card_no}
				, card_type 			= {card_type}
				, credential_no 		= {credential_no}
				, credential_type 		= {credential_type}
				, custom 				= {custom}
				, detail 				= {detail}
				, id_card 				= {id_card}
				, picture_flag 			= 0
				, search_score          = {search_score}
				, person_type_code      = {person_type_code}
				, enter_exit_type_flag  = {enter_exit_type_flag}
				, person_description1   = {person_description1}
				, person_description2   = {person_description2}
		";
		$recog_log_id = DB::insert($sql, $param);
		DB::commit();
				
		// 同一時間の解析用データを削除。
		$sql = "
			delete from  
				t_recog_analize
			where 
				device_id				= {device_id}
				and recog_time			= {recog_time}
		";
		DB::delete($sql, $param);
		DB::commit();
		
		// 解析用データに登録。
		$sql = "
			insert into 
				t_recog_analize
			set
				device_id				= {device_id}
				, create_time			= now()
				, recog_time			= {recog_time}
				, action				= {action}
				, pass_result			= {pass_result}
				, liveness_result		= {liveness_result}
				, liveness_score		= {liveness_score}
				, liveness_threshold	= {liveness_threshold}
				, search_score			= {search_score}
				, search_threshold		= {search_threshold}
				, age					= {age}
				, mouthocc				= {mask}
				, sex					= {sex}
				, temperature			= {temperature}
				, temperature_alarm		= {temperature_alarm}
				, temperature_surface	= {temperature_surface}
				, person_code			= {person_code}

		";
		DB::insert($sql, $param);
		DB::commit();

		// add-start founder luyi
		// デバイス所属グループを取得。
		$sql = "
			select
				tdgd.device_group_id
			from
				t_device_group_device tdgd
			where
				tdgd.device_id = {device_id}
		";
		$res = DB::selectRow($sql, $device);
		$device_group_id = $res["device_group_id"] ?? null;
		
		// 文言メッセージ情報を取得。
		$sql = "
			select
			    tprm.priority
			    , tprm.device_group_id_set
			    , tprm.device_id_set
			    , tprm.person_id_set
			    , tprm.pass_set
			    , tprm.pass_flag_set
				, tprm.custom_tip
			    , tprm.tips_time
				, tprm.border_color
				, tprm.background_color
				, tprm.door_index
				, tprm.open_delay
			from
				t_push_response_msg tprm
			where
				tprm.contractor_id = {contractor_id}
		";
		$res = DB::selectArray($sql, $device);
		
		// マッチング
		$res_filtered = array_filter($res, function($e) use($param, $device_group_id) {
			return (empty($e["device_group_id_set"])	|| in_array($device_group_id,		explode(",", $e["device_group_id_set"])))
				&& (empty($e["device_id_set"])			|| in_array($param["device_id"],	explode(",", $e["device_id_set"])))
				&& (empty($e["person_id_set"])			|| in_array($param["person_code"],	explode(",", $e["person_id_set"]))
					|| empty($param["person_code"]) && $e["person_id_set"] === "-1")
				&& (empty($e["pass_set"]) 				|| in_array($param["pass_result"],	explode(",", $e["pass_set"])))
				&& (empty($e["pass_flag_set"])			|| in_array($param["pass_flag"],	explode(",", $e["pass_flag_set"])));
		});
		// 優先順位が一番高いデータを取得。
		$res_target = array_reduce($res_filtered, function($e1, $e2) {
			return (empty($e1) || $e1["priority"] > $e2["priority"]) ? $e2 : $e1;
		});
		
		if (isset($res_target)) {
			
			// Json文字列に変換。
			$picture_ack = json_encode(
				array(
					"result"=>true,
					"PicID"=>$data["PicID"]
				)
			);
			$door_open_and_show_tips = json_encode(
				array(
					"Type"=>"tips",
					"Customtip"=>$res_target["custom_tip"],
					"Tipstime"=>$res_target["tips_time"],
					"BorderColor"=>explode(",", $res_target["border_color"]),
					"BackgroundColor"=>explode(",", $res_target["background_color"]),
					"DoorIndex"=>$res_target["door_index"],
					"Opendelay"=>$res_target["open_delay"]
				), JSON_NUMERIC_CHECK
			);
			$resp_json = str_replace("\\\\n", "\n","{\"PictureAck\":".$picture_ack.",\"DoorOpenAndShowTips\":".$door_open_and_show_tips."}");
			
			// Pushレスポンスに入れてデバイスに送信。
			header("Content-Type: application/json; charset=utf-8");
			header("Content-Length: ".strlen($resp_json));
			ob_start();
			echo $resp_json;
			ob_end_flush();
			ob_flush();
			flush();
			if (session_id()) {
				session_write_close();
			}
		}
		// add-end founder luyi
		
		// S3にアップロード。
		if ($device["save_recog_picture_flag"] && !empty($picture)) {
			AwsService::uploadS3RecogPicture($device, $recog_log_id, $param["recog_time"], base64_decode($picture));
		}
		
		// デバイスマスタの最終時刻を更新。
		DeviceService::updateDeviceLastTime($device["device_id"]);
		
		infoLog("recog_log_id:".$recog_log_id." を登録。");

		// TeamSpirit連携のオプションを契約している場合は連携を開始
		$teamspiritFlag = DB::selectOne('select teamspirit_flag from m_contractor where contractor_id = {contractor_id}', $device);
		if ($teamspiritFlag) {
			
			// pass_flagを変換
			if (!empty($param['pass_flag'])) $param['pass_flag'] = DB::selectOne("select teamspirit_pass_flag from m_recog_pass_flag where contractor_id = {$device['contractor_id']} and pass_flag = {$param['pass_flag']}");

			/* ==========TeamSpirit連携（pass_flagが入っており、通行許可になっているログの場合連携を行う）========== */
			if (!empty($param['pass_flag']) && !empty($param["person_code"])) {

				// 連携対象条件設定を取得
				$conditionsFlag = DB::selectOne("select conditions_set from m_teamspirit_set where contractor_id = {contractor_id}", $device);

				if (!$conditionsFlag) {
		
					if ($param['pass_result'] == 1) {

						infoLog("TeamSpiritのログ処理を開始：recog_log_id[".$recog_log_id."]");
						AttendanceLogService::attendanceByRecogTime($device, $param, $teamspiritFlag);

					} else {
						// 連携対象外のログの場合
						infoLog("通行許可ではないためTeamSpiritのログ処理をスキップしました：recog_log_id[".$recog_log_id."]");
					}
					
				} else {
					
					infoLog("TeamSpiritのログ処理を開始：recog_log_id[".$recog_log_id."]");
					AttendanceLogService::attendanceByRecogTime($device, $param, $teamspiritFlag);
					
				}
				
			} else {
				// 連携対象外のログの場合
				infoLog("有効なpass_flagではないもしくはPersonCodeがないためTeamSpiritのログ処理をスキップしました：recog_log_id[".$recog_log_id."],pass_flag[".$param['pass_flag']."]");
			}

		}
		
		return ["recog_log_id"=>$recog_log_id, "data"=>$param];
	}
	
	
	// 既に同一日時でデータが存在したら削除する。削除した場合はtrueを返す。
	private static function deleteRecogLogByRecogTime($device, $recogTime) {
		
		$param = ["device_id"=>$device["device_id"], "recog_time"=>$recogTime];
		
		// 対象データの写真がS3登録済みなのであれば、削除を行う。
		$recog_log_ids = DB::selectArray("select recog_log_id from t_recog_log where device_id = {device_id} and recog_time = {recog_time} and sync_type = 9", $param);
		if (!empty($recog_log_ids)) {
			foreach ($recog_log_ids as $index => $arr) {
				if (isset($arr["picture_flag"]) && $arr["picture_flag"]) {
					AwsService::deleteS3RecogPicture($device, $arr["recog_log_id"], $recogTime);
				}
			}
		}
		
		// データを削除。
		foreach ($recog_log_ids as $index => $arr) {
			DB::delete("delete from t_recog_log where recog_log_id = {value}", $arr["recog_log_id"]);
			DB::commit();	// すぐにコミット。
		}
		
		return !empty($recog_log_ids);
	}

	// 近似日時でスコアなどが同一のデータが存在したら削除し、削除された件数を返却する。
	private static function deleteRecogLogByNearRecogTime($device, $registParam) {
		
		// nullはイコール検索は出来ない事もあり、まずは+-2秒で候補データを取得する。
		$param = [
			  "device_id"		=> $device["device_id"]
			, "recog_time_from"	=> date("Y/m/d H:i:s", strtotime($registParam["recog_time"]) - 2)	// -2秒
			, "recog_time_to"	=> date("Y/m/d H:i:s", strtotime($registParam["recog_time"]) + 2)	// +2秒
		];
		
		$targets = DB::selectArray("
			select 
				recog_log_id
				, picture_flag
				, recog_time 
				, person_code
				, person_name
				, mask
				, pass
				, search_score
				, temperature
			from 
				t_recog_log 
			where 
				device_id = {device_id} 
				and recog_time >= {recog_time_from}
				and recog_time <= {recog_time_to}
		", $param);

		$deleted = 0;
		
		foreach ($targets as $target) {
			
			$scoreDiff = $target["search_score"] - $registParam["search_score"];
			$tempDiff = $target["temperature"] - $registParam["temperature"];
			
			if ($scoreDiff < 0) $scoreDiff *= -1;
			if ($tempDiff < 0) $tempDiff *= -1;
	
			// 近似時間帯の削除候補データのうち、人物やスコアなどが一致していれば削除とする。
			if (  $target["person_code"]  == $registParam["person_code"]
			   && $target["person_name"]  == $registParam["person_name"]
			   && $target["mask"]		  == $registParam["mask"]
			   && ($scoreDiff < 0.1)
			   && ($tempDiff < 0.1)
			   ) {
			   	
				// 対象データの写真がS3登録済みなのであれば、削除を行う。
			   	if (!empty($target["picture_flag"])) {
					AwsService::deleteS3RecogPicture($device, $target["recog_log_id"], $target["recog_time"]);
			   	}
			   	
				// データを削除。
				DB::delete("delete from t_recog_log where recog_log_id = {recog_log_id}", $target);
			   	$deleted++;
				DB::commit();	// すぐにコミット。
			}
			
		}
		
		return $deleted;
	}
	
	
	// 検出ログリストを取得。
	public static function getAccessRecord(array $device, $startTimeSec, $endTimeSec, $limit) {

 		$ret = WsApiService::accessWsApi($device, [
 			"method"=>"accessRecord.find"
 			, "id"=>WsApiService::genId()
 			, "params"=>[
 				"Condition"=>[
 					"StartTime"=>$startTimeSec
 					, "EndTime"=>$endTimeSec
 					, "Offset"=>0
 					, "Limit"=>$limit
 				]	
 			]
 					
 		]);
 	
 		if (empty($ret["params"]["Records"])) return [];
 		
 		$records = $ret["params"]["Records"];
 		
 		return $records;
 	}
	

	// 検出ログリストを削除。
	public static function deleteAccessRecord(array $device, $startTimeSec, $endTimeSec) {

 		$ret = WsApiService::accessWsApi($device, [
 			"method"=>"accessRecord.clear"
 			, "id"=>WsApiService::genId()
 			, "params"=>[
 				"Condition"=>[
 					"StartTime"=>$startTimeSec
 					, "EndTime"=>$endTimeSec
 				]	
 			]
 					
 		]);
 		
 	}
	
	
	// WSから認識ログを取得し、DBに保存。
	public static function syncRecogLog($device, $sync_type, $rangeSecFrom = false, $rangeSecTo = false, $limit = 100) {
		
		$deviceId = $device["device_id"];
		
		if (empty($rangeSecFrom)) {
			// 最終取得ログ日時を取得。
			$rangeSecFrom = DB::selectOne("select max(recog_time) from t_recog_log where device_id = {$deviceId} and sync_type in(1, 2)");
			
			// ログが無い場合には、デバイスマスター側の運用開始日とする。
			if (empty($rangeSecFrom)) {
				$rangeSecFrom = DB::selectOne("select start_date from m_device where device_id = {$deviceId}");
			}
				
		}
		
		// 検索対象開始日時：Unitタイムスタンプに。
		$rangeSecFrom = strtotime($rangeSecFrom);
		
		// 検索対象開始日時：同じデータの取得を避けるために+1秒する。
		$rangeSecFrom += 1;
		
		// 検索対象終了日時：指定があればその値に。無ければ現在日時に。 
		if (empty($rangeSecTo)) {
			$rangeSecTo = time();
		} else {
			$rangeSecTo = strtotime($rangeSecTo);
		}
		
		// 制限時間を延長。
		set_time_limit(120);
		
		$logRegisted = 0;
		$list = RecogLogService::getAccessRecord($device, $rangeSecFrom, $rangeSecTo, $limit); // API実行。
		
		foreach ($list as $r) {
			
			$recogTime = date("Y/m/d H:i:s", $r["Time"]);
			
			// DBに登録済みのIDなのであれば削除。
			DB::delete("delete from t_recog_log where device_id = {$deviceId} and device_recog_log_id = {value}", $r["ID"]);
			
			// 既に同一日時でデータが存在したら削除する。
			$deleted = RecogLogService::deleteRecogLogByRecogTime($device, $recogTime);
			
			$sql = "
				insert into 
					t_recog_log
				set 
					device_id 				= {device_id}
					, create_time           = now()
					, sync_log_id 			= {sync_log_id} 
					, sync_type 			= {sync_type}
					, device_recog_log_id 	= {device_recog_log_id}
					, recog_time 			= {recog_time} 
					, person_code			= {person_code}
					, person_name 			= {person_name}
					, person_type 			= {person_type}
					, access_type 			= {access_type}
					, mask 					= {mask}
					, pass 					= {pass}
					, pass_flag 			= {pass_flag}
					, temperature 			= {temperature}
					, card_bind_person_code = {card_bind_person_code}
					, card_bind_person_name = {card_bind_person_name}
					, card_no 				= {card_no}
					, card_type 			= {card_type}
					, credential_no 		= {credential_no}
					, credential_type 		= {credential_type}
					, custom 				= {custom}
					, detail 				= {detail}
					, id_card 				= {id_card}
					, picture_flag 			= 0
					, search_score          = {search_score}
					, enter_exit_type_flag	= {enter_exit_type_flag}
					, person_type_code		= {person_type_code}
					, person_description1   = {person_description1}
					, person_description2   = {person_description2}
			";
			
			$param = [];
			$param["device_id"] 			= $deviceId;
			$param["sync_log_id"] 			= SyncService::$processingSyncLogId;
			$param["sync_type"] 			= $sync_type;
			$param["device_recog_log_id"] 	= $r["ID"];
			$param["recog_time"] 			= $recogTime;
			$param["person_code"] 			= $r["PersonCode"];
			if ($device["save_recog_name_flag"]) {
				$param["person_name"] 		= $r["PersonName"];
			}
			$param["person_type"] 			= $r["PersonType"];
			$param["access_type"] 			= $r["AccessType"];
			$param["mask"] 					= $r["Mask"];
			$param["pass"] 					= $r["Pass"] ? 1 : 0;
			$param["pass_flag"] 			= $r["PassFlag"];
			$param["temperature"]	 		= ($r["Temperature"] >= 43) ? 0 : $r["Temperature"];
			$param["card_bind_person_code"] = $r["CardBindPersonCode"];
			$param["card_bind_person_name"] = $r["CardBindPersonName"];
			$param["card_no"] 				= $r["CardNo"];
			$param["card_type"] 			= $r["CardType"];
			$param["credential_no"] 		= $r["CredentialNo"];
			$param["credential_type"] 		= $r["CredentialType"];
			$param["custom"] 				= $r["Custom"];
			$param["detail"] 				= $r["Detail"];
			$param["id_card"] 				= $r["IdCard"];
			$param["search_score"] 			= isset($r["SearchScore"]) ? $r["SearchScore"] : null;
			$param["enter_exit_type_flag"]	= $device["device_role"];
			// 人物特定に成功した場合、対象者区分を取得
			if (isset($param["person_code"])) {
				$param["contractor_id"] = $device["contractor_id"];
				$select_sql = "
					select
						person_type_code
						, person_description1
						, person_description2
					from
						t_person
					where
						person_code = {person_code}
						and contractor_id = {contractor_id}
					order by
						person_id
					limit 1
				";
				$enter_exit_contents = DB::selectRow($select_sql, $param);
				if ($enter_exit_contents) {
					$param["person_type_code"]    = $enter_exit_contents["person_type_code"];
					$param["person_description1"] = $enter_exit_contents["person_description1"];
					$param["person_description2"] = $enter_exit_contents["person_description2"];
				}
			}
			
			// 近似日時でデータが存在したら削除する。
//			$deleted = RecogLogService::deleteRecogLogByNearRecogTime($device, $param);
			
			if (ApbService::isActive($device) && !empty($param["pass"])) {
				
				// 削除されていない場合、Pushを取りこぼした可能性があるため、APB修復対象に入れる。
				if (empty($deleted)) {
					
					// このデバイスが、入室専用のものである場合には修復対象にはしない。
					if ($device["apb_type"] != 3) {
					
						// 同一グループに属する全てのデバイスに対して修復情報を登録する。
						$apbRepairTargets = [];
						foreach (ApbService::getApbGroupDevices($device["device_id"], [1, 3]) as $d) $apbRepairTargets[] = $d;  
						foreach (ApbService::getApbGroupDevices($device["device_id"], [2]) as $d) $apbRepairTargets[] = $d;  
						
						foreach ($apbRepairTargets as $apbRepairTarget) {
							ApbService::registRepairTarget($apbRepairTarget, null, $param["person_code"]);	
						}
							
					}
					
				}				
			}
			
			
			// DBに登録。
			DB::insert($sql, $param);
			DB::commit();	// すぐにコミット。
			$logRegisted++;
			SyncService::$processingRegisted = true;
		}
		
		// ================================================================== 画像の取得。
		$pictureRegisted = 0;
		if ($device["save_recog_picture_flag"]) {

			// 画像未取得のデータを検索。
			$targets = DB::selectArray("select recog_log_id, device_recog_log_id, recog_time from t_recog_log where device_id = {$deviceId} and picture_flag = 0 limit $limit");
			foreach ($targets as $target) {
				
				// 制限時間を延長。
				set_time_limit(30);
				
				$picture = false;
				try {
					$picture = RecogLogService::getAccessRecordPicture($device, $target["device_recog_log_id"]);
					
				} catch (DeviceWsException $e) {
					if ($e->isConnectError) throw $e;
					
					// 画像が取得出来ない場合に「RPCFrame Error: Unknown error!」が発生するので握りつぶす
				}				
				
				if (empty($picture)) {
					// 取得出来ないのあれば諦める。
					DB::update("update t_recog_log set picture_flag = -1 where recog_log_id = {recog_log_id}", $target);
					DB::commit();
				} else {
					// S3にアップロード。
					if (AwsService::uploadS3RecogPicture($device, $target["recog_log_id"], $target["recog_time"], $picture)) {
						SyncService::$processingRegisted = true;
						$pictureRegisted++;
					}
				}
				
			}
			
		}

		// TeamSpirit連携のオプションを契約している場合は連携を開始
		$teamspiritFlag = DB::selectOne('select teamspirit_flag from m_contractor where contractor_id = {contractor_id}', $device);
		if ($teamspiritFlag) {
			
			infoLog("TeamSpiritのログ処理を開始：device_id[".$device['device_id']."]");

			foreach ($list as $r) {

				// pass_flag変換
				if (!empty($r["PassFlag"])) {
					$param['pass_flag'] = DB::selectOne("select teamspirit_pass_flag from m_recog_pass_flag where contractor_id = {$device['contractor_id']} and pass_flag = {$r["PassFlag"]}");
				} else {
					$param['pass_flag'] = NULL;
				}

				// 認証時刻
				$param['recog_time']  = date("Y/m/d H:i:s", $r["Time"]);
				$param['pass'] 		  = $r["Pass"] ? 1 : 0;
				$param["device_id"]	  = $device['device_id'];
				$param["person_code"] = $r["PersonCode"];

				/* ==========TeamSpirit連携（pass_flagが入っており、通行許可になっているログの場合連携を行う）========== */
				if (!empty($param['pass_flag']) && !empty($param["person_code"])) {

					// 既に登録されているログかどうかの確認
					$existAttendanceLog = DB::selectRow('
						select * 
						from t_attendance_log 
						where device_id = {device_id} 
						and person_code = {person_code} 
						and attendance_time = {recog_time} 
						and pass_flag = {pass_flag}
					', $param);

					if (empty($existAttendanceLog)) {

						// 通行許可である必要があるかどうかのフラグ取得
						$conditionsFlag = DB::selectOne("select conditions_set from m_teamspirit_set where contractor_id = {contractor_id}", $device);
		
						if (!$conditionsFlag) { // 通行許可必要あり
				
							// Sync時はDisplayMessageはなしで、ログ保存のみのためteamspiritFlagは3で指定
							if ($param['pass'] == 1) AttendanceLogService::attendanceByRecogTime($device, $param, 3);
							
						} else { // 通行許可不要
							
							// Sync時はDisplayMessageはなしで、ログ保存のみのためteamspiritFlagは3で指定
							AttendanceLogService::attendanceByRecogTime($device, $param, 3);
							
						}

					} else {

						infoLog("既に登録されているログのためスキップしました：device_id[".$param['device_id']."],person_code[".$param['person_code']."],recog_time[".$param['recog_time']."],pass_flag[".ltrim($param['pass_flag'], "0")."]");

					}
		
				}

			}

		}
		
		return ["recogLogRegisted"=>$logRegisted, "recogPictureRegisted"=>$pictureRegisted];
	}
 		

	// base64のjpegを取得。
	public static function getAccessRecordPicture(array $device, $recordId) {
		
 		$picRet = WsApiService::accessWsApi($device, '{"method":"accessRecord.getPicture","params":{"ID":'.$recordId.'},"id":'.WsApiService::genId().'}');

		if (empty($picRet["data"])) {
			return false;
		}
		
		return base64_decode($picRet["data"]);
	}

	public static function execPrinterAPI(array $device, array $recogData) {

		$linked_printers = DB::selectArray("
			select
				*
			from
				t_printer_linked_devices
			where 
				device_id = {device_id} 
		", $device);

		foreach ($linked_printers as $value) {

			// 連携プリンターごとに処理開始
			$printer = DB::selectRow("
			select
				*
			from
				t_printer_settings
			where 
				printer_id = {printer_id}
			", $value);

			$printer_id 	= $printer["printer_id"];
			$is_enabled 	= $printer["is_enabled"];
			$printer_name = $printer["printer_name"];
			$printer_ip 	= $printer["printer_ip"];
			$printer_port = $printer["port"];
			$timeout    	= $printer["print_timeout_ms"] ?? 10000;
			$xml_template = $printer["print_xml"];

			if (empty($xml_template)) {
				infoLog("プリンターAPIの呼び出しに失敗：printer_id[{$printer_id}]のXMLテンプレートが空です。");
				continue;
			}

			if (!$is_enabled) {
				infoLog("プリンターAPIの呼び出しに失敗：printer_id[{$printer_id}]は無効になっています。");
				continue;
			}
			
			// 変数内容の変換
			$accessTypeText;
			switch ($recogData["access_type"] ?? "") {
				case "Face": $accessTypeText = "顔認証"; break;
				case "Card": $accessTypeText = "カード認証"; break;
				case "FaceAndCard": $accessTypeText = "顔＋カード認証"; break;
				case "FirstCardAndFace": $accessTypeText = "カード先行顔認証"; break;
				default: $accessTypeText = "不明"; break;
			};

			$xml 	= '<?xml version="1.0" encoding="utf-8"?><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body>';
			$xml .= $xml_template;
			$xml .= '</s:Body></s:Envelope>';

			// プレースホルダーの置換
			$xml = str_replace("{recog_time}", $recogData["recog_time"] ?? "", $xml);
			$xml = str_replace("{person_name}", $recogData["person_name"] ?? "未登録者", $xml);
			$xml = str_replace("{person_code}", $recogData["person_code"] ?? "unknown", $xml);
			$xml = str_replace("{access_type}", $accessTypeText, $xml);
			$xml = str_replace("{card_no}", $recogData["card_no"] ?? "", $xml);
			$xml = str_replace("{temperature}", $recogData["temperature"] ?? "", $xml);
			$xml = str_replace("{person_description1}", $recogData["person_description1"] ?? "", $xml);
			$xml = str_replace("{person_description2}", $recogData["person_description2"] ?? "", $xml);

			// 送信処理開始
			$url = "http://{$printer_ip}/cgi-bin/epos/service.cgi?devid=local_printer&timeout={$timeout}";
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
					"Content-Type: text/xml; charset=utf-8"
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			if (curl_errno($ch)) {
				infoLog("プリンターAPIの呼び出しに失敗：printer_id[{$printer_id}], printer_ip[{$printer_ip}], error[".curl_error($ch)."]");
			} else {
				infoLog("プリンターAPIの呼び出しに成功：printer_id[{$printer_id}], printer_ip[{$printer_ip}], response[{$response}]");
			}
			curl_close($ch);

		}

	}
	
}
