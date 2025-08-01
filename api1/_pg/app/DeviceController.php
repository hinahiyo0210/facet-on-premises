<?php

class DeviceController extends ApiBaseController {
	
	// @Override
	public function prepare(&$form) {
	}
	
	// デバイスの一覧を取得。
	public function getDeviceAction(&$form) {
		
		$ret = [];
		foreach ($this->contractor["deviceList"] as $d) {
			$device = [];
			$device["serialNo"]    = $d["serial_no"];
			$device["description"] = $d["description"];
			if (isset($d["last_ws_access"]) && isset($d["last_push_access"])) {
        if (strtotime($d["last_ws_access"]) > strtotime($d["last_push_access"])) {
          $device["lastAccess"] = formatTime($d["last_ws_access"]);
        } else {
          $device["lastAccess"] = formatTime($d["last_push_access"]);
        }
			} else {
        if (isset($d["last_ws_access"]) || isset($d["last_push_access"])) {
          $device["lastAccess"] = (isset($d["last_ws_access"])) ? $d["last_ws_access"] : $d["last_push_access"];
        } else {
          $device["lastAccess"] = null;
        }
			}
			
			$device["lastRecog"]   = formatTime($d["last_recog"]);
			
			$ret[] = $device;
		}
		
		$this->responseJson($ret);
	}
	
	// デバイスを同期
	public function syncAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		// 同期処理。
		$sync_type = 2;
		$logResult = SyncService::syncDevice($device, $sync_type, basename(__FILE__));
		
		$this->responseJson($logResult);
	}

	// APB情報の修復を実行。
	public function apbRepaireAction(&$form) {
		
		if (empty($this->contractor["apb_mode_flag"])) {
			throw new ApiParameterException("contractor", "この契約情報はAPBモードが有効ではありません。");
		}
		
		$result = ApbService::repairAll($this->contractor["deviceList"]);
		
		$this->responseJson($result);
	}

	// 指定デバイスのpush_urlを取得する
	public function getPushUrlAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);
		
		$this->responseJson(["push_url"=>$device["push_url"]]);
	}

	// 指定デバイスのpush_urlを登録する（空入力の場合はNULLとなる）
	public function registPushUrlAction(&$form) {
		
		$device = $this->getDeiveBySerialNo($form);

		// 入力チェック。
		$data = Validators::set($form)
			->at("pushUrl", "pushUrl")->maxlength(200)
			->getValidatedData();

		$params = [];
		$params["serial_no"] = $device["serial_no"];
		$params["push_url"]  = $data["pushUrl"];

		if (empty($data["pushUrl"])) {
			// パラメーターが空の場合NULLで登録（削除と同義）
			DB::update("update m_device set push_url = NULL where serial_no = {serial_no}", $params);
		} else {
			DB::update("update m_device set push_url = {push_url} where serial_no = {serial_no}", $params);
		}
		
		$this->responseJson(["result"=>true]);
	}

	// t_push_response_messageの取得
	public function getPushResponseMsgAction(&$form) {

		// 登録上限数の取得＆ハンドリング
		$registLimit = 5;
		$countPushResponseMsg = DB::selectOne("SELECT COUNT(push_response_msg_id) FROM t_push_response_msg WHERE contractor_id = {value}", $this->contractor['contractor_id']);

		// 現状のpusResponsehMsgを取得
		$sql = '
			select 
				push_response_msg_id
				, priority
				, custom_tip
				, tips_time
				, border_color
				, background_color
				, device_group_id_set
				, device_id_set
				, person_id_set
				, pass_set
				, pass_flag_set
			from 
				t_push_response_msg
			where 
				contractor_id = {value}
		';
		$pushResponseMsgs = DB::selectArray($sql, $this->contractor['contractor_id']);
		$ret = [];
		$ret["registCount"] = (int)$countPushResponseMsg;
		$ret["registLimit"] = $registLimit;

		foreach ($pushResponseMsgs as $index => $pushResponseMsg) {
			$ret["content"][$index] = $pushResponseMsg;
		}
		
		$this->responseJson($ret);
		
	}

	// t_push_response_messageへの登録（コントラクタにつき上限5つ）
	public function registPushResponseMsgAction(&$form) {

		// 登録上限数の取得＆ハンドリング
		$registLimit = 5;
		$countPushResponseMsg = DB::selectOne("SELECT COUNT(push_response_msg_id) FROM t_push_response_msg WHERE contractor_id = {value}", $this->contractor['contractor_id']);
		if ($countPushResponseMsg >= $registLimit) {
			$this->responseJson(["result"=>false,"message"=>"登録上限数「{$registLimit}」に達しています。"]);
			exit;
		}

		// 入力チェック。
		$data = Validators::set($form)
			->at("custom_tip"         , "custom_tip"         )->required()->maxlength(30)
			->at("tips_time"          , "tips_time"          )->required()->digit(1,10000)
			->at("border_color"       , "border_color"       )->required()->maxlength(15)
			->at("background_color"   , "background_color"   )->required()->maxlength(15)
			->at("priority"           , "priority"           )->required()->digit(1,10)
			->at("device_group_id_set", "device_group_id_set")->digit(1)
			->at("device_id_set"      , "device_id_set"      )->digit(1)
			->at("person_id_set"      , "person_id_set"      )->digit(1)
			->at("pass_set"           , "pass_set"           )->digit(1)
			->at("pass_flag_set"      , "pass_flag_set"      )->digit(1)
			->at("door_index"         , "door_index"         )->digit(0)
			->at("open_delay"         , "open_delay"         )->digit(0)
			->getValidatedData();

		$data['contractor_id'] = $this->contractor['contractor_id'];

		$ret = DeviceService::registPushResponseMsg($data); 
		$this->responseJson($ret);
		
	}

	// t_push_response_messageの削除
	public function deletePushResponseMsgAction(&$form) {

		// 入力チェック。
		$data = Validators::set($form)
			->at("pushResponseMsgId", "pushResponseMsgId")->required()->digit(1,10000)
			->getValidatedData();
		
		// パラメーター格納
		$params = [];
		$params["contractor_id"]        = $this->contractor['contractor_id'];
		$params["push_response_msg_id"] = $data["pushResponseMsgId"];
		$where = " where contractor_id = {contractor_id} and push_response_msg_id = {push_response_msg_id}";

		// 指定IDが存在しない場合のエラー
		if (empty(DB::selectRow("select * from t_push_response_msg".$where, $params))) throw new ApiParameterException("pushResponseMsgId", "指定されたpushResponseMsgIdは存在しません。");

		DB::delete("delete from t_push_response_msg".$where, $params);

		$this->responseJson(["result"=>true]);
		
	}
	
}
