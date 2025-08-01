<?php 
/*
接続状況の監視のためのバッチ処理
contractorによって実行時間の変更を行う
※前提としてm_contractor.getsysteminfo_timeはオプション時に入れる。それがないとアラーム設定でプルダウン表示なし（機能使えない）
*/
define("BATCH", true);
include(dirname(__FILE__)."/../../procedural_php/ProceduralPhp.php");

// 実行時間の取得
$dateMin = date("i");
// メール送信有効のcontractorの取得
$enableAlerts = DB::selectKeyRow("select * from t_alert where alert_no = 6 and enable_flag = 1", "", "contractor_id");

infoLog("GetSystemInfoBatchを開始します。");
/*==========contractor毎の処理==========*/
foreach ($enableAlerts as $contractorId => $enableAlert) {

	// 実行間隔時間の取得
	$getsysteminfoTime = DB::selectOne("select getsysteminfo_time from m_contractor where contractor_id = {value}", $contractorId);

	// 実行時の分(min)が、設定時間で割り切れない場合はスキップ
	if ($dateMin % $getsysteminfoTime) {
		continue;
	}

	// メール内容格納
	$mailSubject = explode("【以降は復旧】", $enableAlert["mail_subject"]);
	$mailBody    = explode("【以降は復旧】", $enableAlert["mail_body"]);

	/*==========実行処理==========*/
	$alertDevices = DB::selectArray("select * from t_alert_device where alert_id = {alert_id}", $enableAlert);
	foreach ($alertDevices as $index => $alertDevice) {
		
		// getSystemInfoのための変数格納
		$device = DB::selectRow("select * from m_device where device_id = {device_id}", $alertDevice);

		try {

			// getSystemInfo実行
			$ret = SystemService::getSystemInfo($device);

			if (!empty($ret) && !($device['ws_connect_flag'])) {

				DB::update("update m_device set ws_connect_flag = 1 where device_id = {device_id}", $device);

				# メール処理
				$body = $mailBody[1];
				$body = str_replace("【シリアルNo】"	, $device["serial_no"]	, $body);
				$body = str_replace("【カメラ名称】"	, $device["description"], $body);
				$body = str_replace("【確認日時】"		, date("Y/m/d H:i:s")	, $body);
				$to = [];
				if ($enableAlert["mail_1"]) $to[] = $enableAlert["mail_1"];
				if ($enableAlert["mail_2"]) $to[] = $enableAlert["mail_2"];
				if ($enableAlert["mail_3"]) $to[] = $enableAlert["mail_3"];
				execSendMailPlain($to, "facet Cloud", "noreply@fc2-cloud.com", $mailSubject[1], $body);

				infoLog("device_id[".$alertDevice["device_id"]."]：復旧したので復旧メールを送信しました。");
			}

		} catch (DeviceWsException $e) {

			if ($device['ws_connect_flag'] || $device['ws_connect_flag'] === NULL) {
				
				DB::update("update m_device set ws_connect_flag = 0 where device_id = {device_id}", $device);

				# メール処理
				$body = $mailBody[0];
				$body = str_replace("【シリアルNo】"	, $device["serial_no"]	, $body);
				$body = str_replace("【カメラ名称】"	, $device["description"], $body);
				$body = str_replace("【確認日時】"		, date("Y/m/d H:i:s")	, $body);
				$to = [];
				if ($enableAlert["mail_1"]) $to[] = $enableAlert["mail_1"];
				if ($enableAlert["mail_2"]) $to[] = $enableAlert["mail_2"];
				if ($enableAlert["mail_3"]) $to[] = $enableAlert["mail_3"];
				execSendMailPlain($to, "facet Cloud", "noreply@fc2-cloud.com", $mailSubject[0], $body);

				infoLog("device_id[".$alertDevice["device_id"]."]：接続が確認できないので接続切断通知メールを送信しました。");
			}

		}

	}

	/*
		・接続切れたらそのまま繋がるまでずっとメールを送信し続けるかどうか検討⇒一度でよい場合はm_deviceにフラグ設定必要⇒フラグ入れる
		・台数が多く間隔が短い場合マルチスレッドにする必要あり⇒初回切断時の時間とリトライ時間計測⇒マルチスレッドも検討
		・基本5分毎の間隔（8分ごとはできず、10,15,20,25,30...から選ぶ）⇒5,10,15,20,30
		・getsysteminfo_timeが入っている場合とそうでない場合の分岐を改めて見直す
		・ログをbatch.logにどこまで残すかどうか⇒getSystemInfoのような形で⇒一旦メール発砲時メールログのみ残す
		・復旧処理も実行したい
	*/
	
}