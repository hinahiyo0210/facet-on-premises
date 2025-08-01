<?php

// ------------------------------------------------------------------ include
// require_once(DIR_APP.'/_base/UserBaseController.php');
// require_once(DIR_APP.'/_base/AdminBaseController.php');
require_once(DIR_APP.'/_base/ApiBaseController.php');
require_once(DIR_APP.'/_base/Enums.php');

spl_autoload_register(function($class) {
	$class = getSafeFileName($class);
	if (endsWith($class, "Service")) {
		if (file_exists(DIR_APP.'/_service/'.$class.'.php')) {
			require_once(DIR_APP.'/_service/'.$class.'.php');
			return;
		}
		foreach (glob(DIR_APP.'/_service/*/'.$class.'.php') as $filename) {
			require_once($filename);
			return;
		}
	}
	
	if (endsWith($class, "Vo")) {
		if (file_exists(DIR_APP.'/_service/vo/'.$class.'.php')) {
			require_once(DIR_APP.'/_service/vo/'.$class.'.php');
			return;
		}
	}
	
	if (endsWith($class, "Exception")) {
		if (file_exists(DIR_APP.'/_service/exception/'.$class.'.php')) {
			require_once(DIR_APP.'/_service/exception/'.$class.'.php');
			return;
		}
	}
	
});

// ------------------------------------------------------------------ functions
// 現在日時を取得
function getNow($format = false) {
	
	if (Session::isStartedSession() && Session::exists("debug_time")) {
		$ret = Session::get("debug_time");
	} else {
		$ret = DB::selectOne("select getdate()");
	}

	if ($format !== false) {
		$ret = formatDate($ret, $format);
	}
	
	return $ret;
}

// appディレクトリの中身をinclude。(tplから呼び出す用)
function getAppPath($path) {
	return DIR_APP."/".$path;
}

