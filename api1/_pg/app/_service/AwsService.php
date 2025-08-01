<?php 

require_once(DIR_LIB.'/aws/aws-autoloader.php');
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use function Aws\dir_iterator;

class AwsService {
	
	// ローカルモード用の暗号キー
 	const LOCAL_PICTURE_CRYPT_KEY = "j6yUaDa7CvSErFRH91wl";
	
	// S3のパスを取得する。
	public static function getS3ObjectFilePath($primary_id, $regist_time, $appendRand = false) {
		
		if ($appendRand) {
			$hash = md5($primary_id.mt_rand(0, 99999999)."tbpUHhg4aChhzPLy");
		} else {
			$hash = md5($primary_id."tbpUHhg4aChhzPLy");
		}
		$hash = strrev($hash);
		
		// ハッシュ衝突を避けるために本来のidを16進数で潜ませる。
		$hex = dechex($primary_id);
		
		$hash = substr($hash, 0, 20 - strlen($hex));
		
		$hash1 = substr($hash, 0, 3);
		$hash2 = substr($hash, 3, strlen($hash));
		
		$objectName = $hash1.$hex.$hash2;
		
		// パスを作成。
		$sec = strtotime($regist_time);
		$objectPath = "/".date("Y", $sec)."/".date("md", $sec)."/".$objectName.".jpg";
		$path = $objectPath;
		
		return $path;
	}
	
	
	
	// S3にファイルが存在しているかを確認する。
	public static function existsS3RecogPicture($path) {
		
		// ------------ ローカルモード
		if (!ENABLE_AWS) {
			$localPath = LOCAL_PICTURE_DIR."/".$path;
			return file_exists($localPath);
		}		

		
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => "ap-northeast-1"
		]);
		
		try {
			
			// そのまま該当するメソッドは無いためgetObjectTaggingを使い検証する。
			$r = $s3->getObjectTagging([
			    'Bucket' => S3_BUCKET,
				'Key'    => $path
			]);
			
			if (empty($r["@metadata"]["statusCode"])) return false;
			
			return $r["@metadata"]["statusCode"] == 200;
			
		} catch (S3Exception $e) {
			return false;
		}
		
	}
	
	
	// S3に認識された写真ファイルを削除する。
	// 何かしらのエラーが発生し、継続すべきでは無い場合にはfalseを返す。
	// https://docs.aws.amazon.com/ja_jp/AmazonS3/latest/dev/DeletingOneObjectUsingPHPSDK.html
	public static function deleteS3RecogPicture($device, $recog_log_id, $recog_time) {
		
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => S3_REGION
		]);
		
		try {
			
			$objectPath = self::getS3ObjectFilePath($recog_log_id, $recog_time);
			
			$path = $device["s3_path_prefix"]."/recog".$objectPath;
			
			// 存在していないファイルなのであればスキップする。
			if (!self::existsS3RecogPicture($path)) {
				warnLog("削除対象のファイルがS3上に存在していません。 $path");
				
			} else {
				infoLog("S3からファイルを削除。 ".$path);
				
				if (ENABLE_AWS) {
					$result = $s3->deleteObject([
						'Bucket' => S3_BUCKET,
						'Key'    => $path
					]);
				} else {
					// ローカルモード
					$localPath = LOCAL_PICTURE_DIR."/".$path;
					if (file_exists($localPath)) unlink($localPath);
					$result = ["localPath"=>$localPath];
				}
				
				infoLog("S3ファイル削除処理を完了。".json_encode($result, JSON_UNESCAPED_UNICODE));
			}
			
					
		} catch (S3Exception $e) {
			errorLog($e->getMessage());
			errorLog(json_encode($e, JSON_UNESCAPED_UNICODE));
		}
		
		// 削除後にファイルが存在していなければOK。
		if (self::existsS3RecogPicture($path)) {
			infoLog("S3ファイル削除が正常に完了しません。ファイルが存在しています。 ".$path);
			return false;
		}

		// ファイルが存在しなくなっているのであれば、DBにもその旨を登録する。
		$param = ["recog_log_id"=>$recog_log_id];
		DB::update("
			update 
				t_recog_log 
			set 
				picture_flag = 0
				, s3_object_path = null
			where
				recog_log_id = {recog_log_id}
		", $param);
		DB::commit();	// S3に対する処理後にはすぐにコミット。
		
		return true;
	}
	
	// S3に保存された認識ログ画像ファイルを取得する。
	// https://docs.aws.amazon.com/ja_jp/AmazonS3/latest/dev/RetrieveObjSingleOpPHP.html
	public static function downloadS3RecogPicture($device, $recog_log_id, $recog_time) {
		
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => S3_REGION
		]);
		
		try {
			
			$objectPath = self::getS3ObjectFilePath($recog_log_id, $recog_time);
			
			$path = $device["s3_path_prefix"]."/recog".$objectPath;
		
			infoLog("S3ダウンロードを開始。 ".$path);
			
			if (ENABLE_AWS) {
				// ------------ Upload data.
				$result = $s3->getObject([
					'Bucket' => S3_BUCKET,
					'Key'    => $path
				]);
				
			} else {
				// ------------ ローカルモード
				$localPath = LOCAL_PICTURE_DIR."/".$path;
				$bin = file_get_contents($localPath); 
				if (!$bin)  {
					throw new S3Exception("file_put_contents ERROR $localPath ", "");
				}
				$result = ["Body"=>$bin];
			}
			
			infoLog("ダウンロードを完了。 ");
			
		} catch (S3Exception $e) {
			errorLog($e->getMessage());
			errorLog(json_encode($e, JSON_UNESCAPED_UNICODE));
			return false;
		}
		
		return $result["Body"];
	}
	

	// S3に認識された写真ファイルをアップする。
	// https://docs.aws.amazon.com/ja_jp/AmazonS3/latest/dev/UploadObjSingleOpPHP.html
	public static function uploadS3RecogPicture($device, $recog_log_id, $recog_time, $body) {
		
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => S3_REGION
		]);
		
		try {
			
			$objectPath = self::getS3ObjectFilePath($recog_log_id, $recog_time);
			
			$path = $device["s3_path_prefix"]."/recog".$objectPath;
		
			infoLog("S3アップロードを開始。 ".$path);
			
			if (ENABLE_AWS) {
				// ------------ Upload data.
				$result = $s3->putObject([
					'Bucket' => S3_BUCKET,
					'Key'    => $path,
					'Body'   => $body
				]);
				
			} else {
				// ------------ ローカルモード
				$localPath = LOCAL_PICTURE_DIR."/".$path;
				$dir = pathinfo($localPath, PATHINFO_DIRNAME);
				if (!file_exists($dir)) mkdirs($dir);
				if (!file_put_contents($localPath, $body))  {
					throw new S3Exception("file_put_contents ERROR $localPath ", "");
				}
				$result = ["localPath"=>$localPath];
			}
			
			infoLog("アップロードを完了。 ".json_encode($result, JSON_UNESCAPED_UNICODE));
			
			
		} catch (S3Exception $e) {
			errorLog($e->getMessage());
			errorLog(json_encode($e, JSON_UNESCAPED_UNICODE));
			return false;
		}
		
		$param = ["recog_log_id"=>$recog_log_id, "s3_object_path"=>$objectPath];
		DB::update("
			update 
				t_recog_log 
			set 
				picture_flag = 1
				, s3_object_path = {s3_object_path}
			where
				recog_log_id = {recog_log_id}
		", $param);
		DB::commit();	// すぐにコミット。
					
		return true;
	}
	
	
	// S3に人物の写真をアップする。
	// https://docs.aws.amazon.com/ja_jp/AmazonS3/latest/dev/UploadObjSingleOpPHP.html
	public static function uploadS3PersonPicture($contractor, $person_id, $create_time, $body) {
		
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => S3_REGION
		]);
		
		try {
			
			// ログについてはキャッシュされても良いが、人物についてはファイルをキャッシュさせたく無いので、
			// 毎回別のパスとなるようにする。
			$objectPath = self::getS3ObjectFilePath($person_id, $create_time, true);
			
			$path = $contractor["s3_path_prefix"].$objectPath;
		
			infoLog("S3アップロードを開始。 ".$path);
			
			if (ENABLE_AWS) {
				// ------------ Upload data.
				$result = $s3->putObject([
					'Bucket' => S3_BUCKET,
					'Key'    => $path,
					'Body'   => $body
				]);
				
			} else {
				// ------------ ローカルモード
				$localPath = LOCAL_PICTURE_DIR."/".$path;
				$dir = pathinfo($localPath, PATHINFO_DIRNAME);
				if (!file_exists($dir)) mkdirs($dir);
				if (!file_put_contents($localPath, $body))  {
					throw new S3Exception("file_put_contents ERROR $localPath ", "");
				}
				$result = ["localPath"=>$localPath];
			}
			
			infoLog("アップロードを完了。 ".json_encode($result, JSON_UNESCAPED_UNICODE));
			
			
		} catch (S3Exception $e) {
			errorLog($e->getMessage());
			errorLog(json_encode($e, JSON_UNESCAPED_UNICODE));
			return false;
		}
		

		$param = ["person_id"=>$person_id, "s3_object_path"=>$objectPath];
		DB::update("
			update 
				t_person
			set 
				s3_object_path = {s3_object_path}
			where
				person_id = {person_id}
		", $param);
		
		return true;
	}
	
	// S3に保存された人物画像ファイルを取得する。
	public static function downloadS3PersonPicture($contractor, $objectPath, $create_time) {
		
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => S3_REGION
		]);
		
		try {
			
			$path = $contractor["s3_path_prefix"].$objectPath;
			
			infoLog("S3ダウンロードを開始。 ".$path);
			
			if (ENABLE_AWS) {
				// ------------ Upload data.
				$result = $s3->getObject([
					'Bucket' => S3_BUCKET,
					'Key'    => $path
				]);
				
			} else {
				// ------------ ローカルモード
				$localPath = LOCAL_PICTURE_DIR."/".$path;
				if (!file_exists($localPath)) {
					return false;
				}
				$bin = file_get_contents($localPath); 
				if (!$bin)  {
					throw new S3Exception("file_put_contents ERROR $localPath ", "");
				}
				$result = ["Body"=>$bin];
			}
			
			infoLog("ダウンロードを完了。 ");
			
		} catch (S3Exception $e) {
			errorLog($e->getMessage());
			errorLog(json_encode($e, JSON_UNESCAPED_UNICODE));
			return false;
		}
		
		return $result["Body"];
	}
	

	
	// S3に設置された顔画像ファイルを削除する。
	// 何かしらのエラーが発生し、継続すべきでは無い場合にはfalseを返す。
	// https://docs.aws.amazon.com/ja_jp/AmazonS3/latest/dev/DeletingOneObjectUsingPHPSDK.html
	public static function deleteS3PersonPicture($contractor, $person_id, $objectPath, $create_time) {
		
		$s3 = new S3Client([
			'version' => 'latest',
			'region'  => S3_REGION
		]);
		
		try {
			$path = $contractor["s3_path_prefix"].$objectPath;
		
			// 存在していないファイルなのであればスキップする。
			if (!self::existsS3RecogPicture($path)) {
				warnLog("削除対象のファイルがS3上に存在していません。 $path");
				
			} else {
				infoLog("S3から顔ファイルを削除。 ".$path);
				
				if (ENABLE_AWS) {
					$result = $s3->deleteObject([
						'Bucket' => S3_BUCKET,
						'Key'    => $path
					]);
				} else {
					// ローカルモード
					$localPath = LOCAL_PICTURE_DIR."/".$path;
					if (file_exists($localPath)) unlink($localPath);
					$result = ["localPath"=>$localPath];
				}
				
				infoLog("S3顔ファイル削除処理を完了。".json_encode($result, JSON_UNESCAPED_UNICODE));
			}
			
					
		} catch (S3Exception $e) {
			errorLog($e->getMessage());
			errorLog(json_encode($e, JSON_UNESCAPED_UNICODE));
		}
		
		// 削除後にファイルが存在していなければOK。
		if (self::existsS3RecogPicture($path)) {
			infoLog("S3顔ファイル削除が正常に完了しません。ファイルが存在しています。 ".$path);
			return false;
		}
		
		$param = ["person_id"=>$person_id];
		DB::update("
			update 
				t_person
			set 
				s3_object_path = null
			where
				person_id = {person_id}
		", $param);
		
		return true;
	}
	
	
	
	
	// 署名付きCookieを発行する。
	public static function createS3SignedCookie($path, $expiresTimeSec = null, $clientIp = null) {
		
		$urlParam = AwsService::createS3SignedUrlParameter($path, $expiresTimeSec, $clientIp);
		
		$ret = [];
		foreach (explode("&", $urlParam) as $param) {
			$arr = explode("=", $param);
			$ret["CloudFront-".$arr[0]] = $arr[1]; 
		}
		
		return $ret;
	}

	// ローカルモード向けの署名付きURLのパラメータを複合化する。
	public static function decodeLocalPictureSignedUrlParameter($policy, $signature, $kPId) {
		
		$signature = urldecode($signature);
		
		// チェックサム確認。
		if (sha1(self::LOCAL_PICTURE_CRYPT_KEY.$signature) != $kPId) {
			warnLog("invalid check1");
			return false;
		}
		if (sha1($signature.$signature.self::LOCAL_PICTURE_CRYPT_KEY) != $policy) {
			warnLog("invalid check2");
			return false;
		}
		
		$signature = str_replace("う", "=", $signature);
		
		// 複合化
		$param = openssl_decrypt($signature, "aes-256-ecb", self::LOCAL_PICTURE_CRYPT_KEY);
		if (empty($param)) {
			warnLog("invalid decrypt");
			return false;
		}
		
		$arr = explode("@", $param);
		
		$path     = $arr[1];
		$expires  = $arr[3];
		$clientIp = $arr[5];
		
		return ["path"=>$path, "expires"=>$expires, "clientIp"=>$clientIp];
	}
	
	
	// ローカルモード向けの署名付きURLのパラメータを発行する。
	public static function createLocalPictureSignedUrlParameter($path, $expires, $clientIp = null) {
		
		$val = getRandomPassword(5)."@".$path."@".getRandomPassword(5)."@".$expires."@".getRandomPassword(5)."@".$clientIp;
		$encd = openssl_encrypt($val, "aes-256-ecb", self::LOCAL_PICTURE_CRYPT_KEY);
		$encd = str_replace("=", "う", $encd);
		infoLog($encd);
		
		$check1 = sha1(self::LOCAL_PICTURE_CRYPT_KEY.$encd);
		$check2 = sha1($encd.$encd.self::LOCAL_PICTURE_CRYPT_KEY);
		
		return "Policy=".$check2."&Signature=".urlencode($encd)."&Key-Pair-Id=".$check1;
	}
 		     
	

	// 署名付きURLのパラメータを発行する。
	public static function createS3SignedUrlParameter($path, $expiresTimeSec = null, $clientIp = null) {
		
		if (!startsWith($path, "/")) {
			$path = "/".$path;
		}
		$resourcePath = CLOUDFRONT_URL.$path;

		if (!empty($expiresTimeSec)) {
			$expires = time() + $expiresTimeSec;
		} else {
			$expires = time() + CLOUDFRONT_DEFAULT_EXPIRES_SEC;
		}

		// ローカルモード
 		if (!ENABLE_AWS) {
 			return self::createLocalPictureSignedUrlParameter($path, $expires, $clientIp);
 		}

		$private_key_filename = CLOUDFRONT_PEM_PATH;
		$key_pair_id = CLOUDFRONT_PEM_KEY_PAIR_ID;


// 		$canned_policy_stream_name = self::get_canned_policy_stream_name($resourcePath, $private_key_filename, $key_pair_id, $expires);

		$policy =
		'{'.
		    '"Statement":['.
		        '{'.
		            '"Resource":"'. $resourcePath . '",'.
		            '"Condition":{'.
		               (empty(!$clientIp) ? '"IpAddress":{"AWS:SourceIp":"' . $clientIp . '/32"},' :  '').
		                '"DateLessThan":{"AWS:EpochTime":' . $expires . '}'.
		            '}'.
		        '}'.
		    ']' .
		    '}';
		$custom_policy_stream_name = self::get_custom_policy_stream_name($resourcePath, $private_key_filename, $key_pair_id, $policy);
	
		$arr = explode("?", urldecode($custom_policy_stream_name));
		
		return $arr[1];
	}

	// -----------------------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------------------
	// https://docs.aws.amazon.com/ja_jp/AmazonCloudFront/latest/DeveloperGuide/CreateURL_PHP.html
	private static function rsa_sha1_sign($policy, $private_key_filename) {
	    $signature = "";
	
	    // load the private key
	    $fp = fopen($private_key_filename, "r");
	    $priv_key = fread($fp, 8192);
	    fclose($fp);
	    $pkeyid = openssl_get_privatekey($priv_key);
	
	    // compute signature
	    openssl_sign($policy, $signature, $pkeyid);
	
	    // free the key from memory
	    openssl_free_key($pkeyid);
	
	    return $signature;
	}
	
	private static function url_safe_base64_encode($value) {
	    $encoded = base64_encode($value);
	    // replace unsafe characters +, = and / with the safe characters -, _ and ~
	    return str_replace(
	        array('+', '=', '/'),
	        array('-', '_', '~'),
	        $encoded);
	}
	
	private static function create_stream_name($stream, $policy, $signature, $key_pair_id, $expires) {
	    $result = $stream;
	    // if the stream already contains query parameters, attach the new query parameters to the end
	    // otherwise, add the query parameters
	    $separator = strpos($stream, '?') == FALSE ? '?' : '&';
	    $path = "";
	    
	    // the presence of an expires time means we're using a canned policy
	    if($expires) {
	        $result .= $path . $separator . "Expires=" . $expires . "&Signature=" . $signature . "&Key-Pair-Id=" . $key_pair_id;
	    }
	    // not using a canned policy, include the policy itself in the stream name
	    else {
	        $result .= $path . $separator . "Policy=" . $policy . "&Signature=" . $signature . "&Key-Pair-Id=" . $key_pair_id;
	    }
	
	    // new lines would break us, so remove them
	    return str_replace('\n', '', $result);
	}
	
	private static function encode_query_params($stream_name) {
	    // Adobe Flash Player has trouble with query parameters being passed into it,
	    // so replace the bad characters with their URL-encoded forms
	    return str_replace(
	        array('?', '=', '&'),
	        array('%3F', '%3D', '%26'),
	        $stream_name);
	}
	
	private static function get_canned_policy_stream_name($video_path, $private_key_filename, $key_pair_id, $expires) {
	    // this policy is well known by CloudFront, but you still need to sign it, since it contains your parameters
	    $canned_policy = '{"Statement":[{"Resource":"' . $video_path . '","Condition":{"DateLessThan":{"AWS:EpochTime":'. $expires . '}}}]}';
	    // the policy contains characters that cannot be part of a URL, so we base64 encode it
	    $encoded_policy = self::url_safe_base64_encode($canned_policy);
	    // sign the original policy, not the encoded version
	    $signature = self::rsa_sha1_sign($canned_policy, $private_key_filename);
	    // make the signature safe to be included in a URL
	    $encoded_signature = self::url_safe_base64_encode($signature);
	
	    // combine the above into a stream name
	    $stream_name = self::create_stream_name($video_path, null, $encoded_signature, $key_pair_id, $expires);
	    // URL-encode the query string characters to support Flash Player
	    return self::encode_query_params($stream_name);
	}
	
	private static function get_custom_policy_stream_name($video_path, $private_key_filename, $key_pair_id, $policy) {
	    // the policy contains characters that cannot be part of a URL, so we base64 encode it
	    $encoded_policy = self::url_safe_base64_encode($policy);
	    // sign the original policy, not the encoded version
	    $signature = self::rsa_sha1_sign($policy, $private_key_filename);
	    // make the signature safe to be included in a URL
	    $encoded_signature = self::url_safe_base64_encode($signature);
	
	    // combine the above into a stream name
	    $stream_name = self::create_stream_name($video_path, $encoded_policy, $encoded_signature, $key_pair_id, null);
	    // URL-encode the query string characters to support Flash Player
	    return self::encode_query_params($stream_name);
	}
	// -----------------------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------------------
	
	
	
}