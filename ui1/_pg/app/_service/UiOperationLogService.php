<?php

/* dev founder luyi */

class UiOperationLogService {
	
	// Facet操作ログを検索する。
	public static function getFacetLogList($pageInfo, $data) {
		$where = "";
		if (!empty($data["facet_date_from"])) {
			$where .= " and tfol.operate_time >= {facet_date_from}";
		}
		if (!empty($data["facet_date_to"])) {
			$data["facet_date_to"] = addDate($data["facet_date_to"], "+1 day");
			$where .= " and tfol.operate_time <= {facet_date_to}";
		}
		if (($data["facet_account_id"] === "0") || !empty($data["facet_account_id"])) {
			$where .= " and tfol.operate_user_id like {like_LR facet_account_id}";
		}
		if (($data["facet_account_name"] === "0") || !empty($data["facet_account_name"])) {
			$where .= " and tfol.operate_user_name like {like_LR facet_account_name}";
		}
		if (!empty($data["facet_operate_type"])) {
			$where .= " and tfol.operate_type = {facet_operate_type}";
		}
		
		$sql = "
			select
				tfol.operate_log_id,
				tfol.operate_time,
				tfol.operate_user_id,
			    tfol.operate_user_name,
				tfol.operate_type,
				tfol.detail_json
			from
				t_facet_operate_log tfol
			where
				tfol.contractor_id = {contractor_id}
			$where
		";
		$order = "
			order by
				tfol.operate_time desc
		";
		return DB::selectPagerArray($pageInfo, $sql, $order, $data);
	}
	
	// Facet操作ログの詳細を検索する（PK検索）。
	public static function getFacetLogDetail($data) {
		$sql = "
			select
				tfol.detail_json
			from
				t_facet_operate_log tfol
			where
				tfol.operate_log_id = {facet_operate_log_id}
		";
		return DB::selectOne($sql, $data);
	}
	
	// Facefc操作ログを検索する。
	public static function getFacefcLogList($pageInfo, $data) {
		$where = "";
		if (!empty($data["facefc_date_from"])) {
			$where .= " and tol.operate_time >= {facefc_date_from}";
		} else {
			$where .= " and tol.operate_time >= CURDATE()";
		}
		if (!empty($data["facefc_date_to"])) {
			$data["facefc_date_to"] = addDate($data["facefc_date_to"], "+1 day");
			$where .= " and tol.operate_time <= {facefc_date_to}";
		} else {
			$where .= " and tol.operate_time <= CURDATE()";
		}
		if (!empty($data["facefc_account_id"])) {
			$where .= " and tol.operate_user like {like_LR facefc_account_id}";
		}
		if (!empty($data["facefc_device_ids"])) {
			$where .= " and md.device_id in {in facefc_device_ids}";
		}
		if (!empty($data["facefc_main_type"])) {
			$where .= " and tol.main_type = {facefc_main_type}";
		}
		if (!empty($data["facefc_sub_type"])) {
			$where .= " and tol.sub_type like {like_LR facefc_sub_type}";
		}
		
		$sql = "
			select
				tol.operate_log_id,
				tol.operate_time,
				tol.operate_user,
				tdgd2.group_name,
				md.description,
				tol.main_type,
				tol.sub_type
			from
				t_operate_log tol
			inner join
				m_device md
			on
				tol.device_id = md.device_id
			left outer join (
				select
					tdg.group_name as group_name,
					tdgd.device_id as device_id
				from
					t_device_group_device tdgd
				inner join
					t_device_group tdg
				on
					tdg.device_group_id = tdgd.device_group_id
				) tdgd2
			on
				tdgd2.device_id = md.device_id
			where
				md.contractor_id = {contractor_id}
			$where
		";
		$order = "
			order by
				tol.operate_time desc
		";
		return DB::selectPagerArray($pageInfo, $sql, $order, $data);
	}
	
	// Facefc操作ログの詳細を検索する（PK検索）。
	public static function getFacefcLogDetail($data) {
		$sql = "
			select
				tol.detail_json
			from
				t_operate_log tol
			where
				tol.operate_log_id = {facefc_operate_log_id}
		";
		return DB::selectOne($sql, $data);
	}
}