<?php
require_once(dirname(__FILE__)."/Errors.php");
require_once(dirname(__FILE__)."/Validator.php");

// バリデータを少ないコード量で利用するためのクラス
class Validators {
	
	// バリデータのインスタンス
	public $validators;
	
	// 検証中のデータ一式。
	public $datas;
	
	
	private static $erroredFunction;
	
	public static function setErroredFunction($erroredFunction) {
		self::$erroredFunction = $erroredFunction;
	}
	
	/**
	 * 
	 * @param unknown $form
	 * @return Validators
	 */
	public static function set($form) {
		$v = new Validators(); 
		$v->datas = $form;
		$v->validators = array();
		return $v; 
	}
	
	private static function arr($datas, $name) {
		if (isset($datas[$name])) {
			return $datas[$name];
		}
		return null;
	}
	
	/**
	 * 
	 * @param unknown $name
	 * @param unknown $label
	 * @return Validator
	 */
	public function at($name, $label = false) {
		if ($label === false) {
			$label = $name;
		}
		$v = new Validator($name, $label, self::arr($this->datas, $name), $this->datas);
		$v->setValidators($this);
		$this->validators[$name] = $v;
		return $v;
	}			

	/**
	 * 
	 * @param Validator $validator
	 * @return Validator
	 */
	public function atByValidator($name, $label, $ifContinue, $lastIfValidator) {
		$v = $this->at($name, $label);
		$v->ifContinue = $ifContinue;
		$v->lastIfValidator = $lastIfValidator;
		return $v;
	}			
	
	// 一度でもgetメソッドが呼ばれ、エラーが発生していない事を根拠に「安全であろう」と思われるリクエストデータ配列を取得。
	public function getValidatedData() {
		
		$ret = array();

		foreach ($this->validators as $name=>$validator) {
			
			if (!$validator->isValidated()) {
				continue;
//				throw new Exception("Validators::getValidatedData()にて入力チェックを一度も実施していない項目を取得しようとしました。[".$name."]コーディングのミスが考えれます。");
			}

			if ($validator->isErrored()) {
				if (self::$erroredFunction) {
					$f = self::$erroredFunction;
					$f();
				}
				return false;
			}
			
			$ret[$name] = $validator->data;
		}

		return $ret;
	}
	
	
	
}

