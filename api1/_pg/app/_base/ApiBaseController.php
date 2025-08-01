<?php

abstract class ApiBaseController extends BaseController {

 	protected $contractor;
	
 	protected $serialNos = [];
	
 	protected $isJsonp = false;
	
	// @Override
	public function init() {
		parent::init();

		define("CONTROLLER_TYPE", "api");
		
		// ---------------------------------- DB
		DB::init(DB_DRIVER, DB_HOST, DB_USER, DB_PASS, DB_NAME);	// ここではまだDBに接続される訳では無い。初回利用時に接続される。
		
		// バリデーションエラー時にthrowする。
		Validators::setErroredFunction(function() {
			throw new ApiParameterException();
		});
		
		// 認証。
		$contractorOrErrorStatus = ContractorService::auth();
		if (Errors::isErrored()) {
			infoLog("AUTH_ERROR ".getDetailAccessLog());
			$this->responseError($contractorOrErrorStatus);
			return false;
		}
		$this->contractor = $contractorOrErrorStatus;
		
		foreach ($this->contractor["deviceList"] as $d) {
			$this->serialNos[] = $d["serial_no"];
		}
		
		// 全ての操作を記録する。
		$log = "CONT[".$this->contractor["contractor_id"]."] ".getDetailAccessLog();
		infoLog($log);
		
		// ブラウザからのアクセスの場合にtrue。
		$this->isJsonp = !empty($_GET["callback"]) && strlen($_GET["callback"]) < 100;
		
		
		

	}
	
	// エラーを返却。
	protected function responseError($status = 400) {
		
		$errors = [];
		foreach (Errors::getMessages() as $name=>$msgs) {
			foreach ($msgs as $msg) {
				if ($name == "device") $status = 503;
				$errors[] = ["name"=>$name, "message"=>$msg];
			}
		}
		$ret = [
			"error"=>true
			, "errors"=>$errors
		];
		
		if (empty($_REQUEST["nochange-response-status"])) {
			http_response_code($status);
		}
		
		infoLog("HTTP{$status} ".json_encode($ret, JSON_UNESCAPED_UNICODE));
		
		Errors::clear();
		
		$this->responseJson($ret);
	}
	
	// 処理結果で返却。
	protected function response($callback) {
		
		if (Errors::isErrored()) {
			$this->responseError();
		}
		$callback();
	}
	
	// JSONを返却。
	protected function responseJson($json) {
		
		if (Errors::isErrored()) {
			$this->responseError();
		
		} else {
			if (!is_string($json)) {
				if (!empty($_REQUEST["json-debug"])) {
					$json = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
					
				} else {
					$json = json_encode($json);
				}
			}
			
		}

		if (!empty($this->isJsonp)) {
			// jsonp
			$jsonp = $_GET["callback"]."(".$json.")";
			header("Content-type: application/x-javascript");
			header("Content-Length: ".strlen($jsonp));
    		echo $jsonp;
			return;
		}
		
		header("Content-Type: application/json; charset=utf-8");
		header("Content-Length: ".strlen($json));
    	echo $json;
    	return;
	}

	protected function getDeiveBySerialNo(&$form) {
		
		$data = Validators::set($form)
					->at("serialNo", "serialNo")->required()->inArray($this->serialNos)
					->getValidatedData();
		
		$serialNo = $data["serialNo"];
		$device = $this->contractor["deviceList"][$serialNo];
		
		return $device; 
	}

	// @Override
	public function doException(Exception $e) {
		
		// 入力チェックエラー。
		if ($e instanceof ApiParameterException) {

			// ロールバック。
			DB::rollback();
			DB::begin();
			
			// エラーを返却。
			$this->responseError(400);

			// afterの処理を続行する。
			return true;	
		}
		
		// システムエラー
		if ($e instanceof SystemException) {
			
			// ロールバック。
			DB::rollback();
			DB::begin();
			
			// エラーを返却。
			$this->responseError(500);

			// afterの処理を続行する。
			return true;	
		}
		
		// デバイス通信系エラー。
		if ($e instanceof DeviceWsException) {
			// ロールバック。
			DB::rollback();
			DB::begin();
			
			// エラーを返却。
			Errors::add("device", $e->getMessage());
			$this->responseError(503);

			// afterの処理を続行する。
			return true;	
		}
		
		// 排他エラー。
		if ($e instanceof DeviceExclusionException) {
			// ロールバック。
			DB::rollback();
			DB::begin();
			
			// エラーを返却。
			Errors::add("exclusion", $e->getMessage());
			$this->responseError(400);

			// afterの処理を続行する。
			return true;	
		}
		
		
		return false;	// 処理を続行しない。
	}
	
	// @Override
	public function actionAfter(&$form, $tpl) {

	}
	
	// @Override
	public function after(&$form) {
		
		// 最終WebSocket通信時刻を更新。
		if (!empty(WsApiService::$lastAccessTime)) {
			DeviceService::updateWsLastTime(WsApiService::$accessSerialNo, WsApiService::$lastAccessTime);
		}
		
		// 同期ログで未保存のデータがあるであればupdate。 
		SyncService::updateEndLog(SyncService::$processingRegisted ? 40 : 30);
		
	}

}
