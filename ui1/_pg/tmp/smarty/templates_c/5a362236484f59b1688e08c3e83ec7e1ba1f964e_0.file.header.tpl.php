<?php
/* Smarty version 4.5.3, created on 2024-09-18 09:26:04
  from '/var/www/html/ui1/_pg/app/_inc/header.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ea1e1cad87b3_24384823',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '5a362236484f59b1688e08c3e83ec7e1ba1f964e' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/header.tpl',
      1 => 1725590030,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ea1e1cad87b3_24384823 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE HTML>
<html lang="ja" dir="ltr">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="keywords" content="">
<meta name="description" content="">
<title><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['title']->value ));?>
</title>

<link rel="icon" href="/ui1/static/images/favicon_valtec.ico">

<!-- CSS -->
<link rel="stylesheet" href="/ui1/static/css/reset.css">
<link rel="stylesheet" href="/ui1/static/css/all.css">
<link rel="stylesheet" href="/ui1/static/css/style.css?_t=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( filemtime(((string)(defined('DIR_FRONT') ? constant('DIR_FRONT') : null))."/static/css/style.css") ));?>
">
<!-- JS -->
<?php echo '<script'; ?>
 src="/ui1/static/js/jquery/jquery-3.4.1.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/ui1/static/js/jquery/jquery-ui.min.js"><?php echo '</script'; ?>
>

<?php echo '<script'; ?>
 src="/ui1/static/js/flatpickr/flatpickr.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/ui1/static/js/flatpickr/ja.js"><?php echo '</script'; ?>
>
<link rel="stylesheet" href="/ui1/static/js/flatpickr/flatpickr.min.css">
<?php echo '<script'; ?>
 src="/ui1/static/js/Chart.js/Chart.min.js"><?php echo '</script'; ?>
>

<?php echo '<script'; ?>
 src="/ui1/static/js/common.js?_t=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( filemtime(((string)(defined('DIR_FRONT') ? constant('DIR_FRONT') : null))."/static/js/common.js") ));?>
"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/ui1/static/js/dev-common.js?_t=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( filemtime(((string)(defined('DIR_FRONT') ? constant('DIR_FRONT') : null))."/static/js/dev-common.js") ));?>
"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/ui1/static/js/app.js?_t=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( filemtime(((string)(defined('DIR_FRONT') ? constant('DIR_FRONT') : null))."/static/js/app.js") ));?>
"><?php echo '</script'; ?>
>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<?php echo '<script'; ?>
 src="/ui1/static/js/lodash/lodash.min.js"><?php echo '</script'; ?>
>

<?php echo '<script'; ?>
 type="text/javascript">
	
	var _ACTION = "<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['_controllerName']->value ));?>
.<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['_actionName']->value ));?>
";

		$(function() {

		$("form[method='post']").each(function(idx) {
			
			$(this).append("<input type='hidden' name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['___WebTransactionToken_']->value['name'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
' value='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['___WebTransactionToken_']->value['value'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
' />");
			
		});
		
	});
	
	
<?php echo '</script'; ?>
>
</head>
<body class="page_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtolower($_smarty_tpl->tpl_vars['_controllerName']->value) ));?>
 action_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtolower($_smarty_tpl->tpl_vars['_actionName']->value) ));?>
">
	
		<?php if (req("_p")) {?>
		<style type="text/css">
			body { opacity: 0; }
		</style>
		<?php echo '<script'; ?>
>
			$(function() {
				$("body").css("opacity", "1");
				$(window).scrollTop(parseInt("<?php echo hjs(req("_p"));?>
", 36));
			});
		<?php echo '</script'; ?>
>
	<?php }?>

		<?php if (Session::existsMessages()) {?>
		<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/info_messages.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('messages'=>Session::getMessage($_smarty_tpl->tpl_vars['form']->value['msg'])), 0, true);
?>
	<?php }?>

		<?php if (req("msg") == "tokenErr") {?>
		<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/error_messages.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('messages'=>"最新ではない情報に対して登録操作が実行されました。登録は実行されていません。最新の情報に対し、再度登録操作を行ってください。"), 0, true);
?>
	<?php }?>

	<?php if (Errors::isErrored()) {?>
		<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/error_messages.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('messages'=>Errors::getMessages()), 0, true);
?>
	<?php }?>
	
	
	<?php if (empty($_smarty_tpl->tpl_vars['noHeader']->value)) {?>

		<header class="mainbar">
			<h1 class="product"><?php if (Session::getLoginUser("header_title")) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( Session::getLoginUser("header_title") ));
} else { ?>facet<?php if (ENABLE_AWS) {?> Cloud<?php }
}?></h1>
			<p class="page_name"><span class="icon"><i class="fas <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['icon']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
"></i></span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['title']->value ));?>
</p>
			<div class="mainbar_info">
					<div class="account_wrap">
					<p class="account"><?php if (Session::getLoginUser("logo_url")) {?><span class="icon_account"><img src="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( Session::getLoginUser("logo_url") ));?>
" alt=""></span><?php }?><span class="account_name"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( Session::getLoginUser("user_name") ));?>
</span><i class="fas fa-caret-down"></i></p>
					<a href="/ui1/user/modPassword" class="mod_password">パスワード変更</a>
					<a href="/ui1/logout" class="logout">ログアウト</a>
				</div>
				<div class="logo_dscope" style="height:80%;display:flex;align-items:center;">
					<?php if (Session::getLoginUser("header_logo_url")) {?>
						<div style="height:100%;">
							<img src="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( Session::getLoginUser("header_logo_url") ));?>
" style="width:auto;height:100%;">
						</div>
					<?php } else { ?>
						<img src="/ui1/static/images/logo_valtec.png" alt="D-Scope" style="height:60%;">
					<?php }?>
				</div>
			</div>
		</header>
		<div class="adminmenuwrap">
			<nav class="adminmenu">
				<ul>
										<?php $_smarty_tpl->_assignInScope('isAdmin', Session::getLoginUser("user_flag") == 1);?>
					<?php if ((in_array("dashboard",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value)) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "Dashboard") {?>class="current"<?php }?>>
						<a href="/ui1/dashboard/"><span class="icon"><i class="fas fa-tachometer-alt-fast"></i></span><span class="menu_name">ダッシュボード</span></a>
					</li>
					<?php }?>

					<?php if ((Session::getLoginUser("enter_exit_mode_flag") == 1 && (in_array("enterExitManage",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value))) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "EnterExitManage") {?>class="current"<?php }?>>
						<a href="/ui1/enterExitManage/"><span class="icon"><i class="fas fa-door-open"></i></span><span class="menu_name">入退管理</span></a>
					</li>
					<?php }?>

					<?php if ((in_array("monitor",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value)) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "Monitor") {?>class="current"<?php }?>>
						<a href="/ui1/monitor/"><span class="icon"><i class="fas fa-video"></i></span><span class="menu_name">リアルタイムモニタ</span></a>
					</li>
					<?php }?>

					<?php if ((in_array("log",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value)) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "Log") {?>class="current"<?php }?>>
						<a href="/ui1/log/"><span class="icon"><i class="fas fa-list"></i></span><span class="menu_name">ログ一覧</span></a>
					</li>
					<?php }?>

					<?php if ((in_array("person",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value)) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "Person") {?>class="current"<?php }?>>
						<a href="/ui1/person/"><span class="icon"><i class="fas fa-user-circle"></i></span><span class="menu_name">ユーザー登録・変更</span></a>
					</li>
					<?php }?>

					<?php if ((Session::getLoginUser("apb_mode_flag") == 1 && (in_array("apbLog",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value))) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "ApbLog") {?>class="current"<?php }?>>
						<a href="/ui1/apbLog/"><span class="icon"><i class="fas fa-outdent"></i></span><span class="menu_name">APBログ一覧</span></a>
					</li>
					<?php }?>
					
					<?php if ($_smarty_tpl->tpl_vars['isAdmin']->value && (Session::getLoginUser("teamspirit_flag") > 0)) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "AttendanceLog") {?>class="current"<?php }?>>
						<a href="/ui1/attendanceLog/"><span class="icon"><i class="far fa-clipboard-user"></i></span><span class="menu_name">勤怠ログ一覧</span></a>
					</li>
					<?php }?>

	
					<?php if ((in_array("device",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value)) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "Device") {?>class="current"<?php }?>>
						<a href="/ui1/device/"><span class="icon"><i class="fas fa-cog"></i></span><span class="menu_name">端末設定</span></a>
					</li>
					<?php }?>

					<?php if ($_smarty_tpl->tpl_vars['isAdmin']->value) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "DeviceMaintenance") {?>class="current"<?php }?>>
						<a href="/ui1/deviceMaintenance/"><span class="icon"><i class="fas fa-cogs"></i></span><span class="menu_name">端末メンテナンス</span></a>
					</li>
					<?php }?>

					<?php if ($_smarty_tpl->tpl_vars['isAdmin']->value) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "IdManage") {?>class="current"<?php }?>>
						<a href="/ui1/idManage/"><span class="icon"><i class="fas fa-users-cog"></i></span><span class="menu_name">ログインID管理</span></a>
					</li>
					<?php }?>

	
					<?php if ($_smarty_tpl->tpl_vars['isAdmin']->value) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "OperationLog") {?>class="current"<?php }?>><a href="/ui1/operationLog/">
							<span class="icon"><i class="fas fa-user-clock"></i></span><span class="menu_name">操作ログ</span></a>
					</li>
					<?php }?>

					<?php if ($_smarty_tpl->tpl_vars['isAdmin']->value) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "FacetSetting") {?>class="current"<?php }?>>
						<a href="/ui1/facetSetting/"><span class="icon"><i class="fas fa-wrench"></i></i></span><span class="menu_name">facet設定</span></a>
					</li>
					<?php }?>

					<?php if ((in_array("help",Session::getUserFunctionAccess("url_menu_name")) || $_smarty_tpl->tpl_vars['isAdmin']->value)) {?>
					<li <?php if ($_smarty_tpl->tpl_vars['_controllerName']->value == "Help") {?>class="current"<?php }?>>
						<a href="/ui1/help/"><span class="icon"><i class="fas fa-question-circle"></i></span><span class="menu_name">ヘルプ</span></a>
					</li>
					<?php }?>
					
					<li>
						<a href="javascript:void(0)" class="menu_close"><span class="icon"><i class="fas fa-chevron-circle-left"></i></span><span class="menu_name">メニューを閉じる</span></a>
					</li>
				</ul>
			</nav>
		</div>

		<!-- コンテンツ -->
		<div class="main_container">

	<?php }?>


<?php }
}
