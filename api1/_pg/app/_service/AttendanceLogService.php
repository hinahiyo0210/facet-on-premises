<?php
require_once '/var/www/html/api1/_pg/app/_service/TeamSpirit/oauth.php';

class AttendanceLogService {

	// ログのenumを作成。
	public static function createAttendanceLogEnum() {
		
		SimpleEnums::add("attendance_log_type", [
			"I01"=>"リアルタイムでの連携処理が完了しました。"
			, "I02"=>"バッチでの連携処理が完了しました。"
			, "I03"=>"WebUIでの連携処理が完了しました。"
			, "I04"=>"未連携のログとして保存しました。"
			
			, "W01"=>"認証したユーザーIDは連携先に存在しません。"
			
			, "E01"=>"OAuth認証に失敗しました。"
			, "E02"=>"連携処理に失敗しました。"
		]);
		
	}
	
	// ログを検索。
	public static function getAttendanceLogList($pageInfo, $contractor_id, $data, $devices) {

		// カメラが選択されていない場合、空の配列を返却
		if (empty($data["device_ids"])) return [];
		
		$data["contractor_id"] = $contractor_id;

		$where = " where a.device_id in {in device_ids} and a.contractor_id = {contractor_id}";
		
		if ($data["date_from"])	{
			$where .= " and a.attendance_time >= {date_from}";
		}
		if ($data["date_to"]) {
			$data["date_to"] = addDate($data["date_to"], "+1 day");
			$where .= " and a.attendance_time < {date_to}";
		}
		if ($data["log_type"]) {
			$where .= " and a.log_type = {log_type} ";
		}
		if ($data["log_level"]) {
			$where .= " and a.log_type like {like_R log_level} ";
		}
		if ($data["decision"]) {
			$where .= ($data["decision"] == "OK") ? " and a.decision = 1 " : " and a.decision <> 1 ";
		}
		if (($data["person_code"] === "0") || $data["person_code"]) {
			$where .= " and a.person_code like {like_LR person_code} ";
		}
		
		$sql = "
			select 
				a.attendance_log_id
				, a.create_time
				, a.attendance_time
				, a.device_id
				, a.person_code
				, a.pass_flag
				, a.log_type
				, a.decision
				, p.person_name
			from
				t_attendance_log a 
				
				left outer join t_person p on 
				p.contractor_id   = {contractor_id}
				and p.person_code = a.person_code

			$where
		
		";
		$order = "
			order by
				attendance_time desc
		";
		
		$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);
		
		return $list;
	}

	public static function attendanceByRecogTime($device, $param, $teamspiritFlag) {

		/* ==========TeamSpiritへの連携処理========== */

		// OAuth認証のための情報取得
		$oauthSet = DB::selectRow('select * from m_teamspirit_set where contractor_id = {contractor_id}', $device);

		// OAuth認証ファイルの格納場所の作成
		$oauthDir = __DIR__.'/TeamSpirit/oauthFile/'.$device['contractor_id'];
		if (!file_exists($oauthDir)) mkdir($oauthDir, 0777);

		// OAuth認証（パスワード）のためのインスタンス化
		$oauth = new oauth(CLIENT_ID, CLIENT_SECRET, CALLBACK_URL, $device['contractor_id']);
		
		if ($oauth->auth_with_password($oauthSet['user_name'], $oauthSet['password'])) {

			/*===人物がTeamSpiritに存在するかを確認===*/
			$url = $oauth->instance_url."/services/data/v55.0/query/?q=SELECT+ID,+teamspirit__EmpCode__c+FROM+teamspirit__AtkEmp__c+WHERE+teamspirit__EmpCode__c+IN+('{$param['person_code']}')";
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$oauth->access_token,"Content-Type: application/json"));
			$response = json_decode(curl_exec($curl), true);
			$status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			if ($status >= 300) {

				warnLog("TeamSpiritへの連携でエラーが発生しました：".json_encode($response));
				$param['decision'] = 2;
				$param['log_type'] = "E02";

			} elseif (empty($response['totalSize'])) {

				warnLog("TeamSpiritに指定の人物コードは存在しません：personCode[".$param['person_code']."]");
				$param['decision'] = 1;
				$param['log_type'] = "W01";
				$param['Customtip'] = 'TeamSpiritにユーザーが存在しません';

			} else {

				// リアルタイム打刻を使用するかを判定
				if ($teamspiritFlag == 1) {
		
					$tsParams = [];
					$tsParams['teamspirit__EmpCode__c']      = $param['person_code'];
					$tsParams['teamspirit__StampedDate__c']  = date(DATE_ISO8601, strtotime($param['recog_time']));
					$tsParams['teamspirit__StampType__c']    = (int)$param['pass_flag'];
					// $tsParams['teamspirit__IpAddress__c']    = "203.181.17.17";
					// $tsParams['teamspirit__Location__latitude__s'] = 35.681167;
					// $tsParams['teamspirit__Location__longitude__s'] = 139.767052;
					$url = $oauth->instance_url."/services/data/v55.0/sobjects/teamspirit__ExternalAttendance__c/";
		
					// 打刻API実行
					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($tsParams));
					curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$oauth->access_token,"Content-Type: application/json"));
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					$response = json_decode(curl_exec($curl), true);
					$status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					curl_close($curl);
		
					// 実行結果によって分岐
					if ($status < 300) {
						
						// TeamSpirit連携が完了した処理
						infoLog("TeamSpiritへの連携を完了しました：person_code[".$param['person_code']."],stampedDate[".date(DATE_ISO8601, strtotime($param['recog_time']))."],pass_flag[".ltrim($param['pass_flag'], "0")."]");
						$param['decision'] = 1;
						$param['log_type'] = "I01";
						$param['Customtip'] = 'TeamSpiritへの連携を完了しました';
		
					} else {
						
						warnLog("TeamSpiritへの連携でエラーが発生しました：".json_encode($response));
						$param['decision'] = 2;
						$param['log_type'] = "E02";
		
					}
		
					
				} else {
					
					infoLog("TeamSpiritへの連携はせず未連携としてログを保存しました：person_code[".$param['person_code']."],stampedDate[".date(DATE_ISO8601, strtotime($param['recog_time']))."],pass_flag[".ltrim($param['pass_flag'], "0")."]");
					$param['decision'] = 0;
					$param['log_type'] = "I04";
					$param['Customtip'] = 'TeamSpiritの未連携ログとして保存しました';
					
				}
				
			}


		} else {

			// OAuth認証が失敗した場合の処理
			warnLog("TeamSpiritへのOAuth認証が失敗しました：person_code[".$param['person_code']."],stampedDate[".date(DATE_ISO8601, strtotime($param['recog_time']))."],pass_flag[".ltrim($param['pass_flag'], "0")."]");
			$param['decision'] = 2;
			$param['log_type'] = "E01";

		}

		// DBへの登録処理
		$sql = "
			insert into 
				t_attendance_log
			set
				create_time				= now()
				, update_time			= now()
				, contractor_id			= {contractor_id}
				, attendance_time		= {recog_time}
				, device_id				= {device_id}
				, person_code			= {person_code}
				, pass_flag				= {pass_flag}
				, log_type				= {log_type}
				, decision				= {decision}
		";

		DB::insert($sql, $param);
		DB::commit();

		// デバイスへ状態画面表示（teamspirit_flagが3の場合はデバイス画面へ表示なし(Sync時にのみ指定)）
		if ($teamspiritFlag != 3) {

			$customTipParams = [];
			$customTipParams['Type']      		= 'tips';
			$customTipParams['Customtip'] 		= ($param['decision'] == 2) ? 'TeamSpiritへの連携が失敗しました' : $param['Customtip'];
			$customTipParams['Tipstime']  		= 5000;
			$customTipParams['BorderColor']  	= [255,255,255,255];
			$customTipParams['BackgroundColor'] = ($param['decision'] == 2) ? [255,0,0,200] : [40,93,242,200];
			SystemService::displayMessage($device, $customTipParams);

		}
		
	}

	// バッチもしくはUIからの連携処理（UIからの実行では$alignmentFlagはtrueで実行する）
	public static function attendanceBatchAlignment($contractorId, $addParam = null, $alignmentFlag = false) {

		// OAuth認証のための情報取得
		$oauthSet = DB::selectRow('select * from m_teamspirit_set where contractor_id = {value}', $contractorId);

		// OAuth認証ファイルの格納場所の作成
		$oauthDir = __DIR__.'/TeamSpirit/oauthFile/'.$contractorId;
		if (!file_exists($oauthDir)) mkdir($oauthDir, 0777);
		
		// 結果初期値
		$result = [];
		$result['result'] = false;

		// // OAuth認証（パスワード）
		$oauth = new oauth(CLIENT_ID, CLIENT_SECRET, CALLBACK_URL, $contractorId);
		if (!($oauth->auth_with_password($oauthSet['user_name'], $oauthSet['password']))) {
			$result['message'] = "OAuth認証が失敗したので連携処理を終了します："."OAuth認証失敗";
			return $result;
		}
		
		if (!$alignmentFlag) { // バッチ処理

			/* パラメータ（複数）*/
			$betweenTime = [];
			$betweenTime['start'] = date("Y-m-d H:i:s",strtotime("-24 hour"));
			$betweenTime['end']   = date('Y-m-d H:i:s');
			$attendancePersons = DB::selectArray('select * from t_attendance_log where decision = 0 and attendance_time between {start} and {end}', $betweenTime);

			// 連携対象がいない場合はバッチ処理を終了する
			if (empty($attendancePersons)) {
				$result['result']  = true;
				$result['message'] = "TeamSpirit連携対象がいないため連携処理を終了します：取得期間[".$betweenTime['start'].'～'.$betweenTime['end']."]";
				return $result;
			}
			
		} else { // UIからの処理
			
			// パラメーター格納
			$attendanceLogIds = [];
			$attendanceLogIds['attLogId'] = $addParam;
			$attendancePersons = DB::selectArray('select * from t_attendance_log where attendance_log_id in {in attLogId}', $attendanceLogIds);

		}
		
		// レコード規定パラメータ格納
		$records = [];
		$attributes = [];
		$attributes['type'] = 'teamspirit__ExternalAttendance__c';
		$attendanceCheckPersons = [];
		foreach ($attendancePersons as $index => $attendancePerson) {
			
			// UI上からの場合、「連携エラーの人物」に限り存在有無確認を行う
			if ($alignmentFlag) {

				if (($attendancePerson['decision'] == 2) && !(AttendanceLogService::existsAttendancePerson($attendancePerson))) {
					continue;
				}

				// UI上から人物存在確認後問題なしもしくはUIからのdecision2以外の場合は再度配列格納
				array_push($attendanceCheckPersons, $attendancePerson);

			}

			// 連番
			$attributes['referenceId'] = "ref".($index+1);
		
			// 各人物レコード格納
			$record = [];
			$record['attributes']                  = $attributes;
			$record['teamspirit__EmpCode__c']      = $attendancePerson['person_code'];
			$record['teamspirit__StampedDate__c']  = date(DATE_ISO8601, strtotime($attendancePerson['attendance_time']));
			$record['teamspirit__StampType__c']    = $attendancePerson['pass_flag'];
		
			// 配列へ追加
			array_push($records, $record);
		}
		
		$params  = [];
		$params['records'] = $records;
		
		/*=== 複数 ===*/
		$url = $oauth->instance_url . "/services/data/v55.0/composite/tree/teamspirit__ExternalAttendance__c/";
		
		/* =====打刻の実行===== */
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$oauth->access_token,"Content-Type: application/json"));
		curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
		$response = json_decode(curl_exec($curl), true);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		
		/*========== DBへ登録 ==========*/
		
		$sql = "update t_attendance_log set log_type = {log_type}, decision = {decision}, update_time = now() where attendance_log_id = {attendance_log_id}";
		
		if ($status < 300) {
		
			if ($alignmentFlag) {
				foreach ($attendanceCheckPersons as $attendanceCheckPerson) {
					$attendanceCheckPerson['log_type'] = "I03";
					$attendanceCheckPerson['decision'] = 1;
					DB::update($sql, $attendanceCheckPerson);
				}
			} else {
				foreach ($attendancePersons as $attendancePerson) {
					$attendancePerson['log_type'] = "I02";
					$attendancePerson['decision'] = 1;
					DB::update($sql, $attendancePerson);
				}
				$result['message'] = 'TeamSpiritへの連携が完了しました：取得期間['.$betweenTime['start'].'～'.$betweenTime['end'].'],連携数['.count($attendancePersons).']';
			}
			DB::commit();

			$result['result']  = true;
			return $result;
		
		} else {
		
			if ($alignmentFlag) {
				foreach ($attendanceCheckPersons as $attendanceCheckPerson) {
					$attendanceCheckPerson['log_type'] = "E02";
					$attendanceCheckPerson['decision'] = 2;
					DB::update($sql, $attendanceCheckPerson);
				}
			} else {
				foreach ($attendancePersons as $attendancePerson) {
					$attendancePerson['log_type'] = "E02";
					$attendancePerson['decision'] = 2;
					DB::update($sql, $attendancePerson);
				}
				$result['message'] = 'TeamSpiritへの連携が失敗しました：取得期間['.$betweenTime['start'].'～'.$betweenTime['end'].'],連携数['.count($attendancePersons).']';
			}
			DB::commit();

			$result['result']  = false;
			return $result;
		
		}

	}

	// TeamSpirit側に存在するかの確認。いない場合はDB更新を行いfalseを返す。
	private static function existsAttendancePerson($param) {

		// OAuth認証のための情報取得
		$oauthSet = DB::selectRow('select * from m_teamspirit_set where contractor_id = {contractor_id}', $param);

		// OAuth認証（パスワード）のためのインスタンス化
		$oauth = new oauth(CLIENT_ID, CLIENT_SECRET, CALLBACK_URL, $param['contractor_id']);
		
		if ($oauth->auth_with_password($oauthSet['user_name'], $oauthSet['password'])) {

			/*===人物がTeamSpiritに存在するかを確認===*/
			$url = $oauth->instance_url."/services/data/v55.0/query/?q=SELECT+ID,+teamspirit__EmpCode__c+FROM+teamspirit__AtkEmp__c+WHERE+teamspirit__EmpCode__c+IN+('{$param['person_code']}')";
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$oauth->access_token,"Content-Type: application/json"));
			$response = json_decode(curl_exec($curl), true);
			$status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			if ($status >= 300) {

				warnLog("TeamSpiritへの連携でエラーが発生しました：".json_encode($response));
				$param['decision'] = 2;
				$param['log_type'] = "E02";

			} elseif (empty($response['totalSize'])) {

				warnLog("TeamSpiritに指定の人物コードは存在しません：personCode[".$param['person_code']."]");
				$param['decision'] = 1;
				$param['log_type'] = "W01";

			} else {

				// 何も問題ない場合はture
				return true;

			}
		
			$sql = "update t_attendance_log set log_type = {log_type}, decision = {decision}, update_time = now() where attendance_log_id = {attendance_log_id}";
			DB::update($sql, $param);
			DB::commit();
			return false;

		} else {

			// OAuth認証が失敗した場合の処理
			warnLog("TeamSpiritへのOAuth認証が失敗しました：person_code[".$param['person_code']."]");
			$param['decision'] = 2;
			$param['log_type'] = "E01";
			$sql = "update t_attendance_log set log_type = {log_type}, decision = {decision}, update_time = now() where attendance_log_id = {attendance_log_id}";
			DB::update($sql, $param);
			DB::commit();
			return false;

		}

	}

	// OAuth認証の確認
	public static function oauthCheck($param) {

		// OAuth認証（パスワード）のためのインスタンス化
		$oauth  = new oauth(CLIENT_ID, CLIENT_SECRET, CALLBACK_URL, $param['contractor_id']);

		// OAuth認証ファイルの格納場所の作成
		$oauthDir = __DIR__.'/TeamSpirit/oauthFile/'.$param['contractor_id'];
		if (!file_exists($oauthDir)) mkdir($oauthDir, 0777);

		// OAuth認証で既に発行されているaccess_tokenがある場合は削除する
		if (file_exists($oauthDir."/access_token")) {
			unlink($oauthDir."/access_token");
			unlink($oauthDir."/instance_url");
			unlink($oauthDir."/refresh_token");
		}
		
		if ($oauth->auth_with_password($param['user_name'], $param['password'])) {

			// OAuth認証が成功した場合の処理
			infoLog("OAuth認証に成功しました。：userName[".$param['user_name']."],contractor[".$param['contractor_id']."]");
			return true;
			
		} else {
			
			// OAuth認証が失敗した場合の処理
			infoLog("OAuth認証に失敗しました。：userName[".$param['user_name']."],contractor[".$param['contractor_id']."]");
			return false;

		}

	}

}
