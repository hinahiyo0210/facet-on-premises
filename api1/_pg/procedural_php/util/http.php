<?php 


function getHtmlDocument($url, $postData = false, $timeout = 60, &$cookie = array(), $header = array()) {
	
	// データ取得
	$httpData = getHttpContent($url, $postData, $timeout, $header, $cookie);
		
    //日本語を数値文字参照に変換する(文字化け対策) 
	$encode = mb_detect_encoding($httpData, 'ASCII, JIS, UTF-8, EUC-JP, SJIS'); 
	$httpData = mb_convert_encoding($httpData, 'HTML-ENTITIES', $encode); 
		
	//HTMLをパースする 
	$doc = new DOMDocument(); 
    Globals::$errorIgnore = true;	
	$loadSuccess = $doc->loadHTML($httpData); 
	Globals::$errorIgnore = false;	
	
	if ($loadSuccess === false) {
		trigger_error("HTMLパースに失敗[".$url."]");
	}
	
	return $doc;
	
	
	
}




