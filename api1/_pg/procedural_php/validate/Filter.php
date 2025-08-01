<?php 
class Filter {
		

	/**
	 * 配列要素の重複を排除する。
	 * @param string $val 対象の配列
	 */
	public static function distinct($val) {
		
		if ($val == null || !is_array($val)) return array();
	
		$ret = array();
		
		foreach ($val as $v) {
			$ret[$v] = 1;
		}	
	
		return array_keys($ret);
	}
	
	
	/**
	 * 半角チェックを行い、不正な場合は$defaultValueの値を返す
	 * @param string $val 対象
	 * @param string $defaultValue 不正値の場合に返す値
	 */
	public static function narrow($val, $defaultValue = null) {
		
		if ($val == null) return $defaultValue;
	
		if (strlen($val) != mb_strlen($val)) {
			return $defaultValue;
		}
	
		return $val;
	}
	
	/**
	 * 数値（配列）チェックをを行い、不正な場合は$defaultValueの値を返す
	 * @param array $val 対象
	 * @param array $defaultValue 不正値の場合に返す値
	 * @param int $from 許容する数値範囲from
	 * @param int $to 許容する数値範囲to
	 */
	public static function digitArray($val, $defaultValue = array(), $from = 0, $to = 2147483647) {
		
		if ($val == null) return $defaultValue;
		
		if (is_array($val)) {
			$arr = $val;
		} else {
			$arr = array($val);
		}
		
		$ret = array();
		foreach ($arr as $v) {
			$num = self::digit($v, null, $from, $to);
			if ($num == null) continue;
			$ret[] = $num;
		}
		
		if (count($ret) == 0) return $defaultValue;
		
		return $ret;
	}
	
	/**
	 * 数値型チェックを行い、不正な場合は$defaultValueの値を返す。
	 * @param string $val 検証値
	 * @param int $defaultValue 不正な場合に返す値
	 * @param int $from 最小値
	 * @param int $to 最大値
	 */
	public static function digit($val, $defaultValue = null, $from = 0, $to = 2147483647) {
		
		if ($val == null) return $defaultValue;
		$val = $val."";
		
		if (!is_numeric($val)) {
			return $defaultValue;
		}
		
		if (!ctype_digit($val)) {
			return $defaultValue;
		}
		
		if ($val < $from) {
			return $defaultValue;
		}
		
		if ($val > $to) {
			return $defaultValue;
		}
		
		return $val;
	}
	
	
	/**
	 * フラグ値チェックを行い、不正な場合は$defaultValueの値を返す
	 * @param string $val 対象
	 * @param string $defaultValue 不正値の場合に返す値
	 */
	public static function flag($val, $default = "") {
		if (strComp($val, "1") || strComp($val, "0")) return $val;
		return $default;
	}
	
	/**
	 * 文字列長チェックを行い、超過している場合はその長さに切り詰める
	 * @param string $val 対象
	 * @param int $len チェックする文字数
	 */
	public static function len($val, $len) {
		if ($val == null) return $val;
		return mb_substr($val, 0, $len);	
	}
	
	/**
	 * 選択肢チェックを行い、不正な場合は$defaultの値を返す
	 * @param string $val 対象
	 * @param array $values 許容する値の配列
	 * @param string $default 不正値の場合に返す値
	 */
	public static function values($val, $values, $default = "") {
		if ($val == null) return $default;
		
		foreach ($values as $v) {
			if (strComp($val, $v)) {
				return $v;
			}
		}
		
		return $default;
	}
	
	
	/**
	 * optionに対応した選択肢チェックを行い、不正な場合は$defaultの値を返す
	 */
	public static function enum($val, $enum, $default = "") {
		if ($val == null) return $default;
		
		if (is_string($enum)) {
			$enum = Enums::$enum();
		}
		
		foreach ($enum as $k=>$v) {
			if (strComp($val, $k)) {
				return $k;
			}
		}
		
		return $default;
	}
	

	/**
	 * optionに対応した選択肢チェックを行い、不正な場合は$defaultの値を返す
	 */
	public static function simpleEnum($val, $enum, $default = "") {
		if ($val == null) return $default;
		
		if (is_string($enum)) {
			$enum = SimpleEnums::getAll($enum);
		}
		
		foreach ($enum as $k=>$v) {
			if (strComp($val, $k)) {
				return $k;
			}
		}
		
		return $default;
	}	
	
	/**
	 * optionに対応した選択肢チェックを行い、不正な場合は$defaultの値を返す
	 */
	public static function enumArray($val, $enum, $default = array()) {

		if ($val == null) return $default;

		if (is_array($val)) {
			$arr = $val;
		} else {
			$arr = array($val);
		}
		
		
		$ret = array();
		foreach ($arr as $v) {
			$optionValue = self::enum($v, $enum, null);
			if ($optionValue == null) continue;
			$ret[] = $optionValue;
		}
		
		if (count($ret) == 0) return array();
		
		return $ret;
	}
	
	
	/**
	 * optionに対応した選択肢チェックを行い、不正な場合は$defaultの値を返す
	 */
	public static function simpleEnumArray($val, $enum, $default = array()) {

		if ($val == null) return $default;

		if (is_array($val)) {
			$arr = $val;
		} else {
			$arr = array($val);
		}
		
		
		$ret = array();
		foreach ($arr as $v) {
			$optionValue = self::simpleEnum($v, $enum, null);
			if ($optionValue == null) continue;
			$ret[] = $optionValue;
		}
		
		if (count($ret) == 0) return array();
		
		return $ret;
	}	
		
	
	/**
	 * checkOptionの配列版
	 * @param array $val 対象
	 * @param array $options 許容するキー/値の配列
	 */
	public static function optionArray($val, $options) {
		
		if ($val == null) return array();
		
		if (is_array($val)) {
			$arr = $val;
		} else {
			$arr = array($val);
		}
		
		$ret = array();
		foreach ($arr as $v) {
			$optionValue = checkOption($v, $options, "");
			if ($optionValue == null) continue;
			$ret[] = $optionValue;
		}
		
		if (count($ret) == 0) return array();
		
		return $ret;
	}
	
	
	/**
	 * 日付チェックを行い、不正な場合は$defaultの値を返す
	 * @param string $val 対象
	 * @param string $default 不正値の場合に返す
	 */
	public static function datetime($val, $default = null) {
	
		if (empty($val)) return $default;
			
		if (Validator::isDateTime($val)) {
			return $val;
		}
		
		if (Validator::isDate($val)) {
			return $val;
		}
				
		return $default;
	}

	
	/**
	 * 日付チェックを行い、不正な場合は$defaultの値を返す
	 * @param string $val 対象
	 * @param string $default 不正値の場合に返す
	 */
	public static function datetime_m($val, $default = null) {
	
		if (isEmpty($val)) return $default;
		
			
		if (preg_match('/^([0-9]{4})\/([01]?[0-9])\/([0123]?[0-9]) ([0-9]?[0-9]):([0-9]?[0-9])$/', $val, $m)) {
			
			if (isDate($m[1]."/".$m[2]."/".$m[3]) && isTime($m[4], $m[5], 0)) {
				return $val;
			}
		}
		
		return $default;
	}
	
	
	/**
	 * 日付チェックを行い、不正な場合は$defaultの値を返す
	 * @param string $val 対象
	 * @param string $default 不正値の場合に返す
	 */
	public static function date($val, $default = null) {
		if (self::isEmpty($val)) return $default;
		if (!self::isDate($val)) return $default;
		return $val;
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
	 * 日付妥当性チェック
	 * @param unknown_type $val
	 */
	public static function isDate($val) {
		
		if(preg_match("*^([0-9]{4})[-/ \.]([01]?[0-9])[-/ \.]([0123]?[0-9])$*", $val, $parts)) {
			if (checkdate($parts[2], $parts[3], $parts[1])) {
	    		if ($parts[1] >= 1900 && $parts[1] <= 2050) {
					return true;
	    		}
	    	}
	  	}
		
	  	return false;
	}
		
	/**
	 * スペースで区切られた検索ワード
	 * 
	 */
	public static function searchWord($val, $len = 100) {
		
		$val = Filter::len($val, $len);
		$val = mb_str_replace($val, "　", " ");
		return $val;
	}
	
	
	// GUID
	public static function guid($value) {
		
		if (preg_match("/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/", $value)) {
			return $value;
	  	}
	  	return null;	  	
	}
	
	
	
	
}
