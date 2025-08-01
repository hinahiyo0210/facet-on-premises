<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:49:40
  from '/var/www/html/ui1/_pg/app/device/tab10_camera.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818324af6096_81583421',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'aeb9310d685813e8a79346c5ce14d1ba3af6bc45' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/device/tab10_camera.tpl',
      1 => 1725436946,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:../_inc/pager_counter_device.tpl' => 2,
  ),
),false)) {
function content_68818324af6096_81583421 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="terminal_cnt">
	<?php echo '<script'; ?>
>
		// システム情報を取得
		function doListFetchFwInfo(serialNo,el) {
			let row = $(el).parents('tr.device_list_row')

			showModal("カメラver番号/型番の読み込み", $("#diviceConfigGetModalTemplate").html())
			$("#modal_message .diviceConnectModal_loading").show()
			$("#modal_message .diviceConnectModal_error").hide()
			$("#modal_message .diviceConnectModal_device_name").text(row.find('td.description').text())

			doAjax("/api1/system/getSystemInfo", { serialNo: serialNo, 'ds-api-token': "<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['api_token'] ));?>
" }, function(data) {

				if (data.error) {
					$("#modal_message .diviceConnectModal_loading").hide()
					$("#modal_message .diviceConnectModal_error").show()
					$("#modal_message .diviceConnectModal_error_msg").append(escapeHtml(data.error))
					return
				}
				row.find('td.device_type').text(data.deviceType)
				row.find('td.fw_ver').text(data.softwareVersion)
				row.find('td.last_get_systemInfo').text(data.lastGetSystemInfo)

				if (document.getElementById('connect_status') != null) {
					row.find('td.connect_status').find('i.fas').removeClass(row.find('td.connect_status').find('i.fas').attr('class')).addClass('fas fa-check-circle').css('color','green')
				}

				removeModal()
			})

		}
		// カメラの変更。
		function doListModDevice(deviceId) {
			$('#list_mod_init_device_id').val(deviceId);

			<?php if ((isset($_smarty_tpl->tpl_vars['list_list']->value))) {?>
			$("input[name='list_pageNo']").val("<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['list_pageInfo']->value->getPageNo() ));?>
");
			<?php }?>

			doPost('./modDeviceInit', true);


		}
		// multi select
		$(function() {
			$("#device_search_m1").fSelect();
			$("#device_search_m2").fSelect();

			if($("input[name='device_search_serial_no']").val()){
				$(".fs-label-wrap").addClass("fs-label-wrap_disabled");
			}

			$("#device_search_m1").on('pulldownChange',function () {
				showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
				const $wrap = $(this).closest('.fs-wrap')
				$("#modal_message #groupsChangeModalBtnCancel").click(function () {
					$wrap.fSelectedValues($wrap.data('oldVal'))
					removeModal()
				})

				$("#modal_message #groupsChangeModalBtnOk").click(function () {
					const newVal = $wrap.fSelectedValues()
					$wrap.data('oldVal',newVal)
					const $deviceSelect = $("#device_search_m2")
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
			})

			$("input[name='device_search_serial_no']").blur(function () {
				if($(this).val()){
					cameraPulldownInit()
					$(".fs-label-wrap").addClass("fs-label-wrap_disabled")
				}else{
					$(".fs-label-wrap").removeClass("fs-label-wrap_disabled")
				}
			})

			// グループ選択を初期リセット
			function cameraPulldownInit(){
				// グループ選択を初期リセット
				if(groupsInit.length>0) {
					const $groupSelect = $("#device_search_m1");
					$groupSelect.empty();
					groupsInit.forEach(group => {
						
						$groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
						
					});
					$groupSelect.data('fSelect').destroy();
					$groupSelect.data('fSelect').create();
				}
				// カメラ選択を初期リセット
				const $deviceSelect = $("#device_search_m2");
				$deviceSelect.empty();
				devices.forEach(device=>{
					
					$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
					
				});
				$deviceSelect.data('fSelect').destroy();
				$deviceSelect.data('fSelect').create();
			}
		})
		function doListDevice() {
			if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
				const $session_key = $('input[name="_form_session_key"]')
				const data = [$('#device_search_m1'), $('#device_search_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				})
				doAjax('../session/setSession', {
					session_key: $session_key.val(),
					value: JSON.stringify(data)
				}, (res) => {
					if (!res.error) {
						$session_key.val(res['session_key'])
						$('input[name="device_search_search_init"]').val(1)
						doGet('./listSearch', true)
					} else {
						alert(JSON.stringify(res))
					}
				}, (errorRes) => {
					alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
				});
			}
		}
	<?php echo '</script'; ?>
>
	<h3 class="tit_cnt_main">カメラ設定</h3>
	<p class="cap_cnt_main">シリアルナンバーに対して、カメラの名称やグループを設定してください。<br>
		複数台の管理の際に、わかりやすい名前をつけることで管理性、操作性が向上します。<br>
		また、カメラのFWバージョン・型番を確認する場合は取得ボタンを押してください。
	</p>


	<h3 class="tit_cnt_main">シリアルNoから検索</h3>
	<br>
	<table class="form_cnt">
		<tr >
			<th>シリアルNo</th>
			<td>
				<input name="device_search_serial_no" type="text" maxlength="11" placeholder="" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['device_search_serial_no'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
			</td>
		</tr>
	</table>
</div>

<div id="registDeviceDescription" class="terminal_cnt">
	<h3 class="tit_cnt_main">グループ・カメラから選択 ※シリアルNoを未入力にする必要があります</h3>
	<br>
	<div class="search_area">
		<table class="form_cnt">
			<tr >
				<th class="fs-select-th-center">グループ選択</th>
				<td>
										<?php $_smarty_tpl->_assignInScope('devicesDisplay', array());?>
					<select id="device_search_m1" class="groups hidden" name="device_search_group_ids[]" multiple="multiple" disabled="disabled"> 						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
							<?php $_smarty_tpl->_assignInScope('selected', '');?>
							<?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["device_search_group_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['g']->value)) {?>
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
			<tr >
				<th class="fs-select-th-center">カメラ選択</th>
				<td>
					<select id="device_search_m2" class="devices hidden" name="device_search_device_ids[]" multiple="multiple" disabled="disabled"> 						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
							<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["device_search_device_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
							<?php }?>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</select>
				</td>
			</tr>
		</table>
		<a href="javascript:void(0)" onclick="doListDevice()" class="enter-submit btn_red"><i class="fas fa-search"></i>検索実行</a>
	</div>
	<input type="hidden" name="_form_session_key" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['_form_session_key'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
	<input type="hidden" id="device_search_search_init" name="device_search_search_init" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['device_search_search_init'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
	<input type="hidden" id="list_mod_init_device_id" name="list_mod_init_device_id" />

	<?php if ((isset($_smarty_tpl->tpl_vars['list_list']->value))) {?>
		<div class="search_results">
			<div class="tit_wrap">
				<h3 class="tit">検索結果</h3>
			</div>

			<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_device.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['list_pageInfo']->value,'topPager'=>true), 0, false);
?>

			<table class="search_results_table">
				<tr>
					<th class="results_oder">No</th>
					<th class="results_id">シリアルNo</th>
					<th class="results_name">カメラグループ</th>
					<th class="results_group">カメラ名称</th>
					<?php if (Session::getLoginUser("apb_mode_flag")) {?>
					<th class="results_group">APB</th>
					<th class="results_group">APB状態</th>
					<?php }?>
					<th class="results_group">型番</th>
					<th class="results_group">バージョン番号</th>
					<th class="results_group">最終取得日時</th>
					<?php if (Session::getLoginUser("getsysteminfo_time")) {?>
					<th class="results_connect">接続状況</th>
					<?php }?>
					<th class="results_btn">ver番号/型番</th>
					<th class="results_btn">編集</th>
				</tr>
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['list_list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
					<tr id="list_user_tr_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
" class="device_list_row">
						<td class="sort_order"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['sort_order'] ));?>
</td>
						<td class="serial_no"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['serial_no'] ));?>
</td>
						<td class="device_group_name"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['device_group_name'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
						<td class="description"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['item']->value['description'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
						<?php if (Session::getLoginUser("apb_mode_flag")) {?>
						<td class="device_apb"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( !empty($_smarty_tpl->tpl_vars['item']->value['apb_group_id']) ? '有効' : '無効' ));?>
</td>
						<td class="device_apb_type"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['apbTypes']->value[$_smarty_tpl->tpl_vars['item']->value['apb_type']] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
						<?php }?>
						<td class="device_type"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_type'] ));?>
</td>
						<td class="fw_ver"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['fw_ver'] ));?>
</td>
						<td class="last_get_systemInfo"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['last_get_systemInfo'] ));?>
</td>
						<?php if (Session::getLoginUser("getsysteminfo_time")) {?>
						<td class="connect_status" id="connect_status" style="font-size:1.5rem;margin:auto 0.8rem;">
							<?php ob_start();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( Session::getLoginUser("getsysteminfo_time") ));
$_prefixVariable1=ob_get_clean();
if (strtotime($_smarty_tpl->tpl_vars['item']->value['last_get_systemInfo']) > strtotime("-".$_prefixVariable1." minute")) {?><i class="fas fa-check-circle" style="color:green;"></i>
							<?php } else { ?><i class="fas fa-times-circle" style="color:red;"></i>
							<?php }?>
						</td>
						<?php }?>
						<td><a class="btn_small" href="javascript:void(0)" onclick="doListFetchFwInfo('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['serial_no'] ));?>
',this); return false">取得</a></td>
						<td><a class="btn_small" href="javascript:void(0)" onclick="doListModDevice('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
'); return false">編集</a></td>
					</tr>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</table>

		</div>

		<?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_device.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['list_pageInfo']->value,'topPager'=>false), 0, true);
?>

	<?php }?>

<!-- ログインユーザー変更モーダル -->
<?php if (!empty($_smarty_tpl->tpl_vars['list_modDevice']->value)) {?>

	<?php echo '<script'; ?>
>
		$(function() {

			let openCallback = function() {

				$("#device_mod_modal_template").remove();

				// プルダウンの初期化
				$("#list_mod_group_id").fSelect();
			};

			let closeCallback = null;
			let noClearError = true;
			let appendTarget = 'form[name="registForm"]';

			showModal("カメラの変更", $("#device_mod_modal_template").html(), "device_mod_modal", openCallback, closeCallback, noClearError, appendTarget);
		});

	<?php echo '</script'; ?>
>
	<div id="device_mod_modal_template" style="display:none">
		<input type="hidden" name="list_mod_back" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_back'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
		<input type="hidden" name="list_pageNo" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['list_pageInfo']->value->getPageNo() ));?>
" />
		<input type="hidden" id="list_mod_device_id" name="list_mod_device_id" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_device_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
		<table class="form_cnt regist_cnt device_manage_modal" style="max-width:80%;">
			<tr>
				<th class="tit_sub">シリアルNo</th>
				<td><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_serial_no'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</td>
			</tr>
			<tr>
				<th class="tit_sub">名称</th>
				<td ><input name="list_mod_description" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_description'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" type="text" placeholder="任意のカメラ名をご入力ください"></td>
			</tr>
			<tr>
				<th class="tit_sub fs-select-th-center">グループ</th>
				<td>
					<select id="list_mod_group_id" class="hidden" name="list_mod_group_id" >
						<option value="">グループを選択</option>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groups']->value, 'g');
$_smarty_tpl->tpl_vars['g']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value) {
$_smarty_tpl->tpl_vars['g']->do_else = false;
?>
							<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value['device_group_id'] ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_group_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['g']->value['device_group_id']) {?> selected <?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value['group_name'] ));?>
</option>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</select>
				</td>
			</tr>
			<tr>
				<th class="tit_sub">No</th>
				<td ><input name="list_mod_sort_order" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_sort_order'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" type="text"></td>
			</tr>
			<tr>
				<th class="tit_sub">PUSH転送先</th>
				<td ><input name="list_mod_push_url" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_push_url'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" type="text"></td>
			</tr>
						<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
				<tr>
					<th>カメラ機能</th>
					<td>
						<p class="select" style="max-width:578px;width:100%;">
							<select name="list_mod_device_role">
								<option value="">&nbsp;</option>
								<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['deviceRoles']->value, 'deviceRole', false, 'device_role');
$_smarty_tpl->tpl_vars['deviceRole']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['device_role']->value => $_smarty_tpl->tpl_vars['deviceRole']->value) {
$_smarty_tpl->tpl_vars['deviceRole']->do_else = false;
?>
									<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_device_role'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['device_role']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device_role']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['deviceRole']->value['device_role_name'] ));?>
</option>
								<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
							</select>
						</p>
					</td>
				</tr>
			<?php }?>
			<?php if (!$_smarty_tpl->tpl_vars['form']->value['enableAws']) {?>
				<tr>
					<th>画像チェックデバイス</th>
					<td>
						<input <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_picture_check_device_flag'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == 1) {?>checked<?php }?> name="list_mod_picture_check_device_flag" id="checkPicture" type="checkbox" value="1"><label for="checkPicture" class="checkbox" style="display:inline"></label>
					</td>
				</tr>
			<?php }?>
						<?php if (Session::getLoginUser("apb_mode_flag")) {?>
				<tr>
					<th class="tit_sub">APB入退室設定</th>
					<td>
						<p class="select" style="width:100%;">
							<select name="list_mod_apb_type">
								<option value="">利用しない</option>
								<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_apb_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == 1) {?>selected<?php }?> value="1">入室用</option>
								<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_apb_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == 2) {?>selected<?php }?> value="2">退室用</option>
								<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_apb_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == 3) {?>selected<?php }?> value="3">入室用(認証時APB制御なし)</option>
							</select>
						</p>
					</td>
				</tr>
								<tr>
					<th class="tit_sub">APB設定</th>
					<td style="padding-bottom:5em;">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['apbGroups']->value, 'g');
$_smarty_tpl->tpl_vars['g']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value) {
$_smarty_tpl->tpl_vars['g']->do_else = false;
?>
							<input type="checkbox" name="list_mod_apb_group_id[]" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value['apb_group_id'] ));?>
" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value['list_mod_apb_group_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['g']->value['apb_group_id'])) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="checkbox">有効</label>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</td>
				</tr>
			<?php }?>
		</table>

		<div class="btns" style="margin-top:2em">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
			<a href="javascript:void(0)" onclick="doPost('./modDevice', false)" class="enter-submit btn btn_red btn_regist" enter-submit-target=".userAuth_mod_modal">設定反映</a>
		</div>

	</div>
<?php }?>
</div>


<?php }
}
