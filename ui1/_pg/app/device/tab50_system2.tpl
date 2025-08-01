<script>

// --------------- システム設定の反映
$(function() {
	{* add-start founder feihan *}
	$("#regist_system_m1").fSelect();
	$("#regist_system_m2").fSelect();

	$("#regist_system_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})

		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#regist_system_m2")
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
		if ($("#regist_system_m2").fSelectedValues().length && $("[name='regist_system_config_set_id']").val()) {
			$("#regist_system_set_btn").removeClass("btn_disabled");
		} else {
			$("#regist_system_set_btn").addClass("btn_disabled");
		}
	};
	$("[name='regist_system_config_set_id']").change(toggleBtn);
	$("#regist_system_m1").change(toggleBtn);
	$("#regist_system_m2").change(toggleBtn);
	{* add-end founder feihan *}

});
// --------------- ファームウェアの更新
$(function() {

	{* add-start founder feihan *}
	$("#regist_fw_m1").fSelect();
	$("#regist_fw_m2").fSelect();

	$("#regist_fw_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})

		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#regist_fw_m2")
			const dids = newVal.flatMap(gid=>groups[gid])
			$deviceSelect.empty()
			devices.forEach(device=>{
				if (dids.indexOf(device.id)===-1 || $("#device_type option:selected").text() !== device.deviceType) {
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
		if ($("#regist_fw_m2").fSelectedValues().length && $("[name='version_name']").val()) {
			$("#regist_fw_set_btn").removeClass("btn_disabled");
		} else {
			$("#regist_fw_set_btn").addClass("btn_disabled");
		}
	};
	$("[name='version_name']").change(toggleBtn);
	$("#regist_fw_m1").change(toggleBtn);
	$("#regist_fw_m2").change(toggleBtn);
	{* add-end founder feihan *}

	{* add-start verion3.0 founder feihan *}
	// 型番選択
	var deviceTypeSel = $("#device_type");
	deviceTypeSel.data("last",deviceTypeSel.val()).change(function(){
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		var old = deviceTypeSel.data('last');
		var now = deviceTypeSel.val();
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$('#device_type').find('option[value="'+old+'"]').prop('selected',true);
			removeModal();
		});
		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			deviceTypeSel.data('last',now);
			// ファームウェア
			const $versionSelect = $("#version_name");
			$versionSelect.empty();
			$versionSelect.append(`<option value="" selected></option>`);
			firmwareVeresions.filter(fwVersion=> $("#device_type").val()==fwVersion.deviceTypeFlag).forEach(fwVersion=>{
				{literal}
				$versionSelect.append(`<option value="${fwVersion.id}">${fwVersion.name}</option>`);
				{/literal}
			});
			// グループ
			const $groupSelect = $("#regist_fw_m1");
			$groupSelect.empty()
			groupsInit.forEach(group=>{
				var lastGourpId
				devices.forEach(device=>{
					if(lastGourpId !== group.id && (group.id === device.groupId || (device.groupId === "" && group.id==="-1")) && $("#device_type option:selected").text() === device.deviceType){
						{literal}
						$groupSelect.append(`<option value="${group.id}">${group.name}</option>`)
						lastGourpId = group.id
						{/literal}

					}
				})
			})
			if ($groupSelect.length) {
				$groupSelect.data('fSelect').destroy()
				$groupSelect.data('fSelect').create()
			}

			// カメラ
			const $deviceSelect = $("#regist_fw_m2")
			$deviceSelect.empty()
			if (!($groupSelect.length)) {
				devices.forEach(device=>{
					if($("#device_type option:selected").text() === device.deviceType){
						{literal}
						$deviceSelect.append(`<option value="${device.id}">${device.name}</option>`)
						{/literal}
					}
				})
			}
			$deviceSelect.data('fSelect').destroy()
			$deviceSelect.data('fSelect').create()
			removeModal();
		});
	});
	{* add-end verion3.0 founder feihan *}
});


</script>

<h2 class="tit_cnt_main">システム設定</h2>
<p class="cap_cnt_main">システム基本設定・更新で作成したセットを、カメラに割り当てます。</p>
<div class="tit_wrap">
	<h3 class="tit">システム基本設定選択</h3>
	<p class="cap">設定するセットを選択してください。</p>
</div>
<table class="form_cnt set_group">
	<tr><th>セット選択</th>
		<td>
			<p class="select">
				<select name="regist_system_config_set_id">
					<option value=""></option>
					{foreach $systemConfigSets as $set}
						<option {if $form.regist_system_config_set_id|default:"" == $set.system_config_set_id}selected{/if} value="{$set.system_config_set_id}">{$set.system_config_set_name}</option>
					{/foreach}
				</select>
			</p>
		</td>
	</tr>

	<tr class="allocation"><th class="tit">割当先</th></tr>
	{* add-start founder yaozhengbang *}
	{assign var=devicesDisplay1 value=[]}
	{if empty(Session::getLoginUser("group_id"))}
		{* add-end founder yaozhengbang *}
		<tr class="allocation"><th class="fs-select-th-center">グループ選択</th>
			<td>
				<p>
					{* mod-start founder feihan *}
					<select id="regist_system_m1" class="groups hidden" name="regist_system_config_set_group_ids[]" multiple="multiple">
						{foreach $groupsDisplay as $g=>$group}
							{$selected = ""}
							{if exists($form["regist_system_config_set_group_ids"]|default:"", $g)}
								{$selected = "selected"}
								{$devicesDisplay1=array_merge($devicesDisplay1,$group.deviceIds)}
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
		{$devicesDisplay1=array_keys($devices)}
	{/if}
	{* add-end founder yaozhengbang *}
	<tr class="allocation"><th class="fs-select-th-center">カメラを選択</th>
		<td>
{*			{foreach $devices as $d}*}
{*				<input type="checkbox" name="regist_system_set_device_ids[]" value="{$d.device_id}" id="{seqId()}"><label for="{seqId(1)}" class="checkbox">{$d.name}</label>*}
{*			{/foreach}*}
			{* mod-start founder feihan *}
			<select id="regist_system_m2" class="devices hidden" name="regist_system_set_device_ids[]" multiple="multiple">
				{foreach $devices as $d=>$device}
					{if exists($devicesDisplay1, $d)}
						<option value="{$d}" {if exists($form["regist_system_set_device_ids"]|default:"", $d)}selected{/if}>{$device.name}</option>
					{/if}
				{/foreach}
			</select>
			{* mod-end founder feihan *}
		</td>
	</tr>
</table>
<a id="regist_system_set_btn" href="javascript:void(0);" onclick="registDeviceConfigBegin('regist_system_set_device_ids[]', 'systemConfig', null, 'regist_system_config_set_id')" class="btn_red btn_disabled">カメラへ設定を登録</a>

{if ENABLE_AWS}
	<!-- 仮で入れます。後藤 -->
	<div style="margin-bottom:6em;"></div>
	
	<div class="tit_wrap">
		<h3 class="tit">ファームウェア更新</h3>
		<p class="cap">更新する端末を選択してください。</p>
	</div>
	<table class="form_cnt set_group">
		{* add-start version3.0 founder feihan *}
		<tr><th>型番選択</th>
			<td>
				<p class="select">
					<select id="device_type" name="new_device_type_id">
						<option value=""></option>
						{foreach $deviceTypes as $device_type_id=>$deviceType}
							<option {if $form.new_device_type_id|default:"" == $device_type_id}selected{/if} value="{$device_type_id}" >{$deviceType.device_type}</option>
						{/foreach}
					</select>
				</p>
			</td>
		</tr>
		{* add-end version3.0 founder feihan *}
		<tr><th>ファームウェア選択</th>
			<td>
				<p class="select">
					<select name="version_name" id="version_name">
						<option value=""></option>
						{foreach $firmwareVeresionNames as $n=>$firmwareVeresionName}
						{if $form.new_device_type_id|default:"" == $firmwareVeresionName.device_type_flag}
							<option {if $form.version_name|default:"" == $n}selected{/if} value="{$n}">{$n}</option>
						{/if}
						{/foreach}
					</select>
				</p>
			</td>
		</tr>
	
		{* add-start founder yaozhengbang *}
		{assign var=devicesDisplay2 value=[]}
		{if empty(Session::getLoginUser("group_id"))}
			{* add-end founder yaozhengbang *}
			<tr class="allocation"><th class="fs-select-th-center">グループ選択</th>
				<td>
					<p>
	{*					<select name="regist_fw_config_set_group_id">*}
	{*						<option value=""></option>*}
	{*						{foreach $groups as $group_id=>$group}*}
	{*							<option value="{$group_id}" device-ids="{join(",", $group.deviceIds)}">{$group.group_name}</option>*}
	{*						{/foreach}*}
	{*					</select>*}
						{* mod-start version3.0 founder feihan *}
						<select id="regist_fw_m1" class="groups" name="regist_fw_config_set_group_ids[]" multiple="multiple">
							{foreach $groupsDisplay as $g=>$group}
								{foreach $devices as $d=>$device}
									{if $g==$device.groupId|default:"" && $form["new_device_type"]|default:"" == $device.deviceType}
										{if exists($form["regist_fw_config_set_group_ids"]|default:"", $g)}
											{$devicesDisplay2=array_merge($devicesDisplay2,$group.deviceIds)}
										{/if}
										<option value="{$g}" >{$group.group_name}</option>
									{/if}
								{/foreach}
							{/foreach}
						</select>
						{* mod-end version3.0 founder feihan *}
					</p>
				</td>
			</tr>
			{* add-start founder yaozhengbang *}
		{else}
			{$devicesDisplay2=array_keys($devices)}
		{/if }
		{* add-end founder yaozhengbang *}
		<tr class="allocation"><th class="fs-select-th-center">カメラを選択</th>
			<td>
	{*			{foreach $devices as $d}*}
	{*				<input type="checkbox" name="regist_fw_set_device_ids[]" value="{$d.device_id}" id="{seqId()}"><label for="{seqId(1)}" class="checkbox">{$d.name}</label>*}
	{*			{/foreach}*}
				{* mod-start founder feihan *}
				<select id="regist_fw_m2" class="devices" name="regist_fw_set_device_ids[]" multiple="multiple">
					{foreach $devices as $d=>$device}
						{if exists($devicesDisplay2, $d)}
							{if empty(Session::getLoginUser("group_id"))}
							<option value="{$d}" data-device>{$device.name}</option>			
							{/if}
						{/if}
					{/foreach}
				</select>
				{* mod-end founder feihan *}
			</td>
		</tr>
	</table>
	
	<a id="regist_fw_set_btn" onclick="registDeviceConfigBegin('regist_fw_set_device_ids[]', 'fw', null, null, 'version_name')" class="btn_red btn_disabled">カメラのファームウェアを更新</a>	
{/if}
