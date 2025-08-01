<?php
// サーバーのIP取得
exec("hostname -I", $output);
$server_addr = (isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : explode(" ", $output[0])[0];

// --------------------------------------- [PHP基本情報]
// 実行可能時間
define("TIME_LIMIT", 90);

// エンコード
define("BASE_ENCODE", "UTF-8");

// 暗号化のSALT
define("CRYPT_SALT", 'uCwiteJ9miFzkZ');

// このシステムの運用開始年月
define("SYSTEM_BEGIN_YM", "2020/09");

define("ENABLE_AWS"     , false); //AWSの場合【true】、オンプレの場合【false】

define("ENABLE_SSL"     , false);

// --------------------------------------- [DB関連]
// ドライバ
define("DB_DRIVER", "mysql");

// DB接続情報
define("DB_HOST", "localhost");

// DB名
define("DB_NAME", "ds");

// DBユーザ
define("DB_USER", "admin");

// DBパスワード
if (ENABLE_AWS) {
    define("DB_PASS", "YAvM1Y0DgsEVjwxA");
} else {
    define("DB_PASS", "un4FAqaMiXAh");
}

// 文字コード変換が必要な場合には、その文字コード。
define("DB_MB_CNV", "");

// --------------------------------------- [ロガー]
// デフォルトのロガー
define("LOGGER_DEFAULT", "ui");

// --------------------------------------- [ユーザ画面セッション]
// セッションタイムアウト（秒）
define("SESSION_TIMEOUT_FRONT", 60 * 30);		// 30分

// セッション名：ユーザ
define("SESSION_NAME_FRONT", "front-user");

// セッションのsecure cookie：ユーザ
define("SESSION_COOKIE_SECURE_FRONT", 0);

// セッションCookie名：ユーザ
define("SESSION_COOKIE_NAME_FRONT", "WoEVhxg0Ow4GAw");

// --------------------------------------- [ユーザ画面のログイン関連/その他設定]
// 自動ログインcookie名
define("AUTO_LOGIN_COOKIE", "XUbDEXYnp6med9");

// 自動ログインcookieの有効期限(日数)
define("AUTO_LOGIN_EXP_DAYS", 30 * 6);	// 6ヶ月間

// --------------------------------------- [メンテナンスモード]
// メンテナンスモード切り替え
define("MAINTE_MODE", false);

// メンテナンス時間の目安
define("MAINTE_SPAN", date("Y年m月d日")." 17:30から18:00までの間");

// メンテナンスモードを解除する隠しコマンド
define("MAINTE_IGNORE_COMMAND", "9999999");

// --------------------------------------- [WS-API]
// WSアドレス(ブラウザから利用。リアルタイムモニタ用)
if (ENABLE_SSL) {
    define("WS_ADDR", "wss://".$server_addr."/ws/notice/[TOKEN]");
} else {
    define("WS_ADDR", "ws://".$server_addr.":8080/ws/notice/[TOKEN]");
}

// 応答待ちタイムアウト(ミリ秒) ※オンプレミスの場合【60000】、AWSの場合【30000】
if (ENABLE_AWS) {
    define("WS_API_TIMEOUT_MS", 30000);
} else {
    define("WS_API_TIMEOUT_MS", 60000);
}

// 通信試行回数。
// 3回の場合、仮にWS3まで設定が入っている場合、優先順位としては、WS1, WS2, WS3となる。
// 仮にWS1のみに設定が入っているデバイスの場合には、WS1(1回目), WS1(2回目), WS1(3回目)となる。
define("WS_API_TRY_COUNT", 3);

// APB制御のtoDeviceにおける通信試行回数。
define("APB_WS_API_TRY_COUNT", 1);

// 排他制御におけるロック取得試行回数
define("CHECK_EXCLUSION_TRY_COUNT", 5);

// URLのプレフィックス
define("URL_PREFIX", "/ui1");

// 人物登録時にデバイスチェック（checkPicture）を実行するかどうか。
//define("ENABLE_CHECK_PICTURE", true);
define("ENABLE_CHECK_PICTURE", false);

// --------------------------------------- [プログラム設置構成]
// APIのServiceディレクトリの場所。
define("API_SERVICE_DIR", "/var/www/html/api1/_pg/app/_service");

// facetのバージョン
define("FACET_VERSION", "4.5");

// --------------------------------------- [S3]
if (ENABLE_AWS) {
    // AWSの場合の定数
    define("S3_BUCKET"			        	, "ds-picture"); //ENABLE_AWS=false 時は指定しない
    define("CLOUDFRONT_PEM_KEY_PAIR_ID"    	, "APKAI2FKIYE73PWA5SSA"); //オンプレの場合指定しない
    define("CLOUDFRONT_PEM_PATH"           	, DIR_CONF."/private_pk-APKAI2FKIYE73PWA5SSA.pem"); //オンプレの場合指定しない
} else {
    // オンプレの場合の定数
    define("S3_BUCKET"			        	, ""); //ENABLE_AWS=false 時は指定しない
    define("CLOUDFRONT_PEM_KEY_PAIR_ID"    	, ""); //オンプレの場合指定しない
    define("CLOUDFRONT_PEM_PATH"           	, ""); //オンプレの場合指定しない
    define("LOCAL_PICTURE_DIR"              , "/var/www/ds-picture"); // ENABLE_AWS=false 時のローカルモード画像ファイル保管先ディレクトリ 
}

define("S3_REGION"			        	, "ap-northeast-1");

 // CLOUDFRONT_COOKIE_DOMAIN AWSの場合【.facet-cloud.com】、オンプレの場合【APIサーバー側のIP】
 // CLOUDFRONT_URL AWSの場合【https://pic.facet-cloud.com】、オンプレの場合【http://APIサーバーIP/api1/localPicture/?f=】
if (ENABLE_AWS) {
    define("CLOUDFRONT_COOKIE_DOMAIN"      , ".facet-cloud.com");
    define("CLOUDFRONT_URL"	               	, "https://pic.facet-cloud.com");
} else {
    define("CLOUDFRONT_COOKIE_DOMAIN"      , $server_addr);
    if (ENABLE_SSL) {
        define("CLOUDFRONT_URL"	, "https://".$server_addr."/api1/localPicture/?f=");
    } else {
        define("CLOUDFRONT_URL"	, "http://".$server_addr."/api1/localPicture/?f=");
    }
}

define("CLOUDFRONT_DEFAULT_EXPIRES_SEC"	, 120);

// --------------------------------------- [TeamSpirit連携用]
define("CLIENT_ID"    , "3MVG9wt4IL4O5wvKfMRpp6knsY68udJaHSapaGLQnGTWHwQAJwb5y8eg7QwAgunl5GP8GUu8Od4DLxLcxdl3K");

define("CLIENT_SECRET", "FD7B2F180023E38CD13256D631E8906A3A97DDBD1B91C97FCA7BB99043E2DC61");

define("CALLBACK_URL" , "http://localhost");
