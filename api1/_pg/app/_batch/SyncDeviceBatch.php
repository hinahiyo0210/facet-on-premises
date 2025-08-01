<?php 
/**
同期処理。

php C:\eclipse-php\workspace\ds-api_v1\api1\_pg\app\_batch\SyncDeviceBatch.php
 */
define("BATCH", true);
$BACTH_LOCK = ["name"=>basename(__FILE__), "limit"=>"-29 minute"];
include(dirname(__FILE__)."/../../procedural_php/ProceduralPhp.php");

// 処理対象のIDを収集。
$deviceList = DB::selectArray("select * from m_device where contract_state = 10");

foreach ($deviceList as $device) {
	
	infoLog("deviceId: ".$device["device_id"]);

	// AIカメラの場合は処理をスキップする
	if (($device['device_type'] === "AIカメラ") || (mb_substr(explode('-', $device['device_type'])[1], 0, 1) == 8)) {
		infoLog("AIカメラのためSync処理をスキップします。".$device['device_type']);
		continue;
	}

	try {
		DeviceService::checkDevice($device);
	} catch (DeviceWsException $e) {
		continue;
	}
	
	// 同期処理。
	$sync_type = 1;
	try {
		$ret = SyncService::syncDevice($device, $sync_type, basename(__FILE__));
		if (!$ret) {
			warnLog(json_encode(Errors::getMessagesArray(), JSON_UNESCAPED_UNICODE));
			continue;
		}
		
	} catch (Exception $e) {
		errorLog(
			$e->getMessage()."\n".
			join("\n", Errors::getMessagesArray())."\n".
			$e->getTraceAsString()
		);

		// 同期ログで未保存のデータがあるのであればupdate
		SyncService::updateEndLog(SyncService::$processingRegisted ? 40 : 30);

	}
	
	DB::commit();
	
}


// APB状況の修復を行う
$deviceList = DB::selectArray("
	select 
		* 
	from 
		m_device d
	where 
		contract_state = 10
		and exists(
			select 1 from m_contractor c where c.contractor_id = d.contractor_id and c.apb_mode_flag = 1 
		)
");
ApbService::repairAll($deviceList);
DB::commit();
