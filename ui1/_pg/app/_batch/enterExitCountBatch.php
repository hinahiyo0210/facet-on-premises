<?php 
/**
 * t_enter_exit_countへのINSERTバッチ
 */

define("BATCH", true);
$BACTH_LOCK = ["name"=>basename(__FILE__), "limit"=>"-29 minute"];
include(dirname(__FILE__)."/../../procedural_php/ProceduralPhp.php");


// 契約者IDの取得
$contractorIds = DB::selectKeyRow("SELECT contractor_id FROM m_contractor WHERE enter_exit_mode_flag = {value}", 1, "contractor_id");

// 指定契約者の処理（enter_exit_modeが1、つまり有効の契約者）
foreach ($contractorIds as $contractorId => $array) {
	
	// 指定契約者内の、デバイスグループの取得
	$groups = DeviceGroupService::getGroups($contractorId);
	// 指定契約者内の、区分の取得
	$personTypes = DB::selectKeyRow("SELECT * FROM m_person_type WHERE contractor_id = {value}", $contractorId, "person_type_code");
	
	// 指定契約者内の、指定デバイスグループの処理
	foreach ($groups as $g => $group) {
		
		$param = [];
		$param['contractor_id']   = $contractorId;
		$param['device_group_id'] = $g;
		
		// 指定契約者内の、指定デバイスグループが設定しているリセット時間
		$switchingTime = DB::selectOne("SELECT switching_time FROM t_device_group WHERE  device_group_id = {device_group_id}", $param) ?? 0;
		
		// 指定契約者の、指定デバイスグループのリセット時間による分岐（実行とリセット時間が一緒だった場合はcountを0に更新）
		if ($switchingTime > date("G")) {
			$countStartTime  = date("Y-m-d H:i:s", strtotime(date("Y-m-d", strtotime('-1 day'))."{$switchingTime}:00:00"));
		} elseif ($switchingTime <= date("G")) {
			$countStartTime  = date("Y-m-d H:i:s", strtotime(date("Y-m-d")."{$switchingTime}:00:00"));
		}

		$param['start_time'] = $countStartTime;
		
		// 指定契約者内の、指定デバイスグループの、指定区分の処理
		foreach ($personTypes as $personTypeCode => $personType) {
			$param['person_type_code'] = $personTypeCode;
			$personTypeCountExists = DB::selectRow("SELECT * FROM t_enter_exit_count WHERE device_group_id = {device_group_id} AND person_type_code = {person_type_code}", $param);
			
			$typeEnterTotal = 0;
			$typeExitTotal  = 0;
			foreach ($group['deviceIds'] as $index => $deviceId) {
				$param['device_id'] = $deviceId;
				$typeEnterCount = DB::selectOne("SELECT COUNT(*) FROM t_recog_log WHERE person_type_code = {person_type_code} AND device_id = {device_id} AND enter_exit_type_flag = 1 AND pass = 1 AND recog_time BETWEEN {start_time} AND now()", $param) ?? 0;
				$typeExitCount  = DB::selectOne("SELECT COUNT(*) FROM t_recog_log WHERE person_type_code = {person_type_code} AND device_id = {device_id} AND enter_exit_type_flag = 2 AND pass = 1 AND recog_time BETWEEN {start_time} AND now()", $param) ?? 0;
				
				$typeEnterTotal += $typeEnterCount;
				$typeExitTotal  += $typeExitCount;
			}
			
			$param['count'] = $typeEnterTotal - $typeExitTotal;
			
			if (empty($personTypeCountExists)) {
				$sql = "
				insert into 
					t_enter_exit_count 
				set
					contractor_id      = {contractor_id}
					, device_group_id  = {device_group_id}
					, person_type_code = {person_type_code}
					, device_role      = NULL
					, count_time       = now()
					, count	           = {count}
				";
				DB::insert($sql, $param);
			} else {

				$sql = "
				update 
					t_enter_exit_count 
				set
					count_time         = now()
					, count	           = {count}
				where
					device_group_id    = {device_group_id}
					AND person_type_code = {person_type_code}
				";
				DB::update($sql, $param);
			}
		}
		
		// 指定契約者の、指定デバイスグループの、総数系
		$enterTotal = 0;
		$exitTotal  = 0;

		foreach ($group['deviceIds'] as $index => $deviceId) {
			$param['device_id'] = $deviceId;
			$enterCount = DB::selectOne("SELECT COUNT(*) FROM t_recog_log WHERE device_id = {device_id} AND enter_exit_type_flag = 1 AND pass = 1 AND recog_time BETWEEN {start_time} AND now()", $param) ?? 0;
			$exitCount  = DB::selectOne("SELECT COUNT(*) FROM t_recog_log WHERE device_id = {device_id} AND enter_exit_type_flag = 2 AND pass = 1 AND recog_time BETWEEN {start_time} AND now()", $param) ?? 0;

			$enterTotal += $enterCount;
			$exitTotal  += $exitCount;
		}
	
		$totalCount    = [];
		$totalCount[1] = $enterTotal;
		$totalCount[2] = $exitTotal;

		$countExists = DB::selectArray("SELECT * FROM t_enter_exit_count WHERE device_group_id = {value} AND device_role IS NOT NULL", $g);
		foreach ($totalCount as $role => $enterExitCount) {
			$param['device_role'] = $role;
			$param['count']       = $enterExitCount;
			if (empty($countExists)) {
				$sql = "
					insert into 
						t_enter_exit_count 
					set
						contractor_id      = {contractor_id}
						, device_group_id  = {device_group_id}
						, person_type_code = NULL
						, device_role      = {device_role}
						, count_time       = now()
						, count	           = {count}
				";
				DB::insert($sql, $param);
			} else {
				$sql = "
				update 
				t_enter_exit_count 
				set
				count_time       = now()
				, count	           = {count}
				where
				contractor_id      = {contractor_id}
				AND device_group_id  = {device_group_id}
				AND device_role      = {device_role}
				";
				DB::update($sql, $param);
			}
		}
	}
}


// file_put_contents("/var/www/html/ui1/_pg/app/_batch/test.log", "通ってる", FILE_APPEND);
// ob_start();
// var_dump($param);
// $dump = ob_get_contents();
// ob_end_clean();
// file_put_contents("/var/www/html/ui1/_pg/app/_batch/test.log", $dump, FILE_APPEND);
