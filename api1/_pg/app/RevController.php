<?php 

class RevController extends BaseController {
	
	private $device;
	
	
	// @Override
	public function init() {
		parent::init();

		setLogger("rev");
		
		define("CONTROLLER_TYPE", "rev");
		
		// ---------------------------------- DB
		DB::init(DB_DRIVER, DB_HOST, DB_USER, DB_PASS, DB_NAME);	// ここではまだDBに接続される訳では無い。初回利用時に接続される。
		
		// 全ての操作を記録する。
		$log = getDetailAccessLog();
		infoLog($log);
	}
	
	// @Override
	public function prepare(&$form) {
		
		if ($this->action == "pushRecvTestAction") return;

    // シグナルはDB接続前に返却検証
    if ($this->action == "signalAction") return;
		
		// --------------- HTTPリバース向けの認証 類似する処理がWebSocket側にも実装されている。 --------------- 
		Validators::setErroredFunction(null);
		$data = Validators::set($form)
					->at("SerialNo")->required()->maxlength(100)
					->getValidatedData();
		
		if (Errors::isErrored()) {
			warnLog(json_encode(Errors::getMessagesArray(), JSON_UNESCAPED_UNICODE));
			response400();
		}
		
		$this->device = DB::selectRow("
				select 
					device_id, device_allow_ip, device_token
				from 
					m_device d 
				where 
					serial_no = {value} 
					and contract_state = 10
					and exists(
						select 1
						from
							m_contractor c
						where
							c.contractor_id = d.contractor_id
							and c.state in (20, 30) 
					) 
				", $data["SerialNo"]);
		
		if (empty($this->device)) {
			warnLog("SerialNoに合致する有効データ無し。[".$data["SerialNo"]."]");
			response400();
		}
		
		// IP制限を確認。
		if (!empty($this->device["device_allow_ip"])) {
			$addr = getRemoteAddr();
			
			$allow = false;
			foreach (explode(",", $this->device["device_allow_ip"]) as $ip) {
				if ($ip == $addr) {
					$allow = true;
					break;
				}
			}
			
			if (!$allow) {
				warnLog("許可されていないIP。[".$addr."] ".json_encode($this->device, JSON_UNESCAPED_UNICODE));
				response400();
			}
		}
		
		// トークンを確認。
		if (empty($this->device["device_token"])) {
			warnLog("トークン未登録 ".json_encode($this->device, JSON_UNESCAPED_UNICODE));
			response400();
		}
		
		// トークンを検証。
		if ($this->urlParams["deviceToken"] != $this->device["device_token"]) {
			warnLog("トークン不一致 urlParam[".$this->urlParams["device_token"]."]".json_encode($this->device, JSON_UNESCAPED_UNICODE));
			response400();
		}
			
		
	}
	
	// HTTPリバース：シグナル
	public function signalAction(&$form) {
		
		// // 最終アクセス時刻を更新。
		// $data = Filters::set($form)->at("SerialNo")->len(100)->getFilteredData();
		// DB::update("update m_device set last_push_access = now() where serial_no = {value}", $data["SerialNo"]);
		
	}
	
	// HTTPリバース：画像プッシュ
	public function pushAction(&$form) {
		
		$data = Filters::set($form)
			->at("SerialNo")->len(100)
			->at("json")->len(1024 * 1024)			// 1MB
			->at("picture")->len(1024 * 1024 * 5)	// 5MB
			->getFilteredData();
		
		// 最終アクセス時刻を更新。
		DB::update("update m_device set last_push_access = now() where serial_no = {value}", $data["SerialNo"]);
		
		// ログに登録。
		$serialNo = $data["SerialNo"];
		$json     = json_decode($data["json"], true);
		$picture  = $data["picture"];
		$device   = DB::selectRow("select * from m_device where serial_no = {value} and contract_state = 10", $serialNo);
		
		// ファームウェアアップデート時のPUSHをはじく
		if (!empty($json['Code']) && $json['Code'] === "Upgrade") exit;

		// mod-start founder feihan
		$pushFlag = false;
		$item = [];
		$registLogResult = [];
		$contracter = DB::selectRow("select * from m_contractor where contractor_id = {value}", $device["contractor_id"]);
		// ▼デバイスからの顔画像登録時・変更時
		if(!empty($json["Method"])&&($json["Method"]=="faceInfoUpdate.addFace"||$json["Method"]=="faceInfoUpdate.updateFaceImage")){
			//「devconf_push_flag」が「1」もしくは「2」以外の場合、顔登録時・変更時のPushは転送できます。
			if($contracter["devconf_push_flag"] == '1' || $contracter["devconf_push_flag"] == '2'){
				$pushFlag = true;
				$item = $json;
				$item["serialNumber"] = $serialNo;
			}
		}else{
			// 顔画像認証時、ログに登録。
			$registLogResult = RecogLogService::registRecogLogByPush($device, $json, $picture);
			if (empty($registLogResult)) return;
			
			// 以降は外部通信処理などが多いため、ここで一度コミットする。
			DB::commitAll();
			DB::begin();
			
			//「devconf_push_flag」が「null」「empty」もしくは「2」以外の場合、顔認証時のPushは転送できます。
			if(empty($contracter["devconf_push_flag"]) || $contracter["devconf_push_flag"] != '2'){
				$pushFlag = true;
				$recog_log_id = $registLogResult["recog_log_id"];
				if(!empty($recog_log_id)){
					$log = DB::selectRow("select * from t_recog_log where recog_log_id = {value}", $recog_log_id);
					
					// 画像用のURLパラメータ作成。
					$pictureParam = null;
					if ($log["s3_object_path"]) {
						$pictureParam = AwsService::createS3SignedUrlParameter($device["s3_path_prefix"]."/recog".$log["s3_object_path"]);
					}
					
					$item = RecogLogService::convertApiFormat($device, $log, $pictureParam);
				}
			}
		}
		
		// ----------------------------------------------------------------------------------------------
		// ----------------------------------------------------------------------------------------------
		// pushFlag:OK場合には、通知の送信を行う。
		if ($pushFlag && !empty($device["push_url"])) {

			// 転送処理
			$curl = curl_init($device["push_url"]);
			curl_setopt($curl,CURLOPT_HEADER, true);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl,CURLOPT_FAILONERROR, true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($curl,CURLOPT_POSTFIELDS, $item);
			curl_setopt($curl,CURLOPT_TIMEOUT_MS, 10000);
			$response  = curl_exec($curl);
			$curlError = curl_error($curl);
			$status    = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$body      = substr($response, curl_getinfo($curl, CURLINFO_HEADER_SIZE));

			// ボディの文字数は30文字までとする
			if (mb_strlen($body) > 30) {
				$body = mb_substr($body, 0, 30)."...";
			}

			// ログ出力
			if (!empty($curlError)) {
				infoLog('転送先['.$device['push_url'].'],転送内容['.json_encode($item).'],レスポンスコード['.$status.'],エラー['.$curlError.']');
			} else {
				infoLog('転送先['.$device['push_url'].'],転送内容['.json_encode($item).'],レスポンスコード['.$status.'],ボディ['.$body.']');
			}

		}
		// ----------------------------------------------------------------------------------------------
		// ----------------------------------------------------------------------------------------------
		// ------------------------------------------- プログラムの呼び出しが指定されている場合には、プログラムを呼び出す。
		if (!empty($device["rev_call"]) && !empty($registLogResult)) {
			eval($device["rev_call"]);
		}
		
		// ----------------------------------------------------------------------------------------------
		// ----------------------------------------------------------------------------------------------
		// ------------------------------------------- UIのリアルタイムモニター機能向けに通知用のWebSocketAPIを呼び出す。
		if (!empty($device["device_id"]) && !empty($registLogResult)) {
 			$timeout = 10;
			$client = new HttpClient("", "text", $timeout);
			$client->get(WS_API_NOTICE_URL."?deviceId=".$device["device_id"]);
		}

		// ----------------------------------------------------------------------------------------------
		// ----------------------------------------------------------------------------------------------
		// ------------------------------------------- プリンタAPIの実行。
		if (!empty($device["device_id"]) && !empty($registLogResult)) {
			$recogData = $registLogResult["data"];
			if ($recogData["pass_result"] == 1) RecogLogService::execPrinterAPI($device, $recogData);
		}

		// ----------------------------------------------------------------------------------------------
		// ----------------------------------------------------------------------------------------------
		// ------------------------------------------- UIからアラートメール送信設定が行われている場合には、メール送信を行う。
		if (!empty($device["device_id"]) && !empty($registLogResult)) {
			
			$data 		    = $registLogResult["data"];
			$noPassFlag 	= $data["pass_result"] == 1 ? false : true;			// PASSされなかったらtrue。
			$guestFlag  	= empty($data["person_code"]) ? true : false;			// ゲスト（登録者では無い）だったらtrue。
			$tempAlertFlag  = $data["temperature_alarm"] == 2 ? true : false;		// 温度異常だったらtrue
			if (isset($data["mask"])) {
				$maskAlertFlag  = $data["mask"] != 1 ? true : false;					// マスク未着用だったらtrue
			} else {
				$maskAlertFlag  = false;
			}
			
			if ($noPassFlag || $guestFlag || $tempAlertFlag || $maskAlertFlag) {
				
				$flagWhere = " and (1 = 0  ";
				if ($noPassFlag)    $flagWhere .= "or nopass_flag = 1 ";
				if ($guestFlag)     $flagWhere .= "or guest_flag = 1 ";
				if ($tempAlertFlag) $flagWhere .= "or temp_flag = 1 ";
				if ($maskAlertFlag) $flagWhere .= "or mask_flag = 1 ";
				$flagWhere .= ") ";
				
				// アラートメール設定を取得。
				$sql = "
					select 
						* 
					from 
						t_alert a 
					where 
						a.contractor_id   = {contractor_id}
						and a.enable_flag = 1
						{$flagWhere}
						and exists(
							select 1 
							from 
								t_alert_device ad 
							where 
								ad.alert_id = a.alert_id 
								and ad.device_id = {device_id} 
						) 
				";
				$list = DB::selectArray($sql, ["contractor_id"=>$device["contractor_id"], "device_id"=>$device["device_id"]]);
			
				// 送信を行う。
				foreach ($list as $item) {
					$body = $item["mail_body"];
					$body = str_replace("【シリアルNo】"	, $device["serial_no"]	, $body);
					$body = str_replace("【カメラ名称】"	, $device["description"], $body);
					$body = str_replace("【認識日時】"		, $data["recog_time"]	, $body);
					$body = str_replace("【PASS結果】"		, $noPassFlag ? "NO PASS" : "PASS", $body);
					$body = str_replace("【温度判定結果】"	, $tempAlertFlag ? "NG" : "OK", $body);
					$body = str_replace("【温度測定結果】"	, (empty($data["temperature"]) ? "":  $data["temperature"]."°"), $body);
					$body = str_replace("【マスク判定結果】", $maskAlertFlag ? "NG" : "OK", $body);
					$body = str_replace("【登録者判定結果】", $guestFlag 	 ? "ゲスト" : "登録者", $body);
					$body = str_replace("【ユーザーID】"	, $data["person_code"], $body);
					$body = str_replace("【ユーザー氏名】"	, $data["person_name"], $body);
					
					$to = [];
					if ($item["mail_1"]) $to[] = $item["mail_1"];  
					if ($item["mail_2"]) $to[] = $item["mail_2"];  
					if ($item["mail_3"]) $to[] = $item["mail_3"];  
					
					execSendMailPlain($to, "facet Cloud", "noreply@fc2-cloud.com", $item["mail_subject"], $body);
				}
				
			}
			
			// ----------------------------------------------------------------------------------------------
			// ----------------------------------------------------------------------------------------------
			// APB設定が行われているデバイスの場合には他デバイスに対して入退室情報を連携する。
			if (ApbService::isActive($device)) {
				
				$person = ApbService::getPersonByRecogLogId($device, $recog_log_id);
				if (!empty($person)) {
					
					if ($noPassFlag) {
						ApbService::registApbLog("W06", $device, $person);	 // 通行が許可されませんでした。
						
					} else {
						ApbService::recogDistribution($device, $person);
						
					}
					
					
				}
					
			}
			
		}
		
		
	}
	
	
	// 契約者への通知機能のテスト用。
	public function pushRecvTestAction(&$form) {
		// no code	
	}
		
	
}
