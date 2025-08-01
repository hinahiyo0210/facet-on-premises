<?php 

class WsApiService {

	/** @var $client HttpClient */
	private static $client;
	
	// アクセスを行ったデバイス。
	public static $accessSerialNo = false;
	
	// 最後にアクセスが成功した日時。
	public static $lastAccessTime = false;
	
	// 最大通信試行回数(デフォルトから変更する場合)
	public static $tryCount = false;
	
	private static function createClient($ws_api_url) {
	
//		if (!empty(WsApiService::$client)) return WsApiService::$client;
		
		WsApiService::$client = new HttpClient($ws_api_url, "json");
		
		return WsApiService::$client;
	}
	
// 	// 接続中のデバイスのシリアルNoを取得する。
// 	public static function getSerialNos() {

//  		$client = WsApiService::createClient();

//  		$serialNos = $client->get("/serialNos");
 		
//  		return $serialNos;
// 	}

	// IDを作成。
	public static function genId() {
		
		return mt_rand(1, 2147483640);
	}
	
	
	// WSサーバにリクエストを送信。
	public static function accessWsApi(array $device, $param, $timeoutMs = WS_API_TIMEOUT_MS, $tryCount = WS_API_TRY_COUNT) {
		// 通信をタスク化する。
		// 最大で $tryCount 回の通信を行う。
		// 3回の場合、優先順位としては、WS1, WS2, WS3, WS1(2回目), WS1(3回目)となる。
		$settingUrls = [];
		foreach (explode(",", $device["ws_api_url"]) as $url) {
			$url = trim($url);
			if (empty($url)) continue;
			$settingUrls[] = $url;
		}
		
		if (self::$tryCount !== false) {
			$tryCount = self::$tryCount;
		}
		
		$tasks = [];
		$settingUrlsIdx = 0;
		for ($i = 0; $i < $tryCount; $i++) {
			if (count($settingUrls) - 1 < $settingUrlsIdx) $settingUrlsIdx = 0;
			$tasks[] = $settingUrls[$settingUrlsIdx];
			$settingUrlsIdx++;
		}
		
		$serialNo = $device["serial_no"];
		
		$catchErrors = [];
		
		// 繰り返し。
		$lastUrl = "";
		
		foreach ($tasks as $tryCount=>$url) {

			if ($tryCount != 0) {
				infoLog("[WS_INFO10] リトライします。{$device["device_id"]} {$device["serial_no"]}");
			}
					
			if ($lastUrl == $url) {
				// 前回と同じURLへのアクセス（リトライ）の場合は少し待機する。
				sleep(2);
			}
			
			$lastUrl = $url;
			
				
			$client = WsApiService::createClient($url);

			try {
				$json;
				if (is_string($param)) {
					$json = $param;
				} else {
					$json = json_encode($param);
				}
				
				$postData = [
		 			"serialNo"=>$serialNo
		 			, "json"=>$json
		 			, "timeoutMs"=>$timeoutMs
		 		];
				$client->timeout = floor($timeoutMs / 1000) + 2;	// WS側の処理タイムアウト+2秒が通信タイムアウト。
				$client->triggerError = false;
				
				$client->errorCallback = function($status) {
		
		 			// それ以外のエラー。（HttpClient側のタイムアウトや、WSサーバが停止していた場合など。）
		 			errorLog("[WS_ERR10] WS HTTPエラー ".json_encode($status, JSON_UNESCAPED_UNICODE));
			 		throw new DeviceWsException("server", "WSサーバとの通信が正常に行われませんでした。");
				};
				
				// 送信
		 		$ret = $client->post("/ws", $postData);
		 		
		 		if (!empty($ret["wserror"])) {
		 			if ($ret["wserror"] == "SESSION_ID_NOT_FOUND") {
		 				
		 				// デバイスが接続されていない。
			 			warnLog("[WS_ERR20] WS API エラー デバイス未接続  \npostData: ".json_encode($postData, JSON_UNESCAPED_UNICODE)."\nret: ".json_encode($ret, JSON_UNESCAPED_UNICODE));
			 			throw new DeviceWsException("デバイス[{$serialNo}]がクラウドサーバに接続されていません。");
		 			}
		 			
		 			if ($ret["wserror"] == "WAIT_CLOSED") {
		 				
		 				// 送信後、切断された。
			 			warnLog("[WS_ERR21] WS API エラー デバイス送信後待機中に切断  \npostData: ".json_encode($postData, JSON_UNESCAPED_UNICODE)."\nret: ".json_encode($ret, JSON_UNESCAPED_UNICODE));
			 			throw new DeviceWsException("デバイス[{$serialNo}]にデータを送信中に切断されました。", false);	// デバイスの内部では処理が成功している恐れがあるので、リトライは行わない
		 			}
		 			
		 			if ($ret["wserror"] == "ID_COLLISION") {
		 				
		 				// ID衝突
			 			warnLog("[WS_ERR31] WS API エラー デバイス内でのID衝突 \npostData: ".json_encode($postData, JSON_UNESCAPED_UNICODE)."\nret: ".json_encode($ret, JSON_UNESCAPED_UNICODE));
			 			throw new DeviceWsException("デバイス[{$serialNo}]との通信が正常には行われませんでした。");
		 			}
		 			
		 			
		 			// それ以外のエラー。
		 			errorLog("[WS_ERR22] WS APIエラー \npostData: ".json_encode($postData, JSON_UNESCAPED_UNICODE)."\nret: ".json_encode($ret, JSON_UNESCAPED_UNICODE));
			 		throw new DeviceWsException("デバイス[{$serialNo}]との通信が正常に行われませんでした。");
		 		}

				// empty分岐で画像処理のスキップ
				if (!empty($ret['method'])) {
					// CaptureModeの戻り値が特殊のため加工
					if (is_string($ret['method'])) {
						if ($ret['method'] == "videoAnalyse.getEnterFaceImage") return $ret;
					}
	
					// checkSimilarityInDeviceのエラーハンドリング
					if ($ret["method"] == "faceRecognize.recognize" && !$ret['result']) {
						switch ($ret["error"]["code"]) {
							case 131072:
								$ret['message'] = "デバイスの中にチェックする人物が存在しません。";
								return $ret;
								break;
							case 589841:
								$ret['message'] = "チェック画像に顔が存在しません。";
								return $ret;
								break;
							case 589842:
								$ret['message'] = "チェック画像の画質が低すぎます。";
								return $ret;
								break;
							case 589849:
								$ret['message'] = "チェック画像に顔が複数存在します。";
								return $ret;
								break;
							default:
								break;
						}
					}
	
					// getPersonPictureの該当人物がいなかった場合のエラーハンドリング
					if ($ret["method"] == "faceInfoFind.getFaceInfoById" && !empty($ret["error"]["code"])) {
						if ($ret["error"]["code"] == 589838) return;
					}

					// AIカメラの人物登録時のレスポンスの場合そのまま返す
					if ($ret["method"] == "faceInfoUpdate.wsAddFace") return $ret;
					// AIカメラの人物取得でいない場合もそのまま返す
					if ($ret["method"] == "faceInfoFind.getQueryResult") return $ret;

				}
		 		
		 		if (empty($ret["result"])) {
		 			// それ以外のエラー。
		 			errorLog("[WS_ERR23] WS APIエラー \npostData: ".json_encode($postData, JSON_UNESCAPED_UNICODE)."\nret: ".json_encode($ret, JSON_UNESCAPED_UNICODE));
			 		throw new DeviceWsException("デバイス[{$serialNo}]がエラーを返却しています。", false);
		 		}
		 		
			} catch (DeviceWsException $e) {
				// 通信系エラーの場合はリトライへ。
				if ($e->isConnectError) {
					$catchErrors[] = $e;
					continue;
				}
				
				// デバイスへ処理が到達しており、デバイス側が明確にエラーを返却した場合はリトライは行わない。
				throw $e;
			}
			
	 		// 正常にアクセスされたシリアルNoと時刻を保持。
			WsApiService::$accessSerialNo = $serialNo;
			WsApiService::$lastAccessTime = time();

			// 正常終了。
			return $ret;
		}
		
		// ---------------------- ここに至る場合は、試行回数を超過している。
		
		// エラーメッセージを集約してthrow。
		$throwMessages = [];
		foreach ($catchErrors as $e) {
			$throwMessages[$e->getMessage()] = 1;
		}

		// 通常はあり得ないルート。
		if (empty($throwMessages)) throw new DeviceWsException("想定外のエラーです。");

		// エラーを投げる。
		throw new DeviceWsException(join(" / ", array_keys($throwMessages))." 試行回数：".($tryCount + 1)."回");
	}


// 	public static function getFacePicture($serialNo, $recordId) {

// 		$param = '{"method":"faceInfoFind.getFace","params":{"FaceToken":"f9eedb4f3467e2bbbaa37b5da426b181"},"session":"4a43630690e0010672d7b0e0ee0e526b","id":"'.WsApiService::genId().'"}';
// 		WsApiService::createClient();
// 		$beforeFormat = WsApiService::$client->format;
// 		WsApiService::$client->format = "text";
//  		$picRet = WsApiService::accessWsApi($serialNo, $param);
 		
//  		$ret = "";
// 		if (!empty($picRet)) {
//  			$picRet = json_decode($picRet, true);
// 			if (!empty($picRet["data"])) {
// 				$ret = $picRet["data"];
//  			}
//  		}
 		
// 		WsApiService::$client->format = $beforeFormat;
		
//  		return $ret;
// 	}
	
	
}