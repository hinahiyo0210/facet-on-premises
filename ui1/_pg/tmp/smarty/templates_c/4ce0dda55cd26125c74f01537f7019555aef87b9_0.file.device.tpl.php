<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:49:40
  from '/var/www/html/ui1/_pg/app/device/device.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818324a8e0b7_47444152',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4ce0dda55cd26125c74f01537f7019555aef87b9' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/device/device.tpl',
      1 => 1725439673,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./tab70_group.tpl' => 1,
    'file:./tab10_camera.tpl' => 1,
    'file:./tab20_recog1.tpl' => 1,
    'file:./tab30_recog2.tpl' => 1,
    'file:./tab40_system1.tpl' => 1,
    'file:./tab50_system2.tpl' => 1,
    'file:./tab60_alerm.tpl' => 1,
  ),
),false)) {
function content_68818324a8e0b7_47444152 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "端末設定");
$_smarty_tpl->_assignInScope('icon', "fa-cog");
$_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/header.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
echo '<script'; ?>
 src="/ui1/static/js/fselect/fSelect.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
		const groups = { }
	const groupsInit = []
	<?php if (empty(Session::getLoginUser("group_id"))) {?>
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'gid');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['gid']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
	groups[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['gid']->value ));?>
] = [ <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['group']->value['deviceIds'], 'deviceId');
$_smarty_tpl->tpl_vars['deviceId']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['deviceId']->value) {
$_smarty_tpl->tpl_vars['deviceId']->do_else = false;
?> '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['deviceId']->value ));?>
', <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?> ]
	groupsInit.push( { id: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['gid']->value ));?>
', name: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtr((string)$_smarty_tpl->tpl_vars['group']->value['group_name'], array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", 
                       "\n" => "\\n", "</" => "<\/", "<!--" => "<\!--", "<s" => "<\s", "<S" => "<\S",
                       "`" => "\\`", "\${" => "\\\$\{")) ));?>
' } )
	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
	<?php }?>
	const devices = []
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
	devices.push( { id: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
', name: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtr((string)$_smarty_tpl->tpl_vars['device']->value['name'], array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", 
                       "\n" => "\\n", "</" => "<\/", "<!--" => "<\!--", "<s" => "<\s", "<S" => "<\S",
                       "`" => "\\`", "\${" => "\\\$\{")) ));?>
', 'deviceType': '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtr((string)$_smarty_tpl->tpl_vars['device']->value['device_type'], array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", 
                       "\n" => "\\n", "</" => "<\/", "<!--" => "<\!--", "<s" => "<\s", "<S" => "<\S",
                       "`" => "\\`", "\${" => "\\\$\{")) ));?>
', 'groupId': '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['device_group_id'] ));?>
' } )
	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
	const firmwareVeresions = []
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['firmwareVeresionNames']->value, 'firmwareVeresion', false, 'n');
$_smarty_tpl->tpl_vars['firmwareVeresion']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['n']->value => $_smarty_tpl->tpl_vars['firmwareVeresion']->value) {
$_smarty_tpl->tpl_vars['firmwareVeresion']->do_else = false;
?>
	firmwareVeresions.push( { id: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['n']->value ));?>
', name: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['n']->value ));?>
', deviceTypeFlag: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtr((string)$_smarty_tpl->tpl_vars['firmwareVeresion']->value['device_type_flag'], array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", 
                       "\n" => "\\n", "</" => "<\/", "<!--" => "<\!--", "<s" => "<\s", "<S" => "<\S",
                       "`" => "\\`", "\${" => "\\\$\{")) ));?>
'})
	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

<?php echo '</script'; ?>
>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">
<?php echo '<script'; ?>
>

let auth_group = <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( Session::getLoginUser("group_id") ));?>


// タブ制御
$(function() {
	$(".tab_btn li").click(function() {
		var url = "./";
		if ($(this).attr("tab-name")) url = "./?tab=" + $(this).attr("tab-name");
		history.pushState("", "", url);
		document.registForm.tab.value = $(this).attr("tab-name");
	});

});

// 送信。
function doPost(action, scrollSave) {
	if (scrollSave) {
		document.registForm._p.value = parseInt($(window).scrollTop()).toString(36);
	}
	document.registForm.action = action;
	document.registForm.submit();
}
function doGet(action, scrollSave) {
	// URLが長くなりすぎないように、値の無いinput類はdisabledにする。
	disableEmptyInput($(document.registForm))

	if (scrollSave) {
		document.registForm._p.value = parseInt($(window).scrollTop()).toString(36)
	}
	document.registForm.action = action
	document.registForm.method = 'get'
	document.registForm.submit()
}

// 設定を取得。
function getDeviceConfig(deviceIdSelectName, targetAreaId) {
	
	var $select = $("select[name=" + deviceIdSelectName + "]");
	var deviceId = $select.val();
	if (deviceId == "") return;
	
	showModal("カメラ設定の読み込み", $("#diviceConfigGetModalTemplate").html());
	$("#modal_message .diviceConnectModal_loading").show();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_device_name").text($select.find("option:selected").text());
	
	doAjax("./getConfigByDevice", { device_id: deviceId }, function(data) {
	
		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			$("#modal_message .diviceConnectModal_error_msg").append(escapeHtml(data.error));
			return;
		}
		
		setFormValue($("#" + targetAreaId), data);
		removeModal();
		
	});
	
}

// 設定の反映を開始。
var _processingRegistConfigDevice;
var _processingRegistConfigDeviceQueue;
var _processingRegistRecogConfigSetId;
var _processingRegistSystemConfigSetId;
var _processingRegistVersionName;
var _processingRegistType;
function registDeviceConfigBegin(deviceIdCheckboxName, type, recogConfigSetIdName, systemConfigSetIdName, verionNameName) {
	let recogIndex;
	let systemIndex;
	$('.fs-dropdown').each(function(index) {
		if ($('.fs-dropdown').eq(index).next().attr('id') !== undefined) {
			if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('recog2_m1') > -1) {
				recogIndex = index;
			} else if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('regist_system_m1') > -1) {
				systemIndex = index;
			}
		}
	});
if ((typeof(auth_group) === 'number') || ($('.fs-dropdown').eq(recogIndex).hasClass('hidden') && $('.fs-dropdown').eq(systemIndex).hasClass('hidden'))) {
	var $deviceOptions = $(byNames(deviceIdCheckboxName)).find(":selected");
	var recogConfigSetIdName  = $(byName(recogConfigSetIdName)).val();
	var systemConfigSetIdName = $(byName(systemConfigSetIdName)).val();
	var verionName            = $(byName(verionNameName)).val();
	
	var title = "";
	if (type == "fw") {
		title = "ファームウェアアップデート";
	} else {
		title = "カメラへ設定を登録";
	}
	showModal(title, $("#diviceConfigRegistModalTemplate").html());

	_processingRegistRecogConfigSetId = recogConfigSetIdName;
	_processingRegistSystemConfigSetId = systemConfigSetIdName;
	_processingRegistVersionName = verionName;
	_processingRegistType = type;
	_processingRegistConfigDeviceQueue = [];
	$deviceOptions.each(function() {
		_processingRegistConfigDeviceQueue.push({ id:$(this).val(), name:$(this).text() });
	});
	
	registDeviceConfigNext();
}
	
}

// 設定の反映をリトライ。
function registDeviceConfigRetry() {
	_processingRegistConfigDeviceQueue.unshift(_processingRegistConfigDevice);
	registDeviceConfigNext();
}

// 次のデバイスの設定の反映を実行。
function registDeviceConfigNext() {

	$("#modal_message .diviceConnectModal_loading").show();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_complete").hide();

	var device = _processingRegistConfigDeviceQueue.shift();

	if (device == null) {
		// 全て終了。
		if ($("#modal_message .diviceConnectModal_error_names .error_device").length) {
			$("#modal_message .diviceConnectModal_error_names_area").show();
		}
		
		$("#modal_message .diviceConnectModal_loading").hide();
		$("#modal_message .diviceConnectModal_error").hide();
		$("#modal_message .diviceConnectModal_complete").show();
		return;
	}

	_processingRegistConfigDevice = device;
	
	// 次のデバイスへ。
	$("#modal_message .diviceConnectModal_device_name").text(device.name).hide().fadeIn(200);
	
	
	doAjax("./registConfigForDevice", { device_id: device.id, type: _processingRegistType, recog_config_set_id: _processingRegistRecogConfigSetId, system_config_set_id: _processingRegistSystemConfigSetId, version_name: _processingRegistVersionName }, function(data) {
		
		var $successArea = $("#modal_message .diviceConnectModal_success_names");
		var $errorArea   = $("#modal_message .diviceConnectModal_error_names");
		
		// エラー
		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			$("#modal_message .diviceConnectModal_error_area").text(data.error);
			
			$errorArea.find(".device_" +  device.id).remove();
			$errorArea.append($("<span style='margin:0 0.5em' class='error_device device_" + device.id + "'></span>").text(device.name));
			return;
		}

		// 成功
		$errorArea.find(".device_" +  device.id).remove();
		$successArea.append($("<span style='margin:0 0.5em' class='device_" + device.id + "'></span>").text(device.name));
		registDeviceConfigNext();
	});
	
}



<?php echo '</script'; ?>
>
<!-- カメラルダウン変更確認モーダル -->
<div id="groupsChangeModalTemplate" style="display:none">
	<div style="">
		<div style="height: 150px;">
			カメラ選択が初期化されますがよろしいですか？
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" id="groupsChangeModalBtnCancel" class="btn btn_gray" >いいえ</a>
			<a href="javascript:void(0);" id="groupsChangeModalBtnOk"  class="btn btn_red">はい</a>
		</div>
	</div>
</div>
<!-- デバイス通信ダイアログ(設定の読み込み) -->
<div id="diviceConfigGetModalTemplate" style="display:none">

	<div class="diviceConnectModal_loading">
		ただいま[<span class="diviceConnectModal_device_name"></span>]への通信を行っています。<br />
		通信状況によっては時間がかかる場合があります。<br />
		このままでお待ちください。
		<div id="loading" class="loader">Loading...</div>
	</div>
	
	<div class="diviceConnectModal_error" style="display:none">
		<div class="diviceConnectModal_error_msg">
			[<span class="diviceConnectModal_device_name"></span>]への通信中にエラーが発生しました。<br />
			<br />
		</div>
		<div class="btn_1">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>
		
	</div>
	
</div>

<!-- デバイス通信ダイアログ(設定の登録) -->
<div id="diviceConfigRegistModalTemplate" style="display:none">

	<div class="diviceConnectModal_loading">
		ただいま[<span class="diviceConnectModal_device_name"></span>]への通信を行っています。<br />
		通信状況によっては時間がかかる場合があります。<br />
		このままでお待ちください。
		<div id="loading" class="loader">Loading...</div>
	</div>
	
	<div class="diviceConnectModal_error" style="display:none">
		<div class="diviceConnectModal_error_msg">
			[<span class="diviceConnectModal_device_name"></span>]への通信中にエラーが発生しました。<br />
			<br />
			<span class="diviceConnectModal_error_area"></span><br />
			<br />
			<div style="color:#000">
				カメラの電源や、カメラのオンライン状態もご確認下さい。また、ファームウェアバージョンが最新で無い場合にもエラーが出る場合があります。
			</div>
		</div>
		<div class="btn_3">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
			<a href="javascript:void(0);" onclick="registDeviceConfigRetry()" class="btn btn_red">リトライする</a>
			<a href="javascript:void(0);" onclick="registDeviceConfigNext()" class="btn btn_red">次のカメラへ</a>
		</div>
		
	</div>

	<div class="diviceConnectModal_complete">
		<div class="trans_complete">
		下記の通りに処理が行われました。<br />
			＜成功＞<br />
			<span class="diviceConnectModal_success_names"></span><br />
			<div class="diviceConnectModal_error_names_area" style="display:none; color:red">
				＜失敗＞<br />
				<span class="diviceConnectModal_error_names"></span><br />
			</div>
		</div>
		<div class="btn_1">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>
	</div>
	
</div>


<form name="registForm" action="./" method="post">
	<input type="hidden" name="tab" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
	<input type="hidden" name="_p" />
		<div class="tab_container">

		<ul class="tab_btn">

			<?php if (empty((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))) {?>
								<?php $_smarty_tpl->_assignInScope('tabDeviceArray', array("groupInsert","recog1","recog2","system1","system2","alerm"));?>
				<?php $_tmp_array = isset($_smarty_tpl->tpl_vars['form']) ? $_smarty_tpl->tpl_vars['form']->value : array();
if (!(is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess)) {
settype($_tmp_array, 'array');
}
$_tmp_array['tab'] = $_smarty_tpl->tpl_vars['tabDeviceArray']->value[array_search(true,$_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value)];
$_smarty_tpl->_assignInScope('form', $_tmp_array);?>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[0]) {?>
				<li tab-name="groupInsert"  <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "groupInsert") {?>class="active"<?php }?>>カメラグループ設定</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[0]) {?>
				<li tab-name="insert"  <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "insert") {?>class="active"<?php }?>>カメラ設定</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[1]) {?>
				<li tab-name="recog1"  <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "recog1") {?>class="active"<?php }?>>認証関連基本設定・更新</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[2]) {?>
				<li tab-name="recog2"  <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "recog2") {?>class="active"<?php }?>>認証関連設定割当</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[3]) {?>
				<li tab-name="system1" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "system1") {?>class="active"<?php }?>>システム基本設定・更新</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[4]) {?>
				<li tab-name="system2" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "system2") {?>class="active"<?php }?>>システム設定割当</li>
			<?php }?>
			<?php if (ENABLE_AWS && $_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[5]) {?>
				<li tab-name="alerm"   <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "alerm") {?>class="active"<?php }?>>アラーム設定</li>
			<?php }?>

		</ul>

		<div class="tab_cnt_wrap">
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[0]) {?>
			<div class="tab_cnt<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "groupInsert") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab70_group.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[0]) {?>
			<div class="tab_cnt<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "insert") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab10_camera.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[1]) {?>
			<div class="tab_cnt<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "recog1") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab20_recog1.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[2]) {?>
			<div class="tab_cnt<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "recog2") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab30_recog2.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[3]) {?>
			<div class="tab_cnt<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "system1") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab40_system1.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[4]) {?>
			<div class="tab_cnt<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "system2") {?> show<?php }?> system_allocation"><?php $_smarty_tpl->_subTemplateRender("file:./tab50_system2.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if (ENABLE_AWS && $_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value[5]) {?>
			<div class="tab_cnt<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "alerm") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab60_alerm.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
		</div>
			</div>

</form>


<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
