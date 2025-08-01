<?php 
/*
DB内のログデータやログ画像の数を制限するためのバッチ
*/
define("BATCH", true);
$BACTH_LOCK = ["name"=>basename(__FILE__), "limit"=>"-360 minute"];
include(dirname(__FILE__)."/../../procedural_php/ProceduralPhp.php");

infoLog("＝＝＝＝＝＝＝＝＝＝データの上限超過削除を開始＝＝＝＝＝＝＝＝＝＝");

$recogLogLimit = 2000000; // 認証ログのログ保存上限数
$recogLogPictureLimit = 800000; // 認証ログのログ画像保存上限数
$otherLogLimit = 100000; // 認証ログ以外のログ保存上限数

// ＝＝＝＝＝＝＝＝＝＝ 認識ログの確認と削除 ＝＝＝＝＝＝＝＝＝＝ //
infoLog("認証ログの削除開始：{$recogLogLimit}件以下のログを検索");

// 上限数+1のログの認証日時を取得
$deleteLogTimestamp = DB::selectOne("SELECT recog_time FROM t_recog_log ORDER BY recog_time DESC Limit $recogLogLimit, 1");

if (!empty($deleteLogTimestamp)) {
  infoLog("認識ログの削除ボーダー時間：$deleteLogTimestamp");
  
  // 上限数を超えたログは削除を行う
  $deleteLogResult = DB::delete("DELETE FROM t_recog_log WHERE recog_time <= {value}", $deleteLogTimestamp);
  DB::commit();
  infoLog("t_recog_logを{$deleteLogResult}件削除");
  
  // 上限数より過去のログとリンクする集計結果は削除する
  $deleteAnalizeResult = DB::delete("DELETE FROM t_recog_analize WHERE recog_time <= {value}", $deleteLogTimestamp);
  DB::commit();
  infoLog("t_recog_analizeを{$deleteAnalizeResult}件削除");

  $deleteAnalizeDailyResult = DB::delete("DELETE FROM t_recog_analize_daily WHERE sum_date <= {value}", date('Y-m-d', strtotime("$deleteLogTimestamp -1 day")));
  DB::commit();
  infoLog("t_recog_analize_dailyを{$deleteAnalizeDailyResult}件削除");

  $deleteAnalizeHourlyResult = DB::delete("DELETE FROM t_recog_analize_hourly WHERE sum_time <= {value}", $deleteLogTimestamp);
  DB::commit();
  infoLog("t_recog_analize_hourlyを{$deleteAnalizeHourlyResult}件削除");
} else {
  infoLog("認識ログの削除ボーダー時間：削除対象なし");
}

// ＝＝＝＝＝＝＝＝＝＝ 認識ログ画像の削除 ＝＝＝＝＝＝＝＝＝＝ //
infoLog("認証ログ画像の削除開始：{$recogLogPictureLimit}件以下のログを検索");

// 上限数+1のログの認証日時を取得
$subSQL = "SELECT recog_time FROM t_recog_log ORDER BY recog_time DESC Limit $recogLogPictureLimit, 1";
$mainSQL = "
  SELECT
    d.s3_path_prefix,
    r.s3_object_path
  FROM t_recog_log AS r
  INNER JOIN m_device AS d
  ON r.device_id = d.device_id
  WHERE r.recog_time <= ($subSQL) AND r.s3_object_path IS NOT NULL
";
$deletePictureArray = DB::selectArray($mainSQL, "");
$deletePictureCount = count($deletePictureArray);

infoLog("ログ画像削除対象{$deletePictureCount}件");

// 上限数を超えたログの認証画像を削除する
foreach ($deletePictureArray as $key => $value) {
  if (!empty($value["s3_object_path"])) {
    $picture_path = LOCAL_PICTURE_DIR . "/" . $value["s3_path_prefix"] . "/recog" . $value["s3_object_path"];
    if (unlink($picture_path)) {
      infoLog("ログ画像削除成功：$picture_path");
    } else {
      warnLog("ログ画像削除失敗：$picture_path");
    }
  } else {
    continue;
  }
}

// 上限数を超えたログのs3_object_pathをNULLにしてpicture_flagを-1にする
$picUpdate = DB::update("UPDATE t_recog_log SET s3_object_path = NULL, picture_flag = -1 WHERE recog_time <= ($subSQL)");
infoLog("画像情報を{$picUpdate}件更新");
DB::commit();


// ＝＝＝＝＝＝＝＝＝＝ 各種ログの削除 ＝＝＝＝＝＝＝＝＝＝ //
// 上限数を超えたt_facet_operate_log, t_operate_log, t_sync_logを削除する
infoLog("その他ログの削除開始：{$otherLogLimit}件以下のログを検索");

$subSQLfl = "SELECT operate_time FROM t_facet_operate_log ORDER BY operate_time DESC Limit {$otherLogLimit}, 1";
$subSQLol = "SELECT operate_time FROM t_operate_log ORDER BY operate_time DESC Limit {$otherLogLimit}, 1";
$subSQLsl = "SELECT begin_time FROM t_sync_log ORDER BY begin_time DESC Limit {$otherLogLimit}, 1";

$flDelete = DB::delete("DELETE FROM t_facet_operate_log WHERE operate_time <= ($subSQLfl)");
infoLog("t_facet_operate_logを{$flDelete}件削除");
$olDelete = DB::delete("DELETE FROM t_operate_log WHERE operate_time <= ($subSQLol)");
infoLog("t_operate_logを{$flDelete}件削除");
$slDelete = DB::delete("DELETE FROM t_sync_log WHERE begin_time <= ($subSQLsl)");
infoLog("t_sync_logを{$flDelete}件削除");

infoLog("＝＝＝＝＝＝＝＝＝＝データの上限超過削除を終了＝＝＝＝＝＝＝＝＝＝");
