<?php 

class TestController extends BaseController {
	
	public function indexAction(&$form) {
		
		DB::init(DB_DRIVER, DB_HOST, DB_USER, DB_PASS, DB_NAME);	// ここではまだDBに接続される訳では無い。初回利用時に接続される。

		$device = DB::selectRow("select * from m_device where device_id = 10");
		
		$list = RecogLogService::getAccessRecord(
				$device
				, strtotime("2021/04/16 08:55:31")
				, strtotime("2021/04/16 08:55:31")
				, 10
				);
		
		ddie($list);
		
	}


	
	
}


