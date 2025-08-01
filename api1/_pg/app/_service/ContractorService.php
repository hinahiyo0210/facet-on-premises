<?php

class ContractorService {

	// 認証。
	public static function auth() {
		
		// トークンを取得。
		if (!empty($_SERVER["HTTP_DS_API_TOKEN"])) {
			$token = $_SERVER["HTTP_DS_API_TOKEN"];
			
		} else if (!empty($_REQUEST["ds-api-token"])){
			$token = $_REQUEST["ds-api-token"];
			
		} else {
			Errors::add("ds-api-token", "ds-api-tokenがリクエストヘッダもしくはリクエストパラメータに指定されていません。");
			return 403;
		}
		
		if (strlen($token) != 64) {
			Errors::add("ds-api-token", "ds-api-tokenの桁数が不正です。64桁で指定して下さい。");
			return 403;
		}

		// 検索。
		$cont = DB::selectRow("select * from m_contractor where api_token = {value}", $token);
		
		// 存在チェック。
		if ($cont == null)  {
			Errors::add("ds-api-token", "ds-api-tokenに該当する契約情報が見つかりませんでした。");
			return 403;
		}
		
		// ステータスチェック。
		/**
		 * 10: 商談中
		 * 20: トライアル導入中
		 * 30: 稼働中
		 * 90: 無効
		 */
		if ($cont["state"] != 20 && $cont["state"] != 30) {
			Errors::add("ds-api-token", "ds-api-tokenに該当する契約情報は見つかりましたが、有効な状態ではありません。");
			return 403;
		}
		
		// トライアルの場合は有効期限チェック。
		if ($cont["state"] == 20) {
			if (strtotime($cont["trial_limit"]) < time()) {
				Errors::add("ds-api-token", "トライアル期間を過ぎています。");
				return 403;
			}
		}
		
		// IPチェック。
		if (!empty($cont["api_allow_ip"])) {
			$ip = getRemoteAddr();
			
			$allow = false;
			foreach (explode(",", $cont["api_allow_ip"]) as $allowIp) {
				if (trim($allowIp) == $ip) {
					$allow = true;
					break;
				}
			}
			
			if (!$allow) {
				Errors::add("ds-api-token", "許可されていないアクセス元です。[".$ip."]");
				return 401;
			}
		}

		// ドメイン指定がある場合には一致性をチェック。
		$domain = $_SERVER["SERVER_NAME"];
		if (!empty($cont["domain"])) {
 			if ($cont["domain"] != $domain) {
		 		Errors::add("ds-api-token", "許可されていないアクセスです。[".$domain."]");
		 		return 401;
 			}
 		}
 		
 		// 他の契約者に割り当てられたドメインの場合もエラーにする。
 		$param = ["contractor_id"=>$cont["contractor_id"], "domain"=>$domain];
 		if (DB::exists("select 1 from m_contractor c where contractor_id != {contractor_id} and state = 30 and domain = {domain}", $param)) {
			Errors::add("ds-api-token", "許可されていないアクセスです。[".$domain."]");
			return 401;
 		}
		
 				
		// デバイスの一覧を取得。
		$cont["deviceList"] = ContractorService::getDeviceList($cont["contractor_id"]);
		
		// 許容。
		return $cont;
	}
	
	// デバイスの一覧を取得。
	public static function getDeviceList($contractor_id) {
		$sql = "select * from m_device where contractor_id = {value} and contract_state = 10 order by sort_order";
		return DB::selectKeyRow($sql, $contractor_id, "serial_no");
			
	}
	
	
	
	

}
