{$title="ログ一覧"}{$icon="fa-list"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}
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
<style type="text/css">
	{* mod-start founder feihan *}
	{if $form["log_searchType"] == 1}
		.condition {
			display: none;
		}
	{/if}
	{* mod-end founder feihan *}
</style>
<script>

// --------------- システム設定の反映
$(function() {

	$('#personType').fSelect();
	$('#enterExitType').fSelect();

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

	// 温度とSCOREは小数点を自動補完
	$("input[name='temperature_from'],input[name='temperature_to'],input[name='score_from'],input[name='score_to']").blur(function () {
		if($(this).val()) {
			var num = Number($(this).val());
			if (num <= 0) {
				$(this).val("0.0");
			} else {
				if (num >= 1000) {
					num = num / 10;
				}
				if (num > 100) {
					num = parseInt(num) / 10;
				}
				$(this).val(num.toFixed(1));
			}
		}
	});
	{* add-end founder feihan *}

	$('input[name="presentFlag"]').on('change', function() {
		const $target = $(this).parents('.setting_cnt').nextAll();
		$target.find('input, label').toggleClass('log_input_disabled', this.checked);
		$target.find('.fs-label-wrap').toggleClass('fs-label-wrap_disabled', this.checked);
	}).triggerHandler('change');

	$("input[name='log_searchType']").click(function() {
		{*  mod-start founder feihan *}
		if ($("input[name='log_searchType']:checked").val() == "1") {
			$(".condition").fadeOut(400);
		} else {
			$(".condition").fadeIn(400).css('display','flex');
		}
		{*  mod-end founder feihan *}
	});
});

<!-- add-start founder feihan -->
// ダウンロード確認画面を表示する。
function showDialog(csvImgType) {
	if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
		$(".loader").hide();
		$(".export_progress").hide();
		$(".btn_gray").removeClass("btn_disabled");
		$(".btn_red").removeClass("btn_disabled");
		showModal("ログ一覧のcsvダウンロード", $("#export_download_modal_template").html(), function() {
		});
		$("#csvImgType").val(csvImgType);
	}
}

// ダウンロード。
function doExportDownload() {
	$(".loader").show();
	$(".btn_gray").addClass("btn_disabled");
	$(".btn_red").addClass("btn_disabled");
	var checkProgress = function () {
		var check = function() {
			doAjax("./exportDownloadCheckProgress", null, function(result) {
				if (result && result.rowCount) {
					$(".export_progress").show();
					$(".export_progress progress").attr("max",   result.rowCount);
					$(".export_progress progress").attr("value", result.processed);
					setTimeout(check, 1500);
				} else {
					if(result && result.fileReadContinue){
						setTimeout(check, 1500);
					}else{
						$(".export_progress progress").attr("max",   "100");
						$(".export_progress progress").attr("value", "100");
						$(".export_progress").fadeOut(200, function() {
							removeModal();
						});
						return;
					}
				}
			});
		};
		setTimeout(check, 500);
	}
	$(".export_progress").fadeIn(400);
	$(".export_progress progress").attr("max", "100").attr("value", "0");
	location.href = "./exportDownload?csvImgType="+ $("#csvImgType").val() +"&export_searchFormKey=" + $("input[name='export_searchFormKey']").val();
	checkProgress();
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
	if ($('select[name="personType"]').length) {
		$('select[name="personType"] option').removeAttr('selected');
		$('select[name="personType"]').val('');
		$('select[name="personType"]').data('fSelect').destroy();
		$('select[name="personType"]').data('fSelect').create();
		$('select[name="enterExitType"] option').removeAttr('selected');
		$('select[name="enterExitType"]').val('');
		$('select[name="enterExitType"]').data('fSelect').destroy();
		$('select[name="enterExitType"]').data('fSelect').create();
	}

	// その他入力項目のリセット
	$('.setting_area input[type="text"]').val('');
	$('.setting_area input[name="date_from"],input[name="date_to"]').each(function() {
		$(this).val($(this).attr('placeholder'));
	});
	$('.setting_area input[type="radio"][value="all"]').click();
	$('.setting_area input[type="checkbox"]').prop('checked', false);
	{if Session::getLoginUser("enter_exit_mode_flag") == 1}
	$('.setting_area input[name="presentFlag"]').triggerHandler('change');
	{/if}
}

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
	var $div = $('<div class="person_picture_box certain_height" style="background-color: white;"></div>');
	var imgHtml = '<div class="img" style="background-image: url(' + $(el).attr('person-picture-url') + ')"></div>'
	var detailHtml = '<span>詳細情報：' + $(el).attr("recog-log-detail") + '</span>';
	$div.append(imgHtml).append(detailHtml).hide();
	$(el).append($div);
	$(".person_picture_box").fadeIn(200);

	$(el).addClass("picture_show");
}

// 検索実行
function logSearch() {
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

<form action="./" name="searchForm">
	<input type="hidden" name="_form_session_key" value="{$form._form_session_key|default:""}">
	<input type="hidden" name="view" value="{$form.view}">
	<input type="hidden" name="limit" value="{$form.limit}">
	<input type="hidden" name="pageNo" value="1">
	<input type="hidden" name="export_searchFormKey" value="{$form.export_searchFormKey}" />
    <input type="hidden" name="searchInit" value="{$form.searchInit}" />
	{* add-start version3.0  founder feihan *}
	<input type="hidden" name="csvImgType" id="csvImgType" value="" />
	{* add-end version3.0  founder feihan *}

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
		<div class="setting_cnt">
			<p class="tit">カメラ選択</p>
			{* mod-start founder feihan *}
			<select id="m2" class="devices hidden" name="device_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
				{foreach $devices as $d=>$device}
					{if exists($devicesDisplay, $d)}
						<option value="{$d}" {if exists($form["device_ids"], $d)}selected{/if}>{$device.name}</option>
					{/if}
				{/foreach}
			</select>
			{* mod-end founder feihan *}
		</div>
		<div class="setting_cnt">
			<p class="tit">期間選択</p>
			<div class="period">
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr_time" autocomplete="off" data-allow-input="true" data-default-date="{$form.date_from}" placeholder="{date('Y/m/d H:i', strtotime('now -1 month'))}" name="date_from" value="{$form.date_from}">
				</div>
				<span>〜</span>
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr_time" autocomplete="off" data-allow-input="true" data-default-date="{$form.date_to}" placeholder="{date('Y/m/d H:i')}" name="date_to" value="{$form.date_to}">
				</div>
			</div>
		</div>
		<div class="setting_cnt">
			<div>
				<input type="radio" name="log_searchType" {if $form["log_searchType"] == 1}checked{/if} value="1" id="{seqId()}"><label for="{seqId(1)}" class="radio">詳細条件なし</label>
				<input type="radio" name="log_searchType" {if $form["log_searchType"] == 2}checked{/if} value="2" id="{seqId()}"><label for="{seqId(1)}" class="radio">詳細を絞り込む</label>
			</div>
		</div>
		{if Session::getLoginUser("enter_exit_mode_flag") == 1}
		<div class="setting_cnt condition">
			<p class="tit">区分</p>
			<div>
				<select id="personType" name="personType" no-search="no-search">
					<option value=""{if empty($form.personType)} selected{/if}>&nbsp;</option>
					{foreach $personTypes as $k=>$v}
						<option value="{$k}"{if $form.personType === $k} selected{/if}>{$v}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}</p>
			<div class="setting_cnt_description">
				<input type="text" name="personDescription1" value="{$form.personDescription1}">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}</p>
			<div class="setting_cnt_description">
				<input type="text" name="personDescription2" value="{$form.personDescription2}">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">検索時在室者</p>
			<div>
				<input{if $form.presentFlag == "1"} checked{/if} name="presentFlag" id="{seqId()}" type="checkbox" value="1"><label for="{seqId(1)}" class="checkbox"></label>
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">状態</p>
			<div>
				<select id="enterExitType" name="enterExitType" no-search="no-search">
					<option value=""{if empty($form.enterExitType)} selected{/if}>&nbsp;</option>
					{foreach $enterExitTypes as $k=>$v}
						<option value="{$k}"{if $form.enterExitType === $k} selected{/if}>{$v}</option>
					{/foreach}
				</select>
			</div>
		</div>
		{/if}
		{* mod-start founder feihan *}
		<div class="setting_cnt condition">
			<p class="tit">ID</p>
			<div>
				<input type="text" name="personCode" value="{$form.personCode}">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">氏名</p>
			<div>
				<input type="text" name="personName" value="{$form.personName}"">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">ICカード番号</p>
			<div>
				<input type="text" name="cardID" value="{$form.cardID}">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">マスク</p>
			<div>
				<input {if $form.mask_type == "all"}checked{/if} id="{seqId()}" name="mask_type" type="radio" value="all"><label for="{seqId(1)}" class="radio">全て</label>
				<input {if $form.mask_type == "yes"}checked{/if} id="{seqId()}" name="mask_type" type="radio" value="yes"><label for="{seqId(1)}" class="radio">有り</label>
				<input {if $form.mask_type == "no" }checked{/if} id="{seqId()}" name="mask_type" type="radio" value="no" ><label for="{seqId(1)}" class="radio">無し</label>
			</div>
		</div>

		<div class="setting_cnt condition">
			<p class="tit">PASS</p>
			<div>
				<input {if $form.pass_type == "all"}checked{/if} id="{seqId()}" name="pass_type" type="radio" value="all"><label for="{seqId(1)}" class="radio">全て</label>
				<input {if $form.pass_type == "yes"}checked{/if} id="{seqId()}" name="pass_type" type="radio" value="yes"><label for="{seqId(1)}" class="radio">PASS</label>
				<input {if $form.pass_type == "no" }checked{/if} id="{seqId()}" name="pass_type" type="radio" value="no" ><label for="{seqId(1)}" class="radio">NO PASS</label>
			</div>
		</div>

		<div class="setting_cnt condition">
			<p class="tit">登録者</p>
			<div>
				<input {if $form.guest_type == "all"}checked{/if} id="{seqId()}" name="guest_type" type="radio" value="all"><label for="{seqId(1)}" class="radio">全て</label>
				<input {if $form.guest_type == "yes"}checked{/if} id="{seqId()}" name="guest_type" type="radio" value="yes"><label for="{seqId(1)}" class="radio">ゲストのみ</label>
				<input {if $form.guest_type == "no" }checked{/if} id="{seqId()}" name="guest_type" type="radio" value="no" ><label for="{seqId(1)}" class="radio">登録者のみ</label>
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">温度</p>
			<div class="period">
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="temperature_from" value="{$form.temperature_from}" maxlength="4">
				<span>〜</span>
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="temperature_to" value="{$form.temperature_to}" maxlength="4">
			</div>
			<input type="checkbox" name="noTempOnly" value="1" {if $form["noTempOnly"] == "1"}checked{/if} id="{seqId()}"><label style="margin-left: 30px;" for="{seqId(1)}" class="checkbox">温度計測なし/失敗のみ表示</label>
		</div>
		{if $recogPassFlags}
		<div class="setting_cnt condition">
			<p class="tit">勤怠区分</p>
			<div>
				{foreach $recogPassFlags as $passFlag}
					<input type="checkbox" name="pass_flags[]" value="{$passFlag.pass_flag}" {if exists($form.pass_flags, $passFlag.pass_flag)}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="checkbox">{$passFlag.flag_name}</label>
				{/foreach}
			</div>
		</div>
		{/if}
		<div class="setting_cnt condition">
			<p class="tit">SCORE</p>
			<div class="period">
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="score_from" value="{$form.score_from}" maxlength="4">
				<span>〜</span>
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="score_to" value="{$form.score_to}" maxlength="4">
			</div>
		</div>
		{* mod-end founder feihan *}
		<div class="log_btn_wrap">
			<a href="javascript:void(0)" onclick="logSearch()" class="btn_log_search enter-submit">ログを表示</a>
			{* mod-start version3.0  founder feihan *}
			<a href="javascript:void(0)" onclick="showDialog(1)" class="export_btn {if $pageInfo->getRowCount() <= 0}btn_disabled{/if} btn_blue" style="font-size: calc(1vw - 0.3vw);"><i class="fas fa-arrow-alt-from-top" ></i>CSV形式（画像あり）でダウンロード</a>
			<a href="javascript:void(0)" onclick="showDialog(0)" class="export_btn {if $pageInfo->getRowCount() <= 0}btn_disabled{/if} btn_blue" style="font-size: calc(1vw - 0.3vw);"><i class="fas fa-arrow-alt-from-top" ></i>CSV形式（画像なし）でダウンロード</a>
			{* mod-end version3.0  founder feihan *}
			<a href="javascript:void(0)" onclick="reset()" value="Reset" id="resetBtn" class="btn_blue">検索条件をリセット</a>
		</div>
		<div class="log_csv_span">
			<span>※「CSV形式でダウンロード」を実行する場合は、 「ログを表示」で検索結果を絞ってから実行してください。</span>
		</div>
	</div>

	{if $pageInfo}
	<div class="log_wrap">

		{* mod-start founder zouzhiyuan *}
		{* {include file="../_inc/pager_log.tpl"} *}
		{include file="../_inc/pager_counter_log.tpl"}
		{* mod-end founder zouzhiyuan *}

		{if $form.view == 1}
			<ul class="log_list">
				{foreach $list as $item}
					{include file="../_inc/log_item_view_1.tpl"}
				{/foreach}
			</ul>
		{/if}

		{if $form.view == 2}
			<div class="log_table_wrap">
				<table class="log_table">
					<tr>
						{* mod-start founder feihan *}
						<th {if Session::getLoginUser("enter_exit_mode_flag") == 1}style="width: 7%"{else}style="width: 10%"{/if}>日時</th>
						<th style="width: 6%">カメラグループ</th>
						<th style="width: 9%">カメラ</th>
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
						{if $recogPassFlags}
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

	{* mod-start founder zouzhiyuan *}
	{* {include file="../_inc/pager_log.tpl"} *}
	{include file="../_inc/pager_counter_log.tpl"}
	{* mod-start founder zouzhiyuan *}

	{/if}
</form>

<!-- ダウンロードモーダル -->
<div id="export_download_modal_template" style="display:none">
	<div>
        {if $pageInfo}
		{formatNumber($pageInfo->getRowCount())}件のログデータをcsv形式でダウンロードします。<br >
		※対象件数が多い場合は、時間がかかることがあります。<br >
        {/if}
	</div>
	<div style="height: 110px">
		<div id="loading" class="loader" style="display:none">>Loading...</div>
		<div class="export_progress" style="display:none"><progress style="width: 200px; "></progress></div>
	</div>
	<div class="dialog_btn_wrap center">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray" >閉じる</a>
		<a href="javascript:void(0);" onclick="doExportDownload()" class="btn btn_red">ダウンロード</a>
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

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}