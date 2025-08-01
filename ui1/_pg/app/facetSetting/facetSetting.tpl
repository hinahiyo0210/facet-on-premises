{$title="facet設定"}{$icon="fas fa-wrench"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}

<script>

function doPost(action, scrollSave) { 
	$(".no-req").val("");
	doFormSend(action, scrollSave, "post");
}

function doFormSend(action, scrollSave, method) {
	
	if (scrollSave) {
		$("input[name='_p']").val(parseInt($(window).scrollTop()).toString(36)).prop("disabled", false);
	}

	document.settingForm.method = method;
	document.settingForm.action = action;
	document.settingForm.submit();
}
$(function() {

	// CRUD選択時の挙動（区分設定）
	$("input[name='{$prefix|default:""}PersonTypeR']").click(function() {
		$('.hidden_person_type').hide()
		switch ($(this).attr('value')) {
		case '1':
			$('#registPersonTypeRow').show()
			break;
		case '2':
			$('#editPersonTypeRow').show()
			break;
		case '3':
			$('#deletePersonTypeRow').show()
			break;
		}
	});

	// グループ変更時の挙動（入退数リセット時間変更）
	$("#group_select").change(function() {
		doPost('./changeGroup', false);
	});

})
</script>

<form action="./" name="settingForm">
	<div class="setting_area">

		{if Session::getLoginUser("enter_exit_mode_flag") == 1}
		<table class="form_cnt setting_person_type">
			<tr class="editRadio">
				<th  class="tit">入退数リセット時間</th>
				<td colspan="5" class="switching_time_td">
					{if !empty($groups)}
					<p class="input_title">グループ</p>
					<p class="select">
						<select id="group_select" name="switch_device_group_id">
						{foreach $groups as $g => $group}
							{if $form['switch_device_group_id']|default:"" == $g}
							<option value="{$g}" selected>{$group.group_name}</option>
							{else}
							<option value="{$g}">{$group.group_name}</option>
							{/if}
						{/foreach}
						</select>
					</p>
					<input class="switching_time_input" type="number" name="switching_time" value="{$form.switchingTime|default:""}" min="0" max="23">
					<p class="input_title">時</p>
					<a href="javascript:void(0)" onclick="doPost('./saveTime', false)" class="btn_save_time">保存</a>
					{else}
					<p>設定する場合はグループを登録してください。</p>
					{/if}
				</td>
			</tr>
		</table>
		<table class="form_cnt setting_person_type">
			<tr class="editRadio">
				<th rowspan="4" class="tit">区分設定</th>
				<td colspan="3">
					<input type="radio" name="{$prefix|default:""}PersonTypeR" value="1" id="{seqId()}"><label for="{seqId(1)}" class="radio" id="regist">登録</label>
					<input type="radio" name="{$prefix|default:""}PersonTypeR" value="2" id="{seqId()}"><label for="{seqId(1)}" class="radio" id="edit">更新</label>
					<input type="radio" name="{$prefix|default:""}PersonTypeR" value="3" id="{seqId()}"><label for="{seqId(1)}" class="radio" id="delete">削除</label>
				</td>
			</tr>
			<tr id="registPersonTypeRow" class="hidden_person_type">
				<td class="edit_person_type_td">
					<input class="person_type_form_box" type="text" name="registPersonTypeText" value="" placeholder="区分名">
					<a href="javascript:void(0)" onclick="doPost('./registPersonType', false)" class="btn edit_person_type_btn">登録</a>
				</td>
			</tr>
			<tr id="editPersonTypeRow" class="hidden_person_type">
				<td class="edit_person_type_td">
					<p class="select person_type_form_box">
						<select name="editPersonType" id="person_type_code">
							<option value=""></option>
							{foreach $personTypeList as $person_type_code=>$personType}
								<option value="{$person_type_code}" >{$personType.person_type_name}</option>
							{/foreach}
						</select>
					</p>
					<input class="person_type_form_box pt_edit_box" type="text" name="editPersonTypeText" value="" placeholder="更新区分名">
					<a href="javascript:void(0)" onclick="doPost('./editPersonType', false)" class="btn edit_person_type_btn">更新</a>
				</td>
			</tr>
			<tr id="deletePersonTypeRow" class="hidden_person_type">
				<td class="edit_person_type_td">
					<p class="select person_type_form_box">
						<select name="deletePersonType" id="person_type_code">
							<option value=""></option>
							{foreach $personTypeList as $person_type_code=>$personType}
								<option value="{$person_type_code}" >{$personType.person_type_name}</option>
							{/foreach}
						</select>
					</p>
					<a href="javascript:void(0)" onclick="doPost('./deletePersonType', false)" class="btn edit_person_type_btn">削除</a>
				</td>
			</tr>
		</table>
		{/if}

		{if Session::getLoginUser("teamspirit_flag") == 1 || Session::getLoginUser("teamspirit_flag") == 2}
		<table class="form_cnt setting_teamspirit">
			<tr>
				<th rowspan="3" class="tit">TeamSpirit連携情報</th>
				<th class="tit_sub">連携条件</th>
				<td class="ts_set ts_padding">
					<input type="checkbox" name="tsSet" value="1" {if $teamspiritSetting.conditions_set}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="checkbox">通行許可でなくても顔認証の結果で連携を行う</label>
				</td>
			</tr>
			<tr>
				<th class="tit_sub">ユーザー名</th>
				<td class="ts_user ts_padding">
					<input type="text" name="tsUserName" value="{if !empty($inputTSinfo)}{$inputTSinfo.user_name}{else}{$teamspiritSetting.user_name}{/if}" placeholder="連携API実行アカウントのユーザー名">
					<a href="javascript:void(0)" onclick="doPost('./oauthCheck', false)" class="btn_ts_setting">OAuth確認</a>
					<p style="margin-left:1rem">{if !empty($form.oauthResult)}{if $form.oauthResult == "OK"}<i class="far fa-badge-check"></i>{else}<i class="far fa-engine-warning"></i>{/if}{/if}</p>
				</td>
			</tr>
			<tr>
				<th class="tit_sub">パスワード</th>
				<td class="ts_pass">
					<input type="password" name="tsUserPass" value="{if !empty($inputTSinfo)}{$inputTSinfo.password}{else}{$teamspiritSetting.password}{/if}" placeholder="連携API実行アカウントのパスワード">
					<a href="javascript:void(0)" onclick="doPost('./saveTsSetting', false)" class="btn_ts_setting">保存</a>
				</td>
			</tr>
		</table>
		{/if}
		
		<table class="form_cnt">
			<tr>
				<th rowspan="1" class="tit">facetバージョン</th>
				<td colspan="1">ver{$facetVersion}</td>
			</tr>
		</table>

	</div>
</form>

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}