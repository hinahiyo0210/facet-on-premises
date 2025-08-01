<?php 

class ApbLogController extends UserBaseController {
	
	public $devices;

	public $groups;
	
	public $groupsDisplay;
	
	// @Override
	public function prepare(&$form) {
		
		if (!Session::getLoginUser("apb_mode_flag")) response400();
		
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
		ApbService::createApbLogEnum();
		
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
			$form["trans_group_ids"] = array_keys($this->groupsDisplay);
			$form["trans_device_ids"] = array_keys($this->devices);
			$form["include_no_trans"] = 1;
		}
		
		$data = Filters::ref($form)
			->at("limit" 	, 20    )->enum(Enums::pagerLimit())
			->at("pageNo" 		    )->digit()
			->at("searchInit"		)->digit()
			->at("group_ids"		)->enumArray($this->groupsDisplay)
			->at("device_ids"	    )->enumArray($this->devices)
			->at("date_from"	    )->date()
			->at("date_to"		    )->date()
			->at("log_type"		    )->enum(SimpleEnums::getAll("apb_log_type"))
			->at("log_level"	    )->values(["I", "W", "E"])
			->at("person_code"	    )->len(100)
			->at("trans_group_ids"	)->enumArray($this->groupsDisplay)
			->at("trans_device_ids" )->enumArray($this->devices)
			->at("include_no_trans" )->values(["0", "1"])
		->getFilteredData();
		
		// 検索。
		$pageInfo = new PageInfo($data["pageNo"], $data["limit"]);
		$list = ApbService::getApbLogList($pageInfo, $this->contractor_id, $data, $this->devices);
			
		$this->assign("pagerLimit", Enums::pagerLimit());
		$this->assign("pageInfo", $pageInfo);
		$this->assign("list", $list);
		return "apbLog.tpl";
	}
	
	
	
	
}
