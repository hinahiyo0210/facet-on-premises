<?php

class UserService {

	// 最終アクセス日時を更新。
	public static function updateAccessTime() {
 		DB::update("update m_user set access_time = now() where user_id = {login_user_id}");
 		DB::commit();
	}

	// UIユーザを取得。
	public static function getUser($userId) {
		// mod-start founder yaozhengbang
		return DB::selectRow("
			select 
				u.user_id
				, u.create_time
				, u.update_time
				, u.contractor_id
				, u.login_id
				, u.password
				, u.user_name
				, u.state
				, u.access_time
				, u.logo_url
				, u.allow_ips
				, u.group_id
				, u.auth_set_id
				, u.user_flag
 				, c.apb_mode_flag
				, c.domain
				, c.enter_exit_mode_flag
				, c.header_logo_url
				, c.header_title
				, c.getsysteminfo_time
				, c.teamspirit_flag
				
			from 
				m_user u
				
				inner join m_contractor c on
				u.contractor_id = c.contractor_id
				and c.state in (20, 30)

			where 
				u.state = 10 
				and u.user_id = {value}

			", $userId);
		// mod-end founder yaozhengbang
	}
	
	// add-start founder luyi
	//契約者に対するパスワードポリシーを取得。
	public static function getPasswordPolicy($contractor_id)
	{
		return DB::selectRow("
			select
				mc.set_password_policy
			from
				m_contractor mc
			where
				mc.contractor_id = {value}
		", $contractor_id)["set_password_policy"] ?? false;
	}
	// add-end founder luyi
	
}