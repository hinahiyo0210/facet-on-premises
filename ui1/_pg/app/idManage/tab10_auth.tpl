{* dev founder luyi *}

<script>
	let _auth_sets = {$auths|@json_encode nofilter};

	$(function() {
		// テキストボックス・プルダウン選択時、ラジオボックスの連動制御
		$('input[name="auth_set_name"]').focus(function() {
			$("#auth_regist_type_add").prop('checked', true);
		});
		$('select[name="auth_set_id"]').focus(function() {
			$('#auth_regist_type_update').prop('checked', true);
		});

		// 機能チェックボックスの木構造を作る
		$('td.table_grid').each(function(index, node) {
			$(node).change(function() {
				let input_node = $(this).find('input')[0];
				changeChildCheckbox(input_node);
				changeParentCheckbox(input_node);
			});
			adjustTableGrid($(node));
		});

		// 点線の追加
		$('td.table_grid:first-child:has(input)').each(function(index, node) {
			$(node).parent().before('<tr><td colspan="9"><div style="border-top: dashed ' + (index ? '1px #91949c' : '2px #4b515e') + '"></div></td></tr>');
		});

		// プルダウン選択時、機能チェックボックスの設定値を表示
		$('select[name="auth_set_id"]').change(function() {
			let selected_val = _auth_sets[$(this).val()];
			if (selected_val) {
				$('#auth_set_delete_btn').show();
				$('#auth_set_area input[name="function_ids[]"]').each(function(index, node) {
					$(node).prop('checked', selected_val['function_ids'].indexOf($(node).val()) >= 0).change();
				});
			} else {
				$('#auth_set_delete_btn').hide();
			}
		}).change();
	});

	function deleteAuth() {
		// 削除確認モーダルを表示
		let $selected = $('select[name="auth_set_id"] option:selected');
		if ($selected.val()) {
			let msg = '下記の権限が削除されます<br>よろしいですか？<br><br>[' + escapeHtml($selected.text()) + ']<br>'
				+ '<div class="btns">'
				+ '<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">キャンセル</a>'
				+ '<a href="javascript:void(0);" onclick="doPost(\'./deleteAuth\')" class="btn btn_red">OK</a>'
				+ '</div>';
			showModal("削除の確認", msg);
		}
	}

	function adjustTableGrid($node) {
		// 機能チェックボックスの位置を調整
		let $parent_func_id = $node.find('input').data('parent-func-id');
		if (!$parent_func_id) {
			return;
		}
		$node.parent().prevAll().find('input').each(function (index, parent_func_node) {
			// マッチング
			if ($parent_func_id.toString() === $(parent_func_node).val()) {
				if ($(parent_func_node).parent().nextAll().length === 1) {
					// 親の後に他のチェックボックスがない場合、親の後に追加
					$node.siblings().remove();
					$node.unwrap().insertAfter($(parent_func_node).parent());
					if (!$(parent_func_node).data('parent-func-id')) {
						$(parent_func_node).removeAttr('name');
					}
				} else {
					// 親の後に他のチェックボックスがある場合、インデントする
					$node.before($('<td class="table_grid"></td>'.repeat($(parent_func_node).parent().prevAll().length + 1)));
				}
			}
		});
	}

	function changeChildCheckbox(node) {
		// 子チェックボックス連動
		let $child_checkbox = $('input[data-parent-func-id=' + $(node).val() + ']')
		if ($child_checkbox[0]) {
			// 再帰
			$child_checkbox.prop('checked', $(node).prop('checked')).each(function(index, child_node) {
				changeChildCheckbox(child_node);
			});
		}
	}

	function changeParentCheckbox(node) {
		// 親チェックボックス連動
		let $parent_checkbox = $('input[value=' + $(node).data('parent-func-id') + ']');
		if ($parent_checkbox[0]) {
			if ($parent_checkbox.data('parent-func-id')) {
				if (node.checked) {
					$parent_checkbox.prop('checked', true);
				}
			} else {
				$parent_checkbox.prop('checked', !$('input[data-parent-func-id=' + $(node).data('parent-func-id') + ']:not(:checked)')[0]);
			}
			// 再帰
			changeParentCheckbox($parent_checkbox[0]);
		}
	}
</script>

<h2 class="tit_cnt_main">権限作成</h2>
<p class="cap_cnt_main">ログインIDの権限を作成することができます。</p>
<table class="form_cnt regist_cnt">
	<tr>
		<th><input {if empty($form.upd_srs|default:"") && $form.auth_regist_type|default:null == "add"}checked{/if} id="auth_regist_type_add" name="auth_regist_type" type="radio" value="add" /><label for="auth_regist_type_add" class="radio">新規追加</label></th>
		<td><input name="auth_set_name" type="text" placeholder="任意の権限名をご入力ください" value="{$form.auth_set_name|default:""}"></td>
	</tr>
	{if !empty($auths)}
		{$updId = base_convert(Filter::len($form.upd_srs|default:"", 10), 36, 10)}
		<tr>
			<th><input {if !empty($form.upd_srs|default:"") || $form.auth_regist_type|default:null == "update"}checked{/if} id="auth_regist_type_update" name="auth_regist_type" type="radio" value="update" /><label for="auth_regist_type_update" class="radio">データ更新</label></th>
			<td>
				<p class="select">
					<select name="auth_set_id">
						<option></option>
						{foreach $auths as $auth}
							<option {if $updId == $auth.auth_set_id || $form.auth_set_id|default:"" == $auth.auth_set_id}selected{/if} value="{$auth.auth_set_id}">{$auth.auth_set_name}</option>
						{/foreach}
					</select>
					<a id="auth_set_delete_btn" style="display: none" onclick="deleteAuth()" href="javascript:void(0)">×</a>
				</p>
			</td>
		</tr>
	{/if}

</table>
<table id="auth_set_area" class="form_cnt regist_cnt">
{foreach $functions as $func}
  {if !(!ENABLE_AWS && $func.function_id === "560")} <!--オンプレミスはアラーム設定非表示-->
    <tr>
      <td class="table_grid">
        <input type="checkbox" name="function_ids[]" value="{$func.function_id}"{if $func.parent_function_id} data-parent-func-id="{$func.parent_function_id}"{/if} id="{seqId()}"><label for="{seqId(1)}" class="checkbox">{$func.function_name}</label>
      </td>
      <td></td>
    </tr>
  {/if}
{/foreach}
</table>
<a href="javascript:void(0)" onclick="doPost('./updateAuth')" class="enter-submit btn_red btn_regist">権限を登録</a>
