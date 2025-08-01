<?php 
// --------------------------------------- [ディレクトリ位置]
// アプリケーションルートディレクトリ
define("DIR_ROOT", realpath(dirname(__FILE__)."/../"));

// procedural_phpディレクトリ
define("DIR_PROCEDURAL_PHP", realpath(DIR_ROOT."/procedural_php"));

// appディレクトリ
define("DIR_APP", realpath(DIR_ROOT."/app"));

// libディレクトリ
define("DIR_LIB", realpath(DIR_ROOT."/lib"));

// 設定ファイルディレクトリ
define("DIR_CONF", realpath(DIR_ROOT."/conf"));

// 作業ディレクトリ
define("DIR_TMP", realpath(DIR_ROOT."/tmp"));

// ドキュメントルートディレクトリ
define("DIR_FRONT", realpath(DIR_ROOT."/../"));

// 管理サイトルートディレクトリ
define("DIR_ADMIN", realpath(DIR_FRONT."/admin"));

// メールテンプレートディレクトリ
define("DIR_MAIL_TEMPLATE", realpath(DIR_ROOT."/mail_template"));

// データディレクトリ
define("DIR_DATA", realpath(DIR_ROOT."/data"));

// トランザクショントークンのリクエストパラメータ名
define("TRANSACTION_TOKEN_POST_NAME", "BtRaMbU0kVIqjB");



// ---------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------- 環境や要件によって実装の異なる関数
// ---------------------------------------------------------------------------------------------------------------------
// アクセス元を取得。
function getRemoteAddr() {
	$ip = getenv("REMOTE_ADDR");
	if (arr($_SERVER, "HTTP_X_FORWARDED_PROTO") == "https") {	// AWSバランサー
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	return $ip;
}

/**
 * 詳細なログ出力用文字列を取得。
 * @return string ログ文字列
 */
function getDetailAccessLog() {

	// ログ登録の際に、マスクするリクエストパラメータ名(入力されたパスワードやクレジットカード番号をテキストログに残さないようにする)
	$masks = array();
	$masks["pass"] 					= 1;
	$masks["password"] 				= 1;
	$masks["confPass"] 				= 1;
	$masks["confPassword"] 			= 1;
	$masks["confirmPass"] 			= 1;
	$masks["confirmPassword"] 		= 1;
	$masks["old_password"] 			= 1;
	$masks["current_password"] 		= 1;
	$masks["new_password"] 			= 1;
	$masks["new_password_confirm"]  = 1;
	$masks["base64"]  = 1;
	$masks["data"]  = 1;
	
	// ファイルアップロードのデータなど、大きすぎる事が見込まれるパラメータ名。テキストログにはバイト数のみを出力する。
	$uploads = array();
	$uploads["logo_file"]      	     = 1;
	$uploads["regulation_file"]      = 1;
	$uploads["prev_regulation_file"] = 1;
	$uploads["picture"] = 1;
	$uploads["data"] = 1;
	$uploads["new_picture"] = 1;
	$uploads["list_mod_picture"] = 1;
		
	$ip = getRemoteAddr();
	
	$user = "";
	if (Session::isStartedSession()) {
		if (Globals::$siteType == "admin") {
			$user = Session::getLoginUser("admin_id", true);
		} else {
			$user = Session::getLoginUser("user_id", true);
		}
	}
	
// 	$headers = [];
// 	foreach (getallheaders() as $k=>$v) {
// 		$headers[] = "{$k}={$v}";
// 	}
	
	$url = @getRequestUrl();
	if ($url == "") {
		
	}
	
	$val = "IP[".$ip."], URL[".$url."], ";
	if (defined("CONTROLLER_TYPE") && CONTROLLER_TYPE == "api") {
		$val .= "H-TOKEN[".arr($_SERVER, "HTTP_DS_API_TOKEN")."] ";
		$val .= "R-TOKEN[".arr($_REQUEST, "ds-api-token")."] ";
	}
	$val .= "REQUEST[";
	
	foreach ($_REQUEST as $k=>$v) {
		if (isset($masks[$k])) {
			$val .= "{$k}=*****, ";
			continue;
		} 
		
		if (isset($uploads[$k])) {
			if (is_array($v)) {
				foreach ($v as $localK=>$localV) {
					$val .= "{$k}[{$localK}]=<".strlen($localV)." bytes>, ";
				}
			} else {
				$val .= "{$k}=<".strlen($v)." bytes>, ";
			}
			continue;
		} 
		
		
		if (is_array($v)) {
			foreach ($v as $localK=>$localV) {
				$val .= "{$k}[{$localK}]={$localV}, ";
			}
			
		} else {
			$val .= "{$k}={$v}, ";
		}
	}
	
	$val .= "]";
	
	return $val;
}

// --------------------------------------- [コントローラー]

// URLマッピング(該当アクション無しの場合)
function proceduralPhpNotFoundController($url) {
	return array("controller"	=>"index", "action"=>"notFound");
}

// システムエラー時の処理
function proceduralPhpInternalServerError($e) {
	http_response_code(500);
	echo "ERROR";
	die;
}

// URLマッピング
// https://www.example.com/aaa/bbbの場合、$urlには"/aaa/bbb"が格納されている。
function proceduralPhpControllerMappings($url) {
	
	// URL判定用の正規表現
	$mappings = array(

		/**
		 * /
		 * $m[0] = /ui1/device/getDevice/X0001
		 * $m[1] = device
		 * $m[2] = getDevice
		 * $m[3] = X0001
		 * 
		 */
		[
			"regex"			=>"*^/ui1/(\\w+)/(\\w+)/(\\w+)?$*"
			, "appDir"		=>"/"
			, "subDir"		=>[1]
			, "controller"	=>1
			, "action"		=>2
			, "params"		=>["id"=>3]
		],

		/**
		 * /
		 * $m[0] = /ui1/device/getDevice
		 * $m[1] = device
		 * $m[2] = getDevice
		 * 
		 */
		[
			"regex"			=>"*^/ui1/(\\w+)/(\\w+)?$*"
			, "appDir"		=>"/"
			, "subDir"		=>[1]
			, "controller"	=>1
			, "action"		=>2
			, "params"		=>[]
		],

		/**
		 * /
		 * $m[0] = /ui1/auth
		 * $m[1] = auth
		 * 
		 */
		[
			"regex"			=>"*^/ui1/(\\w+)?$*"
			, "appDir"		=>"/"
			, "subDir"		=>null
			, "controller"	=>"index"
			, "action"		=>1
			, "params"		=>[]
		],
		
		
	);
	
	// ---------------------------------------- 特殊処理が必要な場合にはここにベタ実装で。
	
	// ---------------------------------------- 正規表現によるURL判定
	foreach ($mappings as $mapping) {
		
		$m = array();
		if (!preg_match($mapping["regex"], $url, $m)) continue;
		
		if (Filter::digit($mapping["controller"], false, -1) === false) {
			$controller = $mapping["controller"];
		} else {
			$controller = (isset($m[$mapping["controller"]])) ? $m[$mapping["controller"]] : NULL;
		}
		
		if (Filter::digit($mapping["action"], false, -1) === false) {
			$action = $mapping["action"];
		} else {
			$action = (isset($m[$mapping["action"]])) ? $m[$mapping["action"]] : NULL;
		}
		
		$urlParams = array();
		foreach ($mapping["params"] as $paramName=>$paramIndex) {
			if (!isset($m[$paramIndex])) continue;
			$urlParams[$paramName] = $m[$paramIndex];
		}
		
		$subDir = null;
		if ($mapping["subDir"]) {
			if (is_array($mapping["subDir"])) {
				$subDirArr = array();
				foreach ($mapping["subDir"] as $idx) {
					$subDirArr[] = $m[$idx];
				}
				$subDir = join("/", $subDirArr);
				
			} else {
				$subDir = $mapping["subDir"];
				
			}
		}
		
		$ret = array(
			"controller"=>$controller
			, "action"=>$action
			, "urlParams"=>$urlParams
			, "appDir"=>(isset($mapping["appDir"])) ? $mapping["appDir"] : NULL
			, "subDir"=>$subDir
			, "siteType"=>(isset($mapping["siteType"])) ? $mapping["siteType"] : NULL
		);
		
		return $ret;
	}
	
}



