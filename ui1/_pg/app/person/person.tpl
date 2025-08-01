{$title="ユーザー登録・変更"}{$icon="fa-user-circle"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}
{* add-start founder feihan *}
<script src="/ui1/static/js/fselect/fSelect.js"></script>
<script>
	{* fSelect用定数 *}
	const groups = { }
	const groupsInit = []
	{if empty(Session::getLoginUser("group_id"))}
	{foreach $groupsDisplay as $gid=>$group}
	groups[{$gid}] = [ {foreach $group.deviceIds as $deviceId} '{$deviceId}', {/foreach} ]
	groupsInit.push( { id: '{$gid}', name: '{$group.group_name|escape:"javascript"}' } )
	{/foreach}
	{/if}
	const devices = []
	{foreach $devices as $d=>$device}
	devices.push( { id: '{$d}', name: '{$device.name|escape:"javascript"}' } )
	{/foreach}

</script>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">
{* add-end founder feihan *}
<script>

// タブ制御
$(function() {

	$(".tab_btn li").click(function() {
		var url = "./";
		if ($(this).attr("tab-name")) url = "./?tab=" + $(this).attr("tab-name");
		history.pushState("", "", url);
		document.personForm.tab.value = $(this).attr("tab-name");
	});

});

// 送信。
function doPost(action, scrollSave) {
	if (action == './registPerson') {
		let newDropdownIndex;
		$('.fs-dropdown').each(function(index) {
			if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('new') > -1) {
				newDropdownIndex = index;
				return false;
			} else {
				return true;
			}
		});
		if ($('.fs-dropdown').eq(newDropdownIndex).hasClass('hidden')) {
			$(".no-req").val("");
			doFormSend(action, scrollSave, "post");
		}
	} else {
		$(".no-req").val("");
		doFormSend(action, scrollSave, "post");
	}
}
function doGet(action, scrollSave) { 
	// URLが長くなりすぎないように、値の無いinput類はdisabledにする。
	$(".no-req").val("");
	disableEmptyInput($(document.personForm));
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
	
	if ($("input[name='tab']").val() == "bulk") {
		$("input[name='bulkFile']").prop("disabled", false);
		document.personForm.enctype = "multipart/form-data";
	} else {
		$("input[name='bulkFile']").prop("disabled", true);
		document.personForm.enctype = "";
	}
	
	document.personForm.method = method;
	document.personForm.action = action;
	document.personForm.submit();
}


// 人物の反映を開始。
var _processingRegistPersonDevice;
var _processingRegistPersonDeviceQueue;
var _processingRegistPersonCode;
var _processingRegistType;
var _processingRegistCompleteCallback;
var _processingRegistCloseCallback;

function registDevicePersonBegin(type, personCode, devices /* [{ id: xxx, name: xxx },{ id: xxx, name: xxx }] */, processingRegistCompleteCallback, isContinue, processingRegistCloseCallback) {
	
	var title = "";
	if (type == "new") title = "ユーザー登録";
	if (type == "mod") title = "ユーザーの変更";
	if (type == "del") title = "ユーザーの削除";
	
	if (isContinue) {
		$("#modal_message .modal_msg_title").text(title);
	} else {
		showModal(title, $("#divicePersonRegistModalTemplate").html());
	}

	_processingRegistPersonCode = personCode;
	_processingRegistType = type;
	_processingRegistPersonDeviceQueue = devices;
	_processingRegistCompleteCallback = processingRegistCompleteCallback;
	_processingRegistCloseCallback = processingRegistCloseCallback;
	registDevicePersonNext();
}

// 人物の反映をリトライ。
function registDevicePersonRetry() {
	_processingRegistPersonDeviceQueue.unshift(_processingRegistPersonDevice);
	registDevicePersonNext();
}

// 次のデバイスの人物の反映を実行。
function registDevicePersonNext() {

	$("#modal_message .diviceConnectModal_loading").show();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_complete").hide();

	var device = _processingRegistPersonDeviceQueue.shift();

	if (device == null) {
		// 全て終了。
		if ($("#modal_message .diviceConnectModal_error_names .error_device").length) {
			$("#modal_message .diviceConnectModal_error_names_area").show();
		}
		
		$("#modal_message .diviceConnectModal_loading").hide();
		$("#modal_message .diviceConnectModal_error").hide();
		$("#modal_message .diviceConnectModal_complete").show();
		
		if (_processingRegistCompleteCallback) _processingRegistCompleteCallback();
		return;
	}

	_processingRegistPersonDevice = device;
	
	// 次のデバイスへ。
	$("#modal_message .diviceConnectModal_device_name").text(device.name).hide().fadeIn(200);
	
	
	doAjax("./registPersonForDevice", { device_id: device.id, type: _processingRegistType, person_code: _processingRegistPersonCode }, function(data) {
		
		var $successArea = $("#modal_message .diviceConnectModal_success_names");
		var $errorArea   = $("#modal_message .diviceConnectModal_error_names");
		
		// エラー
		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			$("#modal_message .diviceConnectModal_error_area").text(data.error);
			
			if (_processingRegistType == "del" && _processingRegistPersonDeviceQueue.length == 0) {
				$(".next_device_label").text("構わずにクラウドサーバ上からデータ削除").css("transition", "0s").css("font-size", "1vw").css("width", "800px");
			}
			
			
			$errorArea.find(".device_" +  device.id).remove();
			$errorArea.append($("<span style='margin:0 0.5em' class='error_device device_" + device.id + "'></span>").text(device.name));
			return;
		}

		// 成功
		$errorArea.find(".device_" +  device.id).remove();
		$successArea.append($("<span style='margin:0 0.5em' class='device_" + device.id + "'></span>").text(device.name));
		registDevicePersonNext();
	});
	
}

// 人物の反映ダイアログを閉じる
function registDevicePersonRemoveModal() {
	
	if (_processingRegistCloseCallback) {
		_processingRegistCloseCallback();
	} else {
		removeModal();
	}
}

$(function() {

	// 人物画像の参照リンク
	$(".person_picture_view").each(function() {
		
/*
		$(this).hover(function() {

			$(".person_picture_box").remove();
			
			var $div = $("<div class='person_picture_box'></div>");
//			$div.css("cursor", "default");
//			$div.css("background-image", "url('" + $(this).attr("person-picture-url") + "')")
			$div.append("<img src='" + $(this).attr("person-picture-url") + "'>");
			
			$div.hide();
//			$div.click(function(e) { e.stopPropagation(); });
			$div.hover(function(e) { e.stopPropagation(); }, function() {});
			
			$(this).append($div);
			
			$(".person_picture_box").fadeIn(200);
			
		}, function() {
			// 
		}).click(function() {
			$(".person_picture_box").fadeOut(200, function() {
				$(this).remove();
			});
	
		});
*/

		$(this).click(function() {

			if ($(this).hasClass("picture_show")) {
				$(this).removeClass("picture_show");
				$(".person_picture_box").fadeOut(200, function() {
					$(this).remove();
				});
				return;
			}

			$(".person_picture_box").remove();
			
			var $div = $("<div class='person_picture_box'></div>");
			$div.append("<img src='" + $(this).attr("person-picture-url") + "'>");
			$div.hide();
			$(this).append($div);
			$(".person_picture_box").fadeIn(200);
			
			$(this).addClass("picture_show");
			
		});

		
	});

	// add-start founder feihan
	$(".txt-nowrap").click(function () {
		$(this).toggleClass('nowrap');
	});
	// add-end founder feihan
});


</script>

{* add-start founder zouzhiyuan *}
<!-- デバイス通信ダイアログ(ドアの一時開錠) -->
<div id="groupsChangeModalTemplate" style="display:none">
	<div style="">
		<div style="height: 150px;">
			カメラ選択が初期化されますがよろしいですか？
		</div>
		<div class="dialog_btn_wrap btns center">
			<a href="javascript:void(0);" id="groupsChangeModalBtnCancel" class="btn btn_gray" >いいえ</a>
			<a href="javascript:void(0);" id="groupsChangeModalBtnOk"  class="btn btn_red">はい</a>
		</div>
	</div>
</div>
{* add-end founder zouzhiyuan *}

<!-- デバイス通信ダイアログ(人物の登録) -->
<div id="divicePersonRegistModalTemplate" style="display:none">

	<div class="diviceConnectModal_loading">
		ただいま[<span class="diviceConnectModal_device_name"></span>]への通信を行っています。<br />
		通信状況によっては時間がかかる場合があります。<br />
		このままでお待ちください。
		<div id="loading" class="loader">Loading...</div>
	</div>
	
	<div class="diviceConnectModal_error" style="display:none">
		<div class="diviceConnectModal_error_msg">
			[<span class="diviceConnectModal_device_name"></span>]への通信中にエラーが発生しました。<br />
			<br />
			<span class="diviceConnectModal_error_area"></span><br />
			<br />
			<div style="color:#000">
				カメラの電源や、カメラのオンライン状態もご確認下さい。また、ファームウェアバージョンが最新で無い場合にもエラーが出る場合があります。
			</div>
		</div>
		<div class="btn_3">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
			<a href="javascript:void(0);" onclick="registDevicePersonRetry()" class="btn btn_red">リトライする</a>
			<a href="javascript:void(0);" onclick="registDevicePersonNext()" class="btn btn_red next_device_label">次のカメラへ</a>
		</div>
		
	</div>

	<div class="diviceConnectModal_complete">
		<div class="trans_complete">
			下記の通りに処理が行われました。<br />
			＜成功＞<br />
			<span class="diviceConnectModal_success_names"></span><br />
			<div class="diviceConnectModal_error_names_area" style="display:none; color:red">
				＜失敗＞<br />
				<span class="diviceConnectModal_error_names"></span><br />
			</div>
		</div>
		<div class="btn_1">
			<a href="javascript:void(0);" onclick="registDevicePersonRemoveModal()" class="btn btn_gray">閉じる</a>
		</div>
	</div>
	
</div>

<form name="personForm" action="./" method="post">
	<input type="hidden" name="_form_session_key" value="{$form._form_session_key|default:""}">
	<input type="hidden" name="tab" value="{$form.tab|default:""}" />
	<input type="hidden" name="_p" />
	{* mod-start founder yaozhengbang *}
	<div class="tab_container">

		<ul class="tab_btn">
			{if empty($form.tab|default:"")}
				{$tabPersonArray = array("insert","list","bulk","export","trans")}
				{$form.tab = $tabPersonArray[array_search(true , $personTopMenuFlag)]}
			{/if}
			{if $personTopMenuFlag[0]}
			<li tab-name="insert" {if $form.tab == "insert"}class="active"{/if}>新規ユーザー登録</li>
			{/if}
			{if $personTopMenuFlag[1]}
			<li tab-name="list"   {if $form.tab == "list"  }class="active"{/if}>ユーザー情報一覧・変更</li>
			{/if}
			{if $personTopMenuFlag[2]}
			<li tab-name="bulk"   {if $form.tab == "bulk"  }class="active"{/if}>一括ユーザー登録</li>
			{/if}
			{if $personTopMenuFlag[3]}
			<li tab-name="export" {if $form.tab == "export"}class="active"{/if}>ユーザーデータのエクスポート</li>
			{/if}
			{if $personTopMenuFlag[4]}
			<li tab-name="trans"  {if $form.tab == "trans" }class="active"{/if}>カメラデータ移行・当て変え</li>
			{/if}
		</ul>

		<div class="tab_cnt_wrap">
			{if $personTopMenuFlag[0]}
			<div class="tab_cnt{if $form.tab == "insert"} show{/if}">{include file="./tab10_new.tpl"   }</div>
			{/if}
			{if $personTopMenuFlag[1]}
			<div class="tab_cnt{if $form.tab == "list"  } show{/if}">{include file="./tab20_list.tpl"  }</div>
			{/if}
			{if $personTopMenuFlag[2]}
			<div class="tab_cnt{if $form.tab == "bulk"  } show{/if}">{include file="./tab30_bulk.tpl"  }</div>
			{/if}
			{if $personTopMenuFlag[3]}
			<div class="tab_cnt{if $form.tab == "export"} show{/if}">{include file="./tab40_export.tpl"}</div>
			{/if}
			{if $personTopMenuFlag[4]}
			<div class="tab_cnt{if $form.tab == "trans" } show{/if}">{include file="./tab50_trans.tpl" }</div>
			{/if}
		</div>

	</div>
	{* mod-end founder yaozhengbang *}
</form>

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}