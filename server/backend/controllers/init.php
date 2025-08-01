<?php
include("/var/www/html/ui1/_pg/conf/conf.php");
include(__DIR__ . "/../conf/conf.php");

class InitController
{
    public $code = 200;
    public $url;
    public $request_body;

    function __construct() {
        $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
        $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
    }

    public function delete($tenant, $contractor_id = null):array {
        global $select_list;
        global $init_list;
      
        $log = new LOG();
        $db = new DB($tenant);

        // 削除のためのID群を取得
        $select_arr = [];
        foreach ($select_list as $value) {
            $sth = $db->pdo()->prepare($value);
            $sth->bindValue(":id", $contractor_id);
            $res = $sth->execute();
            array_push($select_arr, $sth->fetchAll(PDO::FETCH_ASSOC));
        }

        // 取得が問題ない場合は削除処理
        if(!empty($select_arr)){
            // 削除処理開始のログ
            $log->info($_SERVER["PHP_AUTH_USER"], "初期化処理の開始:{\"contractor_id\":".$contractor_id."}", date('Y-m-d H:i:s'));

            // device_id, person_id, auth_set_idをキーに削除する処理
            $exec_arr = [];
            foreach ($select_arr as $i => $arr) {
                if (!empty($arr)) {
                    foreach ($arr as $id_arr) {
                        $key = key($id_arr);
                        foreach ($init_list[$i] as $sql) {
                            $sth = $db->pdo()->prepare($sql);
                            $sth->bindValue(":id", $id_arr[$key]);
                            $res = $sth->execute();
                            if (!$res) {
                                $log->warn($_SERVER["PHP_AUTH_USER"], "初期化実行に失敗しました:{\"contractor_id\":".$contractor_id.",\"bind_id\":".$id_arr[$key].",\"sql\":\"".$sql."\"}", date('Y-m-d H:i:s'));
                            }
                        }
                    }
                } else {
                    if ($i === 0) {
                        $log->info($_SERVER["PHP_AUTH_USER"], "device_idは処理せず:{\"contractor_id\":".$contractor_id."}", date('Y-m-d H:i:s'));
                    } else if ($i === 1) {
                        $log->info($_SERVER["PHP_AUTH_USER"], "person_idは処理せず:{\"contractor_id\":".$contractor_id."}", date('Y-m-d H:i:s'));
                    } else {
                        $log->info($_SERVER["PHP_AUTH_USER"], "auth_set_idは処理せず:{\"contractor_id\":".$contractor_id."}", date('Y-m-d H:i:s'));
                    }
                }
            }

            // contractorをキーとして削除する処理
            foreach ($init_list[3] as $sql) {
                $sth = $db->pdo()->prepare($sql);
                $sth->bindValue(":id", $contractor_id);
                $res = $sth->execute();
                if (!$res) {
                    $log->warn($_SERVER["PHP_AUTH_USER"], "初期化実行に失敗しました:{\"contractor_id\":".$contractor_id.",\"sql\":\"".$sql."\"}");
                }
            }

            $log->info($_SERVER["PHP_AUTH_USER"], "S3削除開始:{\"contractor_id\":".$contractor_id."}", date('Y-m-d H:i:s'));

            // S3の削除
            $contract_num = substr('00000'.$contractor_id, -6);
            if (ENABLE_AWS) {
              $exec_msg = $this->s3_delete("s3://ds-picture/{$contract_num}/");
            } else {
              $exec_msg = $this->s3_delete("/var/www/ds-picture/{$contract_num}/");
            }
            

            $log->info($_SERVER["PHP_AUTH_USER"], $exec_msg[0], date('Y-m-d H:i:s'));
            $log->info($_SERVER["PHP_AUTH_USER"], $exec_msg[1], date('Y-m-d H:i:s'));

            $log->info($_SERVER["PHP_AUTH_USER"], "初期化処理終了:{\"contractor_id\":".$contractor_id."}", date('Y-m-d H:i:s'));

            return ["msg" => "初期化処理が終了しました"];
            
        } else {
            $this->code = 500;
            return ["msg" => "情報取得に失敗しました"];
        }
    }

    private function s3_delete($path = null)
    {
      $res_arr = [];

      if (ENABLE_AWS) {
        // クラウド環境の画像
  
        if ($path) {
          $delete_person   = "aws s3 rm {$path}person --recursive";
          exec($delete_person, $opt, $res);
          if (!$res) {
            // 成功時
            array_push($res_arr, "S3の削除実行成功:{\"exec\":\"".$delete_person."\"}");
          } else {
            // 失敗時
            array_push($res_arr, "S3の削除実行失敗:{\"exec\":\"".$delete_person."\"}");
          }
          
          $delete_picture  = "aws s3 rm {$path}picture --recursive";
          exec($delete_picture, $opt, $res);
          if (!$res) {
            // 成功時
            array_push($res_arr, "S3の削除実行成功:{\"exec\":\"".$delete_picture."\"}");
          } else {
            // 失敗時
            array_push($res_arr, "S3の削除実行失敗:{\"exec\":\"".$delete_picture."\"}");
          }
        }
      } else {
        // オンプレ環境の画像
        $delete_pictures   = "rm -rf {$path}";
        exec($delete_pictures, $opt, $res);
        if (!$res) {
          // 成功時
          array_push($res_arr, "画像の削除実行成功:{\"exec\":\"".$delete_pictures."\"}");
          array_push($res_arr, "オンプレなので分割はなし");
        } else {
          // 失敗時
          array_push($res_arr, "画像の削除実行失敗:{\"exec\":\"".$delete_pictures."\"}");
          array_push($res_arr, "オンプレなので分割はなし");
        }
      }

      return $res_arr;

    }
    
    public function options():array
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET,HEAD,POST,PUT,DELETE");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }

    private function is_set($value):bool
    {
        return !(is_null($value) || $value === "");
    }
}