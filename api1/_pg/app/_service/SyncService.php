<?php 


class SyncService {
	
	// 実行中のログID
	public static $processingSyncLogId;
	
	// 実行中に何か一件でも登録系処理が成功しているのであればtrue
	public static $processingRegisted;
	
	// 対象デバイスの同期処理が継続中であるかどうかを確認。
	public static function checkExclusion($deviceId) {
		
		for ($i = 1; $i <= CHECK_EXCLUSION_TRY_COUNT; $i++) {
			
			// 同期ログをチェックする。
			$log = DB::selectRow("select * from t_sync_log where device_id = {value} order by sync_log_id desc limit 1", $deviceId);
			
			if ($log == null || $log["state"] != 10) return true;	// 実行を許容。
			
			// 実行中の日時が古いようであれば、90に変更する。
			if (strtotime("-5 min") > strtotime($log["begin_time"])) {
				DB::update("update t_sync_log set state = 90 where sync_log_id = {value}", $log["sync_log_id"]);
				return true; 	// 実行を許容。
			}
			
			// トランザクションを終了して再び開始。
			DB::commit();
			DB::begin();
			
			// 1秒待つ。
			sleep(1);
		}
		
		// 実行を許容しない。
		throw new DeviceExclusionException("他のリクエストを処理中です。");
	}
	
	
	// 開始ログを登録。
	public static function insertBeginLog($deviceId, $pg_name) {
		
		$param = ["device_id"=>$deviceId, "pg_name"=>$pg_name];
		
		$id = DB::insert("
			insert into 
				t_sync_log 
			set 
				device_id    = {device_id}
				, begin_time = now()
				, pg_name    = {pg_name}
				, state      = 10
		", $param);
		
		SyncService::$processingSyncLogId = $id;
		SyncService::$processingRegisted = false;
		
		// コミット。
		DB::commit();
		DB::begin();
	}

	// 終了ログを登録。
	// 20: 成功。
	// 30: 継続出来ないエラーが発生し、中断した。データは一切登録されていない。
	// 40: 継続出来ないエラーが発生し、中断した。一部のデータは登録されている。
	public static function updateEndLog($state, $content = null) {
		
		if (empty(SyncService::$processingSyncLogId)) return;
		
		$param = ["sync_log_id"=>SyncService::$processingSyncLogId, "state"=>$state, "content"=>$content];
		
		DB::update("
			update
				t_sync_log
			set
				state = {state}
				, content = {content}
			where
			 	sync_log_id = {sync_log_id}
		", $param);
		
		SyncService::$processingSyncLogId = null;
		SyncService::$processingRegisted = null;
		
		// コミット。
		DB::commit();
		DB::begin();
	}
	
	// デバイスとDBを同期する。
	public static function syncDevice($device, $sync_type, $pg_name) {
		
		infoLog("デバイス同期処理を開始。".$device["device_id"]);
		
		$deviceId = $device["device_id"];
		
		// このデバイスが接続中かどうかを確認する。
		DeviceService::checkDevice($device);
		
		// 排他チェック。
		SyncService::checkExclusion($device["device_id"]);
		
		infoLog("同期処理を開始します。".json_encode($device, JSON_UNESCAPED_UNICODE));
		
		// 開始ログ
		SyncService::insertBeginLog($deviceId, $pg_name);
		
		$ret = [];
		
		// ---------------------- 認識ログを取得。
		infoLog("認識ログ取得を開始");
		$recogLogResult = RecogLogService::syncRecogLog($device, $sync_type);
		infoLog("認識ログ取得を終了".json_encode($recogLogResult, JSON_UNESCAPED_UNICODE));
		
		// ---------------------- 操作ログを取得。
		$operateLogResult = false;
		if ($recogLogResult !== false) {
			foreach ($recogLogResult as $k=>$v) $ret[$k] = $v;
			infoLog("操作ログ取得を開始");
			$operateLogResult = OperateLogService::syncOperateLog($device, $sync_type);
			infoLog("操作ログ取得を終了".json_encode($operateLogResult, JSON_UNESCAPED_UNICODE));
		}
		
		// 制限時間を延長。
		set_time_limit(30);

		if ($operateLogResult !== false) {
			
			foreach ($operateLogResult as $k=>$v) $ret[$k] = $v;
			
			// デバイスマスタの最終時刻を更新。
			DeviceService::updateDeviceLastTime($deviceId);
				
			// 終了ログ
			SyncService::updateEndLog(20, json_encode($ret, JSON_UNESCAPED_UNICODE));
		}
		
		infoLog("デバイス同期処理を終了。".json_encode($ret, JSON_UNESCAPED_UNICODE));
		return $ret;
	}
	
	
}


