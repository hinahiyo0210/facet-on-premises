<?php 

class DashboardController extends UserBaseController {

	// 権限の取得用
	public $deviceTopMenuFlag;
	
	public function prepare(&$form) {
		
		// ユーザ権限設定を取得。
		$this->deviceTopMenuFlag = self::functionAccessFlag();

	}
	
	// トップ
	public function indexAction(&$form) {
		
		$data = Filters::ref($form)
			->at("day"			, date("Y/m/d")						)->date()
			->at("span1_from"	, formatDate(strtotime("-7 day"))	)->date()
			->at("span1_to"		, date("Y/m/d")						)->date()
			->at("span2_from"	, formatDate(strtotime("-14 day"))	)->date()
			->at("span2_to"		, formatDate(strtotime("-7 day"))	)->date()
			->getFilteredData();
		
		// デバイスIDを取得。
		$deviceIds = DB::selectOneArray("select device_id from m_device where contract_state = 10 and contractor_id = {value}", $this->contractor_id);
		if (empty($deviceIds)) $deviceIds = [-1];
		
		// 集計を取得。
		$sum = RecogSummaryService::getSummaryRecog($deviceIds, $data["day"]);
		
		// 期間集計を取得。
		$span = RecogSummaryService::getSpanSummary($deviceIds, $data["span1_from"], $data["span1_to"], $data["span2_from"], $data["span2_to"]);
		
		$this->assign("sum", $sum);
		$this->assign("span", $span);
		return "dashboard.tpl";
	}
	
	private function functionAccessFlag() {

		$funcName =	Session::getUserFunctionAccess("function_name");

		// 管理者ユーザーかどうかの確認
		$flag["admin"] = (Session::getLoginUser("user_flag") == 1) ? true : false;

		// 管理者アカウントならそのまま返却
    $judgeFlag = (isset($flag[0])) ? $flag[0] : null;
		if ($judgeFlag) return $flag;

		$flag["user"]   = (!(array_search("新規ユーザー登録" , $funcName)>-1) && !(array_search("ユーザー情報一覧・変更" , $funcName)>-1)) ? false : true;
		$flag["device"] = (array_search("認証関連基本設定・更新" , $funcName)>-1);

		return $flag;
	}
	
}
