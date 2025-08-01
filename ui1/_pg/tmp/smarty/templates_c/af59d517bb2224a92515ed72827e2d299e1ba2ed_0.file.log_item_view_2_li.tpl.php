<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:21:31
  from '/var/www/html/ui1/_pg/app/_inc/log_item_view_2_li.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818a9be14fa3_39604913',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'af59d517bb2224a92515ed72827e2d299e1ba2ed' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/log_item_view_2_li.tpl',
      1 => 1723534874,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68818a9be14fa3_39604913 (Smarty_Internal_Template $_smarty_tpl) {
?>
<li rid="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['recog_log_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" class="device_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
 recog_row r_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['recog_log_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
"><img src="<?php if ((($tmp = $_smarty_tpl->tpl_vars['item']->value['pictureUrl'] ?? null)===null||$tmp==='' ? false ?? null : $tmp)) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['pictureUrl'] ));
} else { ?>/ui1/static/images/gray.png<?php }?>"></li>

<?php }
}
