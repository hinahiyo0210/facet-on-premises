<script>

$(function() {

	
	{* mod-start founder zouzhiyuan *}
	$("#new_m1").fSelect();
	$("#new_m2").fSelect();
	$("#new_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#new_m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1) {
					return
				}
				{literal}
				$deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
				{/literal}
			})
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal()
		})
	});
	{* mod-end founder zouzhiyuan *}

	
	// デバイスへの登録を開始。
	{if !empty($new_registDevicePersonCode)}
		
		setTimeout(function() {
			
			registDevicePersonBegin("new", "{hjs($new_registDevicePersonCode) nofilter}", {json_encode($new_registDeviceTargets) nofilter});
			
		}, 2000);
		
	{/if}
	
});

</script>

<h2 class="tit_cnt_main">新規ユーザー登録</h2>
<table class="form_cnt regist_cnt">
	<tr>
		<th>ID<span class="required">※</span></th>
		<td>
			<input type="text" placeholder="任意のID" name="new_personCode" value="{$form.new_personCode|default:""}">
			<p class="note red">IDは必須です。英数字と記号が利用可能です。<br>あとから変更はできません。</p>
		</td>
	</tr>
	<tr>
		<th>氏名<span class="required">※</span></th>
		<td>
			<input type="text" placeholder="名前を入力します" name="new_personName" value="{$form.new_personName|default:""}">
		</td>
	</tr>
	<tr><th>生年月日</th>
		<td>
			<p class="select calendar">
				<i class="fas fa-calendar-week"></i>
				<input type="text" class="flatpickr" data-position="above" data-allow-input="true" placeholder="1990/01/01" name="new_birthday" value="{$form.new_birthday|default:""}">
			</p>
		</td>
	</tr>
	{assign var="emptyCnt" value=0}
	{for $i = 0; $i < 3; $i++}
	<tr class="new_cards" {if $i >= 1 && Validator::isEmpty($form['new_card_id'][$i]|default:"")} {$emptyCnt++} style="display:none"{/if}>
		<th style="vertical-align: middle;padding-bottom: 40px;">カードID</th>
		<td>
			<input type="text" placeholder="カード番号を入力します" name="new_card_id[{$i}]" value="{$form['new_card_id'][{$i}]|default:""}">
		</td>
	</tr>
	<tr class="new_cards" {if $i >= 1 && Validator::isEmpty($form['new_card_id'][$i]|default:"")}style="display:none"{/if}>
		<th style="vertical-align: middle;padding-bottom: 40px;"><span>カード有効期間</span></th>
		<td>
			<div class="period">
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d', strtotime('today -1 month'))}" name="new_date_from[{$i}]" value="{$form['new_date_from'][{$i}]|default:""}">
				</div>
				<span>〜</span>
				<div class="select calendar">
					<i class="fas fa-calendar-week"></i>
					<input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d')}" name="new_date_to[{$i}]" value="{$form['new_date_to'][{$i}]|default:""}">
				</div>
			</div>
		</td>
	</tr>
	{/for}
	<tr><th></th>
		<td style="float: left;padding-bottom: 0;">
			{if $emptyCnt > 0 }<span style="padding-left: 558px"><a href="javascript:void(0);" onclick="$('.new_cards').not(':visible').slideDown(200); $(this).hide()">全件を表示</a></span>{/if}
		</td>
	</tr>
	{* add-start version3.0  founder feihan *}
	{if Session::getLoginUser("enter_exit_mode_flag") == 1}
	<tr>
		<th class="fs-select-th-center">区分</th>
		<td>
			<p class="select">
				<select name="new_person_type_code">
					<option value=""></option>
					{foreach $personTypeList as $person_type_code=>$personType}
						<option {if $form.new_person_type_code|default:"" == $person_type_code}selected{/if} value="{$person_type_code}" >{$personType.person_type_name}</option>
					{/foreach}
				</select>
			</p>
		</td>
	</tr>
	<tr>
		<th>{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}</th>
		<td>
			<input type="text" placeholder="{if $contractor.enter_exit_description_name1}{$contractor.enter_exit_description_name1}{else}備考1{/if}を入力します" name="new_description1" value="{$form.new_description1|default:""}">
		</td>
	</tr>
	<tr>
		<th>{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}</th>
		<td>
			<input type="text" placeholder="{if $contractor.enter_exit_description_name2}{$contractor.enter_exit_description_name2}{else}備考2{/if}を入力します" name="new_description2" value="{$form.new_description2|default:""}">
		</td>
	</tr>
	{/if}
	{* add-end version3.0  founder feihan *}
	<tr>
		<th>画像登録</th>
		<td class="btn_file">
			<label for="{seqId()}"><i class="fas fa-arrow-to-top"></i>ファイルをアップロード<input type="file" class="base64_picture" accept=".jpg,.jpeg" img-target="#new_picture_preview" set-target="input[name='new_picture']" id="{seqId(1)}"></label>
			<div class="picture_preview" id="new_picture_preview"></div><input type="hidden" name="new_picture" value="{$form.new_picture|default:""}" error-target="#new_picture_preview" post-only="./registPerson">
			<p class="note">パソコンなどから画像をアップロードすることができます。<br>下記の撮影ガイドを参考に撮影してください。jpg画像が利用できます。</p>
			<a href="/ui1/static/manual/guide.pdf" target="_blank" class="link_pdf"><i class="fas fa-file-alt"></i>登録画像の撮影ガイド（PDF）</a>
		</td>
	</tr>
	
	<tr><th class="tit">登録カメラの選択</th><td class="tit_cap">どのカメラにユーザーを登録するか選択します。</td></tr>
	{* mod-start founder zouzhiyuan *}
	{assign var=devicesDisplay value=[]}
	{if empty(Session::getLoginUser("group_id"))}
		<tr><th class="fs-select-th-center">グループ選択</th>
			<td>
{*				<p class="select">*}
					<select id="new_m1" class="groups" name="new_group_ids[]" multiple="multiple">
						{foreach $groupsDisplay as $g=>$group}
							{$selected = ""}
							{if exists($form["new_group_ids"]|default:"", $g)}
								{$selected = "selected"}
								{$devicesDisplay=array_merge($devicesDisplay,$group.deviceIds)}
							{/if}
							<option value="{$g}" {$selected}>{$group.group_name}</option>
						{/foreach}
					</select>
{*				</p>*}
			</td>
		</tr>
	{else}
		{$devicesDisplay=array_keys($devices)}
	{/if}
	<tr><th class="fs-select-th-center">カメラ選択</th>
		<td>
			<select id="new_m2" class="devices" name="new_device_ids[]" multiple="multiple">
				{foreach $devices as $d=>$device}
					{if exists($devicesDisplay, $d)}
						<option value="{$d}" {if exists($form["new_device_ids"]|default:"", $d)}selected{/if}>{$device.name}</option>
					{/if}
				{/foreach}
			</select>
		</td>
	</tr>
	{* mod-end founder zouzhiyuan *}
</table>
<a href="javascript:void(0)" onclick="doPost('./registPerson', false)" class="enter-submit btn_red btn_regist">登録</a>

