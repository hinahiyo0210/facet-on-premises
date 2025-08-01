{* dev founder zouzhiyuan *}

<script>
	$(function() {
		// プルダウン初期化
		$('#new_camera_group').fSelect();

	})
</script>

<h2 class="tit_cnt_main">新規登録</h2>
<p class="cap_cnt_main">権限を指定してログインIDを作成することができます。{if $contractor.single_tenant_mode != 1}<br>作成できるログインIDは、「{Session::getLoginUser("login_id")}_」から始まるIDのみ作成できます。</p>{/if}
<table class="form_cnt regist_cnt id_manage_new_tbl">
	<tr>
		<th class="table_grid">ログインID <span class="required">※</span></th>
		<td>
			<input type="text" name="new_account_id" value="{$form.new_account_id|default:""}"> {if $contractor.single_tenant_mode != 1}※ 「{Session::getLoginUser("login_id")}_」の後に続くIDを入力します{/if}
		</td>
	</tr>
	<tr>
		<th class="table_grid">パスワード <span class="required">※</span></th>
		<td>
			<input type="password" name="new_password" value="{$form.new_password|default:""}">
		</td>
	</tr>
	<tr>
		<th class="table_grid">パスワード（確認）<span class="required">※</span></th>
		<td>
			<input type="password" name="new_password_confirm" value="{$form.new_password_confirm|default:""}">
		</td>
	</tr>
	<tr>
		<th class="table_grid">氏名 <span class="required">※</span></th>
		<td>
			<input type="text" name="new_account_name" value="{$form.new_account_name|default:""}">
		</td>
	</tr>
	<tr>
		<th class="table_grid fs-select-th-center">カメラグループ（任意）</th>
		<td>
			<select name="new_camera_group" id="new_camera_group">
				<option value="">&nbsp;</option>
				{foreach $groups as $group_id=>$group}
					<option {if $form.new_camera_group|default:"" == $group_id}selected{/if} value="{$group_id}" >{$group.group_name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<th class="table_grid">権限 <span class="required">※</span></th>
		<td>
			<p class="select">
				<select name="new_role">
					<option value="">&nbsp;</option>
					{foreach $auths as $auth_set_id=>$auth}
						<option {if $form.new_role|default:"" == $auth_set_id}selected{/if} value="{$auth_set_id}">{$auth.auth_set_name}</option>
					{/foreach}
				</select>
			</p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<p class="cap_cnt_main">
				※カメラグループを指定した場合は、選択したカメラグループに属するカメラしか表示されなくなります。<br>
				　加えて、ユーザ登録・カメラ設定・アラート設定のメニューが非表示になります。
			</p>
		</td>
	</tr>
</table>
<a href="javascript:void(0)" onclick="doPost('./registLoginUser', false)" class="enter-submit btn_red btn_regist">ログインIDを登録</a>