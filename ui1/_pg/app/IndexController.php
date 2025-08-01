<?php 

// ログイン不要
define("NO_LOGIN", true);

class IndexController extends UserBaseController {
	
	// トップ
	public function indexAction(&$form) {
 		
		if (Session::isLogined()) {
			// mod-start founder yaozhengbang
			$this->urlDefaultRedit();
			// mod-end founder yaozhengbang
 		}
		
		// トークン発行
		$this->useTransactionToken();
		
		return "index.tpl";
	}
	
	// ログイン
	public function loginAction(&$form) {

		$data = Validators::set($form)
			->at("login_id", "ログインID")->required()->maxlength(100)
			->at("password", "パスワード")->required()->maxlength(100)
			->getValidatedData();
		
		$this->validateTransactionToken();
		
		if (Errors::isErrored()) return "index.tpl";
		
		$user = DB::selectRow("select * from m_user where state = 10 and login_id = {login_id}", $data);
		
		if (empty($user) || $user["password"] !== sha1(CRYPT_SALT.$data["password"])) {
			Errors::add("login_id,password", "ログイン出来ませんでした。ログインIDとパスワードをご確認ください。");
			return "index.tpl";
		}
		
		$userDomain = DB::selectOne("select domain from m_contractor where contractor_id = {contractor_id}", $user);
		
		$domain = $_SERVER["SERVER_NAME"];
		
		// ドメイン指定がある場合には一致性をチェックする。
		if (!empty($userDomain)) {

			if ($userDomain != $domain) {
		 		Errors::add("login_id,password", "ログイン出来ませんでした。ログインIDとパスワードをご確認ください。");
				return "index.tpl";
 			}
 			
 		}
 		
 		// 他の契約者に割り当てられたドメインの場合もエラーにする。
 		$param = ["contractor_id"=>$user["contractor_id"], "domain"=>$domain];
 		if (DB::exists("select 1 from m_contractor c where contractor_id != {contractor_id} and state = 30 and domain = {domain}", $param)) {
	 		Errors::add("login_id,password", "ログイン出来ませんでした。ログインIDとパスワードをご確認ください。");
			return "index.tpl";
		}

    // トライアルの場合は有効期限チェック。
    $contractor = DB::selectRow("select * from m_contractor where contractor_id = {contractor_id}", $user);
		if ($contractor["state"] == 20) {
			if (strtotime($contractor["trial_limit"]) < time()) {
				Errors::add("contractor", "トライアル期間を過ぎています。");
				return "index.tpl";
			}
		}
		
		// セッションを開始する
		Session::loginUser($user);
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>"null"
		];
		 $this->facetOperateLog($loginfo);
		 
		// mod-start founder yaozhengbang
		$this->urlDefaultRedit();
		// mod-end founder yaozhengbang
	}
		
	// ログアウト
	public function logoutAction(&$form) {
		
		if (Session::isLogined()) {
			// ログ出力
			$loginfo = [
				"action_sub_type"=>0,
				"detail_json"=>"null"
			];
			$this->facetOperateLog($loginfo);
		}
		
		Session::logoutUser();
		
		sendRedirect(URL_PREFIX."/");
		
	}
	
	// 許可されないIP
	public function disallowAction(&$form) {
		// トークン発行
		$this->useTransactionToken();
		
		Errors::add("", "許可されないアクセスです。");
		return "index.tpl";
	}
	
	// 404
	public function notFoundAction(&$form) {
		return "notFound.tpl";
	}
	// add-start founder yaozhengbang
	public function urlDefaultRedit() {
		if (Session::getLoginUser("user_flag") == "1") {
			sendRedirect(URL_PREFIX."/dashboard/");
		} else {
			sendRedirect(URL_PREFIX."/".Session::getUserFunctionAccess("url_menu_name")[0]."/");
		}
	}
	// add-end founder yaozhengbang
	
	// @Override
	public function setSessionAction(&$form) {
		return "notFound.tpl";
	}
	
}
