<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:18:17
  from '/var/www/html/ui1/_pg/app/_inc/log_item_view_1.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688189d9eae1b2_94795683',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f04944e381d95fc52a96fd9ab787507b422e8e05' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/log_item_view_1.tpl',
      1 => 1753318924,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688189d9eae1b2_94795683 (Smarty_Internal_Template $_smarty_tpl) {
?>
	<li class="device_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
">
		<p class="camera"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['deviceName'] ));?>
</p>
		<div class="img"><div class="recog_picture" style="background-image:url('<?php if ($_smarty_tpl->tpl_vars['item']->value['cardPicture']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['cardPicture'] ));
} elseif ($_smarty_tpl->tpl_vars['item']->value['pictureUrl']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['pictureUrl'] ));
} else { ?>/ui1/static/images/gray.png<?php }?>')"></div></div>
		<div class="txt_wrap">
			<p class="temperature <?php if ($_smarty_tpl->tpl_vars['item']->value['temperature_alarm'] == 2) {?>abnormal<?php }?>"><?php if (!is_null($_smarty_tpl->tpl_vars['item']->value['temp']) && (int)$_smarty_tpl->tpl_vars['item']->value['temp'] === 0) {?>温度測定失敗<?php } elseif (0 < $_smarty_tpl->tpl_vars['item']->value['temp']) {
echo h($_smarty_tpl->tpl_vars['item']->value['temp'],'',"℃");
} else { ?>-<?php }?></p>
			<p class="mask <?php if ($_smarty_tpl->tpl_vars['item']->value['mask'] == 2) {?>nomask<?php }?>"><?php if ($_smarty_tpl->tpl_vars['item']->value['mask'] == 1) {?>マスク着用<?php } elseif ($_smarty_tpl->tpl_vars['item']->value['mask'] == 2) {?>マスク未着用<?php }?></p>
			<div class="desc">
				<p class="time"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatDate($_smarty_tpl->tpl_vars['item']->value['recog_time'],"Y/m/d H:i:s") ));?>
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
					<?php if ($_smarty_tpl->tpl_vars['item']->value['person_description1']) {?><p><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_description1'] ));?>
</p><?php }?>
					<?php if ($_smarty_tpl->tpl_vars['item']->value['person_description2']) {?><p><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_description2'] ));?>
</p><?php }?>
				<?php }?>
				<?php if (!empty($_smarty_tpl->tpl_vars['item']->value['search_score'])) {?>
					<p class="score">SCORE<span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( sprintf('%.1f',$_smarty_tpl->tpl_vars['item']->value['search_score']) ));?>
%</span></p>
				<?php }?>
			</div>
		</div>
		<?php if (empty($_smarty_tpl->tpl_vars['item']->value['pass'])) {?><p class="notauth_tag">NO PASS</p><?php }?>
	</li>

<?php }
}
