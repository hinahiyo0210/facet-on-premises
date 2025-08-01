<?php 

// システムエラー。
class SystemException extends Exception {
	
	public function __construct($name = false, $msg = false) {
		if (!empty($msg)) {
			Errors::add($name, $msg);
		}
		parent::__construct("");
	}
	
}