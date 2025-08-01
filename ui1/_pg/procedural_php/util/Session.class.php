<?php
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 *
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 *
 */

class Session {

	private static $sessionName = "default";

	private static $timeout = 1800;

	private static $isStartedSession = false;

	private static $cookieSecure = 0;

	private static $sessionCookieName;

	private static $cookiePath = "/";

	// 設定値を設定
	public static function init($sessionName, $timeout, $cookieSecure, $sessionCookieName) {
		Session::$sessionName = $sessionName;
		Session::$timeout = $timeout;
		Session::$cookieSecure = $cookieSecure;
		Session::$sessionCookieName = $sessionCookieName;
		Session::$isStartedSession = false;
	}

	/**
	 * セッションを開始する
	 */
	public static function startSession() {

		if (Session::$isStartedSession) return;

		Session::initSession();
		session_start();

// 		// セッションID変更
// 		if (!isset($_SESSION['______expires__'])) {
// 			$_SESSION['______expires__'] = time();
// 		}
// 		if ($_SESSION['______expires__'] < time() - 7) {
// 		    session_regenerate_id(true);
// 		    $_SESSION['______expires__'] = time();
// 		}

//		Session::regenerateId();


		Session::$isStartedSession = true;

	}

	public static function isStartedSession() {
		return self::$isStartedSession;
	}

	private static function regenerateId() {

		if (!isset($_SESSION['______expires__'])) {
 			$_SESSION['______expires__'] = time();
 		}

		// N分の一の確率で再生成
		$randMax = 1;
		$rand = mt_rand(1, $randMax);
		if($rand != 1) return;

		// 5分以上経過で再生成
		$timestamp = $_SESSION['______expires__'];
		$span = 5 * 60;

		if(($timestamp + $span) < time()) {
			$_SESSION['______expires__'] = time();
			session_regenerate_id(true);
		}

	}

	private static function initSession() {

		ini_set('session.cookie_secure', Session::$cookieSecure);
//		ini_set('session.referer_check', HOST_NAME);		// SSL環境下で、Twitterなどから戻る際にセッションがクリアされしまう。
		ini_set('session.use_trans_sid', 0);
		ini_set('url_rewriter.tags', '');
		ini_set('session.serialize_handler', 'php');
		ini_set('session.use_cookies', 1);
		ini_set('session.name', Session::$sessionCookieName);
		ini_set('session.cookie_lifetime', 0);
		ini_set('session.cookie_path', Session::$cookiePath);
		ini_set('session.auto_start', 0);

		ini_set("session.gc_probability", 1);
		ini_set("session.gc_divisor", 1);
		ini_set("session.gc_maxlifetime", Session::$timeout);
		ini_set('session.cookie_httponly', true);

		//ini_set('session.save_path', TMP . 'sessions');
	}

	/**
	 * ログイン済みかをチェック
	 */
	public static function isLogined() {

		Session::startSession();

		if (empty($_SESSION[Session::$sessionName]["user"])) {
			return false;
		}
		
		$now = time(); 
		if ($now - $_SESSION[Session::$sessionName]["___lastAccess"]  > Session::$timeout) {
			self::logoutUser();
			return false;
		}
		
		$_SESSION[Session::$sessionName]["user"]["___lastAccess"] = $now; 
		return true;
	}



	/**
	 * ユーザログイン
	 */
	public static function loginUser($user) {

		Session::startSession();

// 		session_regenerate_id(true);
	
		$_SESSION[Session::$sessionName]["user"] = $user;
		$_SESSION[Session::$sessionName]["___lastAccess"] = time();
		// add-start founder yaozhengbang
		// ユーザ権限設定。
		$_SESSION[Session::$sessionName]["userFunctionAccess"] = UiFunctionAccessService::getUserFunctionAccess($user["auth_set_id"]);
		// add-end founder yaozhengbang
	}

	/**
	 * ユーザ情報に値を追加する
	 */
	public static function setLoginUserInfo($name, $data) {

		Session::startSession();

		if (!empty($_SESSION[Session::$sessionName]["user"])) {
			$_SESSION[Session::$sessionName]["user"][$name] = $data;
		}

	}


	/**
	 * ユーザログアウト
	 */
	public static function logoutUser() {

		Session::startSession();

		$keys = array();
		foreach ($_SESSION as $k=>$v) {
			$keys[] = $k;
		}
		foreach ($keys as $k) {
			unset($_SESSION[$k]);
		}

		if (Session::isLogined()) @session_destroy();
	}



	/**
	 * ログイン情報を更新する
	 * @param unknown_type $prop
	 * @param unknown_type $value
	 */
	public static function setLoginUser($prop, $value) {

		$user = Session::getLoginUser();
		if (empty($user)) return;

		$user[$prop] = $value;
		Session::loginUser($user);
	}

	/**
	 * ログインユーザ取得
	 */
	public static function getLoginUser($prop = null, $ignoreError = false) {

	    Session::startSession();

	    if (!isset($_SESSION[Session::$sessionName]["user"])) {
	        return null;
	    }

	    $user = $_SESSION[Session::$sessionName]["user"];

	    if ($prop != null) {
	        if ($ignoreError) {
	            return arr($user, $prop);
	        } else {
	            return $user[$prop];
	        }
	    }
	    return $user;
	}





	/**
	 * セッションに値をセット
	 * @param string $name
	 * @param string $value
	 */
	public static function set($name, $value) {
		Session::startSession();

		if (!isset($_SESSION[Session::$sessionName]["session_values"])) {
			$_SESSION[Session::$sessionName]["session_values"] = array();
		}

		$_SESSION[Session::$sessionName]["session_values"][$name] = $value;

		return $value;
	}

	/**
	 * セッションから値を取得
	 * @param string $name
	 */
	public static function get($name) {
		Session::startSession();

		if (!isset($_SESSION[Session::$sessionName])) {
			return null;
		}

		if (!isset($_SESSION[Session::$sessionName]["session_values"][$name])) {
			return null;
		}

		return $_SESSION[Session::$sessionName]["session_values"][$name];

	}

	/**
	 * セッションの値の存在可否をチェック
	 * @param string $name
	 */
	public static function exists($name) {

		return Session::get($name) != null;
	}

	/**
	 * セッションの値を削除
	 * @param string $name
	 */
	public static function remove($name) {
		Session::startSession();

		$ret = Session::get($name);
		unset($_SESSION[Session::$sessionName]["session_values"][$name]);
		return $ret;
	}

	public static function removeAll($excludes = array()) {

		Session::startSession();

		if (empty($excludes)) {
			$_SESSION[Session::$sessionName]["session_values"] = array();
			return;
		}

		if (!empty($_SESSION[Session::$sessionName]["session_values"])) {
			foreach ($_SESSION[Session::$sessionName]["session_values"] as $k=>$v) {
				if (in_array($k, $excludes)) {
					continue;
				}
				unset($_SESSION[Session::$sessionName]["session_values"][$k]);
			}
		}

	}



	/**
	 * セッションにメッセージを格納し、キーを返す
	 * @param unknown_type $msg
	 */
	public static function setMessage($msg) {
		return self::setData($msg, "messages");
	}

	/**
	 * セッションに格納されたメッセージからキーに該当するものを返す。
	 * @param unknown_type $key
	 */
	public static function getMessage($key, $delete = true) {
		return self::getData($key, $delete, "messages");
	}

	
	/**
	 * セッションにメッセージを格納し、キーを返す
	 * @param unknown_type $msg
	 */
	public static function setData($msg, $name = "_data") {

		static $seq;
		if (empty($seq)) $seq = 1;
		$seq++;

		$queue = null;

		if (isset($_SESSION[Session::$sessionName][$name])) {
			$queue = $_SESSION[Session::$sessionName][$name];
		} else {
			$queue = array();
		}

		foreach ($queue as $k=>$m) {
			if ($m == $msg) $k;
		}

		// セッションにメッセージが溜まりすぎる事を防止する。
		// 常に最大10個までのメッセージしか保持しないようにする。
		while (count($queue) >= 10) {
			foreach ($queue as $k=>$v) {
				unset($queue[$k]);
				break;
			}
		}

		do {
			$key = getRandomPassword(3);
		} while (isset($queue[$key]));

		$queue[$key] = $msg;

		$_SESSION[Session::$sessionName][$name] = $queue;

		return $key;
	}

	/**
	 * セッションに格納されたメッセージからキーに該当するものを返す。
	 * @param unknown_type $key
	 */
	public static function getData($key, $delete = true, $name = "_data") {

		$key = Filter::len($key, 3);
		
		if (isset($_SESSION[Session::$sessionName][$name][$key])) {
			$msg = $_SESSION[Session::$sessionName][$name][$key];
			if ($delete) {
				unset($_SESSION[Session::$sessionName][$name][$key]);
			}
			return $msg;
		}

		return null;
	}

	public static function replaceData($key, $data, $name = "_data") {
		$key = Filter::len($key, 3);
		$_SESSION[Session::$sessionName][$name][$key] = $data;
	}
	
	/**
	 * 
	 * @param unknown_type $msg
	 */
	public static function clearData($name = "_data") {
		unset($_SESSION[Session::$sessionName][$name]);
	}
	
	
	
	public static function existsMessage($key) {
		$msg = Session::getMessage($key, false);
		return $msg != null;
	}


	public static function existsMessages() {
		return !empty($_SESSION[Session::$sessionName]["messages"]);
	}

	/**
	 * セッションに格納された全てのメッセージを返す
	 */
	public static function getAllMessages($delete = false) {
		if (isset($_SESSION[Session::$sessionName]["messages"])) {
			$ret = $_SESSION[Session::$sessionName]["messages"];
			if ($delete) {
				unset($_SESSION[Session::$sessionName]["messages"]);
			}
			return $ret;
		}
		return array();
	}


	// add-start founder yaozhengbang
	/**
	 * ユーザ権限設定を取得。
	 * @param indexName = "parent_function_id" || "function_id" || "function_name"
	 */
	public static function getUserFunctionAccess($indexName = false) {
		$funAccess = $_SESSION[Session::$sessionName]["userFunctionAccess"] ?? null;
		return ($indexName || $funAccess) ? array_column($funAccess, $indexName) : null;
	}
	// add-end founder yaozhengbang
}


