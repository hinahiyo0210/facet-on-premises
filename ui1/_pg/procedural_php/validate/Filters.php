<?php
require_once(dirname(__FILE__)."/Filter.php");

// フィルタ機能を少ないコード量で利用するためのクラス
// フィルタした結果は参照元に影響を及ぼす。
class Filters {
	
	// 検証中の項目名。
	private $name;
	
	// 検証中の項目に対するデフォルト値。
	private $defaultValue;
	
	// 検証中のデータ一式。
	private $datas;
	
	// フィルタ済みのデータ名。
	private $filteredNames = array();

	private function __construct() {
		// 
	}
	
	
	/**
	 * データを参照渡しでインスタンスを取得。
	 * formにはフィルタ後の値が格納される形となるので、フィルタ後の値をユーザに見せたい場合はこちらを利用する。
	 * @return Filters filters 
	 */
	public static function ref(&$form) {
		$filters = new Filters();
		$filters->datas = &$form;
		return $filters;
	}
	
	/**
	 * データをコピー渡しでインスタンスを取得。
	 * @return Filters filters
	 */
	public static function set($datas) {
		$filters = new Filters();
		$filters->datas = $datas;
		return $filters;
	}
	
	/**
	 * @return Filters filters
	 */
	public function at($name, $defaultValue = null) {
		$this->name = $name;
		$this->defaultValue = $defaultValue;
		return $this;
	}
	
	/**
	 * @return Filters filters
	 */
	protected function filter($function) {
		
    $var = (isset($this->datas[$this->name])) ? $this->datas[$this->name] : null;
		$val = $function($var);
		if ($val == null) {
			$val = $this->defaultValue;
		}
		
		$this->datas[$this->name] = $val;
		$this->filteredNames[] = $this->name;
		return $this;
	}
	
	/**
	 * 整数
	 * 
	 */
	public function digit($from = 0, $to = 2147483647) {
		
		return $this->filter(function($value) use($from, $to) {
			
      $value = (isset($value)) ? mb_convert_kana($value, "n") : null;
			return Filter::digit($value, null, $from, $to);
			
		});
	}
	
	// 文字列長
	public function len($maxlength) {
		
		return $this->filter(function($value) use($maxlength) {
			
			return Filter::len($value, $maxlength);
		});
		
	}
	
	// 半角
	public function narrow() {

		return $this->filter(function($value) {
			
			$value = (isset($value)) ? mb_convert_kana($value, "as") : null;
			return Filter::narrow($value);
		});
	}
	
	// enum
	public function enum($enum = false) {
		if ($enum === false) $enum = $name;
		
		return $this->filter(function($value) use($enum) {
			
			return Filter::enum($value, $enum);
			
		});
	}
	
	// 整数の配列。必ず配列にする。
	public function digitArray($from = 0, $to = 2147483647) {
		
		if ($this->defaultValue == null) $this->defaultValue = array();
		
		return $this->filter(function($value) use($from, $to) {
			
			return Filter::digitArray($value, null, $from, $to);
			
		});
	}
	
	// enum配列。必ず配列にする。
	public function enumArray($enum = false) {
		if ($this->defaultValue == null) $this->defaultValue = array();
		if ($enum === false) $enum = $this->name;
		
		return $this->filter(function($value) use($enum) {
			
			return Filter::enumArray($value, $enum, null);
			
		});
	}
	
	// enum配列。必ず配列にする。 enumArrayと比べて、($optionValue === null)だけを除く
	public function enumArrayIdentical($enum = false) {
		if ($this->defaultValue == null) $this->defaultValue = array();
		if ($enum === false) $enum = $this->name;
		
		return $this->filter(function($value) use($enum) {
			
			return Filter::enumArrayIdentical($value, $enum, null);
			
		});
	}
	
	// 日時
	public function datetime() {
		
		return $this->filter(function($value) {
			
			return Filter::datetime($value);
			
		});
	}
	
	// 日付
	public function date() {
		
		return $this->filter(function($value) {
			
			return Filter::date($value);
			
		});
	}
	
	// 選択肢
	public function values($values) {

		return $this->filter(function($value) use($values) {
			
			return Filter::values($value, $values);
			
		});
	}
	
	// フラグ値（0 or 1）
	public function flag() {

		return $this->filter(function($value) {
			
			return Filter::flag($value);
			
		});
	}

	
	// enum
	public function simpleEnumArray($enum = false) {
	
		if ($enum === false) {
			$enum = $this->name;
		}
		
		return $this->filter(function($value) use ($enum) {
			
			return Filter::simpleEnumArray($value, $enum);
			
		});
	}
	
	
	// 関数
	public function func($func) {

		return $this->filter(function($value) use($func) {
			
			return $func($value);
		});
	}
	
	// 最後に検証された単一値の値を取得する。
	public function getLastData() {
		if (empty($this->filteredNames)) return null;
		$name = $this->filteredNames[count($this->filteredNames) - 1];
		return $this->datas[$name];
	}
	

	// フィルタ済みのデータ一式を取得する。
	public function getFilteredData() {

		$ret = array();
		foreach ($this->filteredNames as $name) {
			$ret[$name] = $this->datas[$name];
		}
		
		return $ret;
	}
	
	// add-start founder feihan
	/**
	 * 小数
	 *
	 */
	public function cDigit($from = 0, $to = 2147483647) {
		
		return $this->filter(function($value) use($from, $to) {
			
      $value = (isset($value)) ? mb_convert_kana($value, "n") : null;
			return Filter::cDigit($value, null, $from, $to);
			
		});
	}
	// add-end founder feihan
}

