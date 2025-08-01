<?php

class UiPersonService {
	
	// アップロードされたExcelを読み込む。
	private static function readExcel($contractor, $excelPath) {
		
		require_once(DIR_LIB."/Psr/SimpleCache/CacheInterface.php");
		require_once(DIR_LIB."/Psr/SimpleCache/CacheException.php");
		require_once(DIR_LIB."/Psr/SimpleCache/InvalidArgumentException.php");
		spl_autoload_register(function ($class_name) {
			$preg_match = preg_match('/^PhpOffice\\\PhpSpreadsheet\\\/', $class_name);
		
			if (1 === $preg_match) {
				$class_name = preg_replace('/\\\/', '/', $class_name);
				$class_name = preg_replace('/^PhpOffice\\/PhpSpreadsheet\\//', '', $class_name);
				require_once(DIR_LIB . '/PhpSpreadsheet/' . $class_name . '.php');
			}
		});
	
		try {
			$reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$reader->setReadDataOnly(true);
			$book = $reader->load($excelPath);
			$sheet = $book->getSheet(0); // 読み込むシートを指定
			
			$ret = [];
			$version = -1;
			foreach ($sheet->getRowIterator() as $i=>$row) {
				// 1行目をチェック。
		    	if ($i == 1) {
					$header_D = $sheet->getCell("D".$row->getRowIndex())->getValue();
					if ($header_D === "顔写真ファイル名") {
						$version = 0;
					} elseif ($header_D === "カード番号１") {
						$version = 1;
					}
					continue;
				};
				
		    	$item = [];
		    	$item["personCode"] = $sheet->getCell("A".$row->getRowIndex())->getValue();
		    	$item["personName"] = $sheet->getCell("B".$row->getRowIndex())->getValue();
		    	$item["birthday"]   = $sheet->getCell("C".$row->getRowIndex())->getValue();
							
				if ($version === 0) {
					$item["fileName"] = $sheet->getCell("D".$row->getRowIndex())->getValue();
					
				} elseif ($version === 1) {
					$item["cardID"]   = [];
					$item["dateFrom"] = [];
					$item["dateTo"] = [];
					// card1
					$item["cardID"][] = $sheet->getCell("D".$row->getRowIndex())->getValue();
					$validityPeriod   = preg_split("/-/", $sheet->getCell("E".$row->getRowIndex())->getValue());
					$item["dateFrom"][] = $validityPeriod[0] ?? "";
					$item["dateTo"][] = $validityPeriod[1] ?? "";
					unset($validityPeriod);
					// card2
					$item["cardID"][] = $sheet->getCell("F".$row->getRowIndex())->getValue();
					$validityPeriod   = preg_split("/-/", $sheet->getCell("G".$row->getRowIndex())->getValue());
					$item["dateFrom"][] = $validityPeriod[0] ?? "";
					$item["dateTo"][] = $validityPeriod[1] ?? "";
					unset($validityPeriod);
					// card3
					$item["cardID"][] = $sheet->getCell("H".$row->getRowIndex())->getValue();
					$validityPeriod   = preg_split("/-/", $sheet->getCell("I".$row->getRowIndex())->getValue());
					$item["dateFrom"][] = $validityPeriod[0] ?? "";
					$item["dateTo"][] = $validityPeriod[1] ?? "";
					unset($validityPeriod);
					// add-start version3.0  founder feihan
					if($contractor["enter_exit_mode_flag"] == 1){
						// enter_exit contents
						$item["person_type_code"] = $sheet->getCell("J".$row->getRowIndex())->getValue();
						$item["person_description1"] = $sheet->getCell("K".$row->getRowIndex())->getValue();
						$item["person_description2"] = $sheet->getCell("L".$row->getRowIndex())->getValue();
						// fileName
						$item["fileName"] = $sheet->getCell("M".$row->getRowIndex())->getValue();
					} else {
						// fileName
						$item["fileName"] = $sheet->getCell("J".$row->getRowIndex())->getValue();
					}
					// add-end version3.0  founder feihan
				}
				
				$ret[] = $item;
			}
		
			$book->disconnectWorksheets();

			return $ret;
			
		} catch (PhpOffice\PhpSpreadsheet\Exception $e) {
			warnLog("Excelファイル読み込みエラー");
			warnLog("code=".$e->getCode()."\nmessage=".$e->getMessage()."\nfile=".$e->getFile()."(".$e->getLine().")\n".$e->getTraceAsString());
			return false;
		}
		
	}
	
	// アップロードされたCsvを読み込む。
	private static function readCsv($csvPath) {
		
		$fp = fopen($csvPath, "r");
		$ret = [];
		$i = -1;
		while (($row = fgetcsv_reg($fp, 1000, ",")) !== false) {
			$i++;
			if ($i == 0) continue;	// 1行目はヘッダ。

			$item = [];
	    	$item["personCode"] = $row[0];
	    	$item["personName"] = $row[3];
			$item["birthday"]   = $row[5];
			$item["cardID"] = [];
			$item["cardID"][]   = $row[7];
			$validityPeriod   = preg_split("/-/", $row[8]);
			$item["dateFrom"] = [];
			$item["dateFrom"][] = $validityPeriod[0] ?? "";
			$item["dateTo"] = [];
			$item["dateTo"][] = $validityPeriod[1] ?? "";
	    	$item["fileName"]   = $row[13];
	    	$ret[] = $item;
		}
		
		fclose($fp);
		
		return $ret;
	}
	
	
	
	// 一括登録ファイルの中身をパースし、チェックを行う。
	public static function processBulkFile($contractor, $name, $user_id, $zipPath, $uploadProgress, $uploadProgressFile, $personTypes) {
		
		// zipを解凍
		$extractTmpDir = createTmpDir("bulk_extract");
		
		register_shutdown_function(function() use ($extractTmpDir) {
			deleteDirectory($extractTmpDir);
		});
		
		$zip = new ZipArchive();
		if (!$zip->open($zipPath)) {
			Errors::add($name, "zipファイルを開く事が出来ませんでした。");
			return false;
		}
		
	    // 進捗を出力。
	    $uploadProgress["processed"] = 5;
	    $uploadProgress["info"] = "アップロードファイルを処理しています。工程：1 / 3";
	    file_put_contents($uploadProgressFile, json_encode($uploadProgress));
		
	    infoLog("zipを解凍 $zipPath -> $extractTmpDir");
	    Globals::$errorDie = false;
	    if (!$zip->extractTo($extractTmpDir)) {
		    $zip->close();
			Errors::add($name, "zipファイルを解凍する事が出来ませんでした。");
			return false;
		}
	    Globals::$errorDie = true;
		$zip->close();
	 
		// 解凍後のディレクトリに、ファイルが存在せず、尚且つフォルダが一つだけ存在している場合には、その中を対象とする。
		$tmpDir = $extractTmpDir;
		if (count(getDirectoryFiles($extractTmpDir)) == 0) {
			
			$subDirs = getSubDirectories($extractTmpDir);
			if (count($subDirs) == 1) {
				$tmpDir = $extractTmpDir."/".$subDirs[0];
			}
		}
		
		// 進捗を出力。
	    $uploadProgress["processed"] = 10;
	    $uploadProgress["info"] = "アップロードファイルを処理しています。工程：2 / 3";
	    file_put_contents($uploadProgressFile, json_encode($uploadProgress));
	    
	    // ファイル名をチェック。
	    $allowExts = ["jpeg"=>1, "jpg"=>1, "xlsx"=>1, "csv"=>1];
	    $allowChars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ._-()%+";
	    $allowCharIdx = [];
	    foreach (str_split($allowChars) as $c) $allowCharIdx[$c] = 1;
	    
	    foreach (getDirectoryFiles($tmpDir) as $file) {
	    	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	    	if (!isset($allowExts[$ext])) {
	    		Errors::add($name, "不正なファイルが格納されています。ファイル名には日本語や記号類は使用しないで下さい。[".getSafeFileName($file)."]");
	    		return false;
	    	}
	    	
	    	foreach (preg_split('//u', $file, -1, PREG_SPLIT_NO_EMPTY) as $c) {
		    	if (!isset($allowCharIdx[$c])) {
		    		Errors::add($name, "不正なファイル名のファイルが格納されています。ファイル名には日本語や記号類は使用しないで下さい。[".getSafeFileName($file)."]");
		    		return false;
		    	}
	    	}
	    }
	    
	    // 進捗を出力。
	    $uploadProgress["processed"] = 15;
	    $uploadProgress["info"] = "アップロードファイルを処理しています。工程：3 / 3";
	    file_put_contents($uploadProgressFile, json_encode($uploadProgress));
	    
	    // データファイルを読み込む。
	    $list = [];
	    $existsExcelCount = 0;
        $existsCsvCount   = 0;
        $readFileName;
	    foreach (getDirectoryFiles($tmpDir) as $fileName) {
	    	if (!startsWith($fileName, "PersonnelInformation")) continue;
	    	$ext = pathinfo($fileName, PATHINFO_EXTENSION);
	    	if ($ext == "xlsx") {
	    		$existsExcelCount++;
	    		$readFileName = $fileName;
	    	}
	    	if ($ext == "csv") {
	    		$existsCsvCount++;
	    		$readFileName = $fileName;
	    	}
	    }
	    
	    if ($existsExcelCount + $existsCsvCount == 0) {
			Errors::add($name, "zipファイルの中にPersonnelInformation.xlsxもしくはPersonnelInformation.csvが存在していません。");
			return false;
	    }

    	if ($existsExcelCount + $existsCsvCount > 1) {
			Errors::add($name, "zipファイルの中にPersonnelInformation.xlsxもしくはPersonnelInformation.csvが複数存在しています。一ファイルのみを格納して下さい。");
			return false;
	    }
	    
	    if ($existsExcelCount) {
	    	// Excel
			$list = UiPersonService::readExcel($contractor, $tmpDir."/".$readFileName);
			if ($list === false) {
	    		Errors::add($name, "Excelファイルが正常に読み込まれませんでした。");
				return false;
			}
	    
	    } else {
	    	// Csv
			$list = UiPersonService::readCsv($tmpDir."/".$readFileName);
	    }
 	   
	    if (empty($list)) {
			Errors::add($name, "データファイルの中に一件もデータが入力されていません。");
			return false;
	    }
	    
	    // 進捗を出力。
	    $uploadProgress["processed"] = 25;
	    file_put_contents($uploadProgressFile, json_encode($uploadProgress));
	    
	    $dataCount = count($list);

      // オンプレの場合は登録数チェック
      if (!ENABLE_AWS) {
        $contractor_id = $contractor["contractor_id"];
        $personLimit = 10000;
        $personCount = DB::selectOne("SELECT COUNT(*) FROM t_person WHERE contractor_id = {$contractor_id}");
        if ($personCount + $dataCount > $personLimit) {
          Errors::add($name, "登録者は最大${personLimit}人までしか登録できません。");
          return false;
        }
      }

      // 制限件数が入っている場合はインポート件数を制限する
      if(!empty($contractor["output_csv_image_limit"])) {
        $inputLimit = $contractor["output_csv_image_limit"];
        if($inputLimit < $dataCount) {
          Errors::add($name, "入力件数が".$inputLimit."件を超えています。入力件数を減らし再度お試しください。");
          return false;
        }
      }
	    
	    // 入力チェック。
	    foreach ($list as $idx=>&$item) {
		    set_time_limit(60);
		    
		    // 進捗を出力。この処理は全体の50%の時間を要するものとする。
		    $progressRatio = floor($idx / $dataCount * 100);	// この処理の進捗率。
		    $uploadProgress["processed"] = 25 + floor($progressRatio / 2);
		    $uploadProgress["info"] = "データの確認を行っています。 ".($idx + 1)." / ".formatNumber($dataCount);
			file_put_contents($uploadProgressFile, json_encode($uploadProgress));
			
	    	if (count(Errors::getMessagesArray()) >= 10) break;	 // エラーが10件以上発生している場合は中断する。
	    	
	    	Errors::setPrefix("[".($idx + 2)."行目のデータ] ");
	    	
			Validators::set($item)
				->at("personCode", "ID"    			)->required()->maxlength(12)->half()
				->at("personName", "氏名"  	 		)->required()->maxlength(32)
				->at("birthday"  , "誕生日"			)->date()
				->at("cardID"	  , "カードID" )
				->arrayValue()->lineRequired("dateFrom")
				->arrayValue()->lineRequired("dateTo")->maxlength(100)->half()
				->at("dateFrom", "カード有効期間From" )
				->arrayValue()->date()
				->at("dateTo"  , "カード有効期間To"   )
				->arrayValue()->date()
				->compFuture("dateFrom", "カード有効期間From");
			
			// add-start version3.0  founder feihan
			if($contractor["enter_exit_mode_flag"] == 1){
				Validators::set($item)->at("person_type_code", "区分")->ifNotEquals("")->inArray(array_keys($personTypes))->ifEnd()
									->at("person_description1", "備考1")->maxlength(30)
									->at("person_description2", "備考2")->maxlength(30);
			}
			// add-end version3.0  founder feihan
			$fileName = $item["fileName"];
			$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
			if ($ext != "jpg" && $ext != "jpeg") {
				Errors::add("fileName", "ファイル名にjpeg(jpg)形式以外の名称が指定されています。");
				continue;
			}
			
			$filePath = $tmpDir."/".getSafeFileName($fileName);
			if (!file_exists($filePath)) {
				Errors::add("fileName", "ファイル [".getSafeFileName($fileName)."] がzipファイル内に存在していません。");
				continue;
			}
			
			$bin = file_get_contents($filePath);
			$base64picture = base64_encode($bin);
			
			if (strlen($base64picture) > 1024 * 1024  * 5) {// Base64後で5MB
				Errors::add("fileName", "ファイル [".getSafeFileName($fileName)."] のサイズが大きすぎます。");
				continue;
			}

			if (!isJpegImage($bin)) {
				Errors::add("fileName", "ファイル [".getSafeFileName($fileName)."] はjpeg形式のものを指定して下さい。");
				continue;
			}
		
			//ICカード番号チェック
			if (!Errors::isErrored("cardID")) {
				for($i=0;$i<3;$i++) {
					if (empty($item["cardID"][$i])) {
						continue;
					} else if (preg_match("/^[0-9]+$/", $item["cardID"][$i])) {
						if (strlen($item["cardID"][$i]) > 20) {
							Errors::add("cardID[$i]", "[ICカード番号".($i+1)."]20桁以下のカード番号(10進数)を指定してください。");
						}else if ("18446744073709551615" < $item["cardID"][$i]) { //TODO UINT_MAX  compare
							Errors::add("cardID[$i]", "[ICカード番号".($i+1)."]18446744073709551615以下のカード番号(10進数)を指定してください。");
						}
					} else if (preg_match("/^[a-fA-F0-9]+$/", $item["cardID"][$i])) {
						if (strlen($item["cardID"][$i]) > 16) {
							Errors::add("cardID[$i]", "[ICカード番号".($i+1)."]16桁以下のカード番号(16進数)を指定してください。");
						} else {
							// 入力文字内にa～fがあった場合は、自動でA～Fに変換してDBに登録
							$item["cardID"][$i] = mb_strtoupper($item["cardID"][$i], 'UTF-8');
						}
					} else {
						Errors::add("cardID[$i]", "[ICカード番号".($i+1)."]「0～9,A～F,a～f」の文字を入力してください。");
					}
				}
			}
			
			// デバイスを使ったチェック処理（これが最も時間がかかる）。
			try {
				if (ENABLE_CHECK_PICTURE) {
					$ret = PersonService::checkPersonPicture($base64picture);
					if (!$ret["result"]) {
						Errors::add("fileName", $ret["message"]);
					}
				}
				
			} catch (ApiParameterException $e) {
				// エラー内容はErrosに追加済み。
				continue;
				
			} catch (DeviceWsException $e) {
				Errors::add("", $e->getMessage());
				return false;
			}
			
	    }
		unset($item);
	    
	    Errors::setPrefix("");
		if (Errors::isErrored()) return false;
		
		// 登録処理。
	    foreach ($list as $idx=>$item) {
		    set_time_limit(60);
	    	
		    // 進捗を出力。この処理は全体の25%の時間を要するものとする。
		    $progressRatio = floor($idx / $dataCount * 100);	// この処理の進捗率。
		    $uploadProgress["processed"] = 75 + floor($progressRatio / 4);
		    $uploadProgress["info"] = "データの登録を行っています。 ".($idx + 1)." / ".formatNumber($dataCount);
			file_put_contents($uploadProgressFile, json_encode($uploadProgress));
		 
			// 登録。
	    	$filePath = $tmpDir."/".getSafeFileName($item["fileName"]);
	    	$bin = file_get_contents($filePath);
	    	$item["picture"] = base64_encode($bin);
	    	$item["user_id"] = $user_id;
	    	PersonService::registPerson($contractor, $item);
			
			// ICカード情報の登録。
			$person = PersonService::getPersonByCode($contractor, $item["personCode"]);	// 人物データを取得。
			
			$cardInfos = [];
			foreach ($item["cardID"] as $idx=>$cardID) {
				if (Validator::isEmpty($cardID)) continue;
				$cardInfos[] = [
					"cardID"		=> $cardID
					, "dateFrom"	=> $item["dateFrom"][$idx]
					, "dateTo"	=> $item["dateTo"][$idx]
				];
			}
			
			PersonService::registPersonCardInfo($person["person_id"], $cardInfos, $user_id);
	    }

		// 進捗状況ファイルを削除。
		unlink($uploadProgressFile);
	 
		return $dataCount;
	}
	
	// ダウンロード実施。
	public static function downloadPersons($contractor, $checkIds, $format, $downloadProgress, $downloadProgressFile, $devices, $groups) {
		
		$ext = ".csv";
		$createFileCallback = null;
		$dataWriteCallback = null;
		$closeFileCallback = null;
		if ($format == "excel") {

			// Excelの場合の拡張子。
			$ext = ".xlsx";
			
			// Excelの場合のファイル作成処理。
			$createFileCallback = function($dataPath) use($devices, $groups, $contractor) {
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
				// add-start version3.0  founder feihan
				if($contractor["enter_exit_mode_flag"] == 1 ) {
					$header["区分"] = "string";
					$desc_name1 = ($contractor["enter_exit_description_name1"]) ? $contractor["enter_exit_description_name1"] : "備考１";
					$desc_name2 = ($contractor["enter_exit_description_name2"]) ? $contractor["enter_exit_description_name2"] : "備考２";
					$header[$desc_name1] = "string";
					$header[$desc_name2] = "string";
				}
				// add-end version3.0  founder feihan= "string";
				$header["顔写真ファイル名"]	= "string";
				$header["登録日時"]	        = "YYYY/MM/DD hh:mm:ss";
				$header["グループ"]	        = "string";
				foreach ($groups as $g)  $header[$g["group_name"]." "] = "string";
				$header["カメラ"]	        = "string";
				foreach ($devices as $d) $header[$d["name"]." "] = "string";
				
				$writer = new XLSXWriter();
				
				$headerOptions = ['font-size' => '11', 'font'=>"Meiryo UI", "font-style"=>"bold"];
				$writer->writeSheetHeader('Sheet1', $header, $headerOptions);
				
				return $writer;
			};
			
			// Excelの場合のデータ書き込み処理。
			$dataWriteCallback =  function($context, $person, $imageFileName) use($devices, $groups, $contractor) {
				
				// 属するデバイスIDを取得。
				$deviceIds = DB::selectKeyValue("select device_id from t_device_person where person_id = {person_id}", $person, "device_id", "device_id");
				
				$row = [];
				$row[] = $person["person_code"];
				$row[] = $person["person_name"];
				$row[] = $person["birthday"];

				for ($i = 0; $i < 3; $i++) {
					if (!empty($person["card_id".($i+1)])) {
						$row[] = $person["card_id".($i+1)];
						$row[] = "{$person["time_from".($i+1)]}-{$person["time_to".($i+1)]}";
					} else {
						$row[] = "";
						$row[] = "";
					}
				}
				// add-start version3.0  founder feihan
				if($contractor["enter_exit_mode_flag"] ==1 ) {
					$row[] = $person["person_type_code"];
					$row[] = $person["person_description1"];
					$row[] = $person["person_description2"];
				}
				// add-end version3.0  founder feihan
				$row[] = $imageFileName;
				$row[] = $person["create_time"];
				
				
				// グループのデバイスID全てを満たすのであれば●となる。
				$row[] = "";
				if (empty($deviceIds)) {
					foreach ($groups as $g) $row[] = "";
				} else {
					foreach ($groups as $g) {
						if (empty($g["deviceIds"])) {
							$row[] = "";
							continue;
						}
						
						$and = true;
						foreach ($g["deviceIds"] as $id) {
							if (!isset($deviceIds[$id])) {
								$and = false;
								break;
							}
						}
						if ($and) {
							$row[] = "●";
						} else {
							$row[] = "";
						}
					}
				}
				
				// デバイスに紐付けがあれば●となる。
				$row[] = "";
				foreach ($devices as $device_id=>$d) {
					if (isset($deviceIds[$device_id])) {
						$row[] = "●";
					} else {
						$row[] = "";
					}
				}
				
				$writer = $context["fp"];
				$options = ['font-size' => '11', 'font'=>"Meiryo UI"];
				$writer->writeSheetRow('Sheet1', $row, $options);
			};
			
			// Excelの場合のファイルクローズ処理。
			$closeFileCallback = function($context) {
				$writer = $context["fp"];
				$writer->writeToFile($context["dataPath"]);
			};
			
		}
		
		// add-start founder feihan
		$shiftJisFlag = false;
		if(!empty($contractor["output_csv_format"]) && $contractor["output_csv_format"]=='1'){
			$shiftJisFlag = true;
		}
		// add-end founder feihan
		
		// 出力を開始。
		$context = SystemService::beginExportPersonFile($contractor, $ext, $createFileCallback, $shiftJisFlag);
		
		// 一件づつ出力。
		foreach ($checkIds as $id=>$dummy) {
		
			// 一件を処理（この中でset_time_limitが行われている）
			SystemService::appendExportPersonFile($context, $id, $dataWriteCallback, $shiftJisFlag);

			// 10件置きに進捗ファイルへ情報を出力する。
			$downloadProgress["processed"] += 3;		// S3のダウンロードに時間が掛かるため、66%をデータ部、33%をzip部とする。
			if ($downloadProgress["processed"] % 30 == 0) {
				file_put_contents($downloadProgressFile, json_encode($downloadProgress));
			}
		}

		// 終了とダウンロード。
		SystemService::endExportPersonFile($context, true, $closeFileCallback, function($fileName) use ($downloadProgressFile, &$downloadProgress) {

			// 20件置きに進捗ファイルへ情報を出力する。
			$downloadProgress["processed"]++;			// S3のダウンロードに時間が掛かるため、66%をデータ部、33%をzip部とする。
			if ($downloadProgress["processed"] % 20 == 0) {
				file_put_contents($downloadProgressFile, json_encode($downloadProgress));
			}
			
		}, function() use ($downloadProgressFile) {
			// 進捗状況ファイルを削除。
			unlink($downloadProgressFile);
		});
		
		
	}

	public static function downloadTemplate($contractor, $format) {
		SystemService::downloadTemplateFile($contractor, $format);
	}
	

	// 一件のみ取得。
	// 通行可能時間帯情報も取得する。
	public static function get($contractor, $devices, $groups, $personId) {
		
		$list = UiPersonService::getList($contractor, $devices, $groups, ["personId"=>$personId, "searchType"=>3], new PageInfo(1, 1));
		
		if (empty($list)) {
			return false;
		}
		
		$person = $list[0];
		
		// 通行可能時間帯を取得。
		$person["accessTimes"] = ApbService::getPersonAcessTimes($personId);
		
		// ICカード情報を取得。
		$person["cardInfo"] = PersonService::getPersonCardInfo($personId);
		
		return $person;
	}

	// 検索用のフィルタを取得。
	public static function getListSearchFilter(&$form, $prefix, $devices, $groups) {
		// 未登録 ＝ -1
		$groups['-1'] = true;
		return Filters::ref($form)
			->at("_form_session_key"	)->len(3)
			->at("enter_exit_mode_flag"	)->len(1)
			->at("{$prefix}searchType"   , 1)->values([1, 2])
			->at("{$prefix}personCode"		)->len(12)->narrow()
			->at("{$prefix}personName"		)->len(32)
			->at("{$prefix}person_description1")->len(30)
			->at("{$prefix}person_description2")->len(30)
			->at("{$prefix}cardID"		)->len(11)
			->at("{$prefix}birthday"		)->date()
			->at("{$prefix}person_type_code")->len(11)
			->at("{$prefix}group_ids"		)->enumArray($groups)
			->at("{$prefix}device_ids"		)->enumArray($devices)
			->at("{$prefix}noCam"			)->flag()
			->at("{$prefix}createDateFrom"	)->date()
			->at("{$prefix}createDateTo"	)->date()
			->at("{$prefix}search_init"		)->digit(1)
			->at("{$prefix}pageNo"		 , 1)->digit()
			->at("{$prefix}limit"		 , 20)->enum(Enums::pagerLimit());
			
	}

  // エラー時の返却パラメーターの整備
  public static function setDefaultSearchParams($sendForm, $receiveForm) {
    $sendForm["list_group_ids"] = $receiveForm["list_group_ids"];
    $sendForm["list_device_ids"] = $receiveForm["list_device_ids"];
    $sendForm["list_searchType"] = "1";
    $sendForm["trans_group_ids"] = $receiveForm["trans_group_ids"];
    $sendForm["trans_device_ids"] = $receiveForm["trans_device_ids"];
    $sendForm["trans_searchType"] = "1";

    return $sendForm;
  }
	
	// 検索。
	public static function getList($contractor, $devices, $groups, $data, $pageInfo, $idOnly = false) {
		
		$where = "";
		$data["contractor_id"] = $contractor["contractor_id"];
		
		if ($data["searchType"] == 1) {
			// 全件検索。
			
		} else if ($data["searchType"] == 2) {
			// 条件指定検索。
			if (($data["personCode"] === "0") || !empty($data["personCode"])) $where .= " and p.person_code like {like_LR personCode}";
			if (($data["personName"] === "0") || !empty($data["personName"])) $where .= " and p.person_name like {like_LR personName}";
			if (($data["cardID"] === "0") || !empty($data["cardID"]))         $where .= " and exists(select person_id from t_person_card_info tmp3 where tmp3.card_id like {like_LR cardID} and tmp3.person_id = p.person_id)";
			if (!empty($data["birthday"]))       $where .= " and p.birthday = {birthday}";
			// add-start version3.0  founder feihan
			if (!empty($data["person_type_code"])) $where .= "and p.person_type_code = {person_type_code}";
			if (($data["person_description1"] === "0") || !empty($data["person_description1"])) $where .= "and p.person_description1 like {like_LR person_description1}";
			if (($data["person_description2"] === "0") || !empty($data["person_description2"])) $where .= "and p.person_description2 like {like_LR person_description2}";
			// add-end version3.0  founder feihan
			if (!empty($data["createDateFrom"])) $where .= " and p.create_time >= {createDateFrom}";
			if (!empty($data["createDateTo"]))   $where .= " and p.create_time < {createDateTo} + interval 1 day";
	
			if (!empty($data["device_ids"]) || !empty($data["noCam"])) {
				$where .= " and ( 1 = 0 " ;
				if (!empty($data["device_ids"])) $where .= " or exists(select 1 from t_device_person dp0 where dp0.person_id = p.person_id and dp0.device_id in {in device_ids}) ";
				if (!empty($data["noCam"])) 	  $where .= " or not exists(select 1 from t_device_person dp0 where dp0.person_id = p.person_id) ";
				$where .= ") ";
			}else{
				// add-start founder feihan
				$where .= " and ( 1 = 0 )" ;
				// add-end founder feihan
			}
			
		} else if ($data["searchType"] == 3) {
			// ID(単一)検索。
			$where .= " and p.person_id = {personId}";
		
		} else {
			trigger_error("不正なsearchType");
			
		}
		
		
		$cols = "";
		
		if (!$idOnly) {
			$cols = "
				, p.create_time
				, p.person_code
				, p.person_name
				, tpci.card_ids
				, p.sex
				, p.birthday
				, p.person_type_code
				, p.person_description1
				, p.person_description2
				, mpt.person_type_name
				, p.memo
				, p.s3_object_path
				, group_concat(dp.device_id order by dp.device_id) as device_ids
				, apb_in_flag
			";
		}
		
		
		$sql = "
			select
				p.person_id
				$cols

			from
				t_person p

				left outer join t_device_person dp on
				p.person_id = dp.person_id

				left outer join (select group_concat(card_id SEPARATOR '/') as card_ids, person_id from t_person_card_info group by person_id) tpci
				on	p.person_id = tpci.person_id
			
			    left outer join m_person_type mpt on
			    p.person_type_code = mpt.person_type_code and mpt.contractor_id = {contractor_id}
			where
				p.contractor_id = {contractor_id}
				$where

			group by
				p.person_id
				, p.contractor_id
				, p.create_time
				, p.person_code
				, p.person_name
				, p.sex
				, p.birthday
			    , p.person_type_code
			    , p.person_description1
			    , p.person_description2
			    , mpt.person_type_name
				, p.memo
				, p.s3_object_path
		";
		$order = "
			order by
				p.person_id desc
		";
		
		$list = DB::selectPagerArray($pageInfo, $sql, $order, $data);
		
		if (!$idOnly) {
			
			
			foreach ($list as $idx=>$item) {
				
				// デバイス名とデバイスグループ名を設定。
				$device_names = [];
				$group_names = [];
				
				
				$deviceIds = (isset($item["device_ids"])) ? explode(",", $item["device_ids"]) : [];
				$deviceIdKeys = [];
				foreach ($deviceIds as $device_id) {
					if (empty($devices[$device_id])) continue;
					
					$device = $devices[$device_id];
					$device_names[] = $device["name"];
					$deviceIdKeys[$device_id] = 1;
				}
				

				foreach ($groups as $group_id=>$g) {
					if (empty($g["deviceIds"])) continue;
					
					$and = true;
					foreach ($g["deviceIds"] as $device_id) {
						if (!isset($deviceIdKeys[$device_id])) {
							$and = false;
							break;
						}
					}
					if ($and) {
						$group_names[] = $g["group_name"];
					}
				}

				$list[$idx]["device_names"] = join(" / ", $device_names);
				$list[$idx]["device_group_names"] = join(" / ", $group_names);
				
				// APIフォーマットに。
				$list[$idx] = PersonService::convertApiFormat($contractor, $list[$idx], false, false);
			}
			
		}
		
		return $list;
	}

	
	
	// 画像参照用のCookieを作成し、設定。
	public static function setCloudFrontCookie($contractor_id, $expiredTimeSec) {
		
		// if (ENABLE_AWS) {
		
			$pictureCookie = AwsService::createS3SignedCookie(zpad($contractor_id, 6)."/person/*", null, getRemoteAddr(), $expiredTimeSec);
		
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
	
	// add-start founder feihan
	// デバイスを取得する。(Ajax)
	public static function getDevicesByGroupIds($groupIds,$contractorId){
		$where = "";
		$t_device_group_device = "";
		if (!empty($groupIds["group_ids"])) {
			$groupIdsStr = $groupIds["group_ids"];
			$where = " and g.device_group_id in ($groupIdsStr) and g.device_id = d.device_id";
			$t_device_group_device = " , t_device_group_device g";
		}
		$sql= "
			select
				*

			from
				m_device d
				$t_device_group_device

			where
				d.contract_state = 10
				and d.contractor_id = {value}
				$where

			order by
				d.sort_order
				, d.device_id
		";
		$list = DB::selectKeyRow($sql, $contractorId, "device_id");
		foreach ($list as $idx=>$d) {
			if (!empty($d["description"])) {
				$list[$idx]["name"] = $d["description"];
			} else {
				$list[$idx]["name"] = $d["serial_no"];
			}
		}
		return $list;
	}
	// add-end founder feihan
}
