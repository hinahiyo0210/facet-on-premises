{$title="勤怠ログ一覧"}{$icon="for fa-clipboard-user"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}

<script src="/ui1/static/js/fselect/fSelect.js"></script>
<script>
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
	});

	// 検索
	function logSearch() {
		const $session_key = $('input[name="_form_session_key"]');
		if ($('#m1').length) {
			var data = [$('#m1'), $('#m2')].map($item => {
				return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
			});
		} else {
			var data = [$('#m2')].map($item => {
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

	// チェックボックスチェック時の連携実行ボタンの挙動
	function attendanceCheckRev(data) {
	
		if (data.attendance_checkExists) {
			$(".attendance_btn").removeClass("btn_disabled");
		} else {
			$(".attendance_btn").addClass("btn_disabled");
		}
	}

	// 単体チェック 
	function doThisCheck(elm) {
		
		doAjax("./attendanceCheck", {
			isLocalOnly : "1"
			, isCheckOn : $(elm).prop("checked") ? "1" : "0"
			, checkIds  : $(elm).val()
			, attendance_checkIdsKey : $("input[name='attendance_checkIdsKey']").val()
			
		}, attendanceCheckRev);
	} 

	// 一覧に出ているものに限り全てチェック。
	function doLocalCheckAll(isCheckOn) {
		
		var ids = [];
		$(byNames("attendance_log_ids[]")).each(function() { 
			ids.push($(this).val()); 
			$(this).prop("checked", isCheckOn);
		});
		
		doAjax("./attendanceCheck", {
			isLocalOnly : "1"
			, isCheckOn : isCheckOn ? "1" : "0"
			, checkIds  : ids.join(",")
			, attendance_checkIdsKey : $("input[name='attendance_checkIdsKey']").val()

		}, attendanceCheckRev);

		
	}

	// 連携情報送信
	function doFormSend(action, method) {

		document.searchForm.method = method;
		document.searchForm.action = action;
		document.searchForm.submit();

	}

	// 連携処理開始
	function doAlignment($action, $method) {

		showModal("打刻連携処理", $("#attendance_download_modal_template").html(), null, function($result) {
			doFormSend($action, $method);
		});
		
	}

</script>

<form action="./" name="searchForm">
	<input type="hidden" name="attendance_searchFormKey" value="{$form.attendance_searchFormKey}" />
	<input type="hidden" name="attendance_checkIdsKey" value="{$form.attendance_checkIdsKey}" />
	<input type="hidden" name="_form_session_key" value="{$form._form_session_key}">
	<input type="hidden" name="searchInit" value="{$form.searchInit}">
	<input type="hidden" name="limit" value="{$form.limit}">
	<input type="hidden" name="pageNo" value="1">

	<!-- ダウンロードモーダル -->
	<div id="attendance_download_modal_template" style="display:none">
		連携処理中です。<br >
		対象件数が多い場合、時間が掛かる場合があります。<br >
		<br />
		<div id="loading" class="loader">Loading...</div>
	</div>

	<div class="setting_area">
		{assign var=devicesDisplay value=[]}
		{if empty(Session::getLoginUser("group_id"))}
		<div class="setting_cnt">
			<p class="tit">グループ選択</p>
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
			<p class="tit">カメラ選択</p>
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
			<p class="tit">期間選択</p>
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
			<p class="tit">ログレベル</p>
			<div class="period">
				<input {if $form.log_level == ""}checked{/if} id="{seqId()}" name="log_level" type="radio" value=""><label for="{seqId(1)}" class="radio">全て</label>
				<input {if $form.log_level == "I"}checked{/if} id="{seqId()}" name="log_level" type="radio" value="I"><label for="{seqId(1)}" class="radio">情報</label>
				<input {if $form.log_level == "W"}checked{/if} id="{seqId()}" name="log_level" type="radio" value="W"><label for="{seqId(1)}" class="radio">警告</label>
				<input {if $form.log_level == "E"}checked{/if} id="{seqId()}" name="log_level" type="radio" value="E"><label for="{seqId(1)}" class="radio">エラー</label>
			</div>
		</div>

		<div class="setting_cnt">
			<p class="tit">連携</p>
			<div class="period">
				<input {if $form.decision == ""}checked{/if} id="{seqId()}" name="decision" type="radio" value=""><label for="{seqId(1)}" class="radio">全て</label>
				<input {if $form.decision == "OK"}checked{/if} id="{seqId()}" name="decision" type="radio" value="OK"><label for="{seqId(1)}" class="radio">連携済み</label>
				<input {if $form.decision == "NG"}checked{/if} id="{seqId()}" name="decision" type="radio" value="NG"><label for="{seqId(1)}" class="radio">未連携</label>
			</div>
		</div>

		<div class="setting_cnt">
			<p class="tit">ユーザーID</p>
			<div class="period">
				<input type="text" name="person_code" value="{$form.person_code}" style="text-align:left">
			</div>
		</div>

		<a href="javascript:void(0)" onclick="logSearch()" class="btn_camera enter-submit">ログを表示</a>
	</div>

	<div class="log_wrap attendance_log">

		{include file="../_inc/pager_attendanceLog.tpl"}

		<div class="log_table_wrap">

			<div class="attendance_log_check">
				この一覧に出ているデータについて
				<a href="javascript:void(0)" onclick="doLocalCheckAll(true)">全て選択</a> / <a href="javascript:void(0)" onclick="doLocalCheckAll(false)">全て解除</a>
			</div>
			
			<table class="search_results_table">
				<tr>
					<th style="width: 5%" class="results_checkbox"></th>
					<th style="width: 10%" class="time">打刻日時</th>
					<th style="width: 10%" class="camera">認識カメラ</th>
					<th style="width: 8%" class="attendance_log_type">ログ種別</th>
					<th style="width: 10%" class="id">ユーザーID</th>
					<th style="width: 10%" class="name">氏名</th>
					<th style="width: 10%" class="pass_flag">勤務種別</th>
					<th style="width: 25%">詳細</th>
				</tr>
				{foreach $list as $item}
					<tr class="{if startsWith($item.log_type, "E")}attendance_error{else if startsWith($item.log_type, "W")}attendance_warn{/if}">
						<td>{if $item.decision != 1}<input type="checkbox" name="attendance_log_ids[]" value="{$item.attendance_log_id}" onclick="doThisCheck(this)" value="user" id="{seqId()}" {if !empty($checkIds[$item.attendance_log_id])}checked{/if}><label for="{seqId(1)}" class="checkbox"></label>{/if}</td>
						<td>{formatTime($item.attendance_time)}</td>
						<td>{$devices[$item.device_id].name}</td>
						<td>{$item.log_type}</td>
						<td>{$item.person_code}</td>
						<td>{$item.person_name}</td>
						<td>{if $item.pass_flag == 1}出勤{elseif $item.pass_flag == 2}退勤{/if}</td>
						<td class="attendance_log_detail">{SimpleEnums::get("attendance_log_type", $item.log_type)}{if $item.message}{$item.message}{/if}</td>
					</tr>
				{/foreach}

			</table>

			<div class="btn_wrap center">
				<a href="javascript:void(0)" onclick="doAlignment('./attendanceAlignment', 'post')" class="attendance_btn {if empty($checkIds)}btn_disabled{/if} btn_blue"></i>連携処理を実行する</a>
			</div>

		</div>

		{include file="../_inc/pager_attendanceLog.tpl"}


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