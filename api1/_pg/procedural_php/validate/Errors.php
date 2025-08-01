<?php 
// エラーメッセージを保持するクラス
class Errors {
	
	/** 項目名に付与するプレフィックス */
	private static $namePrefix = ""; 
	
	/** 項目名に付与するスフィックス */
	private static $nameSuffix = "";
	
	private static $prefix = "";
	
	/** 保持しているエラーメッセージ */
	private static $messages = array();
	
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
	
	
	/**
	 * メッセージを追加する
	 * @param string $name 項目名
	 * @param string $value メッセージ
	 */
	public static function add($name, $value) {
		
		$value = self::mb_str_replace($value, "[prefix]", self::$namePrefix);
		$value = self::mb_str_replace($value, "[suffix]", self::$nameSuffix);
		$value = self::mb_str_replace($value, "[name]", $name);
		
		$value = self::$prefix.$value;
		
		// 重複登録は除外。
		if (!empty(self::$messages[$name])) {
			foreach (self::$messages[$name] as $msg) {
				if ($msg == $value) return;
			}
		}
		
		self::$messages[$name][] = $value;
				
	}

	
	public static function clear() {
		$ret = self::$messages;
		self::$messages = array();
		return $ret;
	}
	
	public static function set($messages) {
		self::$messages = $messages;
	}
	
	/**
	 * プレフィックスを設定
	 * @param string $val 設定文字
	 */
	public static function setPrefix($val) {
		self::$prefix = $val;
	} 
	
	/**
	 * プレフィックスを取得
	 * @param string $val 設定文字
	 */
	public static function getPrefix() {
		return self::$prefix;
	} 

	
	/**
	 * プレフィックスを設定
	 * @param string $val 設定文字
	 */
	public static function setNamePrefix($val) {
		self::$namePrefix = $val;
	} 
	
	/**
	 * スフィックスを設定
	 * @param string $val 設定文字
	 */
	public static function setNameSuffix($val) {
		self::$nameSuffix = $val;
	} 
	
	/**
	 * メッセージが一件でも存在する場合はtrue
	 */
	public static function isErrored($name = null) {
		if ($name) {
			$arr = arr(self::$messages, $name);
			return !empty($arr);
		} else {
			return count(self::$messages) != 0;
		}
	}
	
	/**
	 * メッセージが一件でも存在する場合はfalse
	 */
	public static function isNotErrored($name = null) {
		return !self::isErrored($name);
	}

	/**
	 * 保持しているメッセージ配列を取得
	 */
	public static function getMessages() {
		return self::$messages;
	}
	
	
	public static function getMessage($name) {
		
		$ret = arr(self::$messages, $name);
		
		if ($ret == null) {
			$ret = array();
		}
		
		return $ret;
	}
	
	public static function getFirstMessage() {
		$arr = self::getMessagesArray();
		
		if (empty($arr)) {
			return array();
		}
		
		return $arr[0];
	}
	
	
	
	/**
	 * 保持しているメッセージ配列を単一の配列として取得
	 */
	public static function getMessagesArray() {
		
		$tmp = array();
		
		foreach (self::$messages as $key=>$messageArr) {
			foreach ($messageArr as $msg) {
				if ($msg == "") continue;
				$tmp[$msg] = "";
			}
		}
		
		$ret = array();
		foreach ($tmp as $msg=>$dummy) {
			$ret[] = $msg;
		}
		
		return $ret;
	}		
}

