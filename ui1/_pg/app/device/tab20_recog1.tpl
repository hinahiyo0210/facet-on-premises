<script>

	var _recog_config_sets = {json_encode($recogConfigSets) nofilter};

	$(function() {
		const $select_group = $('select[name="recog_config_set_group"]');
		const $select_device = $('select[name="recog_config_set_device"]');
		const $devices_init = $select_device.clone();

		// プルダウン初期化
		$select_device.fSelect();

		{if empty(Session::getLoginUser("group_id"))}
		$select_group.fSelect()
		$select_group.on('pulldownChange', function() {
			// データ値の更新
			const $wrap = $(this).closest('.fs-wrap')
			$wrap.data('oldVal', $wrap.fSelectedValues())

			// プルダウン連動
			$($select_device).empty()
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
		$("input[name='recog_config_set_name']").focus(function() {
			$("#recog_regist_type_add").prop("checked", true);
		});

		$("select[name='recog_config_set_id']").focus(function() {
			$("#recog_regist_type_update").prop("checked", true);
		});

		// 選択された設定セットの値をセットする。
		$("select[name='recog_config_set_id']").change(function() {
			var config = _recog_config_sets[$(this).val()];
			if (config) {
				$("#recog_set_delete_btn").show();
				setFormValue($("#recog_config_set_area"), config);
			} else {
				$("#recog_set_delete_btn").hide();
			}

		}){if $form.upd_rrs|default:""}.change(){/if};

	});

	function deleteRecogConfigSet() {

		if ($("select[name='recog_config_set_id']").val() == "") return;

		var $opion = $("select[name='recog_config_set_id']").find("option:selected");
		var id = $opion.val();
		var name = $opion.text();

		var msg = "下記の設定セットが削除されます<br />よろしいですか？<br /><br />[" + escapeHtml(name) + "]<br />";
		msg += "<div class=\"btns\">";
		msg += "<a href=\"javascript:void(0);\" onclick=\"removeModal()\" class=\"btn btn_gray\">キャンセル</a>";
		msg += "<a href=\"javascript:void(0);\" onclick=\"doPost('./deleteRecogConfigSet?recog_config_set_id=" + id + "')\" class=\"btn btn_red\">OK</a>";
		msg += "</div>";
		showModal("削除の確認", msg);

	}

</script>

<div class="terminal_cnt">
	<h2 class="tit_cnt_main">認証関連設定</h2>
	<p class="cap_cnt_main">顔認証の設定、温度検知設定、アラートの設定など、認証に関する設定を行います。</p>
	<h3 class="tit_cnt_main">基本設定</h3>
	<p class="cap_cnt_main">基本のセットを登録・更新します。基本設定は各カメラに同一の設定を登録することが可能です。<br><span style="font-weight: bold;">※FaceFCのファームウェアバージョンが古いと正しく読み込めない場合があります。最新のファームウェアをご利用ください。</span></p>
	<table class="form_cnt">
		<tr>
			<th><input {if empty($form.upd_rrs|default:"") && $form.recog_regist_type|default:"" == "add"}checked{/if} id="recog_regist_type_add" name="recog_regist_type" type="radio" value="add" /><label for="recog_regist_type_add" class="radio">新規追加</label></th>
			<td><input name="recog_config_set_name" type="text" placeholder="任意の設定名をご入力ください" value="{$form.recog_config_set_name|default:""}"></td>
		</tr>
		{if !empty($recogConfigSets)}
			{$updId = base_convert(Filter::len($form.upd_rrs|default:"", 10), 36, 10)}

			<tr>
				<th><input {if !empty($form.upd_rrs|default:"") || $form.recog_regist_type|default:"" == "update"}checked{/if} id="recog_regist_type_update" name="recog_regist_type" type="radio" value="update" /><label for="recog_regist_type_update" class="radio">データ更新</label></th>
				<td>
					<p class="select">
						<select name="recog_config_set_id">
							<option></option>
							{foreach $recogConfigSets as $set}
								<option {if $updId == $set.recog_config_set_id || $form.recog_config_set_id|default:"" == $set.recog_config_set_id}selected{/if} value="{$set.recog_config_set_id}">{$set.recog_config_set_name|default:""}</option>
							{/foreach}
						</select>
						<a id="recog_set_delete_btn" style="display: none" onclick="deleteRecogConfigSet()" href="javascript:void(0)">×</a>
					</p>
				</td>
			</tr>
		{/if}
	</table>
</div>

<div id="recog_config_set_area">

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">カメラから設定を読み込む</h3>
		<table class="form_cnt set_group">
			{if empty(Session::getLoginUser("group_id"))}
				<tr><th class="fs-select-th-center">グループ選択</th>
					<td>
							<select name="recog_config_set_group" class="device_group_select">
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
						<select name="recog_config_set_device" class="btn_disable_switch" disable-target="#recog_config_load_btn">
							<option value="">&nbsp;</option>
							{foreach $devices as $d}
								<option value="{$d.device_id}" data-group-id="{$d.device_group_id}">{$d.name}</option>
							{/foreach}
						</select>
				</td>
			</tr>
		</table>
		<a id="recog_config_load_btn" href="javascript:void(0);" class="btn_red btn_set btn_disabled" onclick="getDeviceConfig('recog_config_set_device', 'recog_config_set_area')">設定を読み込む</a>
	</div>

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">ディスプレイ表示</h3>
		<table class="form_cnt">
			<tr>
				<th>会社名/団体名/イベント名など</th>
				<td>
					<input type="text" name="dispInfo" value="{$form.dispInfo|default:""}">
				</td>
			</tr>
			<tr>
				<th rowspan="3">認識人物の情報</th>
				<td>
					<p class="td_cap">氏名表示</p>
					<input type="radio" name="dispShowName" value="1" id="{seqId()}" {if $form.dispShowName|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowName" value="0" id="{seqId()}" {if $form.dispShowName|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">ID表示</p>
					<input type="radio" name="dispShowID" value="1" id="{seqId()}" {if $form.dispShowID|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowID" value="0" id="{seqId()}" {if $form.dispShowID|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">登録写真表示</p>
					<input type="radio" name="dispShowPhoto" value="1" id="{seqId()}" {if $form.dispShowPhoto|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowPhoto" value="0" id="{seqId()}" {if $form.dispShowPhoto|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>

			<tr>
				<th rowspan="5">カメラの情報</th>
				<td>
					<p class="td_cap">IPアドレス表示</p>
					<input type="radio" name="dispShowIp" value="1" id="{seqId()}" {if $form.dispShowIp|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowIp" value="0" id="{seqId()}" {if $form.dispShowIp|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">シリアルNo表示</p>
					<input type="radio" name="dispShowSerailNo" value="1" id="{seqId()}" {if $form.dispShowSerailNo|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowSerailNo" value="0" id="{seqId()}" {if $form.dispShowSerailNo|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">ファームウェアバージョン表示</p>
					<input type="radio" name="dispShowVersion" value="1" id="{seqId()}" {if $form.dispShowVersion|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowVersion" value="0" id="{seqId()}" {if $form.dispShowVersion|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">登録人物データ数表示</p>
					<input type="radio" name="dispShowPersonInfo" value="1" id="{seqId()}" {if $form.dispShowPersonInfo|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowPersonInfo" value="0" id="{seqId()}" {if $form.dispShowPersonInfo|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">オフライン人数表示</p>
					<input type="radio" name="dispShowOfflineData" value="1" id="{seqId()}" {if $form.dispShowOfflineData|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="dispShowOfflineData" value="0" id="{seqId()}" {if $form.dispShowOfflineData|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">顔認証</h3>
		<table class="form_cnt">
			<tr>
				<th rowspan="4">認証成功/失敗時のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">成功時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="tipsCustom" value="{$form.tipsCustom|default:""}">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">成功時のメッセージ背景色</p>
					<p class="select">
						<select name="tipsBackgroundColor">
							{foreach Enums::tipsBackgroundColor() as $k=>$v}
								<option value="{$k}" {if $k == $form.tipsBackgroundColor|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="strangerTipsCustom" value="{$form.strangerTipsCustom|default:""}">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ背景色</p>
					<p class="select">
						<select name="strangerTipsBackgroundColor">
							{foreach Enums::tipsBackgroundColor() as $k=>$v}
								<option value="{$k}" {if $k == $form.strangerTipsBackgroundColor|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<th rowspan="2">認証成功/失敗時の音声再生設定</th>
				<td>
					<p class="td_cap">成功時の音声再生</p>
					<input type="radio" name="tipsVoiceEnable" value="1" id="{seqId()}" {if $form.tipsVoiceEnable|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="tipsVoiceEnable" value="0" id="{seqId()}" {if $form.tipsVoiceEnable|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時の音声再生</p>
					<input type="radio" name="strangerVoiceEnable" value="1" id="{seqId()}" {if $form.strangerVoiceEnable|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="strangerVoiceEnable" value="0" id="{seqId()}" {if $form.strangerVoiceEnable|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main"> カード認証<span style="font-size: 0.7rem;">（カード認証の設定はFaceFCのver96より対応しているため、ご利用の場合は最新のファームウェアをご利用ください）</span></h3>
		<p></p>
		<table class="form_cnt">
			<tr>
				<th rowspan="4">認証成功/失敗時のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">成功時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="tipsCustomCard" value="{$form.tipsCustomCard|default:""}">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">成功時のメッセージ背景色</p>
					<p class="select">
						<select name="tipsBackgroundColorCard">
							{foreach Enums::tipsBackgroundColor() as $k=>$v}
								<option value="{$k}" {if $k == $form.tipsBackgroundColorCard|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="strangerTipsCustomCard" value="{$form.strangerTipsCustomCard|default:""}">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時のメッセージ背景色</p>
					<p class="select">
						<select name="strangerTipsBackgroundColorCard">
							{foreach Enums::tipsBackgroundColor() as $k=>$v}
								<option value="{$k}" {if $k == $form.strangerTipsBackgroundColorCard|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<th rowspan="2">認証成功/失敗時の音声再生設定</th>
				<td>
					<p class="td_cap">成功時の音声再生</p>
					<input type="radio" name="tipsVoiceEnableCard" value="1" id="{seqId()}" {if $form.tipsVoiceEnableCard|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="tipsVoiceEnableCard" value="0" id="{seqId()}" {if $form.tipsVoiceEnableCard|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">失敗時の音声再生</p>
					<input type="radio" name="strangerVoiceEnableCard" value="1" id="{seqId()}" {if $form.strangerVoiceEnableCard|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="strangerVoiceEnableCard" value="0" id="{seqId()}" {if $form.strangerVoiceEnableCard|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">識別設定</h3>
		<table class="form_cnt">
      <tr>
          <th rowspan="6">認識精度</th>
          <td>
            <p class="td_cap">識別距離(0.5メートルから2メートルの範囲)</p>
            <p class="select">
              <select name="recogWorkstateTime">
                {for $i = 5 to 20 step 5}
                  {$val=sprintf("%.1f", $i / 10)}
                  <option value="{$val}" {if $val == $form.recogWorkstateTime|default:null}selected{/if}>{$val}</option>
                {/for}
              </select>
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">識別レベル</p>
            <p class="select">
              <select name="recogLiveness">
                {foreach Enums::recogLiveness() as $k=>$v}
                  <option value="{$k}" {if $k == $form.recogLiveness|default:null}selected{/if}>{$v}</option>
                {/foreach}
              </select>
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">識別間隔秒(0秒～10秒)</p>
            <input type="number" name="recogCircleInterval" value="{$form.recogCircleInterval|default:""}" min="0" max="10" class="mini_txt">
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">認識比較閾値(0～100)</p>
            <input type="number" name="recogSearchThreshold" value="{$form.recogSearchThreshold|default:""}" min="0" max="100" class="mini_txt">
          </td>
        </tr>
        <tr>
          <td>
            <p class="td_cap">マスク検出時の認識比較閾値(0～100)</p>
            <input type="number" name="recogMouthoccThreshold" value="{$form.recogMouthoccThreshold|default:""}" min="0" max="100" class="mini_txt">
          </td>
        </tr>
        {* add-start founder luyi *}
        <tr>
          <td>
            <p class="td_cap">顔写真登録時の警告類似度(0～100)</p>
            <input type="number" name="captureAlarteThreshold" value="{$form.captureAlarteThreshold|default:""}" min="0" max="100" class="mini_txt">
          </td>
        </tr>
        {* add-end founder luyi *}
		</table>
	</div>

	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">マスク検出</h3>
		<table class="form_cnt">

			<tr>
				<th>入場判定</th>
				<td>
					<p class="select">
						<select name="maskDetectMode">
							{foreach Enums::maskDetectMode() as $k=>$v}
								<option value="{$k}" {if $k == $form.maskDetectMode|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>

			<tr>
				<th>マスク検出モード</th>
				<td>
					<input type="radio" name="maskFaceAttrSwitch" value="0" {if $form.maskFaceAttrSwitch|default:null == 0}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">口のみ覆うも許可する</label>
					<input type="radio" name="maskFaceAttrSwitch" value="1" {if $form.maskFaceAttrSwitch|default:null == 1}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">鼻と口の両方を覆う</label>
				</td>
			</tr>


			<tr>
				<th rowspan="4">マスク検出のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">マスク装着者の通知メッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="maskWearShowTips" value="{$form.maskWearShowTips|default:""}">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク装着者の通知テキスト背景色</p>
					<p class="select">
						<select name="maskWearShowBackgroundColor">
							{foreach Enums::maskShowBackgroundColor() as $k=>$v}
								<option value="{$k}" {if $k == $form.maskWearShowBackgroundColor|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク非装着者の通知メッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="maskNowearShowTips" value="{$form.maskNowearShowTips|default:""}">
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク非装着者の通知テキスト背景色</p>
					<p class="select">
						<select name="maskNowearShowBackgroundColor">
							{foreach Enums::maskShowBackgroundColor() as $k=>$v}
								<option value="{$k}" {if $k == $form.maskNowearShowBackgroundColor|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>


			<tr>
				<th rowspan="2">マスク検出の音声通知設定</th>
				<td>
					<p class="td_cap">マスク装着者の音声通知</p>
					<input type="radio" name="maskWearVoiceEnable" value="1" id="{seqId()}" {if $form.maskWearVoiceEnable|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="maskWearVoiceEnable" value="0" id="{seqId()}" {if $form.maskWearVoiceEnable|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">マスク非装着者の音声通知</p>
					<input type="radio" name="maskNowearVoiceEnable" value="1" id="{seqId()}" {if $form.maskNowearVoiceEnable|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="maskNowearVoiceEnable" value="0" id="{seqId()}" {if $form.maskNowearVoiceEnable|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>

		</table>
	</div>
	<div class="terminal_cnt set_detail">
		<h3 class="tit_cnt_main">温度検出</h3>
		<table class="form_cnt">

			<tr>
				<th>有効/無効</th>
				<td>
					<input type="radio" name="tempEnable" value="1" id="{seqId()}" {if $form.tempEnable|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">温度検知を有効にする</label>
					<input type="radio" name="tempEnable" value="0" id="{seqId()}" {if $form.tempEnable|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">温度検知を無効にする</label>
				</td>
			</tr>

			<tr>
				<th>入場判定</th>
				<td>
					<p class="select">
						<select name="tempDetectMode">
							{foreach Enums::tempDetectMode() as $k=>$v}
								<option value="{$k}" {if $k == $form.tempDetectMode|default:null}selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</p>
				</td>
			</tr>

			<tr>
				<th rowspan="2">温度検出のディスプレイ通知設定</th>
				<td>
					<p class="td_cap">温度正常者の通知メッセージ(空登録の場合は通知無し)</p>
					<input type="text" name="tempNormalShowTips" value="{$form.tempNormalShowTips|default:""}">
				</td>
			</tr>
			<td>
				<p class="td_cap">温度異常者の通知メッセージ(空登録の場合は通知無し)</p>
				<input type="text" name="tempAbnormalShowTips" value="{$form.tempAbnormalShowTips|default:""}">
			</td>
			</tr>

			<tr>
				<th rowspan="2">温度検出の音声通知設定</th>
				<td>
					<p class="td_cap">温度正常者の音声通知</p>
					<input type="radio" name="tempNormalVoiceEnable" value="1" id="{seqId()}" {if $form.tempNormalVoiceEnable|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="tempNormalVoiceEnable" value="0" id="{seqId()}" {if $form.tempNormalVoiceEnable|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			<tr>
				<td>
					<p class="td_cap">温度異常者の音声通知</p>
					<input type="radio" name="tempAbnormalVoiceEnable" value="1" id="{seqId()}" {if $form.tempAbnormalVoiceEnable|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="tempAbnormalVoiceEnable" value="0" id="{seqId()}" {if $form.tempAbnormalVoiceEnable|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>

			<tr>
				<th rowspan="3">温度検知設定</th>
				<td>
					<p class="td_cap">正常温度設定(デフォルト：35.5～37.3)</p>

					<div class="inline_select">
						<p class="select">
							<select name="tempValueRangeFrom">
								{for $i = 100 to 420}
									{$val=sprintf("%.1f", $i / 10)}
									<option value="{$val}" {if $val == $form.tempValueRangeFrom|default:null}selected{/if}>{$val}</option>
								{/for}
							</select>
						</p>
						<div>～</div>
						<p class="select">
							<select name="tempValueRangeTo">
								{for $i = 100 to 420}
									{$val=sprintf("%.1f", $i / 10)}
									<option value="{$val}" {if $val == $form.tempValueRangeTo|default:null}selected{/if}>{$val}</option>
								{/for}
							</select>
						</p>
					</div>
				</td>
			</tr>

			<tr>
				<td>
					<p class="td_cap">温度補正(デフォルト:0.0)</p>
					<p class="select">
						<select name="tempCorrection">
							{for $i = -50 to 50}
								{$val=sprintf("%.1f", $i / 10)}
								<option value="{$val}" {if $val == $form.tempCorrection|default:null}selected{/if}>{$val}</option>
							{/for}
						</select>
					</p>
				</td>
			</tr>

			<tr>
				<td>
					<p class="td_cap">低温補正</p>
					<input type="radio" name="tempLowTempCorrection" value="1" id="{seqId()}" {if $form.tempLowTempCorrection|default:null == 1}checked{/if}><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="tempLowTempCorrection" value="0" id="{seqId()}" {if $form.tempLowTempCorrection|default:null != 1}checked{/if}><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
		</table>
	</div>

	<a href="javascript:void(0);" onclick="doPost('./registRecogConfigSet')" class="btn_red">登録</a>

</div>
