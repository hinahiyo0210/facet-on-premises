{* dev founder luyi *}

{$title="ログインID管理"}{$icon="fa-users-cog"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}
<script src="/ui1/static/js/fselect/fSelect.js"></script>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">

<script>

	// タブ制御
	$(function() {
		$(".tab_btn li").click(function() {
			var url = "./";
			if ($(this).attr("tab-name")) url = "./?tab=" + $(this).attr("tab-name");
			history.pushState("", "", url);
			document.idManageForm.tab.value = $(this).attr("tab-name");
		});
	});
	// 送信。
	function doPost(action, scrollSave) {
		$(".no-req").val("");
		doFormSend(action, scrollSave, "post");
	}
	function doGet(action, scrollSave) {
		// URLが長くなりすぎないように、値の無いinput類はdisabledにする。
		$(".no-req").val("");
		disableEmptyInput($(document.idManageForm));
		doFormSend(action, scrollSave, "get");
	}

	function doFormSend(action, scrollSave, method) {

		if (scrollSave) {
			$("input[name='_p']").val(parseInt($(window).scrollTop()).toString(36)).prop("disabled", false);
		}

		$("input").each(function() {
			if ($(this).attr("post-only") && $(this).attr("post-only") != action) {
				$(this).val("");
			}
		});


		document.idManageForm.method = method;
		document.idManageForm.action = action;
		document.idManageForm.submit();
	}

</script>

<form name="idManageForm" action="./" method="post">
	<input type="hidden" name="tab" value="{$form.tab}" />
	<input type="hidden" name="_p" />
	<div class="tab_container">

		<ul class="tab_btn">
			<li tab-name=""       {if empty($form.tab)     }class="active"{/if}>権限作成</li>
			<li tab-name="new"    {if $form.tab == "new"   }class="active"{/if}>新規登録</li>
			<li tab-name="modify" {if $form.tab == "modify"}class="active"{/if}>変更・削除</li>
		</ul>
		<div class="tab_cnt_wrap">
			<div class="tab_cnt{if empty($form.tab)     } show{/if}">{include file="./tab10_auth.tpl"  }</div>
			<div class="tab_cnt{if $form.tab == "new"   } show{/if}">{include file="./tab20_new.tpl"   }</div>
			<div class="tab_cnt{if $form.tab == "modify"} show{/if}">{include file="./tab30_modify.tpl"}</div>
		</div>

	</div>
</form>

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}