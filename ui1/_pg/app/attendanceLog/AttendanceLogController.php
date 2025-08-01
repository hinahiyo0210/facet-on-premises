<?php 

class AttendanceLogController extends UserBaseController {
	
	public $devices;

	public $groups;
	
	public $groupsDisplay;
	
	// @Override
	public function prepare(&$form) {
		
		// sessionからプルダウンを取得
		parent::prepare($form);
		
		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);

		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);
		
		// 表示用グループ。(キーはID)
		$noGroupDeviceIds = array_filter(array_keys($this->devices), function ($device_id) {
			foreach ($this->groups as $gid=>["deviceIds"=>$deviceIds]) {
				if (in_array($device_id,$deviceIds)) {
					return false;
				}
			}
			return true;
		});
		$this->groupsDisplay = array();
		foreach ($this->groups as $gid=>["deviceIds"=>$deviceIds,"group_name"=>$group_name]) {
			$this->groupsDisplay[$gid] = array("deviceIds" => $deviceIds, "group_name" => $group_name);
		}
		$this->groupsDisplay["-1"] = array("deviceIds"=>$noGroupDeviceIds, "group_name"=>"未登録");
		
		// ログ種別enumを作成。
		AttendanceLogService::createAttendanceLogEnum();
		
	}
	
	// トップ
	public function indexAction(&$form) {
		
		// 初期表示の場合
		if (empty($form["searchInit"])) {
			// 期間from 期間to
			$form["date_from"] = date("Y/m/d", strtotime("today -1 week"));
			$form["date_to"] = date("Y/m/d", strtotime("today"));
			// グループ カメラ
			$form["group_ids"] = array_keys($this->groupsDisplay);
			$form["device_ids"] = array_keys($this->devices);
		}
		
		$data = Filters::ref($form)
			->at("limit" 	, 20    )->enum(Enums::pagerLimit())
			->at("pageNo" 		    )->digit()
			->at("searchInit"		)->digit()
			->at("group_ids"		)->enumArray($this->groupsDisplay)
			->at("device_ids"	    )->enumArray($this->devices)
			->at("date_from"	    )->date()
			->at("date_to"		    )->date()
			->at("log_type"		    )->enum(SimpleEnums::getAll("attendance_log_type"))
			->at("log_level"	    )->values(["I", "W", "E"])
			->at("decision"	    	)->values(["OK", "NG"])
			->at("person_code"	    )->len(100)
		->getFilteredData();
		
		// 検索。
		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = AttendanceLogService::getAttendanceLogList($pageInfo, $this->contractor_id, $data, $this->devices);
			
		$this->assign("pagerLimit", Enums::pagerLimit());
		$this->assign("pageInfo", $pageInfo);
		$this->assign("list", $list);
		return "attendanceLog.tpl";
	}
	
	// 全てチェック切り替え。
	public function attendanceCheckAction(&$form) {
		
		$data = Filters::ref($form)
			->at("isLocalOnly")->flag()
			->at("isCheckOn")->flag()
			->at("checkIds")->func(function($v) { return explode(",", $v); } )->digitArray()
			->at("attendance_searchFormKey")->len(3)
			->at("attendance_checkIdsKey")->len(3)
			->getFilteredData();

		// 選択されているIDリスト。
		$checkIds = Session::getData($data["attendance_checkIdsKey"], false, "attendance_checkIds");
		
		if (empty($checkIds)) {
			$checkIds = [];
		}

		// 画面に表示されている範囲のみを処理。
		foreach ($data["checkIds"] as $id) {
			if ($data["isCheckOn"]) {
				$checkIds[$id] = true;
			} else {
				unset($checkIds[$id]);
			}
		}
		
		// 入れ替え。
		Session::replaceData($data["attendance_checkIdsKey"], $checkIds, "attendance_checkIds");
		
		setJsonHeader();
		echo json_encode(["attendance_checkExists"=>empty($checkIds) ? 0 : 1]);
	
	}

	// 打刻連携処理
	public function attendanceAlignmentAction(&$form) {

		// 連携処理の開始
		$param = $form['attendance_log_ids'];
		AttendanceLogService::attendanceBatchAlignment($this->contractor_id, $param, true);

		// セッション内のチェックしたIDをリセット
		Session::replaceData($form["attendance_checkIdsKey"], NULL, "attendance_checkIds");

		sendCompleteRedirect("./", "連携処理を終了しました。") ;
	
	}
	
}
