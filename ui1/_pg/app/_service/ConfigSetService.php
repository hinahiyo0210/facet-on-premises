<?php

class ConfigSetService {
	
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// -------------------------------------------------------------------- 共通
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// ファームウェアバージョンの一覧を取得する。
	public static function getFirmwareVersionNames() {
		
		return DB::selectKeyValue("select version_name, version_name from m_firmware order by create_time desc", [], "version_name", "version_name");
	}
	
	// デバイスから取得した設定値をUI画面向けに加工する。
	public static function convertToUi($config) {
		
		$ret = $config;
		
    // 顔認証
		if (empty($ret["tipsEnable"])) {
			$ret["tipsCustom"] = "";
		} else if ($ret["tipsType"] != "Custom") {
			if ($ret["tipsType"] == "Welcome") 		$ret["tipsCustom"] = "登録者";
			if ($ret["tipsType"] == "RecognitOK") 	$ret["tipsCustom"] = "認証成功";
			if ($ret["tipsType"] == "PunchOK") 		$ret["tipsCustom"] = "カード認証成功";
			if ($ret["tipsType"] == "PassOK") 		$ret["tipsCustom"] = "通行許可";
		}
    // カード認証
		if (empty($ret["tipsEnableCard"])) {
			$ret["tipsCustomCard"] = "";
		} else if ($ret["tipsTypeCard"] != "Custom") {
			if ($ret["tipsTypeCard"] == "Welcome") 		$ret["tipsCustomCard"] = "登録者";
			if ($ret["tipsTypeCard"] == "RecognitOK") 	$ret["tipsCustomCard"] = "認証成功";
			if ($ret["tipsTypeCard"] == "PunchOK") 		$ret["tipsCustomCard"] = "カード認証成功";
			if ($ret["tipsTypeCard"] == "PassOK") 		$ret["tipsCustomCard"] = "通行許可";
		}
		
    // 顔認証
		if (empty($ret["strangerTipsEnable"])) {
			$ret["strangerTipsCustom"] = "";
		} else if ($ret["strangerTipsType"] != "Custom") {
			if ($ret["strangerTipsType"] == "AccessFailNothit") $ret["strangerTipsCustom"] = "未登録者";
		}
    // カード認証
		if (empty($ret["strangerTipsEnableCard"])) {
			$ret["strangerTipsCustomCard"] = "";
		} else if ($ret["strangerTipsTypeCard"] != "Custom") {
			if ($ret["strangerTipsTypeCard"] == "AccessFailNothit") $ret["strangerTipsCustomCard"] = "未登録者";
		}
		
		$ret["recogWorkstateTime"] 	= sprintf("%.1f", $ret["recogWorkstateTime"]);
		$ret["tempValueRangeFrom"] 	= sprintf("%.1f", $ret["tempValueRangeFrom"]);
		$ret["tempValueRangeTo"] 	= sprintf("%.1f", $ret["tempValueRangeTo"]);
		$ret["tempCorrection"] 		= sprintf("%.1f", $ret["tempCorrection"]);
	
		if ($ret["deviceStandbyTime"] == -1) $ret["deviceStandbyTime"] = 0;
		
		if (empty($ret["maskWearShowEnable"])) 		$ret["maskWearShowTips"] = "";
		if (empty($ret["maskNowearShowEnable"])) 	$ret["maskNowearShowTips"] = "";
		if (empty($ret["tempNormalShowEnable"])) 	$ret["tempNormalShowTips"] = "";
		if (empty($ret["tempAbnormalShowEnable"])) $ret["tempAbnormalShowTips"] = "";
		
		return $ret;
	}
	
	// デバイスに取得する設定値をAPI向けに加工する。
	public static function recogConvertToApi($config) {
		
		$ret = $config;
		
    // 顔認証
		if (empty($ret["tipsCustom"])) {
			$ret["tipsEnable"] = 0;
		} else {
			$ret["tipsEnable"] = 1;
			if ($ret["tipsCustom"] == "登録者") {
				$ret["tipsType"]   = "Welcome";
				$ret["tipsCustom"] = "";
				
			} else if ($ret["tipsCustom"] == "認証成功") {
				$ret["tipsType"]   = "RecognitOK";
				$ret["tipsCustom"] = "";
				
			} else if ($ret["tipsCustom"] == "カード認証成功") {
				$ret["tipsType"]   = "PunchOK";
				$ret["tipsCustom"] = "";
				
			} else if ($ret["tipsCustom"] == "通行許可") {
				$ret["tipsType"]   = "PassOK";
				$ret["tipsCustom"] = "";
				
			} else {
				$ret["tipsType"]   = "Custom";
			}
			
		}
    // カード認証
		if (empty($ret["tipsCustomCard"])) {
			$ret["tipsEnableCard"] = 0;
		} else {
			$ret["tipsEnableCard"] = 1;
			if ($ret["tipsCustomCard"] == "登録者") {
				$ret["tipsTypeCard"]   = "Welcome";
				$ret["tipsCustomCard"] = "";
				
			} else if ($ret["tipsCustomCard"] == "認証成功") {
				$ret["tipsTypeCard"]   = "RecognitOK";
				$ret["tipsCustomCard"] = "";
				
			} else if ($ret["tipsCustomCard"] == "カード認証成功") {
				$ret["tipsTypeCard"]   = "PunchOK";
				$ret["tipsCustomCard"] = "";
				
			} else if ($ret["tipsCustomCard"] == "通行許可") {
				$ret["tipsTypeCard"]   = "PassOK";
				$ret["tipsCustomCard"] = "";
				
			} else {
				$ret["tipsTypeCard"]   = "Custom";
			}
			
		}
		
    // 顔認証
		if (empty($ret["strangerTipsCustom"])) {
			$ret["strangerTipsEnable"] = 0;
		} else {
			$ret["strangerTipsEnable"] = 1;
			if ($ret["strangerTipsCustom"] == "未登録者") {
				$ret["strangerTipsType"]   = "AccessFailNothit";
				$ret["strangerTipsCustom"] = "";
			} else {
				$ret["strangerTipsType"]   = "Custom";
			}
			
		}
    // カード認証
		if (empty($ret["strangerTipsCustomCard"])) {
			$ret["strangerTipsEnableCard"] = 0;
		} else {
			$ret["strangerTipsEnableCard"] = 1;
			if ($ret["strangerTipsCustomCard"] == "未登録者") {
				$ret["strangerTipsTypeCard"]   = "AccessFailNothit";
				$ret["strangerTipsCustomCard"] = "";
			} else {
				$ret["strangerTipsTypeCard"]   = "Custom";
			}
			
		}
		
		if (empty($ret["maskWearShowTips"])) {
			$ret["maskWearShowEnable"] = 0;
		} else {
			$ret["maskWearShowEnable"] = 1;
		}
		
		if (empty($ret["maskNowearShowTips"])) {
			$ret["maskNowearShowEnable"] = 0;
		} else {
			$ret["maskNowearShowEnable"] = 1;
		}

		if (empty($ret["tempNormalShowTips"])) {
			$ret["tempNormalShowEnable"] = 0;
		} else {
			$ret["tempNormalShowEnable"] = 1;
		}
		
		if (empty($ret["tempAbnormalShowTips"])) {
			$ret["tempAbnormalShowEnable"] = 0;
		} else {
			$ret["tempAbnormalShowEnable"] = 1;
		}
		
		
		return $ret;
	}
	
	
	
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// -------------------------------------------------------------------- 認識関連
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// 認識関連の設定データセットを取得。
	public static function getRecogConfigSets($contractor_id) {
		
		$sql = "
			select
				*
			from
				t_recog_config_set
			where
				contractor_id = {value}
			order by
				recog_config_set_id desc
		";
		
		$ret = DB::selectKeyRow($sql, $contractor_id, "recog_config_set_id");
		
		// JSON出力の際に不要なものを除去。
		foreach ($ret as $id=>$row) {
			unset($row["contractor_id"]);
			unset($row["create_time"]);
			unset($row["create_user_id"]);
			unset($row["update_time"]);
			unset($row["update_user_id"]);
			$ret[$id] = $row;
		}

		return $ret;
	}
	

	// デフォルト値をセット。
	public static function setRecogConfigSetDefaultValue(&$form) {
		
		$form["recog_regist_type"            ] = "add";
		$form["recog_config_set_name"        ] = "";
		$form["strangerTipsCustom"			 ] = "認証失敗";
		$form["strangerTipsCustomCard"	 ] = "認証失敗";
		$form["tipsVoiceEnable"				 ] = "1";
		$form["tipsVoiceEnableCard"		 ] = "1";
		$form["strangerVoiceEnable"			 ] = "1";
		$form["strangerVoiceEnableCard"	 ] = "1";
		$form["recogWorkstateTime"			 ] = "1.0";
		$form["recogLiveness"				 ] = "3";
		$form["recogCircleInterval"			 ] = "0";
		$form["recogSearchThreshold"		 ] = "80";
		$form["maskDetectMode"				 ] = "1";
		$form["maskFaceAttrSwitch"			 ] = "0";
		$form["maskWearShowTips"			 ] = "マスクあり";
		$form["maskWearShowBackgroundColor"	 ] = "Blue";
		$form["maskNowearShowTips"		 	 ] = "マスクなし";
		$form["maskNowearShowBackgroundColor"] = "Red";
		$form["maskWearVoiceEnable"			 ] = "1";
		$form["maskNowearVoiceEnable"		 ] = "1";
		$form["tempEnable"					 ] = "1";
		$form["tempDetectMode"				 ] = "1";
		$form["tempNormalShowTips"			 ] = "%C℃ 温度正常";
		$form["tempAbnormalShowTips"		 ] = "%C℃ 温度異常";
		$form["tempNormalVoiceEnable"		 ] = "1";
		$form["tempAbnormalVoiceEnable"		 ] = "1";
		$form["tempValueRangeFrom"			 ] = "35.5";
		$form["tempValueRangeTo"		  	 ] = "37.3";
		$form["tempCorrection"				 ] = "0.0";
		$form["dispShowName"		 		 ] = "1";
		$form["dispShowID"				 	 ] = "1";
		$form["dispShowPhoto"		 		 ] = "1";
		$form["dispShowIp"			 		 ] = "1";
		$form["dispShowSerailNo"		 	 ] = "1";
		$form["dispShowVersion"		 		 ] = "1";
		$form["dispShowPersonInfo"		     ] = "1";
		$form["dispShowOfflineData"		     ] = "1";
		$form["tipsEnable"		 			 ] = "1";
		$form["tipsEnableCard"		   ] = "1";
		$form["tipsBackgroundColor"		     ] = "Blue";
		$form["tipsBackgroundColorCard"		 ] = "Blue";
		$form["strangerTipsBackgroundColor"	 ] = "Red";
		$form["strangerTipsBackgroundColorCard"] = "Red";
		$form["recogMouthoccThreshold"		 ] = "80";
		// add-start founder luyi
		$form["captureAlarteThreshold"		 ] = "80";
		// add-end founder luyi
		$form["tempLowTempCorrection"		 ] = "1";
		$form["dispInfo"		             ] = "";
		
	}
	
	// 登録時用のバリデータを取得。
	public static function getRecogConfigSetRegistValidator($form, $recogConfigSets) {
		
		return Validators::set($form)
				->at("recog_regist_type"			, "新規or更新"						)->required()->inArray(["add", "update"])
					->ifEquals("add")
						->at("recog_config_set_name"	, "新規設定名称"				)->required()->maxlength(100)
					->ifEquals("update")
						->at("recog_config_set_id"  	, "更新対象"					)->required()->digit()->inArray(array_keys($recogConfigSets))
					->ifEnd()
				->at("dispInfo"						, "会社名/団体名/イベント名など"	)->maxlength(12)
				->at("dispShowName"					, "氏名表示"						)->required()->flag()
				->at("dispShowID"					, "ID表示"							)->required()->flag()
				->at("dispShowPhoto"				, "登録写真表示"					)->required()->flag()
				->at("dispShowIp"					, "IPアドレス表示"					)->required()->flag()
				->at("dispShowSerailNo"				, "シリアルNo表示"					)->required()->flag()
				->at("dispShowVersion"				, "ファームウェアバージョン表示"	)->required()->flag()
				->at("dispShowPersonInfo"			, "登録人物データ数表示"			)->required()->flag()
				->at("dispShowOfflineData"			, "オフライン人数表示"				)->required()->flag()
// 				->at("tipsEnable"					, "認証成功時の通知表示"			)->required()->flag()
				->at("tipsCustom"					, "認識成功時のメッセージ(顔認証)"			)->maxlength(25)
				->at("tipsCustomCard"					, "認識成功時のメッセージ(カード認証)"			)->maxlength(25)
				->at("tipsBackgroundColor"			, "成功時のメッセージ背景色(顔認証)"		)->required()->enum(Enums::tipsBackgroundColor())
				->at("tipsBackgroundColorCard"	, "成功時のメッセージ背景色(カード認証)"		)->required()->enum(Enums::tipsBackgroundColor())
				->at("strangerTipsCustom"			, "認識失敗時のメッセージ(顔認証)"			)->maxlength(25)
				->at("strangerTipsCustomCard"			, "認識失敗時のメッセージ(カード認証)"			)->maxlength(25)
				->at("strangerTipsBackgroundColor"	, "失敗時のメッセージ背景色(顔認証)"		)->required()->enum(Enums::tipsBackgroundColor())
				->at("strangerTipsBackgroundColorCard"	, "失敗時のメッセージ背景色(カード認証)"		)->required()->enum(Enums::tipsBackgroundColor())
				->at("tipsVoiceEnable"				, "認識成功時の音声有無(顔認証)"  			)->required()->flag()
				->at("tipsVoiceEnableCard"		, "認識成功時の音声有無(カード認証)"  			)->required()->flag()
				->at("strangerVoiceEnable"			, "認識失敗時の音声有無(顔認証)"  			)->required()->flag()
				->at("strangerVoiceEnableCard"			, "認識失敗時の音声有無(カード認証)"  			)->required()->flag()
				->at("recogWorkstateTime"			, "認識精度"			  			)->required()->float(1, 0.5, 2.0)
				->at("recogLiveness"				, "識別レベル"			  			)->required()->enum(Enums::recogLiveness())
				->at("recogCircleInterval"			, "識別間隔秒"			  			)->required()->digit(0, 10)
				->at("recogSearchThreshold"			, "認識比較閾値"		  			)->required()->digit(0, 100)
				->at("recogMouthoccThreshold"		, "マスク検出時の認識比較閾値"		)->required()->digit(0, 100)
				// add-start founder luyi
				->at("captureAlarteThreshold"		, "顔写真登録時の警告類似度"		)->required()->digit(0, 100)
				// add-end founder luyi
				->at("maskDetectMode"				, "マスク入場判定"		  			)->required()->enum(Enums::maskDetectMode())
				->at("maskFaceAttrSwitch"			, "マスク検出モード"	  			)->required()->digit(0, 1)
				->at("maskWearShowTips"				, "マスク装着者の通知メッセージ"		)->maxlength(25)
				->at("maskWearShowBackgroundColor"	, "マスク装着者の通知テキスト背景色"	)->required()->enum(Enums::maskShowBackgroundColor())
				->at("maskNowearShowTips"			, "マスク非装着者の通知メッセージ"		)->maxlength(25)
				->at("maskNowearShowBackgroundColor", "マスク非装着者の通知テキスト背景色"	)->required()->enum(Enums::maskShowBackgroundColor())
				->at("maskWearVoiceEnable"			, "マスク装着者の音声通知"  		)->required()->flag()
				->at("maskNowearVoiceEnable"		, "マスク非装着者の音声通知"		)->required()->flag()
				->at("tempEnable"					, "温度検出の有効無効"  			)->required()->flag()
				->at("tempDetectMode"				, "温度入場判定"  					)->required()->enum(Enums::tempDetectMode())
				->at("tempNormalShowTips"			, "温度正常者の通知メッセージ"		)->maxlength(25)
				->at("tempAbnormalShowTips"			, "温度異常者の通知メッセージ"		)->maxlength(25)
				->at("tempNormalVoiceEnable"		, "温度正常者の音声通知"  			)->required()->flag()
				->at("tempAbnormalVoiceEnable"		, "温度異常者の音声通知"  			)->required()->flag()
				->at("tempValueRangeFrom"			, "正常温度設定(From)"				)->required()->float(1, 10.0, ($form['tempValueRangeTo'] - 0.1))
				->at("tempValueRangeTo"				, "正常温度設定(To)"				)->required()->float(1, 10.0, 42.0)
				->at("tempCorrection"				, "温度補正"			  			)->required()->float(1, -5.0, 5.0)
				->at("tempLowTempCorrection"		, "低温度補正"			  			)->required()->flag()
		;
		
		
	}
	
	// 認識系の設定セットを登録。
	public static function registRecogConfigSet($contractor_id, $data) {
	
		$data["contractor_id"] = $contractor_id;
		
		// mod-start founder luyi
		$set = "
			, tipsCustom					= {tipsCustom}
			, tipsCustomCard			= {tipsCustomCard}
			, strangerTipsCustom			= {strangerTipsCustom}
			, strangerTipsCustomCard	= {strangerTipsCustomCard}
			, tipsVoiceEnable				= {flag tipsVoiceEnable}
			, tipsVoiceEnableCard		= {flag tipsVoiceEnableCard}
			, strangerVoiceEnable			= {flag strangerVoiceEnable}
			, strangerVoiceEnableCard	= {flag strangerVoiceEnableCard}
			, recogWorkstateTime			= {recogWorkstateTime}
			, recogLiveness					= {recogLiveness}
			, recogCircleInterval			= {recogCircleInterval}
			, recogSearchThreshold			= {recogSearchThreshold}
			, maskDetectMode				= {maskDetectMode}
			, maskFaceAttrSwitch			= {maskFaceAttrSwitch}
			, maskWearShowTips				= {maskWearShowTips}
			, maskWearShowBackgroundColor	= {maskWearShowBackgroundColor}
			, maskNowearShowTips			= {maskNowearShowTips}
			, maskNowearShowBackgroundColor	= {maskNowearShowBackgroundColor}
			, maskWearVoiceEnable			= {flag maskWearVoiceEnable}
			, maskNowearVoiceEnable			= {flag maskNowearVoiceEnable}
			, tempEnable					= {tempEnable}
			, tempDetectMode				= {tempDetectMode}
			, tempNormalShowTips			= {tempNormalShowTips}
			, tempAbnormalShowTips			= {tempAbnormalShowTips}
			, tempNormalVoiceEnable			= {flag tempNormalVoiceEnable}
			, tempAbnormalVoiceEnable		= {flag tempAbnormalVoiceEnable}
			, tempValueRangeFrom			= {tempValueRangeFrom}
			, tempValueRangeTo				= {tempValueRangeTo}
			, tempCorrection				= {tempCorrection}
			, dispInfo 						= {dispInfo}
			, dispShowName 					= {flag dispShowName}
			, dispShowID 					= {flag dispShowID}
			, dispShowPhoto 				= {flag dispShowPhoto}
			, dispShowIp 					= {flag dispShowIp}
			, dispShowSerailNo 				= {flag dispShowSerailNo}
			, dispShowVersion 				= {flag dispShowVersion}
			, dispShowPersonInfo 			= {flag dispShowPersonInfo}
			, dispShowOfflineData 			= {flag dispShowOfflineData}
			, tipsEnable 					= {flag tipsEnable}
			, tipsEnableCard 			= {flag tipsEnableCard}
			, tipsBackgroundColor 			= {tipsBackgroundColor}
			, tipsBackgroundColorCard 	= {tipsBackgroundColorCard}
			, strangerTipsBackgroundColor 	= {strangerTipsBackgroundColor}
			, strangerTipsBackgroundColorCard 	= {strangerTipsBackgroundColorCard}
			, recogMouthoccThreshold 		= {recogMouthoccThreshold}
			, captureAlarteThreshold		= {captureAlarteThreshold}
			, tempLowTempCorrection 		= {flag tempLowTempCorrection}
		";
		// mod-end founder luyi
		
		if ($data["recog_regist_type"] == "add") {
			$sql = "
				insert into
					t_recog_config_set
				set
					contractor_id 		    = {contractor_id}
					, recog_config_set_name = {recog_config_set_name}
					, create_time 		    = now()
					, create_user_id 	    = {login_user_id}
					, update_time 		    = now()
					, update_user_id 	    = {login_user_id}
					$set
			";
			
			$id = DB::insert($sql, $data);
			
		} else {
			$sql = "
				update
					t_recog_config_set
				set
					update_time 		= now()
					, update_user_id 	= {login_user_id}
					$set
				where
					recog_config_set_id = {recog_config_set_id}
					and contractor_id   = {contractor_id}
			";

			DB::update($sql, $data);
			$id = $data["recog_config_set_id"];
		}
		
		return $id;
	}
	
	// 認識系の設定セットログ詳細の出力。
	public static function getRecogConfigLogDetail($data) {
		
		$rtn = [];
		$mapper = [
			"dispInfo"=>["name"=>"会社名/団体名/イベント名など","type"=>["class"=>"text","ref"=>null]],
			"dispShowName"=>["name"=>"氏名表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"dispShowID"=>["name"=>"ID表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"dispShowPhoto"=>["name"=>"登録写真表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"dispShowIp"=>["name"=>"IPアドレス表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"dispShowSerailNo"=>["name"=>"シリアルNo表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"dispShowVersion"=>["name"=>"ファームウェアバージョン表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"dispShowPersonInfo"=>["name"=>"登録人物データ数表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"dispShowOfflineData"=>["name"=>"オフライン人数表示","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"tipsCustom"=>["name"=>"認識成功時のメッセージ(顔認証)","type"=>["class"=>"text","ref"=>null]],
			"tipsCustomCard"=>["name"=>"認識成功時のメッセージ(カード認証)","type"=>["class"=>"text","ref"=>null]],
			"tipsBackgroundColor"=>["name"=>"成功時のメッセージ背景色(顔認証)","type"=>["class"=>"enum","ref"=>Enums::tipsBackgroundColor()]],
			"tipsBackgroundColorCard"=>["name"=>"成功時のメッセージ背景色(カード認証)","type"=>["class"=>"enum","ref"=>Enums::tipsBackgroundColor()]],
			"strangerTipsCustom"=>["name"=>"認識失敗時のメッセージ(顔認証)","type"=>["class"=>"text","ref"=>null]],
			"strangerTipsCustomCard"=>["name"=>"認識失敗時のメッセージ(カード認証)","type"=>["class"=>"text","ref"=>null]],
			"strangerTipsBackgroundColor"=>["name"=>"失敗時のメッセージ背景色(顔認証)","type"=>["class"=>"enum","ref"=>Enums::tipsBackgroundColor()]],
			"strangerTipsBackgroundColorCard"=>["name"=>"失敗時のメッセージ背景色(カード認証)","type"=>["class"=>"enum","ref"=>Enums::tipsBackgroundColor()]],
			"tipsVoiceEnable"=>["name"=>"認識成功時の音声有無(顔認証)","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"tipsVoiceEnableCard"=>["name"=>"認識成功時の音声有無(カード認証)","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"strangerVoiceEnable"=>["name"=>"認識失敗時の音声有無(顔認証)","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"strangerVoiceEnableCard"=>["name"=>"認識失敗時の音声有無(カード認証)","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"recogWorkstateTime"=>["name"=>"認識精度","type"=>["class"=>"float","ref"=>null]],
			"recogLiveness"=>["name"=>"識別レベル","type"=>["class"=>"enum","ref"=>Enums::recogLiveness()]],
			"recogCircleInterval"=>["name"=>"識別間隔秒","type"=>["class"=>"digit","ref"=>null]],
			"recogSearchThreshold"=>["name"=>"認識比較閾値","type"=>["class"=>"digit","ref"=>null]],
			"recogMouthoccThreshold"=>["name"=>"マスク検出時の認識比較閾値","type"=>["class"=>"digit","ref"=>null]],
			"captureAlarteThreshold"=>["name"=>"顔写真登録時の警告類似度","type"=>["class"=>"digit","ref"=>null]],
			"maskDetectMode"=>["name"=>"マスク入場判定","type"=>["class"=>"enum","ref"=>Enums::maskDetectMode()]],
			"maskFaceAttrSwitch"=>["name"=>"マスク検出モード","type"=>["class"=>"flag","ref"=>["口のみ覆うも許可する","鼻と口の両方を覆う"]]],
			"maskWearShowTips"=>["name"=>"マスク装着者の通知メッセージ","type"=>["class"=>"text","ref"=>null]],
			"maskWearShowBackgroundColor"=>["name"=>"マスク装着者の通知テキスト背景色","type"=>["class"=>"enum","ref"=>Enums::maskShowBackgroundColor()]],
			"maskNowearShowTips"=>["name"=>"マスク非装着者の通知メッセージ","type"=>["class"=>"text","ref"=>null]],
			"maskNowearShowBackgroundColor"=>["name"=>"マスク非装着者の通知テキスト背景色","type"=>["class"=>"enum","ref"=>Enums::maskShowBackgroundColor()]],
			"maskWearVoiceEnable"=>["name"=>"マスク装着者の音声通知","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"maskNowearVoiceEnable"=>["name"=>"マスク非装着者の音声通知","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"tempEnable"=>["name"=>"温度検出の有効無効","type"=>["class"=>"flag","ref"=>["温度検知を無効にする","温度検知を有効にする"]]],
			"tempDetectMode"=>["name"=>"温度入場判定","type"=>["class"=>"enum","ref"=>Enums::tempDetectMode()]],
			"tempNormalShowTips"=>["name"=>"温度正常者の通知メッセージ","type"=>["class"=>"text","ref"=>null]],
			"tempAbnormalShowTips"=>["name"=>"温度異常者の通知メッセージ","type"=>["class"=>"text","ref"=>null]],
			"tempNormalVoiceEnable"=>["name"=>"温度正常者の音声通知","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"tempAbnormalVoiceEnable"=>["name"=>"温度異常者の音声通知","type"=>["class"=>"flag","ref"=>["しない","する"]]],
			"tempValueRangeFrom"=>["name"=>"正常温度設定(From)","type"=>["class"=>"float","ref"=>null]],
			"tempValueRangeTo"=>["name"=>"正常温度設定(To)","type"=>["class"=>"float","ref"=>null]],
			"tempCorrection"=>["name"=>"温度補正","type"=>["class"=>"float","ref"=>null]],
			"tempLowTempCorrection"=>["name"=>"低温度補正","type"=>["class"=>"flag","ref"=>["しない","する"]]]
		];
		
		foreach ($mapper as $k => ["name"=>$name,"type"=>["class"=>$class,"ref"=>$ref]]) {
			$value = $data[$k];
			if ($class=="flag"){
				if ($value !== "1" && $value !== 1) {
					$value = "0";
				}
				$value = $ref[$value];
			} elseif ($class=="enum") {
				$value = $ref[$value];
			} else {
				$value = $value??"";
			}
			$rtn[$name] = $value;
		}
		return $rtn;
	}
	
	// 削除時用のバリデータを取得。
	public static function getRecogConfigSetDeleteValidator($form, $recogConfigSets) {
		
		return Validators::set($form)
				->at("recog_config_set_id"  	, "更新対象"					)->required()->digit()->inArray(array_keys($recogConfigSets));

	}
	
	// 認識系の設定セットを削除。
	public static function deleteRecogConfigSet($contractor_id, $data) {
	
		$data["contractor_id"] = $contractor_id;
		$sql = "delete from t_recog_config_set where recog_config_set_id = {recog_config_set_id} and contractor_id = {contractor_id}";

		DB::delete($sql, $data);
	}
	
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// -------------------------------------------------------------------- システム関連
	// ----------------------------------------------------------------------------------------------------------------------------------------
	// システム関連の設定データセットを取得。
	public static function getSystemConfigSets($contractor_id) {
		
		$sql = "
			select
				*
			from
				t_system_config_set
			where
				contractor_id = {value}
			order by
				system_config_set_id desc
		";
		
		$ret = DB::selectKeyRow($sql, $contractor_id, "system_config_set_id");
		
		// JSON出力の際に不要なものを除去。
		foreach ($ret as $id=>$row) {
			unset($row["contractor_id"]);
			unset($row["create_time"]);
			unset($row["create_user_id"]);
			unset($row["update_time"]);
			unset($row["update_user_id"]);
			$ret[$id] = $row;
		}

		return $ret;
	}
	

	// デフォルト値をセット。
	public static function setSystemConfigSetDefaultValue(&$form) {
		
		$form["system_regist_type"      ] = "add";
		$form["system_config_set_name"  ] = "";
		$form["deviceAudioVolume"		] = "80";
		$form["deviceScreenBrightness"	] = "80";
		$form["deviceLedBrightness"		] = "30";
		$form["deviceWorkstateTime"		] = "0";
		$form["deviceStandbyTime"		] = "0";
		$form["ntpEnable"				] = "1";
		$form["ntpHostName"				] = "ntp.nict.jp";
		$form["ntpPort"					] = "123";
		$form["ntpInterval"				] = "60";
		$form["hibernateRecogEnable"] = "1";
		$form["hibernateTips"				] = "デバイス休止中";
		
	}
	
	// 登録時用のバリデータを取得。
	public static function getSystemConfigSetRegistValidator($form, $systemConfigSets) {
		
		return Validators::set($form)
				->at("system_regist_type"			, "新規or更新"					)->required()->inArray(["add", "update"])
					->ifEquals("add")
						->at("system_config_set_name"	, "新規設定名称"			)->required()->maxlength(100)
					->ifEquals("update")
						->at("system_config_set_id"  	, "更新対象"				)->required()->digit()->inArray(array_keys($systemConfigSets))
					->ifEnd()
				->at("deviceAudioVolume"			, "音声ボリューム"				)->required()->digit(0, 100)
				->at("deviceScreenBrightness"		, "画面の明るさ"				)->required()->digit(0, 100)
				->at("deviceLedBrightness"			, "LED照明の明るさ"				)->required()->digit(0, 100)
				->at("deviceWorkstateTime"			, "スクリーンセーバに入る時間"	)->required()->digit(0, 86400)
				->at("deviceStandbyTime"			, "スタンバイに入る時間"		)->required()->digit(0, 86400)
				->at("ntpEnable"					, "NTP設定"						)->required()->flag()
				->at("ntpHostName"					, "NTPサーバホスト"				)->required()->maxlength(100)
				->at("ntpPort"						, "NTPサーバポート"				)->required()->digit(0, 65535)
				->at("ntpInterval"					, "時刻同期間隔"				)->required()->digit(1, 1440)
				->at("hibernateRecogEnable"		, "休止時カード認証機能")->required()->flag()
				->at("hibernateTips"					, "休止中メッセージ"		)->required()->maxlength(32)
			;
			
	}
	
	// システム系の設定セットを登録。
	public static function registSystemConfigSet($contractor_id, $data) {
	
		$data["contractor_id"] = $contractor_id;
		
		$set = "
			, deviceAudioVolume			= {deviceAudioVolume}
			, deviceScreenBrightness	= {deviceScreenBrightness}
			, deviceLedBrightness		= {deviceLedBrightness}
			, deviceStandbyTime			= {deviceStandbyTime}
			, deviceWorkstateTime		= {deviceWorkstateTime}
			, ntpEnable					= {flag ntpEnable}
			, ntpHostName				= {ntpHostName}
			, ntpPort					= {ntpPort}
			, ntpInterval				= {ntpInterval}
			, hibernateRecogEnable = {flag hibernateRecogEnable}
			, hibernateTips				= {hibernateTips}
		";
		
		if ($data["system_regist_type"] == "add") {
			$sql = "
				insert into
					t_system_config_set
				set
					contractor_id 		    = {contractor_id}
					, system_config_set_name = {system_config_set_name}
					, create_time 		    = now()
					, create_user_id 	    = {login_user_id}
					, update_time 		    = now()
					, update_user_id 	    = {login_user_id}
					$set
			";
			
			$id = DB::insert($sql, $data);
			
		} else {
			$sql = "
				update
					t_system_config_set
				set
					update_time 		= now()
					, update_user_id 	= {login_user_id}
					$set
				where
					system_config_set_id = {system_config_set_id}
					and contractor_id   = {contractor_id}
			";

			DB::update($sql, $data);
			$id = $data["system_config_set_id"];
		}
		
		return $id;
	}
	
	// システム系の設定セットログ詳細の出力。
	public static function getSystemConfigLogDetail($data) {
		
		$rtn = [];
		$mapper = [
			"deviceAudioVolume"=>["name"=>"音声ボリューム","type"=>["class"=>"digit","ref"=>null]],
			"deviceScreenBrightness"=>["name"=>"画面の明るさ","type"=>["class"=>"digit","ref"=>null]],
			"deviceLedBrightness"=>["name"=>"LED照明の明るさ","type"=>["class"=>"digit","ref"=>null]],
			"deviceWorkstateTime"=>["name"=>"スクリーンセーバに入る時間","type"=>["class"=>"digit","ref"=>null]],
			"deviceStandbyTime"=>["name"=>"スタンバイに入る時間","type"=>["class"=>"digit","ref"=>null]],
			"ntpEnable"=>["name"=>"NTP設定","type"=>["class"=>"flag","ref"=>["無効","有効"]]],
			"ntpHostName"=>["name"=>"NTPサーバホスト","type"=>["class"=>"text","ref"=>null]],
			"ntpPort"=>["name"=>"NTPサーバポート","type"=>["class"=>"digit","ref"=>null]],
			"ntpInterval"=>["name"=>"時刻同期間隔","type"=>["class"=>"digit","ref"=>null]],
			"hibernateRecogEnable"=>["name"=>"休止時カード認証機能","type"=>["class"=>"flag","ref"=>["無効","有効"]]],
			"hibernateTips"=>["name"=>"休止中メッセージ","type"=>["class"=>"text","ref"=>null]]
		];
		
		foreach ($mapper as $k => ["name"=>$name,"type"=>["class"=>$class,"ref"=>$ref]]) {
			$value = $data[$k];
			if ($class=="flag"){
				if ($value !== "1" && $value !== 1) {
					$value = "0";
				}
				$value = $ref[$value];
			} elseif ($class=="enum") {
				$value = $ref[$value];
			} else {
				$value = $value??"";
			}
			$rtn[$name] = $value;
		}
		return $rtn;
	}
	
	// 削除時用のバリデータを取得。
	public static function getSystemConfigSetDeleteValidator($form, $systemConfigSets) {
		
		return Validators::set($form)
				->at("system_config_set_id"  	, "更新対象"					)->required()->digit()->inArray(array_keys($systemConfigSets));

	}
	
	// システム系の設定セットを削除。
	public static function deleteSystemConfigSet($contractor_id, $data) {
	
		$data["contractor_id"] = $contractor_id;
		$sql = "delete from t_system_config_set where system_config_set_id = {system_config_set_id} and contractor_id = {contractor_id}";

		DB::delete($sql, $data);
	}
	
	/* add-start founder luyi */
	
	// 権限 登録・更新用バリデータを取得。
	public static function getAuthUpdateValidator($form, $auths, $functions) {
		
		return Validators::set($form)
			->at("auth_regist_type", "新規or更新")->required()->inArray(["add", "update"])
			->ifEquals("add")
			->at("auth_set_name", "新規設定名称")->required()->maxlength(100)
			->ifEquals("update")
			->at("auth_set_id", "更新対象")->required()->digit()->enum($auths)
			->ifEnd()
			->at("function_ids", "機能")->arrayValue()->inArray(array_map(function($e) { return $e["function_id"]; }, $functions));
	}
	
	// 権限　削除用バリデータを取得。
	public static function getAuthDeleteValidator($form, $auths) {
		return Validators::set($form)
			->at("auth_set_id", "更新対象")->required()->enum($auths);
	}
	
	// 権限 登録・更新。
	public static function updateAuth($data) {
		if ($data["auth_regist_type"] == "add") {
			// 新規登録の場合、権限の登録
			$sql = "
				insert into
					t_auth_set
				set
					contractor_id    = {contractor_id}
					, auth_set_name  = {auth_set_name}
					, create_time    = now()
					, create_user_id = {login_user_id}
					, update_time    = now()
					, update_user_id = {login_user_id}
			";
			$id = DB::insert($sql, $data);
		} else {
			// 既存権限更新の場合、一括削除
			$sql = "
				delete from
					t_function_auth
				where
					auth_set_id = {auth_set_id}
			";
			DB::delete($sql, $data);
			$id = $data["auth_set_id"];
		}
		
		// 一括登録
		$sql  = "
			insert into
				t_function_auth
			(auth_set_id, function_id)
			values
		";
		foreach ($data["function_ids"] as $function_id) {
			$sql = $sql."(".$id.", "."'$function_id'"."), ";
		}
		DB::insert(rtrim($sql, ", "));
		return $id;
	}
	
	// 権限 削除。
	public static function deleteAuth($data) {
		// t_auth_set
		$sql = "delete from t_auth_set where auth_set_id = {auth_set_id}";
		DB::delete($sql, $data);
		
		// t_function_auth
		$sql = "delete from t_function_auth where auth_set_id = {auth_set_id}";
		DB::delete($sql, $data);
	}
	
	/* add-end founder luyi */
}
