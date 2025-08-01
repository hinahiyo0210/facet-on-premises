{assign var="title" value="パスワード変更"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}

<form name="passwordForm" action="./registPassword" method="post">
	


	<div class="setting_area">

		<div class="setting_cnt">
			<p class="tit">現在のパスワード</p>
			<div>
				<input name="password_current" type="password" />
			</div>
		</div>

		<div class="setting_cnt">
			<p class="tit">新しいパスワード</p>
			<div>
				<input name="password" type="password" />
			</div>
		</div>

		<div class="setting_cnt">
			<p class="tit">新しいパスワード（確認用）</p>
			<div>
				<input name="password_confirm" type="password" />
			</div>
		</div>
	
		<a href="javascript:void(0)" onclick="document.passwordForm.submit()" class="enter-submit btn_red">パスワードを変更</a>
	</div>
	
</form>

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}