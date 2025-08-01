<?php 

// デバイス排他エラー。
class DeviceExclusionException extends Exception {
	
	public function __construct($message) {
		 parent::__construct($message);
	}
	
}
