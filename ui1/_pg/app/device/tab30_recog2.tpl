<script>

$(function() {
	{* add-start founder feihan *}
	$("#recog2_m1").fSelect();
	$("#recog2_m2").fSelect();

	$("#recog2_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})

		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#recog2_m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1) {
					return
				}
				{literal}
				$deviceSelect.append(`<option value="${device.id}">${device.name}</option>`)
				{/literal}
			})
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal()
		})
	});
	var toggleBtn = function() {
		if ($("#recog2_m2").fSelectedValues().length && $("[name='regist_recog_config_set_id']").val()) {
			$("#regist_recog_set_btn").removeClass("btn_disabled");
		} else {
			$("#regist_recog_set_btn").addClass("btn_disabled");
		}
	};
	$("[name='regist_recog_config_set_id']").change(toggleBtn);
	$("#recog2_m1").change(toggleBtn);
	$("#recog2_m2").change(toggleBtn);
	{* add-end founder feihan *}
});
</script>

<h2 class="tit_cnt_main">認証関連設定の割当</h2>
<p class="cap_cnt_main">認証関連基本設定・更新で作成したセットをカメラに割り当てます。</p>
<div class="tit_wrap">
	<h3 class="tit">認証関連基本設定選択</h3>
	<p class="cap">設定するセットを選択してください。</p>
</div>
<table class="form_cnt set_group">
	<tr><th>セット選択</th>
		<td>
			<p class="select">
				<select name="regist_recog_config_set_id">
					<option value=""></option>
					{foreach $recogConfigSets as $set}
						<option {if $form.regist_recog_config_set_id|default:"" == $set.recog_config_set_id}selected{/if} value="{$set.recog_config_set_id}">{$set.recog_config_set_name}</option>
					{/foreach}
				</select>
			</p>
		</td>
	</tr>

	<tr class="allocation"><th class="tit">割当先</th></tr>
	{* add-start founder yaozhengbang *}
	{assign var=devicesDisplay value=[]}
	{if empty(Session::getLoginUser("group_id"))}
		{* add-end founder yaozhengbang *}
		<tr class="allocation"><th class="fs-select-th-center">グループ選択</th>
			<td>
				<p>
					{* mod-start founder feihan *}
					<select id="recog2_m1" class="groups hidden" name="regist_recog_config_set_group_ids[]" multiple="multiple">
						{foreach $groupsDisplay as $g=>$group}
							{$selected = ""}
							{if exists($form["regist_recog_config_set_group_ids"]|default:"", $g)}
								{$selected = "selected"}
								{$devicesDisplay=array_merge($devicesDisplay,$group.deviceIds)}
							{/if}
							<option value="{$g}" {$selected}>{$group.group_name}</option>
						{/foreach}
					</select>
					{* mod-end founder feihan *}
				</p>
			</td>
		</tr>
		{* add-start founder yaozhengbang *}
	{else}
		{$devicesDisplay=array_keys($devices)}
	{/if}
	{* add-end founder yaozhengbang *}
	<tr class="allocation"><th class="fs-select-th-center">カメラを選択</th>
		<td id="cameraSelect">
			{* mod-start founder feihan *}
			<select id="recog2_m2" class="devices hidden" name="regist_recog_set_device_ids[]" multiple="multiple">
				{foreach $devices as $d=>$device}
					{if exists($devicesDisplay, $d)}
						<option value="{$d}" {if exists($form["regist_recog_set_device_ids"]|default:"", $d)}selected{/if}>{$device.name}</option>
					{/if}
				{/foreach}
			</select>
			{* mod-end founder feihan *}
		</td>
	</tr>
</table>
<a id="regist_recog_set_btn" href="javascript:void(0);" onclick="registDeviceConfigBegin('regist_recog_set_device_ids[]', 'recogConfig', 'regist_recog_config_set_id', null)" class="btn_red btn_disabled">カメラへ設定を登録</a>
