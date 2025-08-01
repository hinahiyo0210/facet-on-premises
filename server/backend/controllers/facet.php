<?php
include("/var/www/html/ui1/_pg/conf/conf.php");
include(__DIR__ . "/../conf/conf.php");

class FacetController {
  public $code = 200;
  public $url;
  public $request_body;

  function __construct() {
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
  }

  public function get($main, $sub = null):array {

    if ($main === 'facetLog') {
      return $this->getFacetLog();
    } else if ($main === "apiToken") {
      return $this->getApiToken();
    } else {
      $this->code = 404;
      return ["error" => [
        "type" => "not_found"
      ]];
    }
  }

  // facetCloudのログを抽出する
  private function getFacetLog() {
    $tentativePath = __DIR__."/../conf";
    $logs_name = "logs_".date('YmdHis').".tar.gz";

    // tarでログファイル圧縮
    exec("tar zcvf {$tentativePath}/{$logs_name} /var/www/logs/");

    $file = $tentativePath."/".$logs_name;

    // tarファイルの削除を予約
    register_shutdown_function(function() use ($file) {
      unlink($file);
    });

    if (file_exists($file)) {
      // ファイルがあれば出力
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="'.basename($file).'"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      while (ob_get_level()) { ob_end_clean(); }
      readfile($file);
      exit;
   }else{
     die("ダウンロード対象ファイルが見つかりません");
   }
  }

  private function getApiToken() {

    $db = new DB("on-premises");
    
    $sql = "SELECT api_token FROM m_contractor WHERE contractor_id = 1";
    $sth = $db->pdo()->prepare($sql);
    $res = $sth->execute();
    if($res){
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if(!empty($data)){
            return $data;
        }else{
            $this->code = 404;
            return ["error" => [
                "msg" => "token取得に失敗しました"
            ]];
        }
    }else{
        $this->code = 500;
        return ["error" => [
            "msg" => "token取得に失敗しました(SE"
        ]];
    }
  }

  public function post($main, $sub = null):array {
    $this->code = 200;
    switch ($main) {
      case 'versionUpdate':
        return $this->versionUpdate();
        break;
      case 'exclusiveCancel':
        return $this->exclusiveCancel();
        break;
      case 'apiToken':
        return $this->apiTokenUpdate();
        break;
      default:
        $this->code = 404;
        return ["error" => [
          "msg" => "not_found_prefix"
        ]];
        break;
    }
  }

  // facetCloudのバージョンのアップデート
  public function versionUpdate():array {
    global $admin_pass;

    $log = new LOG();
    $file = $_FILES["facet_file"]["tmp_name"];
    $file_name = $_FILES["facet_file"]["name"];
    
    $result;

    if (is_uploaded_file($file)) {
      if (move_uploaded_file($file, $file_name)) {

        if (file_exists($file_name)) {
          // 解凍
          exec("tar zxvf {$file_name}", $tar_files);
          $tar_path = $tar_files[0];

          // ＝＝＝＝＝＝＝＝＝＝DBアップデート＝＝＝＝＝＝＝＝＝＝
          if (file_exists("{$tar_path}update.sql")) {
            exec("mysql -u admin -pun4FAqaMiXAh ds < {$tar_path}update.sql");
            // 更新後ファイルの削除
            unlink("{$tar_path}update.sql");
          }

          $enable_ssl = ENABLE_SSL;

          // ＝＝＝＝＝＝＝＝＝＝ソースの更新＝＝＝＝＝＝＝＝＝＝
          // 既存のソースを削除
          exec("echo '{$admin_pass}' | sudo -S sh -c 'rm -rf /var/www/html/ui1'");
          exec("echo '{$admin_pass}' | sudo -S sh -c 'rm -rf /var/www/html/api1'");

          // 配置して所有者を変更
          exec("echo '{$admin_pass}' | sudo -S sh -c '\cp -rpf {$tar_path}* /var/www/html/'");
          exec("echo '{$admin_pass}' | sudo -S sh -c 'chown valtec:valtec -R /var/www/html/'");

          // 権限変更
          exec("echo '{$admin_pass}' | sudo -S chmod 777 -R /var/www/html/ui1/_pg/tmp/");
          exec("echo '{$admin_pass}' | sudo -S chmod 777 -R /var/www/html/api1/_pg/tmp/");

          // Smarty画面ファイルをリセットする
          exec("sh -c 'rm -rf /var/www/html/ui1/_pg/tmp/smarty/templates_c/*'");

          $result = true;

          // SSLが有効だった場合はconfファイルの書き換え
          $conf_ui = "/var/www/html/ui1/_pg/conf/conf.php";
          $conf_api = "/var/www/html/api1/_pg/conf/conf.php";
          unlink($conf_ui);
          unlink($conf_api);
          if ($enable_ssl) {
            exec("sed -e '21d' {$conf_ui}.back | sed -e '21idefine(\"ENABLE_SSL\"     , true);' | tee {$conf_ui}");
            exec("sed -e '21d' {$conf_api}.back | sed -e '21idefine(\"ENABLE_SSL\"     , true);' | tee {$conf_api}");
          } else {
            exec("sed -e '21d' {$conf_ui}.back | sed -e '21idefine(\"ENABLE_SSL\"     , false);' | tee {$conf_ui}");
            exec("sed -e '21d' {$conf_api}.back | sed -e '21idefine(\"ENABLE_SSL\"     , false);' | tee {$conf_api}");
          }

        } else {
          $result = false;
        }
      } else {
        $result = false;
      }
    } else {
      $result = false;
    }

    // 解凍したフォルダと解凍元のファイル削除
    exec("echo '{$admin_pass}' | sudo -S rm -rf {$tar_path}");
    exec("echo '{$admin_pass}' | sudo -S rm -rf {$file_name}");

    if ($result) {
      return [
        "msg" => "アップデート処理を完了しました"
      ];
    } else {
      $this->code = 400;
      return ["error" => [
        "msg" => "ファイルが取得できませんでした"
      ]];
    }

  }

  // 排他制御の解除や仮ファイルの削除
  public function exclusiveCancel():array {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;

    // 各フォルダ内のファイル削除
    $tmpDir = "/var/www/html/ui1/_pg/tmp";
    $deleteLists = [
      "bulk_extract",
      "bulk_upload",
      "download-progress",
      "upload-progress",
      "exclusive",
      "export"
    ];

    foreach ($deleteLists as $dir) {
      exec("echo '${admin_pass}' | sudo -S rm -rf {$tmpDir}/{$dir}/*");
    }

    return ["msg" => true];
  }


  // APIトークンのランダムもしくは手動入力による更新
  public function apiTokenUpdate() {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;

    if ($post["apiToken"] !== "") {
      // 手動で入力されている場合はそれをトークンとして更新
      if (mb_strlen($post["apiToken"]) == 64) {
        if (preg_match('/^[a-zA-Z0-9]+$/', $post["apiToken"])) {

          // 問題ないのでDB更新処理
          $db = new DB("on-premises");
          $sql = "UPDATE m_contractor SET api_token = :api_token WHERE contractor_id = 1";
          $sth = $db->pdo()->prepare($sql);
          $sth->bindValue(":api_token", $post["apiToken"]);
          $res = $sth->execute();
          if($res){
            return [
              "msg" => "ds-api-tokenの更新完了",
              "token" => $post["apiToken"]
            ];
          }else{
            $this->code = 400;
            return ["error" => [
              "msg" => "ds-api-tokenの更新失敗"
            ]];
          }

        } else {
          $this->code = 400;
          return ["error" => [
            "msg" => "tokenは半角英数字のみで指定してください"
          ]];
        }
      } else {
        $this->code = 400;
        return ["error" => [
          "msg" => "tokenは64文字で指定してください"
        ]];
      }
    } else {
      // api_tokenの格納
      $chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $api_token = substr(str_shuffle(str_repeat($chars, 64)), 0, 64);

      // DB更新処理
      $db = new DB("on-premises");
      $sql = "UPDATE m_contractor SET api_token = :api_token WHERE contractor_id = 1";
      $sth = $db->pdo()->prepare($sql);
      $sth->bindValue(":api_token", $api_token);
      $res = $sth->execute();
      if($res){
        return [
          "msg" => "ds-api-tokenの更新完了",
          "token" => $api_token
        ];
      }else{
        $this->code = 400;
        return ["error" => [
          "msg" => "ds-api-tokenの更新失敗"
        ]];
      }
    }

  }
}
