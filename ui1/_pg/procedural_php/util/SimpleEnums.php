<?php 

/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */


// PHP言語で扱いやすいレベル＆無理をしないレベルを前提とした厳密では無いenum
class SimpleEnums {
	
	private static $datas = array();
	
	public static function add($name, $list) {
		if (isset(SimpleEnums::$datas[$name])) {
			trigger_error("[{$name}]はEnums内に既に作成されています。");;
		}
		SimpleEnums::$datas[$name] = $list;
	}

	public static function set($name, $list) {
		SimpleEnums::$datas[$name] = $list;
	}
	
	public static function addByFromTo($name, $from, $to) {
		$o = array();
		
		if ($to < $from) {
			for ($i = $from; $i >= $to; $i--) {
				$o[$i] = $i;
			}
			
		} else {
			for ($i = $from; $i <= $to; $i++) {
				$o[$i] = $i;
			}
				
		}
		
		SimpleEnums::add($name, $o);
	}
	
	// enum値を取得
	public static function get($name, $value) {
		$list = SimpleEnums::getAll($name);
		return arr($list, $value);
	}
		
	// enum配列を取得
	public static function getAll($name, $excludeNames = false) {
		$list = arr(SimpleEnums::$datas, $name);
		if ($list === null) {
			trigger_error("[{$name}]はEnums内に作成されていません。");
		}
		
		if ($excludeNames === false) return $list;

		if (!is_array($excludeNames)) {
			$excludeNames = array($excludeNames);
		}
		foreach ($excludeNames as $excludeName) { 
			unset($list[$excludeName]);
		}
		return $list;
	}

	// enumのキー配列を取得
	public static function getKeys($name, $excludeNames = false) {
		return array_keys(self::getAll($name, $excludeNames));
	}

	// 次の要素のキーを取得する
	public static function getNextKey($name, $key) {
		$list = SimpleEnums::getAll($name);
		$returnNext = false;
		foreach ($list as $k=>$v) {
			if ($k == $key) {
				$returnNext = true;
				continue;
			}
			if ($returnNext) {
				return $k;
			}
		}
		return null;
	}
	
	// 前の要素のキーを取得する
	public static function getPrevKey($name, $key) {
		$list = SimpleEnums::getAll($name);
		$returnPrev = false;
		foreach ($list as $k=>$v) {
			if ($k == $key) {
				return $returnPrev;
			}
			$returnPrev = $k;
		}
		return null;
	}

	// 最初の要素の値を取得する
	public static function getFirstValue($name) {
		$list = SimpleEnums::getAll($name);
		foreach ($list as $k=>$v) {
			return $k;
		}
		return null;
	}
	
	// 最終要素の値を取得する。checkboxのvalueに利用する場合などを想定。
	public static function getLastValue($name) {
		$list = SimpleEnums::getReserse($name);
		$k = null;
		foreach ($list as $k=>$v) {
			break;
		}
		return $k;
	}
	
	// 逆順で取得。
	public static function getReserse($name) {
		$list = SimpleEnums::getAll($name);
		return array_reverse($list, true);
	}
	
	// ラベルからkeyを取得
	public static function getKeyByValue($name, $value) {
		$enum = SimpleEnums::getAll($name);
		foreach ($enum as $k=>$v) {
			if ($v == $value) return $k;
		}
		return null;
	}
	
	// キーが存在しているかどうか。
	public static function existsKey($name, $key) {
		return isset(SimpleEnums::$datas[$name][$key]);
	}
	
	// 先頭から始まるキーが存在しているかどうか。
	public static function existsKeyPrefix($name, $prefix) {
		$enum = SimpleEnums::getAll($name);
		foreach ($enum as $k=>$v) {
			if (startsWith($k, $prefix)) return true;
		}
		return false;
	}
	
	
}


