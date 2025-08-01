<?php 

// デバイス通信系エラー。
class DeviceWsException extends Exception {
	
	// 通信系エラーの場合はtrue。デバイスとの通信は成功しているが、デバイスが{"result: false}を返却した場合にはfalse。
	public $isConnectError;
	
	public function __construct($message, $isConnectError = true) {
		$this->isConnectError = $isConnectError;
		parent::__construct($message);
	}
	
}