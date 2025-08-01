<?php

/* dev founder yaozhengbang */

class UiFunctionAccessService {

	// ユーザ権限設定を取得。
	public static function getUserFunctionAccess($auth_set_id){
		
		$sql = "
			select
					mf.function_id,
					mf.function_name,
					mf.url_menu_name,
					mf.url_tab_name
			from
					m_function mf left join t_function_auth tfa
			on
					mf.function_id = tfa.function_id
		    where   tfa.auth_set_id = {value}
			order by
					mf.function_id
			";
		$list = DB::selectArray($sql, $auth_set_id);
		return $list;
	}



}