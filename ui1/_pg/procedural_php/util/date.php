<?php
/**
 *
 * 日付関連の関数
 *
 *
 *
 */


/**
 * 年度を取得する
 * @param $targetDate 対象日文字列
 * @param $startMonth 年度の切り替え月(4月が年度の開始である場合に4)
 */
function getFiscalYear($targetDate, $startMonth = 4) {

	$sec = strtotime($targetDate);
	$date = getdate($sec);

	// 年度を計算する
	$year = $date['year'];
	$month = $date['mon'] - ($startMonth - 1);
	$result = getdate(mktime(0, 0, 0, $month, 1, $year));

	return $result['year'];
}


/**
 * 日時フォーマット
 * @param string $val 日付文字列
 * @param boolean $timeBr 日付と時刻の間を<br />で区切る場合はtrue
 */
function formatTime($val, $timeBr = false) {
	if ($val == null) return $val;
	$ret = str_replace("-", "/", $val);
	if ($timeBr) {
		$ret = str_replace(" ", "<br />", $ret);
	}
	return $ret;
}

/**
 * 日付フォーマット
 * @param string $val strtotimeで評価可能な日付文字列
 * @param string $format dateで評価可能なフォーマット
 */
function formatDate($val, $format = "Y/m/d") {
	if ($val == null) return $val;
	if (is_numeric($val)) {
		$sec = $val;
	} else {
		$sec = strtotime($val);
	}
	return date($format, $sec);

}

/**
 * 日付フォーマット(UNIXタイムスタンプ)
 * @param string $val strtotimeで評価可能な日付文字列
 */
function formatIntDate($val) {
	if ($val == null) return $val;

	if (is_numeric($val)) return $val;

	return strtotime($val);
}




/**
 * 日付を加算
 * @param string $val 日付文字列
 * @param string $add strtotimeで評価可能な加算式（例えば "+1 day"）
 * @param string $format 返す際のフォーマット
 */
function addDate($val, $add, $format = "Y/m/d") {

	$dt = new DateTime($val.' '.$add);
	return $dt->format($format);
}




/**
 * 月の初日を取得
 * @param string $date 日付文字列
 */
function getMonthFirstDate($date) {

	$sec = strtotime($date);
	$first = mktime(0, 0, 0, date("m", $sec), 1, date("Y", $sec));
	return date("Y/m/d", $first);
}

/**
 * 月末を取得
 * @param string $date 日付文字列
 */
function getMonthLastDate($date) {

	$sec = strtotime($date);
	$last = mktime(0, 0, 0, date("m", $sec) + 1, 0, date("Y", $sec));
	return date("Y/m/d", $last);
}


/**
 * 曜日を取得
 * @param string $date 日付文字列
 */
function getWeekday($date) {

	$arr = mb_split("/", formatDate($date));
	if (count($arr) != 3) {
		return "";
	}

	$w = @date("w", mktime(0, 0, 0, $arr[1], $arr[2], $arr[0]));

	$weekday = array( "日", "月", "火", "水", "木", "金", "土" );
	return arr($weekday, $w);
}

/**
 * 年齢を取得
 * @param string $birth 日付文字列
 */
function getAge($birth) {
	if ($birth == null) return "";

	$ty = date("Y");
	$tm = date("m");
	$td = date("d");
	$by = formatDate($birth, "Y");
	$bm = formatDate($birth, "m");
	$bd = formatDate($birth, "d");
	$age = $ty - $by;
	if($tm * 100 + $td < $bm * 100 + $bd) $age--;
	return $age;
}


/**
 * 2つの日付の差分日数を取得
 * @param string $date1 日付形式の文字列
 * @param string $date2 日付形式の文字列
 */
function getDiffDays($date1, $date2) {
	if ($date1 == null || $date2 == null) return null;

	return (strtotime($date2)-strtotime($date1))/(3600*24);
}

/**
 * 2つの日付の差分月数を取得
 *
 * 例） 2011-01-01 ～ 2011-03-16 の場合に　2.4　を返す
 *
 * @param string $d1 日付1
 * @param string $d2 日付2
 */
function getDiffMonthes($d1, $d2) {
	if ($d1 == null || $d2 == null) return null;

	$days = getDiffDays($d1, $d2);

	$m = floor($days / 30 * 10) / 10;

	return $m;
}



/**
 * 和暦変換
 * @param string $timestamp 日付文字列
 */
function jdate($date, $format = "Vv年n月j日") {

	if ($date == null) {
		$date = date("Y/m/d");
	}

    // 配列分割
    $y = formatDate($date, "Y");
    $m = formatDate($date, "m");
    $d = formatDate($date, "d");

    // 指定した日時のタイムスタンプを取得する
    $timestamp = mktime(0, 0, 0, intval($m), intval($d), intval($y));

    // 判定用変換
    $ymd = date("Ymd", $timestamp);
    $year = date("Y", $timestamp);

    //日本元号配列
    $j_ary = array(    array("明治",1867),array("大正",1911),array("昭和",1925),array("平成",1988));

    //日付から配列ナンバーを代入
    if($ymd <= "19120729")                                                        $j_num = 0;
    elseif($ymd >= "19120730" && $ymd <= "19261224")     $j_num = 1;
    elseif($ymd >= "19261225" && $ymd <= "19890107")     $j_num = 2;
    elseif($ymd >= "19890108" )                                                 $j_num = 3;

    //引数日付をフォーマット形式に変換
    $ret = date($format, $timestamp);

    //元号へ置換
    $ret = str_replace("V", $j_ary[$j_num][0], $ret);

    //和暦へ置換
    $ret = str_replace("v", $year - $j_ary[$j_num][1], $ret);

    return $ret;
}



/**
 * fromからtoまでの日付で構成された配列を作成する
 * @param string $from 日付from
 * @param string $to 日付to
 * @param string $format フォーマット
 */
function getSpanDays($from, $to, $format = "Y/m/d") {

	$toSec = strtotime($to);
	$sec = strtotime($from);
	$add = 1;

	$ret = array();

	while ($sec <= $toSec) {

		$ret[] = date($format, $sec);

		$sec = strtotime($from." + $add day");
		$add++;
	}

	return $ret;
}


// 対象の月の日(数値)を配列で取得
function getMonthDays($y, $m) {

	if ($y == null || $m == null) return array();

	$from = $y."-".$m."-01";
	$to = addDate($from, "+ 1 month");
	$to = addDate($to, "- 1 day");

	return getSpanDays($from, $to, "j");
}

