<?php
include("/var/www/html/ui1/_pg/conf/conf.php");
include(__DIR__ . "/../conf/conf.php");

class SystemController {
  public $code = 200;
  public $url;
  public $request_body;

  function __construct() {
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
  }

  public function get($main, $sub = null):array {
    switch ($main) {
      case 'datetime':
        return $this->getDatetime();
        break;
      case 'ipconfig':
        return $this->getIpconfig();
        break;
      case 'rebootSchedule':
        return $this->getRebootSchedule();
        break;
      case 'ntp':
        return $this->getNtp();
        break;
      case 'ssl':
        return $this->getSsl();
        break;
      default:
        $this->code = 404;
        return ["error" => [
          "type" => "not_found"
        ]];
        break;
    }
  }
  // 現在のシステム時刻を返却
  private function getDatetime() {
    return [
      "datetime" => date('Y-m-d H:i:s')
    ];
  }

  private function getIpconfig() {

    // 接続済みのネットワーク名を取得
    exec("nmcli connection show --active | grep -v loopback | awk '// {print $1}'", $active_profile);
    $active_profile_name = $active_profile[1];

    // 各設定を取得
    exec("nmcli -f ipv4 con show '{$active_profile_name}' | awk '/ipv4.method:/ {print $2}'", $ipv4Method);
    exec("nmcli -f ipv4 con show '{$active_profile_name}' | awk '/ipv4.addresses:/ {print $2}'", $ipv4Addresses);
    exec("nmcli -f ipv4 con show '{$active_profile_name}' | awk '/ipv4.gateway:/ {print $2}'", $ipv4Gateway);
    exec("nmcli -f ipv4 con show '{$active_profile_name}' | awk '/ipv4.dns:/ {print $2}'", $ipv4Dns);

    if ($ipv4Method[0] === "manual") {
      return [
        "method" => $ipv4Method[0],
        "address" => explode("/", $ipv4Addresses[0])[0],
        "subnet" => explode("/", $ipv4Addresses[0])[1],
        "gateway" => ($ipv4Gateway) ? $ipv4Gateway[0] : "",
        "dns" => ($ipv4Dns) ? $ipv4Dns[0] : ""
      ];
    } else {
      return [
        "method" => $ipv4Method[0],
        "address" => "",
        "subnet" => "",
        "gateway" => "",
        "dns" => ""
      ];
    }

  }

  // サーバーのCRON情報取得
  private function getRebootSchedule() {

    global $admin_pass;

    $log = new LOG();

    exec("echo '{$admin_pass}' | sudo -S crontab -l | grep reboot", $RebootScheOutput, $result);

    if(!empty($RebootScheOutput)){
      return [
        "reboot" => "enable",
        "week" => explode(" ", $RebootScheOutput[0])[4],
        "hour" => explode(" ", $RebootScheOutput[0])[1]
      ];
    }else{
      return ["reboot" => "disable"];
    }
  }

  // NTP設定の取得
  private function getNtp() {
    exec("grep pool /etc/chrony.conf | awk '/^pool/ {print $2}'", $ChronyOutput);
    return ["ntp" => $ChronyOutput[0]];
  }
  
  // SSL設定の取得
  private function getSsl() {
    $month_list = [
      "Jan" => "01",
      "Feb" => "02",
      "Mar" => "03",
      "Apr" => "04",
      "May" => "05",
      "Jun" => "06",
      "Jul" => "07",
      "Aug" => "08",
      "Sep" => "09",
      "Oct" => "10",
      "Nov" => "11",
      "Dec" => "12"
    ];

    if (ENABLE_SSL) {
      // 設定の取得
      exec("openssl x509 -noout -text -in /etc/httpd/ssl/server.crt | grep 'Not After'", $sslOutput);
  
      $expiry_arr = array_filter(explode(' ', $sslOutput[0]));
      
      $year = $expiry_arr[18];
      $month = $month_list[$expiry_arr[15]];
      $date = $expiry_arr[16];
      $time = $expiry_arr[17];
  
      $expiry = date('Y/m/d H:i:s', strtotime($year."/".$month."/".$date." ".$time." +9 hour"));

    } else {
      $expiry = "";
    }
    
    return [
      "ssl_enable" => ENABLE_SSL,
      "expiry" => $expiry
    ];

  }

  public function post($main, $sub = null):array {
    $this->code = 200;
    switch ($main) {
      case 'datetime':
        return $this->syncDatetime();
        break;
      case 'ipconfig':
        return $this->ipconfig();
        break;
      case 'middleware':
        return $this->middleware();
        break;
      case 'reboot':
        if ($sub !== 'auto') {
          return $this->rebootManual();
        } else {
          return $this->rebootAuto();
        }
        break;
      case 'ntp':
        return $this->ntp();
        break;
      case 'ssl':
        return $this->ssl();
        break;
      case 'sslSwitch':
        return $this->sslSwitch();
        break;
      default:
        $this->code = 404;
        return ["error" => [
          "msg" => "not_found"
        ]];
        break;
    }
  }

  // クライアントPCの時刻で再設定
  public function syncDatetime() {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;

    $result = true;

    exec("echo '{$admin_pass}' | sudo -S date -s \"".$post["datetime"]."\"");

    if ($result) {
      return [
        "datetime" => str_replace("/", "-", $post["datetime"])
      ];
    } else {
      $this->code = 404;
      return ["error" => [
        "msg" => "時刻の同期に失敗しました"
      ]];
    }

  }

  // IP設定を変更する
  public function ipconfig():array {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;

    exec("nmcli connection show --active | grep -v loopback | awk '// {print $1}'", $profile);
    
    $profile_name = $profile[1];

    $cmd = "echo '{$admin_pass}' | sudo -S nmcli connection modify {$profile_name}";

    if ($post["ip_type"] === "manual") {
      $cmd .= " ipv4.method ".$post["ip_type"];
      $cmd .= " ipv4.addresses ".join(".", $post["ip"])."/".$post["subnet"];
      $cmd .= (!empty($post["gw"])) ? " ipv4.gateway ".join(".", $post["gw"]) : "";
      $cmd .= (!empty($post["dns"])) ? " ipv4.dns ".join(".", $post["dns"]) : "";
    } else {
      $cmd .= " ipv4.method ".$post["ip_type"];
    }

    exec($cmd, $o, $ip_result);

    if (!$ip_result) {
      return [
        "msg" => $cmd
      ];
    } else {
      $this->code = 404;
      return ["error" => [
        "msg" => "IP設定変更の実行に失敗しました"
      ]];
    }
    
  }

  // ミドルウェアをアップデートする
  public function middleware():array {
    global $admin_pass;

    $log = new LOG();
    $file = $_FILES["middleware_file"]["tmp_name"];
    $file_name = $_FILES["middleware_file"]["name"];

    $result;

    if (is_uploaded_file($file)) {
      if (move_uploaded_file($file, $file_name)) {

        if (file_exists($file_name)) {
          // まず既存のファイルを削除
          exec("echo '{$admin_pass}' | sudo -S sh -c 'rm -rf /opt/tomcat/webapps/ws/WEB-INF/lib/log4j*'");
      
          // 解凍
          exec("tar zxvf {$file_name}", $tar_files);
      
          $tar_path = $tar_files[0];
      
          // 配置して権限を変更
          exec("echo '{$admin_pass}' | sudo -S sh -c 'cp -rp {$tar_path}* /opt/tomcat/webapps/ws/WEB-INF/lib/'");
          exec("echo '{$admin_pass}' | sudo -S sh -c 'chown tomcat:tomcat -R /opt/tomcat/*'");
      
          // 解凍したフォルダと解凍元のファイル削除
          exec("echo '{$admin_pass}' | sudo -S rm -rf {$tar_path}");
          exec("echo '{$admin_pass}' | sudo -S rm -rf {$file_name}");

          $result = true;

        } else {
          $result = false;
        }
      } else {
        $result = false;
      }
    } else {
      $result = false;
    }

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

  // 手動でサーバーを再起動する
  public function rebootManual():array {
    global $admin_pass;

    $log = new LOG();

    exec("echo '{$admin_pass}' | sudo -S reboot");

    return ["msg" => true];
  }

  // サーバー再起動の設定を変更する
  public function rebootAuto():array {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;
    $conf_path = __DIR__ . "/../conf";

    // 有効・無効パラメータがない場合は400で返却
    if (empty($post["reboot_enable"])) {
      $this->code = 400;
      return ["error" => [
        "msg" => "有効なパラメータが取得できませんでした"
      ]];
    }

    // もしファイルが存在したら削除する
    if (file_exists($conf_path."/crontab.new")) unlink($conf_path."/crontab.new");

    if ($post["reboot_enable"] == "enable") {
      // 有効の場合の処理
      $week = ($post["reboot_week"] == "every") ? "*" : $post["reboot_week"];
      $hour = $post["reboot_hour"];

      $cron = "0 {$hour} * * {$week} /sbin/reboot";

      // 自動再起動を有効した場合
      exec("sed -e '2i{$cron}' {$conf_path}/crontab.back > {$conf_path}/crontab.new");
      exec("echo '{$admin_pass}' | sudo -S crontab {$conf_path}/crontab.new");
      unlink("{$conf_path}/crontab.new");
    } else {
      // 自動再起動を無効にした場合
      exec("echo '{$admin_pass}' | sudo -S crontab {$conf_path}/crontab.back");
    }

    return ["msg" => "update reboot auto complete"];
  }


  public function ntp():array {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;

    $ntp = 'pool ' . $post["ntp"] . ' iburst';

    // 同期先を書き換え
    exec("echo '{$admin_pass}' | sudo -S sed -e '3d' /etc/chrony.conf.back | sed -e '3i{$ntp}' | sudo tee /etc/chrony.conf");

    // chrony再起動で反映
    exec("echo '{$admin_pass}' | sudo -S systemctl restart chronyd");

    return ["msg" => "success ntp"];
  }

  public function ssl():array {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;

    $ssl_path = "/etc/httpd/ssl";

    // 既存の証明書の削除
    exec("rm -rf /etc/httpd/ssl/server.crt");

    // 証明書再作成
    exec("echo '{$admin_pass}' | sudo -S openssl x509 -in {$ssl_path}/server.csr -days 365 -req -signkey {$ssl_path}/server.nopass.key > {$ssl_path}/server.crt");

    // apache再起動で反映
    exec("echo '{$admin_pass}' | sudo -S systemctl restart httpd");

    return ["msg" => "success create ssl crt"];
  }

  public function sslSwitch() :array {
    global $admin_pass;

    $log = new LOG();
    $post = $this->request_body;

    $ssl_enable = $post["ssl_enable"];

    $conf_ssl = "/etc/httpd/conf.d/ssl.conf";
    $conf_ui = "/var/www/html/ui1/_pg/conf/conf.php";
    $conf_api = "/var/www/html/api1/_pg/conf/conf.php";

    if ($ssl_enable) { // 有効の場合
      // apacheのsslファイルを有効化
      exec("echo '{$admin_pass}' | sudo -S mv {$conf_ssl}.disabled {$conf_ssl}");

      // ファイアウォールの設定
      exec("echo '{$admin_pass}' | sudo -S firewall-cmd --permanent --zone=public --add-port=443/tcp");
      exec("echo '{$admin_pass}' | sudo -S firewall-cmd --permanent --zone=public --remove-service=http");
      exec("echo '{$admin_pass}' | sudo -S firewall-cmd --permanent --zone=public --remove-port=8080/tcp");

      // confファイルの書き換え
      unlink($conf_ui);
      exec("sed -e '21d' {$conf_ui}.back | sed -e '21idefine(\"ENABLE_SSL\"     , true);' | tee {$conf_ui}");
      unlink($conf_api);
      exec("sed -e '21d' {$conf_api}.back | sed -e '21idefine(\"ENABLE_SSL\"     , true);' | tee {$conf_api}");
      
    } else { // 無効の場合
      // apacheのsslファイルを無効化
      exec("echo '{$admin_pass}' | sudo -S mv {$conf_ssl} {$conf_ssl}.disabled");

      // ファイアウォールの設定
      exec("echo '{$admin_pass}' | sudo -S firewall-cmd --permanent --zone=public --remove-port=443/tcp");
      exec("echo '{$admin_pass}' | sudo -S firewall-cmd --permanent --zone=public --add-service=http");
      exec("echo '{$admin_pass}' | sudo -S firewall-cmd --permanent --zone=public --add-port=8080/tcp");

      // confファイルの書き換え
      unlink($conf_ui);
      exec("sed -e '21d' {$conf_ui}.back | sed -e '21idefine(\"ENABLE_SSL\"     , false);' | tee {$conf_ui}");
      unlink($conf_api);
      exec("sed -e '21d' {$conf_api}.back | sed -e '21idefine(\"ENABLE_SSL\"     , false);' | tee {$conf_api}");

    }
    
    return ["msg" => "success change ssl"];
    
  }

}
