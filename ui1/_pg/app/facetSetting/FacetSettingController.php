<?php 

class FacetSettingController extends UserBaseController {

	public $personTypes;

	public $devices;
	
	public $groups;
	
	public $switchingTime;

	public $contractor;

	public $teamspiritSetting;

	public function prepare(&$form)
	{
		// sessionからプルダウンを取得。
		parent::prepare($form);

		// デバイスを取得。(キーはID)
		$this->devices = DeviceGroupService::getDevices($this->contractor_id);
		
		// グループ設定を取得。(キーはID)
		$this->groups = DeviceGroupService::getGroups($this->contractor_id);
		
		// 契約者情報を取得。
		$this->contractor = DB::selectRow("select * from m_contractor where contractor_id = {value}", $this->contractor_id);
		$this->contractor['deviceList'] = ContractorService::getDeviceList($this->contractor_id);	

		// TeamSpirit連携情報を取得
		$this->teamspiritSetting = DB::selectRow("select * from m_teamspirit_set where contractor_id = {value}", $this->contractor_id);

		// 入退数リセット時間の取得
		if (empty($form['switch_device_group_id']) && !empty($this->groups)) {
			$form['switchingTime'] = DB::selectOne("select switching_time from t_device_group where device_group_id = {value}", current($this->groups)["device_group_id"]);
		}

		// 区分を取得
		$this->personTypes = DB::selectKeyRow("select person_type_code, person_type_name from m_person_type where contractor_id = {value}", $this->contractor_id, "person_type_code");
		$this->assign("personTypeList", $this->personTypes);

		// facetのバージョン取得
		$this->assign("facetVersion", FACET_VERSION);

	}

	private function completeRedirect($msg, $appendParam = "") {
		
		// スクロール位置。
		$p = Filter::len(req("_p"), 5);
		sendCompleteRedirect("./?_p=".urlencode($p).$appendParam, $msg);
	}
	
	public function indexAction(&$form) {

		$this->assign("personTypeList", $this->personTypes);
		
		return "facetSetting.tpl";
	}


	/*======================================================================================*/
	/*====================================リセット時間設定====================================*/
	/*======================================================================================*/

	public function saveTimeAction(&$form) {

		$data = Validators::set($form)
			->at("switch_device_group_id" , "時間変更グループID")->required()->dataExists(false, "select 1 from t_device_group where device_group_id = {value}")
			->at("switching_time"		  , "指定リセット時間"  )->required()->digit(0,23)
		->getValidatedData();

		// バリデーションエラーの場合は画面戻る
		if (Errors::isErrored()) return "facetSetting.tpl";

		$param = [];
		$param["contractor_id"]   = $this->contractor_id;
		$param["device_group_id"] = $data["switch_device_group_id"];
		$param["switching_time"]  = $data["switching_time"];

		// DB更新
		$sql = "update t_device_group set switching_time = {switching_time} where contractor_id = {contractor_id} and device_group_id = {device_group_id}";
		DB::update($sql, $param);
		
		$this->completeRedirect("入退室数リセット時間の更新が完了しました。");

	}

	public function changeGroupAction(&$form) {

		$param = [];
		$param["contractor_id"]   = $this->contractor_id;
		$param["device_group_id"] = $form["switch_device_group_id"];

		$switchingTime = DB::selectOne("select switching_time from t_device_group where contractor_id = {contractor_id} and device_group_id = {device_group_id}", $param);
		
		if (!empty($switchingTime)) $form['switchingTime'] = $switchingTime;
		$form['switch_device_group_id'] = $form['switch_device_group_id'];

		return "facetSetting.tpl";

	}


	/*======================================================================================*/
	/*=======================================区分設定=======================================*/
	/*======================================================================================*/

	public function registPersonTypeAction(&$form) {

		// 登録上限数の取得＆ハンドリング
		$registLimit     = 10;
		$countPersonType = DB::selectOne("SELECT COUNT(person_type_id) FROM m_person_type WHERE contractor_id = {value}", $this->contractor_id);
		if ($countPersonType >= $registLimit) {
			Errors::add("区分登録数", "区分登録数が上限「{$registLimit}」に達しています。");
			return "facetSetting.tpl";
		}

		// バリデーション
		$data = Validators::set($form)
		->at("registPersonTypeText", "区分名")->required()->maxlength(32)
		->getValidatedData();

		// バリデーションエラーの場合は画面戻る
		if (Errors::isErrored()) return "facetSetting.tpl";

		// DB更新
		$param = [];
		$param["user_id"]		   = $this->user_id;
		$param['person_type_name'] = $data['registPersonTypeText'];
		PersonService::registPersonType($this->contractor, $param);

		$this->completeRedirect("区分の登録が完了しました。");
	}

	public function editPersonTypeAction(&$form) {

		// バリデーション
		$data = Validators::set($form)
		->at("editPersonType", "区分コード")->required()->digit(1,100000)
		->at("editPersonTypeText", "更新区分名")->required()->maxlength(32)
		->getValidatedData();

		// バリデーションエラーの場合は画面戻る
		if (Errors::isErrored()) return "facetSetting.tpl";

		// DB更新
		$param = [];
		$param['user_id']		   = $this->user_id;
		$param['override']         = 1;
		$param['person_type_code'] = $data['editPersonType'];
		$param['person_type_name'] = $data['editPersonTypeText'];
		PersonService::registPersonType($this->contractor, $param);

		// 完了したらリダイレクト
		$this->completeRedirect("区分の更新が完了しました。");
	}

	public function deletePersonTypeAction(&$form) {

		// バリデーション
		$data = Validators::set($form)
		->at("deletePersonType", "区分コード")->required()->digit(1,100000)
		->getValidatedData();

		// バリデーションエラーの場合は画面戻る
		if (Errors::isErrored()) return "facetSetting.tpl";

		// DB更新
		PersonService::deletePersonType($this->contractor, $data['deletePersonType']);

		// 完了したらリダイレクト
		$this->completeRedirect("区分の削除が完了しました。");
	}


	/*=============================================================================================*/
	/*====================================TeamSpirit連携情報設定====================================*/
	/*=============================================================================================*/

	public function saveTsSettingAction(&$form) {

		$data = Validators::set($form)
			->at("tsSet" 	  , "条件設定"	    	)->flag()
			->at("tsUserName" , "ユーザー名"		)->required()->mail()->maxlength(50)
			->at("tsUserPass" , "ユーザーパスワード")->required()->maxlength(20)
			->getValidatedData();

		// バリデーションエラーの場合は画面戻る
		if (Errors::isErrored()) return "facetSetting.tpl";

		// パラメータ格納
		$param = [];
		$param["contractor_id"] = $this->contractor_id;
		$param["user_name"] 	= $data["tsUserName"];
		$param["password"]  	= $data["tsUserPass"];
		$param["conditions_set"]= ($data["tsSet"]) ? 1 : null;

		// 既に登録済みかの確認
		$existsTsInfo = DB::selectRow('select * from m_teamspirit_set where contractor_id = {contractor_id}', $param);

		// 既に登録されている場合は更新、されていない場合は新規登録
		if (empty($existsTsInfo)) {

			// DB新規登録
			$sql = "insert into 
						m_teamspirit_set 
					set 
						contractor_id 	 = {contractor_id}
						, criate_time 	 = now()
						, update_time 	 = now()
						, user_name   	 = {user_name}
						, password    	 = {password}
						, conditions_set = {conditions_set}";

			DB::insert($sql, $param);

		} else {
			
			// DB更新
			$sql = "update 
						m_teamspirit_set 
					set 
						user_name 	  = {user_name}
						, password 	  = {password} 
						, conditions_set = {conditions_set}
						, update_time = now()
					where 
						contractor_id = {contractor_id}";
			DB::update($sql, $param);

		}
		
		$this->completeRedirect("TeamSpirit連携情報の保存が完了しました。");

	}

	// OAuth認証の疎通確認
	public function oauthCheckAction(&$form) {

		$data = Validators::set($form)
		->at("tsUserName" , "ユーザー名"		)->required()->mail()
		->at("tsUserPass" , "ユーザーパスワード")->required()->maxlength(20)
		->getValidatedData();

		// バリデーションエラーの場合は画面戻る
		if (Errors::isErrored()) return "facetSetting.tpl";
		
		// パラメータ格納
		$param = [];
		$param["contractor_id"] = $this->contractor_id;
		$param["user_name"] 	= $data["tsUserName"];
		$param["password"]  	= $data["tsUserPass"];

		if (AttendanceLogService::oauthCheck($param)) {
			$form["oauthResult"] = 'OK';
		} else {
			$form["oauthResult"] = 'NG';
		}

		$this->assign("inputTSinfo", $param);
		return "facetSetting.tpl";

	}

	
}
