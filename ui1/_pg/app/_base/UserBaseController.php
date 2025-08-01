<?php

abstract class UserBaseController extends BaseController {

	// ログイン者の情報
	protected $user_id;
	
	protected $contractor_id;
	
	
	// @Override
	public function init() {
		parent::init();
		
		// ---------------------------------- Session
		Session::init(SESSION_NAME_FRONT, SESSION_TIMEOUT_FRONT, SESSION_COOKIE_SECURE_FRONT, SESSION_COOKIE_NAME_FRONT);
		
		// ---------------------------------- DB
		DB::init(DB_DRIVER, DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 		// ログインしていないようであれば、cookieによる自動ログインを試す
//  		if (!Session::isLogined() && empty($byAdmin)) {
//  			$emp_no = AutoLoginService::restore();
//  			if ($emp_no != null) { 
// 	 			Session::loginUser(array("emp_no"=>$emp_no));
//  			}
//  		}
		
		// ログインチェック
		if (!defined("NO_LOGIN") || !NO_LOGIN) {
			if (!Session::isLogined()) {
				if (endsWith(URL_PREFIX, "/")) {
					sendRedirect(URL_PREFIX);
				} else {
					sendRedirect(URL_PREFIX."/");
				}
			}
		}
		
		// 全ての操作を記録する。
		$log = "UI_USER_ID[".Session::getLoginUser("user_id", true)."] ".getDetailAccessLog();
		$byAdmin = Session::get("byAdmin");
		if (!empty($byAdmin)) {
			$log = "BY_ADMIN[".$byAdmin["user_id"]."] ".$log;
		}
		infoLog($log);
		
		// デフォルトのDBパラメータ
		DB::setDefaultParameters(array(
			"login_user_id"=>Session::getLoginUser("user_id")
		));

		// ----------------- メンテナンスモード判定。
		if (!empty($_POST["mainte-ignore"])) {
			if ($_POST["mainte-ignore"] == MAINTE_IGNORE_COMMAND) {
				Session::set("MAINTE-IGNORE", 1);
			}
		}
		if (MAINTE_MODE && !Session::exists("MAINTE-IGNORE")) {
			Globals::$smarty->display(DIR_APP."/mainte.tpl");
			die;
		}
		// -----------------
		
		// 最終アクセスを登録
		if (Session::isLogined() && empty($byAdmin)) {
			UserService::updateAccessTime();
		}
 		
 		if (Session::isLogined()) {
 			// ログイン済みである場合は、セッション情報をリフレッシュする
 			$user = UserService::getUser(Session::getLoginUser("user_id"));
 
 			if (empty($user)) {
 				Session::logoutUser();
 			} else {
	 			// IPアドレス制限が設定されている場合には、チェックを行う。
 				if (!empty($user["allow_ips"])) {
					$ip = getRemoteAddr();
					$allow = false; 					
					foreach (explode(",", $user["allow_ips"]) as $allowIp) {

					   if (strpos($allowIp, '/') !== false) {

						   list($accept_ip, $mask) = explode('/', $allowIp);
						   $accept_long = ip2long($accept_ip) >> (32 - $mask);
						   $remote_long = ip2long($ip) >> (32 - $mask);
						   if ($accept_long == $remote_long) {
							   $allow = true;
							   break;
						   }

					   } else {
						   if ($ip == $allowIp) {
							   $allow = true;
							   break;
						   }
					   }
					}
					if (!$allow) {
						Session::logoutUser();
						sendRedirect(URL_PREFIX."/disallow");
						die;
					}
					
				}
 				
 				Session::loginUser($user);
 				$this->contractor_id = $user["contractor_id"];
 				$this->user_id = $user["user_id"];
 				
	 		}
 		}

	}
	

	public function facetOperateLog($logInfo) {
		$param = [];
		$param["contractor_id"]			= Session::getLoginUser("contractor_id");
		$param["operate_user_id"]			= Session::getLoginUser("login_id");
		$param["operate_user_name"]			= Session::getLoginUser("user_name");
		$param["detail_json"]			= $logInfo["detail_json"];
		$param["controller_name"]			= $this->controller;
		$param["action_name"]			= $this->action;
		$param["action_sub_type"]			= $logInfo["action_sub_type"];
		
		// personIdを得るために先にDBに保存する。
		$sql = "
			insert into
				t_facet_operate_log (
					operate_time,
					contractor_id,
					operate_user_id,
					operate_user_name,
					operate_type,
					detail_json
					)
			select
				now() as operate_time,
				{contractor_id} as contractor_id,
				{operate_user_id} as operate_user_id,
				{operate_user_name} as operate_user_name,
				operate_type,
				{detail_json} as detail_json
			from
				t_facet_operate_log_type
			where
				controller_name = {controller_name}
				and action_name =  {action_name}
				and action_sub_type =  {action_sub_type}
		";
		DB::insert($sql, $param);
	}
	
	// @Override
	public function actionAfter(&$form, $tpl) {
		
	}
	
	// @Override
	public function after(&$form) {
		
		// 同期ログで未保存のデータがあるであればupdate。 
		SyncService::updateEndLog(SyncService::$processingRegisted ? 40 : 30);
		
	}
	
	
	
}
