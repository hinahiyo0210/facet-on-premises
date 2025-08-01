<?php
/* Smarty version 4.5.3, created on 2024-09-20 14:15:52
  from '/var/www/html/ui1/_pg/app/person/person.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ed05084b4aa7_50823427',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '77fb032b97fdcb04e8a474ac0289ca1bf3ddabdc' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/person/person.tpl',
      1 => 1725949269,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./tab10_new.tpl' => 1,
    'file:./tab20_list.tpl' => 1,
    'file:./tab30_bulk.tpl' => 1,
    'file:./tab40_export.tpl' => 1,
    'file:./tab50_trans.tpl' => 1,
  ),
),false)) {
function content_66ed05084b4aa7_50823427 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "ユーザー登録・変更");
$_smarty_tpl->_assignInScope('icon', "fa-user-circle");
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
' } )
	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

<?php echo '</script'; ?>
>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">
<?php echo '<script'; ?>
>

// タブ制御
$(function() {

	$(".tab_btn li").click(function() {
		var url = "./";
		if ($(this).attr("tab-name")) url = "./?tab=" + $(this).attr("tab-name");
		history.pushState("", "", url);
		document.personForm.tab.value = $(this).attr("tab-name");
	});

});

// 送信。
function doPost(action, scrollSave) {
	if (action == './registPerson') {
		let newDropdownIndex;
		$('.fs-dropdown').each(function(index) {
			if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('new') > -1) {
				newDropdownIndex = index;
				return false;
			} else {
				return true;
			}
		});
		if ($('.fs-dropdown').eq(newDropdownIndex).hasClass('hidden')) {
			$(".no-req").val("");
			doFormSend(action, scrollSave, "post");
		}
	} else {
		$(".no-req").val("");
		doFormSend(action, scrollSave, "post");
	}
}
function doGet(action, scrollSave) { 
	// URLが長くなりすぎないように、値の無いinput類はdisabledにする。
	$(".no-req").val("");
	disableEmptyInput($(document.personForm));
	doFormSend(action, scrollSave, "get");
}

function doFormSend(action, scrollSave, method) {
	
	if (scrollSave) {
		$("input[name='_p']").val(parseInt($(window).scrollTop()).toString(36)).prop("disabled", false);
	}
	
	$("input").each(function() {
		if ($(this).attr("post-only") && $(this).attr("post-only") != action) {
			$(this).val("");
		}
	});
	
	if ($("input[name='tab']").val() == "bulk") {
		$("input[name='bulkFile']").prop("disabled", false);
		document.personForm.enctype = "multipart/form-data";
	} else {
		$("input[name='bulkFile']").prop("disabled", true);
		document.personForm.enctype = "";
	}
	
	document.personForm.method = method;
	document.personForm.action = action;
	document.personForm.submit();
}


// 人物の反映を開始。
var _processingRegistPersonDevice;
var _processingRegistPersonDeviceQueue;
var _processingRegistPersonCode;
var _processingRegistType;
var _processingRegistCompleteCallback;
var _processingRegistCloseCallback;

function registDevicePersonBegin(type, personCode, devices /* [{ id: xxx, name: xxx },{ id: xxx, name: xxx }] */, processingRegistCompleteCallback, isContinue, processingRegistCloseCallback) {
	
	var title = "";
	if (type == "new") title = "ユーザー登録";
	if (type == "mod") title = "ユーザーの変更";
	if (type == "del") title = "ユーザーの削除";
	
	if (isContinue) {
		$("#modal_message .modal_msg_title").text(title);
	} else {
		showModal(title, $("#divicePersonRegistModalTemplate").html());
	}

	_processingRegistPersonCode = personCode;
	_processingRegistType = type;
	_processingRegistPersonDeviceQueue = devices;
	_processingRegistCompleteCallback = processingRegistCompleteCallback;
	_processingRegistCloseCallback = processingRegistCloseCallback;
	registDevicePersonNext();
}

// 人物の反映をリトライ。
function registDevicePersonRetry() {
	_processingRegistPersonDeviceQueue.unshift(_processingRegistPersonDevice);
	registDevicePersonNext();
}

// 次のデバイスの人物の反映を実行。
function registDevicePersonNext() {

	$("#modal_message .diviceConnectModal_loading").show();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_complete").hide();

	var device = _processingRegistPersonDeviceQueue.shift();

	if (device == null) {
		// 全て終了。
		if ($("#modal_message .diviceConnectModal_error_names .error_device").length) {
			$("#modal_message .diviceConnectModal_error_names_area").show();
		}
		
		$("#modal_message .diviceConnectModal_loading").hide();
		$("#modal_message .diviceConnectModal_error").hide();
		$("#modal_message .diviceConnectModal_complete").show();
		
		if (_processingRegistCompleteCallback) _processingRegistCompleteCallback();
		return;
	}

	_processingRegistPersonDevice = device;
	
	// 次のデバイスへ。
	$("#modal_message .diviceConnectModal_device_name").text(device.name).hide().fadeIn(200);
	
	
	doAjax("./registPersonForDevice", { device_id: device.id, type: _processingRegistType, person_code: _processingRegistPersonCode }, function(data) {
		
		var $successArea = $("#modal_message .diviceConnectModal_success_names");
		var $errorArea   = $("#modal_message .diviceConnectModal_error_names");
		
		// エラー
		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			$("#modal_message .diviceConnectModal_error_area").text(data.error);
			
			if (_processingRegistType == "del" && _processingRegistPersonDeviceQueue.length == 0) {
				$(".next_device_label").text("構わずにクラウドサーバ上からデータ削除").css("transition", "0s").css("font-size", "1vw").css("width", "800px");
			}
			
			
			$errorArea.find(".device_" +  device.id).remove();
			$errorArea.append($("<span style='margin:0 0.5em' class='error_device device_" + device.id + "'></span>").text(device.name));
			return;
		}

		// 成功
		$errorArea.find(".device_" +  device.id).remove();
		$successArea.append($("<span style='margin:0 0.5em' class='device_" + device.id + "'></span>").text(device.name));
		registDevicePersonNext();
	});
	
}

// 人物の反映ダイアログを閉じる
function registDevicePersonRemoveModal() {
	
	if (_processingRegistCloseCallback) {
		_processingRegistCloseCallback();
	} else {
		removeModal();
	}
}

$(function() {

	// 人物画像の参照リンク
	$(".person_picture_view").each(function() {
		
/*
		$(this).hover(function() {

			$(".person_picture_box").remove();
			
			var $div = $("<div class='person_picture_box'></div>");
//			$div.css("cursor", "default");
//			$div.css("background-image", "url('" + $(this).attr("person-picture-url") + "')")
			$div.append("<img src='" + $(this).attr("person-picture-url") + "'>");
			
			$div.hide();
//			$div.click(function(e) { e.stopPropagation(); });
			$div.hover(function(e) { e.stopPropagation(); }, function() {});
			
			$(this).append($div);
			
			$(".person_picture_box").fadeIn(200);
			
		}, function() {
			// 
		}).click(function() {
			$(".person_picture_box").fadeOut(200, function() {
				$(this).remove();
			});
	
		});
*/

		$(this).click(function() {

			if ($(this).hasClass("picture_show")) {
				$(this).removeClass("picture_show");
				$(".person_picture_box").fadeOut(200, function() {
					$(this).remove();
				});
				return;
			}

			$(".person_picture_box").remove();
			
			var $div = $("<div class='person_picture_box'></div>");
			$div.append("<img src='" + $(this).attr("person-picture-url") + "'>");
			$div.hide();
			$(this).append($div);
			$(".person_picture_box").fadeIn(200);
			
			$(this).addClass("picture_show");
			
		});

		
	});

	// add-start founder feihan
	$(".txt-nowrap").click(function () {
		$(this).toggleClass('nowrap');
	});
	// add-end founder feihan
});


<?php echo '</script'; ?>
>

<!-- デバイス通信ダイアログ(ドアの一時開錠) -->
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

<!-- デバイス通信ダイアログ(人物の登録) -->
<div id="divicePersonRegistModalTemplate" style="display:none">

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
			<a href="javascript:void(0);" onclick="registDevicePersonRetry()" class="btn btn_red">リトライする</a>
			<a href="javascript:void(0);" onclick="registDevicePersonNext()" class="btn btn_red next_device_label">次のカメラへ</a>
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
			<a href="javascript:void(0);" onclick="registDevicePersonRemoveModal()" class="btn btn_gray">閉じる</a>
		</div>
	</div>
	
</div>

<form name="personForm" action="./" method="post">
	<input type="hidden" name="_form_session_key" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['_form_session_key'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
	<input type="hidden" name="tab" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
	<input type="hidden" name="_p" />
		<div class="tab_container">

		<ul class="tab_btn">
			<?php if (empty((($tmp = $_smarty_tpl->tpl_vars['form']->value['tab'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))) {?>
				<?php $_smarty_tpl->_assignInScope('tabPersonArray', array("insert","list","bulk","export","trans"));?>
				<?php $_tmp_array = isset($_smarty_tpl->tpl_vars['form']) ? $_smarty_tpl->tpl_vars['form']->value : array();
if (!(is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess)) {
settype($_tmp_array, 'array');
}
$_tmp_array['tab'] = $_smarty_tpl->tpl_vars['tabPersonArray']->value[array_search(true,$_smarty_tpl->tpl_vars['personTopMenuFlag']->value)];
$_smarty_tpl->_assignInScope('form', $_tmp_array);?>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[0]) {?>
			<li tab-name="insert" <?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "insert") {?>class="active"<?php }?>>新規ユーザー登録</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[1]) {?>
			<li tab-name="list"   <?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "list") {?>class="active"<?php }?>>ユーザー情報一覧・変更</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[2]) {?>
			<li tab-name="bulk"   <?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "bulk") {?>class="active"<?php }?>>一括ユーザー登録</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[3]) {?>
			<li tab-name="export" <?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "export") {?>class="active"<?php }?>>ユーザーデータのエクスポート</li>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[4]) {?>
			<li tab-name="trans"  <?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "trans") {?>class="active"<?php }?>>カメラデータ移行・当て変え</li>
			<?php }?>
		</ul>

		<div class="tab_cnt_wrap">
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[0]) {?>
			<div class="tab_cnt<?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "insert") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab10_new.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[1]) {?>
			<div class="tab_cnt<?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "list") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab20_list.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[2]) {?>
			<div class="tab_cnt<?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "bulk") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab30_bulk.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[3]) {?>
			<div class="tab_cnt<?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "export") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab40_export.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[4]) {?>
			<div class="tab_cnt<?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "trans") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab50_trans.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
			<?php }?>
		</div>

	</div>
	</form>

<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
