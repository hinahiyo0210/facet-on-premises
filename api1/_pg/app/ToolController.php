<?php

class ToolController extends BaseController {	// 認証しない。
	
	// @Override
	public function prepare(&$form) {
	}
	
	// APIデバッグツールを表示する。
	public function indexAction(&$form) {
		
		// Basic認証。
		$this->basicAuth("api", "test");
		
		$isLocal = getRemoteAddr() == "::1";
		
		$this->assign("isLocal", $isLocal);
		return "api-tool.tpl";
	}
	
	// APIデバッグツールを表示する。
	public function index2Action(&$form) {
		
		// Basic認証。
		$this->basicAuth("api", "test");
		
		$isLocal = getRemoteAddr() == "::1";
		
		$this->assign("isLocal", $isLocal);
		return "api-tool2.tpl";
	}
	
	
	
	// S3アクセスのためのCookieを発行する。
	public function setPictureCookieAction(&$form) {
		
		Validators::setErroredFunction(null);
		$data = Validators::set($form)
			->at("p", "p")->required()->maxlength(1000)
			->at("s", "s")->required()->maxlength(1000)
			->at("k", "k")->required()->maxlength(1000)
			->getValidatedData();
		
		$ret = [];
		
		if (Errors::isErrored()) {
			$ret = Errors::getMessagesArray();
			
		} else {
			$ret = ["result"=>"OK"];
			setcookie("CloudFront-Policy"     , $data["p"], ["path" => "/", "domain" => CLOUDFRONT_COOKIE_DOMAIN, "secure" => ENABLE_SSL, "httponly" => true, "samesite" => ENABLE_SSL ? "None" : ""]);
			setcookie("CloudFront-Signature"  , $data["s"], ["path" => "/", "domain" => CLOUDFRONT_COOKIE_DOMAIN, "secure" => ENABLE_SSL, "httponly" => true, "samesite" => ENABLE_SSL ? "None" : ""]);
			setcookie("CloudFront-Key-Pair-Id", $data["k"], ["path" => "/", "domain" => CLOUDFRONT_COOKIE_DOMAIN, "secure" => ENABLE_SSL, "httponly" => true, "samesite" => ENABLE_SSL ? "None" : ""]);
		}
		
		$json = "/* ".json_encode($ret, JSON_UNESCAPED_UNICODE)." */";
		header("Content-type: text/javascript");
		header("Content-Length: ".strlen($json));
		echo $json;
	}
	
	// ワンタイムファイルを出力する。
	public function getOnceFileAction(&$form) {
		
		infoLog(getDetailAccessLog());
		
		Validators::setErroredFunction(null);
		$data = Validators::set($form)
			->at("name", "name")->required()->maxlength(50)
			->getValidatedData();
		
		if (Errors::isErrored()) response400();
		
		$bin = ToolService::readOnceFile($data["name"]);
		infoLog("response: ".strlen($bin)." bytes");

		if (endsWith($data["name"], ".jpg") || endsWith($data["name"], ".jpeg") ) {
			header("content-type: image/jpeg");
			header("content-length: ".strlen($bin));
		}
		
		echo $bin;
	}
	
	// APIアクセスのテスト。
	public function testAccessAction(&$form) {
		
// 		$client = new HttpClient("http://localhost/api1", "text");
// 		$client->header["ds-api-token"] = "HTtfNCzmKk9Vl95kBlWgB8q9Q0Pyl44lGiseH8xIeFmSnXdp92HQo7bWxHhVX8wa";
// 		var_dump($client->get("/device/getDevice?nochange-response-status=1"));
	}
	
	
}
