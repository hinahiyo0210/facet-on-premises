<?php 

class SystemService {
	

	// システム情報を取得
	public static function getSystemInfo(array $device) {
		
		$ret = WsApiService::accessWsApi($device, [
 			"method"=>"sysHelper.getSystemInfo"
 			, "id"=>WsApiService::genId()
 			, "params"=>null
 		]);
		
		if (empty($ret["params"]["deviceInfo"])) {
			throw new DeviceWsException("システム情報が正常に取得出来ませんでした。");
		}
		
		$p = $ret["params"];
		
		return [
			"deviceType"=>$p["deviceInfo"]["deviceType"],
			"serialNumber"=>$p["deviceInfo"]["serialNumber"],
			"softwareVersion"=>$p["softwareVersion"]["version"],
			"softwareBuildDate"=>$p["softwareVersion"]["buildDate"],
		];
	}
	
	// 再起動を要求。
	public static function reboot($device) {
		
		// 開始ログ
		SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_reboot");
		
		try {
			$ret = WsApiService::accessWsApi($device, [
	 			"method"=>"sysHelper.reboot"
	 			, "id"=>WsApiService::genId()
	 			, "params"=>null
	 		]);
		} catch (DeviceWsException $e) {
			if ($e->isConnectError) {
				throw $e;		// 通信異常の場合はthrow。
			}
			
			// sysHelper.rebootはresult:falseを返却するが、再起動は開始される。ので、無視。
		}
		
		// すぐに再起動が開始されるため、結果を知ることは出来ない。
		
		// 終了ログ
		SyncService::updateEndLog(20);
		
	}

	// ドアの強制開錠 2021.09.05追加
	public static function openOnce($device) {

		try{
		//infoLog(print_r($device,true));
		
			$ret = WsApiService::accessWsApi($device, [
	 			"method"=>"devDoor.openOnce"
	 			, "id"=>WsApiService::genId()
	 			, "params"=> [
             			       "channel"=>0
                 		]
	 		]);
		} catch( Exception $e) {
			
			return["result"=>false];
			
		}
             return ["result"=>$ret["result"]];

	}

	// 任意のメッセージ表示 2022年2月14日
	public static function displayMessage($device, $customTipParams) {

		infoLog("[displayMessage] device_id:".$device["device_id"]);

		$ret = WsApiService::accessWsApi($device, [
			 "method"=>"accessView.showPrompt"
			 , "id"=>WsApiService::genId()
			 , "params"=> $customTipParams
		 ]);
		 
		 return ["result"=>$ret["result"]];
	}


	// デバイススタンバイモード 2022年2月14日
	public static function setHibernateMode($device) {

		infoLog("[setHibernateMode] device_id:".$device["device_id"]);

		$ret = WsApiService::accessWsApi($device, [
			 "method"=>"commDS.resumeDormancy"
			 , "id"=>WsApiService::genId()
			 , "params"=> NULL
		 ]);
		 
		 return ["result"=>$ret["result"]];
	}

	// デバイスアクティブモード 2022年2月14日
	public static function setOperationMode($device) {

		infoLog("[setOperationMode] device_id:".$device["device_id"]);

		$ret = WsApiService::accessWsApi($device, [
			 "method"=>"commDS.pauseDormancy"
			 , "id"=>WsApiService::genId()
			 , "params"=> NULL
		 ]);
		 
		 return ["result"=>$ret["result"]];
	}

	// デバイスモード状況 2022年2月14日
	public static function getCurrentMode($device) {

		infoLog("[getCurrentMode] device_id:".$device["device_id"]);

		$ret = WsApiService::accessWsApi($device, [
			 "method"=>"commDS.getDormancyState"
			 , "id"=>WsApiService::genId()
			 , "params"=> NULL
		 ]);
		 
		 return [
			 "result"=>$ret["result"],
			 "state" =>$ret["params"]["State"]
			];
	}
	
	// ファームウェアアップデート
	public static function updateFirmware($device, $version) {
		
		$firmware = DB::selectRow("select * from m_firmware where version_name = {value}", $version);
		$url = $firmware["firmware_url"];
			
		// ファームウェアがS3に設置されている場合には署名を取得。
		if (!empty($firmware["s3_flag"])) {
			$expiresTimeSec = 60 * 60;	// 制限時間は1時間。
			$param = AwsService::createS3SignedUrlParameter("/firmware/*", $expiresTimeSec);
			if (exists($url, "?")) {
				$url .= "&".$param;
			} else {
				$url .= "?".$param;
			}
		}
		
		// 開始ログ
		SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_reboot");
		
		infoLog("ファームウェアアップデート　".$device["serial_no"]."  ".$url."  ".json_encode($firmware, JSON_UNESCAPED_UNICODE));
		
		$ret = WsApiService::accessWsApi($device, [
 			"method"=>"cloud.firmwareUpgrade"
 			, "id"=>WsApiService::genId()
 			, "params"=>[
 				"URL"=>$url,
 				"FirmwareSize"=>$firmware["firmware_size"],
				"HardwareVersion"=>"1.00" // TODO 何を指定する？？
 			]
 		]);
		
		infoLog($ret);
		
		// すぐに再起動が開始されるため、結果を知ることは出来ない。
		// 終了ログ
		SyncService::updateEndLog(20);
	}
	
	
	// -------------
	// 一括登録ファイルの作成を開始。（デバイスの標準管理画面からインポート可能なファイル）
	public static function beginExportPersonFile(array $contractor, $ext = ".csv", $createFileCallback = null, $shiftJisFlag = false) {

		$tmpDirPath = createTmpDir("export");
		$tmpZipPath = createTmpFile("", "export", ".zip");
		
		if (empty($createFileCallback)) {
			// mod-start founder feihan
			$createFileCallback = function($dataPath, $shiftJisFlag) {

				$fp = fopen($dataPath, "w");
				$content = "個人ID,IDタイプ,登録者タイプ,名前,性別,生年月日,権限グループ名,カード番号,カード番号有効期間,部門,国,県,市,顔写真1,特徴値1,顔写真2,特徴値2,顔写真3,特徴値3\n";
				if($shiftJisFlag){
					$content = mb_convert_encoding($content, "SJIS-win", "UTF-8");
				}else{
					fwrite($fp, pack('C*',0xEF,0xBB,0xBF)); // ExcelでUTF8を開くにはBOMが必要。
				}
				fwrite($fp, $content);
				return $fp;
			};
			// mod-end founder feihan
		}
		
		$dataBasename = "PersonnelInformation_".date("Y-m-d-His");
		$dataPath = $tmpDirPath."/".$dataBasename.$ext;
		
		$fp = $createFileCallback($dataPath, $shiftJisFlag);
		
		register_shutdown_function(function() use ($tmpDirPath, $tmpZipPath) {
 			deleteDirectory($tmpDirPath);
			unlink($tmpZipPath);
		});
		
		return ["contractor"=>$contractor, "tmpDirPath"=>$tmpDirPath, "fp"=>$fp, "dataBasename"=>$dataBasename, "tmpZipPath"=>$tmpZipPath, "dataPath"=>$dataPath];
	}
	
	// 一括登録ファイルに情報を追記。
	public static function appendExportPersonFile(array $context, $person_id, $dataWriteCallback, $shiftJisFlag) {
		
		set_time_limit(30);
		// mod-start version3.0  founder feihan
		$sql = "
		select
			p.*
		    ,mpt.person_type_name
		from
			t_person p
		    left join m_person_type mpt on
		    p.person_type_code = mpt.person_type_code
		where person_id = {value}
		";
		// mod-end version3.0  founder feihan
		
		$person = DB::selectRow($sql, $person_id);
		if (empty($person)) return;
	
		$sql = "
		select
			*
		from
			t_person_card_info
		where person_id = {value}
		order by person_card_info_id
		";
		$cardInfos = DB::selectArray($sql, $person_id);
		for ($i = 0; $i < 3; $i++) {
		
			$person["card_id".($i+1)] = (isset($cardInfos[$i])) ? arr($cardInfos[$i], "card_id") : "";
			$person["time_from".($i+1)] = (isset($cardInfos[$i])) ? formatDate(arr($cardInfos[$i], "time_from")) : "";
			$person["time_to".($i+1)] = (isset($cardInfos[$i])) ? formatDate(arr($cardInfos[$i], "time_to")) : "";
		}
		
		if (empty($person["sex"])) {
			$person["sex"] = "male";
		}

		$person["birthday"] = (empty($person["birthday"])) ? "" : formatDate($person["birthday"]);
		
		$imageFileName = getSafeFileName($person["person_code"])."_".getRandomPassword(10).".jpg";
		
		// データ出力。
		if (empty($dataWriteCallback)) {
			
			// mod-start founder feihan
			$dataWriteCallback = function($context, $person, $imageFileName, $shiftJisFlag) {
				
				// ダブルクオーテーションは利用出来ないので、せめて改行除去とカンマの大文字化のみは行う
				$rep = function($val) {
					$val = str_replace(",", "，", $val);
					$val = str_replace("\r", " ", $val);
					$val = str_replace("\n", " ", $val);
					return $val;
				};
				$personCode = $rep($person["person_code"]);
				$personName = $rep($person["person_name"]);
				$card_period = !empty($person["card_id1"]) ? "{$person["time_from1"]}-{$person["time_to1"]}" : "";
				
				$csvContext = "{$personCode},,1,{$personName},{$person["sex"]},{$person["birthday"]},undefined,{$person["card_id1"]},{$card_period},,,,,{$imageFileName},,,,,\n";
				if($shiftJisFlag){
					$csvContext = mb_convert_encoding($csvContext, "SJIS-win", "UTF-8");
				};
				fwrite($context["fp"], $csvContext);
			};
			// mod-end founder feihan
			
		}
		
		$dataWriteCallback($context, $person, $imageFileName, $shiftJisFlag);
		
		// 画像を取得して設置。
		$pictureBin = AwsService::downloadS3PersonPicture($context["contractor"], $person["s3_object_path"], $person["create_time"]);
		if (empty($pictureBin)) {
			throw new SystemException("picture", "クラウドサーバから画像データが取得出来ませんでした。");
		}
		
		if (!file_put_contents($context["tmpDirPath"]."/".$imageFileName, $pictureBin)) {
			throw new SystemException("picture", "ファイルを保存する事が出来ませんでした。");
		}
		
	}
	
	// 一括登録ファイルの作成を終了する。$downloadをtrueとするとダウンロードが行われる。
	public static function endExportPersonFile(array $context, $download = false, $closeFileCallback = null, $addFileCallback = null, $zipCloseCallack = null) {

		if (empty($closeFileCallback)) {
			$closeFileCallback = function($context) {
				$fp = $context["fp"];
				fclose($fp);
			};
		}
		
		$closeFileCallback($context);
		
		set_time_limit(30);
				
		// フォルダをzipにする。
		$zip = new ZipArchive();
		$zip->open($context["tmpZipPath"], ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$fileNames = getDirectoryFiles($context["tmpDirPath"]);
		
		set_time_limit(10 * count($fileNames));
		foreach ($fileNames as $fileName) {
			$zip->addFile($context["tmpDirPath"]."/".$fileName, $fileName);
			if (!empty($addFileCallback)) {
				$addFileCallback($fileName);
			}
		}
		$zip->close();
		
		if (!empty($zipCloseCallack)) $zipCloseCallack();
				
		// ダウンロード。
		if ($download) {
			set_time_limit(10 * count($fileNames));
			header('Content-Disposition: attachment; filename="'.$context["dataBasename"].".zip".'"');
			header('Content-Type: application/octet-stream');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.filesize($context["tmpZipPath"]));
//			readfile($context["tmpZipPath"]);

			// out of memoryエラーが出る場合に出力バッファリングを無効
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
			ob_start();
		
			// ファイル出力
			if ($file = fopen($context["tmpZipPath"], 'rb')) {
				while (!feof($file) and (connection_status() == 0)) {
					echo fread($file, '4096'); //指定したバイト数ずつ出力
					ob_flush();
				}
				ob_flush();
				fclose($file);
			}
			ob_end_clean();
			
		}
		
	}
	
	// -------------

	public static function downloadTemplateFile($contractor, $format) {

		$tmpDirPath = createTmpDir("export");
		$tmpZipPath = createTmpFile("", "export", ".zip");
		
		$dataBasename = "PersonnelInformation_sample_".$format;
		$ext = ($format == "excel") ? ".xlsx" : ".csv";
		$dataPath = $tmpDirPath."/".$dataBasename.$ext;

		register_shutdown_function(function() use ($tmpDirPath, $tmpZipPath) {
			deleteDirectory($tmpDirPath);
			unlink($tmpZipPath);
	   	});

		if ($format == "excel") {
			// ＝＝＝＝＝出力の開始＝＝＝＝＝ //
			require_once(DIR_LIB."/xlsxwriter.class.php");
			
			$header = array();
			$header["ID"] 				= "string";
			$header["氏名"] 			= "string";
			$header["生年月日"] 		= "string";
			$header["カード番号１"] 		= "string";
			$header["カード有効期間１"] 		= "string";
			$header["カード番号２"] 		= "string";
			$header["カード有効期間２"] 		= "string";
			$header["カード番号３"] 		= "string";
			$header["カード有効期間３"] 		= "string";
			if($contractor["enter_exit_mode_flag"] ==1 ) {
				$header["区分"] = "string";

				$desc_name1 = ($contractor["enter_exit_description_name1"]) ? $contractor["enter_exit_description_name1"] : "備考１";
				$desc_name2 = ($contractor["enter_exit_description_name2"]) ? $contractor["enter_exit_description_name2"] : "備考２";
				$header[$desc_name1] = "string";
				$header[$desc_name2] = "string";
			}
			$header["顔写真ファイル名"]	= "string";
			
			$writer = new XLSXWriter();
			
			$headerOptions = ['font-size' => '11', 'font'=>"Meiryo UI", "font-style"=>"bold"];
			$writer->writeSheetHeader('Sheet1', $header, $headerOptions);
			
			$fp = $writer;
	
			$context = ["contractor"=>$contractor, "tmpDirPath"=>$tmpDirPath, "fp"=>$fp, "dataBasename"=>$dataBasename, "tmpZipPath"=>$tmpZipPath, "dataPath"=>$dataPath];
	
			// ＝＝＝＝＝内容の書き込み開始＝＝＝＝＝ //
			$row = [];
			$row[] = "sample";
			$row[] = "サンプル　花子";
			$row[] = "1983/1/12";
			$row[] = 1111;
			$row[] = "2000/01/01-2037/12/31";
			$row[] = 2222;
			$row[] = "2000/01/01-2037/12/31";
			$row[] = 3333;
			$row[] = "2000/01/01-2037/12/31";
			if($contractor["enter_exit_mode_flag"] ==1 ) {
				$row[] = "";
				$row[] = "メモ１";
				$row[] = "メモ２";
			}
			$row[] = "sample.jpg";
			
			$writer = $context["fp"];
			$options = ['font-size' => '11', 'font'=>"Meiryo UI"];
			$writer->writeSheetRow('Sheet1', $row, $options);
	
			// ＝＝＝＝＝終了の処理＝＝＝＝＝ //
			$writer = $context["fp"];
			$writer->writeToFile($context["dataPath"]);
			
		} else {
			// ＝＝＝＝＝出力の開始＝＝＝＝＝ //
			$fp = fopen($dataPath, "w");
			$content = "個人ID,IDタイプ,登録者タイプ,名前,性別,生年月日,権限グループ名,カード番号,カード番号有効期間,部門,国,県,市,顔写真1,特徴値1,顔写真2,特徴値2,顔写真3,特徴値3\n";
			fwrite($fp, pack('C*',0xEF,0xBB,0xBF)); // ExcelでUTF8を開くにはBOMが必要。
			fwrite($fp, $content);
			
			$context = ["contractor"=>$contractor, "tmpDirPath"=>$tmpDirPath, "fp"=>$fp, "dataBasename"=>$dataBasename, "tmpZipPath"=>$tmpZipPath, "dataPath"=>$dataPath];

			// ＝＝＝＝＝内容の書き込み開始＝＝＝＝＝ //
			$csvContext = "sample,,1,サンプル　花子,female,1983/1/12,undefined,1111,2000/01/01-2037/12/31,,,,,sample.jpg,,,,,\n";
			fwrite($context["fp"], $csvContext);

			// ＝＝＝＝＝終了の処理＝＝＝＝＝ //
			$fp = $context["fp"];
			fclose($fp);
		}

		// 画像の設置
		$sampleImg = file_get_contents(DIR_APP."/person/sample.jpg");
		file_put_contents($context["tmpDirPath"]."/sample.jpg", $sampleImg);

		// フォルダをzipにする。
		$zip = new ZipArchive();
		$zip->open($context["tmpZipPath"], ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$fileNames = getDirectoryFiles($context["tmpDirPath"]);
		
		set_time_limit(10 * count($fileNames));
		foreach ($fileNames as $fileName) {
			$zip->addFile($context["tmpDirPath"]."/".$fileName, $fileName);
			if (!empty($addFileCallback)) {
				$addFileCallback($fileName);
			}
		}
		$zip->close();
		
		if (!empty($zipCloseCallack)) $zipCloseCallack();
				
		// ダウンロード。
		set_time_limit(10 * count($fileNames));
		header('Content-Disposition: attachment; filename="'.$context["dataBasename"].".zip".'"');
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($context["tmpZipPath"]));

		// out of memoryエラーが出る場合に出力バッファリングを無効
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		ob_start();
	
		// ファイル出力
		if ($file = fopen($context["tmpZipPath"], 'rb')) {
			while (!feof($file) and (connection_status() == 0)) {
				echo fread($file, '4096'); //指定したバイト数ずつ出力
				ob_flush();
			}
			ob_flush();
			fclose($file);
		}
		ob_end_clean();
	}
	
	
}
