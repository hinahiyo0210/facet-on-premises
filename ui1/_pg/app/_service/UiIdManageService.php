<?php


class UiIdManageService
{
	// 機能リストを取得（管理者以外利用不可タブは除く）。
	public static function getFunctions() {
		$sql = "
			select
				function_id,
				function_name,
				parent_function_id
			from
				m_function
			where
				admin_flag = 0
			order by
				sort_order
		";
		return DB::selectArray($sql);
	}
	
	// 権限データを取得。
	public static function getAuths($contractor_id) {
		$sql = "
			select
				tas.auth_set_id,
				tas.auth_set_name,
				group_concat(tfa.function_id order by tfa.function_id) as function_ids
			from
				t_auth_set tas
			left outer join
				t_function_auth tfa
			on
				tas.auth_set_id = tfa.auth_set_id
			where
				tas.contractor_id = {value}
			group by
				tas.auth_set_id
			order by
				tas.auth_set_id
		";
		$res = DB::selectKeyRow($sql, $contractor_id, "auth_set_id");
		$lambda_split = function($auth) {
			$ret = array();
			$ret["auth_set_id"] = $auth["auth_set_id"];
			$ret["auth_set_name"] = $auth["auth_set_name"];
			$ret["function_ids"] = ($auth["function_ids"] == null) ? array() : explode(",", $auth["function_ids"]);
			return $ret;
		};
		return array_map($lambda_split, $res);
	}
	
	// 該当権限を持っているログインユーザー有無判定。
	public static function isAuthContainUser($auth_set_id) {
		$sql = "
			select
				user_id
			from
				m_user
			where
				auth_set_id = {value}
		";
		return DB::exists($sql, $auth_set_id);
	}
	
	// 検索用のフィルタを取得。
	public static function getListSearchFilter(&$form, $prefix) {

		return Filters::ref($form)
			->at("_form_session_key"	)->len(3)
			->at("{$prefix}search_init"		)->digit(1)
			->at("{$prefix}loginId")->len(12)->narrow()
			->at("{$prefix}userName")->len(32)
			->at("{$prefix}authSetId")->len(12)->narrow()
			->at("{$prefix}pageNo", 1)->digit()
			->at("{$prefix}limit", 20)->enum(Enums::pagerLimit());
	}

	// 検索。
	public static function getList($contractor_id, $data, $pageInfo) {
		$where = "";
		$data["contractor_id"] = $contractor_id;

		// 検索条件
		if (isset($data["loginId"]) && (($data["loginId"] === "0") || !empty($data["loginId"])))	  $where .= " and u.login_id like {like_LR loginId}";
		if (isset($data["userName"]) && (($data["userName"] === "0") || !empty($data["userName"])))   $where .= " and u.user_name like {like_LR userName}";
		if (!empty($data["authSetId"]))   $where .= " and tas.auth_set_id = {authSetId}";
		if (!empty($data["userId"]))   $where .= " and u.user_id = {userId}";

		$sql = "
			select
				u.user_id,
				u.contractor_id,
				u.login_id,
				u.user_name,
				u.user_flag,
				u.auth_set_id,
				u.group_id,
				tdg.group_name,
				tas.auth_set_name
			from
				m_user u
				left outer join t_device_group tdg
				on u.group_id = tdg.device_group_id
				left outer join t_auth_set tas
				on u.auth_set_id = tas.auth_set_id
			where
				u.contractor_id = {contractor_id}
				and u.user_flag in (1,2)
				$where
			";
		$order = "
			order by 
				u.user_id desc
		";

		$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);

		return $list;
	}

	// 一件のみ取得。
	public static function get($contractor_id, $userId) {

		$list = UiIdManageService::getList($contractor_id, ["userId"=>$userId], new PageInfo(1, 1));

		if (empty($list)) {
			return false;
		}

		$loginUser = $list[0];
		$loginUser["userId"] = $loginUser["user_id"];
		$loginUser["loginId"] = $loginUser["login_id"];
		$loginUser["userName"] = $loginUser["user_name"];
		$loginUser["groupId"] = $loginUser["group_id"];
		$loginUser["authSetId"] = $loginUser["auth_set_id"];

		return $loginUser;
	}
	
	// DBにユーザーデータを保存。
	public static function registUser($data) {
		
		$param = [];
		$param["login_id"]			= $data["accountId"];
		$param["password"]			= sha1(CRYPT_SALT.$data["password"]);
		$param["user_name"]			= $data["accountName"];
		$param["group_id"]			= $data["cameraGroup"];
		$param["auth_set_id"]		= $data["role"];
		$param["admin_user_id"]		= $data["adminUserId"];
		$param["contractor_id"]		= $data["contractorId"];
		//UserId 1:Admin;2:User
		$param["user_flag"]			= 2;
		
		// personIdを得るために先にDBに保存する。
		$sql = "
			insert into
				m_user (
					create_time,
					update_time,
					contractor_id,
					login_id,
					password,
					user_name,
					state,
					allow_ips,
					user_flag,
					auth_set_id,
					group_id
					)
			select
				now() as create_time,
				now() as update_time,
				contractor_id,
				{login_id} as login_id,
				{password} as password,
				{user_name} as user_name,
				state,
				allow_ips,
				{user_flag} as user_flag,
				{auth_set_id} as auth_set_id,
				{group_id} as group_id
			from
				m_user
			where
				contractor_id = {contractor_id}
				and user_id =  {admin_user_id}
		";
		DB::insert($sql, $param);
		
		return true;
	}
	
	// ユーザーの更新。
	public static function updateUser($form, $User){
		$errorMsg = "";

		$params = [];
		$setSql = "";
		if(sha1(CRYPT_SALT."")!==sha1(CRYPT_SALT.$form["list_mod_password"])) {
			$setSql.="password = {password}";
			$params["password"] = sha1(CRYPT_SALT.$form["list_mod_password"]);
		}
		if($User["user_name"]!==$form["list_mod_userName"]) {
			if($setSql!=="") $setSql.=",";
			$setSql.="user_name = {user_name}";
			$params["user_name"] = $form["list_mod_userName"];
		}
		if($User["group_id"]==null) $User["group_id"]="";
		if($User["group_id"]!==$form["list_mod_groupId"]) {
			if($setSql!=="") $setSql.=",";
			$setSql.="group_id = {group_id}";
			$params["group_id"] = $form["list_mod_groupId"];
		}
		if($User["auth_set_id"]==null) $User["auth_set_id"]="";
		if($User["auth_set_id"]!==$form["list_mod_authSetId"]) {
			if($setSql!=="") $setSql.=",";
			$setSql.="auth_set_id = {auth_set_id}";
			$params["auth_set_id"] = $form["list_mod_authSetId"];
		}
		if(!empty($params)){
			$params["user_id"] = $form["list_mod_userId"];
			$setSql.=",update_time = now()";
			$updateSql ="update m_user set ".$setSql." where user_id = {user_id}";
			// データを更新
			DB::update($updateSql, $params);
		}else{
			$errorMsg="ユーザーの情報は一つも変更されていません。";
		}

		return $errorMsg;
	}
}