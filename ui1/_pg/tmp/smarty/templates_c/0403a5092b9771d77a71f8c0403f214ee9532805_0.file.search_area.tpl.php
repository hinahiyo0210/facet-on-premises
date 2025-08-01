<?php
/* Smarty version 4.5.3, created on 2024-09-20 14:15:52
  from '/var/www/html/ui1/_pg/app/person/search_area.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ed05085d6fd2_61527727',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0403a5092b9771d77a71f8c0403f214ee9532805' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/person/search_area.tpl',
      1 => 1724397577,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ed05085d6fd2_61527727 (Smarty_Internal_Template $_smarty_tpl) {
?>
<style type="text/css">
		<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."searchType"] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>
		.<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition {
			display: none;
		}
		.<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
resetBtn {
			display: none;
		}
	<?php }?>
	</style>

<?php echo '<script'; ?>
>

$(function() {

	$("input[name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
searchType']").click(function() {
				if ($("input[name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
searchType']:checked").val() == "1") {
			$(".<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition").fadeOut(400);
			$("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
ResetBtn").hide();
		} else {
			$(".<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition").fadeIn(400);
			$("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
ResetBtn").show();
			document.getElementById("<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
ResetBtn").style.display = 'flex';
		}
			});

		$("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1").fSelect();
	$("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2").fSelect();
	$("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1) {
					return
				}
				
				$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
				
			})
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal()
		})
	});

    });

<?php echo '</script'; ?>
>

<input type="hidden" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
search_init" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
search_init" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."search_init"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
<table class="form_cnt">
	<tr <?php if (!empty(Session::getLoginUser("group_id"))) {?>class="hidden"<?php }?>>
		<th class="tit">ユーザー情報検索</th>
		<td colspan="2">
			<input type="radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
searchType" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."searchType"] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?> value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">全て表示</label>
			<input type="radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
searchType" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."searchType"] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 2) {?>checked<?php }?> value="2" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">特定のユーザーを絞り込む</label>
		</td>
	</tr>
	<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
		<th <?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>rowspan="7"<?php } else { ?>rowspan="4"<?php }?> class="tit">登録情報から検索</th>
		<th class="tit_sub">ID</th>
		<td><input type="text" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
personCode" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."personCode"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" placeholder="任意のID"></td>
	</tr>
	<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
		<th class="tit_sub">氏名</th>
		<td><input type="text" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
personName" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."personName"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" placeholder="名前を入力します"></td>
	</tr>
	<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
		<th class="tit_sub">ICカード番号</th>
		<td><input type="text" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
cardID" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."cardID"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" placeholder="カード番号を入力します"></td>
	</tr>
	<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
		<th>生年月日</th>
		<td>
			<p class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input type="text" class="flatpickr" data-position="above" data-allow-input="true" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date("Y") ));?>
/01/01" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
birthday" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."birthday"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" >
			</p>
		</td>
	</tr>
		<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
		<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
			<th class="fs-select-th-center">区分</th>
			<td>
				<p class="select">
					<select name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
person_type_code" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
person_type_code">
						<option value=""></option>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['personTypeList']->value, 'personType', false, 'person_type_code');
$_smarty_tpl->tpl_vars['personType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['person_type_code']->value => $_smarty_tpl->tpl_vars['personType']->value) {
$_smarty_tpl->tpl_vars['personType']->do_else = false;
?>
							<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."person_type_code"] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == $_smarty_tpl->tpl_vars['person_type_code']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['person_type_code']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['personType']->value['person_type_name'] ));?>
</option>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</select>
				</p>
			</td>
		</tr>
		<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
					<th class="tit_sub"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?></th>
			<td><input type="text" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
person_description1" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."person_description1"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" placeholder="<?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?>を入力します"></td>
		</tr>
		<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
					<th class="tit_sub"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?></th>
			<td><input type="text" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
person_description2" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."person_description2"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" placeholder="<?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?>を入力します"></td>
		</tr>
	<?php }?>
			<?php $_smarty_tpl->_assignInScope('devicesDisplay', array());?>
	<?php if (empty(Session::getLoginUser("group_id"))) {?>
	<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
		<th class="tit fs-select-th-center">カメラグループから検索</th>
		<td colspan="2" style="font-size: 0;">
			<select id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1" class="groups hidden" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
group_ids[]" multiple="multiple" disabled="disabled"> 				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
					<?php $_smarty_tpl->_assignInScope('selected', '');?>
					<?php if (exists($_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."group_ids"],$_smarty_tpl->tpl_vars['g']->value)) {?>
						<?php $_smarty_tpl->_assignInScope('selected', "selected");?>
						<?php $_smarty_tpl->_assignInScope('devicesDisplay', array_merge($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['group']->value['deviceIds']));?>
					<?php }?>
					<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value ));?>
" <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['selected']->value ));?>
><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group']->value['group_name'] ));?>
</option>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</select>
		</td>
	</tr>
	<?php } else { ?>
		<?php $_smarty_tpl->_assignInScope('devicesDisplay', array_keys($_smarty_tpl->tpl_vars['devices']->value));?>
	<?php }?>
			<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
		<th class="tit fs-select-th-center">カメラから検索</th>
		<td colspan="2">
			<div class="fs-select-center">
				<select id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2" class="devices hidden" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
device_ids[]" multiple="multiple" disabled="disabled"> 					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
						<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
							<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists($_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."device_ids"],$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
						<?php }?>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</select>
				<?php if (empty(Session::getLoginUser("group_id"))) {?>
				<input type="checkbox" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
noCam" value="1" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."noCam"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "1") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" >
				<label style="margin-left: 10px;" for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox">カメラ未登録ユーザー</label>
				<?php }?>
			</div>
		</td>
	</tr>
		<tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition period">
		<th class="tit">登録期間から検索</th>
		<td colspan="2">
			<div class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
createDateFrom" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."createDateFrom"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" type="text" autocomplete="off" class="flatpickr" data-allow-input="true" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d') ));?>
">
			</div>
			<span>〜</span>
			<div class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
createDateTo" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."createDateTo"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" type="text" autocomplete="off" class="flatpickr" data-allow-input="true" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d') ));?>
">
			</div>
		</td>
	</tr>
</table>
<?php }
}
