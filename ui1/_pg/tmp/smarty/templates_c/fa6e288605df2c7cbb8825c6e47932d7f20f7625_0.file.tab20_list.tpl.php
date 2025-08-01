<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:06:53
  from '/var/www/html/ui1/_pg/app/person/tab20_list.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_6881872d72ba81_54575908',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fa6e288605df2c7cbb8825c6e47932d7f20f7625' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/person/tab20_list.tpl',
      1 => 1753319119,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./search_area.tpl' => 1,
    'file:../_inc/pager_counter_person.tpl' => 2,
  ),
),false)) {
function content_6881872d72ba81_54575908 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

let auth_group = <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( Session::getLoginUser("group_id") ));?>


$(function() {

	// デバイスへの登録を開始。
	<?php if (!empty($_smarty_tpl->tpl_vars['mod_registDevicePersonCode']->value)) {?>
		
		setTimeout(function() {
			registDevicePersonBegin("mod", "<?php echo hjs($_smarty_tpl->tpl_vars['mod_registDevicePersonCode']->value);?>
", <?php echo json_encode($_smarty_tpl->tpl_vars['mod_registDeviceTargets']->value);?>
);
		}, 2000);
		
	<?php }?>

	// デバイスからの削除を開始。
	<?php if (!empty($_smarty_tpl->tpl_vars['del_registDevicePersonCode']->value)) {?>
		
		registDevicePersonBegin("del", "<?php echo hjs($_smarty_tpl->tpl_vars['del_registDevicePersonCode']->value);?>
", <?php echo json_encode($_smarty_tpl->tpl_vars['del_registDeviceTargets']->value);?>
, function() {
			
			var $successArea = $("#modal_message .diviceConnectModal_success_names");
			var $errorArea   = $("#modal_message .diviceConnectModal_error_names");
	
			$successArea.hide();
			$errorArea.hide();
			
			doAjax("./delPersonFromCloud", {
				list_del_personCode: "<?php echo hjs($_smarty_tpl->tpl_vars['del_registDevicePersonCode']->value);?>
"
				
			}, function(data) {
				
				if (data.result == "OK") {
					$successArea.append($("<span style='margin:0 0.5em' class='cloud'></span>").text("クラウドサーバ"));
					
				} else {
					alert(data.msg);
					$errorArea.append($("<span style='margin:0 0.5em' class='cloud'></span>").text("クラウドサーバ"));
					$("#modal_message .diviceConnectModal_error_names_area").show();
				}
				
				$successArea.show();
				$errorArea.show();
			});
			
		}, false, function() {
			location.reload();
			
		});
		
	<?php }?>


});

// 人物の変更。
function doListModPerson(personId) {
	$('#list_modPersonId').val(personId);
	
	<?php if ((isset($_smarty_tpl->tpl_vars['list_list']->value))) {?>
		$("input[name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_pageNo']").val("<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['list_pageInfo']->value->getPageNo() ));?>
");
	<?php }?>

	doPost('./modPersonInit', true);
} 

// 人物の削除。
function doListDeletePerson(personId) {

	$("input[name='list_del_personCode']").val($("#list_person_tr_" + personId + " .personCode").text());
	$("#list_del_personCode").text($("#list_person_tr_" + personId + " .personCode").text());
	$("#list_del_personName").text($("#list_person_tr_" + personId + " .personName").text());
	showModal("ユーザーの削除", $("#person_del_modal_template").html());

}

// 通行可能時間帯設定をクリア。
function clearAccessTimes() {

	$(".acces_times_input").val("");
	$(".acces_times_radio").each(function() {
		$(this).prop("checked", $(this).val() == "");
	});


}

// add-start founder feihan
// 検索条件をリセット
function listSearchInit(){
	$(".list_condition input[type=text]").val('');
	$(".list_condition input[type=checkbox]").prop('checked', false);
	$(".list_condition div.fs-label").text('');
	$(".list_condition div.fs-dropdown div.fs-options div.fs-option").removeClass('selected');
	// グループ選択を初期リセット
	if(groupsInit.length>0) {
		const $groupSelect = $("#list_m1");
		$groupSelect.empty();
		groupsInit.forEach(group => {
			
			$groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
			
		});
		$groupSelect.data('fSelect').destroy();
		$groupSelect.data('fSelect').create();
	}
	// カメラ選択を初期リセット
	const $deviceSelect = $("#list_m2");
	$deviceSelect.empty();
	devices.forEach(device=>{
		
		$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
		
	});
	$deviceSelect.data('fSelect').destroy();
	$deviceSelect.data('fSelect').create();
	$('#list_person_type_code option:first').prop("selected", 'selected');
}
// add-end founder feihan
<?php $_smarty_tpl->_assignInScope('prefix', "list_");?>
function doListSearchPerson() {
	let listDropdownIndex;
	$('.fs-dropdown').each(function(index) {
		if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('list') > -1) {
			listDropdownIndex = index;
			return false;
		} else {
			return true;
		}
	});
	if ((typeof(auth_group) === 'number') || ($('.fs-dropdown').eq(listDropdownIndex).length === 0 || $('.fs-dropdown').eq(listDropdownIndex).hasClass('hidden'))) {
		const $session_key = $('input[name="_form_session_key"]')
		if ($('#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1').length) {
			var data = [$('#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1'),$('#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2')].map($item => {
				return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
			})
		} else {
			var data = [$('#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2')].map($item => {
				return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
			})
		}
		doAjax('../session/setSession', {
			session_key: $session_key.val(),
			value: JSON.stringify(data)
		}, (res) => {
			if (!res.error) {
				$session_key.val(res['session_key'])
				$('input[name*="search_init"]').val(null)
				$('input[name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
search_init"]').val(1)
				doGet('./listSearch', true)
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

<input type="hidden" name="list_del_personCode" value="" />
<input type="hidden" name="list_del_back" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_del_back'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />

<!-- 人物削除確認モーダル -->
<div id="person_del_modal_template" style="display:none">
	このユーザーをクラウドシステムとカメラから削除します。<br >
	データは完全に削除されるため、もとに戻す事はできません。<br >
	本当によろしいですか？<br />
	<br />
	ID：<span id="list_del_personCode"></span><br />
	氏名：<span id="list_del_personName"></span><br />
	
	<div class="btns" style="margin-top:2em">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		<a href="javascript:void(0)" onclick="doPost('./startDelPerson', false)" class="enter-submit btn btn_red btn_regist">削除</a>
	</div>
</div>


<!-- 人物変更モーダル -->
<?php if (!empty($_smarty_tpl->tpl_vars['list_modPerson']->value)) {?>

	<?php echo '<script'; ?>
>
		$(function() {
			
			var openCallback = function() { 
			
				$("#person_mod_modal_template").remove();
				setBae64FileInput();
				$("input[name='list_mod_birthday']").flatpickr();
				
			};
			
			var closeCallback = null;
			var noClearError = true;
			var appndTarget = "form[name='personForm']";
			
			showModal("ユーザーの変更", $("#person_mod_modal_template").html(), "person_mod_modal", openCallback, closeCallback, noClearError, appndTarget);
		});
		
		function doChangeAccessTimeDevice(elm) {
			
			$(".access_time_device").hide();

			if ($(elm).val() == "") {
				return;
			}
			
			$(".access_time_device_" + $(elm).val()).fadeIn(200);
		}
		
		function doApbInFlagChane() {
			
			var inFlag = $("[name='list_mod_apb_in_flag']").prop("checked");
			
			// まず全てクリア。
			$(".acces_times_input").val("");
			$(".acces_times_radio").each(function() {
				$(this).prop("checked", $(this).val() == "");
			});
			
			if (inFlag) {
				// 入室中なのであれば、入室デバイスには禁止を。退室デバイスには許可をセットする。
				$(".apb_type_1").each(function() {
					$(this).find(".acces_times_radio[value='0']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_3").each(function() {
					$(this).find(".acces_times_radio[value='0']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_2").each(function() {
					$(this).find(".acces_times_radio[value='1']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});
				
			} else {
				// 退室中なのであれば、入室デバイスには許可を。退室デバイスには禁止をセットする。
				$(".apb_type_1").each(function() {
					$(this).find(".acces_times_radio[value='1']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_3").each(function() {
					$(this).find(".acces_times_radio[value='1']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_2").each(function() {
					$(this).find(".acces_times_radio[value='0']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});
				
			}
			
			
		}


		// 個別当て変えの実行
		function doModPerson() {
			const $session_key = $('input[name="_form_session_key"]')
			const dataObj ={ }
			const $access_times = $('div.session_access_time')
			$access_times.each(function () {
				const $this = $(this)
				const device_id = $this.data('deviceId')

				dataObj['list_mod_access_flag_' + device_id] || (dataObj['list_mod_access_flag_' + device_id] = [])
				dataObj['list_mod_access_flag_' + device_id].push($('input.acces_times_radio:checked',$this).val() || '')

				dataObj['list_mod_access_time_from_' + device_id] || (dataObj['list_mod_access_time_from_' + device_id] = [])
				dataObj['list_mod_access_time_from_' + device_id].push($('input.acces_times_input_from',$this).val() || '')

				dataObj['list_mod_access_time_to_' + device_id] || (dataObj['list_mod_access_time_to_' + device_id] = [])
				dataObj['list_mod_access_time_to_' + device_id].push($('input.acces_times_input_to',$this).val() || '')
			})
			if ($('#list_m1').length) {
				var data = [$('#list_m1'),$('#list_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				});
			} else {
				var data = [$('#list_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				});
			}
			_.forOwn(dataObj,(v,k)=>{
				data.push({ key : k, value : v })
			})
			doAjax('../session/setSession', {
				session_key: $session_key.val(),
				value: JSON.stringify(data)
			}, (res) => {
				if (!res.error) {
					$session_key.val(res['session_key'])
					$('input', $access_times).attr("disabled", "disabled")
					doPost('./modPerson', true)
				} else {
					alert(JSON.stringify(res))
				}
      }, (errorRes) => {
        alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
      });
		}

	<?php echo '</script'; ?>
>

	<div id="person_mod_modal_template" style="display:none">
		<input type="hidden" name="list_mod_back" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['list_mod_back'] ));?>
" />
		<input type="hidden" name="list_pageNo" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['list_pageInfo']->value->getPageNo() ));?>
" />
		<div class="person_mod_modal_inner">
			<table class="form_cnt regist_cnt">
				<tbody>
					<tr>
						<th>ID</th>
						<td>
							<input type="text" name="list_mod_personCode" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['list_mod_personCode'] ));?>
" readonly class="disabled" />
						</td>
					</tr>
					<tr>
						<th>氏名<span class="required">※</span></th>
						<td>
							<input type="text" name="list_mod_personName" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['list_mod_personName'] ));?>
" placeholder="名前を入力します" >
						</td>
					</tr>
					<tr>
						<th>生年月日</th>
						<td>
							<p class="select calendar">
								<i class="fas fa-calendar-week"></i>
								<input type="text" class="flatpickr flatpickr-input" data-position="below" data-allow-input="true" placeholder="1990/01/01" name="list_mod_birthday" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['list_mod_birthday'] ));?>
">
							</p>
						</td>
					</tr>

					<?php $_smarty_tpl->_assignInScope('emptyCnt', 0);?>
					<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);
$_smarty_tpl->tpl_vars['i']->value = 0;
if ($_smarty_tpl->tpl_vars['i']->value < 3) {
for ($_foo=true;$_smarty_tpl->tpl_vars['i']->value < 3; $_smarty_tpl->tpl_vars['i']->value++) {
?>
						<tr class="list_mod_cards" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 1 && Validator::isEmpty((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_card_id'][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))) {?>  <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['emptyCnt']->value++ ));?>
  style="display:none"<?php }?>>
							<th style="vertical-align: middle;padding-bottom: 15px;">カードID</th>
							<td>
								<input type="text" placeholder="カード番号を入力します" name="list_mod_card_id[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php ob_start();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));
$_prefixVariable1 = ob_get_clean();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_card_id'][$_prefixVariable1] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
							</td>
						</tr>
						<tr class="list_mod_cards" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 1 && Validator::isEmpty((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_card_id'][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))) {?>style="display:none"<?php }?>>
							<th style="vertical-align: middle;padding-bottom: 15px;"><span>カード有効期間</span></th>
							<td>
								<div class="period">
									<div class="select calendar">
										<i class="fas fa-calendar-week"></i>
										<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d',strtotime('today -1 month')) ));?>
" name="list_mod_date_from[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php ob_start();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));
$_prefixVariable2 = ob_get_clean();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_date_from'][$_prefixVariable2] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
									</div>
									<span>〜</span>
									<div class="select calendar">
										<i class="fas fa-calendar-week"></i>
										<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d') ));?>
" name="list_mod_date_to[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php ob_start();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));
$_prefixVariable3 = ob_get_clean();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_date_to'][$_prefixVariable3] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
									</div>
								</div>
							</td>
						</tr>
					<?php }
}
?>
					<tr><th></th>
						<td style="float: right;padding-bottom: 0;">
							<?php if ($_smarty_tpl->tpl_vars['emptyCnt']->value > 0) {?><span><a href="javascript:void(0);" onclick="$('.list_mod_cards').not(':visible').slideDown(200); $(this).hide()">全件を表示</a></span><?php }?>
						</td>
					</tr>
										<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
						<tr>
							<th class="fs-select-th-center">区分</th>
							<td>
								<p class="select">
									<select name="list_mod_person_type_code">
										<option value=""></option>
										<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['personTypeList']->value, 'personType', false, 'person_type_code');
$_smarty_tpl->tpl_vars['personType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['person_type_code']->value => $_smarty_tpl->tpl_vars['personType']->value) {
$_smarty_tpl->tpl_vars['personType']->do_else = false;
?>
											<option <?php if ($_smarty_tpl->tpl_vars['form']->value['list_mod_person_type_code'] == $_smarty_tpl->tpl_vars['person_type_code']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['person_type_code']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['personType']->value['person_type_name'] ));?>
</option>
										<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
									</select>
								</p>
							</td>
						</tr>
						<tr>
							<th><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?></th>
							<td>
								<input type="text" name="list_mod_person_description1" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['list_mod_person_description1'] ));?>
" placeholder="<?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?>を入力します" >
							</td>
						</tr>
						<tr>
							<th><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?></th>
							<td>
								<input type="text" name="list_mod_person_description2" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['list_mod_person_description2'] ));?>
" placeholder="<?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?>を入力します" >
							</td>
						</tr>
					<?php }?>
										<tr>
						<th>画像登録</th>
						<td class="btn_file">
							<label for="list_mod_file_up"><i class="fas fa-arrow-to-top"></i>ファイルをアップロード<input type="file" class="base64_picture" img-target="#list_mod_picture_preview" set-target="input[name='list_mod_picture']" id="list_mod_file_up"></label>
							<div class="picture_preview" id="list_mod_picture_preview"></div><input type="hidden" name="list_mod_picture" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['list_mod_picture'] ));?>
" error-target="#list_mod_picture_preview">
							<p class="note">パソコンなどから画像をアップロードすることができます。<br>下記の撮影ガイドを参考に撮影してください。jpg画像が利用できます。</p>
							<a href="/ui1/static/manual/guide.pdf" target="_blank" class="link_pdf"><i class="fas fa-file-alt"></i>登録画像の撮影ガイド（PDF）</a>
						</td>
					</tr>
					<?php if (Session::getLoginUser("apb_mode_flag")) {?>
						<tr>
							<th>APB状態</th>
							<td>
								<input type="checkbox" onclick="doApbInFlagChane()" name="list_mod_apb_in_flag" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_apb_in_flag'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox">入室中（退室可能）</label>
							</td>
						</tr>
					<?php }?>
					<tr>
						<th>通行可能時間帯</th>
						<td class="access_times">
							<p class="select access_time_device_select">
								<select name="list_mod_access_time_device" onchange="doChangeAccessTimeDevice(this)">
									<option value="" >カメラを選択</option>
									<?php if (!empty($_smarty_tpl->tpl_vars['modDevice']->value)) {?>
										<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['modDevice']->value, 'd');
$_smarty_tpl->tpl_vars['d']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value) {
$_smarty_tpl->tpl_vars['d']->do_else = false;
?>
											<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_access_time_device'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['d']->value['device_id']) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['device_id'] ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['name'] ));?>
</option>
										<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
									<?php }?>
								</select>
								
							</p>
							<a id="clearAccessTimeLink" href="javascript:void(0);" onclick="clearAccessTimes()">全ての通行可能時間帯をクリア</a>
								
							
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['modDevice']->value, 'd');
$_smarty_tpl->tpl_vars['d']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value) {
$_smarty_tpl->tpl_vars['d']->do_else = false;
?>
								<div class="access_time_device access_time_device_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['d']->value['device_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
 apb_type_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['d']->value['apb_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_access_time_device'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) != (($tmp = $_smarty_tpl->tpl_vars['d']->value['device_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) || empty($_smarty_tpl->tpl_vars['modDevice']->value)) {?>style="display:none"<?php }?>>
									<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);
$_smarty_tpl->tpl_vars['i']->value = 0;
if ($_smarty_tpl->tpl_vars['i']->value < 10) {
for ($_foo=true;$_smarty_tpl->tpl_vars['i']->value < 10; $_smarty_tpl->tpl_vars['i']->value++) {
?>
										<div class="acces_times_time session_access_time acces_times_time_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['d']->value['device_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" data-device-id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['d']->value['device_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 1 && Validator::isEmpty((($tmp = $_smarty_tpl->tpl_vars['form']->value[$_smarty_tpl->tpl_vars['name']->value][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))) {?>style="display:none"<?php }?>>
											<?php $_smarty_tpl->_assignInScope('name', (isset($_smarty_tpl->tpl_vars['d']->value['device_id'])) ? "list_mod_access_flag_".((string)$_smarty_tpl->tpl_vars['d']->value['device_id']) : "list_mod_access_flag_");?>
											<input <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[$_smarty_tpl->tpl_vars['name']->value][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? null ?? null : $tmp) === null) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" class="acces_times_radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['name']->value ));?>
[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" type="radio" value="" ><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">指定無し</label>
											<input <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[$_smarty_tpl->tpl_vars['name']->value][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? null ?? null : $tmp) === "1") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" class="acces_times_radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['name']->value ));?>
[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" type="radio" value="1"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">通行許可</label>
											<input <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value[$_smarty_tpl->tpl_vars['name']->value][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? null ?? null : $tmp) === "0") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" class="acces_times_radio" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['name']->value ));?>
[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" type="radio" value="0"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">通行不可</label>
											<div class="access_times_items">
												<p class="select calendar">
													<?php $_smarty_tpl->_assignInScope('name', (isset($_smarty_tpl->tpl_vars['d']->value['device_id'])) ? "list_mod_access_time_from_".((string)$_smarty_tpl->tpl_vars['d']->value['device_id']) : "list_mod_access_time_from_");?>
													<i class="fas fa-calendar-week"></i>
													<input type="text" class="flatpickr_time flatpickr-input acces_times_input acces_times_input_from" data-position="below" data-allow-input="true" placeholder="1990/01/01 10:00" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['name']->value ));?>
[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[$_smarty_tpl->tpl_vars['name']->value][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
												</p>
												<div class="kara">～</div>
												<p class="select calendar">
													<?php $_smarty_tpl->_assignInScope('name', (isset($_smarty_tpl->tpl_vars['d']->value['device_id'])) ? "list_mod_access_time_to_".((string)$_smarty_tpl->tpl_vars['d']->value['device_id']) : "list_mod_access_time_to_");?>
													<i class="fas fa-calendar-week"></i>
													<input type="text" class="flatpickr_time flatpickr-input acces_times_input acces_times_input_to" data-position="below" data-allow-input="true" placeholder="1990/01/01 10:00" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['name']->value ));?>
[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[$_smarty_tpl->tpl_vars['name']->value][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
												</p>
											</div>
										</div>
									<?php }
}
?>
									<a href="javascript:void(0);" onclick="$('.acces_times_time_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['d']->value['device_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
').not(':visible').slideDown(200); $(this).hide()">全件を表示</a>
								</div>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</td>
					</tr>
				</tbody>
			</table>

			<div class="btns" style="margin-top:2em">
				<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
				<a href="javascript:void(0)" onclick="doModPerson()" class="enter-submit btn btn_red btn_regist" enter-submit-target=".person_mod_modal">登録</a>
			</div>
			
		</div>
	</div>
<?php }?>		


<div class="search_area">
	<?php $_smarty_tpl->_subTemplateRender("file:./search_area.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('prefix'=>"list_"), 0, false);
?>
		<div class="userbtn_wrap">
		<a href="javascript:void(0)" onclick="doListSearchPerson()" class="enter-submit btn_red"><i class="fas fa-search"></i>ユーザーを検索</a>
		<a href="javascript:void(0)" onclick="listSearchInit()" value="Reset" id="list_ResetBtn" class="btn_blue list_resetBtn" >検索条件をリセット</a>
	</div>
	</div>

<input type="hidden" id="list_modPersonId" name="list_modPersonId" />

<?php if ((isset($_smarty_tpl->tpl_vars['list_list']->value))) {?>
	<div class="search_results">
		<div class="tit_wrap">
			<h3 class="tit">検索結果</h3>
			<p class="cap">
				右側の編集アイコンから、画像、情報の変更、ユーザーの削除が行えます。<br>
				<!--
				左側のチェックボックスにチェックを入れて、「所属データの一括更新」から、ユーザーの所属カメラを一括更新することができます。
				-->
				<?php if ($_smarty_tpl->tpl_vars['personTopMenuFlag']->value[4]) {?>ユーザーの所属カメラを更新するには「<a href="./?tab=trans">カメラデータ移行・当て変え</a>」から行うことができます。<?php }?>
			</p>
		</div>

						<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_person.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['list_pageInfo']->value,'topPager'=>true), 0, false);
?>
				
		<table class="search_results_table">
			<tr>
				<th class="results_group">グループ</th>
				<th class="results_camera">カメラ</th>
				<th class="results_id">ID</th>
				<th class="results_name">氏名</th>
				<?php if (Session::getLoginUser("apb_mode_flag")) {?>
					<th class="results_name">APB状態</th>
				<?php }?>
				<th class="results_cardIDs">ICカード番号</th>
				<th class="results_birthday">生年月日</th>
				<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
				    <th class="results_personType">区分</th>
				    <th class="results_enterExitDescriptionName1"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?></th>
				    <th class="results_enterExitDescriptionName2"><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?></th>
				<?php }?>
				<th class="results_registration_date">登録日</th>
				<th></th>
								<?php if (empty(Session::getLoginUser("group_id"))) {?>
					<?php if (array_search("ユーザーの変更",Session::getUserFunctionAccess("function_name")) > -1 || Session::getLoginUser("user_flag") == 1) {?>
										<th></th>
										<?php }?>

					<?php if (array_search("ユーザーの削除",Session::getUserFunctionAccess("function_name")) > -1 || Session::getLoginUser("user_flag") == 1) {?>
										<th></th>
										<?php }?>
				<?php }?>
							</tr>
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list_list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
				<tr id="list_person_tr_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
">
					<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_group_names'] ));?>
</td>
					<td><div class="txt-nowrap nowrap"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_names'] ));?>
</div></td>
					<td class="personCode"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personCode'] ));?>
</td>
					<td class="personName"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personName'] ));?>
</td>
					<?php if (Session::getLoginUser("apb_mode_flag")) {?>
						<td><?php if ($_smarty_tpl->tpl_vars['item']->value['apb_in_flag']) {?><span style="color:red">入室中（退室可能）</span><?php } else { ?>入室可能<?php }?></td>
					<?php }?>
					<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['cardIDs'] ));?>
</td>
					<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['birthday'] ));?>
</td>
					<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
					    <td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personTypeName'] ));?>
</td>
					    <td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_description1'] ));?>
</td>
					    <td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_description2'] ));?>
</td>
					<?php }?>
					<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatDate($_smarty_tpl->tpl_vars['item']->value['create_time']) ));?>
</td>
					<td><a href="javascript:void(0)" class="person_picture_view" person-picture-url="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['pictureUrl'] ));?>
"><i class="fas fa-portrait"></i></a></td>
										<?php if (empty(Session::getLoginUser("group_id"))) {?>
						<?php if (array_search("ユーザーの変更",Session::getUserFunctionAccess("function_name")) > -1 || Session::getLoginUser("user_flag") == 1) {?>
												<td><a href="javascript:void(0)" onclick="doListModPerson('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
')"><i class="fas fa-edit"></i></a></td>
												<?php }?>

						<?php if (array_search("ユーザーの削除",Session::getUserFunctionAccess("function_name")) > -1 || Session::getLoginUser("user_flag") == 1) {?>
												<td><a href="javascript:void(0)" onclick="doListDeletePerson('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
')"><i class="fas fa-trash-alt"></i></a></td>
												<?php }?>
					<?php }?>
									</tr>
			<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		</table>
	
	</div>

			<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_person.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['list_pageInfo']->value,'topPager'=>false), 0, true);
?>
	
<?php }
}
}
