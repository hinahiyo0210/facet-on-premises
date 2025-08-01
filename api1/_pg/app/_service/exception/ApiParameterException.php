<?php 

// 入力チェック系エラー。
class ApiParameterException extends Exception {
	
	public $name;
	
	public $msg;
	
	public function __construct($name = false, $msg = false) {
		if (!empty($msg)) {
			Errors::add($name, $msg);
			$this->name = $name;
			$this->msg = $msg;
		}
		parent::__construct("");
	}
	
}