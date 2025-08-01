<script>

	var _system_config_sets = {json_encode($systemConfigSets) nofilter};

	$(function() {
		const $select_group = $('select[name="system_config_set_group"]')
		const $select_device = $('select[name="system_config_set_device"]')
		const $devices_init = $select_device.clone()

		// プルダウン初期化
		$select_device.fSelect()

		{if empty(Session::getLoginUser("group_id"))}
		$select_group.fSelect()
		$select_group.on('pulldownChange', function() {
			// データ値の更新
			const $wrap = $(this).closest('.fs-wrap')
			$wrap.data('oldVal', $wrap.fSelectedValues())

			// プルダウン連動
			$($select_device).empty();
			$devices_init.children().each(function() {
				if (!($wrap.fSelectedValues())
						|| !($(this).val())
						|| ($(this).data('group-id') && $(this).data('group-id') == $wrap.fSelectedValues())) {
					$(this).clone().appendTo($select_device)
				}
			})
			$select_device.data('fSelect').destroy()
			$select_device.data('fSelect').create()
		})
		{/if}

		// 新規 or データ更新のラジオの同期
		$("input[name='system_config_set_name']").focus(function() {
			$("#system_regist_type_add").prop("checked", true);
		});

		$("select[name='system_config_set_id']").focus(function() {
			$("#system_regist_type_update").prop("checked", true);
		});

		// 選択された設定セットの値をセットする。
		$("select[name='system_config_set_id']").change(function() {
			var config = _system_config_sets[$(this).val()];
			if (config) {
				$("#system_set_delete_btn").show();
				setFormValue($("#system_config_set_area"), config);
			} else {
				$("#system_set_delete_btn").hide();
			}

		}){if $form.upd_srs|default:""}.change(){/if};


	});

	function deleteSystemConfigSet() {

		if ($("select[name='system_config_set_id']").val() == "") return;

		var $opion = $("select[name='system_config_set_id']").find("option:selected");
		var id = $opion.val();
		var name = $opion.text();

		var msg = "下記の設定セットが削除されます<br />よろしいですか？<br /><br />[" + escapeHtml(name) + "]<br />";
		msg += "<div class=\"btns\">";
		msg += "<a href=\"javascript:void(0);\" onclick=\"removeModal()\" class=\"btn btn_gray\">キャンセル</a>";
		msg += "<a href=\"javascript:void(0);\" onclick=\"doPost('./deletesystemConfigSet?system_config_set_id=" + id + "')\" class=\"btn btn_red\">OK</a>";
		msg += "</div>";
		showModal("削除の確認", msg);

	}

</script>


<div class="terminal_cnt">
	<h2 class="tit_cnt_main">システム設定</h2>
	<p class="cap_cnt_main">端末のボリューム、画面の明るさや日付等、カメラシステムに関する設定を行います。</p>
	<h3 class="tit_cnt_main">基本設定</h3>
	<p class="cap_cnt_main">基本のセットを登録・更新します。基本設定は各カメラに同一の設定を登録することが可能です。</p>
	<table class="form_cnt">
		<tr>
			<th><input {if empty($form.upd_srs|default:"") && $form.system_regist_type|default:"" == "add"}checked{/if} id="system_regist_type_add" name="system_regist_type" type="radio" value="add" /><label for="system_regist_type_add" class="radio">新規追加</label></th>
			<td><input name="system_config_set_name" type="text" placeholder="任意の設定名をご入力ください" value="{$form.system_config_set_name|default:""}"></td>
		</tr>
		{if !empty($systemConfigSets)}
			{$updId = base_convert(Filter::len($form.upd_srs|default:"", 10), 36, 10)}

			<tr>
				<th><input {if !empty($form.upd_srs|default:"") || $form.system_regist_type|default:"" == "update"}checked{/if} id="system_regist_type_update" name="system_regist_type" type="radio" value="update" /><label for="system_regist_type_update" class="radio">データ更新</label></th>
				<td>
					<p class="select">
						<select name="system_config_set_id">
							<option></option>
							{foreach $systemConfigSets as $set}
								<option {if $updId == $set.system_config_set_id || $form.system_config_set_id|default:"" == $set.system_config_set_id}selected{/if} value="{$set.system_config_set_id}">{$set.system_config_set_name}</option>
							{/foreach}
						</select>
						<a id="system_set_delete_btn" style="display: none" onclick="deleteSystemConfigSet()" href="javascript:void(0)">×</a>
					</p>
				</td>
			</tr>
		{/if}
	</table>
</div>

<div id="system_config_set_area">
	<div class="terminal_cnt set_detail">

		<h3 class="tit_cnt_main">カメラから設定を読み込む</h3>
		<table class="form_cnt set_group">
			{if empty(Session::getLoginUser("group_id"))}
				<tr><th class="fs-select-th-center">グループ選択</th>
					<td>
							<select name="system_config_set_group" class="device_group_select">
								<option value="">&nbsp;</option>
								{foreach $groups as $g}
									<option value="{$g.device_group_id}" device-ids="{join(",", $g.deviceIds)}">{$g.group_name}</option>
								{/foreach}
							</select>
					</td>
				</tr>
			{/if}
			<tr><th class="fs-select-th-center">カメラ選択</th>
				<td>
						<select name="system_config_set_device" class="btn_disable_switch" disable-target="#system_config_load_btn">
							<option value="">&nbsp;</option>
							{foreach $devices as $d}
								<option value="{$d.device_id}" data-group-id="{$d.device_group_id}">{$d.name}</option>
							{/foreach}
						</select>
				</td>
			</tr>
		</table>
		<a id="system_config_load_btn" href="javascript:void(0);" onclick="getDeviceConfig('system_config_set_device', 'system_config_set_area')" class="btn_red btn_set btn_disabled">設定を読み込む</a>

		<h3 class="tit_cnt_main">基本設定</h3>
		<table class="form_cnt">
			<tr>
				<th>音声ボリューム：0～100で設定</th>
				<td>
					<input name="deviceAudioVolume" type="number" class="mini_txt" value="{$form.deviceAudioVolume|default:""}" min="0" max="100">
				</td>
			</tr>
			<tr>
				<th>画面の明るさ：0～100で設定</th>
				<td>
					<input name="deviceScreenBrightness" type="number" class="mini_txt" value="{$form.deviceScreenBrightness|default:""}" min="0" max="100">
				</td>
			</tr>
			<tr>
				<th>LED照明の明るさ：0～100で設定</th>
				<td>
					<input name="deviceLedBrightness" type="number" class="mini_txt" min="0" max="100" value="{$form.deviceLedBrightness|default:""}" min="0" max="100">
				</td>
			</tr>
			<tr>
				<th>スクリーンセーバに入る時間：0～86400秒で設定</th>
				<td>
					<input name="deviceWorkstateTime" value="{$form.deviceWorkstateTime|default:""}" min="0" max="86400" type="number" class="mini_txt"><span class="supplement">秒</span>
				</td>
			</tr>
			<tr>
				<th>スタンバイに入る時間：0～86400秒で設定</th>
				<td>
					<input name="deviceStandbyTime" value="{$form.deviceStandbyTime|default:""}" Bmin="0" max="86400" type="number" class="mini_txt"><span class="supplement">秒</span>
				</td>
			</tr>
      <tr>
				<th>デバイス休止時カード認証機能：有効/無効</th>
				<td>
					<input type="radio" name="hibernateRecogEnable" value="1" {if $form.hibernateRecogEnable|default:null == 1}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">有効</label>
					<input type="radio" name="hibernateRecogEnable" value="0" {if $form.hibernateRecogEnable|default:null != 1}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">無効</label>
				</td>
			</tr>
      <tr>
				<th>デバイス休止中の表示メッセージ：</th>
				<td>
					<input type="text" name="hibernateTips" value="{$form.hibernateTips|default:""}" >
				</td>
			</tr>
		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">日付設定</h3>
		<table class="form_cnt">
			<tr>
				<th>NTP設定：有効/無効</th>
				<td>
					<input type="radio" name="ntpEnable" value="1" {if $form.ntpEnable|default:null == 1}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">有効</label>
					<input type="radio" name="ntpEnable" value="0" {if $form.ntpEnable|default:null != 1}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">無効</label>
				</td>
			</tr>
			<tr>
				<th>NTPサーバホスト：</th>
				<td>
					<input type="text" name="ntpHostName" value="{$form.ntpHostName|default:""}" >
				</td>
			</tr>
			<tr>
				<th>NTPサーバポート：（0～65535,デフォルト:123)</th>
				<td>
					<input type="number" name="ntpPort" value="{$form.ntpPort|default:""}" class="mini_txt" min="0" max="65535">
				</td>
			</tr>
			<tr>
				<th>時刻同期間隔：(1～1440分,デフォルト:60分)</th>
				<td>
					<input type="number" name="ntpInterval" value="{$form.ntpInterval|default:""}" class="mini_txt" min="1" max="1440"><span class="supplement">分</span>
				</td>
			</tr>
		</table>
	</div>

	<a href="javascript:void(0);" onclick="doPost('./registSystemConfigSet')" class="btn_red">登録</a>

</div>
