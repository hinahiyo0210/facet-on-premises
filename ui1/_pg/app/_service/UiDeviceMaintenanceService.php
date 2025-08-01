<?php

/* dev founder feihan */

class UiDeviceMaintenanceService
{
	
	// 検索用のフィルタを取得。
	public static function getListSearchFilter(&$form, $prefix, $devices, $groups){
		// 未登録 ＝ -1
		$groups['-1'] = true;
		return Filters::ref($form)
			->at("_form_session_key")->len(3)
			->at("{$prefix}serialNo"		)->len(100)->narrow()
			->at("{$prefix}group_ids"		)->enumArray($groups)
			->at("{$prefix}device_ids"		)->enumArray($devices)
			->at("{$prefix}search_init"		)->digit(1)
			->at("{$prefix}pageNo"		 , 1)->digit()
			->at("{$prefix}limit"		 , 20)->enum(Enums::pagerLimit());
	}
	
	// 検索。
	public static function getList($contractor_id, $data, $pageInfo){
		
		$where = "";
		$data["contractor_id"] = $contractor_id;
		// 条件指定検索。
		if (empty($data["serialNo"]) && empty($data["device_ids"])) {
			$where .= "and 1 = 0";
		} elseif ($data["serialNo"]) {
			$where .= " and d.serial_no like {like_LR serialNo}";
		} else {
			$where .= " and d.device_id in {in device_ids}";
		}
		
		$sql="
		    select
	            d.device_id,
	            d.sort_order,
	            d.serial_no,
	            tdg.group_name,
	            d.description
		    
            from m_device d
                left join t_device_group_device tdgd
                on d.device_id = tdgd.device_id
	            left join t_device_group tdg
	            on tdgd.device_group_id = tdg.device_group_id
		    
	        where
                d.contractor_id = {contractor_id}
                $where
	        ";
		
		$order = "
			order by
				d.sort_order asc
		";
		$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);
		
		foreach ($list as $idx=>$item) {
			if(empty($item["description"])){
				$list[$idx]["device_name"] = $item["serial_no"];
			}else{
				$list[$idx]["device_name"] = $item["description"];
			}
		}
		
		return $list;
	}
	
	// DBにカメラデータを保存。
	public static function insertDevice($data, $contractorId, $deviceTypes) {
		
		// 登録データを準備する。
		$params = $data;
		$params["contractor_id"] = $contractorId;
		// ソート
		$deviceSort = DB::selectRow("select max(sort_order) as sort_order from m_device where contractor_id = {value} and contract_state = 10", $contractorId, "sort_order");
		if(empty($deviceSort["sort_order"])){
			$deviceSort["sort_order"] = 1;
		}else{
			$deviceSort["sort_order"]++;
		}
		$params["sort_order"] = $deviceSort["sort_order"];
		// 型番
		$params["new_device_type"] = $deviceTypes[$data["new_device_type_id"]]["device_type"];
		// カメラ名称
		$params['new_deviceName'] = $data['new_deviceName'];
		// UI通知のためのWS　APIのURL
		$deviceTmp = DB::selectRow("select * from m_device where contractor_id = {value} and contract_state = 99 limit 1", $contractorId);
		if(empty($deviceTmp)){
			$params["ws_api_url"] = "http://172.31.43.154:8080/ws/api";
		}else{
			$params["ws_api_url"] = $deviceTmp["ws_api_url"];
			// S3パスプレフィックス
			if(!empty($deviceTmp['s3_path_prefix'])){
				$index = strrpos($deviceTmp['s3_path_prefix'],'/');
				if($index > 0){
					$params["s3_path_prefix"] = substr($deviceTmp['s3_path_prefix'],0,$index+1).$data["new_serialNo"];
				}
			}
		}
		// データを登録する。
		$newDeviceId = DB::insert("
			insert into
				m_device
			set
				device_type                 = {new_device_type}
				, serial_no                 = {new_serialNo}
				, contractor_id             = {contractor_id}
				, sort_order                = {sort_order}
				, create_time               = now()
				, contract_state            = 10
				, start_date                = now()
				, save_recog_picture_flag   = 1
				, save_recog_name_flag      = 1
				, description               = {new_deviceName}
				, memo                      = NULL
				, device_allow_ip           = NULL
				, device_token              = NULL
				, last_ws_access            = NULL
				, last_push_access          = NULL
				, last_recog                = NULL
				, ws_api_url                = {ws_api_url}
				, push_url                  = NULL
				, s3_path_prefix            = {s3_path_prefix}
				, rev_call                  = NULL
				, picture_check_device_flag = 0
				, apb_type                  = NULL
                , device_role               = {new_device_role}
			", $params);
		
		// カメラグループの登録
		if (!empty($data['device_group_id'])) {
			DB::insert("insert into t_device_group_device set device_group_id = {device_group_id}, device_id = {device_id}, create_time = now(), create_user_id = {login_user_id}", [
				"device_group_id"=>$data['device_group_id']
				, "device_id"=>$newDeviceId
			]);
		}
		
		return true;
	}
	
}