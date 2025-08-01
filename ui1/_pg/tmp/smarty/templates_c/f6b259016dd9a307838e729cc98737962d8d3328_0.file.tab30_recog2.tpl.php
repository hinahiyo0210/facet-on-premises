<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:49:40
  from '/var/www/html/ui1/_pg/app/device/tab30_recog2.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818324bfb916_53648826',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f6b259016dd9a307838e729cc98737962d8d3328' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/device/tab30_recog2.tpl',
      1 => 1725437716,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68818324bfb916_53648826 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

$(function() {
		$("#recog2_m1").fSelect();
	$("#recog2_m2").fSelect();

	$("#recog2_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})

		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#recog2_m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1) {
					return
				}
				
				$deviceSelect.append(`<option value="${device.id}">${device.name}</option>`)
				
			})
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal()
		})
	});
	var toggleBtn = function() {
		if ($("#recog2_m2").fSelectedValues().length && $("[name='regist_recog_config_set_id']").val()) {
			$("#regist_recog_set_btn").removeClass("btn_disabled");
		} else {
			$("#regist_recog_set_btn").addClass("btn_disabled");
		}
	};
	$("[name='regist_recog_config_set_id']").change(toggleBtn);
	$("#recog2_m1").change(toggleBtn);
	$("#recog2_m2").change(toggleBtn);
	});
<?php echo '</script'; ?>
>

<h2 class="tit_cnt_main">認証関連設定の割当</h2>
<p class="cap_cnt_main">認証関連基本設定・更新で作成したセットをカメラに割り当てます。</p>
<div class="tit_wrap">
	<h3 class="tit">認証関連基本設定選択</h3>
	<p class="cap">設定するセットを選択してください。</p>
</div>
<table class="form_cnt set_group">
	<tr><th>セット選択</th>
		<td>
			<p class="select">
				<select name="regist_recog_config_set_id">
					<option value=""></option>
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recogConfigSets']->value, 'set');
$_smarty_tpl->tpl_vars['set']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['set']->value) {
$_smarty_tpl->tpl_vars['set']->do_else = false;
?>
						<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['regist_recog_config_set_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['set']->value['recog_config_set_id']) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['set']->value['recog_config_set_id'] ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['set']->value['recog_config_set_name'] ));?>
</option>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</select>
			</p>
		</td>
	</tr>

	<tr class="allocation"><th class="tit">割当先</th></tr>
		<?php $_smarty_tpl->_assignInScope('devicesDisplay', array());?>
	<?php if (empty(Session::getLoginUser("group_id"))) {?>
				<tr class="allocation"><th class="fs-select-th-center">グループ選択</th>
			<td>
				<p>
										<select id="recog2_m1" class="groups hidden" name="regist_recog_config_set_group_ids[]" multiple="multiple">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
							<?php $_smarty_tpl->_assignInScope('selected', '');?>
							<?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["regist_recog_config_set_group_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['g']->value)) {?>
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
									</p>
			</td>
		</tr>
			<?php } else { ?>
		<?php $_smarty_tpl->_assignInScope('devicesDisplay', array_keys($_smarty_tpl->tpl_vars['devices']->value));?>
	<?php }?>
		<tr class="allocation"><th class="fs-select-th-center">カメラを選択</th>
		<td id="cameraSelect">
						<select id="recog2_m2" class="devices hidden" name="regist_recog_set_device_ids[]" multiple="multiple">
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
					<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
						<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["regist_recog_set_device_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
					<?php }?>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</select>
					</td>
	</tr>
</table>
<a id="regist_recog_set_btn" href="javascript:void(0);" onclick="registDeviceConfigBegin('regist_recog_set_device_ids[]', 'recogConfig', 'regist_recog_config_set_id', null)" class="btn_red btn_disabled">カメラへ設定を登録</a>
<?php }
}
