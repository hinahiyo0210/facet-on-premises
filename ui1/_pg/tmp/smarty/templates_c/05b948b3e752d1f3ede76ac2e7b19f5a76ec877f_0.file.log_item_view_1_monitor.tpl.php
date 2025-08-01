<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:17:57
  from '/var/www/html/ui1/_pg/app/_inc/log_item_view_1_monitor.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688189c522ef46_08818898',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '05b948b3e752d1f3ede76ac2e7b19f5a76ec877f' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/log_item_view_1_monitor.tpl',
      1 => 1753319858,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688189c522ef46_08818898 (Smarty_Internal_Template $_smarty_tpl) {
?>
<li class="device_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
">
		<p class="camera"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['deviceName'] ));?>
</p>
		<div class="img"><div class="recog_picture" style="background-image:url('<?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['cardPicture'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['cardPicture'] ));
} elseif ((($tmp = $_smarty_tpl->tpl_vars['item']->value['pictureUrl'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['pictureUrl'] ));
} else { ?>/ui1/static/images/gray.png<?php }?>')"></div></div>
		<div class="txt_wrap">
			<p class="temperature <?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['temperature_alarm'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 2) {?>abnormal<?php }?>"><?php if (!is_null((($tmp = $_smarty_tpl->tpl_vars['item']->value['temp'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) && (int)$_smarty_tpl->tpl_vars['item']->value['temp'] === 0) {?>温度測定失敗<?php } elseif (0 < (($tmp = $_smarty_tpl->tpl_vars['item']->value['temp'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {
echo h($_smarty_tpl->tpl_vars['item']->value['temp'],'',"℃");
} else { ?>-<?php }?></p>
			<p class="mask <?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['mask'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 2) {?>nomask<?php }?>"><?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['mask'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>マスク着用<?php } elseif ((($tmp = $_smarty_tpl->tpl_vars['item']->value['mask'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 2) {?>マスク未着用<?php }?></p>
			<div class="desc">
				<p class="time"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatDate((($tmp = $_smarty_tpl->tpl_vars['item']->value['recog_time'] ?? null)===null||$tmp==='' ? null ?? null : $tmp),"Y/m/d H:i:s") ));?>
</p>
				<?php if (empty($_smarty_tpl->tpl_vars['item']->value['personCode'])) {?>
					<p class="id">ゲスト</p>
				<?php } else { ?>
					<p class="id">ID:<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personCode'] ));?>
</p>
					<p class="name"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personName'] ));?>
</p>
				<?php }?>
				<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
					<?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['person_description1'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?><p><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_description1'] ));?>
</p><?php }?>
					<?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['person_description2'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?><p><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_description2'] ));?>
</p><?php }?>
				<?php }?>
				<?php if (!empty($_smarty_tpl->tpl_vars['item']->value['search_score'])) {?>
					<p class="score">SCORE<span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( sprintf('%.1f',$_smarty_tpl->tpl_vars['item']->value['search_score']) ));?>
%</span></p>
				<?php }?>
								<a class="unlock_btn btn_blue"  href="javascript:void(0)" onclick="doDeviceOpenOnce('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
','<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['deviceName'] ));?>
');return false;">ドア開錠</a>
			</div>
		</div>
		<?php if (empty($_smarty_tpl->tpl_vars['item']->value['pass'])) {?><p class="notauth_tag">NO PASS</p><?php }?>
	</li>

<?php }
}
