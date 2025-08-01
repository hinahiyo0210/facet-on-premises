<div class="terminal_cnt">
	<script>
		// システム情報を取得
		function doListFetchFwInfo(serialNo,el) {
			let row = $(el).parents('tr.device_list_row')

			showModal("カメラver番号/型番の読み込み", $("#diviceConfigGetModalTemplate").html())
			$("#modal_message .diviceConnectModal_loading").show()
			$("#modal_message .diviceConnectModal_error").hide()
			$("#modal_message .diviceConnectModal_device_name").text(row.find('td.description').text())

			doAjax("/api1/system/getSystemInfo", { serialNo: serialNo, 'ds-api-token': "{$contractor['api_token']}" }, function(data) {

				if (data.error) {
					$("#modal_message .diviceConnectModal_loading").hide()
					$("#modal_message .diviceConnectModal_error").show()
					$("#modal_message .diviceConnectModal_error_msg").append(escapeHtml(data.error))
					return
				}
				row.find('td.device_type').text(data.deviceType)
				row.find('td.fw_ver').text(data.softwareVersion)
				row.find('td.last_get_systemInfo').text(data.lastGetSystemInfo)

				if (document.getElementById('connect_status') != null) {
					row.find('td.connect_status').find('i.fas').removeClass(row.find('td.connect_status').find('i.fas').attr('class')).addClass('fas fa-check-circle').css('color','green')
				}

				removeModal()
			})

		}
		// カメラの変更。
		function doListModDevice(deviceId) {
			$('#list_mod_init_device_id').val(deviceId);

			{if isset($list_list)}
			$("input[name='list_pageNo']").val("{$list_pageInfo->getPageNo()}");
			{/if}

			doPost('./modDeviceInit', true);


		}
		// multi select
		$(function() {
			$("#device_search_m1").fSelect();
			$("#device_search_m2").fSelect();

			if($("input[name='device_search_serial_no']").val()){
				$(".fs-label-wrap").addClass("fs-label-wrap_disabled");
			}

			$("#device_search_m1").on('pulldownChange',function () {
				showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
				const $wrap = $(this).closest('.fs-wrap')
				$("#modal_message #groupsChangeModalBtnCancel").click(function () {
					$wrap.fSelectedValues($wrap.data('oldVal'))
					removeModal()
				})

				$("#modal_message #groupsChangeModalBtnOk").click(function () {
					const newVal = $wrap.fSelectedValues()
					$wrap.data('oldVal',newVal)
					const $deviceSelect = $("#device_search_m2")
					const dids = newVal.flatMap(gid=>groups[gid])
					$deviceSelect.empty()
					devices.forEach(device=>{
						if (dids.indexOf(device.id)===-1) {
							return
						}
						{literal}
						$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
						{/literal}
					})
					$deviceSelect.data('fSelect').destroy()
					$deviceSelect.data('fSelect').create()
					removeModal()
				})
			})

			$("input[name='device_search_serial_no']").blur(function () {
				if($(this).val()){
					cameraPulldownInit()
					$(".fs-label-wrap").addClass("fs-label-wrap_disabled")
				}else{
					$(".fs-label-wrap").removeClass("fs-label-wrap_disabled")
				}
			})

			// グループ選択を初期リセット
			function cameraPulldownInit(){
				// グループ選択を初期リセット
				if(groupsInit.length>0) {
					const $groupSelect = $("#device_search_m1");
					$groupSelect.empty();
					groupsInit.forEach(group => {
						{literal}
						$groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
						{/literal}
					});
					$groupSelect.data('fSelect').destroy();
					$groupSelect.data('fSelect').create();
				}
				// カメラ選択を初期リセット
				const $deviceSelect = $("#device_search_m2");
				$deviceSelect.empty();
				devices.forEach(device=>{
					{literal}
					$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
					{/literal}
				});
				$deviceSelect.data('fSelect').destroy();
				$deviceSelect.data('fSelect').create();
			}
		})
		function doListDevice() {
			if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
				const $session_key = $('input[name="_form_session_key"]')
				const data = [$('#device_search_m1'), $('#device_search_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				})
				doAjax('../session/setSession', {
					session_key: $session_key.val(),
					value: JSON.stringify(data)
				}, (res) => {
					if (!res.error) {
						$session_key.val(res['session_key'])
						$('input[name="device_search_search_init"]').val(1)
						doGet('./listSearch', true)
					} else {
						alert(JSON.stringify(res))
					}
				}, (errorRes) => {
					alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
				});
			}
		}
	</script>
	<h3 class="tit_cnt_main">カメラ設定</h3>
	<p class="cap_cnt_main">シリアルナンバーに対して、カメラの名称やグループを設定してください。<br>
		複数台の管理の際に、わかりやすい名前をつけることで管理性、操作性が向上します。<br>
		また、カメラのFWバージョン・型番を確認する場合は取得ボタンを押してください。
	</p>


	<h3 class="tit_cnt_main">シリアルNoから検索</h3>
	<br>
	<table class="form_cnt">
		<tr >
			<th>シリアルNo</th>
			<td>
				<input name="device_search_serial_no" type="text" maxlength="11" placeholder="" value="{$form.device_search_serial_no|default:""}" />
			</td>
		</tr>
	</table>
</div>

<div id="registDeviceDescription" class="terminal_cnt">
	<h3 class="tit_cnt_main">グループ・カメラから選択 ※シリアルNoを未入力にする必要があります</h3>
	<br>
	<div class="search_area">
		<table class="form_cnt">
			<tr >
				<th class="fs-select-th-center">グループ選択</th>
				<td>
					{* mod-start founder feihan *}
					{assign var=devicesDisplay value=[]}
					<select id="device_search_m1" class="groups hidden" name="device_search_group_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
						{foreach $groupsDisplay as $g=>$group}
							{$selected = ""}
							{if exists($form["device_search_group_ids"]|default:"", $g)}
								{$selected = "selected"}
								{$devicesDisplay=array_merge($devicesDisplay,$group.deviceIds)}
							{/if}
							<option value="{$g}" {$selected}>{$group.group_name}</option>
						{/foreach}
					</select>
					{* mod-end founder feihan *}
				</td>
			</tr>
			<tr >
				<th class="fs-select-th-center">カメラ選択</th>
				<td>
					<select id="device_search_m2" class="devices hidden" name="device_search_device_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
						{foreach $devices as $d=>$device}
							{if exists($devicesDisplay, $d)}
								<option value="{$d}" {if exists($form["device_search_device_ids"]|default:"", $d)}selected{/if}>{$device.name}</option>
							{/if}
						{/foreach}
					</select>
				</td>
			</tr>
		</table>
		<a href="javascript:void(0)" onclick="doListDevice()" class="enter-submit btn_red"><i class="fas fa-search"></i>検索実行</a>
	</div>
	<input type="hidden" name="_form_session_key" value="{$form._form_session_key|default:""}" />
	<input type="hidden" id="device_search_search_init" name="device_search_search_init" value="{$form.device_search_search_init|default:""}" />
	<input type="hidden" id="list_mod_init_device_id" name="list_mod_init_device_id" />

	{if isset($list_list)}
		<div class="search_results">
			<div class="tit_wrap">
				<h3 class="tit">検索結果</h3>
			</div>

			{include file="../_inc/pager_counter_device.tpl" pageInfo=$list_pageInfo topPager=true}

			<table class="search_results_table">
				<tr>
					<th class="results_oder">No</th>
					<th class="results_id">シリアルNo</th>
					<th class="results_name">カメラグループ</th>
					<th class="results_group">カメラ名称</th>
					{if Session::getLoginUser("apb_mode_flag")}
					<th class="results_group">APB</th>
					<th class="results_group">APB状態</th>
					{/if}
					<th class="results_group">型番</th>
					<th class="results_group">バージョン番号</th>
					<th class="results_group">最終取得日時</th>
					{if Session::getLoginUser("getsysteminfo_time")}
					<th class="results_connect">接続状況</th>
					{/if}
					<th class="results_btn">ver番号/型番</th>
					<th class="results_btn">編集</th>
				</tr>
				{foreach $list_list as $item}
					<tr id="list_user_tr_{$item.device_id}" class="device_list_row">
						<td class="sort_order">{$item.sort_order}</td>
						<td class="serial_no">{$item.serial_no}</td>
						<td class="device_group_name">{$item.device_group_name|default:""}</td>
						<td class="description">{$item.description|default:""}</td>
						{if Session::getLoginUser("apb_mode_flag")}
						<td class="device_apb">{(!empty($item.apb_group_id))?'有効':'無効'}</td>
						<td class="device_apb_type">{$apbTypes[$item.apb_type]|default:""}</td>
						{/if}
						<td class="device_type">{$item.device_type}</td>
						<td class="fw_ver">{$item.fw_ver}</td>
						<td class="last_get_systemInfo">{$item.last_get_systemInfo}</td>
						{if Session::getLoginUser("getsysteminfo_time")}
						<td class="connect_status" id="connect_status" style="font-size:1.5rem;margin:auto 0.8rem;">
							{if strtotime($item.last_get_systemInfo) > strtotime("-{Session::getLoginUser("getsysteminfo_time")} minute")}<i class="fas fa-check-circle" style="color:green;"></i>
							{else}<i class="fas fa-times-circle" style="color:red;"></i>
							{/if}
						</td>
						{/if}
						<td><a class="btn_small" href="javascript:void(0)" onclick="doListFetchFwInfo('{$item.serial_no}',this); return false">取得</a></td>
						<td><a class="btn_small" href="javascript:void(0)" onclick="doListModDevice('{$item.device_id}'); return false">編集</a></td>
					</tr>
				{/foreach}
			</table>

		</div>

		{include file="../_inc/pager_counter_device.tpl" pageInfo=$list_pageInfo topPager=false}

	{/if}

<!-- ログインユーザー変更モーダル -->
{if !empty($list_modDevice)}

	<script>
		$(function() {

			let openCallback = function() {

				$("#device_mod_modal_template").remove();

				// プルダウンの初期化
				$("#list_mod_group_id").fSelect();
			};

			let closeCallback = null;
			let noClearError = true;
			let appendTarget = 'form[name="registForm"]';

			showModal("カメラの変更", $("#device_mod_modal_template").html(), "device_mod_modal", openCallback, closeCallback, noClearError, appendTarget);
		});

	</script>
	<div id="device_mod_modal_template" style="display:none">
		<input type="hidden" name="list_mod_back" value="{$form.list_mod_back|default:""}" />
		<input type="hidden" name="list_pageNo" value="{$list_pageInfo->getPageNo()}" />
		<input type="hidden" id="list_mod_device_id" name="list_mod_device_id" value="{$form.list_mod_device_id|default:""}" />
		<table class="form_cnt regist_cnt device_manage_modal" style="max-width:80%;">
			<tr>
				<th class="tit_sub">シリアルNo</th>
				<td>{$form.list_mod_serial_no|default:""}</td>
			</tr>
			<tr>
				<th class="tit_sub">名称</th>
				<td ><input name="list_mod_description" value="{$form.list_mod_description|default:""}" type="text" placeholder="任意のカメラ名をご入力ください"></td>
			</tr>
			<tr>
				<th class="tit_sub fs-select-th-center">グループ</th>
				<td>
					<select id="list_mod_group_id" class="hidden" name="list_mod_group_id" >
						<option value="">グループを選択</option>
						{foreach $groups as $g}
							<option value="{$g.device_group_id}" {if $form.list_mod_group_id|default:"" == $g.device_group_id} selected {/if}>{$g.group_name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<th class="tit_sub">No</th>
				<td ><input name="list_mod_sort_order" value="{$form.list_mod_sort_order|default:""}" type="text"></td>
			</tr>
			<tr>
				<th class="tit_sub">PUSH転送先</th>
				<td ><input name="list_mod_push_url" value="{$form.list_mod_push_url|default:""}" type="text"></td>
			</tr>
			{*  add-start verson3.0 founder feihan *}
			{if Session::getLoginUser("enter_exit_mode_flag") == 1}
				<tr>
					<th>カメラ機能</th>
					<td>
						<p class="select" style="max-width:578px;width:100%;">
							<select name="list_mod_device_role">
								<option value="">&nbsp;</option>
								{foreach $deviceRoles as $device_role=>$deviceRole}
									<option {if $form.list_mod_device_role|default:"" == $device_role}selected{/if} value="{$device_role}" >{$deviceRole.device_role_name}</option>
								{/foreach}
							</select>
						</p>
					</td>
				</tr>
			{/if}
			{if !$form.enableAws}
				<tr>
					<th>画像チェックデバイス</th>
					<td>
						<input {if $form.list_mod_picture_check_device_flag|default:"" == 1}checked{/if} name="list_mod_picture_check_device_flag" id="checkPicture" type="checkbox" value="1"><label for="checkPicture" class="checkbox" style="display:inline"></label>
					</td>
				</tr>
			{/if}
			{*  add-end verson3.0 founder feihan *}
			{if Session::getLoginUser("apb_mode_flag")}
				<tr>
					<th class="tit_sub">APB入退室設定</th>
					<td>
						<p class="select" style="width:100%;">
							<select name="list_mod_apb_type">
								<option value="">利用しない</option>
								<option {if $form.list_mod_apb_type|default:"" == 1}selected{/if} value="1">入室用</option>
								<option {if $form.list_mod_apb_type|default:"" == 2}selected{/if} value="2">退室用</option>
								<option {if $form.list_mod_apb_type|default:"" == 3}selected{/if} value="3">入室用(認証時APB制御なし)</option>
							</select>
						</p>
					</td>
				</tr>
				{*
                    <tr>
                        <th class="tit_sub">APBグループ設定</th>
                        <td style="padding-bottom:5em;">
                            {$name=concat("device_in_apb_groups_", $d.device_id)}
                            {foreach $apbGroups as $g}
                                <input type="checkbox" name="{$name}[]" value="{$g.apb_group_id}" id="{seqId()}" {if exists($form[$name], $g.apb_group_id)}checked{/if}><label for="{seqId(1)}" class="checkbox">{$g.apb_group_name}</label>
                            {/foreach}
                        </td>
                    </tr>
                *}
				<tr>
					<th class="tit_sub">APB設定</th>
					<td style="padding-bottom:5em;">
						{foreach $apbGroups as $g}
							<input type="checkbox" name="list_mod_apb_group_id[]" value="{$g.apb_group_id}" id="{seqId()}" {if exists($form.list_mod_apb_group_id|default:"", $g.apb_group_id)}checked{/if}><label for="{seqId(1)}" class="checkbox">有効</label>
						{/foreach}
					</td>
				</tr>
			{/if}
		</table>

		<div class="btns" style="margin-top:2em">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
			<a href="javascript:void(0)" onclick="doPost('./modDevice', false)" class="enter-submit btn btn_red btn_regist" enter-submit-target=".userAuth_mod_modal">設定反映</a>
		</div>

	</div>
{/if}
</div>


