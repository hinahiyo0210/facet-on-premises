<?php 
/**
集計処理。

php C:\eclipse-php\workspace\ds-ui_v1\ui1\_pg\app\_batch\RecogSummaryBatch.php [targetDate] [device_id]
 */
define("BATCH", true);
$BACTH_LOCK = ["name"=>basename(__FILE__), "limit"=>"-15 minute"];
include(dirname(__FILE__)."/../../procedural_php/ProceduralPhp.php");

$targetDate = arr($argv, 1);
$device_id  = arr($argv, 2);

infoLog("認識ログ集計バッチを開始。$device_id $targetDate");

$targetDates = [];
if (empty($targetDate)) {
	$today = date("Y/m/d");
	
	// 引数指定が無い場合
	if (((int) date("H")) == 0) {
		// 現在時刻が00時台なのであれば、前日と本日を対象に。
		$targetDates[] = addDate($today, "-1 day");
	}
	$targetDates[] = $today;
	
} else {
	// 引数指定がある場合。
	if (startsWith($targetDate, "from:")) {
		// プレフィックス「from:」が付与されている場合には、その日移行から本日までの再集計を意味する。
		$from = excludePrefix($targetDate, "from:");
		$targetDates = getSpanDays($from, date("Y/m/d"));
		
	} else {
		// 単一の日付指定。
		$targetDates[] = $targetDate;
	}
	
}

if (!empty($device_id)) {
	// デバイスIDの指定がもしれあれば、それに従う。
	foreach ($targetDates as $targetDate) {
		RecogSummaryService::summaryRecog($device_id, $targetDate);
	}
	
} else {

	// 引数指定が無ければ全てを対象に。
	// 契約者単位。デバイス単位に処理を行う。
	$contractorIds = DB::selectOneArray("select contractor_id from m_contractor where state in (10, 20, 30)");
	foreach ($contractorIds as $contractor_id) {
		
		$deviceIds = DB::selectOneArray("select device_id from m_device where contractor_id = {value} and contract_state in(10)", $contractor_id);
		foreach ($deviceIds as $device_id) {

			foreach ($targetDates as $targetDate) {
				RecogSummaryService::summaryRecog($device_id, $targetDate);
			}
			
		}
		
	}
	
		
}


