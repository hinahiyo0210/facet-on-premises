<?php
/* Smarty version 4.5.3, created on 2024-09-20 14:15:52
  from '/var/www/html/ui1/_pg/app/person/tab10_new.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ed05084fa8f2_95085609',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e181e28d4c592d11ea21b767d071f4ede8d1a866' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/person/tab10_new.tpl',
      1 => 1723539042,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ed05084fa8f2_95085609 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>

$(function() {

	
		$("#new_m1").fSelect();
	$("#new_m2").fSelect();
	$("#new_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#new_m2")
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
	
	
	// デバイスへの登録を開始。
	<?php if (!empty($_smarty_tpl->tpl_vars['new_registDevicePersonCode']->value)) {?>
		
		setTimeout(function() {
			
			registDevicePersonBegin("new", "<?php echo hjs($_smarty_tpl->tpl_vars['new_registDevicePersonCode']->value);?>
", <?php echo json_encode($_smarty_tpl->tpl_vars['new_registDeviceTargets']->value);?>
);
			
		}, 2000);
		
	<?php }?>
	
});

<?php echo '</script'; ?>
>

<h2 class="tit_cnt_main">新規ユーザー登録</h2>
<table class="form_cnt regist_cnt">
	<tr>
		<th>ID<span class="required">※</span></th>
		<td>
			<input type="text" placeholder="任意のID" name="new_personCode" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_personCode'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
			<p class="note red">IDは必須です。英数字と記号が利用可能です。<br>あとから変更はできません。</p>
		</td>
	</tr>
	<tr>
		<th>氏名<span class="required">※</span></th>
		<td>
			<input type="text" placeholder="名前を入力します" name="new_personName" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_personName'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
		</td>
	</tr>
	<tr><th>生年月日</th>
		<td>
			<p class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input type="text" class="flatpickr" data-position="above" data-allow-input="true" placeholder="1990/01/01" name="new_birthday" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_birthday'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
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
	<tr class="new_cards" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 1 && Validator::isEmpty((($tmp = $_smarty_tpl->tpl_vars['form']->value['new_card_id'][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))) {?> <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['emptyCnt']->value++ ));?>
 style="display:none"<?php }?>>
		<th style="vertical-align: middle;padding-bottom: 40px;">カードID</th>
		<td>
			<input type="text" placeholder="カード番号を入力します" name="new_card_id[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php ob_start();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));
$_prefixVariable1 = ob_get_clean();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_card_id'][$_prefixVariable1] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
		</td>
	</tr>
	<tr class="new_cards" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 1 && Validator::isEmpty((($tmp = $_smarty_tpl->tpl_vars['form']->value['new_card_id'][$_smarty_tpl->tpl_vars['i']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp))) {?>style="display:none"<?php }?>>
		<th style="vertical-align: middle;padding-bottom: 40px;"><span>カード有効期間</span></th>
		<td>
			<div class="period">
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d',strtotime('today -1 month')) ));?>
" name="new_date_from[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php ob_start();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));
$_prefixVariable2 = ob_get_clean();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_date_from'][$_prefixVariable2] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</div>
				<span>〜</span>
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date('Y/m/d') ));?>
" name="new_date_to[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
]" value="<?php ob_start();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));
$_prefixVariable3 = ob_get_clean();
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_date_to'][$_prefixVariable3] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
				</div>
			</div>
		</td>
	</tr>
	<?php }
}
?>
	<tr><th></th>
		<td style="float: left;padding-bottom: 0;">
			<?php if ($_smarty_tpl->tpl_vars['emptyCnt']->value > 0) {?><span style="padding-left: 558px"><a href="javascript:void(0);" onclick="$('.new_cards').not(':visible').slideDown(200); $(this).hide()">全件を表示</a></span><?php }?>
		</td>
	</tr>
		<?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
	<tr>
		<th class="fs-select-th-center">区分</th>
		<td>
			<p class="select">
				<select name="new_person_type_code">
					<option value=""></option>
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['personTypeList']->value, 'personType', false, 'person_type_code');
$_smarty_tpl->tpl_vars['personType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['person_type_code']->value => $_smarty_tpl->tpl_vars['personType']->value) {
$_smarty_tpl->tpl_vars['personType']->do_else = false;
?>
						<option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['new_person_type_code'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['person_type_code']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['person_type_code']->value ));?>
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
			<input type="text" placeholder="<?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name1'] ));
} else { ?>備考1<?php }?>を入力します" name="new_description1" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_description1'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
		</td>
	</tr>
	<tr>
		<th><?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?></th>
		<td>
			<input type="text" placeholder="<?php if ($_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2']) {
echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['contractor']->value['enter_exit_description_name2'] ));
} else { ?>備考2<?php }?>を入力します" name="new_description2" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_description2'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
		</td>
	</tr>
	<?php }?>
		<tr>
		<th>画像登録</th>
		<td class="btn_file">
			<label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
"><i class="fas fa-arrow-to-top"></i>ファイルをアップロード<input type="file" class="base64_picture" accept=".jpg,.jpeg" img-target="#new_picture_preview" set-target="input[name='new_picture']" id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
"></label>
			<div class="picture_preview" id="new_picture_preview"></div><input type="hidden" name="new_picture" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_picture'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" error-target="#new_picture_preview" post-only="./registPerson">
			<p class="note">パソコンなどから画像をアップロードすることができます。<br>下記の撮影ガイドを参考に撮影してください。jpg画像が利用できます。</p>
			<a href="/ui1/static/manual/guide.pdf" target="_blank" class="link_pdf"><i class="fas fa-file-alt"></i>登録画像の撮影ガイド（PDF）</a>
		</td>
	</tr>
	
	<tr><th class="tit">登録カメラの選択</th><td class="tit_cap">どのカメラにユーザーを登録するか選択します。</td></tr>
		<?php $_smarty_tpl->_assignInScope('devicesDisplay', array());?>
	<?php if (empty(Session::getLoginUser("group_id"))) {?>
		<tr><th class="fs-select-th-center">グループ選択</th>
			<td>
					<select id="new_m1" class="groups" name="new_group_ids[]" multiple="multiple">
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
							<?php $_smarty_tpl->_assignInScope('selected', '');?>
							<?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["new_group_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['g']->value)) {?>
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
	<tr><th class="fs-select-th-center">カメラ選択</th>
		<td>
			<select id="new_m2" class="devices" name="new_device_ids[]" multiple="multiple">
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
					<?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
						<option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists((($tmp = $_smarty_tpl->tpl_vars['form']->value["new_device_ids"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp),$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
					<?php }?>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			</select>
		</td>
	</tr>
	</table>
<a href="javascript:void(0)" onclick="doPost('./registPerson', false)" class="enter-submit btn_red btn_regist">登録</a>

<?php }
}
