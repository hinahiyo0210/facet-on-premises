<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:20:08
  from '/var/www/html/ui1/_pg/app/_inc/log_item_view_2_tr.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818a487e0955_00316610',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7407d283aa07a839517e0eb69583676b795fff25' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/log_item_view_2_tr.tpl',
      1 => 1753319995,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68818a487e0955_00316610 (Smarty_Internal_Template $_smarty_tpl) {
?>
	<tr rid="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['recog_log_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" class="device_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
 recog_row r_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['recog_log_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
 <?php if (empty($_smarty_tpl->tpl_vars['item']->value['pass'])) {?>notauth<?php }?>">
		<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatDate((($tmp = $_smarty_tpl->tpl_vars['item']->value['recog_time'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"Y/m/d") ));?>
<br><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatDate((($tmp = $_smarty_tpl->tpl_vars['item']->value['recog_time'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"H:i:s") ));?>
</td>
				<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['deviceGroupName'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
				<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['deviceName'] ));?>
</td>
		<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( UiRecogLogService::accessTypeNameMap((($tmp = $_smarty_tpl->tpl_vars['item']->value['accessType'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),(($tmp = $_smarty_tpl->tpl_vars['item']->value['cardType'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) ));?>
</td>
		<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
		<td><?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['enter_exit_type_flag'] ?? null)===null||$tmp==='' ? false ?? null : $tmp) == 1) {?>入室<?php } elseif ((($tmp = $_smarty_tpl->tpl_vars['item']->value['enter_exit_type_flag'] ?? null)===null||$tmp==='' ? false ?? null : $tmp) == 2) {?>退室<?php }?></td>
		<?php }?>
		<td><?php if (empty($_smarty_tpl->tpl_vars['item']->value['personCode'])) {?>ゲスト<?php } else {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personCode'] ));
}?></td>
		<td style="word-break:break-all"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['personName'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
		<td style="word-break:break-all"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['cardNo'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
		<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
		<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['personTypes']->value[(($tmp = $_smarty_tpl->tpl_vars['item']->value['person_type_code'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
		<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['person_description1'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
		<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['person_description2'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
		<?php }?>
				<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['passStr'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
				<td <?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['temperature_alarm'] ?? null)===null||$tmp==='' ? false ?? null : $tmp) == 2) {?>class="abnormal"<?php }?>><?php if (!is_null((($tmp = $_smarty_tpl->tpl_vars['item']->value['temp'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) && (int)$_smarty_tpl->tpl_vars['item']->value['temp'] === 0) {?>測定失敗<?php } elseif (0 < (($tmp = $_smarty_tpl->tpl_vars['item']->value['temp'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {
echo h($_smarty_tpl->tpl_vars['item']->value['temp'],'',"℃");
} else { ?>-<?php }?></td>
		<td <?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['mask'] ?? null)===null||$tmp==='' ? false ?? null : $tmp) == 2) {?>nomask<?php }?>><?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['mask'] ?? null)===null||$tmp==='' ? false ?? null : $tmp) == 1) {?>着用<?php } elseif ((($tmp = $_smarty_tpl->tpl_vars['item']->value['mask'] ?? null)===null||$tmp==='' ? false ?? null : $tmp) == 2) {?>未着用<?php }?></td>
		<td><?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['search_score'] ?? null)===null||$tmp==='' ? false ?? null : $tmp)) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( sprintf('%.1f',$_smarty_tpl->tpl_vars['item']->value['search_score']) ));?>
%<?php }?></td>
		<?php if (!empty($_smarty_tpl->tpl_vars['recogPassFlags']->value)) {?>
			<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['passFlagName'] ));?>
</td>
		<?php }?>
				<td><a href="javascript:void(0)" onclick="showPic(this)" class="person_picture_view" person-picture-url="<?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['cardPicture'] ?? null)===null||$tmp==='' ? false ?? null : $tmp)) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['cardPicture'] ));
} else {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['pictureUrl'] ?? null)===null||$tmp==='' ? "/ui1/static/images/gray.png" ?? null : $tmp) ));
}?>" recog-log-detail="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['detail'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
"><i class="fas fa-portrait"></i></a></td>
			</tr>
<?php }
}
