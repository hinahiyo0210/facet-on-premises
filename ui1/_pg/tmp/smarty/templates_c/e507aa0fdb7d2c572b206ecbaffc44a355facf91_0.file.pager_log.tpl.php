<?php
/* Smarty version 4.5.3, created on 2025-08-01 16:13:09
  from '/var/www/html/ui1/_pg/app/_inc/pager_log.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688c6905778035_19217886',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e507aa0fdb7d2c572b206ecbaffc44a355facf91' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/pager_log.tpl',
      1 => 1754032255,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688c6905778035_19217886 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="list_nav">
	<p class="txt">表示形式 :</p>
	<div class="format">
		<a href="./<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( replaceUrl('view=1') ));?>
" <?php if ($_smarty_tpl->tpl_vars['form']->value['view'] == 1) {?>class="active" style="pointer-events:none"<?php }?>><i class="fas fa-th-large"></i></a>
		<a href="./<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( replaceUrl('view=2') ));?>
" <?php if ($_smarty_tpl->tpl_vars['form']->value['view'] == 2) {?>class="active" style="pointer-events:none"<?php }?>><i class="fas fa-list"></i></a>
	</div>
	<p class="txt">表示件数 :</p>
	<p class="select">
		<select onchange="byName('pageNo').value = 1; byName('limit').value = this.value; document.searchForm.submit()">
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['pagerLimit']->value, 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
				<option <?php if ($_smarty_tpl->tpl_vars['form']->value['limit'] == $_smarty_tpl->tpl_vars['k']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
			<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		</select>
	</p>
	<p class="txt">件ごと  <span>(<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['pageInfo']->value->getPageNo()) ));?>
/<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['pageInfo']->value->getPageCount()) ));?>
)</span></p>
	<?php if ($_smarty_tpl->tpl_vars['pageInfo']->value->isEnablePrevPage()) {?>
		<a style="margin-left:1em" href="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( replaceUrl(concat('pageNo=',$_smarty_tpl->tpl_vars['pageInfo']->value->getPrevPageNo())) ));?>
" onclick="byName('pageNo').value = <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['pageInfo']->value->getPrevPageNo() ));?>
; doGet('./', true); return false;">前ページ</a>
	<?php }?>
	<?php if ($_smarty_tpl->tpl_vars['pageInfo']->value->isEnableNextPage()) {?>
		<a style="margin-left:1em" href="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( replaceUrl(concat('pageNo=',$_smarty_tpl->tpl_vars['pageInfo']->value->getNextPageNo())) ));?>
" onclick="byName('pageNo').value = <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['pageInfo']->value->getPrevPageNo() ));?>
; doGet('./', true); return false;">次ページ</a>
	<?php }?>
</div>

<?php }
}
