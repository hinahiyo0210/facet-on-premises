<!DOCTYPE HTML>
<html lang="ja" dir="ltr">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="keywords" content="">
<meta name="description" content="">
<title>{$title}</title>

<link rel="icon" href="/ui1/static/images/favicon_valtec.ico">

<!-- CSS -->
<link rel="stylesheet" href="/ui1/static/css/reset.css">
<link rel="stylesheet" href="/ui1/static/css/all.css">
<link rel="stylesheet" href="/ui1/static/css/style.css?_t={filemtime("`$smarty.const.DIR_FRONT`/static/css/style.css")}">
<!-- JS -->
<script src="/ui1/static/js/jquery/jquery-3.4.1.min.js"></script>
<script src="/ui1/static/js/jquery/jquery-ui.min.js"></script>

<script src="/ui1/static/js/flatpickr/flatpickr.js"></script>
<script src="/ui1/static/js/flatpickr/ja.js"></script>
<link rel="stylesheet" href="/ui1/static/js/flatpickr/flatpickr.min.css">
<script src="/ui1/static/js/Chart.js/Chart.min.js"></script>

<script src="/ui1/static/js/common.js?_t={filemtime("`$smarty.const.DIR_FRONT`/static/js/common.js")}"></script>
<script src="/ui1/static/js/dev-common.js?_t={filemtime("`$smarty.const.DIR_FRONT`/static/js/dev-common.js")}"></script>
<script src="/ui1/static/js/app.js?_t={filemtime("`$smarty.const.DIR_FRONT`/static/js/app.js")}"></script>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
{* add-start founder zouzhiyuan *}
<script src="/ui1/static/js/lodash/lodash.min.js"></script>
{* add-start founder zouzhiyuan *}

<script type="text/javascript">
	
	var _ACTION = "{$_controllerName}.{$_actionName}";

	{* トランザクショントークンをform(post限定)へ落とす *}
	$(function() {

		$("form[method='post']").each(function(idx) {
			
			$(this).append("<input type='hidden' name='{$___WebTransactionToken_.name|default:""}' value='{$___WebTransactionToken_.value|default:""}' />");
			
		});
		
	});
	
	
</script>
</head>
<body class="page_{strtolower($_controllerName)} action_{strtolower($_actionName)}">
	
	{* 指定された位置までスクロール。 *}
	{if req("_p")}
		<style type="text/css">
			body { opacity: 0; }
		</style>
		<script>
			$(function() {
				$("body").css("opacity", "1");
				$(window).scrollTop(parseInt("{hjs(req("_p")) nofilter}", 36));
			});
		</script>
	{/if}

	{* 完了メッセージの表示。 *}
	{if Session::existsMessages()}
		{include file=$smarty.const.DIR_APP|cat:'/_inc/info_messages.tpl' messages=Session::getMessage($form.msg)}
	{/if}

	{* エラーメッセージの表示 *}
	{if req("msg") == "tokenErr"}
		{include file=$smarty.const.DIR_APP|cat:'/_inc/error_messages.tpl' messages="最新ではない情報に対して登録操作が実行されました。登録は実行されていません。最新の情報に対し、再度登録操作を行ってください。"}
	{/if}

	{if Errors::isErrored()}
		{include file=$smarty.const.DIR_APP|cat:'/_inc/error_messages.tpl' messages=Errors::getMessages()}
	{/if}
	
	
	{if empty($noHeader)}

		<header class="mainbar">
			<h1 class="product">{if Session::getLoginUser("header_title")}{Session::getLoginUser("header_title")}{else}facet{if ENABLE_AWS} Cloud{/if}{/if}</h1>
			<p class="page_name"><span class="icon"><i class="fas {$icon|default:""}"></i></span>{$title}</p>
			<div class="mainbar_info">
	{*			<a href="#" class="info"><span class="icon"><i class="fas fa-bell"></i></span><span class="info_count">2</span></a>	*}
				<div class="account_wrap">
					<p class="account">{if Session::getLoginUser("logo_url")}<span class="icon_account"><img src="{Session::getLoginUser("logo_url")}" alt=""></span>{/if}<span class="account_name">{Session::getLoginUser("user_name")}</span><i class="fas fa-caret-down"></i></p>
					<a href="/ui1/user/modPassword" class="mod_password">パスワード変更</a>
					<a href="/ui1/logout" class="logout">ログアウト</a>
				</div>
				<div class="logo_dscope" style="height:80%;display:flex;align-items:center;">
					{if Session::getLoginUser("header_logo_url")}
						<div style="height:100%;">
							<img src="{Session::getLoginUser("header_logo_url")}" style="width:auto;height:100%;">
						</div>
					{else}
						<img src="/ui1/static/images/logo_valtec.png" alt="D-Scope" style="height:60%;">
					{/if}
				</div>
			</div>
		</header>
		<div class="adminmenuwrap">
			<nav class="adminmenu">
				<ul>
					{* mod-start founder yaozhengbang *}
					{assign var = "isAdmin" value = Session::getLoginUser("user_flag") == 1}
					{if (in_array("dashboard", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin)}
					<li {if $_controllerName == "Dashboard"}class="current"{/if}>
						<a href="/ui1/dashboard/"><span class="icon"><i class="fas fa-tachometer-alt-fast"></i></span><span class="menu_name">ダッシュボード</span></a>
					</li>
					{/if }

					{if (Session::getLoginUser("enter_exit_mode_flag") == 1 && (in_array("enterExitManage", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin))}
					<li {if $_controllerName == "EnterExitManage"}class="current"{/if}>
						<a href="/ui1/enterExitManage/"><span class="icon"><i class="fas fa-door-open"></i></span><span class="menu_name">入退管理</span></a>
					</li>
					{/if}

					{if (in_array("monitor", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin)}
					<li {if $_controllerName == "Monitor"}class="current"{/if}>
						<a href="/ui1/monitor/"><span class="icon"><i class="fas fa-video"></i></span><span class="menu_name">リアルタイムモニタ</span></a>
					</li>
					{/if }

					{if (in_array("log", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin)}
					<li {if $_controllerName == "Log"}class="current"{/if}>
						<a href="/ui1/log/"><span class="icon"><i class="fas fa-list"></i></span><span class="menu_name">ログ一覧</span></a>
					</li>
					{/if }

					{if (in_array("person", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin)}
					<li {if $_controllerName == "Person"}class="current"{/if}>
						<a href="/ui1/person/"><span class="icon"><i class="fas fa-user-circle"></i></span><span class="menu_name">ユーザー登録・変更</span></a>
					</li>
					{/if }

					{if (Session::getLoginUser("apb_mode_flag") == 1 && (in_array("apbLog", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin))}
					<li {if $_controllerName == "ApbLog"}class="current"{/if}>
						<a href="/ui1/apbLog/"><span class="icon"><i class="fas fa-outdent"></i></span><span class="menu_name">APBログ一覧</span></a>
					</li>
					{/if}
					
					{if $isAdmin && (Session::getLoginUser("teamspirit_flag") > 0)}
					<li {if $_controllerName == "AttendanceLog"}class="current"{/if}>
						<a href="/ui1/attendanceLog/"><span class="icon"><i class="far fa-clipboard-user"></i></span><span class="menu_name">勤怠ログ一覧</span></a>
					</li>
					{/if}

	{*				<li {if $_controllerName == "Calendar"}class="current"{/if}><a href="/ui1/calendar/" ><span class="icon"><i class="fas fa-calendar-alt"></i></span><span class="menu_name">カレンダー</span></a></li>     *}

					{if (in_array("device", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin)}
					<li {if $_controllerName == "Device"}class="current"{/if}>
						<a href="/ui1/device/"><span class="icon"><i class="fas fa-cog"></i></span><span class="menu_name">端末設定</span></a>
					</li>
					{/if}

					{if $isAdmin}
					<li {if $_controllerName == "DeviceMaintenance"}class="current"{/if}>
						<a href="/ui1/deviceMaintenance/"><span class="icon"><i class="fas fa-cogs"></i></span><span class="menu_name">端末メンテナンス</span></a>
					</li>
					{/if}

					{if $isAdmin}
					<li {if $_controllerName == "IdManage"}class="current"{/if}>
						<a href="/ui1/idManage/"><span class="icon"><i class="fas fa-users-cog"></i></span><span class="menu_name">ログインID管理</span></a>
					</li>
					{/if}

	{*				<li {if $_controllerName == "Info"}class="current"{/if}><a href="/ui1/info/"><span class="icon"><i class="fas fa-bell"></i></span><span class="menu_name">お知らせ</span><span class="info_count">2</span></a></li>	*}

					{if $isAdmin}
					<li {if $_controllerName == "OperationLog"}class="current"{/if}><a href="/ui1/operationLog/">
							<span class="icon"><i class="fas fa-user-clock"></i></span><span class="menu_name">操作ログ</span></a>
					</li>
					{/if}

					{if $isAdmin}
					<li {if $_controllerName == "FacetSetting"}class="current"{/if}>
						<a href="/ui1/facetSetting/"><span class="icon"><i class="fas fa-wrench"></i></i></span><span class="menu_name">facet設定</span></a>
					</li>
					{/if}

					{if (in_array("help", Session::getUserFunctionAccess("url_menu_name")) || $isAdmin)}
					<li {if $_controllerName == "Help"}class="current"{/if}>
						<a href="/ui1/help/"><span class="icon"><i class="fas fa-question-circle"></i></span><span class="menu_name">ヘルプ</span></a>
					</li>
					{/if}
					{* mod-end founder yaozhengbang *}

					<li>
						<a href="javascript:void(0)" class="menu_close"><span class="icon"><i class="fas fa-chevron-circle-left"></i></span><span class="menu_name">メニューを閉じる</span></a>
					</li>
				</ul>
			</nav>
		</div>

		<!-- コンテンツ -->
		<div class="main_container">

	{/if}


