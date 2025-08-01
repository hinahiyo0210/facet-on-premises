<?php 

class DeviceService {
	
	// 最終認識時刻を更新。
	public static function updateDeviceLastTime($deviceId) {
		
		$param = [];
		$param["device_id"] = $deviceId;
		$param["last_recog"] = DB::selectOne("select max(recog_time) from t_recog_log where device_id = {device_id}", $param);
		
		DB::update("update m_device set last_recog = {last_recog} where device_id = {device_id}", $param);
		
	}
	
	// 最終WebSocket通信時刻を更新。
	public static function updateWsLastTime($serialNo, $timeSec) {
		
		$param = [];
		$param["serial_no"] = $serialNo;
		$param["last_ws_access"] = date("Y-m-d H:i:s", $timeSec);
		
		DB::update("update m_device set last_ws_access = {last_ws_access} where serial_no = {serial_no}", $param);
	}
	
	
	// デバイスに通信可能かどうか、のみを事前確認する。エラーがあればDeviceWsExceptionが飛ぶ。
	public static function checkDevice(array $device) {
		
		// システム情報を取得。
		SystemService::getSystemInfo($device);
		
	
	}
	
	// 最終WebSocket通信時刻を更新。
	public static function updateSystemInfo($device, &$systemInfo) {
		
		$param = [];
		$param["device_id"] = $device["device_id"];
		$param["device_type"] = $systemInfo["deviceType"];
		$param["fw_ver"] = $systemInfo["softwareVersion"];
		
		$updateSql = "	update
							m_device
						set
							device_type = {device_type},
							fw_ver = {fw_ver},
							last_get_systemInfo = now()
						where device_id = {device_id}";
		DB::update($updateSql, $param);
		$systemInfo['lastGetSystemInfo'] = DB::selectOne("select last_get_systemInfo from m_device where device_id = {value}", $device["device_id"]);
	}
	
	// t_push_response_messageへの登録（コントラクタにつき上限5つ）
	public static function registPushResponseMsg($params) {
		$sql = "
				insert into 
					t_push_response_msg 
				set
					contractor_id         = {contractor_id}
					, priority            = {priority}
				 	, device_group_id_set = {device_group_id_set}
					, device_id_set       = {device_id_set}
				 	, person_id_set       = {person_id_set}
					, pass_set            = {pass_set}
					, pass_flag_set       = {pass_flag_set}
					, custom_tip          = {custom_tip}
					, tips_time           = {tips_time}
					, border_color		  = {border_color}
					, background_color	  = {background_color}
					, door_index          = {door_index}
					, open_delay          = {open_delay}
			";
		DB::insert($sql, $params);

		return ["result"=>true];
	}

}