
<script>

function exportCheckRev(data) {
	
	if (data.export_checkExists) {
		$(".export_btn").removeClass("btn_disabled");
	} else {
		$(".export_btn").addClass("btn_disabled");
	}
}

// 単一のチェック。
function doThisCheck(elm) {
	
	doAjax("./exportCheck", {
		isLocalOnly : "1"
		, isCheckOn : $(elm).prop("checked") ? "1" : "0"
		, checkIds  : $(elm).val()
		, export_checkIdsKey : $("input[name='export_checkIdsKey']").val()
		
	}, exportCheckRev);
} 

// 一覧に出ているものに限り全てチェック。
function doLocalCheckAll(isCheckOn) {
	
	var ids = [];
	$(byNames("export_person_ids[]")).each(function() { 
		ids.push($(this).val()); 
		$(this).prop("checked", isCheckOn);
	});
	
	doAjax("./exportCheck", {
		isLocalOnly : "1"
		, isCheckOn : isCheckOn ? "1" : "0"
		, checkIds  : ids.join(",")
		, export_checkIdsKey : $("input[name='export_checkIdsKey']").val()

	}, exportCheckRev);

	
}

// 一覧に出ていないものを含む全てチェック。
function doServerCheckAll(isCheckOn) {

	$(byNames("export_person_ids[]")).each(function() { 
		$(this).prop("checked", isCheckOn);
	});

	
	doAjax("./exportCheck", {
		isLocalOnly : "0"
		, isCheckOn : isCheckOn ? "1" : "0"
		, export_searchFormKey : "{hjs($form.export_searchFormKey|default:"") nofilter}"
		, export_checkIdsKey   : $("input[name='export_checkIdsKey']").val()
		
	}, exportCheckRev);

}


// ダウンロード。
function doExportDownload(format) {

	showModal("ダウンロード", $("#export_download_modal_template").html(), null, function() {

		var checkProgress = function () {
			
			var check = function() {
				// mod-start founder feihan
				doAjax("./exportDownloadCheckProgress", null, function(result) {
					if (result && result.rowCount) {
						$(".export_progress").show();
						$(".export_progress progress").attr("max",   result.rowCount);
						$(".export_progress progress").attr("value", result.processed);
						setTimeout(check, 1500);
					} else {
						if(result && result.fileReadContinue){
							setTimeout(check, 1500);
						}else{
							$(".export_progress progress").attr("max",   "100");
							$(".export_progress progress").attr("value", "100");
							$(".export_progress").fadeOut(200, function() {
								removeModal();
							});
							return;
						}
					}
				});
				// mod-end founder feihan
			};
			
			setTimeout(check, 500);
			
		}
	
		$(".export_progress").fadeIn(400);
		$(".export_progress progress").attr("max", "100").attr("value", "0");
		location.href = "./exportDownload?export_checkIdsKey=" + $("input[name='export_checkIdsKey']").val() + "&export_format=" + format + "&export_searchFormKey=" + $("input[name='export_searchFormKey']").val();
		checkProgress();

	});

}

// add-start founder feihan
// 検索条件をリセット
function exportSearchInit(){
	$(".export_condition input[type=text]").val('');
	$(".export_condition input[type=checkbox]").prop('checked', false);
	$(".export_condition div.fs-label").text('');
	$(".export_condition div.fs-dropdown div.fs-options div.fs-option").removeClass('selected');
	// グループ選択を初期リセット
	if(groupsInit.length>0) {
		const $groupSelect = $("#export_m1");
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
	const $deviceSelect = $("#export_m2");
	$deviceSelect.empty();
	devices.forEach(device=>{
		{literal}
		$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
		{/literal}
	});
	$deviceSelect.data('fSelect').destroy();
	$deviceSelect.data('fSelect').create();
	$('#export_person_type_code option:first').prop("selected", 'selected');
}
// add-end founder feihan

{$prefix="export_"}
function doExportSearchPerson() {
	let exportDropdownIndex;
	$('.fs-dropdown').each(function(index) {
		if ($('.fs-dropdown').eq(index).next().attr('id').indexOf('export') > -1) {
			exportDropdownIndex = index;
			return false;
		} else {
			return true;
		}
	});
	if ((typeof(auth_group) === 'number') || $('.fs-dropdown').eq(exportDropdownIndex).hasClass('hidden')) {
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
				doGet('./exportSearch', true)
			} else {
				alert(JSON.stringify(res));
			}
		}, (errorRes) => {
			alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
		});
	}
}

</script>

<input type="hidden" name="export_searchFormKey" value="{$form.export_searchFormKey|default:""}" />
<input type="hidden" name="export_checkIdsKey" value="{$form.export_checkIdsKey|default:""}" />

<!-- ダウンロードモーダル -->
<div id="export_download_modal_template" style="display:none">
	ダウンロードデータの作成中です。<br >
	対象件数が多い場合、時間が掛かる場合があります。<br >
	<br />
	<div id="loading" class="loader">Loading...</div>
	<div class="export_progress" style="display:none"><progress style="width: 200px; "></progress></div>
	
</div>


<div class="search_area">
	{include file="./search_area.tpl" prefix="export_"}
	{* mod-start founder feihan*}
	<div class="userbtn_wrap">
		<a href="javascript:void(0)" onclick="$(byName('export_pageNo')).val(''); doExportSearchPerson()" class="enter-submit btn_red"><i class="fas fa-search"></i>ユーザーを検索</a>
		<a href="javascript:void(0)" onclick="exportSearchInit()" value="Reset" id="export_ResetBtn" class="btn_blue export_resetBtn">検索条件をリセット</a>
	</div>
	{* mod-end founder feihan*}
</div>

{if isset($export_list)}
	<div class="search_results">
		<div class="tit_wrap">
			<h3 class="tit">検索結果</h3>
			<p class="cap">ユーザーデータをエクセル、CSVでエクスポートすることができます。<br>
				それぞれの形式の「ダウンロード」ボタンを押してください。</p>
		</div>

		{* mod-start founder luyi *}
		{* {include file="../_inc/pager_person.tpl" pageInfo=$export_pageInfo topPager=true} *}
		{include file="../_inc/pager_counter_person.tpl" pageInfo=$export_pageInfo topPager=true}
		{* mod-end founder luyi *}
		
		この一覧に出ているデータについて
		<a href="javascript:void(0)" onclick="doLocalCheckAll(true)">全て選択</a> / <a href="javascript:void(0)" onclick="doLocalCheckAll(false)">全て解除</a>
		<br />
		全てのデータ（一覧に出ていないデータも含む）について
		<a href="javascript:void(0)" onclick="doServerCheckAll(true)">全て選択</a> / <a href="javascript:void(0)" onclick="doServerCheckAll(false)">全て解除</a>
	
		<table class="search_results_table">
			<tr>
				<th class="results_checkbox"></th>
				<th class="results_group">グループ</th>
				<th class="results_camera">カメラ</th>
				<th class="results_id">ID</th>
				<th class="results_name">氏名</th>
				<th class="results_cardIDs">ICカード番号</th>
				<th class="results_birthday">生年月日</th>
				{if Session::getLoginUser("enter_exit_mode_flag") == 1}
					<th class="results_personType">区分</th>
					<th class="results_enterExitDescriptionName1">{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}</th>
					<th class="results_enterExitDescriptionName2">{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}</th>
				{/if}
				<th class="results_registration_date">登録日</th>
				<th></th>
			</tr>
			{foreach $export_list as $item}
				<tr id="export_person_tr_{$item.person_id}">
					<td><input type="checkbox" name="export_person_ids[]" value="{$item.person_id}" onclick="doThisCheck(this)" value="user" id="{seqId()}" {if !empty($checkIds[$item.person_id])}checked{/if}><label for="{seqId(1)}" class="checkbox"></label></td>
					<td>{$item.device_group_names}</td>
					<td><div class="txt-nowrap nowrap">{$item.device_names}</div></td>
					<td class="personCode">{$item.personCode}</td>
					<td class="personName">{$item.personName}</td>
					<td>{$item.cardIDs}</td>
					<td>{$item.birthday}</td>
					{if Session::getLoginUser("enter_exit_mode_flag") == 1}
					    <td>{$item.personTypeName}</td>
						<td>{$item.person_description1}</td>
					    <td>{$item.person_description2}</td>
					{/if}
					<td>{formatDate($item.create_time)}</td>
					<td><a href="javascript:void(0)" class="person_picture_view" person-picture-url="{$item.pictureUrl}"><i class="fas fa-portrait"></i></a></td>
				</tr>
			{/foreach}
		</table>
	
	</div>

	{* mod-start founder luyi *}
	{* {include file="../_inc/pager_person.tpl" pageInfo=$export_pageInfo topPager=false} *}
	{include file="../_inc/pager_counter_person.tpl" pageInfo=$export_pageInfo topPager=false}
	{* mod-end founder luyi *}

	<div class="btn_wrap center">
		<a href="javascript:void(0)" onclick="doExportDownload('excel')" class="export_btn {if empty($checkIds)}btn_disabled{/if} btn_blue"><i class="fas fa-arrow-alt-from-top"></i>エクセル形式でダウンロード</a>
		<a href="javascript:void(0)" onclick="doExportDownload('csv')"   class="export_btn {if empty($checkIds)}btn_disabled{/if} btn_blue"><i class="fas fa-arrow-alt-from-top"></i>CSV形式でダウンロード</a>
	</div>
	<div class="btn_wrap" style="margin-top: 10px;">
		<p style="font-size:14px;">※CSVはFaceFCと互換性があるため、FaceFC管理画面でのインポートに利用可能です</p>
	</div>

{/if}