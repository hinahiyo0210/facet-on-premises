<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:47:57
  from '/var/www/html/ui1/_pg/app/_inc/info_messages.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688182bd2851e2_90567407',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e684aa30050d7d9a50461b3aee669936cabc0a70' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/info_messages.tpl',
      1 => 1703242896,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688182bd2851e2_90567407 (Smarty_Internal_Template $_smarty_tpl) {
?><div id="info_message">
	<?php if (is_array($_smarty_tpl->tpl_vars['messages']->value)) {?>
		
		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['messages']->value, 'msg');
$_smarty_tpl->tpl_vars['msg']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['msg']->value) {
$_smarty_tpl->tpl_vars['msg']->do_else = false;
?>
			<div class="info_msg_item"><?php echo $_smarty_tpl->tpl_vars['msg']->value;?>
</div>
		<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		
	<?php } else { ?>
		<div class="info_msg_item"><?php echo $_smarty_tpl->tpl_vars['messages']->value;?>
</div>
	<?php }?>

</div>

<div id="mask"></div>

<?php echo '<script'; ?>
>

	$("#mask").fadeOut(500, function() {
		$("#info_message").fadeOut(2000);
	});
	
<?php echo '</script'; ?>
>
<?php }
}
