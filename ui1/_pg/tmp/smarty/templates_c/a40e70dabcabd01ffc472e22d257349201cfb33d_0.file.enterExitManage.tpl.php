<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:47:40
  from '/var/www/html/ui1/_pg/app/enterExitManage/enterExitManage.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688182accf5cb9_26990605',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a40e70dabcabd01ffc472e22d257349201cfb33d' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/enterExitManage/enterExitManage.tpl',
      1 => 1723538953,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688182accf5cb9_26990605 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "入退管理");
$_smarty_tpl->_assignInScope('icon', "fas fa-door-open");
$_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/header.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

<?php echo '<script'; ?>
>

$(function() {

	$("#group_select").change(function() {
		changeSelect('./changeGroup', false);
	});
	
});

function saveTime(action, scrollSave) {

	document.saveTimeForm.method = "post";
	document.saveTimeForm.action = action;
	document.saveTimeForm.submit();

}

function changeSelect(action, scrollSave) {

	document.groupSelectForm.method = "get";
	document.groupSelectForm.action = action;
	document.groupSelectForm.submit();

}

<?php echo '</script'; ?>
>
<div class="flex_box search_content statistics_box">
	<form action="./" method="get" name="groupSelectForm" class="group_select_box flex_box">
		<p class="input_title">グループ</p>
		<p class="select" style="width:400px">
			<select id="group_select" name="device_group_id" <?php if (!empty(Session::getLoginUser("group_id"))) {?>style="pointer-events:none;background-color:#d8d8d8;"<?php }?>>
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groups']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
				<?php if ((isset($_smarty_tpl->tpl_vars['form']->value['device_group_id'])) && ($_smarty_tpl->tpl_vars['form']->value['device_group_id'] == $_smarty_tpl->tpl_vars['g']->value || Session::getLoginUser("group_id") == $_smarty_tpl->tpl_vars['g']->value)) {?>
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
	</form>
</div>

<div class="main_wrapper count_wrapper">
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title">入室者</h2>
				<p class="count_number"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['groupTotalCounts']->value[1]['count'] ?? null)===null||$tmp==='' ? 0 ?? null : $tmp) ));?>
人</p>
			</div>
		</div>
		<div class="icon_box enter_box"><i class="fas fa-portal-enter"></i></div>
	</div>
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title">退室者</h2>
				<p class="count_number"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['groupTotalCounts']->value[2]['count'] ?? null)===null||$tmp==='' ? 0 ?? null : $tmp) ));?>
人</p>
			</div>
		</div>
		<div class="icon_box exit_box"><i class="fas fa-portal-exit"></i></div>
	</div>
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title">在室者</h2>
				<p class="count_number"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['groupTotalCounts']->value[1]['count'] ?? null)===null||$tmp==='' ? 0 ?? null : $tmp)-(($tmp = $_smarty_tpl->tpl_vars['groupTotalCounts']->value[2]['count'] ?? null)===null||$tmp==='' ? 0 ?? null : $tmp) ));?>
人</p>
			</div>
		</div>
		<div class="icon_box enter_exit_box"><i class="fas fa-users"></i></div>
	</div>
</div>
<div class="sub_wrapper count_wrapper">
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['personTypes']->value, 'personType');
$_smarty_tpl->tpl_vars['personType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['personType']->value) {
$_smarty_tpl->tpl_vars['personType']->do_else = false;
?>
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['personType']->value['person_type_name'] ));?>
在室者</h2>
				<p class="count_number"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['personTypeCounts']->value[$_smarty_tpl->tpl_vars['personType']->value['person_type_code']]['count'] ?? null)===null||$tmp==='' ? 0 ?? null : $tmp) ));?>
人</p>
			</div>
		</div>
	</div>
	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
	<?php if ((count($_smarty_tpl->tpl_vars['personTypes']->value)%3) == 2) {?>
	<div class="count_countainer dummy_container"></div>
	<?php }?>
</div>

<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
