<?php
/* Smarty version 4.5.3, created on 2025-07-24 09:49:40
  from '/var/www/html/ui1/_pg/app/device/tab70_group.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_68818324aa58e5_36194453',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c32f46a092ec6cf8cda083fb5f89daaa281dc24e' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/device/tab70_group.tpl',
      1 => 1725266931,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68818324aa58e5_36194453 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="terminal_cnt">
	<?php echo '<script'; ?>
>

		function addGroup() {
			
			var $row = $($("#new_group_row").prop("outerHTML"));
			$row.hide();
			
			$("#groups").append($row);
			$row.fadeIn(200);
		}
		
		function saveGroup() {
			
			var checkNames = [];
			$(byNames("group_names[]")).each(function() {
				if ($(this).val() == "") {
					checkNames.push(escapeHtml($(this).attr("org-value")));
				}
			});

			
			if (checkNames.length == 0) {
				doPost('./registGroup', true);
				return;
			}

			var msg = "下記のグループは削除されます<br />よろしいですか？<br /><br />[" + checkNames.join(" / ") + "]<br />";
			msg += "<div class=\"btns\">";
			msg += "<a href=\"javascript:void(0);\" onclick=\"removeModal()\" class=\"btn btn_gray\">キャンセル</a>";
			msg += "<a href=\"javascript:void(0);\" onclick=\"doPost('./registGroup', true)\" class=\"btn btn_red\">OK</a>";
			msg += "</div>";
			showModal("削除の確認", msg);
		}
		
	<?php echo '</script'; ?>
>
	<h3 class="tit_cnt_main">カメラグループの設定</h3>
  <?php if (ENABLE_AWS) {?>
    <p class="cap_cnt_main">拠点ごとに複数台カメラを設置している場合、その拠点ごとにカメラを所属させることができます。<br>
      例えば東京支店内に複数台のカメラがある場合は「東京支店」グループにカメラ「A」、「B」、「C」を所属させ、異動があった場合などに一括で認証対象に設定することができます。</p>
  <?php } else { ?>
    <p class="cap_cnt_main">各フロアごとに複数台カメラを設置している場合、そのフロアごとにカメラを所属させることができます。<br>
      例えばAフロア内に複数台のカメラがある場合は「Aフロア」グループにカメラ「A」、「B」、「C」を所属させ、異動があった場合などに一括で認証対象に設定することができます。</p>
  <?php }?>
	<table id="groups" class="form_cnt">
		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, (($tmp = $_smarty_tpl->tpl_vars['form']->value['group_ids'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp), 'id', false, 'idx');
$_smarty_tpl->tpl_vars['id']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['idx']->value => $_smarty_tpl->tpl_vars['id']->value) {
$_smarty_tpl->tpl_vars['id']->do_else = false;
?>
			<?php $_smarty_tpl->_assignInScope('group_name', (($tmp = $_smarty_tpl->tpl_vars['form']->value['group_names'][$_smarty_tpl->tpl_vars['idx']->value] ?? null)===null||$tmp==='' ? '' ?? null : $tmp));?>
			<tr>
				<th>グループ名</th>
				<td>
					<input name="group_ids[]" type="hidden" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['id']->value ));?>
" />
					<input class="update_group" name="group_names[]" type="text" placeholder="空にして登録すると削除されます" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group_name']->value ));?>
" org-value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group_name']->value ));?>
" />
				</td>
			</tr>
		<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_group_names'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp), 'new_name');
$_smarty_tpl->tpl_vars['new_name']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['new_name']->value) {
$_smarty_tpl->tpl_vars['new_name']->do_else = false;
?>
			<?php if (!empty($_smarty_tpl->tpl_vars['new_name']->value)) {?>
				<tr>
					<th>グループ名</th>
					<td>
						<input class="new_group" name="new_group_names[]" type="text" placeholder="任意のグループ名をご入力ください" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['new_name']->value ));?>
" />
					</td>
				</tr>
			<?php }?>
		<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
		<tr id="new_group_row">
			<th>グループ名</th>
			<td>
				<input class="new_group" name="new_group_names[]" type="text" placeholder="任意のグループ名をご入力ください" value="" />
			</td>
		</tr>
	</table>
	<a href="javascript:void(0);" onclick="addGroup()"  class="btn_plus"><i class="fas fa-plus-circle"></i>グループを追加</a>
	<a href="javascript:void(0);" onclick="saveGroup()" class="enter-submit btn_red">グループを登録</a>

</div>

<?php if (Session::getLoginUser("apb_mode_flag")) {?>

<?php }
}
}
