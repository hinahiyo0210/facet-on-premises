<script>

let auth_group = {Session::getLoginUser("group_id")}

$(function() {

	// デバイスへの登録を開始。
	{if !empty($mod_registDevicePersonCode)}
		
		setTimeout(function() {
			registDevicePersonBegin("mod", "{hjs($mod_registDevicePersonCode) nofilter}", {json_encode($mod_registDeviceTargets) nofilter});
		}, 2000);
		
	{/if}

	// デバイスからの削除を開始。
	{if !empty($del_registDevicePersonCode)}
		
		registDevicePersonBegin("del", "{hjs($del_registDevicePersonCode) nofilter}", {json_encode($del_registDeviceTargets) nofilter}, function() {
			
			var $successArea = $("#modal_message .diviceConnectModal_success_names");
			var $errorArea   = $("#modal_message .diviceConnectModal_error_names");
	
			$successArea.hide();
			$errorArea.hide();
			
			doAjax("./delPersonFromCloud", {
				list_del_personCode: "{hjs($del_registDevicePersonCode) nofilter}"
				
			}, function(data) {
				
				if (data.result == "OK") {
					$successArea.append($("<span style='margin:0 0.5em' class='cloud'></span>").text("クラウドサーバ"));
					
				} else {
					alert(data.msg);
					$errorArea.append($("<span style='margin:0 0.5em' class='cloud'></span>").text("クラウドサーバ"));
					$("#modal_message .diviceConnectModal_error_names_area").show();
				}
				
				$successArea.show();
				$errorArea.show();
			});
			
		}, false, function() {
			location.reload();
			
		});
		
	{/if}


});

// 人物の変更。
function doListModPerson(personId) {
	$('#list_modPersonId').val(personId);
	
	{if isset($list_list)}
		$("input[name='{$form.tab}_pageNo']").val("{$list_pageInfo->getPageNo()}");
	{/if}

	doPost('./modPersonInit', true);
} 

// 人物の削除。
function doListDeletePerson(personId) {

	$("input[name='list_del_personCode']").val($("#list_person_tr_" + personId + " .personCode").text());
	$("#list_del_personCode").text($("#list_person_tr_" + personId + " .personCode").text());
	$("#list_del_personName").text($("#list_person_tr_" + personId + " .personName").text());
	showModal("ユーザーの削除", $("#person_del_modal_template").html());

}

// 通行可能時間帯設定をクリア。
function clearAccessTimes() {

	$(".acces_times_input").val("");
	$(".acces_times_radio").each(function() {
		$(this).prop("checked", $(this).val() == "");
	});


}

// add-start founder feihan
// 検索条件をリセット
function listSearchInit(){
	$(".list_condition input[type=text]").val('');
	$(".list_condition input[type=checkbox]").prop('checked', false);
	$(".list_condition div.fs-label").text('');
	$(".list_condition div.fs-dropdown div.fs-options div.fs-option").removeClass('selected');
	// グループ選択を初期リセット
	if(groupsInit.length>0) {
		const $groupSelect = $("#list_m1");
		$groupSelect.empty();
		groupsInit.forEach(group => {
			{literal}
			$groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
			{/literal}
		});
		$groupSelect.data('fSelect').destroy();
		$groupSelect.data('fSelect').create();
	}
	// カメラ選択を初期リセット
	const $deviceSelect = $("#list_m2");
	$deviceSelect.empty();
	devices.forEach(device=>{
		{literal}
		$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
		{/literal}
	});
	$deviceSelect.data('fSelect').destroy();
	$deviceSelect.data('fSelect').create();
	$('#list_person_type_code option:first').prop("selected", 'selected');
}
// add-end founder feihan
{$prefix="list_"}
function doListSearchPerson() {
	let listDropdownIndex;
	$('.fs-dropdown').each(function(index) {
		if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('list') > -1) {
			listDropdownIndex = index;
			return false;
		} else {
			return true;
		}
	});
	if ((typeof(auth_group) === 'number') || ($('.fs-dropdown').eq(listDropdownIndex).length === 0 || $('.fs-dropdown').eq(listDropdownIndex).hasClass('hidden'))) {
		const $session_key = $('input[name="_form_session_key"]')
		if ($('#{$prefix}m1').length) {
			var data = [$('#{$prefix}m1'),$('#{$prefix}m2')].map($item => {
				return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
			})
		} else {
			var data = [$('#{$prefix}m2')].map($item => {
				return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
			})
		}
		doAjax('../session/setSession', {
			session_key: $session_key.val(),
			value: JSON.stringify(data)
		}, (res) => {
			if (!res.error) {
				$session_key.val(res['session_key'])
				$('input[name*="search_init"]').val(null)
				$('input[name="{$prefix}search_init"]').val(1)
				doGet('./listSearch', true)
			} else {
				alert(JSON.stringify(res));
			}
		}, (errorRes) => {
			alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
		});
	}
}


</script>

<input type="hidden" name="list_del_personCode" value="" />
<input type="hidden" name="list_del_back" value="{$form.list_del_back|default:""}" />

<!-- 人物削除確認モーダル -->
<div id="person_del_modal_template" style="display:none">
	このユーザーをクラウドシステムとカメラから削除します。<br >
	データは完全に削除されるため、もとに戻す事はできません。<br >
	本当によろしいですか？<br />
	<br />
	ID：<span id="list_del_personCode"></span><br />
	氏名：<span id="list_del_personName"></span><br />
	
	<div class="btns" style="margin-top:2em">
		<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		<a href="javascript:void(0)" onclick="doPost('./startDelPerson', false)" class="enter-submit btn btn_red btn_regist">削除</a>
	</div>
</div>


<!-- 人物変更モーダル -->
{if !empty($list_modPerson)}

	<script>
		$(function() {
			
			var openCallback = function() { 
			
				$("#person_mod_modal_template").remove();
				setBae64FileInput();
				$("input[name='list_mod_birthday']").flatpickr();
				
			};
			
			var closeCallback = null;
			var noClearError = true;
			var appndTarget = "form[name='personForm']";
			
			showModal("ユーザーの変更", $("#person_mod_modal_template").html(), "person_mod_modal", openCallback, closeCallback, noClearError, appndTarget);
		});
		
		function doChangeAccessTimeDevice(elm) {
			
			$(".access_time_device").hide();

			if ($(elm).val() == "") {
				return;
			}
			
			$(".access_time_device_" + $(elm).val()).fadeIn(200);
		}
		
		function doApbInFlagChane() {
			
			var inFlag = $("[name='list_mod_apb_in_flag']").prop("checked");
			
			// まず全てクリア。
			$(".acces_times_input").val("");
			$(".acces_times_radio").each(function() {
				$(this).prop("checked", $(this).val() == "");
			});
			
			if (inFlag) {
				// 入室中なのであれば、入室デバイスには禁止を。退室デバイスには許可をセットする。
				$(".apb_type_1").each(function() {
					$(this).find(".acces_times_radio[value='0']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_3").each(function() {
					$(this).find(".acces_times_radio[value='0']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_2").each(function() {
					$(this).find(".acces_times_radio[value='1']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});
				
			} else {
				// 退室中なのであれば、入室デバイスには許可を。退室デバイスには禁止をセットする。
				$(".apb_type_1").each(function() {
					$(this).find(".acces_times_radio[value='1']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_3").each(function() {
					$(this).find(".acces_times_radio[value='1']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});

				$(".apb_type_2").each(function() {
					$(this).find(".acces_times_radio[value='0']:eq(0)").prop("checked", true);
					$(this).find(".acces_times_input_from:eq(0)").val("2000/01/01 00:00");
					$(this).find(".acces_times_input_to:eq(0)").val("2099/12/31 23:59");
				});
				
			}
			
			
		}


		// 個別当て変えの実行
		function doModPerson() {
			const $session_key = $('input[name="_form_session_key"]')
			const dataObj ={ }
			const $access_times = $('div.session_access_time')
			$access_times.each(function () {
				const $this = $(this)
				const device_id = $this.data('deviceId')

				dataObj['list_mod_access_flag_' + device_id] || (dataObj['list_mod_access_flag_' + device_id] = [])
				dataObj['list_mod_access_flag_' + device_id].push($('input.acces_times_radio:checked',$this).val() || '')

				dataObj['list_mod_access_time_from_' + device_id] || (dataObj['list_mod_access_time_from_' + device_id] = [])
				dataObj['list_mod_access_time_from_' + device_id].push($('input.acces_times_input_from',$this).val() || '')

				dataObj['list_mod_access_time_to_' + device_id] || (dataObj['list_mod_access_time_to_' + device_id] = [])
				dataObj['list_mod_access_time_to_' + device_id].push($('input.acces_times_input_to',$this).val() || '')
			})
			if ($('#list_m1').length) {
				var data = [$('#list_m1'),$('#list_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				});
			} else {
				var data = [$('#list_m2')].map($item => {
					return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() }
				});
			}
			_.forOwn(dataObj,(v,k)=>{
				data.push({ key : k, value : v })
			})
			doAjax('../session/setSession', {
				session_key: $session_key.val(),
				value: JSON.stringify(data)
			}, (res) => {
				if (!res.error) {
					$session_key.val(res['session_key'])
					$('input', $access_times).attr("disabled", "disabled")
					doPost('./modPerson', true)
				} else {
					alert(JSON.stringify(res))
				}
      }, (errorRes) => {
        alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
      });
		}

	</script>

	<div id="person_mod_modal_template" style="display:none">
		<input type="hidden" name="list_mod_back" value="{$form.list_mod_back}" />
		<input type="hidden" name="list_pageNo" value="{$list_pageInfo->getPageNo()}" />
		<div class="person_mod_modal_inner">
			<table class="form_cnt regist_cnt">
				<tbody>
					<tr>
						<th>ID</th>
						<td>
							<input type="text" name="list_mod_personCode" value="{$form.list_mod_personCode}" readonly class="disabled" />
						</td>
					</tr>
					<tr>
						<th>氏名<span class="required">※</span></th>
						<td>
							<input type="text" name="list_mod_personName" value="{$form.list_mod_personName}" placeholder="名前を入力します" >
						</td>
					</tr>
					<tr>
						<th>生年月日</th>
						<td>
							<p class="select calendar">
								<i class="fas fa-calendar-week"></i>
								<input type="text" class="flatpickr flatpickr-input" data-position="below" data-allow-input="true" placeholder="1990/01/01" name="list_mod_birthday" value="{$form.list_mod_birthday}">
							</p>
						</td>
					</tr>

					{assign var="emptyCnt" value=0}
					{for $i = 0; $i < 3; $i++}
						<tr class="list_mod_cards" {if $i >= 1 && Validator::isEmpty($form['list_mod_card_id'][$i]|default:"")}  {$emptyCnt++}  style="display:none"{/if}>
							<th style="vertical-align: middle;padding-bottom: 15px;">カードID</th>
							<td>
								<input type="text" placeholder="カード番号を入力します" name="list_mod_card_id[{$i}]" value="{$form['list_mod_card_id'][{$i}]|default:""}">
							</td>
						</tr>
						<tr class="list_mod_cards" {if $i >= 1 && Validator::isEmpty($form['list_mod_card_id'][$i]|default:"")}style="display:none"{/if}>
							<th style="vertical-align: middle;padding-bottom: 15px;"><span>カード有効期間</span></th>
							<td>
								<div class="period">
									<div class="select calendar">
										<i class="fas fa-calendar-week"></i>
										<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d', strtotime('today -1 month'))}" name="list_mod_date_from[{$i}]" value="{$form['list_mod_date_from'][{$i}]|default:""}">
									</div>
									<span>〜</span>
									<div class="select calendar">
										<i class="fas fa-calendar-week"></i>
										<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d')}" name="list_mod_date_to[{$i}]" value="{$form['list_mod_date_to'][{$i}]|default:""}">
									</div>
								</div>
							</td>
						</tr>
					{/for}
					<tr><th></th>
						<td style="float: right;padding-bottom: 0;">
							{if $emptyCnt > 0 }<span><a href="javascript:void(0);" onclick="$('.list_mod_cards').not(':visible').slideDown(200); $(this).hide()">全件を表示</a></span>{/if}
						</td>
					</tr>
					{* add-start version3.0  founder feihan *}
					{if Session::getLoginUser("enter_exit_mode_flag") == 1}
						<tr>
							<th class="fs-select-th-center">区分</th>
							<td>
								<p class="select">
									<select name="list_mod_person_type_code">
										<option value=""></option>
										{foreach $personTypeList as $person_type_code=>$personType}
											<option {if $form.list_mod_person_type_code == $person_type_code}selected{/if} value="{$person_type_code}" >{$personType.person_type_name}</option>
										{/foreach}
									</select>
								</p>
							</td>
						</tr>
						<tr>
							<th>{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}</th>
							<td>
								<input type="text" name="list_mod_person_description1" value="{$form.list_mod_person_description1}" placeholder="{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}を入力します" >
							</td>
						</tr>
						<tr>
							<th>{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}</th>
							<td>
								<input type="text" name="list_mod_person_description2" value="{$form.list_mod_person_description2}" placeholder="{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}を入力します" >
							</td>
						</tr>
					{/if}
					{* add-end version3.0  founder feihan *}
					<tr>
						<th>画像登録</th>
						<td class="btn_file">
							<label for="list_mod_file_up"><i class="fas fa-arrow-to-top"></i>ファイルをアップロード<input type="file" class="base64_picture" img-target="#list_mod_picture_preview" set-target="input[name='list_mod_picture']" id="list_mod_file_up"></label>
							<div class="picture_preview" id="list_mod_picture_preview"></div><input type="hidden" name="list_mod_picture" value="{$form.list_mod_picture}" error-target="#list_mod_picture_preview">
							<p class="note">パソコンなどから画像をアップロードすることができます。<br>下記の撮影ガイドを参考に撮影してください。jpg画像が利用できます。</p>
							<a href="/ui1/static/manual/guide.pdf" target="_blank" class="link_pdf"><i class="fas fa-file-alt"></i>登録画像の撮影ガイド（PDF）</a>
						</td>
					</tr>
					{if Session::getLoginUser("apb_mode_flag")}
						<tr>
							<th>APB状態</th>
							<td>
								<input type="checkbox" onclick="doApbInFlagChane()" name="list_mod_apb_in_flag" value="1" id="{seqId()}" {if $form.list_mod_apb_in_flag|default:null == 1}checked{/if}><label for="{seqId(1)}" class="checkbox">入室中（退室可能）</label>
							</td>
						</tr>
					{/if}
					<tr>
						<th>通行可能時間帯</th>
						<td class="access_times">
							<p class="select access_time_device_select">
								<select name="list_mod_access_time_device" onchange="doChangeAccessTimeDevice(this)">
									<option value="" >カメラを選択</option>
									{if !empty($modDevice)}
										{foreach $modDevice as $d}
											<option {if $form.list_mod_access_time_device|default:"" == $d.device_id}selected{/if} value="{$d.device_id}">{$d.name}</option>
										{/foreach}
									{/if}
								</select>
								
							</p>
							<a id="clearAccessTimeLink" href="javascript:void(0);" onclick="clearAccessTimes()">全ての通行可能時間帯をクリア</a>
								
							
							{foreach $modDevice as $d}
								<div class="access_time_device access_time_device_{$d.device_id|default:""} apb_type_{$d.apb_type|default:""}" {if $form.list_mod_access_time_device|default:"" != $d.device_id|default:"" || empty($modDevice)}style="display:none"{/if}>
									{for $i = 0; $i < 10; $i++}
										<div class="acces_times_time session_access_time acces_times_time_{$d.device_id|default:""}" data-device-id="{$d.device_id|default:""}" {if $i >= 1 && Validator::isEmpty($form[$name][$i]|default:"")}style="display:none"{/if}>
											{$name=(isset($d.device_id)) ? "list_mod_access_flag_`$d.device_id`" : "list_mod_access_flag_"}
											<input {if $form[$name][$i]|default:null === null}checked{/if} id="{seqId()}" class="acces_times_radio" name="{$name}[{$i}]" type="radio" value="" ><label for="{seqId(1)}" class="radio">指定無し</label>
											<input {if $form[$name][$i]|default:null === "1" }checked{/if} id="{seqId()}" class="acces_times_radio" name="{$name}[{$i}]" type="radio" value="1"><label for="{seqId(1)}" class="radio">通行許可</label>
											<input {if $form[$name][$i]|default:null === "0" }checked{/if} id="{seqId()}" class="acces_times_radio" name="{$name}[{$i}]" type="radio" value="0"><label for="{seqId(1)}" class="radio">通行不可</label>
											<div class="access_times_items">
												<p class="select calendar">
													{$name=(isset($d.device_id)) ? "list_mod_access_time_from_`$d.device_id`" : "list_mod_access_time_from_"}
													<i class="fas fa-calendar-week"></i>
													<input type="text" class="flatpickr_time flatpickr-input acces_times_input acces_times_input_from" data-position="below" data-allow-input="true" placeholder="1990/01/01 10:00" name="{$name}[{$i}]" value="{$form[$name][$i]|default:""}">
												</p>
												<div class="kara">～</div>
												<p class="select calendar">
													{$name=(isset($d.device_id)) ? "list_mod_access_time_to_`$d.device_id`" : "list_mod_access_time_to_"}
													<i class="fas fa-calendar-week"></i>
													<input type="text" class="flatpickr_time flatpickr-input acces_times_input acces_times_input_to" data-position="below" data-allow-input="true" placeholder="1990/01/01 10:00" name="{$name}[{$i}]" value="{$form[$name][$i]|default:""}">
												</p>
											</div>
										</div>
									{/for}
									<a href="javascript:void(0);" onclick="$('.acces_times_time_{$d.device_id|default:''}').not(':visible').slideDown(200); $(this).hide()">全件を表示</a>
								</div>
							{/foreach}
						</td>
					</tr>
				</tbody>
			</table>

			<div class="btns" style="margin-top:2em">
				<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
				<a href="javascript:void(0)" onclick="doModPerson()" class="enter-submit btn btn_red btn_regist" enter-submit-target=".person_mod_modal">登録</a>
			</div>
			
		</div>
	</div>
{/if}		


<div class="search_area">
	{include file="./search_area.tpl" prefix="list_"}
	{* mod-start founder feihan*}
	<div class="userbtn_wrap">
		<a href="javascript:void(0)" onclick="doListSearchPerson()" class="enter-submit btn_red"><i class="fas fa-search"></i>ユーザーを検索</a>
		<a href="javascript:void(0)" onclick="listSearchInit()" value="Reset" id="list_ResetBtn" class="btn_blue list_resetBtn" >検索条件をリセット</a>
	</div>
	{* mod-end founder feihan*}
</div>

<input type="hidden" id="list_modPersonId" name="list_modPersonId" />

{if isset($list_list)}
	<div class="search_results">
		<div class="tit_wrap">
			<h3 class="tit">検索結果</h3>
			<p class="cap">
				右側の編集アイコンから、画像、情報の変更、ユーザーの削除が行えます。<br>
				<!--
				左側のチェックボックスにチェックを入れて、「所属データの一括更新」から、ユーザーの所属カメラを一括更新することができます。
				-->
				{if $personTopMenuFlag[4]}ユーザーの所属カメラを更新するには「<a href="./?tab=trans">カメラデータ移行・当て変え</a>」から行うことができます。{/if}
			</p>
		</div>

		{* mod-start founder zouzhiyuan *}
		{* {include file="../_inc/pager_person.tpl" pageInfo=$list_pageInfo topPager=true} *}
		{include file="../_inc/pager_counter_person.tpl" pageInfo=$list_pageInfo topPager=true}
		{* mod-end founder zouzhiyuan *}
		
		<table class="search_results_table">
			<tr>
				<th class="results_group">グループ</th>
				<th class="results_camera">カメラ</th>
				<th class="results_id">ID</th>
				<th class="results_name">氏名</th>
				{if Session::getLoginUser("apb_mode_flag")}
					<th class="results_name">APB状態</th>
				{/if}
				<th class="results_cardIDs">ICカード番号</th>
				<th class="results_birthday">生年月日</th>
				{if Session::getLoginUser("enter_exit_mode_flag") == 1}
				    <th class="results_personType">区分</th>
				    <th class="results_enterExitDescriptionName1">{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}</th>
				    <th class="results_enterExitDescriptionName2">{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}</th>
				{/if}
				<th class="results_registration_date">登録日</th>
				<th></th>
				{* add-start founder yaozhengbang *}
				{if empty(Session::getLoginUser("group_id"))}
					{if array_search("ユーザーの変更",Session::getUserFunctionAccess("function_name"))>-1 || Session::getLoginUser("user_flag") == 1}
					{* add-end founder yaozhengbang *}
					<th></th>
					{* add-start founder yaozhengbang *}
					{/if}

					{if array_search("ユーザーの削除",Session::getUserFunctionAccess("function_name"))>-1 || Session::getLoginUser("user_flag") == 1}
					{* add-end founder yaozhengbang *}
					<th></th>
					{* add-start founder yaozhengbang *}
					{/if}
				{/if}
				{* add-end founder yaozhengbang *}
			</tr>
			{foreach $list_list as $item}
				<tr id="list_person_tr_{$item.person_id}">
					<td>{$item.device_group_names}</td>
					<td><div class="txt-nowrap nowrap">{$item.device_names}</div></td>
					<td class="personCode">{$item.personCode}</td>
					<td class="personName">{$item.personName}</td>
					{if Session::getLoginUser("apb_mode_flag")}
						<td>{if $item.apb_in_flag}<span style="color:red">入室中（退室可能）</span>{else}入室可能{/if}</td>
					{/if}
					<td>{$item.cardIDs}</td>
					<td>{$item.birthday}</td>
					{if Session::getLoginUser("enter_exit_mode_flag") == 1}
					    <td>{$item.personTypeName}</td>
					    <td>{$item.person_description1}</td>
					    <td>{$item.person_description2}</td>
					{/if}
					<td>{formatDate($item.create_time)}</td>
					<td><a href="javascript:void(0)" class="person_picture_view" person-picture-url="{$item.pictureUrl}"><i class="fas fa-portrait"></i></a></td>
					{* add-start founder yaozhengbang *}
					{if empty(Session::getLoginUser("group_id"))}
						{if array_search("ユーザーの変更",Session::getUserFunctionAccess("function_name"))>-1 || Session::getLoginUser("user_flag") == 1}
						{* add-end founder yaozhengbang *}
						<td><a href="javascript:void(0)" onclick="doListModPerson('{$item.person_id}')"><i class="fas fa-edit"></i></a></td>
						{* add-start founder yaozhengbang *}
						{/if}

						{if array_search("ユーザーの削除",Session::getUserFunctionAccess("function_name"))>-1 || Session::getLoginUser("user_flag") == 1}
						{* add-end founder yaozhengbang *}
						<td><a href="javascript:void(0)" onclick="doListDeletePerson('{$item.person_id}')"><i class="fas fa-trash-alt"></i></a></td>
						{* add-start founder yaozhengbang *}
						{/if}
					{/if}
					{* add-end founder yaozhengbang *}
				</tr>
			{/foreach}
		</table>
	
	</div>

	{* mod-start founder zouzhiyuan *}
	{* {include file="../_inc/pager_person.tpl" pageInfo=$list_pageInfo topPager=false} *}
	{include file="../_inc/pager_counter_person.tpl" pageInfo=$list_pageInfo topPager=false}
	{* mod-end founder zouzhiyuan *}

{/if}