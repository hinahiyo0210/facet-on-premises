<?php

/*=========== サーバーパスワード ===========*/
$admin_pass = "@Valtec123";

/*=========== RDSホスト ===========*/
$hosts = [
  "on-premises" => "localhost"
];

$ws_api_url_list = [
  "on-premises" => "http://127.0.0.1:8080/ws/api"
];

/*=========== DBログイン情報 ===========*/
$setting = [
  "user" => "admin",
  "password" => "un4FAqaMiXAh"
];

/*=========== ログ出力場所 ===========*/
$log_path = __DIR__."/../backend.log";

/*=========== 取得SQL ===========*/
$select_list = [
  "SELECT device_id FROM m_device WHERE contractor_id = :id",
  "SELECT person_id FROM t_person WHERE contractor_id = :id",
  "SELECT auth_set_id FROM t_auth_set WHERE contractor_id = :id"
];

/*=========== 削除SQL ===========*/
$delete_list_contractor = [
  "DELETE FROM t_alert WHERE contractor_id = :id",
  "DELETE FROM t_apb_group WHERE contractor_id = :id",
  "DELETE FROM t_auth_set WHERE contractor_id = :id",
  "DELETE FROM t_device_group WHERE contractor_id = :id",
  "DELETE FROM t_enter_exit_count WHERE contractor_id = :id",
  "DELETE FROM t_facet_operate_log WHERE contractor_id = :id",
  "DELETE FROM t_person WHERE contractor_id = :id",
  "DELETE FROM t_push_response_msg WHERE contractor_id = :id",
  "DELETE FROM t_recog_config_set WHERE contractor_id = :id",
  "DELETE FROM t_system_config_set WHERE contractor_id = :id",
  "DELETE FROM m_person_type WHERE contractor_id = :id",
  "DELETE FROM m_recog_pass_flag WHERE contractor_id = :id",
  "DELETE FROM m_user WHERE contractor_id = :id",
  "DELETE FROM m_device WHERE contractor_id = :id",
  "DELETE FROM m_contractor WHERE contractor_id = :id"
];
$init_list_contractor = [
  "DELETE FROM t_alert WHERE contractor_id = :id",
  "DELETE FROM t_apb_group WHERE contractor_id = :id",
  "DELETE FROM t_auth_set WHERE contractor_id = :id",
  "DELETE FROM t_device_group WHERE contractor_id = :id",
  "DELETE FROM t_enter_exit_count WHERE contractor_id = :id",
  "DELETE FROM t_facet_operate_log WHERE contractor_id = :id",
  "DELETE FROM t_person WHERE contractor_id = :id",
  "DELETE FROM t_push_response_msg WHERE contractor_id = :id",
  "DELETE FROM t_recog_config_set WHERE contractor_id = :id",
  "DELETE FROM t_system_config_set WHERE contractor_id = :id",
  "DELETE FROM m_person_type WHERE contractor_id = :id",
  "DELETE FROM m_recog_pass_flag WHERE contractor_id = :id",
  "DELETE FROM m_user WHERE contractor_id = :id AND user_flag <> 1",
  "DELETE FROM m_device WHERE contractor_id = :id AND serial_no <> '99999999999'"
];
$delete_list_device = [
  "DELETE FROM t_alert_device WHERE device_id = :id",
  "DELETE FROM t_apb_group_device WHERE device_id = :id",
  "DELETE FROM t_apb_log WHERE device_id = :id",
  "DELETE FROM t_apb_log_device WHERE trans_device_id = :id",
  "DELETE FROM t_apb_repair WHERE device_id = :id",
  "DELETE FROM t_device_group_device WHERE device_id = :id",
  "DELETE FROM t_device_person WHERE device_id = :id",
  "DELETE FROM t_operate_log WHERE device_id = :id",
  "DELETE FROM t_person_access_time WHERE device_id = :id",
  "DELETE FROM t_recog_analize WHERE device_id = :id",
  "DELETE FROM t_recog_analize_daily WHERE device_id = :id",
  "DELETE FROM t_recog_analize_hourly WHERE device_id = :id",
  "DELETE FROM t_recog_log WHERE device_id = :id",
  "DELETE FROM t_sync_log WHERE device_id = :id",
];

$delete_list_person = [
  "DELETE FROM t_person_card_info WHERE person_id = :id"
];

$delete_list_auth = [
  "DELETE FROM t_function_auth WHERE auth_set_id = :id"
];

// SELECTする配列「$select_list」の順番で格納する必要がある
$delete_list = [
  $delete_list_device,
  $delete_list_person,
  $delete_list_auth,
  $delete_list_contractor
];
$init_list = [
  $delete_list_device,
  $delete_list_person,
  $delete_list_auth,
  $init_list_contractor
];

/*=========== 環境バージョン ===========*/
$version = [
  "on-premises" => 4.1
];
