<?php 

// 共通で使用する可能性のあるEnumをメソッドとして定義。
// 極力、メソッド名はDBの項目名と一致させるか、同じ単語を利用する。
class Enums {
	
	public static function get($name, $val = false) {
		return self::$name($val);
	}
	public static function getAll($name) {
		return self::$name(false);
	}
	
	// 識別レベル
	public static function recogLiveness($val = false) {
		
		$getList = function() {

			return [
				"1"=>"写真/ビデオの偽装を判別しない",
				"2"=>"写真/ビデオの偽装を部分的に判別する",
				"3"=>"写真/ビデオの偽装を正確に判別する",
			];
			
		};
		
		return EnumManager::get(__FUNCTION__, $val, $getList);
	}

	// マスク検出の設定
	public static function maskDetectMode($val = false) {
		
		$getList = function() {

			return [
				"0"=>"マスク有無では入場判定は行わない",
				"1"=>"マスクを着用していない人物の入場を拒否する",
				"2"=>"マスクを着用済みの人物の入場を拒否する",
			];
			
		};
		
		return EnumManager::get(__FUNCTION__, $val, $getList);
		
	}

	// 体温検出の設定
	public static function tempDetectMode($val = false) {
		
		$getList = function() {

			return [
				"0"=>"温度では入場判定は行わない",
				"1"=>"温度異常が検知された人物の入場を拒否する",
			];
			
		};
		
		return EnumManager::get(__FUNCTION__, $val, $getList);
		
	}
	
	// マスク通知背景色
	public static function maskShowBackgroundColor($val = false) {
		
		$getList = function() {

			return [
				"Blue"=>"青",
				"Green"=>"緑",
				"Red"=>"赤",
			];
			
		};
		
		return EnumManager::get(__FUNCTION__, $val, $getList);
	}
	
	// メッセージ背景色
	public static function tipsBackgroundColor($val = false) {
		
		$getList = function() {

			return [
				"Blue"=>"青",	
				"Red"=>"赤",
				"Green"=>"緑"	
			];
			
		};
		
		return EnumManager::get(__FUNCTION__, $val, $getList);
	}	
	// 一ページあたりの表示件数
	public static function pagerLimit($val = false) {
		
		$getList = function() {

			return [
				"20"=>20,
				"40"=>40,
				"60"=>60,
				"80"=>80,
				"100"=>100,
				"200"=>200,
			];
			
		};
		
		return EnumManager::get(__FUNCTION__, $val, $getList);
	}
	

	// 一ページあたりの表示件数(リアルタイムモニタ)
	public static function monitorPagerLimit($val = false) {
		
		$getList = function() {

			return [
				"20"=>20,
				"40"=>40,
			];
			
		};
		
		return EnumManager::get(__FUNCTION__, $val, $getList);
	}
	
	
	
	
	
}
