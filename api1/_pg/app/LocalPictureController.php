<?php 

class LocalPictureController extends BaseController {
	
	public function indexAction(&$form) {
		
		$file = Filter::len($form["f"], 200);
		if (empty($file) || empty($_COOKIE["CloudFront-Policy"]) || empty($_COOKIE["CloudFront-Signature"]) || empty($_COOKIE["CloudFront-Key-Pair-Id"])) {
			warnLog("invalid parameter");
			response400();
		}
		
		// cookieから情報を取得。
		$params = AwsService::decodeLocalPictureSignedUrlParameter($_COOKIE["CloudFront-Policy"], $_COOKIE["CloudFront-Signature"], $_COOKIE["CloudFront-Key-Pair-Id"]);
		if (empty($params)) response400();
		
		// IPアドレスをチェック。
		if (!empty($params["clientIp"])) {
			if (getRemoteAddr() != $params["clientIp"]) {
				warnLog("invalid clientIp");
				response400();
			}
		}
		
		// 期限をチェック。
		if (!empty($params["expires"])) { 
			if (time() > $params["expires"]) {
				warnLog("invalid expires");
				response400();
			}
		}
		
		// パスをチェック。
		$path = $params["path"];
		
		if (mb_strpos($path, "..") !== false || mb_strpos($path, "./") !== false) {
			warnLog("invalid path");
			response400();
		}
		
		if (endsWith($path, "*")) {
			// 前方一致
			$path = excludeSuffix($path, "*");
			
			if (!startsWith($file, $path)) {
				warnLog("invalid param path");
				response400();
			}
			
		} else {
			// 完全一致
			if ($file != $path) {
				warnLog("invalid param path");
				response400();
			}
		}
		
		// ファイルを読み込む。
		$filePath = LOCAL_PICTURE_DIR."/".excludePrefix($file, "/");
		
		if (!file_exists($filePath)) {
			warnLog("file not found $filePath");
			response404();
		}
	

		// ヘッダ設定（キャッシュを有効に。）
		$ext = pathinfo($filePath, PATHINFO_EXTENSION);
		if (strtolower($ext) == "jpg") $ext = "jpeg";
		
		$cacheControl = 8640000;	// 60 * 60 * 24 * 100(日)
		
		header("Content-Type: image/".$ext);
		header("Content-Length: ".filesize($filePath));
		header("Cache-Control: max-age=".$cacheControl);
		header("Expires: ".gmdate("D, d M Y H:i:s", time() + $cacheControl)."GMT");
		header("Pragma: ");
		
		// 出力。
		readfile($filePath);
	}	
	
}
