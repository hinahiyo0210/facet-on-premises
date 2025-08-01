<?php 

class ToolService {
	
	// 一度だけアクセス可能なファイルを出力する。
	public static function writeOnceFile($value, $extension) {
	
		$dirPath = DIR_DATA."/once/";
		if (!file_exists($dirPath)) mkdirs($dirPath); 
		
		// テンポラリに古いデータがあるのであれば自動的に削除する。
		deleteOldFiles($dirPath, strtotime("-1 hour"));
		
		$cnt = 0;
		do {
			if ($cnt++ >= 100) trigger_error("無限ループ防止。");
			$fileName = getRandomPassword(40).".".$extension; 
			$path = $dirPath."/".$fileName;
		} while (file_exists($path));
	
		$fp = fopen($path, "w");
		fputs($fp, $value);
		fclose($fp);
		
		return $fileName;
	}
	
	// 一度だけアクセス可能なファイルを取得し、削除する。
	public static function readOnceFile($fileName) {
		
		$dirPath = DIR_DATA."/once/";
		if (!file_exists($dirPath)) mkdirs($dirPath); 
		
		$fileName = getSafeFileName($fileName);
		
		$path = $dirPath."/".$fileName;
		
		if (!file_exists($path)) return false;
		$bin = file_get_contents($path);
		
 		unlink($path);
 		if (file_exists($path)) return false;
		
		return $bin;
	}
	
	
	
}