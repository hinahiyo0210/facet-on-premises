<?php 

class RecogSummaryService {
		
	// 集計を行う。
	public static function summaryRecog($device_id, $date /* Y/m/d */) {
		
		set_time_limit(60 * 2);	// 時間延長。
		
		infoLog("集計を開始。device_id={$device_id} date={$date}");
		
		$begin = $date." 00:00:00";
		$end   = addDate($date, "+1 day")." 00:00:00";
		
		$param = ["device_id"=>$device_id, "begin"=>$begin, "end"=>$end];

		// このカラムを対象に集計を行う。
//		$sumColumn = "r.create_time";
		$sumColumn = "r.recog_time";
		
		// ------------------------------ 時別。
		DB::delete("delete from t_recog_analize_hourly where device_id = {device_id} and sum_time >= {begin} and sum_time < {end}", $param);
		
		$sql = "
			insert into 
				t_recog_analize_hourly
			select 
				{$device_id}
				, date_format({$sumColumn}, '%Y/%m/%d %H:00:00')
				, count(r.recog_log_id) as total_count
				, count(a.recog_analize_id) as ana_count
			    , sum(case when r.search_score > a.search_threshold  then 1 else 0 end) as success_count
			    , sum(case when a.temperature_alarm = 2 then 1 else 0 end) as temp_alert_count
			    , sum(case when a.mouthocc = 2 then 1 else 0 end) as mask_alert_count
				, sum(case when r.person_code is not null then 1 else 0 end) as registed_count
			
			from 
				t_recog_log r
				left outer join t_recog_analize a on
				r.device_id = a.device_id
				and r.recog_time = a.recog_time
			    
			where
				r.device_id = {device_id}
 				and {$sumColumn} >= {begin}
			   	and {$sumColumn} < {end}
				
			group by 
				date_format({$sumColumn}, '%Y/%m/%d %H:00:00')

			having
				count(r.recog_log_id) is not null

			";
		
		infoLog("時別集計を開始。device_id={$device_id}, begin={$begin}, end={$end}");
		$inserted = DB::update($sql, $param);
		infoLog("時別集計を終了。inserted={$inserted}");
	
		// ------------------------------ 日別(時別を足すだけ)。
		DB::delete("delete from t_recog_analize_daily where device_id = {device_id} and sum_date >= {begin} and sum_date < {end}", $param);
		
		$sql = "
			insert into 
				t_recog_analize_daily
			select 
				{$device_id}
				, '{$date}'
				, sum(total_count) 
				, sum(ana_count)
			    , sum(success_count) 
			    , sum(temp_alert_count) 
			    , sum(mask_alert_count) 
				, sum(registed_count) 
			
			from 
				t_recog_analize_hourly h
			    
			where
				h.device_id = {device_id}
				and h.sum_time >= {begin}
			   	and h.sum_time < {end}
			having
				sum(total_count) is not null
			";
		
		infoLog("日別集計を開始。device_id={$device_id}, begin={$begin}, end={$end}");
		$inserted = DB::update($sql, $param);
		DB::commit();
		infoLog("日別集計を終了。inserted={$inserted}");
	}
	
	
	// 集計を取得する。
	public static function getSummaryRecog($deviceIds, $day) {
		
		$sql = "
			select
				sum(total_count) 		as total_count 
				, sum(ana_count) 		as ana_count
				, sum(success_count) 	as success_count
				, sum(temp_alert_count) as temp_alert_count
				, sum(mask_alert_count) as mask_alert_count
				, sum(registed_count) 	as registed_count
			from
				t_recog_analize_daily
			where
				sum_date      >= {from}
				and sum_date  <= {to}
				and device_id in {in device_ids}
		";
		
		$param = [];
		$param["device_ids"] = $deviceIds;
		
		// 割合を計算。
		$cacRatio = function($a, $b) {

			if ($b == 0) {
				return 0.0;
			}
			
			return formatNumber($a / $b * 100, 1);
		};
		
		
		// 加工処理。
		$convert = function($row) use($cacRatio) {
			
			foreach ($row as $k=>$v) {
				if (empty($v)) {
					$row[$k] = 0;
				}
			}
			
			// 認証成功率
			$row["success_ratio"] = $cacRatio($row["success_count"], $row["total_count"]);
			
			// 発熱異常割合
			$row["temp_alert_ratio"] = $row["total_count"] == 0 ? 0 : $cacRatio($row["temp_alert_count"], $row["total_count"]);

			// 発熱正常割合
			$row["temp_ok_ratio"]    = $row["total_count"] == 0 ? 0 : 100.0 - $row["temp_alert_ratio"];
			
			// マスク異常割合
			$row["mask_alert_ratio"] = $row["total_count"] == 0 ? 0 : $cacRatio($row["mask_alert_count"], $row["total_count"]); 
			
			// マスク正常割合
			$row["mask_ok_ratio"]    = $row["total_count"] == 0 ? 0 : 100.0 - $row["mask_alert_ratio"];
			
			// ゲスト数
			$row["guest_count"]   = $row["total_count"] - $row["registed_count"]; 
			
			// ゲスト比率
			$row["guest_rato"]    = $row["total_count"] == 0 ? 0 : $cacRatio($row["guest_count"], $row["total_count"]); 
			
			// 登録者比率
			$row["registed_rato"] = $row["total_count"] == 0 ? 0 : 100.0 - $row["guest_rato"];
						
			return $row;
		};
		
		// 前月比率を求める。
		$lastRatio = function(&$setArr, $setValName, $setCompTypeName, $thisMonthVal, $lastMonthVal) use($cacRatio) {
			
			if ($thisMonthVal == $lastMonthVal) {
				// 比較対象と同じ。
				$setArr[$setValName] = 0.0;
				$setArr[$setCompTypeName] = 1;
				
			} else if ($thisMonthVal > $lastMonthVal) {
				// 比較対象よりも大きい数字である。
				$setArr[$setValName] = $cacRatio($lastMonthVal, $thisMonthVal);
				$setArr[$setCompTypeName] = 2;
				
			} else {
				// 比較対象よりも小さい数字である。
				$setArr[$setValName] = $cacRatio($thisMonthVal, $lastMonthVal);
				$setArr[$setCompTypeName] = 3;

			}
		};
		
		
		// 今月分を取得。
		$param["from"] = date("Y/m")."/01";
		$param["to"]   = getMonthLastDate($param["from"]);
		$thisMonth     = $convert(DB::selectRow($sql, $param));

		// 今日分を取得。
		$param["from"] = date("Y/m/d");
		$param["to"]   = date("Y/m/d");
		$thisDay       = $convert(DB::selectRow($sql, $param));
		
		// 先月分（全体）を取得。
		$param["from"] = addDate(date("Y/m")."/01", "-1 month");
		$param["to"]   = getMonthLastDate($param["from"]);
		$lastMonth     = $convert(DB::selectRow($sql, $param));

		// 昨日分を取得。
		$param["from"] = addDate(date("Y/m/d"), "-1 day");
		$param["to"]   = addDate(date("Y/m/d"), "-1 day");
		$yesterday     = $convert(DB::selectRow($sql, $param));
		
		// 先月分（同一日まで）を取得。
		$param["from"] = addDate(date("Y/m")."/01", "-1 month");
		$param["to"]   = addDate($param["from"], "+ ".date("d")."day");
		if (strtotime($param["to"]) > strtotime(getMonthLastDate($param["from"]))) {
			$param["to"] = getMonthLastDate($param["from"]);
		}
		$lastMonthSameDay = $convert(DB::selectRow($sql, $param));
		
		// 前月比を求める。
		$compMonth = [];
		$lastRatio($compMonth, "total_count_ratio"  , "total_count_compType"     , $thisMonth["total_count"]     , $lastMonthSameDay["total_count"]);
		$lastRatio($compMonth, "success_count_ratio", "success_count_compType"   , $thisMonth["success_count"]   , $lastMonth["success_count"]);
		$lastRatio($compMonth, "temp_alert_ratio"   , "temp_alert_ratio_compType", $thisMonth["temp_alert_ratio"], $lastMonth["temp_alert_ratio"]);
		$lastRatio($compMonth, "mask_alert_ratio"   , "mask_alert_ratio_compType", $thisMonth["mask_alert_ratio"], $lastMonth["mask_alert_ratio"]);

		// 昨日比を求める。
		$compDay = [];
		$lastRatio($compDay, "total_count_ratio"  , "total_count_compType"     , $thisDay["total_count"]     , $yesterday["total_count"]);
		$lastRatio($compDay, "success_count_ratio", "success_count_compType"   , $thisDay["success_count"]   , $yesterday["success_count"]);
		$lastRatio($compDay, "temp_alert_ratio"   , "temp_alert_ratio_compType", $thisDay["temp_alert_ratio"], $yesterday["temp_alert_ratio"]);
		$lastRatio($compDay, "mask_alert_ratio"   , "mask_alert_ratio_compType", $thisDay["mask_alert_ratio"], $yesterday["mask_alert_ratio"]);
		
		// ------------------------------ 時間帯別統計を取得。
		if (empty($day)) $day = date("Y/m/d");
		$list = DB::selectArray("
			select 
				sum_time
				, total_count
				, registed_count
				, temp_alert_count
				, mask_alert_count

			from 
				t_recog_analize_hourly 
			where 
				device_id in {in device_ids} 
				and sum_time >= {from} 
				and sum_time < {to} 

		", ["device_ids"=>$deviceIds, "from"=>$day, "to"=>addDate($day, "+1 day")]);
		
		// 時刻をキーに。
		$hList = [];
		foreach ($list as $item) $hList[((int) formatDate($item["sum_time"], "H"))] = $item;
		
		// グラフ向けのフラットな配列を作成する。
		$tempAlertCountList = [];
		$guestCountList = [];
		$registedCountList = [];
		$dayTotalCount = 0;
		for ($i = 0; $i < 24; $i++) {
			if (isset($hList[$i])) {
				$item = $hList[$i];
				$dayTotalCount += $item["total_count"];
				
				$tempAlertCountList[] 	= $item["temp_alert_count"];
				$guestCountList[] 		= $item["total_count"] - $item["registed_count"];
				$registedCountList[] 	= $item["registed_count"];
			} else {
				$tempAlertCountList[] 	= 0;
				$guestCountList[] 		= 0;
				$registedCountList[] 	= 0;
			}
		}
		
		
		return [
			  "thisMonth"			=> $thisMonth
			, "lastMonth"			=> $lastMonth
			, "thisDay"				=> $thisDay
			, "yesterday"			=> $yesterday
			, "lastMonthSameDay"	=> $lastMonthSameDay
			, "compMonth"			=> $compMonth
			, "compDay"				=> $compDay
			, "tempAlertCountList"	=> $tempAlertCountList
			, "guestCountList"		=> $guestCountList
			, "registedCountList"	=> $registedCountList
			, "dayTotalCount"       => $dayTotalCount
		];
	}

	
	// 期間集計を行う。
	public static function getSpanSummary($deviceIds, $span1From, $span1To, $span2From, $span2To) {
		
		// データ取得。
		$getList = function($spanDays, $from, $to) use($deviceIds) {
			
			$sql = "
				select 
					sum_date
					, total_count
					, temp_alert_count
				from 
					t_recog_analize_daily 
				where 
					sum_date 		>= {from} 
					and sum_date	<= {to} 
					and device_id in {in device_ids}
			";
			
			$list = DB::selectArray($sql, ["device_ids"=>$deviceIds, "from"=>$from, "to"=>$to]);
			
			// 日付をキーに。
			$dayList = [];
			foreach ($list as $item) $dayList[formatDate($item["sum_date"])] = $item;
			
			// グラフ向けのフラットな配列に。
			$totalCountList = [];
			$tempAlertCountList = [];
			$spanTotalCount = 0;
			foreach ($spanDays as $day) {
				if (isset($dayList[$day])) {
					$item = $dayList[$day];
				
					$spanTotalCount      += $item["total_count"];
					$totalCountList[] 	  = $item["total_count"];
					$tempAlertCountList[] = $item["temp_alert_count"];
				} else {
					$totalCountList[] 	  = 0;
					$tempAlertCountList[] = 0;
				}
			}
		
			return ["totalCountList"=>$totalCountList, "tempAlertCountList"=>$tempAlertCountList, "spanTotalCount"=>$spanTotalCount];
		};
		
 		// 日付リスト作成。
		$minDate = DB::selectOne("select min(sum_date) from t_recog_analize_daily where device_id in {in device_ids}", $deviceIds);
		$maxDate = DB::selectOne("select max(sum_date) from t_recog_analize_daily where device_id in {in device_ids}", $deviceIds);
		
		if (empty($minDate)) {
			$minDate = date("Y/m/d");
			$maxDate = date("Y/m/d");
		}
		
		$minFrom = min(strtotime($span1From), strtotime($span2From), strtotime($minDate));
 		$maxTo   = max(strtotime($span1To), strtotime($span2To), strtotime($maxDate));
 		
 		$minFrom = formatDate($minFrom);
 		$maxTo = formatDate($maxTo);
 		
 		$spanDays = getSpanDays($minFrom, $maxTo);
		
		// 期間データ取得。
 		$span1 = $getList($spanDays, $span1From, $span1To);
 		$span2 = $getList($spanDays, $span2From, $span2To);
		
 		return ["minFrom"=>$minFrom, "maxTo"=>$maxTo, "spanDays"=>$spanDays, "span1"=>$span1, "span2"=>$span2];
	}
	
	
	
}