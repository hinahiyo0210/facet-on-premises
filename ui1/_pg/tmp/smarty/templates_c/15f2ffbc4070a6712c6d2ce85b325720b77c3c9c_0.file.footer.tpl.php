<?php
/* Smarty version 4.5.3, created on 2024-09-18 09:26:04
  from '/var/www/html/ui1/_pg/app/_inc/footer.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ea1e1cae14f8_27370364',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '15f2ffbc4070a6712c6d2ce85b325720b77c3c9c' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/footer.tpl',
      1 => 1703242896,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ea1e1cae14f8_27370364 (Smarty_Internal_Template $_smarty_tpl) {
?>	<?php if (empty($_smarty_tpl->tpl_vars['noHeader']->value)) {?>

		</div>
		<!-- /コンテンツ -->

	<?php }?>
	
<?php echo '<script'; ?>
>
$(function() {
	flatpickr(".flatpickr", {
		locale:"ja",
		dateFormat: "Y/m/d",
	});
	flatpickr(".flatpickr_time", {
		enableTime: true,
		locale:"ja",
		dateFormat: "Y/m/d H:i",
	});
});

<?php echo '</script'; ?>
>

<?php echo '<script'; ?>
 type="text/javascript">doPageLast();<?php echo '</script'; ?>
>

</body>
</html>
<?php }
}
