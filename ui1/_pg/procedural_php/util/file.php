<?php
/**
 * ファイル操作やcsvに関連する関数
 * 
 * 
 */


// 安全な拡張子を取得
function getFileExtension($path) {

	return mb_strtolower(Filter::len(getSafeFileName(pathinfo($path, PATHINFO_EXTENSION)), 10));
}


/**
 * ファイルをダウンロードする
 * @param string $filepath 対象のファイルパス
 */
function downloadFile($filepath) {
	header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
	header('Content-Type: application/octet-stream');
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: '.filesize($filepath));
	readfile($filepath);
}



/**
 * 一時ファイルを作成する
 * @param string $value 一時ファイルの内容
 */
function createTmpFile($value, $subDir = "", $extension = "") {

	if ($subDir != "") {
		$dir = DIR_TMP."/".$subDir;
		if (!file_exists($dir)) {
			mkdir($dir);
		}
	} else {
		$dir = DIR_TMP;
	}
	
	
	do {
		$path = $dir."/".getRandomPassword(30).$extension;
	} while (file_exists($path));

	$fp = fopen($path, "w");
	fputs($fp, $value);
	fclose($fp);
	
	return $path;
}

/**
 * 一時ファイルを作成する
 * @param string $contractor_id 一時ファイル名
 */
// 1つの契約につき同時出力処理は出来ない制御に使用
function createExclusivePath($contractor_id) {
  // フォルダがなければ作成する
  $exclusiveDir = DIR_TMP . "/exclusive";
  if (!file_exists($exclusiveDir)) mkdir($exclusiveDir);
  
  return $exclusiveDir . "/" . $contractor_id;

}

/**
 * ディレクトリ内のファイル名を取得
 * @param string $dirPath パス
 */
function getDirectoryFiles($dirPath) {
	$dir = dir($dirPath);

	$ret = array();
	
	while($file = $dir->read()) {
		
		$path = $dirPath."/".$file;

		if (is_dir($path)) {
			continue;
		}
		
		$ret[] = $file;
	}
	
	return $ret;
}


/**
 * ディレクトリ内のディレクトリを取得
 * @param string $dirPath パス
 */
function getSubDirectories($dirPath) {
	$dir = dir($dirPath);

	$ret = array();
	
	while($file = $dir->read()) {
		
		if ($file == "." || $file == "..") continue;
		
		$path = $dirPath."/".$file;

		if (is_file($path)) {
			continue;
		}
		
		$ret[] = $file;
	}
	
	return $ret;
}


/**
 * ディレクトリ内のファイルのサイズ合計を取得
 * @param string $dirPath パス
 */
function getDirectoryFileSize($dirPath) {
	$dir = dir($dirPath);

	$ret = 0;
	
	while($file = $dir->read()) {
		
		$path = $dirPath."/".$file;

		if (is_dir($path)) {
			continue;
		}
					
		$ret += filesize($path);
	}
	
	return $ret;
}

/**
 * ディレクトリ文字などを除去し、ファイル名として安全に使用できる文字列を作成
 * @param string $value ファイル名
 */
function getSafeFileName($value) {
	
	$value = preg_replace('/[\x00-\x1f\x7f]/', '', $value);
	
	$value = mb_str_replace($value, "\0", "");
	$value = mb_str_replace($value, "\\", "_");
	$value = mb_str_replace($value, "/", "_");
	$value = mb_str_replace($value, ":", "_");
	$value = mb_str_replace($value, "*", "_");
	$value = mb_str_replace($value, "?", "_");
	$value = mb_str_replace($value, "\"", "_");
	$value = mb_str_replace($value, "<", "_");
	$value = mb_str_replace($value, ">", "_");
	$value = mb_str_replace($value, "|", "_");
	$value = mb_str_replace($value, "~", "_");
	$value = mb_str_replace($value, "^", "_");
	$value = mb_str_replace($value, "'", "_");
	$value = mb_str_replace($value, '"', "_");
	$value = mb_str_replace($value, " ", "_");
	$value = mb_str_replace($value, "　", "_");
	
	return $value;	
}

/**
 * 指定日未満の更新日時のファイルを全て削除
 * @param string $dirPath ディレクトリパス
 * @param int $comp 時刻（秒）
 */
function deleteOldFiles($dirPath, $comp) {
	
	$dir = dir($dirPath);
	$ret = 0;
	
	while($file = $dir->read()) {
		
		$path = $dirPath."/".$file;

		if (is_dir($path)) {
			continue;
		}

		$time = filemtime($path);
		
		if ($comp > $time) {
			unlink($path);
			$ret++;
		}				
	}

	return $ret;
}


/**
 * ディレクトリ内のファイルを全て削除する
 * @param string $dirPath ディレクトリパス
 */
function deleteDirFiles($dirPath) {
	
	$dir = dir($dirPath);
	$ret = 0;
	
	while($file = $dir->read()) {
		
		$path = $dirPath."/".$file;

		if (is_dir($path)) {
			continue;
		}

		unlink($path);
		$ret++;
	}

	return $ret;
}

// 再帰的にディレクトリをまるごと削除。
function deleteDirectory($dirPath) {

	$cnt = 0;
	
	$handle = opendir($dirPath);
	if (!$handle) {
		return ;
	}
	
	while (false !== ($item = readdir($handle))) {
		
		if ($item === "." || $item === "..") {
		    continue;
		}
		
		$path = $dirPath.DIRECTORY_SEPARATOR.$item;
		
		if (is_dir($path)) {
		    // 再帰的に削除
		    $cnt = $cnt + deleteDirectory($path);
		} else {
		    // ファイルを削除
		    unlink($path);
		}
		
	}
	closedir($handle);
		
	// ディレクトリを削除
	if (!rmdir($dirPath)) {
		return ;
	}
	
}



/**
 * ファイルの指定行のみを読み込む
 * @param string $path ファイルパス
 * @param int $lineNo 行番号
 */
function readFileLine($path, $lineNo) {
	
	$fp = fopen($path, 'r');
	$ret = "";
	
	$readed = 0;
	while ($line = fgets($fp)) {
		$readed++;
		
		if($readed == $lineNo){
			$ret = $line;
			break;
		}
		
	}
	fclose($fp);
	
	return $ret;
}




/**
 * CSVをダウンロードする際のレスポンスヘッダを設定する
 * 
 * @param string $name ファイル名
 */
function setCsvHeader($name) {
	$name = mb_convert_encoding($name, "cp932", mb_internal_encoding());
	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename={$name}");
}


/**
 * csv項目を出力する
 * @param string $val 内容
 * @param boolean $nr 行の末尾である場合はtrue
 */
function echoCsv($val, $nr = false) {
	
	if ($val === null || $val === "") {
		echo '""';
	} else {
		if (endsWith($val, "\\")) {
			$val = $val." ";
		}
		$val = mb_str_replace($val, '"', '""');
		$val = mb_convert_encoding($val, "CP932", "UTF-8");
		echo '"'.$val.'"';
	}
	if ($nr) {
		echo "\n";
	} else {
		echo ",";
	}
}


/**
 * fgetcsvの修正版
 * @param $handle
 * @param $length
 * @param $d
 * @param $e
 */
function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"') {
    $d = preg_quote($d);
    $e = preg_quote($e);
    $_line = "";
    $eof = false;
    
    while ($eof != true) {
        $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
        $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
        if ($itemcnt % 2 == 0) $eof = true;
    }
    $_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
    $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
    preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
    $_csv_data = $_csv_matches[1];
    for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
        $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
        $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
    }
    return empty($_line) ? false : $_csv_data;
}


/**
 * ファイルに文字列を出力
 * @param string $path パス
 * @param stirng $value 出力内容
 */
function writeFile($path, $value) {
	
	$fp = fopen($path, "w");
	fwrite($fp, $value);
	fclose($fp);
		
}

// 再帰的にディレクトリを作成
function mkdirs($dirname, $mode = 0777) {
    if (file_exists($dirname)) return false;
    // 階層指定かつ親が存在しなければ再帰
    if (mb_strpos($dirname, '/') && !file_exists(dirname($dirname))) {
        // 親でエラーになったら自分の処理はスキップ
        if (mkdirs(dirname($dirname), $mode) === false) return false;
    }
    Globals::$errorDie = false;
    $ret = false;
    $ret = mkdir($dirname, $mode, true);
    Globals::$errorDie = true;
    return $ret;
}


/**
 * 一時ディレクトリを作成する
 * @param string $value 一時ファイルの内容
 */
function createTmpDir($subDir = "") {

	if ($subDir != "") {
		$dir = DIR_TMP."/".$subDir;
		if (!file_exists($dir)) {
			mkdir($dir);
		}
	} else {
		$dir = DIR_TMP;
	}
	
	do {
		$path = $dir."/".getRandomPassword(30)."/";
	} while (file_exists($path));

	mkdir($path);
	
	return $path;
}
