
<style type="text/css">
	{* mod-start founder feihan *}
	{if $form["`$prefix`searchType"]|default:null == 1}
		.{$prefix}condition {
			display: none;
		}
		.{$prefix}resetBtn {
			display: none;
		}
	{/if}
	{* mod-end founder feihan *}
</style>

<script>

$(function() {

	$("input[name='{$prefix}searchType']").click(function() {
		{*  mod-start founder feihan *}
		if ($("input[name='{$prefix}searchType']:checked").val() == "1") {
			$(".{$prefix}condition").fadeOut(400);
			$("#{$prefix}ResetBtn").hide();
		} else {
			$(".{$prefix}condition").fadeIn(400);
			$("#{$prefix}ResetBtn").show();
			document.getElementById("{$prefix}ResetBtn").style.display = 'flex';
		}
		{*  mod-end founder feihan *}
	});

	{*  add-start founder feihan *}
	$("#{$prefix}m1").fSelect();
	$("#{$prefix}m2").fSelect();
	$("#{$prefix}m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#{$prefix}m2")
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

</script>

<input type="hidden" id="{$prefix}search_init" name="{$prefix}search_init" value="{$form["`$prefix`search_init"]|default:""}" />
<table class="form_cnt">
	<tr {if !empty(Session::getLoginUser("group_id"))}class="hidden"{/if}>
		<th class="tit">ユーザー情報検索</th>
		<td colspan="2">
			<input type="radio" name="{$prefix}searchType" {if $form["`$prefix`searchType"]|default:null == 1}checked{/if} value="1" id="{seqId()}"><label for="{seqId(1)}" class="radio">全て表示</label>
			<input type="radio" name="{$prefix}searchType" {if $form["`$prefix`searchType"]|default:null == 2}checked{/if} value="2" id="{seqId()}"><label for="{seqId(1)}" class="radio">特定のユーザーを絞り込む</label>
		</td>
	</tr>
	<tr class="{$prefix}condition">
		<th {if Session::getLoginUser("enter_exit_mode_flag") == 1}rowspan="7"{else}rowspan="4"{/if} class="tit">登録情報から検索</th>
		<th class="tit_sub">ID</th>
		<td><input type="text" name="{$prefix}personCode" value="{$form["`$prefix`personCode"]|default:""}" placeholder="任意のID"></td>
	</tr>
	<tr class="{$prefix}condition">
		<th class="tit_sub">氏名</th>
		<td><input type="text" name="{$prefix}personName" value="{$form["`$prefix`personName"]|default:""}" placeholder="名前を入力します"></td>
	</tr>
{*	{if prefix == 'list_'}*}
	<tr class="{$prefix}condition">
		<th class="tit_sub">ICカード番号</th>
		<td><input type="text" name="{$prefix}cardID" value="{$form["`$prefix`cardID"]|default:""}" placeholder="カード番号を入力します"></td>
	</tr>
{*	{/if}*}
	<tr class="{$prefix}condition">
		<th>生年月日</th>
		<td>
			<p class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input type="text" class="flatpickr" data-position="above" data-allow-input="true" placeholder="{date("Y")}/01/01" name="{$prefix}birthday" value="{$form["`$prefix`birthday"]|default:""}" >
			</p>
		</td>
	</tr>
	{* add-start version3.0  founder feihan *}
	{if Session::getLoginUser("enter_exit_mode_flag") == 1}
		<tr class="{$prefix}condition">
			<th class="fs-select-th-center">区分</th>
			<td>
				<p class="select">
					<select name="{$prefix}person_type_code" id="{$prefix}person_type_code">
						<option value=""></option>
						{foreach $personTypeList as $person_type_code=>$personType}
							<option {if $form["`$prefix`person_type_code"]|default:null == $person_type_code}selected{/if} value="{$person_type_code}" >{$personType.person_type_name}</option>
						{/foreach}
					</select>
				</p>
			</td>
		</tr>
		<tr class="{$prefix}condition">
					<th class="tit_sub">{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}</th>
			<td><input type="text" name="{$prefix}person_description1" value="{$form["`$prefix`person_description1"]|default:""}" placeholder="{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}を入力します"></td>
		</tr>
		<tr class="{$prefix}condition">
					<th class="tit_sub">{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}</th>
			<td><input type="text" name="{$prefix}person_description2" value="{$form["`$prefix`person_description2"]|default:""}" placeholder="{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}を入力します"></td>
		</tr>
	{/if}
	{* add-end version3.0  founder feihan *}
	{* add-start founder feihan*}
	{assign var=devicesDisplay value=[]}
	{if empty(Session::getLoginUser("group_id"))}
	<tr class="{$prefix}condition">
		<th class="tit fs-select-th-center">カメラグループから検索</th>
		<td colspan="2" style="font-size: 0;">
			<select id="{$prefix}m1" class="groups hidden" name="{$prefix}group_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
				{foreach $groupsDisplay as $g=>$group}
					{$selected = ""}
					{if exists($form["`$prefix`group_ids"], $g)}
						{$selected = "selected"}
						{$devicesDisplay=array_merge($devicesDisplay,$group.deviceIds)}
					{/if}
					<option value="{$g}" {$selected}>{$group.group_name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	{else}
		{$devicesDisplay=array_keys($devices)}
	{/if}
	{* add-end founder feihan*}
	{* mod-start founder feihan*}
	<tr class="{$prefix}condition">
		<th class="tit fs-select-th-center">カメラから検索</th>
		<td colspan="2">
			<div class="fs-select-center">
				<select id="{$prefix}m2" class="devices hidden" name="{$prefix}device_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
					{foreach $devices as $d=>$device}
						{if exists($devicesDisplay, $d)}
							<option value="{$d}" {if exists($form["`$prefix`device_ids"], $d)}selected{/if}>{$device.name}</option>
						{/if}
					{/foreach}
				</select>
				{if empty(Session::getLoginUser("group_id"))}
				<input type="checkbox" name="{$prefix}noCam" value="1" {if $form["`$prefix`noCam"]|default:"" == "1"}checked{/if} id="{seqId()}" >
				<label style="margin-left: 10px;" for="{seqId(1)}" class="checkbox">カメラ未登録ユーザー</label>
				{/if}
			</div>
		</td>
	</tr>
	{* mod-end founder feihan*}
	<tr class="{$prefix}condition period">
		<th class="tit">登録期間から検索</th>
		<td colspan="2">
			<div class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input name="{$prefix}createDateFrom" value="{$form["`$prefix`createDateFrom"]|default:""}" type="text" autocomplete="off" class="flatpickr" data-allow-input="true" placeholder="{date('Y/m/d')}">
			</div>
			<span>〜</span>
			<div class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input name="{$prefix}createDateTo" value="{$form["`$prefix`createDateTo"]|default:""}" type="text" autocomplete="off" class="flatpickr" data-allow-input="true" placeholder="{date('Y/m/d')}">
			</div>
		</td>
	</tr>
</table>
