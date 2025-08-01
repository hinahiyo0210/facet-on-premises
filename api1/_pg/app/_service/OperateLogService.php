<?php

class OperateLogService {
	
	// DBから取得した操作ログについて、APIで返却するフォーマットに変換する。
	public static function convertApiFormat($row) {
		
		$ret = [];
		$ret["operateTime"] = formatDate($row["operate_time"], "Y/m/d H:i:s");
		$ret["operateUser"] = $row["operate_user"];
		$ret["mainType"]   = $row["main_type"];
		$ret["subType"]    = $row["sub_type"];
		if (empty($row["detail_json"])) {
			$ret["detail"] = "";
		} else {
			$detail = json_decode($row["detail_json"], 1);
			if (empty($detail)) {
				$ret["detail"] = "";
			} else {
				$arr = [];
				foreach ($detail as $k=>$v) {
					//2021年12月29日追記システムセルフチェック内部エラー対応
					if(is_array($v)) {
						foreach($v as $vv => $vk) {
							$arr[] = $vv. ":" .$vk;
						}
					} else {
						$arr[] = $k.":".$v;
					}
					//ここまで追記
					//$arr[] = $k.":".$v;

				}
				$ret["detail"] = join(",", $arr);
				
			}
			
		}
		
		return $ret;
	}
	
	// 操作ログを取得。
	public static function getOperateLog(array $device, $fromTime, $toTime, $limit) {
		
		$ret = WsApiService::accessWsApi($device, [
			"method"=>"log.search"
 			, "id"=>WsApiService::genId()
 			, "params"=>["condition"=>[
 					"startTime"=>formatDate($fromTime, "Y-m-d H:i:s"), 
 					"endTime"  =>formatDate($toTime, "Y-m-d H:i:s")
 				]
 			]
		]);
		
 		$token = $ret["params"]["token"];
		
		$ret = WsApiService::accessWsApi($device, [
			"method"=>"log.getResult"
 			, "id"=>WsApiService::genId()
 			, "params"=>["token"=>(int) $token, "count"=>$limit]
		]);
		
		$stopRet = WsApiService::accessWsApi($device, [
			"method"=>"log.stopSearch"
 			, "id"=>WsApiService::genId()
 			, "params"=>["condition"=>[
 					"token"=>$token
 				]
 			]
		]);
		infoLog("log.stopSearch:".json_encode($stopRet, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		
		if (empty($ret["params"]["items"])) return [];
		
		/*
        "items": [
            {
                "Detail": {
                    "RebootReason": "パワーオフして再起動",
                    "Time": "2020-09-25 19:06:21"
                },
                "MainType": "異常",
                "SubType": "異常再起動",
                "Time": "2020-09-28 11:16:03",
                "User": "System"
            },
            {
                "Detail": null,
                "MainType": "操作",
                "SubType": "起動",
                "Time": "2020-09-28 11:16:03",
                "User": "System"
            },
 		*/
		
		return $ret["params"]["items"];
	}
	
	

	// WSから操作ログを取得し、DBに保存。
	public static function syncOperateLog($device, $sync_type, $rangeSecFrom = false, $rangeSecTo = false, $limit = 500) {
		
		$deviceId = $device["device_id"];
		
		// 制限時間を延長。
		set_time_limit(120);
		
		if (empty($rangeSecFrom)) {
			// 最終取得ログ日時を取得。
			$rangeSecFrom = DB::selectOne("select max(operate_time) from t_operate_log where device_id = {$deviceId} ");
			
			// ログが無い場合には、デバイスマスター側の運用開始日とする。
			if (empty($rangeSecFrom)) {
				$rangeSecFrom = DB::selectOne("select start_date from m_device where device_id = {$deviceId}");
			}
				
		}
		
		// 検索対象開始日時：Unitタイムスタンプに。
		$rangeSecFrom = strtotime($rangeSecFrom);
		
		// 検索対象開始日時：同じデータの取得を避けるために+1秒する。
		$rangeSecFrom += 1;
		
		// 検索対象終了日時：指定があればその値に。無ければ現在日時に。 
		if (empty($rangeSecTo)) {
			$rangeSecTo = time();
		} else {
			$rangeSecTo = strtotime($rangeSecTo);
		}
		
		// ================================================================== ログの取得
		$operateRegisted = 0;
		$list = OperateLogService::getOperateLog($device, $rangeSecFrom, $rangeSecTo, $limit); // API実行。
		
		// データは洗い替えを行う。
		DB::delete("
			delete from 
					t_operate_log 
			where 
				device_id = {device_id}
				and operate_time >= {fromTime} 
				and operate_time <= {toTime}
			", ["device_id"=>$deviceId, "fromTime"=>date("Y/m/d H:i:s", $rangeSecFrom) , "toTime"=>date("Y/m/d H:i:s", $rangeSecTo)]);
		
		foreach ($list as $r) {
			
			set_time_limit(10);	// 制限時間を延長。
			
			// DBに登録。
			$sql = "
				insert into 
					t_operate_log
				set 
					device_id 		= {device_id}
					, sync_log_id 	= {sync_log_id} 
					, sync_type 	= {sync_type}
					, create_time	= now()  
					, operate_time	= {operate_time}
					, operate_user	= {operate_user}
					, main_type		= {main_type}
					, sub_type		= {sub_type}
					, detail_json	= {detail_json}
			";
			
			$param = [];
			$param["device_id"] 	= $deviceId;
			$param["sync_log_id"] 	= SyncService::$processingSyncLogId;
			$param["sync_type"] 	= $sync_type;
			$param["operate_time"] 	= formatDate($r["Time"], "Y/m/d H:i:s");
			$param["operate_user"] 	= $r["User"];
			$param["main_type"] 	= $r["MainType"];
			$param["sub_type"] 		= $r["SubType"];
			$param["detail_json"] 	= json_encode($r["Detail"], JSON_UNESCAPED_UNICODE);
			
			DB::insert($sql, $param);
			$operateRegisted++;
			SyncService::$processingRegisted = true;
		}
		
		set_time_limit(30);	// 制限時間を延長。
		
		return ["operateLogRegisted"=>$operateRegisted];
	}
	
	
	
}
