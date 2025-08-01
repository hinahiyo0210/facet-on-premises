<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:09:18
  from '/var/www/html/ui1/_pg/app/log/log.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688187be0eaea7_37576401',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a504f3a9fe2d48980b40fc0cd6903f0373bf6a5c' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/log/log.tpl',
      1 => 1753318906,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:../_inc/pager_counter_log.tpl' => 2,
    'file:../_inc/log_item_view_1.tpl' => 1,
    'file:../_inc/log_item_view_2_tr.tpl' => 1,
  ),
),false)) {
function content_688187be0eaea7_37576401 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "ログ一覧");
$_smarty_tpl->_assignInScope('icon', "fa-list");
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
<style type="text/css">
		<?php if ($_smarty_tpl->tpl_vars['form']->value["log_searchType"] == 1) {?>
		.condition {
			display: none;
		}
	<?php }?>
	</style>
<?php echo '<script'; ?>
>

// --------------- システム設定の反映
$(function() {

	$('#personType').fSelect();
	$('#enterExitType').fSelect();

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

	// 温度とSCOREは小数点を自動補完
	$("input[name='temperature_from'],input[name='temperature_to'],input[name='score_from'],input[name='score_to']").blur(function () {
		if($(this).val()) {
			var num = Number($(this).val());
			if (num <= 0) {
				$(this).val("0.0");
			} else {
				if (num >= 1000) {
					num = num / 10;
				}
				if (num > 100) {
					num = parseInt(num) / 10;
				}
				$(this).val(num.toFixed(1));
			}
		}
	});
	
	$('input[name="presentFlag"]').on('change', function() {
		const $target = $(this).parents('.setting_cnt').nextAll();
		$target.find('input, label').toggleClass('log_input_disabled', this.checked);
		$target.find('.fs-label-wrap').toggleClass('fs-label-wrap_disabled', this.checked);
	}).triggerHandler('change');

	$("input[name='log_searchType']").click(function() {
				if ($("input[name='log_searchType']:checked").val() == "1") {
			$(".condition").fadeOut(400);
		} else {
			$(".condition").fadeIn(400).css('display','flex');
		}
			});
});

<!-- add-start founder feihan -->
// ダウンロード確認画面を表示する。
function showDialog(csvImgType) {
	if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
		$(".loader").hide();
		$(".export_progress").hide();
		$(".btn_gray").removeClass("btn_disabled");
		$(".btn_red").removeClass("btn_disabled");
		showModal("ログ一覧のcsvダウンロード", $("#export_download_modal_template").html(), function() {
		});
		$("#csvImgType").val(csvImgType);
	}
}

// ダウンロード。
function doExportDownload() {
	$(".loader").show();
	$(".btn_gray").addClass("btn_disabled");
	$(".btn_red").addClass("btn_disabled");
	var checkProgress = function () {
		var check = function() {
			doAjax("./exportDownloadCheckProgress", null, function(result) {
				if (result && result.rowCount) {
					$(".export_progress").show();
					$(".export_progress progress").attr("max",   result.rowCount);
					$(".export_progress progress").attr("value", result.processed);
					setTimeout(check, 1500);
				} else {
					if(result && result.fileReadContinue){
						setTimeout(check, 1500);
					}else{
						$(".export_progress progress").attr("max",   "100");
						$(".export_progress progress").attr("value", "100");
						$(".export_progress").fadeOut(200, function() {
							removeModal();
						});
						return;
					}
				}
			});
		};
		setTimeout(check, 500);
	}
	$(".export_progress").fadeIn(400);
	$(".export_progress progress").attr("max", "100").attr("value", "0");
	location.href = "./exportDownload?csvImgType="+ $("#csvImgType").val() +"&export_searchFormKey=" + $("input[name='export_searchFormKey']").val();
	checkProgress();
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
	if ($('select[name="personType"]').length) {
		$('select[name="personType"] option').removeAttr('selected');
		$('select[name="personType"]').val('');
		$('select[name="personType"]').data('fSelect').destroy();
		$('select[name="personType"]').data('fSelect').create();
		$('select[name="enterExitType"] option').removeAttr('selected');
		$('select[name="enterExitType"]').val('');
		$('select[name="enterExitType"]').data('fSelect').destroy();
		$('select[name="enterExitType"]').data('fSelect').create();
	}

	// その他入力項目のリセット
	$('.setting_area input[type="text"]').val('');
	$('.setting_area input[name="date_from"],input[name="date_to"]').each(function() {
		$(this).val($(this).attr('placeholder'));
	});
	$('.setting_area input[type="radio"][value="all"]').click();
	$('.setting_area input[type="checkbox"]').prop('checked', false);
	<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
	$('.setting_area input[name="presentFlag"]').triggerHandler('change');
	<?php }?>
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
	var $div = $('<div class="person_picture_box certain_height" style="background-color: white;"></div>');
	var imgHtml = '<div class="img" style="background-image: url(' + $(el).attr('person-picture-url') + ')"></div>'
	var detailHtml = '<span>詳細情報：' + $(el).attr("recog-log-detail") + '</span>';
	$div.append(imgHtml).append(detailHtml).hide();
	$(el).append($div);
	$(".person_picture_box").fadeIn(200);

	$(el).addClass("picture_show");
}

// 検索実行
function logSearch() {
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

<form action="./" name="searchForm">
	<input type="hidden" name="_form_session_key" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['_form_session_key'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
	<input type="hidden" name="view" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['view'] ));?>
">
	<input type="hidden" name="limit" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['limit'] ));?>
">
	<input type="hidden" name="pageNo" value="1">
	<input type="hidden" name="export_searchFormKey" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['export_searchFormKey'] ));?>
" />
    <input type="hidden" name="searchInit" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['searchInit'] ));?>
" />
		<input type="hidden" name="csvImgType" id="csvImgType" value="" />
	
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
		<div class="setting_cnt">
			<p class="tit">期間選択</p>
			<div class="period">
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr_time" autocomplete="off" data-allow-input="true" data-default-date="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['date_from'] ));?>
" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d H:i',strtotime('now -1 month')) ));?>
" name="date_from" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['date_from'] ));?>
">
				</div>
				<span>〜</span>
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr_time" autocomplete="off" data-allow-input="true" data-default-date="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['date_to'] ));?>
" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d H:i') ));?>
" name="date_to" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['date_to'] ));?>
">
				</div>
			</div>
		</div>
		<div class="setting_cnt">
			<div>
				<input type="radio" name="log_searchType" <?php if ($_smarty_tpl->tpl_vars['form']->value["log_searchType"] == 1) {?>checked<?php }?> value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">詳細条件なし</label>
				<input type="radio" name="log_searchType" <?php if ($_smarty_tpl->tpl_vars['form']->value["log_searchType"] == 2) {?>checked<?php }?> value="2" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">詳細を絞り込む</label>
			</div>
		</div>
		<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
		<div class="setting_cnt condition">
			<p class="tit">区分</p>
			<div>
				<select id="personType" name="personType" no-search="no-search">
					<option value=""<?php if (empty($_smarty_tpl->tpl_vars['form']->value['personType'])) {?> selected<?php }?>>&nbsp;</option>
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['personTypes']->value, 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
						<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
"<?php if ($_smarty_tpl->tpl_vars['form']->value['personType'] === $_smarty_tpl->tpl_vars['k']->value) {?> selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</select>
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?></p>
			<div class="setting_cnt_description">
				<input type="text" name="personDescription1" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['personDescription1'] ));?>
">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?></p>
			<div class="setting_cnt_description">
				<input type="text" name="personDescription2" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['personDescription2'] ));?>
">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">検索時在室者</p>
			<div>
				<input<?php if ($_smarty_tpl->tpl_vars['form']->value['presentFlag'] == "1") {?> checked<?php }?> name="presentFlag" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" type="checkbox" value="1"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox"></label>
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">状態</p>
			<div>
				<select id="enterExitType" name="enterExitType" no-search="no-search">
					<option value=""<?php if (empty($_smarty_tpl->tpl_vars['form']->value['enterExitType'])) {?> selected<?php }?>>&nbsp;</option>
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['enterExitTypes']->value, 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
						<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
"<?php if ($_smarty_tpl->tpl_vars['form']->value['enterExitType'] === $_smarty_tpl->tpl_vars['k']->value) {?> selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</select>
			</div>
		</div>
		<?php }?>
				<div class="setting_cnt condition">
			<p class="tit">ID</p>
			<div>
				<input type="text" name="personCode" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['personCode'] ));?>
">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">氏名</p>
			<div>
				<input type="text" name="personName" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['personName'] ));?>
"">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">ICカード番号</p>
			<div>
				<input type="text" name="cardID" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['cardID'] ));?>
">
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">マスク</p>
			<div>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['mask_type'] == "all") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="mask_type" type="radio" value="all"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">全て</label>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['mask_type'] == "yes") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="mask_type" type="radio" value="yes"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">有り</label>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['mask_type'] == "no") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="mask_type" type="radio" value="no" ><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">無し</label>
			</div>
		</div>

		<div class="setting_cnt condition">
			<p class="tit">PASS</p>
			<div>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['pass_type'] == "all") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="pass_type" type="radio" value="all"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">全て</label>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['pass_type'] == "yes") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="pass_type" type="radio" value="yes"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">PASS</label>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['pass_type'] == "no") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="pass_type" type="radio" value="no" ><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">NO PASS</label>
			</div>
		</div>

		<div class="setting_cnt condition">
			<p class="tit">登録者</p>
			<div>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['guest_type'] == "all") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="guest_type" type="radio" value="all"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">全て</label>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['guest_type'] == "yes") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="guest_type" type="radio" value="yes"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">ゲストのみ</label>
				<input <?php if ($_smarty_tpl->tpl_vars['form']->value['guest_type'] == "no") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="guest_type" type="radio" value="no" ><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">登録者のみ</label>
			</div>
		</div>
		<div class="setting_cnt condition">
			<p class="tit">温度</p>
			<div class="period">
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="temperature_from" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['temperature_from'] ));?>
" maxlength="4">
				<span>〜</span>
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="temperature_to" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['temperature_to'] ));?>
" maxlength="4">
			</div>
			<input type="checkbox" name="noTempOnly" value="1" <?php if ($_smarty_tpl->tpl_vars['form']->value["noTempOnly"] == "1") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label style="margin-left: 30px;" for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox">温度計測なし/失敗のみ表示</label>
		</div>
		<?php if ($_smarty_tpl->tpl_vars['recogPassFlags']->value) {?>
		<div class="setting_cnt condition">
			<p class="tit">勤怠区分</p>
			<div>
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recogPassFlags']->value, 'passFlag');
$_smarty_tpl->tpl_vars['passFlag']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['passFlag']->value) {
$_smarty_tpl->tpl_vars['passFlag']->do_else = false;
?>
					<input type="checkbox" name="pass_flags[]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['passFlag']->value['pass_flag'] ));?>
" <?php if (exists($_smarty_tpl->tpl_vars['form']->value['pass_flags'],$_smarty_tpl->tpl_vars['passFlag']->value['pass_flag'])) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['passFlag']->value['flag_name'] ));?>
</label>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</div>
		</div>
		<?php }?>
		<div class="setting_cnt condition">
			<p class="tit">SCORE</p>
			<div class="period">
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="score_from" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['score_from'] ));?>
" maxlength="4">
				<span>〜</span>
				<input type="text" onkeyup="if(isNaN(value))execCommand('undo')" name="score_to" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['score_to'] ));?>
" maxlength="4">
			</div>
		</div>
				<div class="log_btn_wrap">
			<a href="javascript:void(0)" onclick="logSearch()" class="btn_log_search enter-submit">ログを表示</a>
						<a href="javascript:void(0)" onclick="showDialog(1)" class="export_btn <?php if ($_smarty_tpl->tpl_vars['pageInfo']->value->getRowCount() <= 0) {?>btn_disabled<?php }?> btn_blue" style="font-size: calc(1vw - 0.3vw);"><i class="fas fa-arrow-alt-from-top" ></i>CSV形式（画像あり）でダウンロード</a>
			<a href="javascript:void(0)" onclick="showDialog(0)" class="export_btn <?php if ($_smarty_tpl->tpl_vars['pageInfo']->value->getRowCount() <= 0) {?>btn_disabled<?php }?> btn_blue" style="font-size: calc(1vw - 0.3vw);"><i class="fas fa-arrow-alt-from-top" ></i>CSV形式（画像なし）でダウンロード</a>
						<a href="javascript:void(0)" onclick="reset()" value="Reset" id="resetBtn" class="btn_blue">検索条件をリセット</a>
		</div>
		<div class="log_csv_span">
			<span>※「CSV形式でダウンロード」を実行する場合は、 「ログを表示」で検索結果を絞ってから実行してください。</span>
		</div>
	</div>

	<?php if ($_smarty_tpl->tpl_vars['pageInfo']->value) {?>
	<div class="log_wrap">

						<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_log.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
		
		<?php if ($_smarty_tpl->tpl_vars['form']->value['view'] == 1) {?>
			<ul class="log_list">
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
					<?php $_smarty_tpl->_subTemplateRender("file:../_inc/log_item_view_1.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
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
						<th style="width: 6%">カメラグループ</th>
						<th style="width: 9%">カメラ</th>
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
						<?php if ($_smarty_tpl->tpl_vars['recogPassFlags']->value) {?>
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

			<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_log.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
	
	<?php }?>
</form>

<!-- ダウンロードモーダル -->
<div id="export_download_modal_template" style="display:none">
	<div>
        <?php if ($_smarty_tpl->tpl_vars['pageInfo']->value) {?>
		<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['pageInfo']->value->getRowCount()) ));?>
件のログデータをcsv形式でダウンロードします。<br >
		※対象件数が多い場合は、時間がかかることがあります。<br >
        <?php }?>
	</div>
	<div style="height: 110px">
		<div id="loading" class="loader" style="display:none">>Loading...</div>
		<div class="export_progress" style="display:none"><progress style="width: 200px; "></progress></div>
	</div>
	<div class="dialog_btn_wrap center">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray" >閉じる</a>
		<a href="javascript:void(0);" onclick="doExportDownload()" class="btn btn_red">ダウンロード</a>
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

<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
