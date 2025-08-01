<?php

class SystemController extends ApiBaseController {
	
	private $device;
	
	// @Override
	public function prepare(&$form) {
		$this->device = $this->getDeiveBySerialNo($form);
		
	}
	
// 	// 隠しパラメータを取得
// 	public function getSaveMessageAction(&$form) {
		
// 		$ret = ConfigService::getConfig($this->device, ConfigService::getHiddenConfigDefine()); 
		
// 		$this->responseJson($ret);
// 	}
	
// 	// 隠しパラメータを設定
// 	public function setSaveMessageAction(&$form) {
		
// 		$define = ConfigService::getHiddenConfigDefine();
		
// 		// デバイスに反映。
// 		$ret = ConfigService::setConfig($this->device, $define, ["saveMessage"=>$form["val"]], "setSaveMessage"); 
		
// 		$this->responseJson($ret);
// 	}
	
	
	// システム情報を取得
	public function getSystemInfoAction(&$form) {
		
		$ret = SystemService::getSystemInfo($this->device); 
		
		// 成功の場合systemInfoの更新を行う
		DeviceService::updateSystemInfo($this->device, $ret);
		
		$this->responseJson($ret);
	}
	
	// 再起動
	public function rebootAction(&$form) {
		
		// 排他チェック。
		SyncService::checkExclusion($this->device["device_id"]);
		
		// 再起動を要求。
		SystemService::reboot($this->device); 
		
		$this->responseJson(["request"=>true]);
	}

	// ドア開錠
	public function openOnceAction(&$form) {
		
		infoLog("openOnceAction:form:".print_r(json_encode($form),true));
		infoLog("openOnceAction:device:".print_r(json_encode($this->device),true));
		$ret = SystemService::openOnce($this->device); 
		$this->responseJson($ret);
		
	}

	// デバイススタンバイモード
	public function setHibernateModeAction(&$form) {
		
		infoLog("setHibernateModeAction:form:".print_r(json_encode($form),true));
		infoLog("setHibernateModeAction:device:".print_r(json_encode($this->device),true));
		$ret = SystemService::setHibernateMode($this->device); 
		$this->responseJson($ret);
		
	}

	// デバイスアクティブモード
	public function setOperationModeAction(&$form) {
		
		infoLog("setOperationModeAction:form:".print_r(json_encode($form),true));
		infoLog("setOperationModeAction:device:".print_r(json_encode($this->device),true));
		$ret = SystemService::setOperationMode($this->device); 
		$this->responseJson($ret);
		
	}

	// デバイスモード状況確認
	public function getCurrentModeAction(&$form) {
		
		infoLog("getCurrentModeAction:form:".print_r(json_encode($form),true));
		infoLog("getCurrentModeAction:device:".print_r(json_encode($this->device),true));
		$ret = SystemService::getCurrentMode($this->device); 
		$this->responseJson($ret);
		
	}

	// 任意のメッセージ表示
	public function displayMessageAction(&$form) {

		// 入力チェック。
		$data = Validators::set($form)
			->at("Customtip"      , "Customtip"      )->required()->maxlength(25)
			->at("Tipstime"       , "Tipstime"       )->required()->digit(1)
			->at("BorderColor"    , "BorderColor"    )->required()->maxlength(100)
			->at("BackgroundColor", "BackgroundColor")->required()->maxlength(100)
			->getValidatedData();
		
		$customTipParams = [];
		$customTipParams['Type']            = 'tips';
		$customTipParams['Customtip']       = $data['Customtip'];
		$customTipParams['Tipstime']        = (int)$data['Tipstime'];
		$BorderColors     = explode(',', $data['BorderColor']);
		$BackgroundColors = explode(',', $data['BackgroundColor']);
		foreach ($BorderColors as $BorderColor) {
			if (empty($customTipParams['BorderColor'])) {
				$customTipParams['BorderColor'] = [(int)$BorderColor];
			} else {
				array_push($customTipParams['BorderColor'],(int)$BorderColor);
			}
		}
		foreach ($BackgroundColors as $BackgroundColor) {
			if (empty($customTipParams['BackgroundColor'])) {
				$customTipParams['BackgroundColor'] = [(int)$BackgroundColor];
			} else {
				array_push($customTipParams['BackgroundColor'],(int)$BackgroundColor);
			}
		}

		infoLog("displayMessageAction:form:".print_r($form,true));
		infoLog("displayMessageAction:device:".print_r($this->device,true));
		$ret = SystemService::displayMessage($this->device, $customTipParams); 
		$this->responseJson($ret);
		
	}
		
	// 自動再起動スケジュールを取得。
	public function getRebootScheduleAction(&$form) {
		
		$ret = ConfigService::getConfig($this->device, ConfigService::getRebootConfigDefine()); 
		
		$this->responseJson($ret);
	}

	// 自動再起動のスケジュールを更新。
	public function setRebootScheduleAction(&$form) {
		
		// 更新対象の定義を取得。
		$define = ConfigService::getRebootConfigDefine();
		
		// 入力チェック。
		$v = Validators::set($form);
		
		foreach ($define as $key=>$config) {
			if (empty($config["validate"])) continue;
		
			$validateFuction = $config["validate"];
			
			$v = $v->at($key, $key);
			$v = $validateFuction($v);
		}
		
		$data = $v->getValidatedData();
		if (Errors::isErrored()) $this->responseError();
		
		// デバイスに反映。
		$ret = ConfigService::setConfig($this->device, $define, $data, "setRebootSchedule"); 
		
		$this->responseJson($ret);
	}
	

	// 設定ファイルをエクスポート
	public function exportConfigAction(&$form) {
		
		$ret = ConfigService::exportConfig($this->device); 
		
		$fileName = "config_".$this->device["serial_no"]."_".date("YmdHis").".devcfg";
		
		$this->response(function() use ($fileName, $ret) {
			
			header("Content-Disposition: attachment; filename=${fileName}");
			header("Content-Length: ".strlen($ret));		
			echo $ret;
			
		});
		
	}
	

	// 設定ファイルをインポート
	public function importConfigAction(&$form) {
		
// 		// 入力チェック。
// 		$data = Validators::set($form)
// 			->at("data", "data")->required()->byteMaxlength(1024 * 1024 * 5)
// 			->getValidatedData();
		
// 		// 何かのバイナリチェック処理。
// // 		$ret = ConfigService::setAccessViewLogo($this->device, $data["data"]); 
		
// 		$this->responseJson(["error"=>false]);
		
		$this->responseJson(["info"=>"この機能は現在ご利用頂けません。"]);
				
	}
	


	// ファームウェアアップデート
	public function updateFirmwareAction(&$form) {
		
		// 入力チェック。
		$data = Validators::set($form)
			->at("version", "version")->required()->maxlength(100)->dataExists(false, "select 1 from m_firmware where version_name = {value}")	
			->getValidatedData();

		// 排他チェック。
		SyncService::checkExclusion($this->device["device_id"]);
		
		// 更新。
		$ret = SystemService::updateFirmware($this->device, $data["version"]); 
		
		$this->responseJson(["request"=>true]);
	}

}
