<?php
/* Smarty version 4.5.3, created on 2024-09-18 09:26:04
  from '/var/www/html/ui1/_pg/app/facetSetting/facetSetting.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ea1e1ca8a630_88633236',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f1ad0710c4e242371195c2a419ae41718116105d' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/facetSetting/facetSetting.tpl',
      1 => 1723015685,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ea1e1ca8a630_88633236 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "facet設定");
$_smarty_tpl->_assignInScope('icon', "fas fa-wrench");
$_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/header.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

<?php echo '<script'; ?>
>

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
	$("input[name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['prefix']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
PersonTypeR']").click(function() {
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
<?php echo '</script'; ?>
>

<form action="./" name="settingForm">
	<div class="setting_area">

		<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
		<table class="form_cnt setting_person_type">
			<tr class="editRadio">
				<th  class="tit">入退数リセット時間</th>
				<td colspan="5" class="switching_time_td">
					<?php if (!empty($_smarty_tpl->tpl_vars['groups']->value)) {?>
					<p class="input_title">グループ</p>
					<p class="select">
						<select id="group_select" name="switch_device_group_id">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groups']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
							<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['switch_device_group_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['g']->value) {?>
							<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value ));?>
" selected><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group']->value['group_name'] ));?>
</option>
							<?php } else { ?>
							<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group']->value['group_name'] ));?>
</option>
							<?php }?>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
					<input class="switching_time_input" type="number" name="switching_time" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['switchingTime'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="23">
					<p class="input_title">時</p>
					<a href="javascript:void(0)" onclick="doPost('./saveTime', false)" class="btn_save_time">保存</a>
					<?php } else { ?>
					<p>設定する場合はグループを登録してください。</p>
					<?php }?>
				</td>
			</tr>
		</table>
		<table class="form_cnt setting_person_type">
			<tr class="editRadio">
				<th rowspan="4" class="tit">区分設定</th>
				<td colspan="3">
					<input type="radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['prefix']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
PersonTypeR" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio" id="regist">登録</label>
					<input type="radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['prefix']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
PersonTypeR" value="2" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio" id="edit">更新</label>
					<input type="radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['prefix']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
PersonTypeR" value="3" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio" id="delete">削除</label>
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
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['personTypeList']->value, 'personType', false, 'person_type_code');
$_smarty_tpl->tpl_vars['personType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['person_type_code']->value => $_smarty_tpl->tpl_vars['personType']->value) {
$_smarty_tpl->tpl_vars['personType']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['person_type_code']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['personType']->value['person_type_name'] ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
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
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['personTypeList']->value, 'personType', false, 'person_type_code');
$_smarty_tpl->tpl_vars['personType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['person_type_code']->value => $_smarty_tpl->tpl_vars['personType']->value) {
$_smarty_tpl->tpl_vars['personType']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['person_type_code']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['personType']->value['person_type_name'] ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
					<a href="javascript:void(0)" onclick="doPost('./deletePersonType', false)" class="btn edit_person_type_btn">削除</a>
				</td>
			</tr>
		</table>
		<?php }?>

		<?php if (Session::getLoginUser("teamspirit_flag") == 1 || Session::getLoginUser("teamspirit_flag") == 2) {?>
		<table class="form_cnt setting_teamspirit">
			<tr>
				<th rowspan="3" class="tit">TeamSpirit連携情報</th>
				<th class="tit_sub">連携条件</th>
				<td class="ts_set ts_padding">
					<input type="checkbox" name="tsSet" value="1" <?php if ($_smarty_tpl->tpl_vars['teamspiritSetting']->value['conditions_set']) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox">通行許可でなくても顔認証の結果で連携を行う</label>
				</td>
			</tr>
			<tr>
				<th class="tit_sub">ユーザー名</th>
				<td class="ts_user ts_padding">
					<input type="text" name="tsUserName" value="<?php if (!empty($_smarty_tpl->tpl_vars['inputTSinfo']->value)) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['inputTSinfo']->value['user_name'] ));
} else {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['teamspiritSetting']->value['user_name'] ));
}?>" placeholder="連携API実行アカウントのユーザー名">
					<a href="javascript:void(0)" onclick="doPost('./oauthCheck', false)" class="btn_ts_setting">OAuth確認</a>
					<p style="margin-left:1rem"><?php if (!empty($_smarty_tpl->tpl_vars['form']->value['oauthResult'])) {
if ($_smarty_tpl->tpl_vars['form']->value['oauthResult'] == "OK") {?><i class="far fa-badge-check"></i><?php } else { ?><i class="far fa-engine-warning"></i><?php }
}?></p>
				</td>
			</tr>
			<tr>
				<th class="tit_sub">パスワード</th>
				<td class="ts_pass">
					<input type="password" name="tsUserPass" value="<?php if (!empty($_smarty_tpl->tpl_vars['inputTSinfo']->value)) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['inputTSinfo']->value['password'] ));
} else {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['teamspiritSetting']->value['password'] ));
}?>" placeholder="連携API実行アカウントのパスワード">
					<a href="javascript:void(0)" onclick="doPost('./saveTsSetting', false)" class="btn_ts_setting">保存</a>
				</td>
			</tr>
		</table>
		<?php }?>
		
		<table class="form_cnt">
			<tr>
				<th rowspan="1" class="tit">facetバージョン</th>
				<td colspan="1">ver<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['facetVersion']->value ));?>
</td>
			</tr>
		</table>

	</div>
</form>

<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
