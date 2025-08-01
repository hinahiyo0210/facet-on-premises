<?php 


/**
 * blowfishで暗号化を行う
 *
 * @param $pass 暗号化する文字列
 * @return 暗号化済バイナリ
*/
function encryptBlowsish($pass) {

	if ($pass == null) return "";
	

	// 暗号化処理
	$blowfish = new Crypt_Blowfish(CRYPT_SALT);
	$encrypted_data = $blowfish->encrypt($pass);

	return base64_encode($encrypted_data);
}

/**
 * blowfishで複合化を行う
 *
 * @param $encrypted 複合化するバイナリ
 * @return 複合化済バイナリ
 */
function decryptBlowsish($encrypted) {

	if ($encrypted == null) return "";
	
	$encrypted = base64_decode($encrypted);
	
	$blowfish = new Crypt_Blowfish(CRYPT_SALT);
	
	// 復号処理
	$decrypted_data = $blowfish->decrypt($encrypted);
	$decrypted_data = rtrim($decrypted_data, "\0");

	return $decrypted_data;
}



