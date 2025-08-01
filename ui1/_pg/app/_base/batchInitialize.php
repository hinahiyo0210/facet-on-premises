<?php 

// ロガーをbacthに切り替える
setLogger("batch");

// 多重起動をファイルで防止
if (!empty($BACTH_LOCK)) {
	$dir = DIR_TMP."/batch_lock";
	if (!file_exists($dir)) mkdir($dir);
	
	$path = $dir."/".$BACTH_LOCK["name"];
	$lockTime = false;
	if (file_exists($path)) $lockTime = file_get_contents($path);
	$limit = strtotime($BACTH_LOCK["limit"]);
	if ($lockTime === false) {
		// ロック無し。ロック作成
		file_put_contents($path, date("Y/m/d H:i:s"));
	} else if (strtotime($lockTime) < $limit) {
		// ロック有効期限切れ。ロック作成
		warnLog("ロックファイル[".$path."]が存在しましたが、内容が[".$lockTime."]であり、有効期限である[".formatDate($limit, "Y/m/d H:i:s")."]より過去であるため処理を続行します。");
		file_put_contents($path, date("Y/m/d H:i:s"));
	} else {
		// 有効なロック有り
		warnLog("ロックファイル[".$path."]が[".$lockTime."]で存在しているため、処理を中断します。");
		die;
	}
	$BACTH_LOCK["path"] = $path;
}


// ---------------------------------- DB
DB::init(DB_DRIVER, DB_HOST, DB_USER, DB_PASS, DB_NAME);	// ここではまだDBに接続される訳では無い。初回利用時に接続される。

// 終了処理を予約。
register_shutdown_function("shutdownBatch");

function shutdownBatch() {
	global $BACTH_LOCK;

	if (function_exists("doBatchEnd")) {
		doBatchEnd();
	}
	
	// ロックファイル削除
	if (!empty($BACTH_LOCK)) {
		unlink($BACTH_LOCK["path"]);
	}
	
	infoLog("バッチを終了します。");
}
