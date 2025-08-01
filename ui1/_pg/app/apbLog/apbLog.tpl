{$title="APBログ一覧"}{$icon="fa-outdent"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}

<script src="/ui1/static/js/fselect/fSelect.js"></script>
<script>
	let auth_group = {Session::getLoginUser("group_id")}

	{* fSelect用定数 *}
	const groups = { };
	const groupsInit = [];
	{if empty(Session::getLoginUser("group_id"))}
	{foreach $groupsDisplay as $gid=>$group}
	groups[{$gid}] = [{foreach $group.deviceIds as $deviceId} '{$deviceId}', {/foreach}];
	groupsInit.push({ id: '{$gid}', name: '{$group.group_name|escape:"javascript"}' });
	{/foreach}
	{/if}
	const devices = [];
	{foreach $devices as $d=>$device}
	devices.push({ id: '{$d}', name: '{$device.name|escape:"javascript"}' });
	{/foreach}
</script>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">

<script>
	$(function() {
		$('#m1').fSelect();
		$('#m2').fSelect();
		$('#trans_m1').fSelect();
		$('#trans_m2').fSelect();
		$('#m1').on('pulldownChange', function() {
			showModal('カメラ選択の初期化', $("#groupsChangeModalTemplate").html());
			const $wrap = $(this).closest('.fs-wrap');
			$('#modal_message #groupsChangeModalBtnCancel').click(function() {
				$wrap.fSelectedValues($wrap.data('oldVal'));
				removeModal();
			});
			$('#modal_message #groupsChangeModalBtnOk').click(function() {
				const newVal = $wrap.fSelectedValues();
				$wrap.data('oldVal', newVal);
				const $deviceSelect = $('#m2');
				const dids = newVal.flatMap(gid => groups[gid]);
				$deviceSelect.empty();
				devices.forEach(device => {
					if (dids.indexOf(device.id) === -1) {
						return;
					}
					{literal}
					$deviceSelect.append(`<option value='${device.id}' selected>${device.name}</option>`);
					{/literal}
				});
				$deviceSelect.data('fSelect').destroy();
				$deviceSelect.data('fSelect').create();
				removeModal();
			});
		});
		$('#trans_m1').on('pulldownChange', function() {
			showModal('カメラ選択の初期化', $("#groupsChangeModalTemplate").html());
			const $wrap = $(this).closest('.fs-wrap');
			$('#modal_message #groupsChangeModalBtnCancel').click(function() {
				$wrap.fSelectedValues($wrap.data('oldVal'));
				removeModal();
			});
			$('#modal_message #groupsChangeModalBtnOk').click(function() {
				const newVal = $wrap.fSelectedValues();
				$wrap.data('oldVal', newVal);
				const $deviceSelect = $('#trans_m2');
				const dids = newVal.flatMap(gid => groups[gid]);
				$deviceSelect.empty();
				devices.forEach(device => {
					if (dids.indexOf(device.id) === -1) {
						return;
					}
					{literal}
					$deviceSelect.append(`<option value='${device.id}' selected>${device.name}</option>`);
					{/literal}
				});
				$deviceSelect.data('fSelect').destroy();
				$deviceSelect.data('fSelect').create();
				removeModal();
			});
		});
	});

	// 検索
	function logSearch() {
		if ((typeof(auth_group) === 'number') || ($('.fs-dropdown').eq(0).hasClass('hidden') && $('.fs-dropdown').eq(2).hasClass('hidden'))) {
			const $session_key = $('input[name="_form_session_key"]');
			if ($('#m1').length) {
				var data = [$('#m1'), $('#m2'), $('#trans_m1'), $('#trans_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
				});
			} else {
				var data = [$('#m2'), $('#trans_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
				});
			}
			doAjax('../session/setSession', {
				session_key: $session_key.val(),
				value: JSON.stringify(data)
			}, (res) => {
				if (!res.error) {
					$session_key.val(res['session_key']);
					$('input[name="searchInit"]').val(1);
					document.searchForm.submit();
				} else {
					alert(JSON.stringify(res));
				}
			}, (errorRes) => {
				alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
			});
		}
	}
</script>

<form action="./" name="searchForm">
	<input type="hidden" name="_form_session_key" value="{$form._form_session_key|default:""}">
	<input type="hidden" name="searchInit" value="{$form.searchInit}">
	<input type="hidden" name="limit" value="{$form.limit}">
	<input type="hidden" name="pageNo" value="1">

	<div class="setting_area">
		{assign var=devicesDisplay value=[]}
		{if empty(Session::getLoginUser("group_id"))}
		<div class="setting_cnt">
			<p class="tit tit_apb no_colon">認識カメラ</p>
		</div>
		<div class="setting_cnt">
			<p class="tit tit_apb">&emsp;&emsp;グループ選択</p>
			<div>
				<select id="m1" class="groups hidden" name="group_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
					{foreach $groupsDisplay as $g=>$group}
						{$selected = ""}
						{if exists($form["group_ids"], $g)}
							{$selected = "selected"}
							{$devicesDisplay=array_merge($devicesDisplay, $group.deviceIds)}
						{/if}
						<option value="{$g}" {$selected}>{$group.group_name}</option>
					{/foreach}
				</select>
			</div>
		</div>
		{else}
			{$devicesDisplay=array_keys($devices)}
		{/if}
		<div class="setting_cnt">
			<p class="tit tit_apb">&emsp;&emsp;カメラ選択</p>
			<div>
				<select id="m2" class="devices hidden" name="device_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
					{foreach $devices as $d=>$device}
						{if exists($devicesDisplay, $d)}
							<option value="{$d}" {if exists($form["device_ids"], $d)}selected{/if}>{$device.name}</option>
						{/if}
					{/foreach}
				</select>
			</div>
		</div>

		<div class="setting_cnt">
			<p class="tit tit_apb">期間選択</p>
			<div class="period">
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr" data-position="above" data-allow-input="true" placeholder="{date('Y/m/d', strtotime('today -1 week'))}" name="date_from" value="{$form.date_from}">
				</div>
				<span>〜</span>
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr" data-position="above" data-allow-input="true" placeholder="{date('Y/m/d', strtotime('today'))}" name="date_to" value="{$form.date_to}">
				</div>
			</div>
		</div>

		<div class="setting_cnt">
			<p class="tit tit_apb">ログレベル</p>
			<div class="period">
				<input {if $form.log_level == ""}checked{/if} id="{seqId()}" name="log_level" type="radio" value=""><label for="{seqId(1)}" class="radio">全て</label>
				<input {if $form.log_level == "I"}checked{/if} id="{seqId()}" name="log_level" type="radio" value="I"><label for="{seqId(1)}" class="radio">情報</label>
				<input {if $form.log_level == "W"}checked{/if} id="{seqId()}" name="log_level" type="radio" value="W"><label for="{seqId(1)}" class="radio">警告</label>
				<input {if $form.log_level == "E"}checked{/if} id="{seqId()}" name="log_level" type="radio" value="E"><label for="{seqId(1)}" class="radio">エラー</label>
			</div>
		</div>

		<div class="setting_cnt">
			<p class="tit tit_apb">ユーザーID</p>
			<div class="period">
				<input type="text" name="person_code" value="{$form.person_code}" style="text-align:left">
			</div>
		</div>

		{assign var=devicesDisplay value=[]}
		{if empty(Session::getLoginUser("group_id"))}
		<div class="setting_cnt">
			<p class="tit tit_apb no_colon">APB連携先カメラ</p>
		</div>
		<div class="setting_cnt">
			<p class="tit tit_apb">&emsp;&emsp;グループ選択</p>
			<div>
				<select id="trans_m1" class="groups hidden" name="trans_group_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
					{foreach $groupsDisplay as $g=>$group}
						{$selected = ""}
						{if exists($form["trans_group_ids"], $g)}
							{$selected = "selected"}
							{$devicesDisplay=array_merge($devicesDisplay, $group.deviceIds)}
						{/if}
						<option value="{$g}" {$selected}>{$group.group_name}</option>
					{/foreach}
				</select>
			</div>
		</div>
		{else}
			{$devicesDisplay=array_keys($devices)}
		{/if}
		<div class="setting_cnt">
			<p class="tit tit_apb">&emsp;&emsp;カメラ選択</p>
			<div>
				<select id="trans_m2" class="devices hidden" name="trans_device_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
					{foreach $devices as $d=>$device}
						{if exists($devicesDisplay, $d)}
							<option value="{$d}" {if exists($form["trans_device_ids"], $d)}selected{/if}>{$device.name}</option>
						{/if}
					{/foreach}
				</select>
			</div>
			<div style="margin-left: 10px;">
				<input {if $form.include_no_trans == "1"}checked{/if} id="{seqId()}" name="include_no_trans" type="checkbox" value="1">
				<label for="{seqId(1)}" class="checkbox">APB連携先カメラなし</label>
			</div>
		</div>

		<a href="javascript:void(0)" onclick="logSearch()" class="btn_camera enter-submit">ログを表示</a>
	</div>

	<div class="log_wrap apb_log">

		{include file="../_inc/pager_apbLog.tpl"}

		<div class="log_table_wrap">

			<table class="search_results_table">
				<tr>
					<th style="width: 10%" class="time">日時</th>
					<th style="width: 10%" class="camera">認識カメラ</th>
					<th style="width: 8%" class="apb_log_type">ログ種別</th>
					<th style="width: 25%">詳細</th>
					<th style="width: 10%" class="id">ユーザーID</th>
					<th style="width: 10%" class="name">氏名</th>
					<th style="width: 27%" class="trans_camera">APB連携先カメラ</th>
				</tr>
				{foreach $list as $item}
					<tr class="{if startsWith($item.log_type, "E")}apb_error{else if startsWith($item.log_type, "W")}apb_warn{/if}">
						<td>{formatTime($item.create_time)}</td>
						<td>{$devices[$item.device_id].name}</td>
						<td>{$item.log_type}</td>
						<td class="apb_log_detail">{SimpleEnums::get("apb_log_type", $item.log_type)}{if $item.message}　{$item.message}{/if}</td>
						<td>{$item.person_code}</td>
						<td>{$item.person_name}</td>
						<td>{strip}
							{$first=1}
							{foreach explode(",", $item.trans_device_ids) as $trans_device_id}
								{if !$first} / {/if}
								{$devices[$trans_device_id].name|default:""}
								{$first=0}
							{/foreach}
						{/strip}</td>
					</tr>
				{/foreach}

			</table>

		</div>
		{include file="../_inc/pager_apbLog.tpl"}
	</div>

	<!-- カメラプルダウン変更確認モーダル -->
	<div id="groupsChangeModalTemplate" style="display: none;">
		<div>
			<div style="height: 150px;">
				カメラ選択が初期化されますがよろしいですか？
			</div>
			<div class="dialog_btn_wrap btns center">
				<a href="javascript:void(0);" id="groupsChangeModalBtnCancel" class="btn btn_gray" >いいえ</a>
				<a href="javascript:void(0);" id="groupsChangeModalBtnOk" class="btn btn_red">はい</a>
			</div>
		</div>
	</div>
</form>


{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}