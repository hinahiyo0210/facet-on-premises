<?php 

/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */
class FileUploadProcessor {
	
	// エラーコード
	const ERROR_NOTFOUND = 10;
	const ERROR_SIZE = 11;
	const ERROR_OTHER = 12;
	const ERROR_EXT = 13;
	const ERROR_READ = 14;
	
	private $name;
	private $required;
	private $maxSizeByExts;
	private $label;
	private $moved = false;
	
	private $fileName;
	private $ext;
	private $fileSize;
	
	private $notFound = false;
	private $errorCode = false;
	
	private $tmpDir;
	private $tmpDirPermission;
	private $tmpFilePermission;
	private $tmpExpiration;
	private $tmpDestPath;
	
	private $deleteTmpByExpirationProcessed = false;	
	
	public function __construct() {
	}
	
	
	public function getTmpDir() {
		return $this->tmpDir;
	}
	public function isNotFound() {
		return $this->notFound;
	}
	
	public function isErrored() {
		return !empty($this->errorCode);
	}
	
	public function settingUpload(
			$name
			, $required
			, $label = false
			, $maxSizeByExts = false
		) {
		
		$this->name = $name;
		$this->required = $required;
		$this->label = $label;
		$this->moved = false;
		
		if ($maxSizeByExts === false) {
			$maxSizeByExts = unserialize(DATA_IMAGE_SIZE_BY_EXTS);
		}
		
		// 拡張子は全て小文字で判定
		$this->maxSizeByExts = array();
		foreach ($maxSizeByExts as $allowExt=>$maxSize) {
			$this->maxSizeByExts[mb_strtolower($allowExt)] = $maxSize; 
		}
		
	}
	
	public function settingMoveTmp(
			$tmpDir
			, $tmpDirPermission = DATA_IMAGE_TMP_DIR_CHMOD
			, $tmpFilePermission = DATA_IMAGE_TMP_FILE_CHMOD
			, $tmpExpiration = DATA_IMAGE_TMP_FILE_EXPIRATION
			, $tmpMaxSize = DATA_IMAGE_TMP_FILE_MAXSIZE
		) {
		
		$this->tmpDir = $tmpDir;
		$this->tmpDirPermission = $tmpDirPermission;
		$this->tmpFilePermission = $tmpFilePermission;
		$this->tmpExpiration = $tmpExpiration;
		$this->tmpMaxSize = $tmpMaxSize;
		
		// テンポラリディレクトリが存在しなければ作成
		if (!file_exists($this->tmpDir)) {
			if (!mkdirs($this->tmpDir, $this->tmpDirPermission)) {
				throw new Exception("tmpディレクトリを作成出来ません。tmpDir[".$this->tmpDir."], tmpDirPermission[".$this->tmpDirPermission."]");
			}
		}
		
		if (!is_dir($this->tmpDir)) throw new Exception("tmpディレクトリのパスがディレクトリパスではありません。tmpDir[".$this->tmpDir."], tmpDirPermission[".$this->tmpDirPermission."]");
		
	}

	public function moveEditingFileToPublish(
			$tmpFileName
			, $destPath
			, $destDirPermission = DATA_IMAGE_DIR_CHMOD
			, $destFilePermission = DATA_IMAGE_FILE_CHMOD
		) {
		
		if ($this->tmpDir == null) throw new Exception("settingMoveTmpでtmp情報を設定してください。");
		
		$tmpPath = $this->tmpDir."/".getSafeFileName(Filter::len($tmpFileName, 100));
		
		if (!file_exists($tmpPath)) {
			warnLog("移動元ファイルが存在しません。[".$tmpPath."]");
			return false;
		}
		
		$destDir = pathinfo($destPath, PATHINFO_DIRNAME);
		if (!file_exists($destDir)) {
			if (!mkdirs($destDir, $destDirPermission)) {
				throw new Exception("ディレクトリが作成出来ません。destDir[".$destDir."], destDirPermission[".$destDirPermission."]");
			}
		}
		
		if (!copy($tmpPath, $destPath)) {
			throw new Exception("ファイルのコピーに失敗しました。tmpPath[".$tmpPath."], destPath[".$destPath."]");
		}
		
		if (!chmod($destPath, $destFilePermission)) {
			throw new Exception("ファイルのchmodに失敗しました。destPath[".$destPath."], destFilePermission[".$destFilePermission."]");
		}
		
		unlink($tmpPath);
		return true;
	}
			
	
	public function copyEditingFileToTmp($srcPath) {
		if ($this->tmpDir == null) throw new Exception("settingMoveTmpでtmp情報を設定してください。");
		
		if (!file_exists($srcPath)) {
			warnLog("指定されたファイルは存在しません。[".$srcPath."]");
			return;
		}
		
		$this->deleteTmpByExpiration();
		
		$ext = getFileExtension($srcPath);
		
		// テンポラリファイル名を決定
 		$tmpDestPath = "";
 		$tmpFileName = ""; 		
 		do {
 			$tmpFileName = getRandomPassword(10).".".$ext;
 			$tmpDestPath = $this->tmpDir."/".$tmpFileName;
 		} while (file_exists($tmpDestPath));
 		
 		// コピー
 		if (!copy($srcPath, $tmpDestPath)) {
 			throw new Exception("ファイルのコピーに失敗しました。srcPath[".$srcPath."], tmpDestPath[".$tmpDestPath."]");
 		}
 		
		// chmod
		if (!chmod($tmpDestPath, $this->tmpFilePermission)) {
			throw new Excepton("chmodに失敗しました。tmpDestPath[".$tmpDestPath."], tmpFilePermission[".$this->tmpFilePermission."]");
		}

		return $tmpFileName;
	}
	
	
	public function deleteTmp($fileName) {
		if ($this->tmpDir == null) throw new Exception("settingMoveTmpでtmp情報を設定してください。");
		
		$path = $this->tmpDir."/".getSafeFileName(Filter::len($fileName, 100));
		
		Globals::$errorDie = false;
		@unlink($path);
		Globals::$errorDie = true;
		
		if (file_exists($path)) {
			warnLog("tmpファイル[".$path."]の削除に失敗しました。");
			return false;
		}
		
		return true;
	}
		
	public function deleteTmpByExpiration() {
		
		if ($this->tmpDir == null) throw new Exception("settingMoveTmpでtmp情報を設定してください。");
		
		if ($this->deleteTmpByExpirationProcessed) return;
		
		$deleteLimit = strtotime($this->tmpExpiration);
		
		$dir = dir($this->tmpDir);
	
		$deletedFilePathes = array();
		$erroredFilePathes = array();

		// 有効期限による検証
		while ($file = $dir->read()) {
			
			$path = $this->tmpDir."/".$file;
			if (is_dir($path)) continue;
			
			if (filemtime($path) >= $deleteLimit) {
				continue;
			}

			Globals::$errorDie = false;
			@unlink($path);
			Globals::$errorDie = true;
			
			if (file_exists($path)) {
				$erroredFilePathes[] = $path;
				warnLog("tmpファイル[".$path."]の削除に失敗しました。");
				
			} else {
				$deletedFilePathes[] = $path;
				infoLog("tmpファイル[".$path."]は日付が古いため削除されました。");
			}
		}
		

		// ファイルサイズによる検証
		for ($i = 0; $i < 100; $i++) {	// 設定情報不備の場合による無限ループ防止
			$size = getDirectoryFileSize($this->tmpDir);
			if ($size < $this->tmpMaxSize) break;
			
			infoLog("tmpファイルディレクトリ[".$this->tmpDir."]は容量不足のためファイル削除を実施します。許容サイズ[".$this->tmpMaxSize."], 現サイズ[".$size."]");
			
			// ファイルを日付の古いものから10件ずつ削除
			$files = $this->getDirectoryFilesWithModified($this->tmpDir);
			asort($files, SORT_NUMERIC);	// 更新日時（数値）で昇順ソート
			$cnt = 1;
			foreach ($files as $path=>$time) {
				if ($cnt > 10) break;
				$cnt++;
				
				unlink($path);
				infoLog("tmpファイル[".$path."]は容量不足解決のために削除されました。");
			}
			
		}
		
		
		$this->deleteTmpByExpirationProcessed = true;
		return array("deletedFilePathes"=>$deletedFilePathes, "erroredFilePathes"=>$erroredFilePathes);
	}
	
	/**
	 * ディレクトリ内のファイル名と更新日時のマップを取得
	 * @param string $dirPath パス
	 */
	private function getDirectoryFilesWithModified($dirPath) {
		$dir = dir($dirPath);
	
		$ret = array();
		
		while ($file = $dir->read()) {
			
			$path = $dirPath."/".$file;
	
			if (is_dir($path)) {
				continue;
			}
			
			$ret[$path] = filemtime($path);
		}
		
		return $ret;
	}
		

	
	
	public function moveUploadFileToTmp() {
		
		if ($this->name == null) throw new Exception("settingUploadでupload情報を設定してください。");
		if ($this->tmpDir == null) throw new Exception("settingMoveTmpでtmp情報を設定してください。");
		if ($this->moved) throw new Exception("既にこのファイルは移動済みです。name[".$this->name."]");
		
		$this->deleteTmpByExpiration();
		
		// ファイルの検証
		$validateResult = $this->validateFile();

		// エラーがあれば終了
		if (!$validateResult) return false;
		
		// 必須指定が無く、ファイルが無しの場合は終了
		if (!$this->required && $this->notFound) return false;
		
		// テンポラリファイル名を決定
 		$this->tmpDestPath = "";
 		$tmpFileName = ""; 		
 		do {
 			$tmpFileName = getRandomPassword(10).".".$this->ext;
 			$this->tmpDestPath = $this->tmpDir."/".$tmpFileName;
 		} while (file_exists($this->tmpDestPath));
		
		// ファイルを移動
		if (!move_uploaded_file($_FILES[$this->name]["tmp_name"], $this->tmpDestPath)) {
			throw new Exception("一時ファイルを保存できません。name[".$this->name."], tmpDir[".$this->tmpDir."], tmpDirPermission[".$this->tmpDirPermission."], tmpDestPath[".$this->tmpDestPath."]");
		}

		// chmod
		if (!chmod($this->tmpDestPath, $this->tmpFilePermission)) {
			throw new Excepton("chmodに失敗しました。name[".$this->name."], tmpDir[".$this->tmpDir."], tmpDirPermission[".$this->tmpDirPermission."], tmpDestPath[".$this->tmpDestPath."], tmpFilePermission[".$this->tmpFilePermission."]");
		}
		
		$this->moved = true;
		return $tmpFileName;
	}
	
	// エラー情報を設定
	private function setError($errorCode, $msg) {
		
		$this->errorCode = $errorCode;
		
		if ($this->label === false) return;
		
		$msg = mb_str_replace($msg, "[label]", $this->label);
		Errors::add($this->name, $msg);
	}
	
	
	// アップロードファイルの検証
	public function validateFile() {
		
		if ($this->name == null) throw new Exception("settingUploadでupload情報を設定してください。");

		if (!isset($_FILES[$this->name]['error']) || !is_int($_FILES[$this->name]['error'])) {
			
			$nativeError = error_get_last();
			if (arr($nativeError, "type") == 2 && startsWith(arr($nativeError, "message"), "POST Content-Length of")) {
				warnLog($nativeError);
				$this->setError(self::ERROR_SIZE, "[label]のファイルサイズが大きすぎます");
				Globals::$errored = true;
				return false;
			}
			
			$this->notFound = true;
			if ($this->required) {
				$this->setError(self::ERROR_NOTFOUND, "[label]がアップロードされていません");
				return false;
			} else {
				return true;
			}
		}
		
		$file = $_FILES[$this->name];
		
	    switch ($file['error']) {
	        case UPLOAD_ERR_OK: // OK
	            break;
	        case UPLOAD_ERR_NO_FILE:   // ファイル未選択
				$this->notFound = true;
	        	if ($this->required) {
					$this->setError(self::ERROR_NOTFOUND, "[label]がアップロードされていません");
		        	return false;
				} else {
					return true;
				}
            case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
	        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
	        	$this->setError(self::ERROR_SIZE, "[label]のファイルサイズが大きすぎます");
				return false;
	        default:
	        	$this->setError(self::ERROR_OTHER, "[label]でその他のエラーが発生しました");
				return false;
	     }
		
		if (!file_exists($file["tmp_name"])) {
        	$this->setError(self::ERROR_READ, "[label]のファイルを読み込む事が出来ません");
            return false;
		}
	     
		// 安全な拡張子を取得
		$this->ext = getFileExtension($file["name"]);
		
		// 安全なファイル名を取得
		$this->fileName = Filter::len(getSafeFileName($file["name"]), 100);
		
		// ファイルサイズはtmpから直接取得
		$this->fileSize = filesize($file["tmp_name"]);
		
		// 拡張子チェック
		if (!isset($this->maxSizeByExts[$this->ext])) {
        	$this->setError(self::ERROR_EXT, "[label]のファイルの種類が不正です(".$this->ext.")\n拡張子は「".join('/', array_keys($this->maxSizeByExts))."」でアップロードして下さい。");
			return false;
		}
		
		// サイズ上限のオーバーチェック
	    $maxSize = $this->maxSizeByExts[$this->ext];
		
		if ($file['size'] > $maxSize) {
        	$this->setError(self::ERROR_SIZE, "[label]のファイルサイズが大きすぎます\n「".formatNumber($maxSize)."」bytesまでのファイルのみ許可されています。");
			return false;
		}

		if (empty($this->fileSize)) {
            $this->setError(self::ERROR_NOTFOUND, "[label]がアップロードされていません");
            return false;
		}
		
		if ($this->fileSize > $maxSize) {
			$this->setError(self::ERROR_SIZE, "[label]のファイルサイズが大きすぎます\n「".formatNumber($maxSize)."」bytesまでのファイルのみ許可されています。");
			return false;
		}
		
		return true;
	}

	

	
}
