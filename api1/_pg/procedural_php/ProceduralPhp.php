<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

// 設定ファイル
require_once(dirname(__FILE__)."/../conf/procedural_php.php");
require_once(dirname(__FILE__)."/../conf/conf.php");

// 制限時間をセット
set_time_limit(TIME_LIMIT);

// log4php用意
require_once(DIR_LIB."/log4php/Logger.php");
Logger::configure(DIR_CONF."/log4php.properties");

// 基本共通ファイルをロード
require_once(DIR_PROCEDURAL_PHP."/Globals.class.php");
require_once(DIR_PROCEDURAL_PHP."/common.php");

require_once(DIR_PROCEDURAL_PHP."/util/Session.class.php");
require_once(DIR_PROCEDURAL_PHP."/util/web.php");
require_once(DIR_PROCEDURAL_PHP."/util/date.php");
require_once(DIR_PROCEDURAL_PHP."/util/image.php");
require_once(DIR_PROCEDURAL_PHP."/util/file.php");
require_once(DIR_PROCEDURAL_PHP."/util/crypt.php");
require_once(DIR_PROCEDURAL_PHP."/util/PageInfo.class.php");
require_once(DIR_PROCEDURAL_PHP."/util/EnumManager.php");
require_once(DIR_PROCEDURAL_PHP."/util/Session.class.php");
require_once(DIR_PROCEDURAL_PHP."/util/HttpClient.class.php");
require_once(DIR_PROCEDURAL_PHP."/util/SimpleEnums.php");
require_once(DIR_PROCEDURAL_PHP."/BaseController.php");

// ロガーのデフォルト設定
setLogger(LOGGER_DEFAULT);

// セキュリティチェック
require_once(DIR_PROCEDURAL_PHP."/security.php");

require_once(DIR_PROCEDURAL_PHP."/validate/Filter.php");
require_once(DIR_PROCEDURAL_PHP."/validate/Filters.php");
require_once(DIR_PROCEDURAL_PHP."/validate/Errors.php");
require_once(DIR_PROCEDURAL_PHP."/validate/Validator.php");
require_once(DIR_PROCEDURAL_PHP."/validate/Validators.php");
require_once(DIR_PROCEDURAL_PHP."/dba/DB.class.php");

require_once(DIR_APP."/_base/appInitialize.php");

// -----------------------------------------------------------------
if (defined("BATCH")) {
	
	require_once(DIR_APP."/_base/batchInitialize.php");
// 	die;
	
} else {
	$invoked = proceduralPhpInvokeControllerInstance();
	
	if ($invoked === false) die;
	if ($invoked === true) die;
	warnLog($invoked);
	die;
}


function proceduralPhpCreateControllerInstance($mapping, &$form) {
	
	$controller = Filter::len($mapping["controller"], 50);
	$action     = Filter::len($mapping["action"], 50);
	
	if ($controller == null) $controller = "index";
	if ($action == null)     $action = "index";
		
	$className = ucfirst($controller)."Controller";
	$methodName = $action."Action";

	Globals::$controller = $className;
	Globals::$action = $methodName;
	
	$phpDir = DIR_APP.$mapping["appDir"];
	if ($mapping["subDir"]) {
		$subDir = $mapping["subDir"];
		if ($subDir) $subDir .= "/";
		if (exists($subDir, ".")) {
			warnLog("subDirにドット(.)は指定出来ません。".print_r($mapping, true));
			return false;
		}
		if (startsWith($subDir, "_")) {
			warnLog("subDirの先頭にアンダースコア(_)は指定出来ません。".print_r($mapping, true));
			return false;
		}
		
		$phpDir .= $subDir;
		
	}
	
	if (endsWith($phpDir, "/") || endsWith($phpDir, "\\")) {
		$phpPath = $phpDir.$className.".php";
	} else {
		$phpPath = $phpDir."/".$className.".php";
	}
	$php = realpath($phpPath);

	if (!file_exists($php)) {
		warnLog("phpファイルが存在しません。[{$phpPath}]");
		return false;
	}
		
	if (!startsWith($php, DIR_APP)) {
		warnLog("定数「DIR_APP」から開始されていないパスのphpファイルです。".$php);
		return false;
	}

	// phpファイルを読み込む。
	ob_start();
	require_once($php);
	ob_end_clean();
	
	if (!class_exists($className)) {
		warnLog("Controllerクラスが定義されていません。php[{$php}], className[{$className}], action[{$methodName}]");
		return false;
	}
	
	$instance = new $className();
	
	if (!method_exists($instance, $methodName)) {
		warnLog("対象メソッドが存在しません。php[{$php}], className[{$className}], action[{$methodName}]");
		return false;
	}

	$cnt = 0;
	$baseControllerExisted = false;
	$checkTarget = $instance;
	while (true) {
		
		$checkTarget = get_parent_class($checkTarget);
		if (empty($checkTarget)) break;
		
		if ($checkTarget == "BaseController") {
			$baseControllerExisted = true;
			break;
		}
		
		if ($cnt++ >= 10000) trigger_error("無限ループ防止");
	}
	
	
	if (!$baseControllerExisted) {
		warnLog("BaseControllerがextendsされていないコントローラーです。php[{$php}], className[{$className}], action[{$methodName}]");
		return false;
	}
	
	$instance->setUrlParams($mapping["urlParams"]);
	$instance->setForm($form);
	$instance->setController($className);
	$instance->setAction($methodName);
	$instance->setPhpDir($phpDir);
	
	return array("instance"=>$instance, "phpDir"=>$phpDir, "methodName"=>$methodName);
}


function proceduralPhpInvokeControllerInstance() {
	
	$url = $_SERVER["REQUEST_URI"];

	$queryPos = mb_strpos($url, "?");
	if ($queryPos !== false) {
		$url = mb_substr($url, 0, $queryPos);
	}
	
	if (mb_strpos($url, "../") !== false) return "URL_MAP_ERROR:1";
	
	if ($url == "/favicon.ico") response404();
	
	$isNotFoundPage = false;
	$mapping = proceduralPhpControllerMappings($url);
	if (empty($mapping)) {
		$isNotFoundPage = true;
		$mapping = proceduralPhpNotFoundController($url);
	}

	// siteTypeが指定されている場合には、サイト種別とロガーを設定する。
	if (!empty($mapping["siteType"])) {
		Globals::$siteType = $mapping["siteType"];
		setLogger($mapping["siteType"]);
	}
	
	$form = $_REQUEST;
	
	$result = proceduralPhpCreateControllerInstance($mapping, $form);
	
	if ($result === false) {
		if ($isNotFoundPage) return "URL_MAP_ERROR:2";
		$mapping = proceduralPhpNotFoundController($url);
		$result = proceduralPhpCreateControllerInstance($mapping, $form);
		if ($result === false) return "URL_MAP_ERROR:3";
	}
	
	$instance = $result["instance"];
	$methodName = $result["methodName"];
	$phpDir = $result["phpDir"];

	try {
		
		$initResult = true;
		// init
		if (method_exists($instance, "init")) {
			$initResult = $instance->init();
		}
	
		if ($initResult === false) {
			// no code
		} else {
			// prepare
			$tpl = $instance->prepare($form);
			
			if ($tpl != null) {
				Globals::$smarty->assign("controller", $instance);
				Globals::$smarty->assign("form", $form);
			
			} else {
				// invoke
				$tpl = $instance->$methodName($form);
		
				Globals::$smarty->assign("controller", $instance);
				Globals::$smarty->assign("form", $form);
				
				// actionAfter
				$actionAfterResult = $instance->actionAfter($form, $tpl);
				if ($actionAfterResult) {
					$tpl = $actionAfterResult;
				}
			}
			
			if ($tpl != null) {
				Globals::$tpl = $tpl; 
				Globals::$smarty->display($phpDir."/".$tpl);
			}
			
		}
		
	} catch (Exception $e) {
		if ($instance->doException($e) !== true) {
			throw $e;
		}
		
	}
	
	// after
	$instance->after($form);
	
	return true;
}


