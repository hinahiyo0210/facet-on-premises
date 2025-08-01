<?php
/**
 * 画像関連の関数
 * 
 * 
 */

/**
 * 指定された拡張子が画像のものである場合にtrueを返す
 * 
 * @param string $ext 拡張子
 * @return boolean true:画像ファイル
 */
function isImageExt($ext) {
	
	return orValue(mb_strtolower($ext), "jpeg", "jpg", "png", "gif");
	
}

/**
 * 指定されたパスが画像のものである場合にtrueを返す
 * @param string $path 画像ファイルパス
 * @return boolean true:画像ファイル
 */
function isImageUrl($path) {
	
	if ($path == null) return false;
	
	$ext = pathinfo($path, PATHINFO_EXTENSION);
	
	return isImageExt($ext);
}


/**
 * 指定ファイルサイズになるまで圧縮
 * @param string $srcPath ファイルのパス
 * @param string $destPath 出力先パス
 * @param int $limitBytes ファイルサイズバイト数
 * @param string $ext 変更する場合は拡張子
 */
function resizeJpegLength($srcPath, $destPath, $limitBytes, $ext = null) {
	
	if ($ext == null) {
		$pathinfo = pathinfo($srcPath);
		$ext = $pathinfo["extension"];
	}
	
	
	if (strtolower($ext) == "jpg" || strtolower($ext) == "jpeg") {
    	$in = imagecreatefromjpeg($srcPath);
	
	} else if (strtolower($ext) == "png") {
    	$in = imagecreatefrompng($srcPath);
	
	} else if (strtolower($ext) == "gif") {
    	$in = imagecreatefromgif($srcPath);
		
    } else {
    	return false;
    }
    
    // jpegとして一時ファイルに保存
	$jpegTmp = createTmpFile("");

	imagejpeg($in, $jpegTmp);
		
    //jpeg圧縮保存を繰り返す
	$i = 0;
	while(filesize($jpegTmp) > $limitBytes) {
		$i++;
		$tmp2 = createTmpFile("");
		imagejpeg($in, $tmp2, 75 - 15 * ($i - 1));
		unlink($jpegTmp);
		$jpegTmp = $tmp2;
	    imagedestroy($in);
		$in = imagecreatefromjpeg($jpegTmp);
	    
		if($i > 10) {
			imagedestroy($in);
			return false;
		}
	}
	
	imagedestroy($in);
	
    // 保存
	copy($jpegTmp, $destPath);
	chmod($destPath, 0666);
	
	unlink($jpegTmp);
		
	return true;
}


/**
 * 画像の解像度を指定値まで下げjpegに変換する
 * @param string $srcPath ファイルのパス
 * @param string $destPath 出力先パス
 * @param string $maxPx 解像度
 * @param string $imageType もとのファイル拡張子
 */
function resizeJpeg($srcPath, $destPath, $maxPx, $imageType = "jpg") {
	
	if (strtolower($imageType) == "jpg" || strtolower($imageType) == "jpeg") {
    	$in = imagecreatefromjpeg($srcPath);
	
	} else if (strtolower($imageType) == "png") {
    	$in = imagecreatefrompng($srcPath);
	
	} else if (strtolower($imageType) == "gif") {
    	$in = imagecreatefromgif($srcPath);
		
    } else {
    	die("不正な拡張子");
    }
	
    // 現在のサイズを取得
	list($width, $height, $type, $attr) = getimagesize($srcPath);
	
	// 縦横のどちらかが指定pxを超えていた場合
	if ($width > $maxPx || $height > $maxPx) {
		
	    // 正方形の場合はどちらも640pxで統一
	    if ($width == $height) {
	        $destWidth = $maxPx;
	        $destHeight = $maxPx;
	        
	    } else if ($width > $height) {
	        $destWidth = $maxPx;
	        $base = round($destWidth / $width, 2);
	        $destHeight = $height * $base;
	    
	    } else if ($width < $height) {
	        $destHeight = $maxPx;
	        $base = round($destHeight / $height, 2);
	        $destWidth = $width * $base;
	    	
	    }
		
	    
	} else {
		// 変更なしの場合はそのまま保存
		imagejpeg($in, $destPath);
    
		// 解放
    	imagedestroy($in);
    	
    	return true;
	}
	   	
    // 画像を生成して上書きコピー
    $out = imagecreatetruecolor($destWidth, $destHeight);
   	imagecopyresized($out, $in, 0, 0, 0, 0, $destWidth, $destHeight, $width, $height);
    
   	if ($destPath === false) {
    	// --------------------- ブラウザ出力
		
		if ($imageType == "jpg" || $imageType == "jpeg") {
    		header('Content-type: image/jpeg');
			ImageJPEG($out);
		} else if ($imageType == "png") {
			header('Content-type: image/gif');
			ImageGIF($out);
		} else if ($imageType == "gif") {
			header('Content-type: image/png');
			ImagePNG($out);
		}
		
   	} else {
   		imagejpeg($out, $destPath);
   	
   	}
			

    
    // 解放
    imagedestroy($in);
    imagedestroy($out);
	
    return true;
}



/**
 * 画像を特定pxに変更後、縦横比率を維持しするように背景色を塗りつぶす
 * @param string $srcPath ファイルのパス
 * @param string $destPath 出力先パス
 * @param string $imageType 画像形式
 * @param int $w 幅
 * @param int $h 高さ
 * @param int $r 色（R)
 * @param int $g 色（G)
 * @param int $b 色（B)
 */
function resizeJpegFillBg($srcPath, $destPath, $imageType, $param_w, $param_h, $r, $g, $b) {
	
	$imageType = mb_strtolower($imageType);
	
	// 現在のサイズを取得
	list($image_w, $image_h, $type, $attr) = getimagesize($srcPath);

	// param_w: パラメータで指定された納めるべきwidth
	// param_h: パラメータで指定された納めるべきheight
	// image_w: 画像そのもののwidth
	// image_h: 画像そのもののheight
	// calc_w: トリミングを考慮しない状態の計算後のwidth
	// calc_h: トリミングを考慮しない状態の計算後のheight
// 	$param_w = 640;
// 	$param_h = 640;
// 	$image_w = 10;
// 	$image_h = 1000;
	$calc_w = 0;
	$calc_h = 0;
	
	if ($param_w == $param_h) {

		if ($image_w > $image_h) {
			// パラメータ指定されたpxに対する現在pxの比率
			$calc_rate = $param_w / $image_w;
			
			$image_rate = $image_h / $image_w;	// width=1に対するheightの割合を求める
			$calc_w = floor($image_w * $calc_rate);
			$calc_h = floor($calc_w * $image_rate);
				
		} else {
			// パラメータ指定されたpxに対する現在pxの比率
			$calc_rate = $param_h / $image_h;
			
			$image_rate = $image_w / $image_h;	// height=1に対するwidthの割合を求める
			$calc_h = floor($image_h * $calc_rate);
			$calc_w = floor($calc_h  * $image_rate);
			
		}
		
	} else if ($param_w < $param_h) {		
		
		// パラメータ指定されたpxに対する現在pxの比率
		$calc_rate = $param_w / $image_w;
		
		$image_rate = $image_h / $image_w;	// width=1に対するheightの割合を求める
		$calc_w = floor($image_w * $calc_rate);
		$calc_h = floor($calc_w * $image_rate);
			
	} else {
		
		// パラメータ指定されたpxに対する現在pxの比率
		$calc_rate = $param_h / $image_h;
		
		$image_rate = $image_w / $image_h;	// height=1に対するwidthの割合を求める
		$calc_h = floor($image_h * $calc_rate);
		$calc_w = floor($calc_h  * $image_rate);
	}
	
	
	if ($imageType == "jpg" || $imageType == "jpeg") {
    	$in = imagecreatefromjpeg($srcPath);
	
	} else if ($imageType == "png") {
    	$in = imagecreatefrompng($srcPath);
	
	} else if ($imageType == "gif") {
    	$in = imagecreatefromgif($srcPath);
		
    } else {
    	return false;
    }
	
    $out = imagecreatetruecolor($param_w, $param_h);
    imagefilledrectangle($out, 0, 0, $param_w, $param_h, imagecolorallocate($out, $r, $g, $b));
    imagecopyresized($out, $in, $param_w / 2 - $calc_w / 2, $param_h / 2 - $calc_h / 2, 0, 0, $calc_w, $calc_h, $image_w, $image_h);
    
    if ($destPath === false) {
    	// --------------------- ブラウザ出力
		
    	setImageResponseHader($imageType);
    	
		if ($imageType == "jpg" || $imageType == "jpeg") {
			ImageJPEG($out);
		} else if ($imageType == "png") {
			ImageGIF($out);
		} else if ($imageType == "gif") {
			ImagePNG($out);
		}
		
    } else {
    	// --------------------- ファイル保存
	    imagejpeg($out, $destPath);
    }
	
    // 解放
    imagedestroy($in);
    imagedestroy($out);
	
    
    return true;
}
    

function setImageResponseHader($imageType) {
	
	if ($imageType == "jpg" || $imageType == "jpeg") {
		header('Content-type: image/jpeg');
	} else if ($imageType == "png") {
		header('Content-type: image/gif');
	} else if ($imageType == "gif") {
		header('Content-type: image/png');
	}
	
}



// 		// param_w: パラメータで指定された納めるべきwidth
// 		// param_h: パラメータで指定された納めるべきheight
// 		// image_w: 画像そのもののwidth
// 		// image_h: 画像そのもののheight
// 		// calc_w: トリミングを考慮しない状態の計算後のwidth
// 		// calc_h: トリミングを考慮しない状態の計算後のheight
// 		$param_w = 640;
// 		$param_h = 640;
// 		$image_w = 10;
// 		$image_h = 1000;
// 		$calc_w = 0;
// 		$calc_h = 0;
		
// 		if ($param_w == $param_h) {

// 			if ($image_w > $image_h) {
// 				// パラメータ指定されたpxに対する現在pxの比率
// 				$calc_rate = $param_w / $image_w;
				
// 				$image_rate = $image_h / $image_w;	// width=1に対するheightの割合を求める
// 				$calc_w = floor($image_w * $calc_rate);
// 				$calc_h = floor($calc_w * $image_rate);
					
// 			} else {
// 				// パラメータ指定されたpxに対する現在pxの比率
// 				$calc_rate = $param_h / $image_h;
				
// 				$image_rate = $image_w / $image_h;	// height=1に対するwidthの割合を求める
// 				$calc_h = floor($image_h * $calc_rate);
// 				$calc_w = floor($calc_h  * $image_rate);
				
// 			}
			
// 		} else if ($param_w < $param_h) {		
			
// 			// パラメータ指定されたpxに対する現在pxの比率
// 			$calc_rate = $param_w / $image_w;
			
// 			$image_rate = $image_h / $image_w;	// width=1に対するheightの割合を求める
// 			$calc_w = floor($image_w * $calc_rate);
// 			$calc_h = floor($calc_w * $image_rate);
				
// 		} else {
			
// 			// パラメータ指定されたpxに対する現在pxの比率
// 			$calc_rate = $param_h / $image_h;
			
// 			$image_rate = $image_w / $image_h;	// height=1に対するwidthの割合を求める
// 			$calc_h = floor($image_h * $calc_rate);
// 			$calc_w = floor($calc_h  * $image_rate);
// 		}

// 		echo "
// param_w:{$param_w}<br />
// param_h:{$param_h}<br />
// <br />
// image_w:{$image_w}<br />
// image_h:{$image_h}<br />
// <br />
// calc_w:{$calc_w}<br />
// calc_h:{$calc_h}<br />
// <br />
// calc_rate:{$calc_rate}
// 		";
// 		die;


// jpegである場合にtrueを返す。
function isJpegImage($fileBin) {
		
	$tmp = createTmpFile($fileBin);
	Globals::$errorDie = false;
	$type = exif_imagetype($tmp);
	Globals::$errorDie = true;
	unlink($tmp);
	
	return $type == IMAGETYPE_JPEG;
}

// pngである場合にtrueを返す。
function isPngImage($fileBin) {

	$tmp = createTmpFile($fileBin);
	Globals::$errorDie = false;
	$type = exif_imagetype($tmp);
	Globals::$errorDie = true;
	unlink($tmp);
	
	return $type == IMAGETYPE_PNG;
}