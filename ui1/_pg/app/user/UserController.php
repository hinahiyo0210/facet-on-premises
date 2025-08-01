<?php 

class UserController extends UserBaseController {
	
	public function indexAction(&$form) {
	}
	
	// パスワード変更。
	public function modPasswordAction(&$form) {
		return "modPassword.tpl";
	}
	public function registPasswordAction(&$form) {
	
		// 入力チェック
		$data = Validators::set($form)
			->at("password_current"	, "現在のパスワード")->required()->maxlength(50)
			->at("password"			, "新しいパスワード")->required()->minlength(8)->maxlength(50)->half()
			->at("password_confirm"	, "新しいパスワード（確認用）")->required()->compSame("password", "新しいパスワード")
			->getValidatedData();
		
		// add-start founder luyi
		// 英字大小文字・数字・記号混在チェック
		if (Errors::isNotErrored()
			&& UserService::getPasswordPolicy($this->contractor_id) === "1"
			&& !(preg_match("/(?=.*[[:lower:]])(?=.*[[:upper:]])(?=.*[[:digit:]])(?=.*[[:punct:]])/", $form["password"]))) {
			
			Errors::add("password", "新しいパスワードに英字大文字、英字小文字、 数字、記号が含まれておりません。");
		}
		// add-end founder luyi
		
		if (Errors::isErrored()) return "modPassword.tpl";
			
		$current = sha1(CRYPT_SALT.$data["password_current"]);
		$new = sha1(CRYPT_SALT.$data["password"]);
		
		if (Errors::isNotErrored()) {
			
			if ($current !== DB::selectOne("select password from m_user where user_id = {login_user_id}")) {
				Errors::add("password_current", "現在のパスワードが正しいものではありません。");
			}
		}
		
		if (Errors::isErrored()) return "modPassword.tpl";
		
		DB::update("
			update
				m_user
			set
				password = {value}
			where
				user_id = {login_user_id}
		", $new);
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>"null"
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect("./modPassword", "パスワード変更を完了しました。");
	}
	
	
}
