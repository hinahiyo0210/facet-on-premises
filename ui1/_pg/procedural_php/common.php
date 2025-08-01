<?php
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 *
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 *
 */

/**
 * 共通処理・共通関数
 *
 *
 *
 */

// エンコード・タイムゾーンを指定
mb_language('Japanese');
mb_internal_encoding(BASE_ENCODE);
mb_regex_encoding(BASE_ENCODE);
ini_set('mbstring.detect_order', BASE_ENCODE);

if (startsWith(phpversion(), "5.3")) {
	ini_set('mbstring.internal_encoding', BASE_ENCODE);
	ini_set('mbstring.http_input', BASE_ENCODE);
	ini_set('mbstring.http_output', BASE_ENCODE);
	ini_set("date.timezone", "Asia/Tokyo");
} else {
	date_default_timezone_set("Asia/Tokyo");
	
}

setlocale(LC_ALL, 'ja_JP.'.BASE_ENCODE);


// ---------------------------- 発生したエラーをハンドルする begin
set_exception_handler("exceptionHandler");
function exceptionHandler($e) {
	if (Globals::$errored) return;

	$msg = "エラーが発生しました。\ncode=".$e->getCode()."\nmessage=".$e->getMessage()."\nfile=".$e->getFile()."(".$e->getLine().")\n";
	if (class_exists("DB")) {
		if (DB::getExecSql()) {
			$msg .= "実行SQL[\n".DB::getExecSql()."\n]\n";
		}
	}
	$msg .= $e->getTraceAsString();
	errorLog($msg);
	errorLog(getDetailAccessLog());

	if (Globals::$errorDie) {
		Globals::$errored = true;
		proceduralPhpInternalServerError($e);
	} else {
		return;
	}

}

error_reporting(E_ALL);
set_error_handler("errorHandler", E_ALL);
function errorHandler($errno, $errstr = "", $errfile, $errline) {

	if (Globals::$errored) return;

	$errfile = realpath($errfile);

	if ($errno == 8192 && $errstr == "mysql_connect(): The mysql extension is deprecated and will be removed in the future: use mysqli or PDO instead") {
		return;
	}

	// ライブラリ系エラーのWARNINGとNOTICEは見逃す
	if (startsWith($errfile, DIR_LIB)) {
		if ($errno == E_WARNING || $errno == E_NOTICE || $errno == E_STRICT || $errno == E_DEPRECATED) {
			return;
		}
	}

	// smartyのUndefined indexを無視する
	if (Globals::$smarty != null) {
		if ($errno == E_NOTICE) {
			if (startsWith($errfile, Globals::$smarty->compile_dir) || startsWith($errfile, Globals::$smarty->cache_dir)) {
				return;
			}
		}
	}

	// Undefined indexとUndefined offsetを無視する
	if ($errno == E_NOTICE) {
		if (startsWith($errstr, "Undefined index:") || startsWith($errstr, "Undefined offset:")) {
			return;
		}
	}

	// 非推奨のエラーは一旦無視する(php8.3.10のため)
	if ($errno == E_DEPRECATED) {
		return;
	}

	try {
		throw new Exception;
	} catch (Exception $e) {
		$stack = $e->getTraceAsString();
		$stack = explode("\n", $stack);
		unset($stack[0]);
		$stack = join("\n", $stack);
	}

	$msg = "エラーが発生しました。\nerrno=".$errno."\nerrstr=".$errstr."\n#0 ".$errfile."(".$errline.")\n";

	if (class_exists("DB")) {
		if (DB::getExecSql()) {
			$msg .= "実行SQL[\n".DB::getExecSql()."\n]\n";
		}
	}
	$msg .= $stack;

	errorLog($msg);
	errorLog(getDetailAccessLog());

	if (Globals::$errorDie) {
		Globals::$errored = true;
		proceduralPhpInternalServerError($e);
	} else {
		return;

	}

}


// FatalErrorもキャッチして処理する
register_shutdown_function("errorHandleShutdownFunction");
function errorHandleShutdownFunction() {
	if (Globals::$errored) return;

	if ($error = error_get_last()){
		errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
	}
}
// ---------------------------- 発生したエラーをハンドルする end

// ------------------------------------------------------------------------------ [ここから関数]


function getStackTrace() {

	try {
		throw new Exception;
	} catch (Exception $e) {
		$stack = $e->getTraceAsString();
		$stack = explode("\n", $stack);
		unset($stack[0]);
		$stack = join("\n", $stack);
	}

	return $stack;
}

// ゼロ埋め
function zpad($val, $len) {
	return sprintf('%0'.$len.'d', $val);
}

/**
 * 使用するロガーを設定
 * @param Logger $logger log4phpロガーインスタンス
 */
function setLogger($logger) {
	Globals::$logger = Logger::getLogger($logger);
}

// ログ整形
$_log_req_uid = uniqid(); 
function formatLog($val) {
	global $_log_req_uid;
	if (is_array($val)) {
		$val = print_r($val, 1);
	}
	if (Globals::$controller) {
		$val = $_log_req_uid." ".Globals::$controller."#".Globals::$action." ".$val;
	}
	return $val;
}

/**
 * debugログを出力
 * @param string $val 内容
 */
function debugLog($val) {
	Globals::$logger->debug(formatLog($val));
	if (Globals::$bufferLogging) Globals::$bufferedLog .= "DEBUG ".date("Y/m/d H:i:s")." ".$val."\n";
}


/**
 * infoログを出力
 * @param string $val 内容
 */
function infoLog($val) {
	Globals::$logger->info(formatLog($val));
	if (Globals::$bufferLogging) Globals::$bufferedLog .= "INFO ".date("Y/m/d H:i:s")." ".$val."\n";
}

/**
 * warnログを出力
 * @param string $val 内容
 */
function warnLog($val) {
	global $___bufferedLog;
	Globals::$logger->warn(formatLog($val));
	if (Globals::$bufferLogging) Globals::$bufferedLog .= "WARN ".date("Y/m/d H:i:s")." ".$val."\n";
}

/**
 * errorログを出力
 * @param string $val 内容
 */
function errorLog($val) {
	global $___bufferedLog;
	if (Globals::$logger == null) {
		error_log($val);
	} else {
		Globals::$logger->error(formatLog($val));
	}
	if (Globals::$bufferLogging) Globals::$bufferedLog .= "ERROR ".date("Y/m/d H:i:s")." ".$val."\n";
}




/**
 * パラメータを与えて/includeディレクトリ内のファイルをincludeする。
 * @param string $fileName ファイル名
 * @param array $params パラメータ
 */
function includeByInclude($fileName, $params = array()) {

	$INC_PARAM = $params;
	include(DIR_INCLUDE."/".$fileName);
}

/**
 * パラメータを与えて/includeディレクトリ内のファイルをrequire_onceする。
 * @param string $fileName ファイル名
 * @param array $params パラメータ
 */
function requireOnceByInclude($fileName, $params = array()) {

	$INC_PARAM = $params;
	require_once(DIR_INCLUDE."/".$fileName);
}

/**
 * パラメータを与えて/commonディレクトリ内のファイルをrequire_onceする。
 * @param string $fileName ファイル名
 * @param array $params パラメータ
 */
function requireOnceByCommon($fileName, $params = array()) {

	$INC_PARAM = $params;
	require_once(DIR_COMMON."/".$fileName);
}


function requireOnceByProcedalPhp($fileName, $params = array()) {

	$INC_PARAM = $params;
	require_once(DIR_PROCEDURAL_PHP."/".$fileName);
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
 * Incorrect string value対策
 * @param array $arr データ配列
 */
function revConvertEncodings(&$arr) {
	foreach ($arr as $k=>$v) {
		$arr[$k] = revConvertEncoding($v);
	}
}
/**
 * Incorrect string value対策
 * @param string $str 文字列
 */
function revConvertEncoding($str) {

	mb_substitute_character("none");

	$str = mb_convert_encoding($str, "CP932", "UTF-8");
	$str = mb_convert_encoding($str, "UTF-8", "CP932");

	return $str;
}


/**
 * 文字コード変換
 * @param array $arr 対象
 * @param strin $from 変換前charset
 * @param strin $to 変換後charset
 */
function convertEncodingArray(&$arr, $from, $to) {

	foreach ($arr as $k=>$v) {
		$arr[$k] = mb_convert_encoding($v, $from, $to);
	}

}


/**
 * 文字列を結合
 *
 * @param string 可変長引数。
 */
function cat() {

	$ret = null;

	for ($i = 0; $i < func_num_args(); $i++) {
		$ret .= func_get_arg($i);
	}

	return $ret;
}



/**
 * 引数のうち、空でない方の値を取得
 *
 * @param string 可変長引数。
 */
function nval() {

	$val = null;

	for ($i = 0; $i < func_num_args(); $i++) {
		$val = func_get_arg($i);
		if (!empty($val)) {
			break;
		}
	}

	return $val;
}

/**
 * 引数の配列のうち、最大のcountを取得
 *
 */
function getMaxCount() {

	$ret = 0;

	for ($i = 0; $i < func_num_args(); $i++) {
		$arr = func_get_arg($i);
		if (empty($arr)) continue;
		if (count($arr) > $ret) {
			$ret = count($arr);
		}
	}

	return $ret;
}




/**
 * $valが空で無い場合に限り$suffixを付与して返す
 * @param $val
 * @param $suffix
 */
function suffix($val, $suffix) {

	if ($val === null || $val === "") {
		return $val;
	}

	return $val.$suffix;
}


/**
 * ラップタイム計測開始
 */
function beginRap() {
	list ($msec, $sec) = explode(' ', microtime());
	$microtime = (float)$msec + (float)$sec;
	return $microtime;
}

/**
 * ラップタイム計測終了
 * @param $start
 */
function endRap($start) {

	$end = beginRap();

	return round($end - $start, 3);
}


/**
 * 条件が満たされている場合に引数で指定された値を取得
 *
 * @param boolean $test
 * @param $true
 * @param $false
 */
function ifval($test, $true, $false = "") {
	if ($test || $test == "1") {
		return $true;
	}
	return $false;
}



/**
 * 1を①に変換
 *
 * @param int $num 変換対象
 * @return string 変換後
 */
function convertMaruNum($num) {

	if ($num == 1) return "①";
	if ($num == 2) return "②";
	if ($num == 3) return "③";
	if ($num == 4) return "④";
	if ($num == 5) return "⑤";
	if ($num == 6) return "⑥";
	if ($num == 7) return "⑦";
	if ($num == 8) return "⑧";
	if ($num == 9) return "⑨";
	if ($num == 10) return "⑩";

	return "";
}


/**
 * 変数デバッグ用。デバッグ内容出力後、dieする。
 *
 * @param $var
 * @param $indent
 */
function ddie($var, $indent = 0) {
	dd($var, $indent);
	die;
}

/**
 * 変数デバッグ用
 * @param $var
 * @param $indent
 */
function dd($var, $indent = 0) {

	$margin = $indent * 10;
	if ($indent == 0) {
		echo "<hr />";
		$color = "ffffcc";
	} else if ($indent == 1) {
		$color = "eeeebb";
	} else if ($indent == 2) {
		$color = "ddddaa";
	} else if ($indent == 3) {
		$color = "cccc99";
	} else {
		$color = "bbbb88";
	}

	echo '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
		<head>
			<meta name="robots" content="noindex, nofollow">
			<meta http-equiv="Content-Type" content="text/html; charset='.BASE_ENCODE.'" />
			<meta http-equiv="content-style-type" content="text/css" />
			<meta http-equiv="content-script-type" content="text/javascript" />
			<meta http-equiv="Pragma" content="no-cache">
			<meta http-equiv="Cache-Control" content="no-cache">
		</head>
		<body>'
	;


	echo "<div nowrap='nowrap' style='font-size:12px; font-family:monospace; background-color:#$color; margin-left:{$margin}px;'>";

	if ($var === null) {
		echo "[null]";
		echo "<br />";

	} else if ($var === "") {
		echo "[empty string]";
		echo "<br />";

	} else if ($var === "0") {
		echo "\"0\"";
		echo "<br />";

	} else if ($var === 0) {
		echo "0";
		echo "<br />";

	} else if (empty($var)) {
		echo "[empty]";
		echo "<br />";

	} else if (!is_array($var)) {
		echo $var;
		echo "<br />";
	} else {
		foreach ($var as $p=>$v) {

			if (is_array($v)) {
				echo $p."={";
				dd($v, $indent + 1);
				echo "}<br />";
			} else {
				if ($v."" === "0") {
					echo "$p=\"0\"";
					echo "<br />";

				} else if ($v === 0) {
					echo "$p=0";
					echo "<br />";

				} else if ($v === null) {
					echo "$p=[null]";
					echo "<br />";

				} else if ($v === "") {
					echo "$p=[empty sting]";
					echo "<br />";

				} else if (empty($v)) {
					echo "$p=[empty]";
					echo "<br />";

				} else {
					echo $p."=".$v;
					echo "<br />";
				}
			}
		}

	}

	echo "</div></body></html>";
}




/**
 * メール送信(テンプレートは使わない版)
 *
 */
function execSendMailPlain($to, $mail_from_name, $mail_from_addr, $mail_subject, $mail_content) {

	if (empty($to)) return true;

	$mail_content = chompFirst($mail_content);
	$mail_content = chompLast($mail_content);

	if (!is_array($to)) {
		$to = array($to);
	}

	$totalSuccess = true;

	foreach ($to as $toAddr) {
		infoLog("メール送信を開始します。mail_address[$toAddr], mail_subject[$mail_subject], mail_content[$mail_content]");


		$header = "From: ".mb_encode_mimeheader($mail_from_name)."<".$mail_from_addr.">";

		$before = mb_language();
		mb_language('uni');
		$success = $sendResult = mb_send_mail($toAddr, $mail_subject, $mail_content, $header);
		mb_language($before);


		if ($success) {
			infoLog("メール送信に成功しました。");
		} else {
			errorLog("メール送信に失敗しました。mail_address[$toAddr], mail_subject[$mail_subject], mail_content[$mail_content]");
		}

		if (!$success) {
			$totalSuccess = false;
		}

	}


	return $totalSuccess;
}




/**
 * メール送信
 *
 * @param unknown_type $template
 * @param unknown_type $to
 * @param unknown_type $param
 */
function execSendMail($template, $to, $param = array()) {

	if (empty($to)) return true;

	foreach ($param as $k=>$v) {
		if (is_array($v)) continue;

		if (Filter::digit($v) != null) {
			$param[$k."_num"] = formatNumber($v);
		}
	}

	if (exists($template, "/") || exists($template, "\\")) {
		include($template);
	} else {
		include(DIR_MAIL_TEMPLATE."/$template");
	}
	

	$mail_content = chompFirst($mail_content);
	$mail_content = chompLast($mail_content);


/*
	if (!empty($mobile)) {
		$mail_from_name = mb_convert_encoding($mail_from_name, $conf_enc, "utf-8");
		$mail_from_addr = mb_convert_encoding($mail_from_addr, $conf_enc, "utf-8");
		$mail_subject = mb_convert_encoding($mail_subject, $conf_enc, "utf-8");
		$mail_content = mb_convert_encoding($mail_content, $conf_enc, "utf-8");
	}
*/

	if (!is_array($to)) {
		$to = array($to);
	}

	$totalSuccess = true;

	foreach ($to as $toAddr) {
		infoLog("メール送信を開始します。mail_address[$toAddr], mail_subject[$mail_subject], mail_content[$mail_content]");


		$header = "From: ".mb_encode_mimeheader($mail_from_name)."<".$mail_from_addr.">";

		$before = mb_language();
		mb_language('uni');
		$success = $sendResult = mb_send_mail($toAddr, $mail_subject, $mail_content, $header);
		mb_language($before);


		if ($success) {
			infoLog("メール送信に成功しました。");
		} else {
			errorLog("メール送信に失敗しました。mail_address[$toAddr], mail_subject[$mail_subject], mail_content[$mail_content]");
		}

		if (!$success) {
			$totalSuccess = false;
		}

	}


	return $totalSuccess;
}



/**
 * 文字列を厳密に比較する
 * @param string $val1 対象1
 * @param string $val2 対象2
 */
function strComp($val1, $val2) {
	return ($val1."") === ($val2."");
}


/**
 * ページ内でシーケンス番号を取得する
 * return int 1から開始されるシーケンス番号
 */
function getSeqId() {

	static $seq;
	if (empty($seq)) {
		$seq = 0;
	}
	$seq++;

	return "id_".$seq;
}


/**
 * ページ内でシーケンス番号を出力する
 * @param boolean $sameBofore 新たに発番せずに前回と同じ結果を出力する場合にtrue
 * return int 1から開始されるシーケンス番号
 */
function seqId($sameBofore = false) {

	static $before;

	if ($sameBofore) {
		echo $before;
		return;
	}

	$id = getSeqId();
	$before = $id;
	echo $id;
}

/**
 * プレフィックスを除去
 * $val: "http://xxxx"
 * $prefix: "http:"
 * 上記の場合、"//xxxx"が返却される。
 * "http:"から開始されない$valだった場合は何もしない
 *
 * @param string $val 対象
 * @param string $prefix 除去するプレフィックス
 */
function excludePrefix($val, $prefix) {

	if (!startsWith($val, $prefix)) {
		return $val;
	}

	$idx = mb_strlen($prefix);

	return mb_substr($val, $idx, mb_strlen($val) - $idx);
}

/**
 * スフィックスを除去
 *
 * @param string $val 対象
 * @param string $suffix 除去するスフィックス
 */
function excludeSuffix($val, $suffix) {

	if (!endsWith($val, $suffix)) {
		return $val;
	}

	$len = mb_strlen($suffix);

	return mb_substr($val, 0, mb_strlen($val) - $len);
}


/**
 * 文字が長すぎる場合に省略を行う
 * @param string $val 対象
 * @param int $len 長さ
 */
function optional($val, $len) {

	if ($val == null) return $val;

	$beforeLen = mb_strlen($val);

	$val = mb_substr($val, 0, $len);

	if ($beforeLen != mb_strlen($val)) {
		$val .= "...";
	}

	return $val;
}

/**
 * 文字が長すぎる場合に行数で省略を行う
 * @param string $val 対象
 * @param int $len 長さ
 */
function optionalTextLine($val, $lineCount) {

	if ($val == null) return $val;

	$val = mb_str_replace($val, "\r\n", "\n");
	$val = mb_str_replace($val, "<p>", "");
	$val = mb_str_replace($val, "<P>", "");
	$val = mb_str_replace($val, "</p>", "<br />");
	$val = mb_str_replace($val, "</P>", "<br />");
	$val = mb_str_replace($val, "<br>", "<br />");
	$val = mb_str_replace($val, "<BR>", "<br />");
	$val = mb_str_replace($val, "<BR />", "<br />");
	$val = mb_str_replace($val, "<br />", "\n");

	$ret = "";
	$lines = splitLine($val);

	$cnt = 0;
	foreach ($lines as $line) {
		$cnt++;
		$ret .= $line."<br />\n";
		if ($cnt >= $lineCount) break;
	}

	if ($lineCount < count($lines)) {
		$ret .= "...";
	}

	return $ret;
}


/**
 * 文字列の最初が改行である場合に削除して返す
 *
 * @param stirng $val 対象
 */
function chompFirst($val) {

	while (startsWith($val, "\r") || startsWith($val, "\n")) {
		$val = mb_substr($val, 1, mb_strlen($val));
	}

	return $val;
}

/**
 * 文字列の最後が改行である場合に削除して返す
 *
 * @param string $val 対象
 */
function chompLast($val) {

	while (endsWith($val, "\r") || endsWith($val, "\n") || endsWith($val, "\t") || endsWith($val, " ")) {
		$val = mb_substr($val, 0, mb_strlen($val) - 1);
	}

	return $val;
}



/**
 * 指定文字列から開始される場合にtrue
 * @param string $val 対象
 * @param string $test 検証する文字列
 */
function startsWith($val, $test) {
	if ($val == "") return false;
	return mb_strpos($val, $test) === 0;
}

/**
 * 指定文字列で終了する場合にtrue
 * @param string $val 対象
 * @param string $test 検証する文字列
 */
function endsWith($val, $test) {
	if ($val == "") return false;
	return mb_substr($val, mb_strlen($val) - mb_strlen($test)) === $test;
}

/**
 * str_replaceのマルチバイト版
 * @param $haystack
 * @param $search
 * @param $replace
 * @param $offset
 * @param $encoding
 */
function mb_str_replace($haystack, $search,$replace, $offset=0,$encoding='utf8'){
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
 * 改行を除去
 * @param string $val 対象
 */
function excludeNewLine($val) {
	if ($val == null || is_array($val)) return $val;

	$val = mb_str_replace($val, "\r", "");
	$val = mb_str_replace($val, "\n", "");
	return $val;
}

/**
 * 指定文字列が存在している場合にtrueを返す
 * @param string $txt 対象
 * @param string $val 存在しているかどうかをチェックする文字列
 */
function exists($txt, $val) {
	if ($val == null) return false;
	if (is_array($txt)) {
		return inArray($txt, $val);
	} else {
		return mb_strpos($txt, $val) !== false;

	}
}

/**
 * 配列要素中に指定値がある場合にtrueを返す
 * @param unknown_type $arr
 * @param unknown_type $txt
 */
function inArray($arr, $txt) {
	if ($txt == null) return false;
	if ($arr == null) return false;

	foreach ($arr as $val) {
		if ($val == $txt) return true;
	}

	return false;
}



/**
 * 配列を結合した文字列を返す
 * @param array $arr 配列
 * @param string $delim 結合時の区切り文字
 */
function concatArray($arr, $delim = ", ", $arrIndex = false) {

	if ($arr == null) {
		return "";
	}

	$ret = null;

	foreach ($arr as $val) {
		if ($arrIndex === false) {
			$v = $val;
		} else {
			$v = $val[$arrIndex];
		}

		if ($ret == null) {
			$ret = $v;
		} else {
			$ret = $ret.$delim.$v;
		}
	}

	return $ret;
}

/**
 * 文字のアスキー値の集計を返す
 * @param string $str 対象
 */
function getAsciiSum($str) {

	$len = mb_strlen($str);

	$ret = 0;

	for ($i = 0; $i < $len; $i++) {
		$ret += ord(mb_substr($str, $i, 1));
	}

	return $ret;
}



/**
 * 配列キーのみで構成された配列を作成
 * @param array $arr 配列
 */
function getKeyArray($arr) {

	$ret = array();

	foreach ($arr as $k=>$v) {
		$ret[] = $k;
	}

	return $ret;
}


/**
 * 配列の先頭に要素を追加する
 * @param array $arr 配列
 * @param array $add 追加する要素
 */
function insertArray($arr, $add) {

	$ret = array();

	foreach ($add as $k=>$v) {
		$ret[$k] = $v;
	}
	foreach ($arr as $k=>$v) {
		$ret[$k] = $v;
	}

	return $ret;
}


/**
 * javascript用にエスケープ
 * @param string $value 対象
 */
function hjs($value) {
	return mb_str_replace(mb_str_replace(h($value), "\r\n", "\n"), "\n", "\\n");
}


/**
 * 第一引数と第二引数以降の全てが一致している場合にtrue
 * @param string $val 比較対象
 * @param string... 比較対象
 */
function andValue($val) {

	for ($i = 1; $i < func_num_args(); $i++) {
		if ($val != func_get_arg($i)) {
			return false;
		}
	}

	return true;
}

/**
 * 第一引数と第二引数以降のどれかが一致している場合にtrue
 * @param string $val 比較対象
 * @param string... 比較対象
 */
function orValue($val) {

	for ($i = 1; $i < func_num_args(); $i++) {
		if ($val == func_get_arg($i)) {
			return true;
		}
	}

	return false;
}

/**
 * 改行ごとに区切って配列で返す
 * @param string $val 対象文字列
 */
function splitLine($val) {

	if (empty($val)) return array();

	$val = mb_str_replace($val, "\r\n", "\n");
	$arr = mb_split("\n", $val);

	$ret = array();

	// 空白行を除去した状態で配列作成
	foreach ($arr as $v) {
		//$v = trim($v);
		if (Validator::isEmpty($v)) continue;

		$ret[] = $v;
	}

	return $ret;
}

/**
 * 指定文字ごとに区切って配列で返す
 * @param string $val 対象
 * @param string $delim 区切り文字
 */
function splitArray($val, $delim) {

	if (empty($val)) return array();

	$arr = mb_split($delim, $val);

	$ret = array();

	// 空白行を除去した状態で配列作成
	foreach ($arr as $v) {
		$v = trim($v);
		if (Validator::isEmpty($v)) continue;

		$ret[] = $v;
	}

	return $ret;
}


/**
 * 指定文字数で分割して配列で返す
 * @param string $str 対象文字列
 * @param int $len 区切る文字数
 */
function splitLength($str, $len) {
	$ret = array();

	$strLen = mb_strlen($str);

	for ($i = 0; $i < $strLen; ) {
		$ret[] = mb_substr($str, $i, $len);
		$i += $len;
	}

	return $ret;
}


/**
 * ランダムなパスワードを発行
 * @param string $len 発行する文字数
 */
function getRandomPassword($len = 10, $chars = false) {

	if ($chars === false) {
		$chars = array(
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
			'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
			'u', 'v', 'w', 'x', 'y', 'z',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
			'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
			'U', 'V', 'W', 'X', 'Y', 'Z'
		);
	}
	
	$pw = array();

	for ($i = 0; $i < $len; $i++)
	{
		shuffle($chars);
		$pw[] = $chars[mt_rand(0, count($chars) - 1)];
	}

	shuffle($pw);
	$pw = implode('', $pw);

	return $pw;
}

/**
 * 最初呼び出し判定を開始する
 */
function beginIsFirst() {
	Globals::$isFirstFlag = true;
}

/**
 * 最初呼び出し判定を行う。最初の呼び出しである場合はtrue
 * @param $output 最初の呼び出しである場合にechoする文字列
 */
function isFirst($output = null) {
	if (Globals::$isFirstFlag) {
		Globals::$isFirstFlag = false;
		echo $output;
		return true;
	} else {
		return false;
	}
}

/**
 * 非最初呼び出し判定を行う。非最初の呼び出しである場合はtrue
 * @param $output 非最初の呼び出しである場合にechoする文字列
 */
function isNotFirst($output = null) {
	if (Globals::$isFirstFlag) {
		Globals::$isFirstFlag = false;
		return false;
	} else {
		echo $output;
		return true;
	}
}


function debugAdd($list, $count = 2) {
	$ret = array();
	for ($i = 0; $i < $count; $i++) {
		foreach ($list as $item) {
			$ret[] = $item;
		}
	}

	return $ret;
}

// keyと値を入れ替える
function swapArray($arr) {

	if (empty($arr)) return array();

	$ret = array();
	foreach ($arr as $k=>$v) {
		$ret[$v] = $k;
	}

	return $ret;

}

// 仮想配列中の指定インデックスの一件を取得する。
function getByIndex($arr, $idx) {

	$cnt = 0;
	foreach ($arr as $k=>$v) {

		if ($cnt == $idx) {
			return $v;
		}
		$cnt++;
	}


	return null;
}


// 配列中の最後のキーを取得
function getLastKey(array $array) {
    end($array);
    return key($array);
}

// 配列内要素が全て空である場合にtrue
function isEmptyArray($array) {

	if (empty($array)) return true;

	if (!is_array($array)) {
		return empty($array);
	}

	foreach ($array as $key=>$value) {
		if (!empty($value)) return false;
	}

	return true;

}


