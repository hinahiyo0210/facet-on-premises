<?php

class ConfigController extends ApiBaseController {
	
	private $device;
	
	// @Override
	public function prepare(&$form) {
		$this->device = $this->getDeiveBySerialNo($form);
		
	}
	
	// 設定を取得。
	public function getConfigAction(&$form) {
		
		$ret = ConfigService::getConfig($this->device, ConfigService::getBasicConfigDefine()); 
		
		$this->responseJson($ret);
	}

	// 設定を更新。
	public function setConfigAction(&$form) {
		
		// 更新対象の定義を取得。
		$define = ConfigService::getBasicConfigDefine();
		
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
		$ret = ConfigService::setConfig($this->device, $define, $data, "setConfig"); 
		
		$this->responseJson($ret);
	}
	
	
	
	// ロゴ画像を取得。
	public function getLogoAction(&$form) {
		
		$ret = ConfigService::getAccessViewLogo($this->device); 
		
		$this->response(function() use ($ret) {
			
			header("Content-type: image/png");
			header("Content-Length: ".strlen($ret));		
			echo $ret;
			
		});
	}
	
	// ロゴ画像を設定。
	public function setLogoAction(&$form) {
		
		// 入力チェック。
		$data = Validators::set($form)
			->at("data", "data")->required()->byteMaxlength(1024 * 1024 * 5)
			->getValidatedData();
		
		// デコード後のバイナリがpng形式であるかをチェックする。
		$birnary = base64_decode($data["data"]);
		if (!isPngImage($birnary)) {
			throw new ApiParameterException("data", "画像ファイルはpng形式で指定してください。");
		}
		
		$ret = ConfigService::setAccessViewLogo($this->device, $data["data"]); 
		
		$this->responseJson(["result"=>true]);
	}
	

		
	
}
