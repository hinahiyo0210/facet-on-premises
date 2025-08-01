<?php

class DeviceGroupService {
	
	// デバイスIDからグループを検索可能な配列を作成する。
	public static function mapGroupsByDeviceId($groups) {
		
		$ret = [];
		
		foreach ($groups as $groupId=>$group) {
			foreach ($group["deviceIds"] as $deviceId) {
				
				if (empty($ret[$deviceId])) $ret[$deviceId] = [];
				$ret[$deviceId][] = $group;
				
			}
			
		}
		
		return $ret;
	}
	
	// デバイスを取得する。
	public static function getDevices($contractor_id) {
		
		// mod-start founder luyi
		// 権限制御
		$where = "";
		$group_id = Session::getLoginUser("group_id");
		if (!empty($group_id)) {
			$where = " and tdgd.device_group_id = $group_id ";
		}
		
		$sql = "
			select
				d.*
				, tdgd.device_group_id as device_group_id
			from
				m_device d
			left outer join
				( t_device_group_device tdgd
				inner join
					t_device_group tdg
				on
				    tdg.device_group_id = tdgd.device_group_id
				)
			on
				tdgd.device_id = d.device_id
			where
				d.contract_state = 10
				and d.contractor_id = {value}
				$where
			order by
				d.sort_order
				, d.device_id
		";
		// mod-end founder luyi
		
		$list = DB::selectKeyRow($sql, $contractor_id, "device_id");
		foreach ($list as $idx=>$d) {
			if (!empty($d["description"])) {
				$list[$idx]["name"] = $d["description"];
			} else {
				$list[$idx]["name"] = $d["serial_no"];
			}
		}
		
		return $list;
	}

	// 編集する人物と関連づいているデバイスを取得する。
	public static function getModPersonDevices($contractor_id, $modPerson) {
		
		// 編集する人物の関連づいているデバイスIDを取得
		$deviceIds = DB::selectArray("select device_id from t_device_person where person_id = {person_id}", $modPerson);

		if (empty($deviceIds)) return false;

		$where = "";

		// 関連付け登録デバイスを格納
		$ids = "";
		foreach ($deviceIds as $device) {
			$ids .= $device['device_id'].",";
		}
		$where .= "and d.device_id in(".rtrim($ids, ",").") ";

		// 権限制御
		$group_id = Session::getLoginUser("group_id");
		if (!empty($group_id)) {
			$where .= " and tdgd.device_group_id = $group_id ";
		}
		
		$sql = "
			select
				d.*
				, tdgd.device_group_id as device_group_id
			from
				m_device d
			left outer join
				( t_device_group_device tdgd
				inner join
					t_device_group tdg
				on
				    tdg.device_group_id = tdgd.device_group_id
				)
			on
				tdgd.device_id = d.device_id
			where
				d.contract_state = 10
				and d.contractor_id = {value}
				$where
			order by
				d.sort_order
				, d.device_id
		";
		// mod-end founder luyi
		
		$list = DB::selectKeyRow($sql, $contractor_id, "device_id");
		foreach ($list as $idx=>$d) {
			if (!empty($d["description"])) {
				$list[$idx]["name"] = $d["description"];
			} else {
				$list[$idx]["name"] = $d["serial_no"];
			}
		}
		
		return $list;
	}
	
	
	// グループ設定を取得する。
	public static function getGroups($contractor_id) {
		
		$sql = "
			select 
				device_group_id
				, group_name
			from
				t_device_group
			where
				contractor_id = {value}
			order by 
				sort_order
				, device_group_id
		";
		
		$list = DB::selectKeyRow($sql, $contractor_id, "device_group_id");
		
		$device_group_ids = [];
		foreach ($list as $idx=>$group) {
			$device_group_ids[] = $group["device_group_id"];
			$list[$idx]["deviceList"] = [];
			$list[$idx]["deviceIds"] = [];
		}
		
		// 属するデバイスの情報を取得する。
		$sql = "
			select
				dd.device_group_id
				, d.device_id
				, d.serial_no
				, d.description
				
			from
				m_device d
				
				inner join t_device_group_device dd on
				d.device_id = dd.device_id
				and dd.device_group_id in {in device_group_ids}
				
			where
				d.contract_state = 10
				and d.contractor_id = {contractor_id}
				
			order by
				d.sort_order
				, d.device_id
		";
		
		$devices = DB::selectArray($sql, ["contractor_id"=>$contractor_id, "device_group_ids"=>$device_group_ids]);
		
		foreach ($devices as $device) {
			$groupId = $device["device_group_id"];
			unset($device["device_group_id"]);
			$list[$groupId]["deviceList"][] = $device;
			$list[$groupId]["deviceIds"][] = $device["device_id"];
		}
		
		return $list;
	}
	
	
	
	
}
