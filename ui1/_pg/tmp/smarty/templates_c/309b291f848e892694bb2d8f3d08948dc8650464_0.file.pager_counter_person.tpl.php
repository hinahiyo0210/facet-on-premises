<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:47:50
  from '/var/www/html/ui1/_pg/app/_inc/pager_counter_person.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688182b66ee520_42835921',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '309b291f848e892694bb2d8f3d08948dc8650464' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/pager_counter_person.tpl',
      1 => 1723017779,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688182b66ee520_42835921 (Smarty_Internal_Template $_smarty_tpl) {
if ($_smarty_tpl->tpl_vars['topPager']->value) {?>
	<input type="hidden" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_limit"  value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['form']->value['tab'])."_limit"] ));?>
">
	<input type="hidden" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_pageNo" value="1">
<?php }?>

<div class="list_nav">
	<p class="txt">ヒット件数 :</p>
	<div class="format">
		<p class="txt"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['pageInfo']->value->getRowCount()) ));?>
 件</p>
	</div>
	<p class="txt">表示件数 :</p>
	<p class="select">
		<select onchange="byName('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_pageNo').value = 1; byName('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_limit').value = this.value; doGet('./<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
Search', true)">
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::pagerLimit(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
				<option <?php if ($_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['form']->value['tab'])."_limit"] == $_smarty_tpl->tpl_vars['k']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
			<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		</select>
	</p>
	<p class="txt">件ごと <span>(<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['pageInfo']->value->getPageNo()) ));?>
/<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['pageInfo']->value->getPageCount()) ));?>
)</span></p>
	<?php if ($_smarty_tpl->tpl_vars['pageInfo']->value->isEnablePrevPage()) {?>
		<?php $_smarty_tpl->_assignInScope('url', replaceUrl(array("_p"=>"90",((string)$_smarty_tpl->tpl_vars['form']->value['tab'])."_pageNo"=>$_smarty_tpl->tpl_vars['pageInfo']->value->getPrevPageNo(),"export_checkIdsKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['export_checkIdsKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"export_searchFormKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['export_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"trans_checkIdsKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_checkIdsKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"trans_searchFormKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))));?>
		<a style="margin-left:1em" href="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['url']->value ));?>
" onclick="byName('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_pageNo').value = <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['pageInfo']->value->getPrevPageNo() ));?>
; doGet('./<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
Search', true); return false;">前ページ</a>
	<?php }?>
	<?php if ($_smarty_tpl->tpl_vars['pageInfo']->value->isEnableNextPage()) {?>
		<?php $_smarty_tpl->_assignInScope('url', replaceUrl(array("_p"=>"90",((string)$_smarty_tpl->tpl_vars['form']->value['tab'])."_pageNo"=>$_smarty_tpl->tpl_vars['pageInfo']->value->getNextPageNo(),"export_checkIdsKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['export_checkIdsKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"export_searchFormKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['export_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"trans_checkIdsKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_checkIdsKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),"trans_searchFormKey"=>(($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))));?>
		<a style="margin-left:1em" href="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['url']->value ));?>
" onclick="byName('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_pageNo').value = <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['pageInfo']->value->getNextPageNo() ));?>
; doGet('./<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
Search', true); return false;">次ページ</a>
	<?php }?>
</div><?php }
}
