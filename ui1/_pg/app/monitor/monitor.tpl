{$title="リアルタイムモニタ"}{$icon="fa-video"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}
{* add-start founder feihan *}
<script src="/ui1/static/js/fselect/fSelect.js"></script>
<script>
	{* fSelect用定数 *}
	const groups = { }
	const groupsInit = []
	{if empty(Session::getLoginUser("group_id"))}
	{foreach $groupsDisplay as $gid=>$group}
	groups[{$gid}] = [ {foreach $group.deviceIds as $deviceId} '{$deviceId}', {/foreach} ]
	groupsInit.push( { id: '{$gid}', name: '{$group.group_name|escape:"javascript"}' } )
	{/foreach}
	{/if}
	const devices = []
	{foreach $devices as $d=>$device}
	devices.push( { id: '{$d}', name: '{$device.name|escape:"javascript"}' } )
	{/foreach}

</script>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">
{* add-end founder feihan *}

<script>

// --------------- システム設定の反映
$(function() {
	{* add-start founder feihan *}
	$("#m1").fSelect();
	$("#m2").fSelect();

	$("#m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#m2")
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
	});
	{* add-end founder feihan *}
});


// デバイスIDに該当するカメラの最新情報を取得し、表示。
function dispDeviceLog(deviecId) {

	doAjax("./getLatestLog?view={$form.view}&device_id=" + deviecId, {}, function(data) {


		{if $form.view == "1"}

			$(".device_" + deviecId).replaceWith(data.view_1);
			$(".device_" + deviecId).addClass("monitor_push")
			setTimeout(function() {
				$(".device_" + deviecId).removeClass("monitor_push")
			}, 100);

		{else}

			$(".log_table .device_" + deviecId).replaceWith(data.view_2_tr);
			$(".log_table_imglist .device_" + deviecId).replaceWith(data.view_2_li);

			$(".log_table .device_" + deviecId).addClass("monitor_push")
			$(".log_table_imglist .device_" + deviecId).addClass("monitor_push")
			setTimeout(function() {
				$(".log_table .device_" + deviecId).removeClass("monitor_push")
				$(".log_table_imglist .device_" + deviecId).removeClass("monitor_push")
			}, 100);


			// ログテーブルクリック時。
			$(".recog_row").unbind().click(function() {
				{* mod-start founder feihan *}
				// $(".recog_row.current").removeClass("current");
				//
				// var rid = $(this).attr("rid");
				// $(".r_" + rid).addClass("current");
				{* mod-end founder feihan *}
			});


		{/if}

	});

}

$(function() {

	// 最新を取得。
	{foreach $list as $item}
		dispDeviceLog({$item.device_id});
	{/foreach}

	// 通知接続。
	{if empty($wsAddr)} return; {/if}

	var ws = new WebSocket("{$wsAddr}");
	var errored = false;
	var init = true;

	// 接続が開いたときのイベント
	ws.addEventListener("open", function(event) {
	    console.log('Open server ', event);

		init = false;

		$(".monitor_loader_msg").text("モニターが開始されました。");
		$(".monitor_loader .loader").hide();
		setTimeout(function() {
			$(".monitor_loader").fadeOut(5000);
		});

	});

	// メッセージの待ち受け
	ws.addEventListener('message', function(event) {
	    console.log('Message from server ', event);

	    var deviceId = event.data;
	    dispDeviceLog(deviceId);

	});


	// エラー時
	ws.addEventListener("error", function(event) {
		console.log('WebSocket error: ', event);

		$(".monitor_loader").hide();
		if (init) {
			showModal("エラー", $("#monitorErrorModal").html());
			errored = true;
		}

	});

	// 切断時。
	ws.addEventListener("close", function(event) {
		console.log('WebSocket close ', event);

		$(".monitor_loader .loader").hide();
		$(".monitor_loader").hide();;

		if (errored) return;

		showModal("モニター切断", $("#monitorCloseModal").html());

	});

});

{* add-start founder zouzhiyuan *}
// ドアの一時開錠。
function doDeviceOpenOnce(deviceId, deviceName) {

	showModal("ドアの一時開錠", $("#diviceOpenOnceModalTemplate").html());
	$("#modal_message .diviceConnectModal_confirm").show();
	$("#modal_message .diviceConnectModal_loading").hide();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_complete").hide();
	$("#modal_message .diviceConnectModal_loading").data('deviceId',deviceId);

	$("#modal_message .diviceConnectModal_device_name").text(deviceName);


}
function openOnce() {

	let deviceId = $("#modal_message .diviceConnectModal_loading").data('deviceId');

	$("#modal_message .diviceConnectModal_confirm").hide();
	$("#modal_message .diviceConnectModal_loading").show();

	doAjax("./doDeviceOpenOnce", { device_id: deviceId }, function(data) {

		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			data.error!==true && $("#modal_message .diviceConnectModal_error_msg").append(escapeHtml(data.error));
		} else {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_complete").show();
		}

	});

}
{* add-end founder zouzhiyuan *}

{* add-start founder feihan *}
// 人物画像の参照リンク
function showPic(el){
	if ($(el).hasClass("picture_show")) {
		$(el).removeClass("picture_show");
		$(".person_picture_box").fadeOut(200, function() {
			$(this).remove();
		});
		return;
	}

	$(".person_picture_box").remove();

	var $div = $("<div class='person_picture_box' style='background-color: white;'></div>");
	$div.append("<div><img src='" + $(el).attr("person-picture-url") + "'></div>");
	var detail = $(el).attr("recog-log-detail");
	$div.append('<span>詳細情報：'+detail+'</span>');
	$div.hide();
	$(el).append($div);
	$(".person_picture_box").fadeIn(200);

	$(el).addClass("picture_show");
}

// 検索条件をリセット
function reset(){
	// グループ選択を初期リセット
	if(groupsInit.length>0) {
		const $groupSelect = $("#m1");
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
	const $deviceSelect = $("#m2");
	$deviceSelect.empty();
	devices.forEach(device=>{
		{literal}
		$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
		{/literal}
	});
	$deviceSelect.data('fSelect').destroy();
	$deviceSelect.data('fSelect').create();
}

// 検索実行
function resultSearch() {
	if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
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
}
{* add-end founder feihan *}
</script>

{* add-start founder zouzhiyuan *}
<!-- デバイス通信ダイアログ(ドアの一時開錠) -->
<div id="diviceOpenOnceModalTemplate" style="display:none">

	<div class="diviceConnectModal_confirm" style="display:none">
		<div style="height: 150px;">
			カメラ名：「<span class="diviceConnectModal_device_name"></span>」のドアを一時的に開錠します。よろしいですか？
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray" >閉じる</a>
			<a href="javascript:void(0);" onclick="openOnce()"  class="btn btn_red">開錠する</a>
		</div>
	</div>

	<div class="diviceConnectModal_loading">
		ただいま[<span class="diviceConnectModal_device_name"></span>]への通信を行っています。<br />
		通信状況によっては時間がかかる場合があります。<br />
		このままでお待ちください。
		<div id="loading" class="loader">Loading...</div>
	</div>

	<div class="diviceConnectModal_error" style="display:none">
		<div class="diviceConnectModal_error_msg">
			カメラ名：[<span class="diviceConnectModal_device_name"></span>]への通信中にエラーが発生しました。<br />
			<br />
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>

	</div>

	<div class="diviceConnectModal_complete">
		<div class="openOnce_complete">
			カメラ名：[<span class="diviceConnectModal_device_name"></span>]のドアを開錠しました。
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>
	</div>

</div>
{* add-end founder zouzhiyuan *}


<!-- 切断ダイアログ -->
<div id="monitorCloseModal" style="display:none">

	<div class="monitorModal_error_msg">
		モニターが切断されました。<br />
		長時間のモニターを行っている場合や、無線ＬＡＮ環境などの通信状況が不安定な場合、モニターの切断が発生する場合があります。<br />
		再読み込みを行う事で、再度モニターが出来るようになります。<br />
		<br />
	</div>
	<div class="btns">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		<a href="javascript:void(0);" onclick="location.reload()" class="btn btn_red">再読み込みを行う</a>
	</div>

</div>

<!-- 通信エラーダイアログ -->
<div id="monitorErrorModal" style="display:none">

	<div class="monitorModal_error_msg">
		モニターを開始する事が出来ませんでした。<br />
		<br />
	</div>
	<div class="btn_1">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
	</div>

</div>

{* add-start founder feihan *}
<!-- カメラルダウン変更確認モーダル -->
<div id="groupsChangeModalTemplate" style="display:none">
	<div style="">
		<div style="height: 150px;">
			カメラ選択が初期化されますがよろしいですか？
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" id="groupsChangeModalBtnCancel" class="btn btn_gray" >いいえ</a>
			<a href="javascript:void(0);" id="groupsChangeModalBtnOk"  class="btn btn_red">はい</a>
		</div>
	</div>
</div>
{* add-end founder feihan *}

<form action="./" name="searchForm">
	<input type="hidden" name="_form_session_key" value="{$form._form_session_key|default:""}">
	<input type="hidden" name="view" value="{$form.view}">
	<input type="hidden" name="limit" value="{$form.limit}">
	<input type="hidden" name="pageNo" value="1">
	<input type="hidden" name="searchInit" value="{$form.searchInit}" />

	<div class="monitor_loader">
		<div class="monitor_loader_inner">
			<span class="monitor_loader_msg">準備中です...</span>
			<div class="loader"></div>
		</div>
	</div>

	<div class="setting_area">
		<input type="hidden" name="view" value="{$form.view}">
		{* add-start founder yaozhengbang *}
		{assign var=devicesDisplay value=[]}
		{if empty(Session::getLoginUser("group_id"))}
			{* add-end founder yaozhengbang *}
			<div class="setting_cnt">
				<p class="tit">グループ選択</p>
				{* mod-start founder feihan *}
				<select id="m1" class="groups hidden" name="group_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
					{foreach $groupsDisplay as $g=>$group}
						{$selected = ""}
						{if exists($form["group_ids"], $g)}
							{$selected = "selected"}
							{$devicesDisplay=array_merge($devicesDisplay,$group.deviceIds)}
						{/if}
						<option value="{$g}" {$selected}>{$group.group_name}</option>
					{/foreach}
				</select>
				{* mod-end founder feihan *}
			</div>
			{* add-start founder yaozhengbang *}
		{else}
			{$devicesDisplay=array_keys($devices)}
		{/if}
		{* add-end founder yaozhengbang *}
		{* mod-start founder feihan *}
		<div class="setting_cnt">
			<p class="tit">カメラ選択</p>
			<select id="m2" class="devices hidden" name="device_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
				{foreach $devices as $d=>$device}
					{if exists($devicesDisplay, $d)}
						<option value="{$d}" {if exists($form["device_ids"], $d)}selected{/if}>{$device.name}</option>
					{/if}
				{/foreach}
			</select>
		</div>
		<div class="log_btn_wrap">
			<a href="javascript:void(0)" onclick="resultSearch()" class="btn_log_search">表示</a>
			<a href="javascript:void(0)" onclick="reset()" value="Reset" id="resetBtn" class="btn_blue">検索条件をリセット</a>
		</div>
		{* mod-end founder feihan *}
	</div>


	<div class="log_wrap monitor_wrap">

		{include file="../_inc/pager_log.tpl"}

		{if $form.view == 1}
			<ul class="log_list">
				{foreach $list as $item}
					{* mod-start founder zouzhiyuan *}
					{include file="../_inc/log_item_view_1_monitor.tpl"}
					{* mod-end founder zouzhiyuan *}
				{/foreach}
			</ul>
		{/if}

		{if $form.view == 2}
			<div class="log_table_wrap">
				<table class="log_table">
					<tr>
						{* mod-start founder feihan *}
						<th {if Session::getLoginUser("enter_exit_mode_flag") == 1}style="width: 7%"{else}style="width: 10%"{/if}>日時</th>
						<th {if Session::getLoginUser("enter_exit_mode_flag") == 1}style="width: 6%"{else}style="width: 8%"{/if}>カメラグループ</th>
						<th {if Session::getLoginUser("enter_exit_mode_flag") == 1}style="width: 8%"{else}style="width: 11%"{/if}>カメラ</th>
						<th style="width: 7%">認証方式</th>
						{if Session::getLoginUser("enter_exit_mode_flag") == 1}
							<th style="width: 6%">状態</th>
						{/if}
						<th style="width: 6%">ID／ゲスト</th>
						<th style="width: 8%">名前</th>
						<th style="width: 6%">ICカード番号</th>
						{if Session::getLoginUser("enter_exit_mode_flag") == 1}
							<th style="width: 6%">区分</th>
							<th style="width: 6%">{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}</th>
							<th style="width: 6%">{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}</th>
						{/if}
						<th style="width: 6%">PASS</th>
						<th style="width: 6%">温度</th>
						<th style="width: 6%">マスク</th>
						<th style="width: 6%">スコア</th>
						{if $recogPassFlags|default:false}
							<th style="width: 6%">勤怠区分</th>
						{/if}
						<th style="width: 7%">{if Session::getLoginUser("enter_exit_mode_flag") == 1}画像{else}画像/詳細情報{/if}</th>
						{* mod-end founder feihan *}
					</tr>
					{foreach $list as $item}
						{include file="../_inc/log_item_view_2_tr.tpl"}
					{/foreach}
				</table>
				{* mod-start founder feihan *}
{*				<ul class="log_table_imglist">*}
{*					{foreach $list as $item}*}
{*						{include file="../_inc/log_item_view_2_li.tpl"}*}
{*					{/foreach}*}
{*				</ul>*}
				{* mod-end founder feihan *}
			</div>

		{/if}

	</div>

	{include file="../_inc/pager_log.tpl"}

</form>


{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}