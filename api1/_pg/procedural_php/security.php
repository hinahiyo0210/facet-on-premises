<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

/**
 * セキュリティ関連
 * 
 * 
 * 
 */

// -------------------------------------------------------- マジッククオートの無効化
// http://www.php.net/manual/ja/security.magicquotes.disabling.php
if (PHP_VERSION_ID < 70400) {
	if (get_magic_quotes_gpc()) {
	    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	    while (list($key, $val) = each($process)) {
	        foreach ($val as $k => $v) {
	            unset($process[$key][$k]);
	            if (is_array($v)) {
	                $process[$key][stripslashes($k)] = $v;
	                $process[] = &$process[$key][stripslashes($k)];
	            } else {
	                $process[$key][stripslashes($k)] = stripslashes($v);
	            }
	        }
	    }
	    unset($process);
	}
}

// -------------------------------------------------------- 文字コード及び0バイトチェック
function checkRequestArrayEncoding($arr) {

	foreach ($arr as $k=>$val) {
		
		checkRequestArrayEncodingOneValue($k);
		
		if (is_array($val)) {
			foreach ($val as $v) {
				checkRequestArrayEncodingOneValue($v);
			}
		} else {
			checkRequestArrayEncodingOneValue($val);
		}
		
		
	}
}
function checkRequestArrayEncodingOneValue($value) {
	
	if (!strComp($value, mb_convert_encoding($value, BASE_ENCODE, BASE_ENCODE))
		|| !mb_check_encoding($value, BASE_ENCODE)
		|| preg_match('/\0/', $value)
		) {
		warnLog("文字コード不正。[".$value."] ".getDetailAccessLog());
		response400();
	}
	
}

header("Server: valtec");
header("X-Powered-By: Secret");


checkRequestArrayEncoding($_REQUEST);
checkRequestArrayEncoding($_POST);
checkRequestArrayEncoding($_GET);
checkRequestArrayEncoding($_COOKIE);
checkRequestArrayEncoding($_SERVER);

// -------------------------------------------------------- アクセスURLの検証
// $filterPatterns = unserialize(REQUEST_PATH_FILTER_PATTERNS);
// $urlPath = parse_url(arr($_SERVER, "REQUEST_URI"), PHP_URL_PATH);
// foreach ($filterPatterns as $patten) {
	
// 	if (preg_match($patten["pattern"], $urlPath)) {
// 		warnLog("不正なURLアクセスです。url[".arr($_SERVER, "REQUEST_URI")."], pattern[".$patten["pattern"]."], 返却ヘッダ[".$patten["header"]."]を送信後、処理を終了します。");
// 		header($patten["header"]);
// 		die;
// 	}
	
// }


// ----------------------------------------------------------------------------------------- [以下関数]


