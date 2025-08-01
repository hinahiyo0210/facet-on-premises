{* dev founder feihan *}

<script>
	// ユーザーの変更。
	function doListModAuthRole(userId) {
		$('#list_mod_loginUserId').val(userId);

		{if isset($list_list)}
		$("input[name='list_pageNo']").val("{$list_pageInfo->getPageNo()}");
		{/if}

		doPost('./modLoginUserInit', true);
	}

	// ユーザーの削除。
	function doListDeleteAuthRole(userId){
		$("input[name='list_del_userId']").val(userId);
		$("#list_del_loginId").text($("#list_user_tr_" + userId + " .loginId").text());
		$("#list_del_userName").text($("#list_user_tr_" + userId + " .userName").text());
		showModal("ログインIDの削除", $("#userauth_del_modal_template").html());
	}

</script>

<input type="hidden" name="list_del_userId" value="" />
<input type="hidden" name="list_del_back" value="{$form.list_del_back|default:""}" />

<!-- ログインユーザー削除確認モーダル -->
<div id="userauth_del_modal_template" style="display:none">
	下記のログインIDを削除します。<br >
	本当によろしいですか？<br >
	<br />
	ID：<span id="list_del_loginId"></span><br />
	氏名：<span id="list_del_userName"></span><br />

	<div class="dialog_btn_wrap" style="margin-top:1em">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		<a href="javascript:void(0);" onclick="doPost('./delLoginUser', false)" class="enter-submit btn btn_red btn_regist">削除</a>
	</div>
</div>

<!-- ログインユーザー変更モーダル -->
{if !empty($list_modLoginUser)}

	<script>
		$(function() {
			var openCallback = function() {

				// 変更モーダルが開く
				$("#loginuser_mod_modal_template").remove();
				// プルダウン初期化
				$('#list_mod_groupId').fSelect()


			};
			var closeCallback = null;
			var noClearError = true;
			var appndTarget = "form[name='idManageForm']";

			showModal("ログインIDの変更", $("#loginuser_mod_modal_template").html(), "loginuser_mod_modal", openCallback, closeCallback, noClearError, appndTarget);
		});
	</script>

	<div id="loginuser_mod_modal_template" style="display:none">
		<input type="hidden" name="list_mod_back" value="{$form.list_mod_back}" />
		<input type="hidden" name="list_pageNo" value="{$list_pageInfo->getPageNo()}" />
		<input type="hidden" id="list_mod_userId" name="list_mod_userId" value="{$form.list_mod_userId}" />
		<div class="loginuser_mod_modal_inner">
			<table class="form_cnt regist_cnt id_manage_modal">
				<tr>
					<th>ログインID</th>
					<td>
						<input type="text" name="list_mod_loginId" value="{$form.list_mod_loginId}" readonly class="disabled" />
					</td>
				</tr>
				<tr>
					<th>パスワード</th>
					<td>
						<input name="list_mod_password" type="password" value="{$form.list_mod_password}">
					</td>
				</tr>
				<tr>
					<th>パスワード（確認）</th>
					<td>
						<input name="list_mod_passwordConfirm" type="password" value="{$form.list_mod_passwordConfirm}">
					</td>
				</tr>
				<tr>
					<th>氏名 <span class="required">※</span></th>
					<td>
						<input type="text" name="list_mod_userName" value="{$form.list_mod_userName}">
					</td>
				</tr>
				<tr>
					<th class="fs-select-th-center" style="padding-bottom:20px">カメラグループ</th>
					<td>
						<select name="list_mod_groupId" id="list_mod_groupId">
							<option value=""></option>
							{foreach $groups as $group_id=>$group}
								<option {if $form.list_mod_groupId == $group_id}selected{/if} value="{$group_id}" >{$group.group_name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<th>権限<span class="required">※</span></th>
					<td>
						<p class="select">
							<select name="list_mod_authSetId">
								<option value=""></option>
								{foreach $auths as $auth_set_id=>$auth}
									<option {if $form.list_mod_authSetId == $auth_set_id}selected{/if} value="{$auth_set_id}">{$auth.auth_set_name}</option>
								{/foreach}
							</select>
						</p>
					</td>
				</tr>
			</table>

			<div class="btns" style="margin-top:2em">
				<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
				<a href="javascript:void(0)" onclick="doPost('./modLoginUser', false)" class="enter-submit btn btn_red btn_regist" enter-submit-target=".userAuth_mod_modal">設定反映</a>
			</div>

		</div>
	</div>
{/if}

<h2 class="tit_cnt_main">変更・削除</h2>
<p class="cap_cnt_main">ログインIDの変更・削除を行います。</p>

<div class="search_area">
<table class="form_cnt regist_cnt">
	<tr class="condition">
		<th class="tit">ログインID</th>
		<td><input type="text" name="list_loginId" value="{$form["list_loginId"]|default:""}" placeholder="任意のID"></td>
	</tr>
	<tr class="condition">
		<th class="tit">氏名</th>
		<td><input type="text" name="list_userName" value="{$form["list_userName"]|default:""}" placeholder="名前を入力します"></td>
	</tr>
	<tr class="condition">
		<th class="tit">権限</th>
		<td>
			<p class="select">
				<select name="list_authSetId">
					<option value=""></option>
					{foreach $auths as $auth_set_id=>$auth}
						<option {if $form.list_authSetId|default:"" == $auth_set_id}selected{/if} value="{$auth_set_id}">{$auth.auth_set_name}</option>
					{/foreach}
				</select>
			</p>
		</td>
	</tr>
</table>
<a href="javascript:void(0)" onclick="doGet('./listSearch', true)" class="enter-submit btn_red"><i class="fas fa-search"></i>検索実行</a>
</div>
<input type="hidden" id="list_mod_loginUserId" name="list_mod_loginUserId" />

{if isset($list_list)}
	<div class="search_results">
		<div class="tit_wrap">
			<h3 class="tit">検索結果</h3>
		</div>

		{include file="../_inc/pager_counter_userauth.tpl" pageInfo=$list_pageInfo topPager=true}

		<table class="search_results_table">
			<tr>
				<th class="results_id">ログインID</th>
				<th class="results_name">氏名</th>
				<th class="results_group">カメラグループ</th>
				<th class="results_group">権限</th>
				<th></th>
				<th></th>
			</tr>
			{foreach $list_list as $item}
				<tr id="list_user_tr_{$item.user_id}">
					<td class="loginId">{$item.login_id}</td>
					<td class="userName">{$item.user_name}</td>
					<td class="groupName">{$item.group_name}</td>
					<td class="authSetName">{$item.auth_set_name}</td>
					<td><a href="javascript:void(0)"{if $item.user_id == Session::getLoginUser("user_id")} class="icon_disabled"{else} onclick="doListModAuthRole('{$item.user_id}')"{/if}><i class="fas fa-edit"></i></a></td>
					<td><a href="javascript:void(0)"{if $item.user_id == Session::getLoginUser("user_id")} class="icon_disabled"{else} onclick="doListDeleteAuthRole('{$item.user_id}')"{/if}><i class="fas fa-trash-alt"></i></a></td>
				</tr>
			{/foreach}
		</table>
	</div>
	{include file="../_inc/pager_counter_userauth.tpl" pageInfo=$list_pageInfo topPager=false}
{/if}
