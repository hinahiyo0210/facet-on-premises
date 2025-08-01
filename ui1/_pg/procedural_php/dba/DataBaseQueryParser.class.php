<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

class DataBaseQueryParser {

	const LEN_LIKE_LR = 8; // mb_strlen("like_LR ")
	const LEN_LIKE_L  = 7; // mb_strlen("like_L ")
	const LEN_LIKE_R  = 7; // mb_strlen("like_R ")
	const LEN_IN	   = 3; // mb_strlen("in ")
	const LEN_FLAG    = 5; // mb_strlen("flag ")
	const LEN_FUNC	  = 5; // mb_strlen("func ")
	
	public function __construct() {
	}
	
	private function isNull($value) {
		
		if ($value === null || $value === "") {
			return true;
		}
		
		return false;
	}
	
	
	private function createIn($driver, $arr, $quot = true, $leftAny = false, $rightAny = false) {
		
		$ret = "";
		
		$first = true;
		foreach ($arr as $val) {
			
			if (!$first) {
				$ret .= ",";
			}
			$first = false;
			
			$val = $driver->escape($val);
			
			if ($leftAny || $rightAny) {
				$val = $driver->escapeLike($val);
			}
			if ($leftAny) {
				$val = $val."%";
			}
			if ($rightAny) {
				$val = "%".$val;
			}
			
			if ($quot) {
				$ret .= "'".$val."'";
			} else {
				$ret .= $val;
			}
		}
	
		if ($ret == "") {
			$ret = "(null)";
			
		} else {
			$ret = "(".$ret.")";
		}
		
		return $ret;
		
	}
	
	// SQLパラメータを解析する
	public function parse($driver, $sql, $parmas = array()) {
		
		$start = false;
		$line = 1;
		$buf = "";
		
		$charArray = preg_split("//u", $sql, -1, PREG_SPLIT_NO_EMPTY);
		$len = count($charArray);
		
// 		echo $sql."\n";
// 		echo $len."\n";
		
		
		for ($i = 0; $i < $len; $i++) {
			$c = $charArray[$i];
			
//			echo $sql."------------------ $len ".count($charArray)." \n";
			
			
			if ($c == "\n") {
				$line++;
				$start = false;
				continue;
			}
			
			if ($c == "{") {
				if ($start) throw new Exception("SQLパラメータの解析が出来ません。{}の対応が不正です。 [{$i}]文字目。[{$line}]行目");
				$start = true;
				$buf = "";
				continue;
			}
					
			if ($c == "}") {
				if (!$start) throw new Exception("SQLパラメータの解析が出来ません。{}の対応が不正です。 [{$i}]文字目。[{$line}]行目");
				$start = false;
				
				$bufLen = mb_strlen($buf);
					
				$prefix = "";
				$suffix = "";
				$escape = false;
				$escapeLike = false;
				
				if ($this->startsWith($buf, "flag ")) {
					$buf = mb_substr($buf, DataBaseQueryParser::LEN_FLAG);
					$value = $this->arr($parmas, $buf);
					if ($value !== "1" && $value !== 1) {
						$value = "0";
					}
					
				} else if ($this->startsWith($buf, "like_LR ")) {
					$prefix = "%";
					$suffix = "%";
					$buf = mb_substr($buf, DataBaseQueryParser::LEN_LIKE_LR);
					$escapeLike = true;
					$value = $this->arr($parmas, $buf);
					$value = $this->revConvertEncoding($value);
					
				} else if ($this->startsWith($buf, "like_L ")) {
					$prefix = "%";
					$suffix = "";
					$buf = mb_substr($buf, DataBaseQueryParser::LEN_LIKE_L);
					$escapeLike = true;
					$value = $this->arr($parmas, $buf);
					$value = $this->revConvertEncoding($value);
					
				} else if ($this->startsWith($buf, "like_R ")) {
					$prefix = "";
					$suffix = "%";
					$buf = mb_substr($buf, DataBaseQueryParser::LEN_LIKE_R);
					$escapeLike = true;
					$value = $this->arr($parmas, $buf);
					$value = $this->revConvertEncoding($value);
					
				} else if ($this->startsWith($buf, "in ")) {
					$buf = mb_substr($buf, DataBaseQueryParser::LEN_IN);
					$value = $this->arr($parmas, $buf);
					$value = $this->revConvertEncoding($value);
					
					if (!is_array($value)) $value = array($value);
					
					$prefix = "";
					$suffix = "";
					
					$value = $this->createIn($driver, $value);

				} else if ($this->startsWith($buf, "func ")) {
					$buf = mb_substr($buf, DataBaseQueryParser::LEN_FUNC);
					
					$value = $buf();
					
					if ($this->isNull($value)) {
						$value = "null";
					} else {
						$value = "'".$driver->escape($value)."'";
					}
					
					
				} else {
					$value = $this->arr($parmas, $buf);
					$value = $this->revConvertEncoding($value);
					$escape = true;
					
				}

				if ($escape) {
					if ($this->isNull($value)) {
						$value = "null";
					} else {
						$value = "'".$prefix.$driver->escape($value).$suffix."'";
					}
					
				} else if ($escapeLike) {
					if ($this->isNull($value)) {
						$value = "null";
					} else {
						$value = "'".$prefix.$driver->escapeLike($value).$suffix."'";
					}
										
				} else {
					$value = $prefix.$value.$suffix;
					
				}

				
				
				$sql = mb_substr($sql, 0, $i - $bufLen - 1).$value.mb_substr($sql, $i + 1);
				$charArray = preg_split("//u", $sql, -1, PREG_SPLIT_NO_EMPTY);
				
				$i = $i - ($bufLen + 1) + mb_strlen($value);
				
				$len = $len - (($bufLen + 2) - mb_strlen($value));
			
				$buf = "";
				
				continue;
			}
			
			if ($start) {
				$buf .= $c;
			}
			
		}

		
		return $sql;
		
	}

	/**
	 * Undefined indexを出さないように配列値を取得する
	 * 
	 * @param array $arr 配列
	 * @param string $idx 添え字
	 */
	function arr($arr, $idx) {
		if ($arr == null) {
			return null;
		}
		
		if (isset($arr[$idx])) {
			return $arr[$idx];
		}
		return null;	
	}
		
	
	/**
	 * 指定文字列から開始される場合にtrue
	 * @param string $val 対象
	 * @param string $test 検証する文字列
	 */
	private function startsWith($val, $test) {
		if ($val == "") return false;
		return mb_strpos($val, $test) === 0;
	}
	
	/**
	 * Incorrect string value対策
	 * @param string $str 文字列
	 */
	private function revConvertEncoding($str) {
		
// 		mb_substitute_character("none");
		
// 		$str = mb_convert_encoding($str, "CP932", "UTF-8");
// 		$str = mb_convert_encoding($str, "UTF-8", "CP932");
		
		return $str;
	}
	
	
	
}
