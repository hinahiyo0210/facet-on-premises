<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:38:25
  from '/var/www/html/ui1/_pg/app/person/tab40_export.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818e91394617_62406801',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '43aea818613cc03b73bc6b6153b8b727dd893de2' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/person/tab40_export.tpl',
      1 => 1753320962,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./search_area.tpl' => 1,
    'file:../_inc/pager_counter_person.tpl' => 2,
  ),
),false)) {
function content_68818e91394617_62406801 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

function exportCheckRev(data) {
	
	if (data.export_checkExists) {
		$(".export_btn").removeClass("btn_disabled");
	} else {
		$(".export_btn").addClass("btn_disabled");
	}
}

// 単一のチェック。
function doThisCheck(elm) {
	
	doAjax("./exportCheck", {
		isLocalOnly : "1"
		, isCheckOn : $(elm).prop("checked") ? "1" : "0"
		, checkIds  : $(elm).val()
		, export_checkIdsKey : $("input[name='export_checkIdsKey']").val()
		
	}, exportCheckRev);
} 

// 一覧に出ているものに限り全てチェック。
function doLocalCheckAll(isCheckOn) {
	
	var ids = [];
	$(byNames("export_person_ids[]")).each(function() { 
		ids.push($(this).val()); 
		$(this).prop("checked", isCheckOn);
	});
	
	doAjax("./exportCheck", {
		isLocalOnly : "1"
		, isCheckOn : isCheckOn ? "1" : "0"
		, checkIds  : ids.join(",")
		, export_checkIdsKey : $("input[name='export_checkIdsKey']").val()

	}, exportCheckRev);

	
}

// 一覧に出ていないものを含む全てチェック。
function doServerCheckAll(isCheckOn) {

	$(byNames("export_person_ids[]")).each(function() { 
		$(this).prop("checked", isCheckOn);
	});

	
	doAjax("./exportCheck", {
		isLocalOnly : "0"
		, isCheckOn : isCheckOn ? "1" : "0"
		, export_searchFormKey : "<?php echo hjs((($tmp = $_smarty_tpl->tpl_vars['form']->value['export_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp));?>
"
		, export_checkIdsKey   : $("input[name='export_checkIdsKey']").val()
		
	}, exportCheckRev);

}


// ダウンロード。
function doExportDownload(format) {

	showModal("ダウンロード", $("#export_download_modal_template").html(), null, function() {

		var checkProgress = function () {
			
			var check = function() {
				// mod-start founder feihan
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
				// mod-end founder feihan
			};
			
			setTimeout(check, 500);
			
		}
	
		$(".export_progress").fadeIn(400);
		$(".export_progress progress").attr("max", "100").attr("value", "0");
		location.href = "./exportDownload?export_checkIdsKey=" + $("input[name='export_checkIdsKey']").val() + "&export_format=" + format + "&export_searchFormKey=" + $("input[name='export_searchFormKey']").val();
		checkProgress();

	});

}

// add-start founder feihan
// 検索条件をリセット
function exportSearchInit(){
	$(".export_condition input[type=text]").val('');
	$(".export_condition input[type=checkbox]").prop('checked', false);
	$(".export_condition div.fs-label").text('');
	$(".export_condition div.fs-dropdown div.fs-options div.fs-option").removeClass('selected');
	// グループ選択を初期リセット
	if(groupsInit.length>0) {
		const $groupSelect = $("#export_m1");
		$groupSelect.empty();
		groupsInit.forEach(group => {
			
			$groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
			
		});
		$groupSelect.data('fSelect').destroy();
		$groupSelect.data('fSelect').create();
	}
	// カメラ選択を初期リセット
	const $deviceSelect = $("#export_m2");
	$deviceSelect.empty();
	devices.forEach(device=>{
		
		$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
		
	});
	$deviceSelect.data('fSelect').destroy();
	$deviceSelect.data('fSelect').create();
	$('#export_person_type_code option:first').prop("selected", 'selected');
}
// add-end founder feihan

<?php $_smarty_tpl->_assignInScope('prefix', "export_");?>
function doExportSearchPerson() {
	let exportDropdownIndex;
	$('.fs-dropdown').each(function(index) {
		if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('export') > -1) {
			exportDropdownIndex = index;
			return false;
		} else {
			return true;
		}
	});
	if ((typeof(auth_group) === 'number') || $('.fs-dropdown').eq(exportDropdownIndex).hasClass('hidden')) {
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
				doGet('./exportSearch', true)
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

<input type="hidden" name="export_searchFormKey" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['export_searchFormKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
<input type="hidden" name="export_checkIdsKey" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['export_checkIdsKey'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />

<!-- ダウンロードモーダル -->
<div id="export_download_modal_template" style="display:none">
	ダウンロードデータの作成中です。<br >
	対象件数が多い場合、時間が掛かる場合があります。<br >
	<br />
	<div id="loading" class="loader">Loading...</div>
	<div class="export_progress" style="display:none"><progress style="width: 200px; "></progress></div>
	
</div>


<div class="search_area">
	<?php $_smarty_tpl->_subTemplateRender("file:./search_area.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('prefix'=>"export_"), 0, false);
?>
		<div class="userbtn_wrap">
		<a href="javascript:void(0)" onclick="$(byName('export_pageNo')).val(''); doExportSearchPerson()" class="enter-submit btn_red"><i class="fas fa-search"></i>ユーザーを検索</a>
		<a href="javascript:void(0)" onclick="exportSearchInit()" value="Reset" id="export_ResetBtn" class="btn_blue export_resetBtn">検索条件をリセット</a>
	</div>
	</div>

<?php if ((isset($_smarty_tpl->tpl_vars['export_list']->value))) {?>
	<div class="search_results">
		<div class="tit_wrap">
			<h3 class="tit">検索結果</h3>
			<p class="cap">ユーザーデータをエクセル、CSVでエクスポートすることができます。<br>
				それぞれの形式の「ダウンロード」ボタンを押してください。</p>
		</div>

						<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_person.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['export_pageInfo']->value,'topPager'=>true), 0, false);
?>
				
		この一覧に出ているデータについて
		<a href="javascript:void(0)" onclick="doLocalCheckAll(true)">全て選択</a> / <a href="javascript:void(0)" onclick="doLocalCheckAll(false)">全て解除</a>
		<br />
		全てのデータ（一覧に出ていないデータも含む）について
		<a href="javascript:void(0)" onclick="doServerCheckAll(true)">全て選択</a> / <a href="javascript:void(0)" onclick="doServerCheckAll(false)">全て解除</a>
	
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
			</tr>
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['export_list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
				<tr id="export_person_tr_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
">
					<td><input type="checkbox" name="export_person_ids[]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['person_id'] ));?>
" onclick="doThisCheck(this)" value="user" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if (!empty($_smarty_tpl->tpl_vars['checkIds']->value[$_smarty_tpl->tpl_vars['item']->value['person_id']])) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
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
				</tr>
			<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		</table>
	
	</div>

			<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_person.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['export_pageInfo']->value,'topPager'=>false), 0, true);
?>
	
	<div class="btn_wrap center">
		<a href="javascript:void(0)" onclick="doExportDownload('excel')" class="export_btn <?php if (empty($_smarty_tpl->tpl_vars['checkIds']->value)) {?>btn_disabled<?php }?> btn_blue"><i class="fas fa-arrow-alt-from-top"></i>エクセル形式でダウンロード</a>
		<a href="javascript:void(0)" onclick="doExportDownload('csv')"   class="export_btn <?php if (empty($_smarty_tpl->tpl_vars['checkIds']->value)) {?>btn_disabled<?php }?> btn_blue"><i class="fas fa-arrow-alt-from-top"></i>CSV形式でダウンロード</a>
	</div>
	<div class="btn_wrap" style="margin-top: 10px;">
		<p style="font-size:14px;">※CSVはFaceFCと互換性があるため、FaceFC管理画面でのインポートに利用可能です</p>
	</div>

<?php }
}
}
