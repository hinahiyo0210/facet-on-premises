<?php

class UiRecogLogService {
	
	// 認証方式Label
	public static function accessTypeNameMap($accessType, $cardType) {
		if ($accessType==="Face") {
			//Face	-(any)	顔認証
			return '顔認証';
		} elseif ($accessType==="Card") {
			//Card	1		ICカード
			//		6		QRコード
			//		null,	カード
			if ($cardType == 1 ) {
				return 'ICカード';
			} elseif ($cardType == 6) {
				return 'QRコード';
			} else {
				return 'カード';
			}
		} elseif ($accessType==="FaceAndCard") {
			//FaceAndCard	1			IC・顔認証
			//				6			QR・顔認証
			//				null,		カード・顔認証
			if ($cardType == 1 ) {
				return 'IC・顔認証';
			} elseif ($cardType == 6) {
				return 'QR・顔認証';
			} else {
				return 'カード・顔認証';
			}
		} elseif ($accessType==="FirstCardAndFace") {
			//FirstCardAndFace	1		先IC・顔認証
			//					6		先QR・顔認証
			//					null	先カード・顔認証
			if ($cardType == 1 ) {
				return '先IC・顔認証';
			} elseif ($cardType == 6) {
				return '先QR・顔認証';
			} else {
				return '先カード・顔認証';
			}
		}
		return "";
	}
	
	// ログを検索。
	public static function getRecogLogList($pageInfo, $data, $devices, $gropus=null, $csvFlag=false, $passFlags = false, $isInCondition = true) {

		// mod-start founder feihan
		$where = $data["device_ids"] ? " and r.device_id in {in device_ids}" : "and 1 = 0";
		$join = "";
		
		if ($data["date_from"])		$where .= " and r.recog_time >= {date_from}";
		if ($data["date_to"])		$where .= " and r.recog_time < {date_to} + interval 1 minute";

		// 詳細情報を含めないようにしている場合は以下条件をスキップ
		if ($isInCondition) {
			if (isset($data["personType"]) && $data["personType"])	$where .= " and r.person_type_code = {personType}";
			if (isset($data["personDescription1"]) && (($data["personDescription1"] === "0") || $data["personDescription1"])) {
				$where .= " and r.person_description1 like {like_LR personDescription1}";
			}
			if (isset($data["personDescription2"]) && (($data["personDescription2"] === "0") || $data["personDescription2"])) {
				$where .= " and r.person_description2 like {like_LR personDescription2}";
			}
			
			if (isset($data["presentFlag"]) && $data["presentFlag"] == 1) {
				// 検索時在館者フラグが１の場合、date_fromの後入室、かつ、date_toの前退室していないデータのみ抽出する
				// 「状態」以降の検索条件を無視
				$join = "inner join
					(
						select
							r.person_code as person_code, max(r.recog_time) as recog_time
						from
							t_recog_log r
						where
							r.recog_time <= now() + interval 1 minute
							and pass = 1
							$where
						group by r.person_code
					) r2
				on
					r.person_code = r2.person_code
					and r.recog_time = r2.recog_time
					and r.enter_exit_type_flag = 1
				";
			} else {
				if (($data["personCode"] === "0") || $data["personCode"]) $where .= " and r.person_code like {like_LR personCode}";
				if (($data["personName"] === "0") || $data["personName"]) $where .= " and r.person_name like {like_LR personName}";
				// add-start founder feihan
        if (isset($data["enterExitType"])) {
          if ($data["enterExitType"] == "1")	$where .= " and r.pass = 1 and r.enter_exit_type_flag = 1";
          if ($data["enterExitType"] == "2")	$where .= " and r.pass = 1 and r.enter_exit_type_flag = 2";
          if ($data["enterExitType"] == "9")	$where .= " and r.pass <> 1";
        }
				// add-end founder feihan
				if (($data["cardID"] === "0") || $data["cardID"]) $where .= " and r.card_no like {like_LR cardID}";
				if ($data["pass_type"] == "yes")	$where .= " and r.pass = 1";
				if ($data["pass_type"] == "no")		$where .= " and r.pass <> 1";
				if ($data["mask_type"] == "yes")	$where .= " and r.mask = 1";
				if ($data["mask_type"] == "no")		$where .= " and r.mask = 2";
				if ($data["guest_type"] == "yes")	$where .= " and r.person_code is null";
				if ($data["guest_type"] == "no")	$where .= " and r.person_code is not null";
				// add-start founder feihan
				if ($data["noTempOnly"]) {
					$where .= " and (r.temperature <= 0 or r.temperature is null)";
				} else {
					if ($data["temperature_from"])		$where .= " and r.temperature >= {temperature_from}";
					if ($data["temperature_to"])		$where .= " and r.temperature <= {temperature_to}";
				}
				if ($data["score_from"])			$where .= " and r.search_score >= {score_from}";
				if ($data["score_to"])				$where .= " and r.search_score <= {score_to}";
				if ($data["pass_flags"])			$where .= " and r.pass_flag in {in pass_flags}";
				// add-end founder feihan
			}
		}
		
		$sql = "
			select
				r.recog_log_id
				, r.device_recog_log_id
				, r.recog_time
				, r.person_code
				, r.person_name
				, r.access_type
				, r.card_no
				, r.card_type
				, r.mask
				, r.temperature
				, r.s3_object_path
				, r.pass
				, r.device_id
				, r.search_score
				, a.temperature_alarm
			    , tdgd.device_group_id
			    , r.detail
				, r.pass_flag
				, r.enter_exit_type_flag
				, r.person_type_code
				, r.person_description1
				, r.person_description2
			from
				t_recog_log r
				
				left outer join t_recog_analize a on
				r.device_id = a.device_id
				and r.recog_time = a.recog_time
			    left outer join t_device_group_device tdgd on
		        tdgd.device_id = r.device_id
				$join

			where
				r.recog_time <= now() + interval 1 minute
				$where
		";
		$order = "
			order by
				recog_time desc
				, recog_log_id desc
		";
		
		$rawList = DB::selectPagerArray($pageInfo, $sql, $order, $data);

		// 加工を行う。
		$list = [];
		foreach ($rawList as $item) {
			$device = $devices[$item["device_id"]];
			$item["deviceName"] = $device["name"];
			// add-start founder feihan
			if(!empty($gropus)){
				$group = isset($gropus[$item["device_group_id"]]) ? $gropus[$item["device_group_id"]] : [];
				$item["deviceGroupName"] = &$group["group_name"];
			}
			// 詳細
			if(empty($item["detail"])){
				$item["detail"]="";
			}else{
				$item["detail"]=str_replace(array("\r\n", "\r", "\n"), '',$item["detail"]);
			}
			// PASS
			if($item["pass"]=="1"){
				$item["passStr"] = "OK";
			}else{
				$item["passStr"] = "NG";
				// 詳細
				if($item["pass"]=="16"){
					$item["detail"]="未登録者";
				}elseif($item["pass"]=="5"){
					$item["detail"]="時間外のため開錠できません";
				}elseif($item["pass"]=="22"){
					$item["detail"]="マスクを確認できませんでした";
				}elseif($item["pass"]=="9"){
					$item["detail"]="ブラックリスト";
				}elseif($item["pass"]=="20"){
					$item["detail"]="温度異常";
				}
			}
            // add-end founder feihan
			
			// 勤怠区分情報
			if(!empty($passFlags)){
				$passFlag = $passFlags[$item["pass_flag"]];
				$item["flag_name"] = &$passFlag["flag_name"];
			}
			
			// mod-start founder feihan
			if(!$csvFlag){
				$item = RecogLogService::convertApiFormat($device, $item, false, false, true);
			}
			// mod-end founder feihan
			$list[] = $item;
		}
		
		return $list;
	}

	public static function getLogForMonitor($devices, $deviceId) {
		
		$sql = "
			select
				r.recog_time
				, r.person_code
				, r.person_name
				, r.access_type
				, r.card_no
				, r.card_type
				, r.mask
				, r.temperature
				, r.s3_object_path
				, r.pass
				, r.device_id
				, r.search_score
				, a.temperature_alarm
			  , r.detail
				, r.pass_flag
				, r.enter_exit_type_flag
				, r.person_type_code
				, r.person_description1
				, r.person_description2
			from
				t_recog_log r
			left outer join
        t_recog_analize a
      on
				r.device_id = a.device_id
			where
				r.device_id = $deviceId
      order by
        recog_time desc
      limit
        1
		";
		
		$raw = DB::selectRow($sql);

		// 加工を行う。
		$list = [];
		if (!empty($raw)) {
			$device = $devices[$raw["device_id"]];
			$raw["deviceName"] = $device["name"];
			// 詳細
			if(empty($raw["detail"])){
				$raw["detail"]="";
			}else{
				$raw["detail"]=str_replace(array("\r\n", "\r", "\n"), '',$raw["detail"]);
			}
			// PASS
			if($raw["pass"]=="1"){
				$raw["passStr"] = "OK";
			}else{
				$raw["passStr"] = "NG";
				// 詳細
				if($raw["pass"]=="16"){
					$raw["detail"]="未登録者";
				}elseif($raw["pass"]=="5"){
					$raw["detail"]="時間外のため開錠できません";
				}elseif($raw["pass"]=="22"){
					$raw["detail"]="マスクを確認できませんでした";
				}elseif($raw["pass"]=="9"){
					$raw["detail"]="ブラックリスト";
				}elseif($raw["pass"]=="20"){
					$raw["detail"]="温度異常";
				}
			}
			
			// 勤怠区分情報
			if(!empty($passFlags)){
				$passFlag = $passFlags[$raw["pass_flag"]];
				$raw["flag_name"] = &$passFlag["flag_name"];
			}
			
			$raw = RecogLogService::convertApiFormat($device, $raw, false, false, true);

			$list[] = $raw;
			
			return $list;

		} else {
			// 空の場合は空を返却
			return [];
		}

	}
	
	// 画像参照用のCookieを作成し、設定。
	public static function setCloudFrontCookie($contractor_id) {
		
		// if (ENABLE_AWS) {
		
			$pictureCookie = AwsService::createS3SignedCookie(zpad($contractor_id, 6)."/picture/*", null, getRemoteAddr());
		
			foreach ($pictureCookie as $k=>$v) {
				setcookie($k, $v, [
					"path" 		=> "/",
		            "domain" 	=> CLOUDFRONT_COOKIE_DOMAIN,
		            "secure" 	=> ENABLE_SSL,
		            "httponly" 	=> true,
		            "samesite" 	=> ENABLE_SSL ? "None" : ""
				]);
			}
		// }
		
	}
	
	
	// 古すぎる情報は削除する。
	public static function clearnToken($contractor_id) {
	
		// 制限時間による自動削除。
		DB::delete("delete from t_push_watch where create_time <= now() - interval 1 day");
		
		// 契約者IDごとに「作成しすぎ」の場合の自動削除。(100件以降の10件を取得)
		$tokens = DB::selectOneArray("
			select
				push_watch_token
			from
				t_push_watch
			where
				contractor_id = {value}
			order by
				create_time desc
			limit
				10
			offset
				100
		", $contractor_id);
		
		foreach ($tokens as $token) {
			DB::delete("delete from t_push_watch where push_watch_token = {value}", $token);
		}
		
	}
	
	// デバイスのログ更新の監視を開始するためのWebSocketアドレスを返却する。
	public static function beginMonitor($contractor_id, $user_id, $deviceIds) {
		
		// 古すぎる情報は削除する。
		UiRecogLogService::clearnToken($contractor_id);
		
		if (empty($deviceIds)) return "";
		
		// 新しいトークンを発行。
		$count = 0;
		do {
			$token = getRandomPassword(64);
			
			if ($count++ >= 100) {
				trigger_error("無限ループ防止 $count");
			}
			
		} while (DB::exists("select 1 from t_push_watch where push_watch_token = {value}", $token));
		
		DB::insert("
			insert into
				t_push_watch
			set
				push_watch_token = {push_watch_token}
				, create_time 	 = now()
				, contractor_id  = {contractor_id}
				, user_id 		 = {user_id}
				, device_ids	 = {device_ids}
				, remote_addr    = {remote_addr}
		", [
			"push_watch_token"	=> $token
			, "contractor_id" 	=> $contractor_id
			, "user_id" 		=> $user_id
			, "device_ids" 		=> join(",", $deviceIds)
			, "remote_addr"     => getRemoteAddr()
		]);
		
		return str_replace("[TOKEN]", $token, WS_ADDR);
	}

    // add-start founder feihan
    // ダウンロード実施
    public static function downloadLogs($searchForm, $downloadProgress, $downloadProgressFile, $devices, $gropus, $contractor, $recogPassFlags, $csvImgType){
        $ext = ".csv";
        $createFileCallback = null;
        $dataWriteCallback = null;
        $closeFileCallback = null;

        $shiftJisFlag = false;
        if(!empty($contractor["output_csv_format"]) && $contractor["output_csv_format"]=='1'){
            $shiftJisFlag = true;
        }

        // 出力を開始。
        $passFlagHeader =  empty($recogPassFlags) ? "" : ",勤怠区分";
        if ($contractor["enter_exit_mode_flag"] == 1) {
          $desc_name1 = ($contractor["enter_exit_description_name1"]) ? $contractor["enter_exit_description_name1"] : "備考１";
          $desc_name2 = ($contractor["enter_exit_description_name2"]) ? $contractor["enter_exit_description_name2"] : "備考２";
          $personDescriptionHeader = ",".$desc_name1.",".$desc_name2;
        } else {
          $personDescriptionHeader = "";
        }
        $csvImgHeader = $csvImgType == 1 ? ",顔写真" : "";
        $contextHeader = "日時,カメラグループ,カメラ,認証方式,判定,ID/ゲスト,名前,ICカード番号,温度,マスク,スコア".$personDescriptionHeader.$passFlagHeader.",詳細".$csvImgHeader;
        $context = UiRecogLogService::beginExportLogFile($contextHeader, $ext, $createFileCallback, $shiftJisFlag, $csvImgType);
        
        // 出力データは多すぎる場合は20000件毎に検索取得して。
        $count = $searchForm["csvCount"];
        $per = 20000;
        $section = array();
        for ($i = $per; $i <= $count; $i += $per) {
          $section[] = $i;
        }
        if (end($section) < $count) {
          $section[] = $count;
        }

        foreach ($section as $k => $v) {
            $pageInfo = new PageInfo($k + 1, $per);
            $recogLogIds = UiRecogLogService::getRecogLogList($pageInfo, $searchForm, $devices, $gropus, true);
            // 一件づつ出力。
            foreach ($recogLogIds as $id => $dummy) {
                $device = $devices[$dummy["device_id"]];
                // 一件を処理（この中でset_time_limitが行われている）
                UiRecogLogService::appendExportLogFile($device, $context, $dummy, $dataWriteCallback, $shiftJisFlag, $recogPassFlags, $csvImgType, $contractor);
                // 10件置きに進捗ファイルへ情報を出力する。
                $downloadProgress["processed"] += 3;
                if ($downloadProgress["processed"] % 30 == 0) {
                    file_put_contents($downloadProgressFile, json_encode($downloadProgress));
                }
            }
        }

        // 終了とダウンロード。
        UiRecogLogService::endExportLogFile($context, true, $csvImgType, $closeFileCallback, function($fileName) use ($downloadProgressFile, &$downloadProgress) {
            // 20件置きに進捗ファイルへ情報を出力する。
            $downloadProgress["processed"]++;
            if ($downloadProgress["processed"] % 20 == 0) {
                file_put_contents($downloadProgressFile, json_encode($downloadProgress));
            }

        }, function() use ($downloadProgressFile) {
            // 進捗状況ファイルを削除。
            unlink($downloadProgressFile);
        });
    }

    // CSVファイルの作成を開始
    public static function beginExportLogFile($content, $ext, $createFileCallback = null, $shiftJisFlag, $csvImgType) {

        $tmpDirPath = createTmpDir("export");
        $tmpPath = $csvImgType == 1 ? createTmpFile("", "export", ".zip") : createTmpFile("", "export", ".csv");

        if (empty($createFileCallback)) {
            $createFileCallback = function($dataPath, $shiftJisFlag) use ($content) {
                $fp = fopen($dataPath, "w");
				if($shiftJisFlag){
					$content = mb_convert_encoding($content, "SJIS-win", "UTF-8");
                }else {
					fwrite($fp, pack('C*', 0xEF, 0xBB, 0xBF)); // ExcelでUTF8を開くにはBOMが必要。
				}
				fwrite($fp,$content);
                return $fp;
            };
        }

        $dataBasename = "ninsyoLog_".date("Y-m-d-His");
        $dataPath = $tmpDirPath."/".$dataBasename.$ext;

        $fp = $createFileCallback($dataPath, $shiftJisFlag);

        register_shutdown_function(function() use ($tmpDirPath, $tmpPath) {
            deleteDirectory($tmpDirPath);
            unlink($tmpPath);
        });

        return ["tmpDirPath"=>$tmpDirPath, "fp"=>$fp, "dataBasename"=>$dataBasename, "tmpPath"=>$tmpPath, "dataPath"=>$csvImgType == 1 ? $tmpPath : $dataPath];
    }

    // CSVファイルに情報を追記。
    public static function appendExportLogFile(array $device, array $context, $recogLog, $dataWriteCallback = null, $shiftJisFlag, $recogPassFlags, $csvImgType, $contractor) {

      set_time_limit(30);

      if (empty($recogLog)) return;

      // 日時
      if (empty($recogLog["recog_time"])) {
          $recogLog["recog_time"] = "";
      }else{
          $recogLog["recog_time"] = formatDate($recogLog["recog_time"],"Y/m/d H:i:s");
      }
      // カメラグループ
      if (empty($recogLog["deviceGroupName"])) {
          $recogLog["deviceGroupName"] = "";
      }
      // カメラ
      if (empty($recogLog["deviceName"])) {
          $recogLog["deviceName"] = "";
      }
      // D/ゲスト 名前
      if(empty($recogLog["person_code"])){
          $recogLog["person_code"] = "";
          $recogLog["person_name"] = "";
      }else{
          if(isset($recogLog["person_type"]) && $recogLog["person_type"] != '0'){
						$recogLog["person_code"] = $recogLog["person_code"];
						$recogLog["person_name"] = $recogLog["person_name"];
          }else{
						$recogLog["person_code"] = "";
						$recogLog["person_name"] = "";
          }
      }
      // ICカード番号
      if(empty($recogLog["card_no"])){
        $recogLog["card_no"]="";
      }else{
        $recogLog["card_no"]= $recogLog["card_no"];
      }
      // 温度
      if(empty($recogLog["temperature"])){
          $recogLog["temperature"]="";
      }else{
          $recogLog["temperature"]=sprintf('%.1f', $recogLog["temperature"]);
      }
      // マスク
      if($recogLog["mask"]==1){
          $recogLog["mask"]="着用";
      }else if($recogLog["mask"]==2){
          $recogLog["mask"]="未着用";
      }
      // スコア
      if(empty($recogLog["search_score"])){
          $recogLog["search_score"]="";
      }else{
          $recogLog["search_score"]=sprintf('%.1f', $recogLog["search_score"]);
      }
      // 勤怠区分
      if(empty($recogPassFlags)||empty($recogLog["pass_flag"])){
        $recogLog["flag_name"]= "";
      }else{
        $recogLog["flag_name"]= $recogPassFlags[$recogLog["pass_flag"]]["flag_name"];
      }
		
      // 認証方式
      $recogLog["access_type_name"] = UiRecogLogService::accessTypeNameMap($recogLog["access_type"], $recogLog["card_type"]);

      //画像名称
      $imageFileName = "";
      // 画像あり時
      if($csvImgType == 1 && !empty($recogLog["s3_object_path"]) && !empty($device["save_recog_picture_flag"])){
        // 画像を取得して設置。
        $pictureBin = AwsService::downloadS3RecogPicture($device, $recogLog["recog_log_id"], $recogLog["recog_time"]);
        if(!empty($pictureBin)){
          //画像名称
          $index = strrpos($recogLog['s3_object_path'],'/');

          $imageFileName = substr($recogLog['s3_object_path'],$index+1);
          if (!file_put_contents($context["tmpDirPath"]."/".$imageFileName, $pictureBin)) {
            throw new SystemException("recogLogId", "ファイルを保存する事が出来ませんでした。");
          }
        }
      }
		
      // データ出力。
      if (empty($dataWriteCallback)) {
        $dataWriteCallback = function($context, $recogLog, $shiftJisFlag, $csvImgType, $imageFileName, $contractor) use ($recogPassFlags) {
          $passFlag = (empty($recogPassFlags) ? "" : ",").$recogLog["flag_name"];
          $personDescription = (($contractor["enter_exit_mode_flag"] == 1) ? ",".$recogLog["person_description1"].",".$recogLog["person_description2"] : "");
          // 画像あり時
          $imageFileName = $csvImgType == 1 ? ",{$imageFileName}" : "";
          $csvContext = str_replace(array("\r\n", "\r", "\n"), '', "{$recogLog["recog_time"]},{$recogLog["deviceGroupName"]},{$recogLog["deviceName"]},{$recogLog["access_type_name"]},{$recogLog["passStr"]},{$recogLog["person_code"]},{$recogLog["person_name"]},{$recogLog["card_no"]},{$recogLog["temperature"]},{$recogLog["mask"]},{$recogLog["search_score"]}{$personDescription}$passFlag,{$recogLog["detail"]}$imageFileName");
          if($shiftJisFlag){
              $csvContext = mb_convert_encoding($csvContext, "SJIS-win", "UTF-8");
          }
          fwrite($context["fp"],"\r\n".$csvContext);
        };
      }

      $dataWriteCallback($context, $recogLog, $shiftJisFlag, $csvImgType, $imageFileName, $contractor);
    }

    // CSVファイルの作成を終了する。$downloadをtrueとするとダウンロードが行われる。
    public static function endExportLogFile(array $context, $download = false, $csvImgType = null, $closeFileCallback = null, $addFileCallback = null, $zipCloseCallack = null) {

        if (empty($closeFileCallback)) {
            $closeFileCallback = function($context) {
                $fp = $context["fp"];
                fclose($fp);
            };
        }

        $closeFileCallback($context);

        set_time_limit(30);

        $fileNames = getDirectoryFiles($context["tmpDirPath"]);
        
		    // 画像あり時
        if($csvImgType == 1){
          // フォルダをzipにする。
          $zip = new ZipArchive();
          $zip->open($context["tmpPath"], ZipArchive::CREATE | ZipArchive::OVERWRITE);
      
          set_time_limit(10 * count($fileNames));
          foreach ($fileNames as $fileName) {
            $zip->addFile($context["tmpDirPath"]."/".$fileName, $fileName);
            if (!empty($addFileCallback)) {
              $addFileCallback($fileName);
            }
          }
          $zip->close();
      
          if (!empty($zipCloseCallack)) $zipCloseCallack();
        }

        // ダウンロード。
        if ($download) {
            set_time_limit(10 * count($fileNames));
			      $extName = $csvImgType == 1 ? ".zip":".csv";
            header('Content-Disposition: attachment; filename="'.$context["dataBasename"].$extName.'"');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '.filesize($context["dataPath"]));

            // out of memoryエラーが出る場合に出力バッファリングを無効
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();
            // ファイル出力
            if ($file = fopen($context["dataPath"], 'rb')) {
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
    // add-end founder feihan
	// 勤怠区分情報取得
	public static function getPassFlags($contractor_id){
		$sql = "
			select
				pass_flag
				, flag_name
			from
				m_recog_pass_flag
			where
				contractor_id = {value}
			order by
				pass_flag
		";
		
		return DB::selectKeyRow($sql, $contractor_id, "pass_flag");
	}
}