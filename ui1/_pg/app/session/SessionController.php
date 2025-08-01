<?php

class SessionController extends UserBaseController {
	
	// @Override
	public function prepare(&$form) {

	}
	
	// トップ
	public function indexAction(&$form) {
		
		response400();
	}

	/*
	* setSessionAction
	*
	* url長さ対応、jsonからの長い入力項目をセッションへセットし、セッションキーを返却する。
	*/
	public function setSessionAction(&$form) {
		setJsonHeader();
		
		$data = Validators::set($form)
			->at("session_key", "session_key")->maxlength(3)
			->at("value","value")->required()->byteMaxlength(1024 * 1024  * 5)
			->getValidatedData();
		
		if (Errors::isErrored()) {
			echo json_encode(["error"=>join(" / " , Errors::getMessagesArray())]);
			return;
		}
		$value = [];
		foreach (json_decode($data["value"], true, 4) as $entity) {
			$value[$entity['key']] = $entity['value'];
		}
		if (empty($data["session_key"])) {
			$data["session_key"] = Session::setData($value, 'dsFormSession');
		} else {
			Session::replaceData($data["session_key"], $value, 'dsFormSession');
		}
		
		echo json_encode(["error"=>false,"session_key"=>$data["session_key"]]);
		return;
	}
}


