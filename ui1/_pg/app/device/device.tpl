{$title="端末設定"}{$icon="fa-cog"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}
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
	devices.push( { id: '{$d}', name: '{$device.name|escape:"javascript"}', 'deviceType': '{$device.device_type|escape:"javascript"}', 'groupId': '{$device.device_group_id}' } )
	{/foreach}
	const firmwareVeresions = []
	{foreach $firmwareVeresionNames as $n=>$firmwareVeresion}
	firmwareVeresions.push( { id: '{$n}', name: '{$n}', deviceTypeFlag: '{$firmwareVeresion.device_type_flag|escape:"javascript"}'})
	{/foreach}

</script>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">
{* add-end founder feihan *}
<script>

let auth_group = {Session::getLoginUser("group_id")}

// タブ制御
$(function() {
	$(".tab_btn li").click(function() {
		var url = "./";
		if ($(this).attr("tab-name")) url = "./?tab=" + $(this).attr("tab-name");
		history.pushState("", "", url);
		document.registForm.tab.value = $(this).attr("tab-name");
	});

});

// 送信。
function doPost(action, scrollSave) {
	if (scrollSave) {
		document.registForm._p.value = parseInt($(window).scrollTop()).toString(36);
	}
	document.registForm.action = action;
	document.registForm.submit();
}
function doGet(action, scrollSave) {
	// URLが長くなりすぎないように、値の無いinput類はdisabledにする。
	disableEmptyInput($(document.registForm))

	if (scrollSave) {
		document.registForm._p.value = parseInt($(window).scrollTop()).toString(36)
	}
	document.registForm.action = action
	document.registForm.method = 'get'
	document.registForm.submit()
}

// 設定を取得。
function getDeviceConfig(deviceIdSelectName, targetAreaId) {
	
	var $select = $("select[name=" + deviceIdSelectName + "]");
	var deviceId = $select.val();
	if (deviceId == "") return;
	
	showModal("カメラ設定の読み込み", $("#diviceConfigGetModalTemplate").html());
	$("#modal_message .diviceConnectModal_loading").show();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_device_name").text($select.find("option:selected").text());
	
	doAjax("./getConfigByDevice", { device_id: deviceId }, function(data) {
	
		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			$("#modal_message .diviceConnectModal_error_msg").append(escapeHtml(data.error));
			return;
		}
		
		setFormValue($("#" + targetAreaId), data);
		removeModal();
		
	});
	
}

// 設定の反映を開始。
var _processingRegistConfigDevice;
var _processingRegistConfigDeviceQueue;
var _processingRegistRecogConfigSetId;
var _processingRegistSystemConfigSetId;
var _processingRegistVersionName;
var _processingRegistType;
function registDeviceConfigBegin(deviceIdCheckboxName, type, recogConfigSetIdName, systemConfigSetIdName, verionNameName) {
	let recogIndex;
	let systemIndex;
	$('.fs-dropdown').each(function(index) {
		if ($('.fs-dropdown').eq(index).next().attr('id') !== undefined) {
			if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('recog2_m1') > -1) {
				recogIndex = index;
			} else if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('regist_system_m1') > -1) {
				systemIndex = index;
			}
		}
	});
if ((typeof(auth_group) === 'number') || ($('.fs-dropdown').eq(recogIndex).hasClass('hidden') && $('.fs-dropdown').eq(systemIndex).hasClass('hidden'))) {
	var $deviceOptions = $(byNames(deviceIdCheckboxName)).find(":selected");
	var recogConfigSetIdName  = $(byName(recogConfigSetIdName)).val();
	var systemConfigSetIdName = $(byName(systemConfigSetIdName)).val();
	var verionName            = $(byName(verionNameName)).val();
	
	var title = "";
	if (type == "fw") {
		title = "ファームウェアアップデート";
	} else {
		title = "カメラへ設定を登録";
	}
	showModal(title, $("#diviceConfigRegistModalTemplate").html());

	_processingRegistRecogConfigSetId = recogConfigSetIdName;
	_processingRegistSystemConfigSetId = systemConfigSetIdName;
	_processingRegistVersionName = verionName;
	_processingRegistType = type;
	_processingRegistConfigDeviceQueue = [];
	$deviceOptions.each(function() {
		_processingRegistConfigDeviceQueue.push({ id:$(this).val(), name:$(this).text() });
	});
	
	registDeviceConfigNext();
}
	
}

// 設定の反映をリトライ。
function registDeviceConfigRetry() {
	_processingRegistConfigDeviceQueue.unshift(_processingRegistConfigDevice);
	registDeviceConfigNext();
}

// 次のデバイスの設定の反映を実行。
function registDeviceConfigNext() {

	$("#modal_message .diviceConnectModal_loading").show();
	$("#modal_message .diviceConnectModal_error").hide();
	$("#modal_message .diviceConnectModal_complete").hide();

	var device = _processingRegistConfigDeviceQueue.shift();

	if (device == null) {
		// 全て終了。
		if ($("#modal_message .diviceConnectModal_error_names .error_device").length) {
			$("#modal_message .diviceConnectModal_error_names_area").show();
		}
		
		$("#modal_message .diviceConnectModal_loading").hide();
		$("#modal_message .diviceConnectModal_error").hide();
		$("#modal_message .diviceConnectModal_complete").show();
		return;
	}

	_processingRegistConfigDevice = device;
	
	// 次のデバイスへ。
	$("#modal_message .diviceConnectModal_device_name").text(device.name).hide().fadeIn(200);
	
	
	doAjax("./registConfigForDevice", { device_id: device.id, type: _processingRegistType, recog_config_set_id: _processingRegistRecogConfigSetId, system_config_set_id: _processingRegistSystemConfigSetId, version_name: _processingRegistVersionName }, function(data) {
		
		var $successArea = $("#modal_message .diviceConnectModal_success_names");
		var $errorArea   = $("#modal_message .diviceConnectModal_error_names");
		
		// エラー
		if (data.error) {
			$("#modal_message .diviceConnectModal_loading").hide();
			$("#modal_message .diviceConnectModal_error").show();
			$("#modal_message .diviceConnectModal_error_area").text(data.error);
			
			$errorArea.find(".device_" +  device.id).remove();
			$errorArea.append($("<span style='margin:0 0.5em' class='error_device device_" + device.id + "'></span>").text(device.name));
			return;
		}

		// 成功
		$errorArea.find(".device_" +  device.id).remove();
		$successArea.append($("<span style='margin:0 0.5em' class='device_" + device.id + "'></span>").text(device.name));
		registDeviceConfigNext();
	});
	
}



</script>
{* add-start founder feihan *}
<!-- カメラルダウン変更確認モーダル -->
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
{* add-end founder feihan *}
<!-- デバイス通信ダイアログ(設定の読み込み) -->
<div id="diviceConfigGetModalTemplate" style="display:none">

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
		</div>
		<div class="btn_1">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>
		
	</div>
	
</div>

<!-- デバイス通信ダイアログ(設定の登録) -->
<div id="diviceConfigRegistModalTemplate" style="display:none">

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
			<a href="javascript:void(0);" onclick="registDeviceConfigRetry()" class="btn btn_red">リトライする</a>
			<a href="javascript:void(0);" onclick="registDeviceConfigNext()" class="btn btn_red">次のカメラへ</a>
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
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>
	</div>
	
</div>


<form name="registForm" action="./" method="post">
	<input type="hidden" name="tab" value="{$form.tab|default:""}" />
	<input type="hidden" name="_p" />
	{* mod-start founder yaozhengbang *}
	<div class="tab_container">

		<ul class="tab_btn">

			{if empty($form.tab|default:"")}
				{* "groupInsert" と "insert" が同じ権限で利用出来ます *}
				{$tabDeviceArray = array("groupInsert","recog1","recog2","system1","system2","alerm")}
				{$form.tab = $tabDeviceArray[array_search(true , $deviceTopMenuFlag)]}
			{/if}
			{if $deviceTopMenuFlag[0]}
				<li tab-name="groupInsert"  {if $form.tab|default:"" == "groupInsert" }class="active"{/if}>カメラグループ設定</li>
			{/if}
			{if $deviceTopMenuFlag[0]}
				<li tab-name="insert"  {if $form.tab|default:"" == "insert" }class="active"{/if}>カメラ設定</li>
			{/if}
			{if $deviceTopMenuFlag[1]}
				<li tab-name="recog1"  {if $form.tab|default:"" == "recog1" }class="active"{/if}>認証関連基本設定・更新</li>
			{/if}
			{if $deviceTopMenuFlag[2]}
				<li tab-name="recog2"  {if $form.tab|default:"" == "recog2" }class="active"{/if}>認証関連設定割当</li>
			{/if}
			{if $deviceTopMenuFlag[3]}
				<li tab-name="system1" {if $form.tab|default:"" == "system1"}class="active"{/if}>システム基本設定・更新</li>
			{/if}
			{if $deviceTopMenuFlag[4]}
				<li tab-name="system2" {if $form.tab|default:"" == "system2"}class="active"{/if}>システム設定割当</li>
			{/if}
			{if ENABLE_AWS && $deviceTopMenuFlag[5]}
				<li tab-name="alerm"   {if $form.tab|default:"" == "alerm"  }class="active"{/if}>アラーム設定</li>
			{/if}

		</ul>

		<div class="tab_cnt_wrap">
			{if $deviceTopMenuFlag[0]}
			<div class="tab_cnt{if $form.tab|default:"" == "groupInsert" } show{/if}">{include file="./tab70_group.tpl"}</div>
			{/if}
			{if $deviceTopMenuFlag[0]}
			<div class="tab_cnt{if $form.tab|default:"" == "insert" } show{/if}">{include file="./tab10_camera.tpl"}</div>
			{/if}
			{if $deviceTopMenuFlag[1]}
			<div class="tab_cnt{if $form.tab|default:"" == "recog1" } show{/if}">{include file="./tab20_recog1.tpl"}</div>
			{/if}
			{if $deviceTopMenuFlag[2]}
			<div class="tab_cnt{if $form.tab|default:"" == "recog2" } show{/if}">{include file="./tab30_recog2.tpl"}</div>
			{/if}
			{if $deviceTopMenuFlag[3]}
			<div class="tab_cnt{if $form.tab|default:"" == "system1"} show{/if}">{include file="./tab40_system1.tpl"}</div>
			{/if}
			{if $deviceTopMenuFlag[4]}
			<div class="tab_cnt{if $form.tab|default:"" == "system2"} show{/if} system_allocation">{include file="./tab50_system2.tpl"}</div>
			{/if}
			{if ENABLE_AWS && $deviceTopMenuFlag[5]}
			<div class="tab_cnt{if $form.tab|default:"" == "alerm"  } show{/if}">{include file="./tab60_alerm.tpl"}</div>
			{/if}
		</div>
		{* mod-end founder yaozhengbang *}
	</div>

</form>


{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}