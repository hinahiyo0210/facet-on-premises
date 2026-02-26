<?php
// ===== CORS (must be before any output / includes) =====
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

header("Access-Control-Allow-Origin: $origin");
header("Vary: Origin");

header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// preflight はここで終了
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
  http_response_code(204);
  exit;
}
// ===== /CORS =====

include("/var/www/html/ui1/_pg/conf/conf.php");
include(__DIR__ . "/../conf/conf.php");

class PrinterController {
  public $code = 200;
  public $url;
  public $request_body;

  function __construct() {
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
  }

  public function get($main, $id = null):array {
    if ($main === 'devices') {
      return $this->getDevices($id);
    } else if ($main === "printers") {
      return $this->getPrinters($id);
    } else if ($main === "linkedDevices") {
      return $this->getLinkPrinters($id);
    } else {
      $this->code = 404;
      return ["error" => [
        "type" => "not_found"
      ]];
    }
  }

  private function getDevices($id):array
  {
    $db = new DB("on-premises");

    $sql = "SELECT * FROM m_device WHERE contractor_id = :id AND contract_state <> 99";
    $sth = $db->pdo()->prepare($sql);
    $sth->bindValue(":id",$id);
    $res = $sth->execute();
    if($res){
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($data)){
            return $data;
        }else{
            $this->code = 404;
            return ["error" => [
                "type" => "not_in_contractor"
            ]];
        }
    }else{
        $this->code = 500;
        return ["error" => [
            "type" => "fatal_error"
        ]];
    }
  }
  private function getPrinters($id):array
  {
    $db = new DB("on-premises");

    $sql = "SELECT * FROM t_printer_settings WHERE contractor_id = :id";
    $sth = $db->pdo()->prepare($sql);
    $sth->bindValue(":id",$id);
    $res = $sth->execute();
    if($res){
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($data)){
            return $data;
        }else{
            $this->code = 404;
            return ["error" => [
                "type" => "not_in_contractor"
            ]];
        }
    }else{
        $this->code = 500;
        return ["error" => [
            "type" => "fatal_error"
        ]];
    }
  }

  private function getLinkPrinters($id):array
  {
    $db = new DB("on-premises");

    $sql = "SELECT * FROM t_printer_linked_devices WHERE printer_id = :id";
    $sth = $db->pdo()->prepare($sql);
    $sth->bindValue(":id",$id);
    $res = $sth->execute();
    if($res){
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($data)){
            return $data;
        }else{
            return [];
        }
    }else{
        $this->code = 500;
        return ["error" => [
            "type" => "fatal_error"
        ]];
    }
  }

  public function post($main, $sub = null):array {
    $this->code = 200;
    switch ($main) {
      case 'setting':
        return $this->setting();
        break;
      case 'delete':
        return $this->deletePrinter();
        break;
      default:
        $this->code = 404;
        return ["error" => [
          "msg" => "not_found_prefix"
        ]];
        break;
    }
  }

  // プリンター登録処理
  public function setting():array {

    $log = new LOG();
    $post = $this->request_body;

    $id = isset($post["old_printer_id"]) ? $post["old_printer_id"] : null;

    $log->info($_SERVER["PHP_AUTH_USER"], "設定保存開始:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
    // ＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝設定の保存＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
    if (empty($id)) {
      // 台数チェック
      $allowed_printers = 30; // 1契約あたりの最大プリンター登録数
      $db = new DB("on-premises");
      $sql = "SELECT COUNT(*) as cnt FROM t_printer_settings WHERE contractor_id = 1";
      $sth = $db->pdo()->prepare($sql);
      $sth->execute();
      $count = $sth->fetch(PDO::FETCH_ASSOC);
      if($count["cnt"] >= $allowed_printers){
        $log->error($_SERVER["PHP_AUTH_USER"], "プリンター設定の保存失敗:台数上限超過", date('Y-m-d H:i:s'));
        $this->code = 400;
        return ["error" => [
          "msg" => "プリンターは{$allowed_printers}台まで登録可能です"
        ]];
      }
      // 新規登録処理
      $db = new DB("on-premises");
      $sql = "INSERT INTO t_printer_settings (contractor_id, is_enabled, printer_name, printer_ip, port, print_timeout_ms, print_xml) VALUES (1, 1, :printer_name, :printer_ip, :port, :print_timeout_ms, :print_xml)";
      $sth = $db->pdo()->prepare($sql);
      $sth->bindValue(":printer_name", $post["printer_name"]);
      $sth->bindValue(":printer_ip", $post["printer_ip"]);
      $sth->bindValue(":port", $post["port"]);
      $sth->bindValue(":print_timeout_ms", $post["print_timeout_ms"]);
      $sth->bindValue(":print_xml", $post["print_xml"]);
      $res = $sth->execute();
    } else {
      // 更新処理
      $db = new DB("on-premises");
      $sql = "UPDATE t_printer_settings SET printer_name = :printer_name, printer_ip = :printer_ip, port = :port, print_timeout_ms = :print_timeout_ms, print_xml = :print_xml WHERE contractor_id = 1 AND printer_id = :printer_id";
      $sth = $db->pdo()->prepare($sql);
      $sth->bindValue(":printer_name", $post["printer_name"]);
      $sth->bindValue(":printer_ip", $post["printer_ip"]);
      $sth->bindValue(":port", $post["port"]);
      $sth->bindValue(":print_timeout_ms", $post["print_timeout_ms"]);
      $sth->bindValue(":print_xml", $post["print_xml"]);
      $sth->bindValue(":printer_id", $id);
      $res = $sth->execute();
    }

    if($res){
      // 新規の場合はIDを取得
      if(empty($id)){
        $db  = new DB("on-premises");
        $sql = "SELECT printer_id FROM t_printer_settings WHERE contractor_id = 1 ORDER BY printer_id DESC LIMIT 1";
        $sth = $db->pdo()->prepare($sql);
        $sth->execute();
        $printer_id = $sth->fetch(PDO::FETCH_ASSOC);
        $printer_id = $printer_id["printer_id"];
      }
      $log->info($_SERVER["PHP_AUTH_USER"], "設定保存の成功:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
    }else{
      $log->error($_SERVER["PHP_AUTH_USER"], "設定保存の失敗:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      $this->code = 400;
      return ["error" => [
        "msg" => "プリンター設定の更新失敗"
      ]];
    }

    // ＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝連携設定の削除・保存＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
    if (!empty($id)) {
      // 既存設定の削除
      $db = new DB("on-premises");
      $sql = "DELETE FROM t_printer_linked_devices WHERE printer_id = :printer_id";
      $sth = $db->pdo()->prepare($sql);
      $sth->bindValue(":printer_id", $id);
      $res = $sth->execute();
      if($res){
        $log->info($_SERVER["PHP_AUTH_USER"], "連携設定削除の成功:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      }else{
        $log->error($_SERVER["PHP_AUTH_USER"], "連携設定削除の失敗:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      }
    }
    
    // 新しい設定の登録
    $db = new DB("on-premises");
    // 新規の場合もID格納
    $id = ($id == null) ? $printer_id : $id;
    foreach ($post["device_ids"] as $device_id) {
      $sql = "INSERT INTO t_printer_linked_devices (printer_id, device_id) VALUES (:printer_id, :device_id)";
      $sth = $db->pdo()->prepare($sql);
      $sth->bindValue(":printer_id", $id);
      $sth->bindValue(":device_id", $device_id);
      $res = $sth->execute();
      if($res){
        $log->info($_SERVER["PHP_AUTH_USER"], "連携設定登録の成功:{\"printerID\":".$id.",\"deviceID\":".$device_id."}", date('Y-m-d H:i:s'));
      }else{
        $log->error($_SERVER["PHP_AUTH_USER"], "連携設定登録の失敗:{\"printerID\":".$id.",\"deviceID\":".$device_id."}", date('Y-m-d H:i:s'));
      }
    }

    if($res){
      $log->info($_SERVER["PHP_AUTH_USER"], "プリンタPOST処理の成功:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      return [
        "msg" => "プリンター設定の更新完了",
        "printer_id" => $id
      ];
    }else{
      $log->error($_SERVER["PHP_AUTH_USER"], "プリンタPOST処理の失敗:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      $this->code = 400;
      return ["error" => [
        "msg" => "プリンター設定の更新失敗"
      ]];
    }

  }

  // プリンター削除処理
  public function deletePrinter():array {

    $log = new LOG();
    $post = $this->request_body;

    $id = isset($post["id"]) ? $post["id"] : null;

    $log->info($_SERVER["PHP_AUTH_USER"], "設定削除開始:{\"params\":{\"id\":\"".$id."\"}}", date('Y-m-d H:i:s'));
    // ＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝設定の保存＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
    if ($id === null) {
      $log->error($_SERVER["PHP_AUTH_USER"], "プリンタDELETE処理の失敗:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      $this->code = 400;
      return ["error" => [
        "msg" => "プリンター設定の削除失敗：ID不明"
      ]];
    } else {
      // 更新処理
      $db = new DB("on-premises");
      $sql = "DELETE FROM t_printer_settings WHERE contractor_id = 1 AND printer_id = :id";
      $sth = $db->pdo()->prepare($sql);
      $sth->bindValue(":id", $id);
      $res = $sth->execute();
    }

    if($res){
      $log->info($_SERVER["PHP_AUTH_USER"], "プリンタDELETE処理の成功:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      return [
        "msg" => "プリンター設定の削除完了",
      ];
    }else{
      $log->error($_SERVER["PHP_AUTH_USER"], "プリンタDELETE処理の失敗:{\"params\":".json_encode($post)."}", date('Y-m-d H:i:s'));
      $this->code = 400;
      return ["error" => [
        "msg" => "プリンター設定の削除失敗"
      ]];
    }


  }

}
