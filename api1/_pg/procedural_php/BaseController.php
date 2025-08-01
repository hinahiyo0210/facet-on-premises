<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */
abstract class BaseController {

	// リクエスト配列。actionの処理が終わればそのままsmartyにもassignされる。方針として&による参照渡しを行う。
	protected $form;
	
	// URLリライトが行われた際のパラメータ配列
	protected $urlParams;
	
	// 実行中のコントローラー名。例えば"IndexController"
	protected $controller;
	
	// 実行中のアクション名。例えば"indexAction"
	protected $action;

	// action処理の中でsmarty向けにassignした結果の配列
	protected $assigned = array();
	
	// コントローラーの実ディレクトリ
	protected $phpDir;
	
	public function init() {

		// ---------------------------------- smarty
		// smarytを用意する
		requireOnceByProcedalPhp("smarty/smarty_setup.php");
		
		$GLOBALS["smarty"] = Globals::$smarty;
		
		Globals::$smarty->assign("_controllerName", excludeSuffix($this->controller, "Controller"));
		Globals::$smarty->assign("_actionName", excludeSuffix($this->action, "Action"));
	}
	
	public function prepare(&$form) {
		
	}
		
	public function actionAfter(&$form, $tpl) {
		
	}
			
	public function after(&$form) {
		
	}
	
	public function doException(Exception $e) {
		return false;
	}
	
	public function setUrlParams($urlParams) {
		$this->urlParams = $urlParams;
	}
	
	public function setForm(&$form) {
		$this->form = &$form;
	}
	
	public function &getForm() {
		return $this->form;
	}
	
	public function setController($controller) {
		$this->controller = $controller;
	}
	
	public function setAction($action) {
		$this->action = $action;
	}
	
	public function setPhpDir($phpDir) {
		$this->phpDir = $phpDir;
	}
	
	public function assign($name, $value) {
		$this->assigned[$name] = $value;
		Globals::$smarty->assign($name, $value);
	}
	
	public function assignAll($values) {
		foreach ($values as $name=>$value) {
			$this->assigned[$name] = $value;
			Globals::$smarty->assign($name, $value);
		}
	}
	
	public function getAssigned($name = false) {
		if ($name === false) return $this->assigned;
		return $this->assigned[$name];
	}
	
	public function fetch($tpl) {
		return Globals::$smarty->fetch($this->phpDir."/".$tpl);
	}
	
	
	// 単独のBasic認証。
	protected function basicAuth($user, $pass) {
		
		$headers = getallheaders();
		
		$check = "Basic ".base64_encode($user.":".$pass);
		
		if (empty($headers["Authorization"])) {
			$auth = isset($headers["authorization"]) ? $headers["authorization"] : "";
		} else {
			$auth = $headers["Authorization"];
		}
		
		if (empty($auth) || $check != $auth) {
			header('WWW-Authenticate: Basic realm="Enter username and password."');
		    header('Content-Type: text/plain; charset=utf-8');
		    die;
		}

	}
	


	// -------------------------------------------------------- transaction token
	
	// トランザクショントークンを発行する
	public function useTransactionToken($tokenName = "") {
		
		if ($tokenName == null) $tokenName = $this->controller;
		
		$token = getRandomPassword(10);
		Session::set("___WebTransactionToken_".$tokenName, $token);
		
		$this->assign("___WebTransactionToken_", array("name"=>TRANSACTION_TOKEN_POST_NAME, "value"=>$token));
	}
	
	// トランザクショントークンを検証する
	public function validateTransactionToken($reGenerate = true, $tokenName = "") {
		
		if ($tokenName == null) $tokenName = $this->controller;
		
		$token = Session::remove("___WebTransactionToken_".$tokenName);
		$postedToken = $_POST[TRANSACTION_TOKEN_POST_NAME];
		
		if ($reGenerate) {
			$this->useTransactionToken($tokenName);
		}
		
		if ($token == null || $postedToken == null || $token != $postedToken) {
			Errors::add("", "ボタン2度押し等の不正な操作が検出されました。お手数ですが再度やり直してください。");
			return false;
		}  
		
		return true;
		
	}

	
}