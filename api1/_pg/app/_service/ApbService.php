<?php 

class ApbService {

	// 修復処理の最大試行回数。
	const MAX_REPAIR_TRY_COUNT = 10;
	
	
	// ログのenumを作成。
	public static function createApbLogEnum() {
		
		SimpleEnums::add("apb_log_type", [
			  "I01"=>"APB設定が行われたデバイスではありません。"
			, "I02"=>"クラウド上の通行可能時間帯設定を「通行許可」としました。"
			, "I03"=>"クラウド上の通行可能時間帯設定を「通行拒否」としました。"
			, "I04"=>"デバイスへの通行許可情報の送信が正常に終了しました。"
			
			, "I05"=>"デバイス側データの不整合の修復を行いました。"
			, "I06"=>"クラウド上のデータ不整合修復として通行可能時間帯設定を「通行許可」としました。"
			, "I07"=>"クラウド上のデータ不整合修復として通行可能時間帯設定を「通行拒否」としました。"
			
			, "I08"=>"入室専用デバイスでの認識のため、入退室状況は変更されません。"
			
			, "W01"=>"未登録者です。人物データがクラウド上に見つかりません。"
			, "W02"=>"APBグループが設定されていないデバイスです。"
			, "W03"=>"入室中の人物が、再度入退デバイスで認証されました。"
			, "W04"=>"退室中の人物が、再度退室デバイスで認証されました。"
			, "W05"=>"データ修復を実行しようとしましたが、APBグループが設定されていないデバイスです。"
			, "W06"=>"通行が許可されませんでした。"
				
			, "E01"=>"契約者情報が見つかりません。"
			, "E02"=>"デバイスへ通行許可情報を送信する事が出来ませんでした。"
			, "E03"=>"デバイス側データの不整合の修復に失敗しました。"
			, "E04"=>"デバイス側データの不整合修復の試行上限に到達しました。"
				
		]);
		
	}
	
	// 契約者情報よりAPBの有効性を確認する。
	public static function isActive($device) {
		
		$apb_mode_flag = DB::selectOne("select apb_mode_flag from m_contractor where contractor_id = {contractor_id}", $device);
		
		return !empty($apb_mode_flag);
	}
	
	
	// 修復対象を登録。
	public static function registRepairTarget($device, $personId, $personCode) {
		
		// 人物特定されていないケースは無視。
		if (empty($personId) && empty($personCode)) return;
		
		// personIdを特定。
		if (empty($personId) && !empty($personCode)) {
			
			$personId = DB::selectOne("select person_id from t_person where person_code = {person_code} and contractor_id = {contractor_id}", [
				"person_code"=>$personCode
				, "contractor_id"=>$device["contractor_id"]
			]);
			
			// personIdが特定出来ない場合はクラウド未登録者なので無視。
			if (empty($personId)) return;
		}
		
		$param = ["device_id"=>$device["device_id"], "person_id"=>$personId];
		
		// 既に同じデータが存在するのであれば何もしない。
		if (DB::exists("select 1 from t_apb_repair where device_id = {device_id} and person_id = {person_id}", $param)) {
			return;
		}
		
		// 登録。
		$sql = "
			insert into
				t_apb_repair
			set
				device_id          = {device_id}
				, person_id        = {person_id}
				, create_time      = now()
				, update_time      = now()
				, repair_try_count = 0
		";
		
		DB::insert($sql, $param);
	}
	
	
	// ログを登録(都度commitする)。
	public static function registApbLog($type, $device, $person = null, $message = null, $transDeviceIds = null) {

		$logText = "ApbService:".$type." ".$device["device_id"]." ".arr($person, "person_id")." ".$message;
		if (!empty($transDeviceIds)) {
			$logText .= " ".join(",", $transDeviceIds);
		}
		if (startsWith($type, "I")) {
			infoLog($logText);
		} else if (startsWith($type, "W")) {
			warnLog($logText);
		} else {
			errorLog($logText);
		}
		
		$sql = "
			insert into 
				t_apb_log
			set
				create_time 	= now()
				, log_type 		= {log_type}
				, device_id 	= {device_id}
				, person_code  	= {person_code}
				, message 		= {message}
		";
		$apb_log_id = DB::insert($sql, ["log_type"=>$type, "device_id"=>$device["device_id"], "person_code"=>(empty($person) ? null : $person["person_code"]), "message"=>$message]);
		
		
		if (!empty($transDeviceIds)) {
			$sql = "insert into t_apb_log_device set apb_log_id = {apb_log_id}, trans_device_id = {trans_device_id}";
			
			foreach ($transDeviceIds as $transDeviceId) {
				DB::insert($sql, ["apb_log_id"=>$apb_log_id, "trans_device_id"=>$transDeviceId]);
			}
			
			DB::commitAll();
			DB::begin();
		}
		
		
	}
	
	
	// 同一グループに属するデバイスを取得する。
	public static function getApbGroupDevices($device_id, $apb_types) {
		
		$sql = "
			select
				*
			from
				m_device d 

			where
				d.contract_state = 10
				and d.apb_type in {in apb_types}
				and exists(
					select 
						1
					from 
						t_apb_group_device g
					where 
						g.device_id = d.device_id
						and exists(
							select 1
							from
								t_apb_group_device g0
							where
								g0.device_id = {device_id}
								and g0.apb_group_id = g.apb_group_id
						)
						
				)

		";
		
		return DB::selectArray($sql, ["device_id"=>$device_id, "apb_types"=>$apb_types]);
	}
	
	
	
	// recog_log_idから人物を取得する。
	// 取得出来ない場合にはログを出力する。
	public static function getPersonByRecogLogId($device, $recog_log_id) {
		
		// 人物データを取得する。
		$personCode = DB::selectOne("select person_code from t_recog_log where recog_log_id = {recog_log_id}", ["recog_log_id"=>$recog_log_id]);
		
		$person = null;
		
		if (!empty($personCode)) {
				
			$person = DB::selectRow("
				select 
					* 
				from 
					t_person 
				where 
					contractor_id = {contractor_id} 
					and person_code = {person_code}
			", ["person_code"=>$personCode, "contractor_id"=>$device["contractor_id"]]);
			
		}
		
		if (empty($person)) {
			self::registApbLog("W01", $device);	 // 未登録者です。人物データがクラウド上に見つかりません。 
		}
		
		return $person;
	}
	
	// クラウドDBの通行可能時間帯情報を更新
	private static function registCloudAccessTime($inDevices, $outDevices, $device, $person, $isRealtime) {
		
		// 通行を許可する通行可能時間帯設定。
		$enableAccessTimes  = [["accessFlag"=>1, "accessTimeFrom"=>"2000/01/01 00:00:00", "accessTimeTo"=>"2099/12/31 23:59:59"]];

		// 通行を禁止する通行可能時間帯設定。
		$disablAccessTimes  = [["accessFlag"=>0, "accessTimeFrom"=>"2000/01/01 00:00:00", "accessTimeTo"=>"2099/12/31 23:59:59"]]; 
		
		$enableDeviceIds = [];
		$disableDeviceIds = [];
		
		if ($device["apb_type"] == 1) {
			// ------------- 入退
			
			if ($isRealtime && !empty($person["apb_in_flag"])) {
				// リアルタイム処理中は入退中⇒入退の場合は何もしない。
				if ($isRealtime) self::registApbLog("W03", $device, $person);	// 入退中の人物が、再度入退デバイスで認証されました。
				return false;
			}
			
			// この人物について「入室」側に「通行禁止」を設定する。
			foreach ($inDevices as $d) {
				ApbService::registPersonAcessTimes($person["person_id"], $d["device_id"], $disablAccessTimes);
				$disableDeviceIds[] = $d["device_id"];
			}
			
			// この人物について「退室」側に「通行許可」を設定する。
			foreach ($outDevices as $d) {
				ApbService::registPersonAcessTimes($person["person_id"], $d["device_id"], $enableAccessTimes);
				$enableDeviceIds[] = $d["device_id"];
			}
			
			// 人物データの状況をUpdate。
			DB::update("update t_person set apb_in_flag = 1, update_time = now(), update_user_id = -2 where person_id = {value}", $person["person_id"]);
			
			
		} else if ($device["apb_type"] == 2) {
			// ------------- 退室
			
			if ($isRealtime && empty($person["apb_in_flag"])) {
				// リアルタイム処理中は退室中⇒退室の場合は何もしない。
				if ($isRealtime) self::registApbLog("W04", $device, $person);	// 退室中の人物が、再度退室デバイスで認証されました。
				return false;
			}
			
			// この人物について「退室」側に「通行禁止」を設定する。
			foreach ($outDevices as $d) {
				ApbService::registPersonAcessTimes($person["person_id"], $d["device_id"], $disablAccessTimes);				
				$disableDeviceIds[] = $d["device_id"];
			}
			
			// この人物について「入室」側に「通行許可」を設定する。
			foreach ($inDevices as $d) {
				ApbService::registPersonAcessTimes($person["person_id"], $d["device_id"], $enableAccessTimes);				
				$enableDeviceIds[] = $d["device_id"];
			}
			
			// 人物データの状況をUpdate。
			DB::update("update t_person set apb_in_flag = 0, update_time = now(), update_user_id = -2 where person_id = {value}", $person["person_id"]);
			
		} else {
			throw new Exception("不正なapb_type".print_r($device, 1));
		}
		
		// ログ登録。
		if ($isRealtime) {
			self::registApbLog("I02", $device, $person, null, $enableDeviceIds);		// クラウド上の通行可能時間帯設定を「通行許可」としました。
			self::registApbLog("I03", $device, $person, null, $disableDeviceIds);		// クラウド上の通行可能時間帯設定を「通行拒否」としました。
		} else {
			self::registApbLog("I06", $device, $person, null, $enableDeviceIds);		// クラウド上のデータ不整合修復として通行可能時間帯設定を「通行許可」としました。
			self::registApbLog("I07", $device, $person, null, $disableDeviceIds);		// クラウド上のデータ不整合修復として通行可能時間帯設定を「通行拒否」としました。
			
		}
	
		return true;
	}
	
	// 認証結果を各種端末に配信する。
	// 戻り値： 
	// 10: データ関連の問題により中断。
	// 20: 何も行う必要が無かった。
	// 30: 一件以上の通信エラーがあった。
	// 40: 全て正常に終了した。
	public static function recogDistribution($device, $person) {
		
		$deviceId = $device["device_id"];
	
		// ------------------------------------------------------------------------------------------------------------ 
		// ------------------------------------------------------------------------------------------------------------ 
		// ------------------------------------------------------- データの取得。
		// 契約者情報を取得する。
		$contractor = DB::selectRow("select * from m_contractor c where c.state = 30 and contractor_id = {value}", $device["contractor_id"]);
		if (empty($contractor)) {
			self::registApbLog("E01", $device); // 契約者情報が見つかりません
			return 10;
		}
		
		// デバイスのAPB種別が空の場合は何も行わない。
		if (empty($device["apb_type"])) {
			self::registApbLog("I01", $device);	// APB設定が行われたデバイスではありません。
			return 10;
		}

		// 認識したデバイスが入室専用デバイスの場合には、何も行わない
		if ($device["apb_type"] == 3) {
			self::registApbLog("I08", $device, $person);	// 入室専用デバイスでの認識のため、入退室状況は変更されません。
			return 20;
		}
		
		$personId = $person["person_id"];
		
		// 同一グループに属するデバイスを取得する。
		$inDevices  = self::getApbGroupDevices($device["device_id"], [1, 3]);	 // 入室用のデバイス 
		$outDevices = self::getApbGroupDevices($device["device_id"], [2]);	 // 退室用のデバイス
		
		// グループが未設定の場合には何も行わない。
		if (empty($inDevices) && empty($outDevices)) {
			self::registApbLog("W02", $device, $person);	// APBグループが設定されていないデバイスです。 
			return 10;
		}
		
		// ------------------------------------------------------------------------------------------------------------ 
		// ------------------------------------------------------------------------------------------------------------ 
		// ----------------------------------------------------------------------------------　通行可能時間帯情報を更新。 
		if (!self::registCloudAccessTime($inDevices, $outDevices, $device, $person, true)) {
			return 20;
		}
		
		// ------------------------------------------------------------------------------------------------------------ 
		// ------------------------------------------------------------------------------------------------------------ 
		// ----------------------------------------------------------------------------------　デバイスへデータを配信する。 
		$devices = [];
		
		// まず認識されたデバイスの自分自身を先に処理する。
		$devices[] = $device;
		
		// 次に、認識デバイスが入室側⇒退室側を優先、認識デバイスが退室側⇒入室側を優先、とする。
		if ($device["apb_type"] == 1) {
			foreach ($outDevices as $d) {
				if ($d["device_id"] == $device["device_id"]) continue;
				$devices[] = $d;
			}
			foreach ($inDevices as $d) {
				if ($d["device_id"] == $device["device_id"]) continue;
				$devices[] = $d;
			}
			
		} else {
			foreach ($inDevices as $d) {
				if ($d["device_id"] == $device["device_id"]) continue;
				$devices[] = $d;
			}
			foreach ($outDevices as $d) {
				if ($d["device_id"] == $device["device_id"]) continue;
				$devices[] = $d;
			}
		}

		
		$successDeviceIds = [];
		$errored = false;	// 一件でもエラーが発生している場合にtrue。
		
		foreach ($devices as $relDevice) {
			set_time_limit(90);	// 実行可能時間を延長。
			
			$error = false;
			$repairTarget = false;
			
			try {
				
				// リトライ回数を変更。
				WsApiService::$tryCount = APB_WS_API_TRY_COUNT;
				
				$existsParam = ["device_id"=>$relDevice["device_id"], "person_id"=>$person["person_id"]];
				if (DB::exists("select 1 from t_device_person where device_id = {device_id} and person_id = {person_id}", $existsParam)) {
					
					// デバイスへ。
					PersonService::toDevice($contractor, $relDevice, $person["person_code"], 1, 1);
					
					// 成功
					$successDeviceIds[] = $relDevice["device_id"];
						
				}

				
				continue;
				
			} catch (ApiParameterException $e) {
				$error = join(" / " , Errors::getMessagesArray());
				$repairTarget = false;	// パラメータエラーは修復対象とはしない。				
				
			} catch (DeviceExclusionException $e) {
				$error = $e->getMessage();
				$repairTarget = true;	// 排他エラーは修復対象とする。				
				
			} catch (SystemException $e) {
				$error = Errors::getMessagesArray();
				$repairTarget = true;	// システムエラーは修復対象とする。
				
			} catch (DeviceWsException $e) {
				$error = $e->getMessage();
				$repairTarget = true;	// デバイス接続エラーは修復対象とする。
				
			} finally {
				// 同期ログで未保存のデータがあるであればupdate。 
				SyncService::updateEndLog(SyncService::$processingRegisted ? 40 : 30);
				
			}
		
			$errored = true;
			
			// toDeviceに失敗したデータは修復対象とする。
			if ($repairTarget) {
				ApbService::registRepairTarget($relDevice, $person["person_id"], null);
			}
			
			// エラーログ。
			self::registApbLog("E02", $device, $person, $error, [$relDevice["device_id"]]);		// デバイスへ通行許可情報を送信する事が出来ませんでした
		}
		
		if (!empty($successDeviceIds)) {
			// 	成功ログ。
			self::registApbLog("I04", $device, $person, null, $successDeviceIds);			// デバイスへの通行許可情報の送信が正常に終了しました
		}
		
		
		if ($errored) {
			return 30;
		} else {
			return 40;
		}
	}
	
	
	// グループ設定を取得する。
	public static function getApbGroups($contractor_id) {
		
		$sql = "
			select 
				apb_group_id
				, apb_group_name
			from
				t_apb_group
			where
				contractor_id = {value}
			order by 
				sort_order
				, apb_group_id
		";
		
		$list = DB::selectKeyRow($sql, $contractor_id, "apb_group_id");
		
		$apb_group_ids = [];
		foreach ($list as $idx=>$group) {
			$apb_group_ids[] = $group["apb_group_id"];
			$list[$idx]["deviceList"] = [];
			$list[$idx]["deviceIds"] = [];
		}
		
		// 属するデバイスの情報を取得する。
		$sql = "
			select
				dd.apb_group_id
				, d.device_id
				, d.serial_no
				, d.description
				
			from
				m_device d
				
				inner join t_apb_group_device dd on
				d.device_id = dd.device_id
				and dd.apb_group_id in {in apb_group_ids}
				
			where
				d.contract_state = 10
				and d.contractor_id = {contractor_id}
				
			order by
				d.sort_order
				, d.device_id
		";
		
		$devices = DB::selectArray($sql, ["contractor_id"=>$contractor_id, "apb_group_ids"=>$apb_group_ids]);
		
		foreach ($devices as $device) {
			$groupId = $device["apb_group_id"];
			unset($device["apb_group_id"]);
			$list[$groupId]["deviceList"][] = $device;
			$list[$groupId]["deviceIds"][] = $device["device_id"];
		}
		
		return $list;
	}

	// 通行許可設定を登録する。
	public static function registPersonAcessTimes($personId, $deviceId, $accessTimes) {
		
		// 仕組み上、デッドロックが発生する恐れがある。発生した場合にはもう一度行う。
		DB::commit();
		DB::begin();
		
		$loop = 0;
		while (true) {
			if ($loop++ > 10) trigger_error("無限ループ防止　registPersonAcessTimes()");
			try {
		
				// 既存データ削除。
				$sql = "delete from t_person_access_time where person_id = {person_id} and device_id = {device_id}";
				DB::delete($sql, ["person_id"=>$personId, "device_id"=>$deviceId]);
				
						
				// 登録。
				foreach ($accessTimes as $time) {
					$param = [
						"person_id"		=> $personId
						, "device_id"   => $deviceId
						, "access_flag"	=> $time["accessFlag"]
						, "time_from"	=> $time["accessTimeFrom"]
						, "time_to"		=> $time["accessTimeTo"]
					];
					
					// 期間重複が発生する場合にはロールバックしてエラーとする。
					$exist = DB::exists("
						select 
							1 
						from 
							t_person_access_time 
						where 
							person_id = {person_id}
							and device_id = {device_id}
							and (
								({time_from} <= time_from and time_from <= {time_to})
								or ({time_from} <= time_to and time_to <= {time_to})
								or (time_from <= {time_from} and {time_to} <= time_to)
							)
					", $param);
					
					if ($exist) {
						DB::rollbackAll();
						throw new ApiParameterException("accessTime", "期間が重複するデータが含まれています。{$param["time_from"]}～{$param["time_to"]}");
					}
					
					// 登録。
					DB::insert("
						insert into 
							t_person_access_time
						set
							person_id		 = {person_id}
							, device_id      = {device_id}
							, create_time	 = now()
							, create_user_id = -1
							, update_time    = now()
							, update_user_id = -1
							, access_flag    = {flag access_flag}
							, time_from      = {time_from}
							, time_to        = {time_to}
					", $param);
				}
				
				DB::commit();
				DB::begin();
				break;
				
			} catch (Exception $e) {
				if (exists($e->getMessage(), "Deadlock")) {
					warnLog("[Deadlock] registPersonAcessTimes");
					DB::rollback();
					DB::begin();
					sleep(1);
					continue;
				}
				throw $e;
			}
			
		}
				
				
	}
	
	
	// personIdから通行許可設定を取得する。
	public static function getPersonAcessTimes($personId, $deviceId = null) {

		$ret = [];
		
		$appendWhere = "";
		if (!empty($deviceId)) {
			$appendWhere .= " and pt.device_id = {device_id} ";
		}
		
		$sql = "
			select 
				pt.device_id
				, pt.person_id
				, pt.access_flag
				, pt.time_from
				, pt.time_to
				, d.serial_no

			from 
				t_person_access_time pt
    			inner join m_device d on
				pt.device_id = d.device_id

			where 
				pt.person_id = {person_id}
				$appendWhere 

			order by 
				pt.time_from
			";
		
		$param = ["person_id"=>$personId, "device_id"=>$deviceId];
				
		foreach (DB::selectArray($sql, $param) as $item) {
			
			$data = [
				"accessFlag"	   => (int) $item["access_flag"]
				, "accessTimeFrom" => formatDate($item["time_from"], "Y/m/d H:i:s")
				, "accessTimeTo"   => formatDate($item["time_to"], "Y/m/d H:i:s")
			];
			
			if (empty($deviceId)) {
				if (!isset($ret[$item["serial_no"]])) {
					$ret[$item["serial_no"]] = [];
				}
				
				$ret[$item["serial_no"]][] = $data;
					
			} else {
				$ret[] = $data;
				
			}

		}
				
		return $ret;
		
	}
	
	// ログを検索。
	public static function getApbLogList($pageInfo, $contractor_id, $data, $devices) {

		// 認識カメラ・APB連携先カメラの中のどちらか１台も選択されていない場合、空の配列を返却
		if (empty($data["device_ids"]) || (empty($data["include_no_trans"]) && empty($data["trans_device_ids"]))) return [];
		
		$data["contractor_id"] = $contractor_id;
		
		$where = "a.device_id in {in device_ids}
		and ( exists (
			select 1
				from
					t_apb_log_device ad
				where
					ad.apb_log_id = a.apb_log_id
					and ad.trans_device_id in {in trans_device_ids}
		)";
		if ($data["include_no_trans"] == 1) {
			$where .= "or not exists (
			select 1
			from
				t_apb_log_device ad
			where
				ad.apb_log_id = a.apb_log_id
			)";
		}
		$where .= ")";
		
		if ($data["date_from"])	{
			$where .= " and a.create_time >= {date_from}";
		}
		if ($data["date_to"]) {
			$data["date_to"] = addDate($data["date_to"], "+1 day");
			$where .= " and a.create_time < {date_to}";
		}
		if ($data["log_type"]) {
			$where .= " and a.log_type = {log_type} ";
		}
		if ($data["log_level"]) {
			$where .= " and a.log_type like {like_R log_level} ";
		}
		if (($data["person_code"] === "0") || $data["person_code"]) {
			$where .= " and a.person_code like {like_LR person_code} ";
		}
		
		$sql = "
			select 
				a.apb_log_id
				, a.create_time
				, a.log_type
				, a.device_id
				, a.person_code
				, p.person_name
				, a.message
				, (select group_concat(ad.trans_device_id) from t_apb_log_device ad where ad.apb_log_id  = a.apb_log_id) as trans_device_ids

			from
				t_apb_log a 
				
				left outer join t_person p on 
				p.contractor_id   = {contractor_id}
				and p.person_code = a.person_code

			where
				$where
		
		"; 
		$order = "
			order by
				apb_log_id desc
		";
		
		$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);
		
		return $list;
	}
	
	// 通信に失敗した処理の修復を行う。
	public static function repairAll($devices) {

		// キーをdevice_idにした全デバイス情報。
		$allDevicesById = [];
		foreach ($devices as $serialNo=>$device) {
			$allDevicesById[$device["device_id"]] = $device; 
		}
		
		// 修復を実行。
		$ret = [];
		foreach ($devices as $serialNo=>$device) {
			$result = self::repair($allDevicesById, $device);
			
			$ret[$serialNo] = $result;
		}
		
		return $ret;
	}
	
	// 通信に失敗した処理の修復を行う。
	public static function repair($allDevicesById, $device) {
		
		$allDevicesByIds = array_keys($allDevicesById);
		
		$contractor = DB::selectRow("select * from m_contractor where contractor_id = {contractor_id}", $device);
		
		// 同一グループに属するデバイスを取得する。
		$inDevices  = self::getApbGroupDevices($device["device_id"], [1, 3]);	 // 入室用のデバイス 
		$outDevices = self::getApbGroupDevices($device["device_id"], [2]);	 	// 退室用のデバイス
		
		// グループが未設定の場合には何も行わない。
		if (empty($inDevices) && empty($outDevices)) {
			self::registApbLog("W05", $device);	// データ修復を実行しようとしましたが、APBグループが設定されていないデバイスです。 
			return;
		}
		
		// 戻り値
		$apbRepaireSuccess = [];
		$apbRepaireError = [];
		
		// 削除用SQL
		$deleteRepairSql = "delete from t_apb_repair where device_id = {device_id} and person_id = {person_id}";
		
		// 対象を検索。リトライ数の少ないものから優先する
		$limit = 100; 	// 1回あたりの最大実行数は100件までとする。
		$targets = DB::selectArray("select * from t_apb_repair where device_id = {device_id} order by repair_try_count limit $limit", $device);
		
		foreach ($targets as $target) {
			
			infoLog("target: ".json_encode($target, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
			
			set_time_limit(90);	// 実行可能時間を延長。
			
			// ユーザを取得。
			$person = DB::selectRow("select * from t_person where person_id = {person_id}", ["person_id"=>$target["person_id"]]);
			
			if (empty($person)) {
				warnLog("人物該当無し。".json_encode($target, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
				DB::delete($deleteRepairSql, $target);	// レコード削除
				continue;
			}

			// この人物の最終認識ログより、最も最後にPASS認識されたデバイスを取得し、現在「入室中」なのか「退室中」なのかを判定し、
			// クラウド側DBに通行可能時間帯設定を登録する。
			// この際、入室専用デバイス(apb_type = 3)は含めない。
			$param = ["device_ids"=>$allDevicesByIds, "person_code"=>$person["person_code"]];
			$lastDeviceId = DB::selectOne("
				select 
					log.device_id 
				from 
					t_recog_log log
				where 
					log.device_id in {in device_ids} 
					and log.person_code = {person_code}
					and log.pass = 1
					and exists(
						select 1 
						from
							m_device d
						where
							d.device_id = log.device_id
							and d.apb_type in(1, 2)  
					)
 
				order by 
					log.recog_time desc 

				limit 1

			", $param);
			
			if (empty($lastDeviceId)) {	// 本来あり得ないはず。
				errorLog("該当デバイスID無し。".json_encode($param, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
				DB::delete($deleteRepairSql, $target);	// レコード削除
				continue;
			}
			
			// クラウド側DBに通行可能時間帯設定を登録
			$lastRecogDevice = $allDevicesById[$lastDeviceId];
			self::registCloudAccessTime($inDevices, $outDevices, $lastRecogDevice, $person, false);	  // 反映漏れを起こしているデバイス 
			
			// 最新のデータをデバイスへ送信する。
			$repairTarget = false;
			try {
				
				$existsParam = ["device_id"=>$device["device_id"], "person_id"=>$person["person_id"]];
				if (DB::exists("select 1 from t_device_person where device_id = {device_id} and person_id = {person_id}", $existsParam)) {
				
					// デバイスへ。
					PersonService::toDevice($contractor, $device, $person["person_code"], 1, 1);
					
					// 成功ログ
					self::registApbLog("I05", $device, $person);		// デバイス側データの不整合の修復を行いました
				
				}
				
				// レコード削除
				DB::delete($deleteRepairSql, $target);
				
				$apbRepaireSuccess[] = $person["person_code"];
				
				// 処理を切り上げ。
				continue;
				
			} catch (ApiParameterException $e) {
				$error = join(" / " , Errors::getMessagesArray());
				$repairTarget = false;	// パラメータエラーは修復対象とはしない。				
				
			} catch (DeviceExclusionException $e) {
				$error = $e->getMessage();
				$repairTarget = true;	// 排他エラーは修復対象とする。				
				
			} catch (SystemException $e) {
				$error = Errors::getMessagesArray();
				$repairTarget = true;	// システムエラーは修復対象とする。
				
			} catch (DeviceWsException $e) {
				$error = $e->getMessage();
				$repairTarget = true;	// デバイス接続エラーは修復対象とする。
			
			} finally {
				// 同期ログで未保存のデータがあるであればupdate。 
				SyncService::updateEndLog(SyncService::$processingRegisted ? 40 : 30);
				DB::commitAll();
				DB::begin();
			}
			
			$apbRepaireError[] = $person["person_code"];
			
			if ($repairTarget) {
				// 修復対象エラーは試行回数をカウントアップし、引き続き修復対象とする。
				$target["repair_try_count"]++;
				
				self::registApbLog("E03", $device, $person, $error);		// デバイス側データの不整合の修復に失敗しました
				
				if ($target["repair_try_count"] >= self::MAX_REPAIR_TRY_COUNT) {
					
					// エラーログ。
					self::registApbLog("E04", $device, $person);				// デバイス側データの不整合修復の試行上限に到達しました
					
					// 最大試行回数を超過。これ以上は行わない（削除する。）
					DB::delete($deleteRepairSql, $target);
					
					continue;
				} 
				
				// 次回へ持ちす。
				DB::update("update t_apb_repair set update_time = now(), repair_try_count = repair_try_count + 1 where device_id = {device_id} and person_id = {person_id}", $target);	
				
			} else {
				// 修復不能エラーはレコード削除
				DB::delete($deleteRepairSql, $target);
				
			}
			
			// エラーログ。
			self::registApbLog("E03", $device, $person, $error, [$device["device_id"]]);		// デバイス側データの不整合の修復に失敗しました
						
			
		}
		
		
		return ["success"=>$apbRepaireSuccess, "error"=>$apbRepaireError];
	}
	
	
}
