<?php

// メッセージフォーマットのデフォルト
define("VALIDATOR_DEFAULT_MESSAGE_FORMAT", "[prefix]{label}[suffix]{message}");
define("VALIDATOR_DEFAULT_ARRAY_MESSAGE_FORMAT", "[prefix]{label}（{rowNo}個目）[suffix]{message}");

// 日付許容のデフォルト
define("VALIDATOR_DEFAULT_DATE_FROM", 1900);
define("VALIDATOR_DEFAULT_DATE_TO", 2050);

class Validator {

	public $name;

	public $data;

	public $label;

	protected $array = false;

	protected $arrayAllowEmpty = false;

	protected $arrayErrored = false;

	protected $validated = false;

	protected $errored = false;

	public $ifContinue = true;
	public $lastIfValidator = null;

	protected $messageFormat = false;

	protected $arrayMessageFormat = false;

	protected $allDatas;

	public function __construct($name, $label, $data, $allDatas = false) {

		$this->name = $name;
		$this->label = $label;
		$this->data = $data;
		if ($this->label == null) {
			$this->label = $this->name;
		}
		$this->allDatas = $allDatas;
	}

	// -----------------
	/**
	 * @var Validators validators
	 */
	protected $validators;
	public function setValidators($validators) {
		$this->validators = $validators;
	}
	/**
	 * @return Validator
	 */
	public function at($name, $value) {
		$lastIfValidator = $this->lastIfValidator;
		$this->lastIfValidator = null;
		return $this->validators->atByValidator($name, $value, $this->ifContinue, $lastIfValidator);
	}
	/**
	 * @return array
	 */
	public function getValidatedData() {
		return $this->validators->getValidatedData();
	}

	public function getThisValidatedData() {

		if (!$this->isValidated()) return null;
		if ($this->isErrored()) return null;

		return $this->data;
	}
	// -----------------


	public function getAllDatas() {
		return $this->allDatas;
	}

	public function setMessageFormat($messageFormat) {
		$this->messageFormat = $messageFormat;
	}
	public function setArrayMessageFormat($arrayMessageFormat) {
		$this->arrayMessageFormat = $arrayMessageFormat;
	}
	public function isArray() {
		return $this->array;
	}

	public function isValidated() {
		return $this->validated;
	}

	public function isErrored() {
		return $this->errored;
	}

	private static function mb_str_replace($haystack, $search,$replace, $offset=0,$encoding='utf8'){
	    $len_sch=mb_strlen($search,$encoding);
	    $len_rep=mb_strlen($replace,$encoding);

	    while (($offset=mb_strpos($haystack,$search,$offset,$encoding))!==false){
	        $haystack=mb_substr($haystack,0,$offset,$encoding)
	            .$replace
	            .mb_substr($haystack,$offset+$len_sch,1000000,$encoding);
	        $offset=$offset+$len_rep;
	        if ($offset>mb_strlen($haystack,$encoding))break;
	    }
	    return $haystack;
	}

	public function addError($msgPrefix, $msg, $rowNo = false) {

		if ($rowNo === false) {
			if (empty($this->messageFormat)) {
				$this->messageFormat = VALIDATOR_DEFAULT_MESSAGE_FORMAT;
			}
			$errorMessage = self::mb_str_replace($this->messageFormat, "{label}", $this->label);
			$errorMessage = self::mb_str_replace($errorMessage, "{message}", $msg);
			$name = $this->name;

		} else {
			if (empty($this->arrayMessageFormat)) {
				$this->arrayMessageFormat = VALIDATOR_DEFAULT_ARRAY_MESSAGE_FORMAT;
			}
			$errorMessage = self::mb_str_replace($this->arrayMessageFormat, "{label}", $this->label);
			$errorMessage = self::mb_str_replace($errorMessage, "{message}", $msg);
			$errorMessage = self::mb_str_replace($errorMessage, "{rowNo}", ($rowNo + 1));
			$name = $this->name."[".$rowNo."]";
		}

		$errorMessage = $msgPrefix.$errorMessage;

		$this->errored = true;

		Errors::add($name, $errorMessage);
	}

	/**
	 * 値が空である場合にtrueを返す
	 * @param $val
	 */
	public static function isEmpty($val) {

		if ($val == null) {
			return true;
		}

		if (is_array($val)) {

			if (count($val) == 0) {
				return true;
			} else {
				return false;
			}

		}

		return trim($val) == "";
	}

	/**
	 * 指定文字列が存在している場合にtrueを返す
	 * @param string $txt 対象
	 * @param string $val 存在しているかどうかをチェックする文字列
	 */
	public static function exists($txt, $val) {
		if ($val == null) return false;
		if (is_array($txt)) {
			return self::isInArray($txt, $val);
		} else {
			return mb_strpos($txt, $val) !== false;

		}
	}

	/**
	 * 配列要素中に指定値がある場合にtrueを返す
	 * @param unknown_type $arr
	 * @param unknown_type $txt
	 */
	private static function isInArray($arr, $txt) {
		if ($txt == null) return false;
		if ($arr == null) return false;

		foreach ($arr as $val) {
			if ($val == $txt) return true;
		}

		return false;
	}



	public static function isDate($val, $min = false, $max = false) {

		if ($min === false) $min = VALIDATOR_DEFAULT_DATE_FROM;
		if ($max === false) $max = VALIDATOR_DEFAULT_DATE_TO;

		if(preg_match("*^([0-9]{4})[-/ \.]([01]?[0-9])[-/ \.]([0123]?[0-9])$*", $val, $parts)) {
	    	if (checkdate($parts[2], $parts[3], $parts[1])) {
	    		if ($parts[1] >= $min && $parts[1] <= $max) {
					return true;
	    		}
	    	}
	  	}

	  	return false;
	}

	public static function isDateTime($val, $min = false, $max = false) {

		if ($min === false) $min = VALIDATOR_DEFAULT_DATE_FROM;
		if ($max === false) $max = VALIDATOR_DEFAULT_DATE_TO;

		if(preg_match("*^([0-9]{4})[-/ \.]([01]?[0-9])[-/ \.]([0123]?[0-9]) ([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})$*", $val, $parts)) {
			if (checkdate($parts[2], $parts[3], $parts[1])) {

				if ($parts[4] > 23) return false;
    			if ($parts[5] > 59) return false;
    			if ($parts[6] > 59) return false;

    			if ($parts[1] < $min) return false;
    			if ($parts[1] > $max) return false;

    			return true;
	    	}
	  	}

	  	return false;
	}

	// 必須チェック(指定された値のうち、どれか一つに値が入っている場合に全てが埋まっている事が必須となる。)
	// 返却値には空であった項目名の配列を返す。
	public static function getMissings(array $values) {

		$allEmpty = true;
		$ret = array();

		foreach ($values as $key=>$value) {
			if (self::isEmpty($value)) {
				$ret[] = $key;
			} else {
				$allEmpty = false;
			}
		}

		if ($allEmpty) return array();

		return $ret;
	}


	public static function validateMultiLineMissings($form, array $targets) {

		$count = 0;
		foreach ($targets as $key=>$label) {
			if (empty($form[$key])) continue;
			if (count($form[$key]) > $count) {
				$count = count($form[$key]);
			}
		}

		for ($i = 0; $i < $count; $i++) {
			$check = array();
			foreach ($targets as $key=>$label) {
				$check[$key] = arr($form[$key], $i);
			}
			$erroredKeys = self::getMissings($check);
			foreach ($erroredKeys as $erroredKey) {
				Errors::add($erroredKey."[".$i."]", $targets[$erroredKey]."(".($i + 1)."個目)が入力されていません。");
			}
		}
	}


	// ----------------------------------------------------------------------------------------------------------

	protected function validate($validateFunction) {

		if ($this->arrayErrored) return $this;
		if (!$this->ifContinue) return $this;

		$this->validated = true;

		if ($this->array) {

			if (!is_array($this->data) && !(empty($this->data) && $this->arrayAllowEmpty)) {
				$this->addError("", "の形式が不正です。");

			} else {
				if (!is_array($this->data)) $this->data = [];
				$len = count($this->data);

				for ($i = 0; $i < $len; $i++) {
					$v = null;

					if (isset($this->data[$i])) {
						$v = $this->data[$i];
					}

					if (Validator::isEmpty($v)) continue;
					if (!$validateFunction($this, $v, $i)) {
						$success = false;
					}
				}


			}

		} else {
			if (Validator::isEmpty($this->data)) return $this;

			if (is_array($this->data)) {
				$this->addError("", "の形式が不正です。");
			} else {
				$validateFunction($this, $this->data, false);
			}

		}


		return $this;
	}

	// ----------------------------------------- ここから下、検証用メソッド

	// 配列値の場合、事前に呼び出す(requiredよりも先に呼び出す必要がある)
	public function arrayValue($allowEmpty = true) {

		if (!$this->ifContinue) return $this;

		$this->array = true;
		$this->arrayAllowEmpty = $allowEmpty;

		if (Validator::isEmpty($this->data)) {
			if (!is_array($this->data)) $this->data = [];
			return $this;
		}
		if (is_array($this->data)) return $this;
		if ($allowEmpty && empty($this->data)) return $this;

		$this->addError("", "の形式が不正です。");
		$this->arrayErrored = true;
		return $this;
	}

	// 特定の区切り文字による配列値の場合、事前に呼び出す(requiredよりも先に呼び出す必要がある)
	public function exlopdeArrayValue($delim) {

		if (!$this->ifContinue) return $this;

		$allowEmpty = true;
		$this->array = true;
		$this->arrayAllowEmpty = $allowEmpty;

		if (Validator::isEmpty($this->data)) {
			$this->data = [];
			return $this;
		}
	
		$this->data = explode($delim, $this->data);
		return $this;
	}
	
	
	
	// 必須チェック
	public function required($errorSuffix = "は必須です。") {

		if ($this->arrayErrored) return $this;

		if (!$this->ifContinue) return $this;

		if ($this->array) {

			if (count($this->data) == 0) {
				$this->addError("", $errorSuffix);
				return $this;
			}

			$allEmpty = true;

			foreach ($this->data as $v) {
				if (!Validator::isEmpty($v)) {
					$allEmpty = false;
					break;
				}
			}
			if ($allEmpty) {
				$this->addError("", $errorSuffix);
			}
			return $this;

		} else {

			if (Validator::isEmpty($this->data)) {
				$this->addError("", $errorSuffix);
				return $this;
			}

		}

		return $this;
	}

	// 配列の要素数（最大）
	public function arrayMaxCount($count) {

		if ($this->arrayErrored) return $this;
		if (!$this->ifContinue) return $this;

		if (count($this->data) == 0) return $this;

		if (count($this->data) > $count) {
			$this->addError("", "の数が多すぎます。".$count."個以内で入力してください。");
		}
		return $this;
	}



	/**
	 * バイト数
	 * @param unknown $len
	 * @return Validator
	 */
	public function byteMaxlength($len) {

		return $this->validate(function($validator, $value, $rowNo) use($len) {

			$valLen = strlen($value);

			if ($valLen > $len) {
				$validator->addError("", "の容量数が多すぎます。".$len."バイト以内で入力してください。", $rowNo);
				return false;
			}

		});
	}


	/**
	 * HTML特殊文字。CSPは表示時にHTMLエスケープされないようなので、ここで食い止める。
	 * @param unknown $len
	 * @return Validator
	 */
	public function htmlspchars() {

		return $this->validate(function($validator, $value, $rowNo) {

			$hVal = h($value);

			for ($i = 0; $i < mb_strlen($value); $i++) {
				$c = mb_substr($value, $i, 1);
				$hc = mb_substr($hVal, $i, 1);
				if ($c !== $hc) {
					$validator->addError("", "に使用出来ない文字が含まれています「{$c}」。", $rowNo);
					return false;
				}
			}

		});
	}


	/**
	 * 文字数（最大）
	 * @param unknown $len
	 * @return Validator
	 */
	public function maxlength($len) {

		return $this->validate(function($validator, $value, $rowNo) use($len) {

			$valLen = mb_strlen($value);

			if ($valLen > $len) {
				$validator->addError("", "の文字数が多すぎます。".$len."文字以内で入力してください。", $rowNo);
				return false;
			}

		});
	}


	// 文字数（最小）
	public function minlength($len) {

		return $this->validate(function($validator, $value, $rowNo) use($len) {

			$valLen = mb_strlen($value);

			if ($valLen < $len) {
				$validator->addError("", "の文字数が少なすぎます。".$len."文字以上で入力してください。", $rowNo);
				return false;
			}

		});

	}

	// 文字数（固定）
	public function fixlength($len) {

		return $this->validate(function($validator, $value, $rowNo) use($len) {

			$valLen = mb_strlen($value);

			if ($valLen != $len) {
				$validator->addError("", "は".$len."文字で入力してください。", $rowNo);
				return false;
			}

		});
	}


	// 数値（ID）
	public function id() {
		return $this->digit(1, 200000000);
	}


	// 数値（整数）
	public function digit($min = 0, $max = 200000000) {

		$validateFunction = function($validator, $value, $rowNo) use ($min, $max) {
			$value = $value."";

			if (is_numeric($value)) {

				$digitResult = true;

				$checkVal = $value;
				if ($min < 0 && mb_substr($value, 0, 1) == "-") {
					$checkVal = mb_substr($value, 1, mb_strlen($value));
				}

				if (!ctype_digit($checkVal)) {
					$digitResult = false;
				}

				if ($digitResult) {

					if ($min !== false) {
						if ($value < $min) {
							$validator->addError("", "は".$min."以上の数字で入力してください。", $rowNo);
				    		return false;
				    	}
					}

					if ($max !== false) {
						if ($value > $max) {
							$validator->addError("", "は".$max."以下の数字で入力してください。", $rowNo);
				    		return false;
				    	}
					}
					
			    	return true;
				}

			}

			$validator->addError("", "は半角数字で入力してください。", $rowNo);
			return false;
		};

		return $this->validate($validateFunction);
	}

	// 数値（小数点）
	// $scaleに小数点以下桁数を指定
	public function float($scale, $min = 0, $max = 200000000) {

		return $this->validate(function($validator, $value, $rowNo) use($scale, $min, $max) {

			$checkVal = $value;
			if ($min < 0 && mb_substr($value, 0, 1) == "-") {
				$checkVal = mb_substr($value, 1, mb_strlen($value));
			}
				
			if (!is_numeric($checkVal)) {
				$validator->addError("", "は小数点を含む半角数値形式で入力してください。", $rowNo);
				return false;
			}

			$split = explode(".", $checkVal);

			if (count($split) == 1) {
				$left = $split[0];
				$right = "0";
			} else if (count($split) == 2) {
				$left = $split[0];
				$right = $split[1];
			} else {
				$validator->addError("", "は小数点を含む半角数値形式で入力してください。", $rowNo);
				return false;
			}

			if (!ctype_digit($left) || !ctype_digit($right)) {
				$validator->addError("", "は小数点を含む半角数値形式で入力してください。", $rowNo);
				return false;
			}

			// 小数点桁数
			if (strlen($right."") > $scale) {
				$validator->addError("", "は小数点以下第{$scale}位までで入力してください。", $rowNo);
				return false;
			}

			if ($min !== false) {
				if ($checkVal < $min) {
					$validator->addError("", "は".$min."以上の数字で入力してください。", $rowNo);
		    		return false;
		    	}
			}

			if ($max !== false) {
				if ($checkVal > $max) {
					$validator->addError("", "は".$max."以下の数字で入力してください。", $rowNo);
		    		return false;
		    	}
			}

			return true;
		});
	}

	// 0 or 1 のみを許容
	public function flag() {
		return $this->validate(function($validator, $value, $rowNo) {

			if ($value === 0 || $value === "0" || $value === 1 || $value === "1") return true;

			$validator->addError("", "の値が不正です。", $rowNo);

			return false;
		});
	}

	// ひらがなのみを許容
	public function hiragana() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (preg_match("/^[ぁ-ん]+$/u", $value)) {
				return true;
			}

			$validator->addError("", "は、ひらがなで入力してください。", $rowNo);
			return false;

		});

	}

	// カタカナのみを許容
	public function katakana() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (preg_match("/^[ァ-ヶー]+$/u", $value)) {
				return true;
			}

			$validator->addError("", "は、カタカナで入力してください。", $rowNo);
			return false;
		});

	}


	// enumチェック
	public function enum($enum = false) {

		if ($enum === false) {
			$name = $this->name;
			$enum = Enums::$name();
		}

		return $this->validate(function($validator, $value, $rowNo) use($enum) {

			if (!isset($enum[$value])) {
				$validator->addError("", "の値が不正です。", $rowNo);
				return false;
			}
			return true;
		});
	}
	
	// enumチェック
	public function simpleEnum($enum = false) {

		if ($enum === false) {
			$name = $this->name;
			$enum = SimpleEnums::getAll($this->name);
		} else if (is_string($enum)) {
			$enum = SimpleEnums::getAll($enum);
		}

		return $this->validate(function($validator, $value, $rowNo) use($enum) {

			if (!isset($enum[$value])) {
				$validator->addError("", "の値が不正です。", $rowNo);
				return false;
			}
			return true;
		});
	}
	
	
	

	// 許容値（配列で指定）チェック
	public function inArray($allows) {

		return $this->validate(function($validator, $value, $rowNo) use($allows) {

			foreach ($allows as $a) {
				if ($a."" === $value."") {
					return true;
				}
			}

			$validator->addError("", "の値が不正です。", $rowNo);
			return false;
		});

	}

	// SQLでデータの存在チェック
	// $errorExist をtrueにした場合、データが存在した場合にエラーとなり、
	// falseを指定した場合には、データが存在しない場合ににエラーとなる
	public function dataExists($errorExist, $sql, $params = false) {

		// 不正なデータはDBに投げたくないので検証しない
		if ($this->errored) return $this;

		if ($params === false) {
			$params = $this->allDatas;
		}

		return $this->validate(function($validator, $value, $rowNo) use($errorExist, $sql, $params) {

			if (is_array($params) && !isset($params["value"])) {
				$params["value"] = $value;
			}

			$exists = DB::exists($sql, $params);

			if ($exists && $errorExist) {
				$validator->addError("入力された", "は既に利用されています。", $rowNo);
				return false;
			}

			if (!$exists && !$errorExist) {
				$validator->addError("入力された", "は存在しません。", $rowNo);
				return false;
			}

			return true;
		});
	}


	// 日付チェック
	public function date($minYear = false, $maxYear = false) {

		return $this->validate(function($validator, $value, $rowNo) use ($minYear, $maxYear) {

			if (Validator::isDate($value, $minYear, $maxYear)) return true;

			$validator->addError("", "の日付形式が不正です。", $rowNo);
			return false;
		});

	}

	// 日時チェック
	public function dateTime($min = false, $max = false) {

		return $this->validate(function($validator, $value, $rowNo) use($min, $max) {

			if (Validator::isDateTime($value, $min, $max)) return true;

			$validator->addError("", "の日時形式が不正です。", $rowNo);
			return false;
		});

	}

	
	// 日付大小チェック：引数にfrom側の日付の名称を入れる
	// 同一日を許容する場合には$inclusiveにてtrueを指定する。
	public function compFuture($fromName, $fromLabel, $inclusive = false) {

		if ($this->errored) return $this;

		return $this->validate(function($validator, $value, $rowNo) use($fromName, $fromLabel, $inclusive) {

			if (Validator::isEmpty($value)) return true;
			if (!Validator::isDate($value)) return true;

			$compDatas = $this->getAllDatas();
			$compValue = arr($compDatas, $fromName);

			if ($rowNo !== false) {
				$compValue = arr($compValue, $rowNo);
			}

			if (Validator::isEmpty($compValue)) return true;
			if (!Validator::isDate($compValue)) return true;

			$valid = false;

			if ($inclusive) {
				if (strtotime($value) >= strtotime($compValue)) {
					$valid = true;
				}
				$msg = "は".$fromLabel."と同日か、未来の日付で指定してください。";
			} else {
				if (strtotime($value) > strtotime($compValue)) {
					$valid = true;
				}
				$msg = "は".$fromLabel."よりも未来の日付で指定してください。";
			}

			if ($valid) return true;

			$validator->addError("", $msg, $rowNo);
			return false;
		});

	}

	// 日時大小チェック：引数にfrom側の日時の名称を入れる
	// 同一日時を許容する場合には$inclusiveにてtrueを指定する。
	public function compFutureTime($fromName, $fromLabel, $inclusive = false) {

		if ($this->errored) return $this;

		return $this->validate(function($validator, $value, $rowNo) use($fromName, $fromLabel, $inclusive) {

			if (Validator::isEmpty($value)) return true;
			if (!Validator::isDateTime($value)) return true;

			$compDatas = $validator->getAllDatas();
			$compValue = arr($compDatas, $fromName);

			if ($rowNo !== false) {
				$compValue = arr($compValue, $rowNo);
			}

			if (Validator::isEmpty($compValue)) return true;
			if (!Validator::isDateTime($compValue)) return true;

			$valid = false;

			if ($inclusive) {
				if (strtotime($value) >= strtotime($compValue)) {
					$valid = true;
				}
				$msg = "は".$formLabel."と同日時か、未来の日時で指定してください。";
			} else {
				if (strtotime($value) > strtotime($compValue)) {
					$valid = true;
				}
				$msg = "は".$formLabel."よりも未来の日時で指定してください。";
			}

			if ($valid) return true;

			$validator->addError("", $msg, $rowNo);
			return false;
		});

	}


	/**
	 * メールアドレス
	 * @return Validator
	 */
	public function mail() {

		return $this->validate(function($validator, $value, $rowNo) {

			$err = strlen($value) != mb_strlen($value) || !Validator::exists($value, "@") || strlen($value) > 255;

			if (!$err) {
				$err = Validator::exists($value, ",") || Validator::exists($value, "\r") || Validator::exists($value, "\n");
			}

			if ($err) {
				$validator->addError("", "が正しいEメールアドレスの形式ではありません。", $rowNo);
				return false;
			}
			return true;
		});
	}


	/**
	 * パスワード
	 * @return Validator
	 */
	public function password() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (mb_strlen($value) < 8) {
				$validator->addError("", "は8文字以上で入力してください。", $rowNo);
				return false;
			}

			if (mb_strlen($value) > 20) {
				$validator->addError("", "は20文字以内で入力してください。", $rowNo);
				return false;
			}

			if (mb_strlen($value) > 20) {
				$validator->addError("", "は20文字以内で入力してください。", $rowNo);
				return false;
			}

			// なんか色々とロジック。半角英数と記号を許可。できれば柔らかい制限が良い。


			return true;
		});

	}

	// 同一性チェック
	public function compSame($compName, $compLabel) {

		if ($this->errored) return $this;

		return $this->validate(function($validator, $value, $rowNo) use($compName, $compLabel) {

			$compDatas = $validator->getAllDatas();

			$compValue = arr($compDatas, $compName);
			if ($rowNo !== false) {
				$compValue = arr($compValue, $rowNo);
			}

			if (Validator::isEmpty($compValue)) return true;

			if ($value != $compValue) {
				$validator->addError("", "の内容が".$compLabel."と異なっています。", $rowNo);
				return false;
			}

			return true;
		});

	}


	// 同一性チェック(同一の場合にエラー)
	public function compNotSame($compName, $compLabel) {

		if ($this->errored) return $this;

		return $this->validate(function($validator, $value, $rowNo) use($compName, $compLabel) {

			$compDatas = $validator->getAllDatas();

			$compValue = arr($compDatas, $compName);
			if ($rowNo !== false) {
				$compValue = arr($compValue, $rowNo);
			}

			if (Validator::isEmpty($compValue)) return true;

			if ($value == $compValue) {
				$validator->addError("", "の内容が".$compLabel."と同一です。", $rowNo);
				return false;
			}

			return true;
		});

	}
	
	
	// 半角英数
	public function alphaNum() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (preg_match("/^[a-zA-Z0-9]+$/", $value)) {
				return true;
		  	}

			$validator->addError("", "が半角英数形式ではありません。", $rowNo);
			return false;
		});

	}

	// 郵便番号
	public function postal() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (preg_match("/^\d{3}\-\d{4}$/", $value)) {
				return true;
		  	}

			$validator->addError("", "は000-0000の形式で入力してください。", $rowNo);
			return false;
		});

	}

	// 関数
	public function func($function) {

		return $this->validate(function($validator, $value, $rowNo) use($function) {

			$errorMessage = $function($value);

			if ($errorMessage === true) {
				return;
			}

			if ($errorMessage === false) {
				$validator->addError("", "が不正です。", $rowNo);
			} else {
				$validator->addError("", $errorMessage, $rowNo);
			}
			return false;
		});

	}



	// 固定電話
	public function tel() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (preg_match("/^0((\\d{1,2}-?\\d{1,4}|\\d{3,4}-?\\d{1,2})-?\\d{4}|120-?\\d{3}-?\\d{3})$/", $value)) {
				return true;
		  	}

			$validator->addError("", "の形式が不正です。03-0000-0000 / 0426-00-0000 といった形式で入力してください。", $rowNo);
			return false;
		});

	}

	// 携帯電話
	public function mobile() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (preg_match("/^0[1-9]{1}0\-[0-9]{4}\-[0-9]{4}$/", $value)) {
				return true;
		  	}

			$validator->addError("", "の形式が不正です。090-0000-0000 といった形式で入力してください。", $rowNo);
			return false;
		});

	}

	// 番地
	public function streetNum() {

	    return $this->validate(function($validator, $value, $rowNo) {

	            if (preg_match('/[0-9０-９一-九十]+/u', $value)) {
	            return true;
	        }

	        $validator->addError("", "の番号が入力されていません。", $rowNo);
	        return false;
	    });

	}

	// GUID
	public function guid() {

		return $this->validate(function($validator, $value, $rowNo) {

			if (preg_match("/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/", $value)) {
				return true;
		  	}

			$validator->addError("", "が不正です。", $rowNo);
			return false;
		});

	}

	// このValidatorについて、エラーが発生していないようであれば実行。
	public function noErrored($function) {
		if ($this->errored) return $this;
		return $this->validate(function($validator, $value, $rowNo) use($function) {
			$function($value);
		});
	}


	// ------------------------------------------ if系

	private function isMatch($values) {

		if ($values == null) {
			if ($this->array) {
				return $this->data == null || count($this->data) == 0;
			} else {
				return $this->data == null;
			}

		}

		if (!is_array($values)) {
			$values = array($values);
		}

		if ($this->array) {

			if (count($this->data) == 0 && count($values) == 0) {
				return true;
			}

			foreach ($values as $i=>$v) {
				if ($this->data[$i] != $v) {
					return false;
				}
			}

		} else {
			foreach ($values as $i=>$v) {
				if ($this->data != $v) {
					return false;
				}
			}
		}

		return true;
	}

	public function ifThen($callback) {
		$this->ifContinue = $callback($this->getValidatedData());
		return $this;
	}
	public function ifElse($callback) {
		$this->ifContinue = !$callback($this->getValidatedData());
		return $this;
	}

	/**
	 * 指定値と同一である場合に続行。
	 * @return Validator
	 */
	public function ifEquals($values = null) {

		if ($this->lastIfValidator) {
			$this->lastIfValidator->ifEquals($values);
			$this->ifContinue = $this->lastIfValidator->ifContinue;

		} else {
			$this->ifContinue = $this->isMatch($values);
			$this->lastIfValidator = $this;

		}
		return $this;
	}

	/**
	 * 指定値と同一で無い場合に続行。
	 * @return Validator
	 */
	public function ifNotEquals($values = null) {

		if ($this->lastIfValidator) {
			$this->lastIfValidator->ifNotEquals($values);
			$this->ifContinue = $this->lastIfValidator->ifContinue;

		} else {
			$this->ifContinue = !$this->isMatch($values);
			$this->lastIfValidator = $this;
		}
		return $this;
	}

	/**
	 * ifに関連する続行判定を初期化。
	 * @return Validator
	 */
	public function ifEnd() {
		$this->ifContinue = true;
		$this->lastIfValidator = null;
		return $this;
	}




}

