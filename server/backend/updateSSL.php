<?php

// 各種設定・読み込み
date_default_timezone_set('Asia/Tokyo');
include(__DIR__."/conf/conf.php");

global $admin_pass;

$update_buffer = "-1 month";

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

// 設定の取得
exec("openssl x509 -noout -text -in /etc/httpd/ssl/server.crt | grep 'Not After'", $out_ssl);
$expiry_arr = array_filter(explode(' ', $out_ssl[0]));
$year = $expiry_arr[18];
$month = $month_list[$expiry_arr[15]];
$date = $expiry_arr[16];
$time = $expiry_arr[17];

// 日本時間の期限と現在時刻を格納
$expiry = date('Y/m/d H:i:s', strtotime($year."/".$month."/".$date." ".$time." +9 hour"));
$now = date('Y/m/d H:i:s');

// 現在時刻とバッファ込みの期限を比較
if (strtotime($now) > strtotime($expiry." ".$update_buffer)) {
  $ssl_path = "/etc/httpd/ssl";

  // 既存の証明書の削除
  exec("rm -rf /etc/httpd/ssl/server.crt");

  // 証明書再作成
  exec("echo '{$admin_pass}' | sudo -S openssl x509 -in {$ssl_path}/server.csr -days 365 -req -signkey {$ssl_path}/server.nopass.key > {$ssl_path}/server.crt");

  // apache再起動で反映
  exec("echo '{$admin_pass}' | sudo -S systemctl restart httpd");
}
