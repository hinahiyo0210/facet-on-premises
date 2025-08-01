<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

/**
 * WEB表示に関連する関数
 * 
 * 
 * 
 */


// user-agentからアクセス端末がスマートフォンかどうかを判定する
function isSp() {
	
	$useragents = array(
		'iPhone', // Apple iPhone
		'iPod', // Apple iPod touch
		'Android', // 1.5+ Android
		'dream', // Pre 1.5 Android
		'CUPCAKE', // 1.5+ Android
		'blackberry9500', // Storm
		'blackberry9530', // Storm
		'blackberry9520', // Storm v2
		'blackberry9550', // Storm v2
		'blackberry9800', // Torch
		'webOS', // Palm Pre Experimental
		'incognito', // Other iPhone browser
		'webmate' // Other iPhone browser
	);
	
	$pattern = '/'.implode('|', $useragents).'/i';
	
	return preg_match($pattern, arr($_SERVER, 'HTTP_USER_AGENT'));
}




function setJsonHeader() {
	
	header('Content-Type: text/javascript; charset='.BASE_ENCODE);
}


function ifChecked($val) {

	if ($val == "1") {
	 	echo "checked=\"checked\"";
	}

}

function echoIfLink($url, $val, $tagetBlank = false) {
	
	if (!empty($url)) {
		echo "<a href=\"".h($url)."\"".($tagetBlank ? " target=\"_blank\"" : "").">".h($val)."</a>";
		return;
	}
	
	echo h($val);
}

function echoHiddens($form, $exclude = null) {
	
	
	echo "\n<!-- ======================================== form ======================================== -->\n";
	foreach ($form as $name=>$val) {
		
		if (strComp($name, $exclude)) {
			continue;
		}
		
		if (is_array($val)) {
			foreach ($val as $v) {
				echo '<input type="hidden" name="'.h($name."[]").'" value="'.h($v).'" />'."\n";
			}
		} else {
			echo '<input type="hidden" name="'.h($name).'" value="'.h($val).'" />'."\n";
			
		}
		
	}
	echo "<!-- ======================================== /form ======================================== -->\n";
	
	
}

function getListUrl($form, $first = true, $exclude = null) {

	$url = "";
	
	foreach ($form as $name=>$val) {
		
		if (strComp($name, $exclude)) {
			continue;
		}
		
		if (empty($val)) {
			continue;
		}
		
		if (is_array($val)) {
			foreach ($val as $v) {
				if ($first) {
					$url .= "?";
					$first = false;
				} else {
					$url .= "&";
				}
				$url .= urlencode($name."[]")."=".urlencode($v);
			}
		} else {
			if ($first) {
				$url .= "?";
				$first = false;
			} else {
				$url .= "&";
			}
			$url .= urlencode($name)."=".urlencode($val);
			
		}
		
	}
	
	return $url;
}
	
/**
 * 数値を整数3桁カンマでフォーマット
 * 
 * @param string $val 対象
 * @return string フォーマット済み数値文字列
 */
function formatNumber($val, $n = null) {
	
	if ($val !== 0 && $val !== "0" && empty($val)) {
		return $val;
	}
	
	if ($n) {
		return number_format($val, $n);
	}
	return number_format($val);
	
}



/**
 * 指定数で区切られた配列を作成して返す。
 */
function getSeparateArray($arr, $count, $fillBlank = true) {
	
	if ($arr == null || !is_array($arr)) {
		return;
	}
	
	$ret = array();
	$idx = 0;
	foreach ($arr as $k=>$v) {
		
		$ret[$idx][$k] = $v;
		
		if (count($ret[$idx]) >= $count) {
			$idx++;
		}
		
	}
	
	if ($fillBlank && isset($ret[$idx])) {
		while (count($ret[$idx]) < $count) {
			$ret[$idx][] = null;
		}
	}
	
	return $ret;
}


/**
 * http status 400をレスポンスする
 * @param string $msg 画面に表示するメッセージ
 */
function response400($msg = "不正アクセス") {
	
	http_response_code(400);
	
	echo '<html><head><meta name="robots" content="noindex, nofollow"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>';
	echo h($msg)."<br />";
	for ($i = 0; $i < 2000; $i++) echo "　";	// htmlのサイズが小さすぎるとIEで画面表示されないので、全角スペース出力
	echo "</body></html>";
	
	try {
		throw new Exception;
	} catch (Exception $e) {
		$stack = $e->getTraceAsString();
		$stack = explode("\n", $stack);
//		unset($stack[0]);
// 		$stack = join("\n", $stack);
		$stack = $stack[0];
	}
	
	warnLog("不正アクセスを返却。".$stack);
	
	
	die;
}

/**
 * タグと特殊文字を除去する
 * @param string $val 対象
 * return string 処理済みの文字列
 */
function htag($val) {
	$val = mb_str_replace($val, "<div>", "\r\n<div>");
	$val = mb_str_replace($val, "<p>", "");
	$val = mb_str_replace($val, "<P>", "");
	$val = mb_str_replace($val, "</p>", "\r\n");
	$val = mb_str_replace($val, "</P>", "\r\n");
	$val = mb_str_replace($val, "&nbsp;", " ");
	return h(html_entity_decode(strip_tags($val)));	
}

/**
 * 改行をbrに変換後タグと特殊文字を除去
 * @param $val
 */
function hbrtag($val) {
	return html_entity_decode(hbr(strip_tags($val, "<br>")));
}


/**
 * 引数の値が空の場合に404をレスポンスする
 * 
 * @param string 可変長引数。
 * 
 */
function ifEmpty404() {
	
	for ($i = 0; $i < func_num_args(); $i++) {
		$val = func_get_arg($i);
		if (empty($val)) {
			response404();
		}
	}
	
}


/**
 * tableレイアウトを出力する
 * 
 * @param $arr
 * @param $delimCount
 * @param $tableAttr
 * @param $tdAttr
 */
function echoTable($arr, $delimCount = 5, $tableAttr = "", $tdAttr = "") {
	
	if ($arr == null || count($arr) == 0) return;
	
	echo '<table '.$tableAttr.'>';
	echo '<tr>';
	
	$cnt = 0;
	
	for ($i = 0; $i < count($arr); $i++) {
		
		$item = $arr[$i];
			
		if ($cnt == $delimCount) {
			echo '</tr>';
			echo '<tr>';
		}
		
		echo '<td '.$tdAttr.'>';
		echo $item;
		echo '</td>';
		
		$cnt++;
	}

	while ($i % $delimCount != 0) {
		echo '<td '.$tdAttr.'></td>';
		$i++;
	}
	
	echo '</tr>';
	echo '</table>';
	
}


/**
 * rowspanを自動結合したtableを出力する。
 * th部分のtrは出力しない。
 * 
 * @param unknown $arr
 * @param unknown $cols
 * @param unknown $unionCols
 */
function echoRowUnionTable($list, $cols, $unionCols, $noEscapeCols = array()) {
	
	$len = count($list);
	$unionColsLen = count($unionCols);
	
	
	$rowSpans = array(); // 名称ごと行番号ごとのrowspan数
	for ($i = 0; $i < $len; $i++) {
		if ($i == 0) continue;	// 一行目は無視。
		
		$row = $list[$i];	// この行
		$beforeRow = $list[$i - 1];	// ひとつ上の行
		
		for ($j = 0; $j < $unionColsLen; $j++) {
			$unionCol = $unionCols[$j];
			
			// 先頭のカラムで無い場合、前位置にあるカラムも前行との一致性を検証する。
			$same = true;
			for ($k = 0; $k < $j + 1; $k++) {
				if ($row[$unionCols[$k]] != $beforeRow[$unionCols[$k]]) {
					$same = false;
					break;
				}
			}
			
			
			if ($same) {
				
				if (isset($rowSpans[$unionCol])) {
					$unionRowNo = getLastKey($rowSpans[$unionCol]);
					$rowSpans[$unionCol][$unionRowNo]++;
				} else {
					$rowSpans[$unionCol][$i - 1] = 2;
				}
				
			} else {
				$rowSpans[$unionCol][$i] = 1;
			}
			
		}
		
	}
	
	
	$rowSpanning = array();
			
	for ($i = 0; $i < $len; $i++) {
		$row = $list[$i];
				
		echo '<tr>'."\n";
		
		foreach ($cols as $col) {
			
			$attr = "";
			$rowspan = 1;
			
			if (isset($rowSpans[$col][$i])) {
				$rowspan = $rowSpans[$col][$i];
				if ($rowspan != 1) {
					$attr = ' rowspan="'.$rowspan.'"';
					$rowSpanning[$col] = $i + $rowspan;
				}
			}
			
			
			if ($rowspan == 1 && isset($rowSpanning[$col]) && $rowSpanning[$col] > $i) {
				continue;
			}
			
			$val = $row[$col];
			if (!in_array($col, $noEscapeCols)) {
				$val = h($val);
			}

			echo '<td'.$attr.'>'.$val.'</td>'."\n";
			
		}
		
		echo '</tr>'."\n";
	}
	
}


/**
 * http status 404で応答する
 */
function response404() {
	header("HTTP/1.1 404 Not Found");
	die();
}


/**
 * リクエストされたディレクトリを返す
 * 
 * @param string $encode urlエンコードする場合はtrue
 */
function getRequestDir($encode = false) {
	
	$ret = $_SERVER['REQUEST_URI'];
	
	if ($encode) {
		return rawurlencode($ret);
	} else {
		return $ret;
	}
	
}

/**
 * リクエストされたURLを返す
 * 
 * @param string $encode urlエンコードする場合はtrue
 */
function getRequestUrl($encode = false, $ssl = false) {
	
	if ($ssl) {
		$ret = "https://".arr($_SERVER, "SERVER_NAME").arr($_SERVER, 'REQUEST_URI');
	} else 	if (arr($_SERVER, 'HTTPS')) {
		$ret = "https://".arr($_SERVER, "SERVER_NAME").arr($_SERVER, 'REQUEST_URI');
	} else {
		$ret = "http://".arr($_SERVER, "SERVER_NAME").arr($_SERVER, 'REQUEST_URI');
	}
	
	if ($encode) {
		return urlencode($ret);
	} else {
		return $ret;
	}
	
}


/**
 * 改行を除去
 * @param string $val 対象
 * @param string $replaceVal 改行を置き換える
 */
function deleteNr($val, $replaceVal = "") {
	
	if ($val == null) return $val;
	
	$val = mb_str_replace($val, "\n", $replaceVal);
	$val = mb_str_replace($val, "\r", $replaceVal);
	
	return $val;
}

/**
 * 改行・タブを除去
 * 
 * @param string $val 対象
 * @param string $replaceVal 置き換え文字列
 */
function deleteTab($val, $replaceVal = "") {
	
	if ($val == null) return $val;
	
	$val = mb_str_replace($val, "\t", $replaceVal);
	
	return $val;
}


/**
 * 文字列のhtmlエスケープ(単一行用) 
 * @param string $val 対象
 * @param string $prefix 対象が空でない場合にのみ付与するプレフィックス
 * @param string $suffix 対象が空でない場合にのみ付与するスフィックス
 */
function h($val, $prefix = "", $suffix = "") {
	if ($val == null || is_array($val)) return $val;
		
	$val = htmlspecialchars($val, ENT_QUOTES, BASE_ENCODE);
	
	return $prefix.$val.$suffix;
}


/**
 * 文字列のhtmlエスケープ(複数行用)
 * \nを<br />に変換する
 * @param string $val 対象
 * @param string $prefix 対象が空でない場合にのみ付与するプレフィックス
 * @param string $suffix 対象が空でない場合にのみ付与するスフィックス
 */
function hbr($val, $prefix = "", $suffix = "") {
	if ($val == null) return $val;
	
	$val = h($val);
	
	if (exists($val, "\n")) {
		$val = mb_str_replace($val, "\r\n", "\n");
		$val = mb_str_replace($val, "\n", "<br>");
		$val = mb_str_replace($val, "\n", "<br/>");
		$val = mb_str_replace($val, "\n", "<br />");
		$val = mb_str_replace($val, "\n", "<BR>");
		$val = mb_str_replace($val, "\n", "<BR/>");
		$val = mb_str_replace($val, "\n", "<BR />");
	}
	
	return $prefix.$val.$suffix;
}

function h_appendBr($val) {
	if ($val == null) return $val;
	
	$val = h($val);
	
	$val = mb_str_replace($val, "\r\n", "\n");
	$val = mb_str_replace($val, "\n", "\n<br />");
	
	return $val;
}

/**
 * リクエストの値を取得
 * @param string $name パラメータ名
 * @param string $default nameで得られる値が空である場合に返すデフォルト値
 */
function req($name, $default = "") {
	
	if (isset($_REQUEST[$name])) {
		return $_REQUEST[$name];
	}
	return $default;
}

/**
 * POSTの値を取得
 * @param string $name パラメータ名
 * @param string $default nameで得られる値が空である場合に返すデフォルト値
 */
function post($name, $default = "") {
	
	if (isset($_POST[$name])) {
		return $_POST[$name];
	}
	return $default;
}



/**
 * リクエストの値を必ず配列で取得
 * @param string $name パラメータ名
 */
function reqArr($name) {
	
	if (isset($_REQUEST[$name])) {
		
		if (is_array($_REQUEST[$name])) {
			return $_REQUEST[$name];
		} else {
			return array($_REQUEST[$name]);
			
		}
		
	}
	
	return array();
}



/**
 * リダイレクトを行う
 * @param string $url URL
 * @param string $addParam URLに追加するgetパラメータ
 */
function sendRedirect($url, $addParam = "") {
	
	$url = addUrlParam($url, $addParam);
	
	header("Location: {$url}");
	die;
}


function sendCompleteRedirect($url, $msg) {
	$key = Session::setMessage($msg);
	sendLocalRedirect($url, "msg=$key");
}

/**
 * リダイレクトを行う(同一サーバ限定に制限)
 * @param string $url URL
 * @param string $addParam URLに追加するgetパラメータ
 */
function sendLocalRedirect($url, $addParam = "") {
	
	if (exists($url, "://") || exists(urldecode($url), "://")) {
		response400("不正なパラメータです。");
	}
	
	sendRedirect($url, $addParam);
}


/**
 * 指定行数に達した文字列である場合に、それ以降を切り落とした文字列を返す。
 * 
 * @param stirng $value 対象
 * @param int $limit 長さ
 */
function limitBr($value, $limit) {
	
	if ($value == null) return $value;
	
	$value = mb_str_replace($value, "\r", "");
	$value = mb_str_replace($value, "\n", "");
	$value = mb_str_replace($value, "<br />", "<br />\n");
	$value = mb_str_replace($value, "<br/>", "<br />\n");
	$value = mb_str_replace($value, "<br>", "<br />\n");
	$value = mb_str_replace($value, "<br >", "<br />\n");
	$value = mb_str_replace($value, "<BR/>", "<br />\n");
	$value = mb_str_replace($value, "<BR>", "<br />\n");
	$value = mb_str_replace($value, "<BR >", "<br />\n");
	$value = mb_str_replace($value, "</p>", "</p>\n");
	$value = mb_str_replace($value, "</P>", "</p>\n");
	
	$value = strip_tags($value);
	
	$arr = mb_split("\n", $value);
		
	$ret = "";
	
	$added = 0;
	
	for ($i = 0; $i < $limit; $i++) {
		
		if (!isset($arr[$i])) {
			break;
		}

		if ($i != 0) {
			$ret .= "<br />";
		}
		$ret .= $arr[$i];
		$added++;
		
	}
	
	if ($added != count($arr)) {
		$ret .= "&nbsp;&nbsp;......";
	}
	
	return $ret;
}


/**
 * 配列の内容をjs用に出力
 * @param array $arr 配列
 * @param string $varName js変数名
 */
function echoJsListArray($arr, $varName) {
	
	echo "var {$varName} = [];\n";
	
	if ($arr == null) return;
	
	foreach ($arr as $row) {
		echo "var _tmp = new Object();\n";
		
		foreach ($row as $k=>$v) {
			if (is_numeric($k)) {
				continue;
			}
			$k = hjs($k);
			$v = hjs($v);
			echo "_tmp.{$k} = \"{$v}\";\n";
		}
		
		echo "{$varName}.push(_tmp);\n";
	}
	
}

/**
 * 配列の内容をjs用に出力
 * 配列のキーに$arrayKeyを使用する
 * @param stirng $arr 配列
 * @param string $varName js変数名
 * @param string $arrayKey 配列の添え字
 */
function echoJsIndexArray($arr, $varName, $arrayKey) {
	
	if ($arr == null) return;
	
	echo "var {$varName} = [];\n";
	foreach ($arr as $row) {
		echo "var _tmp = new Object();\n";
		
		foreach ($row as $k=>$v) {
			if (is_numeric($k)) {
				continue;
			}
			$k = hjs($k);
			$v = hjs($v);
			echo "_tmp.{$k} = \"{$v}\";\n";
		}
		
		$keyValue = $row[$arrayKey];
		
		echo "{$varName}[{$keyValue}] = _tmp;\n";
	}
	
}

/**
 * 配列の内容をjs用に出力：option用
 * @param array $options 選択肢配列
 * @param stirng $varName js変数名
 */
function echoJsOptionArray($options, $varName) {
	
	echo "var {$varName} = [];\n";
	foreach ($options as $k=>$v) {
		$k = hjs($k);
		$v = hjs($v);
		echo "{$varName}[\"{$k}\"] = \"{$v}\";\n";
	}
		
}


/**
 * 配列の内容をjs用に出力
 * @param array $arr 配列
 * @param string $varName js変数名
 */
function echoJsArray($arr, $varName) {
	
	if ($arr == null) return;
	
	echo "var {$varName} = [];\n";
	foreach ($arr as $k=>$v) {
		$k = hjs($k);
		$v = hjs($v);
		
		echo "{$varName}[\"$k\"] = \"$v\";\n";
	}
	
}

/**
 * URLパラメータを追加
 * @param string $url URL
 * @param string $add 追加するgetパラメータ
 */
function addUrlParam($url, $add) {
	if ($add == null) {
		return $url;
	}
	
	if (exists($url, "?")) {
		return $url."&".$add;
	}
	return $url."?".$add;
}

/**
 * 指定されたパラメータ値で現在のリクエストURIのパラメータ部分を置き換えて取得
 */
function replaceUrl($replace) {
	
	$params = array();
	parse_str($_SERVER["QUERY_STRING"], $params);
	
	if (is_array($replace)) {
		foreach ($replace as $k=>$v) {
			$params[$k] = $v;
		}
		
	} else {
		$repArr = explode("=", $replace);
		$params[$repArr[0]] = $repArr[1];
			
	}
	
	
	return "?".http_build_query($params);
}


/**
 * 文字列を結合。smarty用。
 */
function concat() {

	$val = "";
	
	for ($i = 0; $i < func_num_args(); $i++) {
		$val .= func_get_arg($i);
	}

	return $val;
}


/**
 * 既に出現済みのIDであるかどうかを判定。smarty用。
 */
function isVirgin($id) {
	
	static $ids;
	if (empty($ids)) $ids = array();
	
	
	if (isset($ids[$id])) {
		return false;		
	}
	
	$ids[$id] = 1;
	
	return true;
}


// ファイルライブラリ用。拡張子に応じたサムネイルURLを取得
function getFileLibThumb($url) {

	if ($url == null) {
		return FILELIB_URL."/noimg.jpg";
	}
	
	
	$info = pathinfo($url);
	$ext = mb_strtolower(arr($info, 'extension'));
	
	if (isImageExt($ext)) {
		return FILELIB_URL."/".$url;
	}
	
	if (file_exists(FILELIB_DIR."/".$ext.".png")) {
		return FILELIB_URL."/".$ext.".png";
	}
	
	return FILELIB_URL."/etc.png";
}



// ファイルライブラリ用。src用
function getFileLibImg($url) {
	
	if ($url == null) {
		return "";
		//return FILELIB_URL."/noimg.jpg";
	}
	
	return FILELIB_URL."/".$url;
}



/**
 * リクエストされたトップディレクトリ名を取得
 */
function getRequestTopDir($base = null, $sslBase = null) {
	
	if ($base == null) $base = URL_FRONT;
	if ($sslBase == null) $sslBase = $base;
	
	$url = getRequestUrl();
	if (startsWith($url, $base)) {
		$url = excludePrefix($url, $base);
		
	} else {
		$url = excludePrefix($url, $sslBase);
		
	}
	
	$url = excludePrefix($url, "/");
	
	$arr = explode("/", $url);
	
	return arr($arr, 0);
}

function getNoQueryUrl($url) {

	$pos = mb_strpos($url, "?");
	if ($pos === false) return $url;
	
	return mb_substr($url, 0, $pos);
}
