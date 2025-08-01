<?php

/* dev founder luyi */

class OperationLogController extends UserBaseController {
	
	public $devices;
	
	public $groups;
	
	public $groupsDisplay;
	
	public $facet_operate_types;
	
	public $facefc_main_types;
	
	// @Override
	public function prepare(&$form) {
		
		// sessionからプルダウンを取得。
		parent::prepare($form);
		
		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);
		
		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);
		
		// 表示用グループ。(キーはID)
		$noGroupDeviceIds = array_filter(array_keys($this->devices),function ($device_id)  {
			foreach ($this->groups as $gid=>["deviceIds"=>$deviceIds]){
				if (in_array($device_id,$deviceIds)){
					return false;
				}
			}
			return true;
		});
		$this->groupsDisplay = array();
		foreach ($this->groups as $gid=>["deviceIds"=>$deviceIds,"group_name"=>$group_name]) {
			$this->groupsDisplay[$gid] = array("deviceIds" => $deviceIds, "group_name" => $group_name);
		}
		$this->groupsDisplay["-1"] = array("deviceIds"=>$noGroupDeviceIds,"group_name"=>"未登録");
		
		// Facet 操作区分を設定。
		$tmp_facet_operate_types =  DB::selectOneArray("SELECT DISTINCT operate_type FROM t_facet_operate_log_type");
		array_unshift($tmp_facet_operate_types, '0');
		unset($tmp_facet_operate_types[0]);
		$this->facet_operate_types = $tmp_facet_operate_types;
		
		// FaceFC メインタイプ設定。
		$this->facefc_main_types = [
			1=>"操作",
			2=>"異常",
			3=>"情報"];
		
		// 初期表示時、グループとカメラは全て選択に設定される。
		if(empty($form["searchInit"])) {
			$form["facefc_group_ids"] = array_keys($this->groupsDisplay);
			$form["facefc_device_ids"] = array_keys($this->devices);
		}
	}
	
	// 画面初期表示。
	public function indexAction(&$form) {
		
		Filters::ref($form)->at("tab","facet")->values(["facet", "facefc"]);
		
		$this->indexFacet($form);
		$this->indexFacefc($form);
		
		return "operationLog.tpl";
	}
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：facetログ。
	// ---------------------------------------------------------------------------------------------------------------------------
	public function indexFacet(&$form) {
		$form["facet_date_from"] = $form["facet_date_to"] = date('Y/m/d');
	}
	
	// 検索。
	public function facetSearchAction(&$form) {
		$data = Filters::ref($form)
			->at("facet_limit", 20)->enum(Enums::pagerLimit())
			->at("facet_pageNo", 1)->digit()
			->at("facet_date_from")->date()
			->at("facet_date_to")->date()
			->at("facet_account_id")->len(100)
			->at("facet_account_name")->len(100)
			->at("facet_operate_type")->enum($this->facet_operate_types)
			->getFilteredData();
		
		$pageInfo = new PageInfo($data["facet_pageNo"], $data["facet_limit"]);
		$data["contractor_id"] = $this->contractor_id;
		$data["facet_operate_type"] = isset($this->facet_operate_types[$data["facet_operate_type"]]) ? $this->facet_operate_types[$data["facet_operate_type"]] : "";
		$list = UiOperationLogService::getFacetLogList($pageInfo, $data);

		$this->assign("facet_pagerLimit", Enums::pagerLimit());
		$this->assign("facet_pageInfo", $pageInfo);
		$this->assign("facet_list", $list);
		
		return "operationLog.tpl";
	}
	
	// Facetログ：詳細取得。
	public function facetDetailAction(&$form) {
		
		$this->facetSearchAction($form);
		
		$data = Filters::ref($form)
			->at("facet_operate_log_id")->digit()
			->getFilteredData();
		
		$detail = UiOperationLogService::getFacetLogDetail($data);
		$this->assign("facet_detail", $detail);
		
		return "operationLog.tpl";
	}
	
	// ---------------------------------------------------------------------------------------------------------------------------
	// ---------------------------------------------------------------------------------------------------------------------------
	// ------------------------------------------------- タブ：FaceFCログ。
	// ---------------------------------------------------------------------------------------------------------------------------
	public function indexFacefc(&$form) {
		$form["facefc_date_from"] = $form["facefc_date_to"] = date('Y/m/d');
	}
	
	// FaceFCログ：検索。
	public function facefcSearchAction(&$form) {
		$data = Filters::ref($form)
			->at("facefc_limit", 20)->enum(Enums::pagerLimit())
			->at("facefc_pageNo", 1)->digit()
			->at("facefc_date_from")->date()
			->at("facefc_date_to")->date()
			->at("facefc_account_id")->len(100)
			->at("facefc_group_ids")->enumArray($this->groupsDisplay)
			->at("facefc_device_ids")->enumArray($this->devices)
			->at("facefc_main_type")->enum($this->facefc_main_types)
			->at("facefc_sub_type")->len(100)
			->getFilteredData();
		
		$pageInfo = new PageInfo($data["facefc_pageNo"], $data["facefc_limit"]);
		$data["contractor_id"] = $this->contractor_id;
		$data["facefc_main_type"] = isset($this->facefc_main_types[$data["facefc_main_type"]]) ? $this->facefc_main_types[$data["facefc_main_type"]] : "";
		$list = UiOperationLogService::getFacefcLogList($pageInfo, $data);
		
		$this->assign("facefc_pagerLimit", Enums::pagerLimit());
		$this->assign("facefc_pageInfo", $pageInfo);
		$this->assign("facefc_list", $list);
		
		return "operationLog.tpl";
	}
	
	// FaceFCログ：詳細取得。
	public function facefcDetailAction(&$form) {
		
		$this->facefcSearchAction($form);
		
		$data = Filters::ref($form)
			->at("facefc_operate_log_id")->digit()
			->getFilteredData();
		
		$detail = UiOperationLogService::getFacefcLogDetail($data);
		$this->assign("facefc_detail", $detail);
		
		return "operationLog.tpl";
	}
}