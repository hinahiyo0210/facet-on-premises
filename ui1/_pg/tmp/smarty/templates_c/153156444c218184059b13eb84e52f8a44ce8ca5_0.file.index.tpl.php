<?php
/* Smarty version 4.5.3, created on 2024-09-18 09:59:43
  from '/var/www/html/ui1/_pg/app/index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ea25ff5c1b02_40109497',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '153156444c218184059b13eb84e52f8a44ce8ca5' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/index.tpl',
      1 => 1725590014,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ea25ff5c1b02_40109497 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "ログイン");
$_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/header.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('noHeader'=>true), 0, true);
?>

<?php echo '<script'; ?>
>

function showPassModal() {
	
	showModal("パスワードをお忘れの場合", $("#passModal").html());
	
}

<?php echo '</script'; ?>
>

<div id="passModal" style="display:none">
	<br />
	ご本人様確認の上で、パスワードをリセットさせて頂きます。<br />
	<br />
	大変お手数ではございますが、ご契約の際の販売代理店窓口にお問い合わせください。<br />
	<br />
	
	<div class="btn_1">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
	</div>
	
</div>


<div class="login_cnt">

	<form action="./login" method="post">
  <h1 class="tit">facet<?php if (ENABLE_AWS) {?> Cloud<?php }?></h1>
		<h2 class="tit_element">ログインID</h2>
		<p class="element"><input name="login_id" type="text" placeholder="ログインIDを入力"></p>
		<h2 class="tit_element">パスワード</h2>
		<p class="element"><input name="password" type="password" placeholder="パスワードを入力"></p>
		<input type="submit" value="ログイン" class="enter-submit btn_login">
		<a href="javascript:void(0)" onclick="showPassModal()">パスワードを忘れた方はこちら<i class="far fa-angle-right"></i></a>
	</form>

</div>

<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('noHeader'=>true), 0, true);
}
}
