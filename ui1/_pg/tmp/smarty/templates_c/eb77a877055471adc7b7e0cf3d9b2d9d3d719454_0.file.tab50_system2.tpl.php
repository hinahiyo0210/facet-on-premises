<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:49:40
  from '/var/www/html/ui1/_pg/app/device/tab50_system2.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818324c6b5d8_56001850',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'eb77a877055471adc7b7e0cf3d9b2d9d3d719454' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/device/tab50_system2.tpl',
      1 => 1725343681,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68818324c6b5d8_56001850 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

// --------------- システム設定の反映
$(function() {
		$("#regist_system_m1").fSelect();
	$("#regist_system_m2").fSelect();

	$("#regist_system_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})

		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#regist_system_m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1) {
					return
				}
				
				$deviceSelect.append(`<option value="${device.id}">${device.name}</option>`)
				
			})
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal()
		})
	});
	var toggleBtn = function() {
		if ($("#regist_system_m2").fSelectedValues().length && $("[name='regist_system_config_set_id']").val()) {
			$("#regist_system_set_btn").removeClass("btn_disabled");
		} else {
			$("#regist_system_set_btn").addClass("btn_disabled");
		}
	};
	$("[name='regist_system_config_set_id']").change(toggleBtn);
	$("#regist_system_m1").change(toggleBtn);
	$("#regist_system_m2").change(toggleBtn);
	
});
// --------------- ファームウェアの更新
$(function() {

		$("#regist_fw_m1").fSelect();
	$("#regist_fw_m2").fSelect();

	$("#regist_fw_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})

		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#regist_fw_m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1 || $("#device_type option:selected").text() !== device.deviceType) {
					return
				}
				
				$deviceSelect.append(`<option value="${device.id}">${device.name}</option>`)
				
			})
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal()
		})
	});
	var toggleBtn = function() {
		if ($("#regist_fw_m2").fSelectedValues().length && $("[name='version_name']").val()) {
			$("#regist_fw_set_btn").removeClass("btn_disabled");
		} else {
			$("#regist_fw_set_btn").addClass("btn_disabled");
		}
	};
	$("[name='version_name']").change(toggleBtn);
	$("#regist_fw_m1").change(toggleBtn);
	$("#regist_fw_m2").change(toggleBtn);
	
		// 型番選択
	var deviceTypeSel = $("#device_type");
	deviceTypeSel.data("last",deviceTypeSel.val()).change(function(){
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		var old = deviceTypeSel.data('last');
		var now = deviceTypeSel.val();
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$('#device_type').find('option[value="'+old+'"]').prop('selected',true);
			removeModal();
		});
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			deviceTypeSel.data('last',now);
			// ファームウェア
			const $versionSelect = $("#version_name");
			$versionSelect.empty();
			$versionSelect.append(`<option value="" selected></option>`);
			firmwareVeresions.filter(fwVersion=> $("#device_type").val()==fwVersion.deviceTypeFlag).forEach(fwVersion=>{
				
				$versionSelect.append(`<option value="${fwVersion.id}">${fwVersion.name}</option>`);
				
			});
			// グループ
			const $groupSelect = $("#regist_fw_m1");
			$groupSelect.empty()
			groupsInit.forEach(group=>{
				var lastGourpId
				devices.forEach(device=>{
					if(lastGourpId !== group.id && (group.id === device.groupId || (device.groupId === "" && group.id==="-1")) && $("#device_type option:selected").text() === device.deviceType){
						
						$groupSelect.append(`<option value="${group.id}">${group.name}</option>`)
						lastGourpId = group.id
						

					}
				})
			})
			if ($groupSelect.length) {
				$groupSelect.data('fSelect').destroy()
				$groupSelect.data('fSelect').create()
			}

			// カメラ
			const $deviceSelect = $("#regist_fw_m2")
			$deviceSelect.empty()
			if (!($groupSelect.length)) {
				devices.forEach(device=>{
					if($("#device_type option:selected").text() === device.deviceType){
						
						$deviceSelect.append(`<option value="${device.id}">${device.name}</option>`)
						
					}
				})
			}
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal();
		});
	});
	});


<?php echo '</script'; ?>
>

<h2 class="tit_cnt_main">システム設定</h2>
<p class="cap_cnt_main">システム基本設定・更新で作成したセットを、カメラに割り当てます。</p>
<div class="tit_wrap">
	<h3 class="tit">システム基本設定選択</h3>
	<p class="cap">設定するセットを選択してください。</p>
</div>
<table class="form_cnt set_group">
	<tr><th>セット選択</th>
		<td>
			<p class="select">
				<select name="regist_system_config_set_id">
					<option value=""></option>
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['systemConfigSets']->value, 'set');
$_smarty_tpl->tpl_vars['set']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['set']->value) {
$_smarty_tpl->tpl_vars['set']->do_else = false;
?>
						<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['regist_system_config_set_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['set']->value['system_config_set_id']) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['set']->value['system_config_set_id'] ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['set']->value['system_config_set_name'] ));?>
</option>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</select>
			</p>
		</td>
	</tr>

	<tr class="allocation"><th class="tit">割当先</th></tr>
		<?php $_smarty_tpl->_assignInScope('devicesDisplay1', array());?>
	<?php if (empty(Session::getLoginUser("group_id"))) {?>
				<tr class="allocation"><th class="fs-select-th-center">グループ選択</th>
			<td>
				<p>
										<select id="regist_system_m1" class="groups hidden" name="regist_system_config_set_group_ids[]" multiple="multiple">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
							<?php $_smarty_tpl->_assignInScope('selected', '');?>
							<?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["regist_system_config_set_group_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['g']->value)) {?>
								<?php $_smarty_tpl->_assignInScope('selected', "selected");?>
								<?php $_smarty_tpl->_assignInScope('devicesDisplay1', array_merge($_smarty_tpl->tpl_vars['devicesDisplay1']->value,$_smarty_tpl->tpl_vars['group']->value['deviceIds']));?>
							<?php }?>
							<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value ));?>
" <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['selected']->value ));?>
><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group']->value['group_name'] ));?>
</option>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</select>
									</p>
			</td>
		</tr>
			<?php } else { ?>
		<?php $_smarty_tpl->_assignInScope('devicesDisplay1', array_keys($_smarty_tpl->tpl_vars['devices']->value));?>
	<?php }?>
		<tr class="allocation"><th class="fs-select-th-center">カメラを選択</th>
		<td>
						<select id="regist_system_m2" class="devices hidden" name="regist_system_set_device_ids[]" multiple="multiple">
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
					<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay1']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
						<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["regist_system_set_device_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
					<?php }?>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</select>
					</td>
	</tr>
</table>
<a id="regist_system_set_btn" href="javascript:void(0);" onclick="registDeviceConfigBegin('regist_system_set_device_ids[]', 'systemConfig', null, 'regist_system_config_set_id')" class="btn_red btn_disabled">カメラへ設定を登録</a>

<?php if (ENABLE_AWS) {?>
	<!-- 仮で入れます。後藤 -->
	<div style="margin-bottom:6em;"></div>
	
	<div class="tit_wrap">
		<h3 class="tit">ファームウェア更新</h3>
		<p class="cap">更新する端末を選択してください。</p>
	</div>
	<table class="form_cnt set_group">
				<tr><th>型番選択</th>
			<td>
				<p class="select">
					<select id="device_type" name="new_device_type_id">
						<option value=""></option>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['deviceTypes']->value, 'deviceType', false, 'device_type_id');
$_smarty_tpl->tpl_vars['deviceType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['device_type_id']->value => $_smarty_tpl->tpl_vars['deviceType']->value) {
$_smarty_tpl->tpl_vars['deviceType']->do_else = false;
?>
							<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['new_device_type_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['device_type_id']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device_type_id']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['deviceType']->value['device_type'] ));?>
</option>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</select>
				</p>
			</td>
		</tr>
				<tr><th>ファームウェア選択</th>
			<td>
				<p class="select">
					<select name="version_name" id="version_name">
						<option value=""></option>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['firmwareVeresionNames']->value, 'firmwareVeresionName', false, 'n');
$_smarty_tpl->tpl_vars['firmwareVeresionName']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['n']->value => $_smarty_tpl->tpl_vars['firmwareVeresionName']->value) {
$_smarty_tpl->tpl_vars['firmwareVeresionName']->do_else = false;
?>
						<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['new_device_type_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['firmwareVeresionName']->value['device_type_flag']) {?>
							<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['version_name'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['n']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['n']->value ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['n']->value ));?>
</option>
						<?php }?>
						<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
					</select>
				</p>
			</td>
		</tr>
	
				<?php $_smarty_tpl->_assignInScope('devicesDisplay2', array());?>
		<?php if (empty(Session::getLoginUser("group_id"))) {?>
						<tr class="allocation"><th class="fs-select-th-center">グループ選択</th>
				<td>
					<p>
																		<select id="regist_fw_m1" class="groups" name="regist_fw_config_set_group_ids[]" multiple="multiple">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
								<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
									<?php if ($_smarty_tpl->tpl_vars['g']->value == (($tmp = $_smarty_tpl->tpl_vars['device']->value['groupId'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) && (($tmp = $_smarty_tpl->tpl_vars['form']->value["new_device_type"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['device']->value['deviceType']) {?>
										<?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["regist_fw_config_set_group_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['g']->value)) {?>
											<?php $_smarty_tpl->_assignInScope('devicesDisplay2', array_merge($_smarty_tpl->tpl_vars['devicesDisplay2']->value,$_smarty_tpl->tpl_vars['group']->value['deviceIds']));?>
										<?php }?>
										<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group']->value['group_name'] ));?>
</option>
									<?php }?>
								<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
											</p>
				</td>
			</tr>
					<?php } else { ?>
			<?php $_smarty_tpl->_assignInScope('devicesDisplay2', array_keys($_smarty_tpl->tpl_vars['devices']->value));?>
		<?php }?>
				<tr class="allocation"><th class="fs-select-th-center">カメラを選択</th>
			<td>
											<select id="regist_fw_m2" class="devices" name="regist_fw_set_device_ids[]" multiple="multiple">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
						<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay2']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
							<?php if (empty(Session::getLoginUser("group_id"))) {?>
							<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" data-device><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>			
							<?php }?>
						<?php }?>
					<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
				</select>
							</td>
		</tr>
	</table>
	
	<a id="regist_fw_set_btn" onclick="registDeviceConfigBegin('regist_fw_set_device_ids[]', 'fw', null, null, 'version_name')" class="btn_red btn_disabled">カメラのファームウェアを更新</a>	
<?php }
}
}
