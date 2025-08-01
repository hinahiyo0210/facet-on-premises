<?php
/* Smarty version 4.5.3, created on 2025-08-01 16:13:09
  from '/var/www/html/ui1/_pg/app/monitor/monitor.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688c690575aec3_72167631',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '16e835e3b4b076b0107d7685227be50d55e65a96' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/monitor/monitor.tpl',
      1 => 1754032373,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:../_inc/pager_log.tpl' => 2,
    'file:../_inc/log_item_view_1_monitor.tpl' => 1,
    'file:../_inc/log_item_view_2_tr.tpl' => 1,
  ),
),false)) {
function content_688c690575aec3_72167631 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "リアルタイムモニタ");
$_smarty_tpl->_assignInScope('icon', "fa-video");
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

// --------------- システム設定の反映
$(function() {
		$("#m1").fSelect();
	$("#m2").fSelect();

	$("#m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1) {
					return
				}
				
				$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
				
			})
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal()
		})
	});
	});


// デバイスIDに該当するカメラの最新情報を取得し、表示。
function dispDeviceLog(deviecId) {

	doAjax("./getLatestLog?view=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['view'] ));?>
&device_id=" + deviecId, {}, function(data) {


		<?php if ($_smarty_tpl->tpl_vars['form']->value['view'] == "1") {?>

			$(".device_" + deviecId).replaceWith(data.view_1);
			$(".device_" + deviecId).addClass("monitor_push")
			setTimeout(function() {
				$(".device_" + deviecId).removeClass("monitor_push")
			}, 100);

		<?php } else { ?>

			$(".log_table .device_" + deviecId).replaceWith(data.view_2_tr);
			$(".log_table_imglist .device_" + deviecId).replaceWith(data.view_2_li);

			$(".log_table .device_" + deviecId).addClass("monitor_push")
			$(".log_table_imglist .device_" + deviecId).addClass("monitor_push")
			setTimeout(function() {
				$(".log_table .device_" + deviecId).removeClass("monitor_push")
				$(".log_table_imglist .device_" + deviecId).removeClass("monitor_push")
			}, 100);


			// ログテーブルクリック時。
			$(".recog_row").unbind().click(function() {
								// $(".recog_row.current").removeClass("current");
				//
				// var rid = $(this).attr("rid");
				// $(".r_" + rid).addClass("current");
							});


		<?php }?>

	});

}

$(function() {

	// 最新を取得。
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
		dispDeviceLog(<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
);
	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

	// 通知接続。
	<?php if (empty($_smarty_tpl->tpl_vars['wsAddr']->value)) {?> return; <?php }?>

	var ws = new WebSocket("<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['wsAddr']->value ));?>
");
	var errored = false;
	var init = true;

	// 接続が開いたときのイベント
	ws.addEventListener("open", function(event) {
	    console.log('Open server ', event);

		init = false;

		$(".monitor_loader_msg").text("モニターが開始されました。");
		$(".monitor_loader .loader").hide();
		setTimeout(function() {
			$(".monitor_loader").fadeOut(5000);
		});

	});

	// メッセージの待ち受け
	ws.addEventListener('message', function(event) {
	    console.log('Message from server ', event);

	    var deviceId = event.data;
	    dispDeviceLog(deviceId);

	});


	// エラー時
	ws.addEventListener("error", function(event) {
		console.log('WebSocket error: ', event);

		$(".monitor_loader").hide();
		if (init) {
			showModal("エラー", $("#monitorErrorModal").html());
			errored = true;
		}

	});

	// 切断時。
	ws.addEventListener("close", function(event) {
		console.log('WebSocket close ', event);

		$(".monitor_loader .loader").hide();
		$(".monitor_loader").hide();;

		if (errored) return;

		showModal("モニター切断", $("#monitorCloseModal").html());

	});

});

// ドアの一時開錠。
function doDeviceOpenOnce(deviceId, deviceName) {

	showModal("ドアの一時開錠", $("#diviceOpenOnceModalTemplate").html());
	$("#modal_message .diviceConnectModal_confirm").show();
	$("#modal_message .diviceConnectModal_loading").hide();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_complete").hide();
	$("#modal_message .diviceConnectModal_loading").data('deviceId',deviceId);

	$("#modal_message .diviceConnectModal_device_name").text(deviceName);


}
function openOnce() {

	let deviceId = $("#modal_message .diviceConnectModal_loading").data('deviceId');

	$("#modal_message .diviceConnectModal_confirm").hide();
	$("#modal_message .diviceConnectModal_loading").show();

	doAjax("./doDeviceOpenOnce", { device_id: deviceId }, function(data) {

		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			data.error!==true && $("#modal_message .diviceConnectModal_error_msg").append(escapeHtml(data.error));
		} else {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_complete").show();
		}

	});

}

// 人物画像の参照リンク
function showPic(el){
	if ($(el).hasClass("picture_show")) {
		$(el).removeClass("picture_show");
		$(".person_picture_box").fadeOut(200, function() {
			$(this).remove();
		});
		return;
	}

	$(".person_picture_box").remove();

	var $div = $("<div class='person_picture_box' style='background-color: white;'></div>");
	$div.append("<div><img src='" + $(el).attr("person-picture-url") + "'></div>");
	var detail = $(el).attr("recog-log-detail");
	$div.append('<span>詳細情報：'+detail+'</span>');
	$div.hide();
	$(el).append($div);
	$(".person_picture_box").fadeIn(200);

	$(el).addClass("picture_show");
}

// 検索条件をリセット
function reset(){
	// グループ選択を初期リセット
	if(groupsInit.length>0) {
		const $groupSelect = $("#m1");
		$groupSelect.empty();
		groupsInit.forEach(group => {
			
			$groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
			
		});
		$groupSelect.data('fSelect').destroy();
		$groupSelect.data('fSelect').create();
	}
	// カメラ選択を初期リセット
	const $deviceSelect = $("#m2");
	$deviceSelect.empty();
	devices.forEach(device=>{
		
		$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
		
	});
	$deviceSelect.data('fSelect').destroy();
	$deviceSelect.data('fSelect').create();
}

// 検索実行
function resultSearch() {
	if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
		const $session_key = $('input[name="_form_session_key"]');
		if ($('#m1').length) {
			var data = [$('#m1'), $('#m2')].map($item => {
				return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
			});
		} else {
			var data = [$('#m2')].map($item => {
				return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
			});
		}
		doAjax('../session/setSession', {
			session_key: $session_key.val(),
			value: JSON.stringify(data)
		}, (res) => {
			if (!res.error) {
				$session_key.val(res['session_key']);
				$('input[name="searchInit"]').val(1);
				document.searchForm.submit();
			} else {
				alert(JSON.stringify(res));
			}
		}, (errorRes) => {
			alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
		});
	}
}
<?php echo '</script'; ?>
>

<!-- デバイス通信ダイアログ(ドアの一時開錠) -->
<div id="diviceOpenOnceModalTemplate" style="display:none">

	<div class="diviceConnectModal_confirm" style="display:none">
		<div style="height: 150px;">
			カメラ名：「<span class="diviceConnectModal_device_name"></span>」のドアを一時的に開錠します。よろしいですか？
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray" >閉じる</a>
			<a href="javascript:void(0);" onclick="openOnce()"  class="btn btn_red">開錠する</a>
		</div>
	</div>

	<div class="diviceConnectModal_loading">
		ただいま[<span class="diviceConnectModal_device_name"></span>]への通信を行っています。<br />
		通信状況によっては時間がかかる場合があります。<br />
		このままでお待ちください。
		<div id="loading" class="loader">Loading...</div>
	</div>

	<div class="diviceConnectModal_error" style="display:none">
		<div class="diviceConnectModal_error_msg">
			カメラ名：[<span class="diviceConnectModal_device_name"></span>]への通信中にエラーが発生しました。<br />
			<br />
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>

	</div>

	<div class="diviceConnectModal_complete">
		<div class="openOnce_complete">
			カメラ名：[<span class="diviceConnectModal_device_name"></span>]のドアを開錠しました。
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>
	</div>

</div>


<!-- 切断ダイアログ -->
<div id="monitorCloseModal" style="display:none">

	<div class="monitorModal_error_msg">
		モニターが切断されました。<br />
		長時間のモニターを行っている場合や、無線ＬＡＮ環境などの通信状況が不安定な場合、モニターの切断が発生する場合があります。<br />
		再読み込みを行う事で、再度モニターが出来るようになります。<br />
		<br />
	</div>
	<div class="btns">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		<a href="javascript:void(0);" onclick="location.reload()" class="btn btn_red">再読み込みを行う</a>
	</div>

</div>

<!-- 通信エラーダイアログ -->
<div id="monitorErrorModal" style="display:none">

	<div class="monitorModal_error_msg">
		モニターを開始する事が出来ませんでした。<br />
		<br />
	</div>
	<div class="btn_1">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
	</div>

</div>

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

<form action="./" name="searchForm">
	<input type="hidden" name="_form_session_key" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['_form_session_key'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
	<input type="hidden" name="view" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['view'] ));?>
">
	<input type="hidden" name="limit" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['limit'] ));?>
">
	<input type="hidden" name="pageNo" value="1">
	<input type="hidden" name="searchInit" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['searchInit'] ));?>
" />

	<div class="monitor_loader">
		<div class="monitor_loader_inner">
			<span class="monitor_loader_msg">準備中です...</span>
			<div class="loader"></div>
		</div>
	</div>

	<div class="setting_area">
		<input type="hidden" name="view" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['view'] ));?>
">
				<?php $_smarty_tpl->_assignInScope('devicesDisplay', array());?>
		<?php if (empty(Session::getLoginUser("group_id"))) {?>
						<div class="setting_cnt">
				<p class="tit">グループ選択</p>
								<select id="m1" class="groups hidden" name="group_ids[]" multiple="multiple" disabled="disabled"> 					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
						<?php $_smarty_tpl->_assignInScope('selected', '');?>
						<?php if (exists($_smarty_tpl->tpl_vars['form']->value["group_ids"],$_smarty_tpl->tpl_vars['g']->value)) {?>
							<?php $_smarty_tpl->_assignInScope('selected', "selected");?>
							<?php $_smarty_tpl->_assignInScope('devicesDisplay', array_merge($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['group']->value['deviceIds']));?>
						<?php }?>
						<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value ));?>
" <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['selected']->value ));?>
><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group']->value['group_name'] ));?>
</option>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</select>
							</div>
					<?php } else { ?>
			<?php $_smarty_tpl->_assignInScope('devicesDisplay', array_keys($_smarty_tpl->tpl_vars['devices']->value));?>
		<?php }?>
						<div class="setting_cnt">
			<p class="tit">カメラ選択</p>
			<select id="m2" class="devices hidden" name="device_ids[]" multiple="multiple" disabled="disabled"> 				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
					<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
						<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists($_smarty_tpl->tpl_vars['form']->value["device_ids"],$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
					<?php }?>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</select>
		</div>
		<div class="log_btn_wrap">
			<a href="javascript:void(0)" onclick="resultSearch()" class="btn_log_search">表示</a>
			<a href="javascript:void(0)" onclick="reset()" value="Reset" id="resetBtn" class="btn_blue">検索条件をリセット</a>
		</div>
			</div>


	<div class="log_wrap monitor_wrap">

		<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_log.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

		<?php if ($_smarty_tpl->tpl_vars['form']->value['view'] == 1) {?>
			<ul class="log_list">
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
										<?php $_smarty_tpl->_subTemplateRender("file:../_inc/log_item_view_1_monitor.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
									<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</ul>
		<?php }?>

		<?php if ($_smarty_tpl->tpl_vars['form']->value['view'] == 2) {?>
			<div class="log_table_wrap">
				<table class="log_table">
					<tr>
												<th <?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>style="width: 7%"<?php } else { ?>style="width: 10%"<?php }?>>日時</th>
						<th <?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>style="width: 6%"<?php } else { ?>style="width: 8%"<?php }?>>カメラグループ</th>
						<th <?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>style="width: 8%"<?php } else { ?>style="width: 11%"<?php }?>>カメラ</th>
						<th style="width: 7%">認証方式</th>
						<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
							<th style="width: 6%">状態</th>
						<?php }?>
						<th style="width: 6%">ID／ゲスト</th>
						<th style="width: 8%">名前</th>
						<th style="width: 6%">ICカード番号</th>
						<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
							<th style="width: 6%">区分</th>
							<th style="width: 6%"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?></th>
							<th style="width: 6%"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?></th>
						<?php }?>
						<th style="width: 6%">PASS</th>
						<th style="width: 6%">温度</th>
						<th style="width: 6%">マスク</th>
						<th style="width: 6%">スコア</th>
						<?php if ((($tmp = $_smarty_tpl->tpl_vars['recogPassFlags']->value ?? null)===null||$tmp==='' ? false ?? null : $tmp)) {?>
							<th style="width: 6%">勤怠区分</th>
						<?php }?>
						<th style="width: 7%"><?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>画像<?php } else { ?>画像/詳細情報<?php }?></th>
											</tr>
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
						<?php $_smarty_tpl->_subTemplateRender("file:../_inc/log_item_view_2_tr.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</table>
											</div>

		<?php }?>

	</div>

	<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_log.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

</form>


<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
