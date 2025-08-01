<?php

/* dev founder luyi */

class IdManageController extends UserBaseController {

	public $contractor;

	public $groups;

	public $auths;

	public $functions;

	// @Override
	public function prepare(&$form) {
		
		// 契約者情報を取得。
		$this->contractor = DB::selectRow("select * from m_contractor where contractor_id = {value}", $this->contractor_id);
		
		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);

		// 権限リスト設定を取得。
		$this->auths = UiIdManageService::getAuths($this->contractor_id);
		
		// 機能リストを取得。（APBモードフラグが1以外の場合、APBログ一覧画面を除く；入退管理モードフラグが1以外の場合、入退管理画面を除く）
		$this->functions = UiIdManageService::getFunctions();
		if ($this->contractor["apb_mode_flag"] != 1) {
			$this->functions = array_filter($this->functions, function($e) { return $e["function_name"] != "APBログ一覧"; });
		}
		if ($this->contractor["enter_exit_mode_flag"] != 1) {
			$this->functions = array_filter($this->functions, function($e) { return $e["function_name"] != "入退管理"; });
		}
	}

	// 配列キーのプレフィックスを除去する。
	private function arrayExcludePrefix($arr, $prefix) {

		$ret = [];
		foreach ($arr as $k=>$v) {
			$ret[excludePrefix($k, $prefix)] = $v;
		}

		return $ret;
	}

	// 画面初期表示。
	public function indexAction(&$form) {

		Filters::ref($form)->at("tab")->values(["new", "modify"]);

		$this->indexAuth($form);
		$this->indexNew($form);
		$this->indexModify($form);

		return "idManage.tpl";
	}

	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：権限作成。
	// ---------------------------------------------------------------------------------------------------------------------------
	public function indexAuth(&$form) {
		// 初期表示状態　「新規追加」ラジオボックス：選択 「データ更新」プルダウン：未選択
		$form["auth_regist_type"] = "add";
		$form["auth_set_id"] = "";
	}

	public function deleteAuthAction(&$form) {
		// 入力チェック
		$data = ConfigSetService::getAuthDeleteValidator($form, $this->auths)->getValidatedData();
		if (Errors::isErrored()) {
			return "idManage.tpl";
		}
		// 当該権限のログインユーザー有無チェック
		if (UiIdManageService::isAuthContainUser($form["auth_set_id"])) {
			$auth_set_name = "";
			foreach($this->auths as $auth) {
				if ($auth["auth_set_id"] == $form["auth_set_id"]) {
					$auth_set_name = $auth["auth_set_name"];
					break;
				}
			}
			Errors::add("auth_set_id",
				"下記の権限はログインIDが存在するため削除できません。<br>"
				."権限に割り当てられているログインIDを別の権限に変更するか、削除してからお試しください。<br>"
				."権限：[".h($auth_set_name)."]");
			return "idManage.tpl";
		}
		
		// 削除
		ConfigSetService::deleteAuth($data);
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"権限名"=>$this->auths[$data["auth_set_id"]]["auth_set_name"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect("./", "権限の削除が完了しました。");
	}
	
	public function updateAuthAction(&$form) {
		// 入力チェック
		$data = ConfigSetService::getAuthUpdateValidator($form, $this->auths, $this->functions)->getValidatedData();
		// 権限の必須チェック
		if (!isset($form["function_ids"]) || $form["function_ids"] == null) {
			Errors::add("", "権限は必須です。");
		}
		// 権限名の重複チェック
		if (array_search($form["auth_set_name"], array_column($this->auths, "auth_set_name")) !== false) {
			Errors::add("auth_set_name", "権限名「".h($form["auth_set_name"])."」が重複しています。");
		}
		
		if (Errors::isErrored()) {
			return "idManage.tpl";
		}
		
		// 権限最大登録数チェック
		if ($data["auth_regist_type"] == "add" && count($this->auths) >= 10) {
			Errors::add("", "権限は最大10件までしか登録できません。");
			return "idManage.tpl";
		}
		
		// 登録
		$data["contractor_id"] = $this->contractor_id;
		$id = ConfigSetService::updateAuth($data);
		
		// ログ出力
		$detail_json = [];
		$authSetDetail = [];
		foreach ($this->functions as $function_id=>$function){
			if (inArray($data["function_ids"], $function["function_id"])) {
				$authSetDetail[$function["function_name"]] = "権限あり";
			}else{
				$authSetDetail[$function["function_name"]] = "権限なし";
			}
		}
		if ($data["auth_regist_type"] == "add") {
			$action_sub_type = 0;
			$detail_json["権限名"] = $data["auth_set_name"];
			$detail_json["権限登録内容"] = $authSetDetail;
		} else {
			$action_sub_type = 1;
			$detail_json["権限名"] = $this->auths[$data["auth_set_id"]]["auth_set_name"];
			$detail_json["権限変更内容"] = $authSetDetail;
		}
		$loginfo = [
			"action_sub_type"=>$action_sub_type,
			"detail_json"=>json_encode($detail_json,JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect("./", "権限の登録が完了しました。", "&tab=system1&upd_srs=".base_convert($id, 10, 36));
	}
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：新規登録。
	// ---------------------------------------------------------------------------------------------------------------------------
	public function indexNew(&$form) {
	}
	
	
	// ユーザーを登録
	public function registLoginUserAction(&$form) {

    // オンプレの場合は登録数チェック
    if (!ENABLE_AWS) {
      $loginUserCount = DB::selectOne("SELECT COUNT(*) FROM m_user WHERE contractor_id = {value} AND user_flag <> 1", $this->contractor_id);
      if ($loginUserCount + 1 > 100) {
        Errors::add("", "ログインユーザーは最大100件までしか登録できません。");
        return "idManage.tpl";
      }
    }
		
		$loginIdPrefix = "";
		
		$v = Validators::set($form);
		if ($this->contractor["single_tenant_mode"]!=1) {
			$loginIdPrefix = Session::getLoginUser("login_id")."_";
			$v = $v->at("new_account_id"		, "ログインID"			)->required()->half()->maxlength(100-strlen($loginIdPrefix))
				->dataExists(1, "select 1 from m_user where contractor_id = {$this->contractor_id} and login_id = concat('{$loginIdPrefix}',{value})");
		} else {
			$v = $v->at("new_account_id"		, "ログインID"			)->required()->half()->maxlength(100)
				->dataExists(1, "select 1 from m_user where contractor_id = {$this->contractor_id} and login_id = {value}");
		}
		
		$v = $v->at("new_password"			, "パスワード" 			)->required()->minlength(8)->maxlength(50)->half()
			->at("new_password_confirm"	, "パスワード（確認）"		)->required()->compSame("new_password", "パスワード")
			->at("new_account_name"		, "氏名"			)->required()->maxlength(32)
			->at("new_camera_group"		, "カメラグループ（任意）"	)->ifNotEquals("")->inArray(array_keys($this->groups))->ifEnd()
			->at("new_role"				, "権限"					)->required()->inArray(array_keys($this->auths));
		
			$data = $v->getValidatedData();
		
		if (Errors::isErrored())	return "idManage.tpl";
		
		
		// データを登録
		$serviceParam = [];
		$serviceParam["accountId"] = $loginIdPrefix.$data["new_account_id"];
		$serviceParam["password"] = $data["new_password"];
		$serviceParam["accountName"]   = $data["new_account_name"];
		$serviceParam["cameraGroup"]    = (isset($data["new_camera_group"])) ? $data["new_camera_group"] : "";
		$serviceParam["role"]    = $data["new_role"];
		$serviceParam["adminUserId"]    = $this->user_id;
		$serviceParam["contractorId"]    = $this->contractor_id;
		UiIdManageService::registUser($serviceParam);

		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"ログインID"=>$serviceParam["accountId"],
				"氏名"=>$serviceParam["accountName"],
				"カメラグループ"=>isset(($this->groups[$serviceParam["cameraGroup"]]??[])["group_name"]) ? ($this->groups[$serviceParam["cameraGroup"]]??[])["group_name"] : "",
				"権限"=>$this->auths[$serviceParam["role"]]["auth_set_name"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect("./?tab=new", "ログインユーザー情報の登録を完了しました。");
		
	}
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：変更・削除。
	// ---------------------------------------------------------------------------------------------------------------------------
	public function indexModify(&$form) {
	}

	// 一覧の検索。
	public function listSearchAction(&$form, $isInlucde = false) {
		// 検索。
		$filtedData = UiIdManageService::getListSearchFilter($form, "list_")->getFilteredData();
		$data = $this->arrayExcludePrefix($filtedData, "list_");

		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = UiIdManageService::getList($this->contractor_id, $data, $pageInfo);

		// 削除完了後に戻るためのパラメータ。
		if (!$isInlucde) {
			$form["list_del_back"] = Session::setData(createQueryString($filtedData)."&tab=modify&_p=".urlencode($form["_p"]));
		}

		$this->assign("list_list", $list);
		$this->assign("list_pageInfo", $pageInfo);
		return "idManage.tpl";
	}

	// ユーザーの更新の開始。
	public function modLoginUserInitAction(&$form){
		// 一覧の再検索。
		$this->listSearchAction($form, true);

		// 対象データの取得。
		$data = Filters::ref($form)
			->at("list_mod_loginUserId")->digit()
			->getFilteredData();

		if (!empty($data["list_mod_loginUserId"])) {
			$modLoginUser = UiIdManageService::get($this->contractor_id, $data["list_mod_loginUserId"]);
			if(!empty($modLoginUser)){
				// 完了後に戻るためのパラメータ。
				$form["list_mod_back"] = Session::setData(createQueryString($form, ["list_mod_loginUserId"]));

				foreach ($modLoginUser as $k=>$v) {
					$form["list_mod_".$k] = $v;
				}
				$form["list_mod_password"] = "";
				$form["list_mod_passwordConfirm"] = "";
				$this->assign("list_modLoginUser", true);
			}
		}

		return "idManage.tpl";
	}
	
	// ユーザーの更新
	public function modLoginUserAction(&$form){
		// 一覧の再検索。
		$this->listSearchAction($form, true);

		$this->assign("list_modLoginUser", true);

		// 入力チェック。
		Validators::set($form)
			->at("list_mod_password","パスワード")->minlength(8)->maxlength(50)->half()
			->at("list_mod_passwordConfirm","パスワード（確認）")->compSame("list_mod_password", "パスワード")
			->at("list_mod_userName","氏名")->required()->maxlength(32)
			->at("list_mod_groupId","カメラグループ")->ifNotEquals("")->inArray(array_keys($this->groups))->ifEnd()
			->at("list_mod_authSetId","権限")->required()->inArray(array_keys($this->auths))
			->getValidatedData();

		// 確認用パスワードが未入力時の分岐
		if ($form["list_mod_password"] !== "" && $form["list_mod_passwordConfirm"] === "") Errors::add("","パスワード（確認）の内容がパスワードと異なっています。");
		
		if (Errors::isErrored()) return "idManage.tpl";
		
		$User = DB::selectRow("select * from m_user where user_id = {value}", $form["list_mod_userId"]);
		if (empty($User)) response400();

		$msg = UiIdManageService::updateUser($form, $User);
		if(!empty($msg)){
			Errors::add("", $msg);
			if (Errors::isErrored()) return "idManage.tpl";
		}

		$query = Session::getData($form["list_mod_back"]);
		$url = "./listSearch".$query;
		
		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"ログインID"=>$User["login_id"],
				"変更前氏名"=>$User["user_name"],
				"変更前カメラグループ"=>isset(($this->groups[$User["group_id"]]??[])["group_name"]) ? ($this->groups[$User["group_id"]]??[])["group_name"] : "",
				"変更前権限"=>$this->auths[$User["auth_set_id"]]["auth_set_name"],
				"変更後氏名"=>$form["list_mod_userName"],
				"変更後カメラグループ"=>isset(($this->groups[$form["list_mod_groupId"]]??[])["group_name"]) ? ($this->groups[$form["list_mod_groupId"]]??[])["group_name"] : "",
				"変更後権限"=>$this->auths[$form["list_mod_authSetId"]]["auth_set_name"],
				"パスワード初期化有無"=>empty($form["list_mod_password"])?"なし":"あり"
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect($url, "ログインユーザー情報の変更を行いました。");
	}

	// ユーザーの削除。
	public function delLoginUserAction(&$form) {

		// 一覧の再検索。
		$this->listSearchAction($form, true);

		// 入力チェック。
		$data = Validators::set($form)
			->at("list_del_userId", "ID"  )->required()->maxlength(12)->half()->dataExists(0, "select 1 from m_user where user_id = {value}")
			->getValidatedData();

		if (Errors::isErrored()) return "idManage.tpl";

		$User = DB::selectRow("select user_id, login_id from m_user where user_id = {value}", $data["list_del_userId"]);
		if (empty($User)) response400();

		// データを削除。
		DB::delete("delete from m_user where user_id = {value}", $data["list_del_userId"]);

		$query = Session::getData($form["list_del_back"]);
		$url = "./listSearch".$query;

		// ログ出力
		$loginfo = [
			"action_sub_type"=>0,
			"detail_json"=>json_encode([
				"ログインID"=>$User["login_id"]
			],JSON_UNESCAPED_UNICODE)
		];
		$this->facetOperateLog($loginfo);
		
		sendCompleteRedirect($url, "ログインユーザー情報の削除を行いました。");
	}

}