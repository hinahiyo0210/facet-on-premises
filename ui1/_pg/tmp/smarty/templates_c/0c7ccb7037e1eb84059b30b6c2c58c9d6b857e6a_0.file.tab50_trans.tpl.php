<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:38:25
  from '/var/www/html/ui1/_pg/app/person/tab50_trans.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818e91405183_70138745',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0c7ccb7037e1eb84059b30b6c2c58c9d6b857e6a' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/person/tab50_trans.tpl',
      1 => 1753321023,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./search_area.tpl' => 1,
    'file:../_inc/pager_counter_person.tpl' => 2,
  ),
),false)) {
function content_68818e91405183_70138745 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>


$(function() {

		$("#trans_to_m1").fSelect();
	$("#trans_to_m2").fSelect();
	$("#trans_to_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#trans_to_m2")
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

var _trans_checkCount = <?php if (empty($_smarty_tpl->tpl_vars['trans_checkIds']->value)) {?>0<?php } else {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( count($_smarty_tpl->tpl_vars['trans_checkIds']->value) ));
}?>;

// 移行ボタンのアクティブ切り替え。
function toggleTransBtn() {

	var num = formatNumber(_trans_checkCount);
	$("#trans_checkCount").text((num ? num : 0) + "件");

	if (_trans_checkCount && $("#trans_to_m2").fSelectedValues().length) {
		$(".trans_btn").removeClass("btn_disabled");
	} else {
		$(".trans_btn").addClass("btn_disabled");
	}

}

$(function() {
	$("#trans_to_m1").change(toggleTransBtn);
	$("#trans_to_m2").change(toggleTransBtn);
});

function transCheckRev(data) {

	_trans_checkCount = data.trans_checkCount;
	toggleTransBtn();

}

// 単一のチェック。
function doTransThisCheck(elm) {

	doAjax("./transCheck", {
		isLocalOnly : "1"
		, isCheckOn : $(elm).prop("checked") ? "1" : "0"
		, checkIds  : $(elm).val()
		, trans_checkIdsKey : $("input[name='trans_checkIdsKey']").val()

	}, transCheckRev);

}



// 一覧に出ているものに限り全てチェック。
function doTransLocalCheckAll(isCheckOn) {

	var ids = [];
	$(byNames("trans_person_ids[]")).each(function() {
		ids.push($(this).val());
		$(this).prop("checked", isCheckOn);
	});

	doAjax("./transCheck", {
		isLocalOnly : "1"
		, isCheckOn : isCheckOn ? "1" : "0"
		, checkIds  : ids.join(",")
		, trans_checkIdsKey : $("input[name='trans_checkIdsKey']").val()

	}, transCheckRev);


}

// 一覧に出ていないものを含む全てチェック。
function doTransServerCheckAll(isCheckOn) {

	$(byNames("trans_person_ids[]")).each(function() {
		$(this).prop("checked", isCheckOn);
	});


	doAjax("./transCheck", {
		isLocalOnly : "0"
		, isCheckOn : isCheckOn ? "1" : "0"
		, trans_searchFormKey : "<?php echo hjs((($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp));?>
"
		, trans_checkIdsKey   : $("input[name='trans_checkIdsKey']").val()

	}, transCheckRev);

}

// デバイスへ人物を登録。
var _transTask;
var _transTaskIndex;

function doTrans() {

let transDropdownIndex;
	$('.fs-dropdown').each(function(index) {
		console.log($('.fs-dropdown').eq(index).next().attr('id'))
		if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('trans') > -1) {
			transDropdownIndex = index;
			return false;
		} else {
			return true;
		}
	});
	if ($('.fs-dropdown').eq(transDropdownIndex).hasClass('hidden') && ($('.fs-dropdown').eq(transDropdownIndex+2).length === 0 || $('.fs-dropdown').eq(transDropdownIndex+2).hasClass('hidden'))) {
		// タスクを作成。
		_transTask = [];
		_transTaskIndex = 0;
	
		$("#trans_to_m2").find("option:selected").each(function() {	// デバイスの繰り返し。
	
			var deviceId   = $(this).val();
			var isClear    = $("input[name='trans_clear']:checked").val() == "1";
	
			var task = {};
			task.deviceStatus = 1;	// 1: 未実施, 2: 完全に実施済み, 3: エラーが発生し、スキップした。
			task.deviceId   = $(this).val();
			task.deviceName = $(this).text();
			task.personIdx  = 0;
			task.successCount = 0;
	
			// クリアが指定されていない場合:-1
			// クリアが指定されており、未実施の場合:1
			// クリアが指定されており、実施済みの場合:2
			// クリアが指定されており、エラーでスキップした場合:3
			if (isClear) {
				task.clearStatus = 1;
			} else {
				task.clearStatus = -1;
			}
	
			_transTask.push(task);
		});
	
		$(".trans_loading").hide();
		$(".trans_error").hide();
		$(".trans_complete").hide();
		$(".trans_progress_info").empty();
		$(".trans_progress").hide();
	
		showModal("カメラへユーザを登録", $("#trans_modal_template").html(), null, function() {
			nextTransTask();
		});
	}


}

// 次のタスクを実行する。
function nextTransTask() {

	$(".trans_loading").show();
	$(".trans_error").hide();
	$(".trans_progress_info").empty();
	$(".trans_progress").hide();

	for (var i = 0; i < _transTask.length; i++) {
		var task = _transTask[i];
		_transTaskIndex = i;

		if (task.deviceStatus != 1) {
			// このデバイスは終了している。
			continue;
		}

		$(".trans_progress_device_name").text(task.deviceName);

		if (task.clearStatus == 1) {
			// このデバイスについて、クリアを行う必要がある。
			doTransClear(task.deviceId);
			return;
		}

		// 人物の登録を行う。
		doTransToDevice(task.deviceId, task.personIdx);
		return;
	}

	// ============================= 全てのタスクは終えている。
	$(".trans_loading").hide();
	$(".trans_error").hide();
	$(".trans_complete").show();
	$(".trans_complete_info").empty();

	for (var i = 0; i < _transTask.length; i++) {
		var task = _transTask[i];

		var html = "[" + escapeHtml(task.deviceName) + "]　";
		if (task.clearStatus == 1 || task.clearStatus == 3)  {
			html += "<span style='color:red'>全件削除時にエラー</span>　";
		} else if(task.clearStatus == 2) {
			html += "全件削除を正常に終了　";
		}

		var style = "";
		if (task.successCount != _trans_checkCount) {
			style = "color:red";
		}
		html += "<span style='" + style + "'>登録件数：" + task.successCount + "/" + _trans_checkCount+ "</span>";
		html += "<br />";
		$(".trans_complete_info").append(html);
	}


}

// スキップして次の人物へ。
function skipTransPersonTask() {

	if (_transTask[_transTaskIndex].clearStatus == 1) {
		// 削除中だった場合。
		_transTask[_transTaskIndex].clearStatus = 3;

	} else {
		// 人物登録中だった場合。
		_transTask[_transTaskIndex].personIdx++;

		if (_trans_checkCount <= _transTask[_transTaskIndex].personIdx) {
			// このデバイスは終了。
			_transTask[_transTaskIndex].deviceStatus = 2;
		}
	}


	nextTransTask();
}


// スキップして次のデバイスへ。
function skipTransDeviceTask() {
	_transTask[_transTaskIndex].deviceStatus = 3;
	nextTransTask();
}

// 人物の登録を行う。
function doTransToDevice(deviceId, personIdx) {

	var param = {
		trans_checkIdsKey 	: $("input[name='trans_checkIdsKey']").val()
		, personIdx        	: personIdx
		, device_id  		: deviceId
		, override 			: $("input[name='trans_override']:checked").val() == "1" ? 1 : 0
	};

	$(".trans_progress").show();
	$(".trans_progress progress").attr("max",   _trans_checkCount);
	$(".trans_progress progress").attr("value", personIdx + 1);
	$(".trans_progress_info").text("ユーザーデータを登録中です。" +  formatNumber(personIdx + 1) + " / " + formatNumber(_trans_checkCount));

	doAjax("./transPersonToDevice", param, function(data) {

		if (data.result == "OK") {

			_transTask[_transTaskIndex].successCount++;

			// 成功していれば次のタスクへ。
			_transTask[_transTaskIndex].personIdx++;

			if (_trans_checkCount <= _transTask[_transTaskIndex].personIdx) {
				// このデバイスは終了。
				_transTask[_transTaskIndex].deviceStatus = 2;
			}

			nextTransTask();
			return;
		}

		// エラーを表示。
		dispTransError(data);

	}, function(request, textStatus, errorThrown) {

		// エラーを表示。
		dispTransError({ error: textStatus + " / " + errorThrown });

	});

}


// クリアを行う。
function doTransClear(deviceId) {

	$(".trans_progress").hide();
	$(".trans_progress_info").text("カメラ内の全てのユーザーデータを削除しています。");

	doAjax("./transClearDevice", { device_id: deviceId}, function(data) {

		if (data.result == "OK") {
			// 成功していれば次のタスクへ。
			_transTask[_transTaskIndex].clearStatus = 2;
			nextTransTask();
			return;
		}

		// エラーを表示。
		dispTransError(data);

	}, function(request, textStatus, errorThrown) {

		// エラーを表示。
		dispTransError({ error: textStatus + " / " + errorThrown });

	});


}

// エラーを表示。
function dispTransError(data) {
	$(".trans_loading").hide();
	$(".trans_error").show();
	$(".trans_error_area").text(data.error);


}

// 人物の個別当てかえ。
function doTransModPerson(personId) {
	if ($('.fs-dropdown').eq(6).hasClass('hidden') && $('.fs-dropdown').eq(8).hasClass('hidden')) {
		$('#trans_modPersonId').val(personId);
	
		<?php if ((isset($_smarty_tpl->tpl_vars['trans_list']->value))) {?>
			$("input[name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
_pageNo']").val("<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['trans_pageInfo']->value->getPageNo() ));?>
");
		<?php }?>
	
		doPost('./modTransInit', true);
	}
}

// 上書き指定の表示切替。
$(function() {

	$("input[name='trans_clear']").click(function() {
		var val = $(this).filter(":checked").val();
		if (val == "1") {
			$("#trans_override_tr").fadeOut(200);
		} else {
			$("#trans_override_tr").fadeIn(200);
		}
	});

});

// add-start founder feihan
// 検索条件をリセット
function transSearchInit(){
	$(".trans_condition input[type=text]").val('');
	$(".trans_condition input[type=checkbox]").prop('checked', false);
	$(".trans_condition div.fs-label").text('');
	$(".trans_condition div.fs-dropdown div.fs-options div.fs-option").removeClass('selected');
	// グループ選択を初期リセット
	if(groupsInit.length>0) {
		const $groupSelect = $("#trans_m1");
		$groupSelect.empty();
		groupsInit.forEach(group => {
			
			$groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
			
		});
		$groupSelect.data('fSelect').destroy();
		$groupSelect.data('fSelect').create();
	}
	// カメラ選択を初期リセット
	const $deviceSelect = $("#trans_m2");
	$deviceSelect.empty();
	devices.forEach(device=>{
		
		$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
		
	});
	$deviceSelect.data('fSelect').destroy();
	$deviceSelect.data('fSelect').create();
	$('#trans_person_type_code option:first').prop("selected", 'selected');
}
// add-end founder feihan

<?php $_smarty_tpl->_assignInScope('prefix', "trans_");?>
function doTransSearchPerson() {
	let transDropdownIndex;
	$('.fs-dropdown').each(function(index) {
		console.log($('.fs-dropdown').eq(index).next().attr('id'))
		if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('trans') > -1) {
			transDropdownIndex = index;
			return false;
		} else {
			return true;
		}
	});
	if ($('.fs-dropdown').eq(transDropdownIndex).hasClass('hidden') && ($('.fs-dropdown').eq(transDropdownIndex+2).length === 0 || $('.fs-dropdown').eq(transDropdownIndex+2).hasClass('hidden'))) {
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
				doGet('./transSearch', true)
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

<!-- モーダル -->
<div id="trans_modal_template" style="display:none">

	<div class="trans_loading">
		ただいま[<span class="trans_progress_device_name"></span>]への通信を行っています。<br />
		通信状況によっては時間がかかる場合があります。<br />
		このままでお待ちください。
		<div id="loading" class="loader">Loading...</div>

		<span class="trans_progress_info"></span><span class="trans_progress" style="padding-left:1em; display:none"><progress style="width: 150px; "></progress></span><br />
	</div>

	<div class="trans_error">
		<div class="trans_error_msg">
			<span class="trans_progress_info"></span><span class="trans_progress" style="padding-left:1em; display:none"><progress style="width: 150px; "></progress></span><br />

			[<span class="trans_progress_device_name"></span>]への通信中にエラーが発生しました。<br />
			<br />
			<span class="trans_error_area" style="color:red"></span><br />
			<br />
			<div style="color:#000">
				カメラの電源や、カメラのオンライン状態もご確認下さい。また、ファームウェアバージョンが最新で無い場合にもエラーが出る場合があります。
			</div>
		</div>
		<div class="btn_4">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
			<a href="javascript:void(0);" onclick="nextTransTask()" class="btn btn_red">リトライする</a>
			<a href="javascript:void(0);" onclick="skipTransPersonTask()" class="btn btn_red" style="font-size:0.9vw">次のユーザを処理する</a>
			<a href="javascript:void(0);" onclick="skipTransDeviceTask()" class="btn btn_red" style="font-size:0.9vw">このカメラをスキップ</a>
		</div>

	</div>

	<div class="trans_complete">
		下記の通りに処理が行われました。<br />
		<div class="trans_complete_info"></div>
		<div class="btn_1">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>
	</div>


</div>

<!--  デバイスへの登録を開始。 -->
<?php if (!empty($_smarty_tpl->tpl_vars['transMod_deleteDeviceTargets']->value) || !empty($_smarty_tpl->tpl_vars['transMod_addDeviceTargets']->value)) {?>

	<?php echo '<script'; ?>
>

		$(function() {
			<?php if (!empty($_smarty_tpl->tpl_vars['transMod_deleteDeviceTargets']->value) && !empty($_smarty_tpl->tpl_vars['transMod_addDeviceTargets']->value)) {?>
				registDevicePersonBegin("del", "<?php echo hjs($_smarty_tpl->tpl_vars['transMod_registDevicePersonCode']->value);?>
", <?php echo json_encode($_smarty_tpl->tpl_vars['transMod_deleteDeviceTargets']->value);?>
, function() {
					registDevicePersonBegin("new", "<?php echo hjs($_smarty_tpl->tpl_vars['transMod_registDevicePersonCode']->value);?>
", <?php echo json_encode($_smarty_tpl->tpl_vars['transMod_addDeviceTargets']->value);?>
, function() {
						$("#modal_message .modal_msg_title").text("移行処理の完了");
					}, true, function() {
						location.href = "./transModComplete?trans_mod_back=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_back'] ));?>
";
					});
				});

			<?php } elseif (!empty($_smarty_tpl->tpl_vars['transMod_deleteDeviceTargets']->value)) {?>
				registDevicePersonBegin("del", "<?php echo hjs($_smarty_tpl->tpl_vars['transMod_registDevicePersonCode']->value);?>
", <?php echo json_encode($_smarty_tpl->tpl_vars['transMod_deleteDeviceTargets']->value);?>
, null, false, function() {
					location.href = "./transModComplete?trans_mod_back=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_back'] ));?>
";
				});

			<?php } else { ?>
				registDevicePersonBegin("new", "<?php echo hjs($_smarty_tpl->tpl_vars['transMod_registDevicePersonCode']->value);?>
", <?php echo json_encode($_smarty_tpl->tpl_vars['transMod_addDeviceTargets']->value);?>
, null, false, function() {
					location.href = "./transModComplete?trans_mod_back=<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_back'] ));?>
";
				});
			<?php }?>

		});

	<?php echo '</script'; ?>
>

<?php }?>


<!-- 当てかえ変更モーダル -->
<?php if (!empty($_smarty_tpl->tpl_vars['trans_modPerson']->value)) {?>

	<?php echo '<script'; ?>
>
		var _trans_mod_deviceNames = [];
		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'd');
$_smarty_tpl->tpl_vars['d']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value) {
$_smarty_tpl->tpl_vars['d']->do_else = false;
?>_trans_mod_deviceNames["<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['device_id'] ));?>
"] = "<?php echo hjs($_smarty_tpl->tpl_vars['d']->value['name']);?>
";<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

		var _trans_mod_deleteDevices;
		var _trans_mod_addDevices;


		$(function() {

			var openCallback = function() {

				$("#trans_mod_modal_template").remove();
				setBae64FileInput();
				$("input[name='trans_mod_birthday']").flatpickr();

			};

			var closeCallback = null;
			var noClearError = true;
			var appndTarget = "form[name='personForm']";

			showModal("ユーザーの当て変え", $("#trans_mod_modal_template").html(), "trans_mod_modal", openCallback, closeCallback, noClearError, appndTarget);

			$(".trans_mod_new_device_ids").click(doTransModNewDeviceIdsChange);
			doTransModNewDeviceIdsChange();
		});

		// チェックボックス変更時。
		function doTransModNewDeviceIdsChange() {

			// 現在の設定値。
			var registed = [];
			$(byNames("trans_mod_registed_device_ids[]")).each(function() {
				registed[$(this).val()] = 1;
			});

			// 変更後の設定値と比較。
			var addDevices = [];
			var deleteDevices = [];
			$(byNames("trans_mod_new_device_ids[]")).filter(":checked").each(function() {
				var newVal = $(this).val();
				if (!registed[newVal]) {
					addDevices.push(newVal);
					return;
				}
				registed[newVal] = 0;
			});

			for (var id in registed) {
				if (registed[id]) {
					deleteDevices.push(id);
				}
			}

			// 表示。
			$("#trans_mod_delete_device_names").text(deleteDevices.map(function(id) { return _trans_mod_deviceNames[id]; }).join(" / ")).hide().fadeIn(100);
			$("#trans_mod_add_device_names").text(addDevices.map(function(id) { return _trans_mod_deviceNames[id]; }).join(" / ")).hide().fadeIn(100);

			$("#trans_mod_regist_btn").removeClass("btn_disabled");
			if (deleteDevices.length == 0 && addDevices.length == 0) {
				$("#trans_mod_regist_btn").addClass("btn_disabled");
			}

			_trans_mod_deleteDevices = deleteDevices;
			_trans_mod_addDevices = addDevices;
		}

		// 個別当て変えの実行
		function doTransMod() {
			const $session_key = $('input[name="_form_session_key"]');
			if ($('#trans_m1').length) {
				var data = [$('#trans_m1'),$('#trans_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				});
			} else {
				var data = [$('#trans_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				});
			}
			data.push({ key : "trans_mod_deleteDevices", value : _trans_mod_deleteDevices },
					{ key : "trans_mod_addDevices", value : _trans_mod_addDevices });
			doAjax('../session/setSession', {
				session_key: $session_key.val(),
				value: JSON.stringify(data)
			}, (res) => {
				if (!res.error) {
					$session_key.val(res['session_key']);
					$('input[name="trans_mod_registed_device_ids[]"]').attr("disabled", "disabled");
					$('input[name="trans_mod_new_device_ids[]"]').attr("disabled", "disabled");
					doPost('./modTrans', true);
				} else {
					alert(JSON.stringify(res));
				}
      }, (errorRes) => {
        alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
      });
		}

	<?php echo '</script'; ?>
>

	<div id="trans_mod_modal_template" style="display:none">
		<input type="hidden" name="trans_mod_back" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_back'] ));?>
" />
		<input type="hidden" name="trans_pageNo" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['trans_pageInfo']->value->getPageNo() ));?>
" />
		<div class="trans_mod_modal_inner">
			<table class="form_cnt regist_cnt">
				<tbody>
					<tr>
						<th>ID</th>
						<td>
							<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_personCode'] ));?>

						</td>
					</tr>
					<tr>
						<th>氏名</th>
						<td>
							<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_personName'] ));?>

						</td>
					</tr>
					<tr><th>生年月日</th>
						<td>
							<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_birthday'] ));?>

						</td>
					</tr>
					<tr>
						<th>画像</th>
						<td>
							<img src="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['trans_mod_pictureUrl'] ));?>
" />
						</td>
					</tr>
				</tbody>
			</table>

			<div class="trans_mod_regist_devices">
				<div class="trans_mod_registed">
					【現在】登録カメラ一覧
					<div class="trans_mod_deviceList">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'd');
$_smarty_tpl->tpl_vars['d']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value) {
$_smarty_tpl->tpl_vars['d']->do_else = false;
?>
							<?php if (exists($_smarty_tpl->tpl_vars['form']->value['trans_mod_registed_device_ids'],$_smarty_tpl->tpl_vars['d']->value['device_id'])) {?>
								<div class="trans_mod_deviceItem"><i class="fas fa-check"></i><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['name'] ));?>
</div>
								<input type="hidden" name="trans_mod_registed_device_ids[]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['device_id'] ));?>
" />
							<?php } else { ?>
								<div class="trans_mod_deviceItem"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['name'] ));?>
</div>
							<?php }?>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</div>
				</div>

				<div class="trans_mod_to_regist">
					【新規】登録カメラ一覧
					<div class="trans_mod_deviceList">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'd');
$_smarty_tpl->tpl_vars['d']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value) {
$_smarty_tpl->tpl_vars['d']->do_else = false;
?>
							<div class="trans_mod_deviceItem"><input id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" class="trans_mod_new_device_ids" type="checkbox" name="trans_mod_new_device_ids[]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['device_id'] ));?>
" <?php if (exists($_smarty_tpl->tpl_vars['form']->value['trans_mod_new_device_ids'],$_smarty_tpl->tpl_vars['d']->value['device_id'])) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['name'] ));?>
</label></div>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</div>
				</div>

			</div>

			<div class="trans_mod_process_info_block">
				【実施内容】
				<div class="trans_mod_process_info">
					[カメラから削除　　]<br />
					<span id="trans_mod_delete_device_names"></span><br />
					[カメラへ新たに登録]<br />
					<span id="trans_mod_add_device_names"></span><br />
				</div>
			</div>

			<div class="btns" style="margin-top:2em">
				<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
				<a href="javascript:void(0)" id="trans_mod_regist_btn" onclick="doTransMod()" class="enter-submit btn btn_red btn_regist btn_disabled" enter-submit-target=".person_mod_modal">ユーザーデータを移行</a>
			</div>

		</div>
	</div>


	<div id="trans_mod_params"><!-- jsから挿入 --></div>

<?php }?>

<input type="hidden" id="trans_modPersonId" name="trans_modPersonId" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_modPersonId'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />

<input type="hidden" name="trans_searchFormKey" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
<input type="hidden" name="trans_checkIdsKey" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['trans_checkIdsKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />

<p class="tab_cnt_cap">特定のカメラから他のカメラへデータを移行します。</p>

<div class="search_area">
	<?php $_smarty_tpl->_subTemplateRender("file:./search_area.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('prefix'=>"trans_"), 0, false);
?>
		<div class="userbtn_wrap">
		<a href="javascript:void(0)" onclick="$(byName('trans_pageNo')).val(''); doTransSearchPerson()" class="enter-submit btn_red"><i class="fas fa-search"></i>ユーザーを検索</a>
		<a href="javascript:void(0)" onclick="transSearchInit()" value="Reset" id="trans_ResetBtn" class="btn_blue trans_resetBtn">検索条件をリセット</a>
	</div>
	</div>

<?php if ((isset($_smarty_tpl->tpl_vars['trans_list']->value))) {?>

	<div class="search_results">
		<div class="tit_wrap">
			<h3 class="tit">検索結果</h3>
			<p class="cap">ユーザーデータを他のカメラへインポートすることができます。<br>
				左側のチェックボックスにチェックを入れて、「ユーザーデータを移行」ボタンを押してください。</p>
		</div>

						<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_person.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['trans_pageInfo']->value,'topPager'=>true), 0, false);
?>
		
		この一覧に出ているデータについて
		<a href="javascript:void(0)" onclick="doTransLocalCheckAll(true)">全て選択</a> / <a href="javascript:void(0)" onclick="doTransLocalCheckAll(false)">全て解除</a>
		<br />
		全てのデータ（一覧に出ていないデータも含む）について
		<a href="javascript:void(0)" onclick="doTransServerCheckAll(true)">全て選択</a> / <a href="javascript:void(0)" onclick="doTransServerCheckAll(false)">全て解除</a>

		<table class="search_results_table">
			<tr>
				<th class="results_checkbox"></th>
				<th class="results_group">グループ</th>
				<th class="results_camera">カメラ</th>
				<th class="results_id">ID</th>
				<th class="results_name">氏名</th>
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
								<?php if (array_search("ユーザーの変更",Session::getUserFunctionAccess("function_name")) > -1 || Session::getLoginUser("user_flag") == 1) {?>
								<th></th>
								<?php }?>
							</tr>
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['trans_list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
				<tr id="trans_person_tr_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
">
					<td><input type="checkbox" name="trans_person_ids[]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
" onclick="doTransThisCheck(this)" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if (!empty($_smarty_tpl->tpl_vars['trans_checkIds']->value[$_smarty_tpl->tpl_vars['item']->value['person_id']])) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox"></label></td>
					<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_group_names'] ));?>
</td>
					<td><div class="txt-nowrap nowrap"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_names'] ));?>
</div></td>
					<td class="personCode"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personCode'] ));?>
</td>
					<td class="personName"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['personName'] ));?>
</td>
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
          <?php if (array_search("ユーザーの変更",Session::getUserFunctionAccess("function_name")) > -1 || Session::getLoginUser("user_flag") == 1) {?>
					<td><a href="javascript:void(0)" onclick="doTransModPerson('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
')"><i class="fas fa-edit"></i></a></td>
          <?php }?>
				</tr>
			<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		</table>

	</div>

			<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_person.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['trans_pageInfo']->value,'topPager'=>false), 0, true);
?>
	
	<div class="search_area">
		<table class="form_cnt">
			<tr><th class="tit">選択件数</th>
				<td>
					<span id="trans_checkCount"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber(count($_smarty_tpl->tpl_vars['trans_checkIds']->value)) ));?>
件</span>
				</td>
			</tr>
						<?php $_smarty_tpl->_assignInScope('devicesDisplay', array());?>
			<?php if (empty(Session::getLoginUser("group_id"))) {?>
			<tr><th class="tit fs-select-th-center">カメラグループから検索</th>
				<td colspan="2">
					<select id="trans_to_m1" class="groups" name="trans_to_group_ids[]" multiple="multiple" disabled="disabled"> 						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
							<?php $_smarty_tpl->_assignInScope('selected', '');?>
							<?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["trans_to_group_ids"] ?? null)===null||$tmp==='' ? array() ?? null : $tmp),$_smarty_tpl->tpl_vars['g']->value)) {?>
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
				</td>
			</tr>
			<?php } else { ?>
				<?php $_smarty_tpl->_assignInScope('devicesDisplay', array_keys($_smarty_tpl->tpl_vars['devices']->value));?>
			<?php }?>
			<tr><th class="tit fs-select-th-center">移行先カメラを選択</th>
				<td>
					<select id="trans_to_m2" class="devices" name="trans_to_device_ids[]" multiple="multiple" disabled="disabled"> 						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
							<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists($_smarty_tpl->tpl_vars['form']->value["trans_to_device_ids"],$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
							<?php }?>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</select>
				</td>
			</tr>
						<tr><th class="tit">旧データ</th>
				<td>
					<input type="radio" name="trans_clear" value="0" checked class="no-req" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">消去せずに追加</label>
					<input type="radio" name="trans_clear" value="1" class="no-req" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">全て消去して入れ替え</label>
					<p class="note red">※「全て消去して入れ替え」を選択すると、該当カメラのユーザー情報が全て消去され、設定したユーザー情報のみ登録されます。</p>
				</td>
			</tr>
			<tr id="trans_override_tr"><th class="tit">ID重複データ</th>
				<td>
					<input type="radio" name="trans_override" value="1" checked class="no-req" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">上書き</label>
					<input type="radio" name="trans_override" value="0" class="no-req" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">旧データを使用</label>
				</td>
			</tr>

		</table>
		<a href="javascript:void(0)" onclick="doTrans()"  class="trans_btn btn_disabled btn_red btn_user">ユーザーデータを移行</a>

	</div>

<?php }
}
}
