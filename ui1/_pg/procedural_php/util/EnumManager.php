<?php 

class EnumManager {
	
	private static $datas = array();
	
	public static function clear($name) {
		unset(self::$datas[$name]);
	}
	
	public static function clearAll() {
		self::$datas = array();
	}
	
	public static function get($name, $value, $getListFunction) {
		
		if (isset(self::$datas[$name])) {
			if ($value === false) {
				return self::$datas[$name];
			}
			if (is_array($value)) {
				$value = $value[$name];
			}
			
			return self::$datas[$name][$value];
		}
		
		self::$datas[$name] = $getListFunction();
		
		return self::get($name, $value, $getListFunction);
	}

	
}


