<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:49:40
  from '/var/www/html/ui1/_pg/app/device/tab20_recog1.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818324bc65c1_91677013',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '89c4fb8e11525cf04586b7f2b35ac13e94a02b95' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/device/tab20_recog1.tpl',
      1 => 1725426989,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68818324bc65c1_91677013 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

	var _recog_config_sets = <?php echo json_encode($_smarty_tpl->tpl_vars['recogConfigSets']->value);?>
;

	$(function() {
		const $select_group = $('select[name="recog_config_set_group"]');
		const $select_device = $('select[name="recog_config_set_device"]');
		const $devices_init = $select_device.clone();

		// プルダウン初期化
		$select_device.fSelect();

		<?php if (empty(Session::getLoginUser("group_id"))) {?>
		$select_group.fSelect()
		$select_group.on('pulldownChange', function() {
			// データ値の更新
			const $wrap = $(this).closest('.fs-wrap')
			$wrap.data('oldVal', $wrap.fSelectedValues())

			// プルダウン連動
			$($select_device).empty()
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
		$("input[name='recog_config_set_name']").focus(function() {
			$("#recog_regist_type_add").prop("checked", true);
		});

		$("select[name='recog_config_set_id']").focus(function() {
			$("#recog_regist_type_update").prop("checked", true);
		});

		// 選択された設定セットの値をセットする。
		$("select[name='recog_config_set_id']").change(function() {
			var config = _recog_config_sets[$(this).val()];
			if (config) {
				$("#recog_set_delete_btn").show();
				setFormValue($("#recog_config_set_area"), config);
			} else {
				$("#recog_set_delete_btn").hide();
			}

		})<?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_rrs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) {?>.change()<?php }?>;

	});

	function deleteRecogConfigSet() {

		if ($("select[name='recog_config_set_id']").val() == "") return;

		var $opion = $("select[name='recog_config_set_id']").find("option:selected");
		var id = $opion.val();
		var name = $opion.text();

		var msg = "下記の設定セットが削除されます<br />よろしいですか？<br /><br />[" + escapeHtml(name) + "]<br />";
		msg += "<div class=\"btns\">";
		msg += "<a href=\"javascript:void(0);\" onclick=\"removeModal()\" class=\"btn btn_gray\">キャンセル</a>";
		msg += "<a href=\"javascript:void(0);\" onclick=\"doPost('./deleteRecogConfigSet?recog_config_set_id=" + id + "')\" class=\"btn btn_red\">OK</a>";
		msg += "</div>";
		showModal("削除の確認", msg);

	}

<?php echo '</script'; ?>
>

<div class="terminal_cnt">
	<h2 class="tit_cnt_main">認証関連設定</h2>
	<p class="cap_cnt_main">顔認証の設定、温度検知設定、アラートの設定など、認証に関する設定を行います。</p>
	<h3 class="tit_cnt_main">基本設定</h3>
	<p class="cap_cnt_main">基本のセットを登録・更新します。基本設定は各カメラに同一の設定を登録することが可能です。<br><span style="font-weight: bold;">※FaceFCのファームウェアバージョンが古いと正しく読み込めない場合があります。最新のファームウェアをご利用ください。</span></p>
	<table class="form_cnt">
		<tr>
			<th><input <?php if (empty((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_rrs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) && (($tmp = $_smarty_tpl->tpl_vars['form']->value['recog_regist_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "add") {?>checked<?php }?> id="recog_regist_type_add" name="recog_regist_type" type="radio" value="add" /><label for="recog_regist_type_add" class="radio">新規追加</label></th>
			<td><input name="recog_config_set_name" type="text" placeholder="任意の設定名をご入力ください" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['recog_config_set_name'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
"></td>
		</tr>
		<?php if (!empty($_smarty_tpl->tpl_vars['recogConfigSets']->value)) {?>
			<?php $_smarty_tpl->_assignInScope('updId', base_convert(Filter::len((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_rrs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),10),36,10));?>

			<tr>
				<th><input <?php if (!empty((($tmp = $_smarty_tpl->tpl_vars['form']->value['upd_rrs'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) || (($tmp = $_smarty_tpl->tpl_vars['form']->value['recog_regist_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "update") {?>checked<?php }?> id="recog_regist_type_update" name="recog_regist_type" type="radio" value="update" /><label for="recog_regist_type_update" class="radio">データ更新</label></th>
				<td>
					<p class="select">
						<select name="recog_config_set_id">
							<option></option>
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['recogConfigSets']->value, 'set');
$_smarty_tpl->tpl_vars['set']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['set']->value) {
$_smarty_tpl->tpl_vars['set']->do_else = false;
?>
								<option <?php if ($_smarty_tpl->tpl_vars['updId']->value == $_smarty_tpl->tpl_vars['set']->value['recog_config_set_id'] || (($tmp = $_smarty_tpl->tpl_vars['form']->value['recog_config_set_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['set']->value['recog_config_set_id']) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['set']->value['recog_config_set_id'] ));?>
"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['set']->value['recog_config_set_name'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
						<a id="recog_set_delete_btn" style="display: none" onclick="deleteRecogConfigSet()" href="javascript:void(0)">×</a>
					</p>
				</td>
			</tr>
		<?php }?>
	</table>
</div>

<div id="recog_config_set_area">

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">カメラから設定を読み込む</h3>
		<table class="form_cnt set_group">
			<?php if (empty(Session::getLoginUser("group_id"))) {?>
				<tr><th class="fs-select-th-center">グループ選択</th>
					<td>
							<select name="recog_config_set_group" class="device_group_select">
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
						<select name="recog_config_set_device" class="btn_disable_switch" disable-target="#recog_config_load_btn">
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
		<a id="recog_config_load_btn" href="javascript:void(0);" class="btn_red btn_set btn_disabled" onclick="getDeviceConfig('recog_config_set_device', 'recog_config_set_area')">設定を読み込む</a>
	</div>

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">ディスプレイ表示</h3>
		<table class="form_cnt">
			<tr>
				<th>会社名/団体名/イベント名など</th>
				<td>
					<input type="text" name="dispInfo" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['dispInfo'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<tr>
				<th rowspan="3">認識人物の情報</th>
				<td>
					<p class="td_cap">氏名表示</p>
					<input type="radio" name="dispShowName" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowName'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowName" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowName'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">ID表示</p>
					<input type="radio" name="dispShowID" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowID'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowID" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowID'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">登録写真表示</p>
					<input type="radio" name="dispShowPhoto" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowPhoto'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowPhoto" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowPhoto'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>

			<tr>
				<th rowspan="5">カメラの情報</th>
				<td>
					<p class="td_cap">IPアドレス表示</p>
					<input type="radio" name="dispShowIp" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowIp'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowIp" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowIp'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">シリアルNo表示</p>
					<input type="radio" name="dispShowSerailNo" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowSerailNo'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowSerailNo" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowSerailNo'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">ファームウェアバージョン表示</p>
					<input type="radio" name="dispShowVersion" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowVersion'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowVersion" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowVersion'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">登録人物データ数表示</p>
					<input type="radio" name="dispShowPersonInfo" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowPersonInfo'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowPersonInfo" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowPersonInfo'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">オフライン人数表示</p>
					<input type="radio" name="dispShowOfflineData" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowOfflineData'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="dispShowOfflineData" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['dispShowOfflineData'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">顔認証</h3>
		<table class="form_cnt">
			<tr>
				<th rowspan="4">認証成功/失敗時のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">成功時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="tipsCustom" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsCustom'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">成功時のメッセージ背景色</p>
					<p class="select">
						<select name="tipsBackgroundColor">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::tipsBackgroundColor(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsBackgroundColor'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="strangerTipsCustom" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerTipsCustom'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ背景色</p>
					<p class="select">
						<select name="strangerTipsBackgroundColor">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::tipsBackgroundColor(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerTipsBackgroundColor'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<th rowspan="2">認証成功/失敗時の音声再生設定</th>
				<td>
					<p class="td_cap">成功時の音声再生</p>
					<input type="radio" name="tipsVoiceEnable" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="tipsVoiceEnable" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時の音声再生</p>
					<input type="radio" name="strangerVoiceEnable" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="strangerVoiceEnable" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main"> カード認証<span style="font-size: 0.7rem;">（カード認証の設定はFaceFCのver96より対応しているため、ご利用の場合は最新のファームウェアをご利用ください）</span></h3>
		<p></p>
		<table class="form_cnt">
			<tr>
				<th rowspan="4">認証成功/失敗時のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">成功時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="tipsCustomCard" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsCustomCard'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">成功時のメッセージ背景色</p>
					<p class="select">
						<select name="tipsBackgroundColorCard">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::tipsBackgroundColor(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsBackgroundColorCard'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="strangerTipsCustomCard" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerTipsCustomCard'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ背景色</p>
					<p class="select">
						<select name="strangerTipsBackgroundColorCard">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::tipsBackgroundColor(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerTipsBackgroundColorCard'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<th rowspan="2">認証成功/失敗時の音声再生設定</th>
				<td>
					<p class="td_cap">成功時の音声再生</p>
					<input type="radio" name="tipsVoiceEnableCard" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsVoiceEnableCard'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="tipsVoiceEnableCard" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tipsVoiceEnableCard'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時の音声再生</p>
					<input type="radio" name="strangerVoiceEnableCard" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerVoiceEnableCard'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="strangerVoiceEnableCard" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['strangerVoiceEnableCard'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">識別設定</h3>
		<table class="form_cnt">
      <tr>
          <th rowspan="6">認識精度</th>
          <td>
            <p class="td_cap">識別距離(0.5メートルから2メートルの範囲)</p>
            <p class="select">
              <select name="recogWorkstateTime">
                <?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);$_smarty_tpl->tpl_vars['i']->step = 5;$_smarty_tpl->tpl_vars['i']->total = (int) ceil(($_smarty_tpl->tpl_vars['i']->step > 0 ? 20+1 - (5) : 5-(20)+1)/abs($_smarty_tpl->tpl_vars['i']->step));
if ($_smarty_tpl->tpl_vars['i']->total > 0) {
for ($_smarty_tpl->tpl_vars['i']->value = 5, $_smarty_tpl->tpl_vars['i']->iteration = 1;$_smarty_tpl->tpl_vars['i']->iteration <= $_smarty_tpl->tpl_vars['i']->total;$_smarty_tpl->tpl_vars['i']->value += $_smarty_tpl->tpl_vars['i']->step, $_smarty_tpl->tpl_vars['i']->iteration++) {
$_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->iteration === 1;$_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration === $_smarty_tpl->tpl_vars['i']->total;?>
                  <?php $_smarty_tpl->_assignInScope('val', sprintf("%.1f",$_smarty_tpl->tpl_vars['i']->value/10));?>
                  <option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['val']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['recogWorkstateTime'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
</option>
                <?php }
}
?>
              </select>
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">識別レベル</p>
            <p class="select">
              <select name="recogLiveness">
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::recogLiveness(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
                  <option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['recogLiveness'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
              </select>
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">識別間隔秒(0秒～10秒)</p>
            <input type="number" name="recogCircleInterval" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['recogCircleInterval'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="10" class="mini_txt">
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">認識比較閾値(0～100)</p>
            <input type="number" name="recogSearchThreshold" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['recogSearchThreshold'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="100" class="mini_txt">
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">マスク検出時の認識比較閾値(0～100)</p>
            <input type="number" name="recogMouthoccThreshold" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['recogMouthoccThreshold'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="100" class="mini_txt">
          </td>
        </tr>
                <tr>
          <td>
            <p class="td_cap">顔写真登録時の警告類似度(0～100)</p>
            <input type="number" name="captureAlarteThreshold" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['captureAlarteThreshold'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" min="0" max="100" class="mini_txt">
          </td>
        </tr>
        		</table>
	</div>

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">マスク検出</h3>
		<table class="form_cnt">

			<tr>
				<th>入場判定</th>
				<td>
					<p class="select">
						<select name="maskDetectMode">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::maskDetectMode(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['maskDetectMode'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>

			<tr>
				<th>マスク検出モード</th>
				<td>
					<input type="radio" name="maskFaceAttrSwitch" value="0" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['maskFaceAttrSwitch'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 0) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">口のみ覆うも許可する</label>
					<input type="radio" name="maskFaceAttrSwitch" value="1" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['maskFaceAttrSwitch'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">鼻と口の両方を覆う</label>
				</td>
			</tr>


			<tr>
				<th rowspan="4">マスク検出のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">マスク装着者の通知メッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="maskWearShowTips" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['maskWearShowTips'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク装着者の通知テキスト背景色</p>
					<p class="select">
						<select name="maskWearShowBackgroundColor">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::maskShowBackgroundColor(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['maskWearShowBackgroundColor'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク非装着者の通知メッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="maskNowearShowTips" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['maskNowearShowTips'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク非装着者の通知テキスト背景色</p>
					<p class="select">
						<select name="maskNowearShowBackgroundColor">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::maskShowBackgroundColor(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['maskNowearShowBackgroundColor'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>


			<tr>
				<th rowspan="2">マスク検出の音声通知設定</th>
				<td>
					<p class="td_cap">マスク装着者の音声通知</p>
					<input type="radio" name="maskWearVoiceEnable" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['maskWearVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="maskWearVoiceEnable" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['maskWearVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク非装着者の音声通知</p>
					<input type="radio" name="maskNowearVoiceEnable" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['maskNowearVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="maskNowearVoiceEnable" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['maskNowearVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>

		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">温度検出</h3>
		<table class="form_cnt">

			<tr>
				<th>有効/無効</th>
				<td>
					<input type="radio" name="tempEnable" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">温度検知を有効にする</label>
					<input type="radio" name="tempEnable" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">温度検知を無効にする</label>
				</td>
			</tr>

			<tr>
				<th>入場判定</th>
				<td>
					<p class="select">
						<select name="tempDetectMode">
							<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, Enums::tempDetectMode(), 'v', false, 'k');
$_smarty_tpl->tpl_vars['v']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
$_smarty_tpl->tpl_vars['v']->do_else = false;
?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['k']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['k']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['tempDetectMode'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['v']->value ));?>
</option>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</select>
					</p>
				</td>
			</tr>

			<tr>
				<th rowspan="2">温度検出のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">温度正常者の通知メッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="tempNormalShowTips" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['tempNormalShowTips'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</td>
			</tr>
			<td>
				<p class="td_cap">温度異常者の通知メッセージ(空登録の場合は通知無し)</p>
				<input type="text" name="tempAbnormalShowTips" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['tempAbnormalShowTips'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
			</td>
			</tr>

			<tr>
				<th rowspan="2">温度検出の音声通知設定</th>
				<td>
					<p class="td_cap">温度正常者の音声通知</p>
					<input type="radio" name="tempNormalVoiceEnable" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempNormalVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="tempNormalVoiceEnable" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempNormalVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">温度異常者の音声通知</p>
					<input type="radio" name="tempAbnormalVoiceEnable" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempAbnormalVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="tempAbnormalVoiceEnable" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempAbnormalVoiceEnable'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>

			<tr>
				<th rowspan="3">温度検知設定</th>
				<td>
					<p class="td_cap">正常温度設定(デフォルト：35.5～37.3)</p>

					<div class="inline_select">
						<p class="select">
							<select name="tempValueRangeFrom">
								<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);$_smarty_tpl->tpl_vars['i']->step = 1;$_smarty_tpl->tpl_vars['i']->total = (int) ceil(($_smarty_tpl->tpl_vars['i']->step > 0 ? 420+1 - (100) : 100-(420)+1)/abs($_smarty_tpl->tpl_vars['i']->step));
if ($_smarty_tpl->tpl_vars['i']->total > 0) {
for ($_smarty_tpl->tpl_vars['i']->value = 100, $_smarty_tpl->tpl_vars['i']->iteration = 1;$_smarty_tpl->tpl_vars['i']->iteration <= $_smarty_tpl->tpl_vars['i']->total;$_smarty_tpl->tpl_vars['i']->value += $_smarty_tpl->tpl_vars['i']->step, $_smarty_tpl->tpl_vars['i']->iteration++) {
$_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->iteration === 1;$_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration === $_smarty_tpl->tpl_vars['i']->total;?>
									<?php $_smarty_tpl->_assignInScope('val', sprintf("%.1f",$_smarty_tpl->tpl_vars['i']->value/10));?>
									<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['val']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['tempValueRangeFrom'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
</option>
								<?php }
}
?>
							</select>
						</p>
						<div>～</div>
						<p class="select">
							<select name="tempValueRangeTo">
								<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);$_smarty_tpl->tpl_vars['i']->step = 1;$_smarty_tpl->tpl_vars['i']->total = (int) ceil(($_smarty_tpl->tpl_vars['i']->step > 0 ? 420+1 - (100) : 100-(420)+1)/abs($_smarty_tpl->tpl_vars['i']->step));
if ($_smarty_tpl->tpl_vars['i']->total > 0) {
for ($_smarty_tpl->tpl_vars['i']->value = 100, $_smarty_tpl->tpl_vars['i']->iteration = 1;$_smarty_tpl->tpl_vars['i']->iteration <= $_smarty_tpl->tpl_vars['i']->total;$_smarty_tpl->tpl_vars['i']->value += $_smarty_tpl->tpl_vars['i']->step, $_smarty_tpl->tpl_vars['i']->iteration++) {
$_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->iteration === 1;$_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration === $_smarty_tpl->tpl_vars['i']->total;?>
									<?php $_smarty_tpl->_assignInScope('val', sprintf("%.1f",$_smarty_tpl->tpl_vars['i']->value/10));?>
									<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['val']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['tempValueRangeTo'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
</option>
								<?php }
}
?>
							</select>
						</p>
					</div>
				</td>
			</tr>

			<tr>
				<td>
					<p class="td_cap">温度補正(デフォルト:0.0)</p>
					<p class="select">
						<select name="tempCorrection">
							<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);$_smarty_tpl->tpl_vars['i']->step = 1;$_smarty_tpl->tpl_vars['i']->total = (int) ceil(($_smarty_tpl->tpl_vars['i']->step > 0 ? 50+1 - (-50) : -50-(50)+1)/abs($_smarty_tpl->tpl_vars['i']->step));
if ($_smarty_tpl->tpl_vars['i']->total > 0) {
for ($_smarty_tpl->tpl_vars['i']->value = -50, $_smarty_tpl->tpl_vars['i']->iteration = 1;$_smarty_tpl->tpl_vars['i']->iteration <= $_smarty_tpl->tpl_vars['i']->total;$_smarty_tpl->tpl_vars['i']->value += $_smarty_tpl->tpl_vars['i']->step, $_smarty_tpl->tpl_vars['i']->iteration++) {
$_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->iteration === 1;$_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration === $_smarty_tpl->tpl_vars['i']->total;?>
								<?php $_smarty_tpl->_assignInScope('val', sprintf("%.1f",$_smarty_tpl->tpl_vars['i']->value/10));?>
								<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
" <?php if ($_smarty_tpl->tpl_vars['val']->value == (($tmp = $_smarty_tpl->tpl_vars['form']->value['tempCorrection'] ?? null)===null||$tmp==='' ? null ?? null : $tmp)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['val']->value ));?>
</option>
							<?php }
}
?>
						</select>
					</p>
				</td>
			</tr>

			<tr>
				<td>
					<p class="td_cap">低温補正</p>
					<input type="radio" name="tempLowTempCorrection" value="1" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempLowTempCorrection'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) == 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">する</label>
					<input type="radio" name="tempLowTempCorrection" value="0" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['tempLowTempCorrection'] ?? null)===null||$tmp==='' ? null ?? null : $tmp) != 1) {?>checked<?php }?>><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>

	<a href="javascript:void(0);" onclick="doPost('./registRecogConfigSet')" class="btn_red">登録</a>

</div>
<?php }
}
