<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

class HttpClient {

	public $urlPrefix;
	public $timeout;
	
	public $lastUrl = "";
	public $lastResult;
	public $lastHeader;
	public $cookie = array();
	public $header = array();
	
	public $useReferer = false;
	public $continueCookie = true;
	public $format = "";	// text / xml / json
	public $triggerError = false;
	public $errorMessage;
	
	public $ingoreLogParams;
	
	public $infoLog = false;
	
	public $logger = false;
	
	public $errorCallback = null;
	
	public function __construct($urlPrefix = "", $format = "", $timeout = 120) {
		$this->urlPrefix = $urlPrefix;
		$this->timeout = $timeout;
		$this->format = $format;
		
		$this->ingoreLogParams = array();
// 		foreach (unserialize(LOG_IGNORE_PARAMS) as $mask) {
// 			$this->ingoreLogParams[$mask] = true;
// 		}
	}
	
	
	public function restGet($url, $id = "") {
		return $this->send("GET", $url."/".$id, false);
	}
	public function get($url, $bodyData = array()) {
		return $this->send("GET", $url, $bodyData);
	}
	public function post($url, $bodyData = array()) {
		return $this->send("POST", $url, $bodyData);
	}
	public function put($url, $bodyData = array()) {
		return $this->send("PUT", $url, $bodyData);
	}
	public function delete($url, $bodyData = array()) {
		return $this->send("DELETE", $url, $bodyData);
	}
	
	protected function buildPostDataString($method, &$url, $bodyData) {
			
		if ($bodyData === false) return "";
		
		if (!is_array($bodyData)) {
			return $bodyData;
		}
		
		if ($method == "GET") {
			$urlParam = http_build_query($bodyData, "", "&");
			if ($urlParam) {
				if (exists($url, "?")) {
					$url .= "&".$urlParam;
				} else {
					$url .= "?".$urlParam;
				}	
			}
			return "";
			
// 		} else if ($this->format == "json") {
// 			return json_encode($bodyData);
			
// 		} else if ($this->format == "xml") {
// 			return simplexml_load_string($bodyData);
			
		} else {
			return http_build_query($bodyData, "", "&");
		}
		
	}
	
	
	public function send($method, $url, $bodyData = array()) {
	
		$url = $this->urlPrefix.$url;
		$header = $this->header;
		
		// postData
		$postDataString = $this->buildPostDataString($method, $url, $bodyData);
		
		// ログ出力用（パスワード系文字列はマスクする）
		$logBodyData = array();
		$logPostDataString = "";
		if (is_array($bodyData)) {
			foreach ($bodyData as $k=>$v) {
				if (isset($this->ingoreLogParams[$k])) $v = "*****";
				$logBodyData[$k] = $v;
			}
			$logPostDataString = $this->buildPostDataString($method, $url, $logBodyData);
		} else {
			$logPostDataString = $postDataString;
			
		}
		
			
		// Content-Type
		if (empty($header["Content-Type"])) {
			$header["Content-Type"] = "application/x-www-form-urlencoded";
		}
		
		// Referer
		if ($this->useReferer && !empty($this->lastUrl) && empty($header["Referer"])) {
			$header["Referer"] = $this->lastUrl;
		}
		
		// Cookie
		if (!empty($this->cookie)) {
			$c = '';
			$first = true;
			foreach ($this->cookie as $k=>$v) {
				if ($first) {
					$first = false;
				} else {
					$c .= "; ";
				}
				$c .= $k."=".$v;
			}
			$header["Cookie"] = $c;
		}
		
		// ボディ部		
		if (!empty($postDataString) && $method != "GET") {
			$header["Content-Length"] = mb_strlen($postDataString);
		}
		
		// HTTP/1.1 100 Continue返却を受けないようにする。
		$header["Expect"] = "";
		
		
		// header
		$haderString = "";
		foreach ($header as $n=>$v) {
			if ($haderString != "") {
				$haderString .= "\r\n";
			}
			$haderString .= $n.": ".$v;
		}
		$contextHttp["header"] = $haderString;
	
		$sendInfo = "method<{$method}> url<{$url}> timeout<{$this->timeout}> data\n{$haderString}\n\n{$logPostDataString}\n";
		
	    // データ取得を実行
	    $logId = time()."_".getRandomPassword(3);
	    if ($this->infoLog) {
	    	if ($this->logger) {
	    		$this->logger->info(@session_id()." [REQUEST $logId] $sendInfo");
	    	} else {
			    infoLog("http通信を開始します。$sendInfo");
	    	}
	    }
		
		$data = false;
	    Globals::$errorDie = false;	
//		$httpData = file_get_contents($url, false, stream_context_create($context));

	    $httpResult = $this->http($url, $method, $haderString, $postDataString);
	    Globals::$errorDie = true;	
		
	    if ($this->infoLog) {
	    	if ($this->logger) {
	    		$this->logger->info(@session_id()." [RESPONSE $logId] ".print_r($httpResult, 1));
	    	} else {
		    	infoLog(print_r($httpResult, 1));
	    	}
	    }
	    
	    if(!$this->isSuccess($httpResult["httpStatusLine"])) {
	    	
    		$sendInfo .= " http_response_header[".join("\n", $httpResult["header"])."], http_response_body[".$httpResult["body"]."]";
    		
    		// エラーで失敗
    		$this->errorMessage = "http通信時にエラーが発生しました。\n{$sendInfo}";
			
    		if ($this->triggerError) {
    			trigger_error($this->errorMessage);
    		} else {
    			errorLog($this->errorMessage);
    		}
    		
    		if ($this->errorCallback) {
    			$callback = $this->errorCallback;
    			$callback($httpResult);
    		}
    		
    		return false;	
    		
//	        // タイムアウトで失敗
//    		$this->errorMessage = "http通信でタイムアウト、もしくは通信障害が発生しました。\n{$sendInfo}";
//	    	if ($this->triggerError) trigger_error($this->errorMessage);	
//    		return false;
	    }
	
	    // Set-Cookie(セッション継続などの場合に利用)
	    if ($this->continueCookie) {
	    	
		    foreach ($httpResult["header"] as $r) {
		        if (strpos($r, 'Set-Cookie') === false) {
		            continue;
		        }
		        
		        $c = explode(' ', $r);
		        $c = str_replace(';', '', $c[1]);
		        $pos = mb_strpos($c, "=");
		        
		        $k = mb_substr($c, 0, $pos);
		        $v = mb_substr($c, $pos + 1, mb_strlen($c));
		        
		        $this->cookie[$k] = $v;
		    }
		    	
	    }
	    
		$this->lastUrl = $url;
	    
		if ($this->format == "xml") return simplexml_load_string($httpResult["body"]);
		if ($this->format == "json") return json_decode($httpResult["body"], true);
		return $httpResult["body"];
	}	
	
	protected function isSuccess($httpStatusLine) {
		
		return exists($httpStatusLine, "200") || exists($httpStatusLine, "201") || exists($httpStatusLine, "301") || exists($httpStatusLine, "302") ;
	}
	
	
	protected function http($url, $method, $haderString, $bodyDataString) {
		
		$curl = curl_init();
		
//		curl_setopt($curl,CURLOPT_FORBID_REUSE, true);		// TRUE を設定すると、処理が終了した際に明示的に接続を切断します。 接続を再利用しません。
//		curl_setopt($curl,CURLOPT_FRESH_CONNECT, true);		// TRUE を設定すると、キャッシュされている接続を利用せずに 新しい接続を確立します。
		curl_setopt($curl,CURLOPT_HEADER, true);			// TRUE を設定すると、ヘッダの内容も出力します。
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);	// TRUE を設定すると、curl_exec() の返り値を 文字列で返します。通常はデータを直接出力します。
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);  // FALSE を設定すると、cURL はサーバー証明書の検証を行いません
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION, true);	// TRUE を設定すると、サーバーが HTTP ヘッダの一部として送ってくる "Location: " ヘッダの内容をたどります
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, false);  // 1 は SSL ピア証明書に一般名が存在するかどうかを調べます。 2 はそれに加え、その名前がホスト名と一致することを検証します。
		curl_setopt($curl,CURLOPT_FAILONERROR, false);  	// TRUE を設定すると、HTTP で 400 以上のコードが返ってきた際に 処理失敗と判断します。デフォルトでは、コードの値を無視して ページの内容を取得します。
		
		
//		curl_setopt($curl, CURLOPT_COOKIE, "");	// HTTP リクエストにおける "Cookie: " ヘッダの内容。 クッキーが複数ある場合は、セミコロンとスペースで区切られる (例 "fruit=apple; colour=red") ことに注意しましょう。
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method); // HTTP リクエストで "GET" あるいは "HEAD" 以外に 使用するカスタムメソッド。これが有用なのは、"DELETE" やその他のあまり知られていない HTTP リクエストを実行する場合です
		curl_setopt($curl,CURLOPT_POSTFIELDS, $bodyDataString);	// HTTP "POST" で送信するすべてのデータ。 ファイルを送信するには、ファイル名の先頭に @ をつけてフルパスを指定します。
//		curl_setopt($curl,CURLOPT_REFERER, "");	// HTTP リクエストで使用される "Referer: " ヘッダの内容。	
		
		curl_setopt($curl,CURLOPT_URL, $url);	// 取得する URL 。curl_init() でセッションを 初期化する際に指定することも可能です。
//		curl_setopt($curl,CURLOPT_USERAGENT, "");	// HTTP リクエストで使用される "User-Agent: " ヘッダの内容

		$headerArr = array();
		foreach (splitLine($haderString) as $h) {
			$headerArr[] = $h;
		}
		
		curl_setopt($curl,CURLOPT_HTTPHEADER, $headerArr);	// 設定する HTTP ヘッダフィールドの配列。 array('Content-type: text/plain', 'Content-length: 100') 形式。
		curl_setopt($curl,CURLOPT_TIMEOUT_MS, $this->timeout * 1000);	// cURL 関数の実行にかけられる最大のミリ秒数。
		
		
		//	CURLINFO_EFFECTIVE_URL - 直近の有効な URL
		//	CURLINFO_HTTP_CODE - 最後に受け取った HTTP コード
		//	CURLINFO_FILETIME - ドキュメントを取得するのにかかった時間。 取得できなかった場合は -1
		//	CURLINFO_TOTAL_TIME - 直近の伝送にかかった秒数
		//	CURLINFO_NAMELOOKUP_TIME - 名前解決が完了するまでにかかった秒数
		//	CURLINFO_CONNECT_TIME - 接続を確立するまでにかかった秒数
		//	CURLINFO_PRETRANSFER_TIME - 開始からファイル伝送がはじまるまでにかかった秒数
		//	CURLINFO_STARTTRANSFER_TIME - 最初のバイトの伝送がはじまるまでの秒数
		//	CURLINFO_REDIRECT_COUNT - リダイレクト処理の回数
		//	CURLINFO_REDIRECT_TIME - 伝送が始まるまでのリダイレクト処理の秒数
		//	CURLINFO_SIZE_UPLOAD - アップロードされたバイト数
		//	CURLINFO_SIZE_DOWNLOAD - ダウンロードされたバイト数
		//	CURLINFO_SPEED_DOWNLOAD - 平均のダウンロード速度
		//	CURLINFO_SPEED_UPLOAD - 平均のアップロード速度
		//	CURLINFO_HEADER_SIZE - 受信したヘッダのサイズ
		//	CURLINFO_HEADER_OUT - 送信したリクエスト文字列。 これを動作させるには、curl_setopt() をコールする際に CURLINFO_HEADER_OUT オプションを使うようにしておく必要があります。
		//	CURLINFO_REQUEST_SIZE - 発行されたリクエストのサイズ。現在は HTTP リクエストの場合のみ
		//	CURLINFO_SSL_VERIFYRESULT - CURLOPT_SSL_VERIFYPEER を設定した際に要求される SSL 証明書の認証結果
		//	CURLINFO_CONTENT_LENGTH_DOWNLOAD - ダウンロードされるサイズ。 Content-Length: フィールドの内容を取得する
		//	CURLINFO_CONTENT_LENGTH_UPLOAD - アップロードされるサイズ。
		//	CURLINFO_CONTENT_TYPE - 要求されたドキュメントの Content-Type:。 NULL は、サーバーが適切な Content-Type: ヘッダを返さなかったことを示す		

		$ret = curl_exec($curl);
		
		if ($ret === false) {
			errorLog("curl_errno:".curl_errno($curl)." curl_error:".curl_error($curl));
		}
		
		$this->lastResult = $ret;
		$headerSize = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
		curl_close($curl);		
		
		$header = substr($ret, 0, $headerSize);
		$header = splitLine($header);
		$httpStatusLine = arr($header, 0);
		$body = substr($ret, $headerSize);
		
		$this->lastHeader = $header;
		
		return array("header"=>$header, "httpStatusLine"=>$httpStatusLine, "body"=>$body);
	}
	
	

}