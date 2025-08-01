{assign var="title" value="ログイン"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl' noHeader=true}

<script>

function showPassModal() {
	
	showModal("パスワードをお忘れの場合", $("#passModal").html());
	
}

</script>

<div id="passModal" style="display:none">
	<br />
	ご本人様確認の上で、パスワードをリセットさせて頂きます。<br />
	<br />
	大変お手数ではございますが、ご契約の際の販売代理店窓口にお問い合わせください。<br />
	<br />
	
	<div class="btn_1">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
	</div>
	
</div>


<div class="login_cnt">

	<form action="./login" method="post">
  <h1 class="tit">facet{if ENABLE_AWS} Cloud{/if}</h1>
		<h2 class="tit_element">ログインID</h2>
		<p class="element"><input name="login_id" type="text" placeholder="ログインIDを入力"></p>
		<h2 class="tit_element">パスワード</h2>
		<p class="element"><input name="password" type="password" placeholder="パスワードを入力"></p>
		<input type="submit" value="ログイン" class="enter-submit btn_login">
		<a href="javascript:void(0)" onclick="showPassModal()">パスワードを忘れた方はこちら<i class="far fa-angle-right"></i></a>
	</form>

</div>

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl' noHeader=true}