<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:49:40
  from '/var/www/html/ui1/_pg/app/device/tab40_system1.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818324c31805_08488179',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9a78a89eed18d6895cbab374a1bef95ffff3ae11' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/device/tab40_system1.tpl',
      1 => 1724394314,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68818324c31805_08488179 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

	var _system_config_sets = <?php echo json_encode($_smarty_tpl->tpl_vars['systemConfigSets']->value);?>
;

	$(function() {
		const $select_group = $('select[name="system_config_set_group"]')
		const $select_device = $('select[name="system_config_set_device"]')
		const $devices_init = $select_device.clone()

		// プルダウン初期化
		$select_device.fSelect()

		<?php if (empty(Session::getLoginUser("group_id"))) {?>
		$select_group.fSelect()
		$select_group.on('pulldownChange', function() {
			// データ値の更新
			const $wrap = $(this).closest('.fs-wrap')
			$wrap.data('oldVal', $wrap.fSelectedValues())

			// プルダウン連動
			$($select_device).empty();
			$devices_init.children().each(function() {
				if (!($wrap.fSelectedValues())
						|| !($(this).val())
						|| ($(this).data('group-id') && $(this).data('group-id') == $wrap.fSelectedValues())) {
					$(this).clone().appendTo($select_device)
				}
			})
			$select_device.data('fSelect').destroy()
			$select_device.data('fSelect').create()
		})
		<?php }?>

		// 新規 or データ更新のラジオの同期
		$("input[name='system_config_set_name']").focus(function() {
			$("#system_regist_type_add").prop("checked", true);
		});

		$("select[name='system_config_set_id']").focus(function() {
			$("#system_regist_type_update").prop("checked", true);
		});

		// 選択された設定セットの値をセットする。
		$("select[name='system_config_set_id']").change(function() {
			var config = _system_config_sets[$(this).val()];
			if (config) {
				$("#system_set_delete_btn").show();
				setFormValue($("#system_config_set_area"), config);
			} else {
				$("#system_set_delete_btn").hide();
			}

		})<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_srs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) {?>.change()<?php }?>;


	});

	function deleteSystemConfigSet() {

		if ($("select[name='system_config_set_id']").val() == "") return;

		var $opion = $("select[name='system_config_set_id']").find("option:selected");
		var id = $opion.val();
		var name = $opion.text();

		var msg = "下記の設定セットが削除されます<br />よろしいですか？<br /><br />[" + escapeHtml(name) + "]<br />";
		msg += "<div class=\"btns\">";
		msg += "<a href=\"javascript:void(0);\" onclick=\"removeModal()\" class=\"btn btn_gray\">キャンセル</a>";
		msg += "<a href=\"javascript:void(0);\" onclick=\"doPost('./deletesystemConfigSet?system_config_set_id=" + id + "')\" class=\"btn btn_red\">OK</a>";
		msg += "</div>";
		showModal("削除の確認", msg);

	}

<?php echo '</script'; ?>
>


<div class="terminal_cnt">
	<h2 class="tit_cnt_main">システム設定</h2>
	<p class="cap_cnt_main">端末のボリューム、画面の明るさや日付等、カメラシステムに関する設定を行います。</p>
	<h3 class="tit_cnt_main">基本設定</h3>
	<p class="cap_cnt_main">基本のセットを登録・更新します。基本設定は各カメラに同一の設定を登録することが可能です。</p>
	<table class="form_cnt">
		<tr>
			<th><input <?php if (empty((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_srs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) && (($tmp = $_smarty_tpl->tpl_vars['form']->value['system_regist_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "add") {?>checked<?php }?> id="system_regist_type_add" name="system_regist_type" type="radio" value="add" /><label for="system_regist_type_add" class="radio">新規追加</label></th>
			<td><input name="system_config_set_name" type="text" placeholder="任意の設定名をご入力ください" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['system_config_set_name'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
"></td>
		</tr>
		<?php if (!empty($_smarty_tpl->tpl_vars['systemConfigSets']->value)) {?>
			<?php $_smarty_tpl->_assignInScope('updId', base_convert(Filter::len((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_srs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),10),36,10));?>

			<tr>
				<th><input <?php if (!empty((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_srs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) || (($tmp = $_smarty_tpl->tpl_vars['form']->value['system_regist_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "update") {?>checked<?php }?> id="system_regist_type_update" name="system_regist_type" type="radio" value="update" /><label for="system_regist_type_update" class="radio">データ更新</label></th>
				<td>
					<p class="select">
						<select name="system_config_set_id">
							<option></option>
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['systemConfigSets']->value, 'set');
$_smarty_tpl->tpl_vars['set']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['set']->value) {
$_smarty_tpl->tpl_vars['set']->do_else = false;
?>
								<option <?php if ($_smarty_tpl->tpl_vars['updId']->value == $_smarty_tpl->tpl_vars['set']->value['system_config_set_id'] || (($tmp = $_smarty_tpl->tpl_vars['form']->value['system_config_set_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['set']->value['system_config_set_id']) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['set']->value['system_config_set_id'] ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['set']->value['system_config_set_name'] ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
						<a id="system_set_delete_btn" style="display: none" onclick="deleteSystemConfigSet()" href="javascript:void(0)">×</a>
					</p>
				</td>
			</tr>
		<?php }?>
	</table>
</div>

<div id="system_config_set_area">
	<div class="terminal_cnt set_detail">

		<h3 class="tit_cnt_main">カメラから設定を読み込む</h3>
		<table class="form_cnt set_group">
			<?php if (empty(Session::getLoginUser("group_id"))) {?>
				<tr><th class="fs-select-th-center">グループ選択</th>
					<td>
							<select name="system_config_set_group" class="device_group_select">
								<option value="">&nbsp;</option>
								<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groups']->value, 'g');
$_smarty_tpl->tpl_vars['g']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value) {
$_smarty_tpl->tpl_vars['g']->do_else = false;
?>
									<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value['device_group_id'] ));?>
" device-ids="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['g']->value['deviceIds']) ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value['group_name'] ));?>
</option>
								<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
							</select>
					</td>
				</tr>
			<?php }?>
			<tr><th class="fs-select-th-center">カメラ選択</th>
				<td>
						<select name="system_config_set_device" class="btn_disable_switch" disable-target="#system_config_load_btn">
							<option value="">&nbsp;</option>
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'd');
$_smarty_tpl->tpl_vars['d']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value) {
$_smarty_tpl->tpl_vars['d']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['device_id'] ));?>
" data-group-id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['device_group_id'] ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value['name'] ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
				</td>
			</tr>
		</table>
		<a id="system_config_load_btn" href="javascript:void(0);" onclick="getDeviceConfig('system_config_set_device', 'system_config_set_area')" class="btn_red btn_set btn_disabled">設定を読み込む</a>

		<h3 class="tit_cnt_main">基本設定</h3>
		<table class="form_cnt">
			<tr>
				<th>音声ボリューム：0～100で設定</th>
				<td>
					<input name="deviceAudioVolume" type="number" class="mini_txt" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['deviceAudioVolume'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="100">
				</td>
			</tr>
			<tr>
				<th>画面の明るさ：0～100で設定</th>
				<td>
					<input name="deviceScreenBrightness" type="number" class="mini_txt" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['deviceScreenBrightness'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="100">
				</td>
			</tr>
			<tr>
				<th>LED照明の明るさ：0～100で設定</th>
				<td>
					<input name="deviceLedBrightness" type="number" class="mini_txt" min="0" max="100" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['deviceLedBrightness'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="100">
				</td>
			</tr>
			<tr>
				<th>スクリーンセーバに入る時間：0～86400秒で設定</th>
				<td>
					<input name="deviceWorkstateTime" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['deviceWorkstateTime'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="86400" type="number" class="mini_txt"><span class="supplement">秒</span>
				</td>
			</tr>
			<tr>
				<th>スタンバイに入る時間：0～86400秒で設定</th>
				<td>
					<input name="deviceStandbyTime" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['deviceStandbyTime'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" Bmin="0" max="86400" type="number" class="mini_txt"><span class="supplement">秒</span>
				</td>
			</tr>
      <tr>
				<th>デバイス休止時カード認証機能：有効/無効</th>
				<td>
					<input type="radio" name="hibernateRecogEnable" value="1" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['hibernateRecogEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">有効</label>
					<input type="radio" name="hibernateRecogEnable" value="0" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['hibernateRecogEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">無効</label>
				</td>
			</tr>
      <tr>
				<th>デバイス休止中の表示メッセージ：</th>
				<td>
					<input type="text" name="hibernateTips" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['hibernateTips'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" >
				</td>
			</tr>
		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">日付設定</h3>
		<table class="form_cnt">
			<tr>
				<th>NTP設定：有効/無効</th>
				<td>
					<input type="radio" name="ntpEnable" value="1" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['ntpEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">有効</label>
					<input type="radio" name="ntpEnable" value="0" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['ntpEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">無効</label>
				</td>
			</tr>
			<tr>
				<th>NTPサーバホスト：</th>
				<td>
					<input type="text" name="ntpHostName" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['ntpHostName'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" >
				</td>
			</tr>
			<tr>
				<th>NTPサーバポート：（0～65535,デフォルト:123)</th>
				<td>
					<input type="number" name="ntpPort" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['ntpPort'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" class="mini_txt" min="0" max="65535">
				</td>
			</tr>
			<tr>
				<th>時刻同期間隔：(1～1440分,デフォルト:60分)</th>
				<td>
					<input type="number" name="ntpInterval" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['ntpInterval'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" class="mini_txt" min="1" max="1440"><span class="supplement">分</span>
				</td>
			</tr>
		</table>
	</div>

	<a href="javascript:void(0);" onclick="doPost('./registSystemConfigSet')" class="btn_red">登録</a>

</div>
<?php }
}
