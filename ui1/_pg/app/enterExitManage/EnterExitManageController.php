<?php 

class EnterExitManageController extends UserBaseController {
	
	public $groups;

	// @Override
	public function prepare(&$form) {
		
		// sessionからプルダウンを取得。
		parent::prepare($form);
		
		// グループ設定を取得。(キーはID)
		$this->groups   = DeviceGroupService::getGroups($this->contractor_id);
		
	}

	// トップ
	public function indexAction(&$form) {

		$param = [];
		$param["contractor_id"]   = $this->contractor_id;
		if (empty(Session::getLoginUser("group_id"))) {
			$param["device_group_id"] = (!empty($this->groups)) ? current($this->groups)["device_group_id"] : NULL;
		} else {
			$param["device_group_id"] = Session::getLoginUser("group_id");
		}

		$this->indexAssign($param);
		
		return "enterExitManage.tpl";

	}

	public function changeGroupAction(&$form) {

		$param = [];
		$param["contractor_id"]   = $this->contractor_id;
		$param["device_group_id"] = $form["device_group_id"];

		$this->indexAssign($param);
		
		return "enterExitManage.tpl";

	}

	public function indexAssign($param) {

		$personTypes      = DB::selectArray("select * from m_person_type where contractor_id = {contractor_id}", $param);
		$groupTotalCounts = DB::selectKeyRow("select * from t_enter_exit_count where contractor_id = {contractor_id} and device_group_id = {device_group_id} and device_role IS NOT NULL", $param, "device_role");
		$personTypeCounts = DB::selectKeyRow("select * from t_enter_exit_count where contractor_id = {contractor_id} and device_group_id = {device_group_id} and person_type_code IS NOT NULL", $param, "person_type_code");

		$this->assign("personTypes", $personTypes);
		$this->assign("groupTotalCounts", $groupTotalCounts);
		$this->assign("personTypeCounts", $personTypeCounts);

		return;
	}
}
