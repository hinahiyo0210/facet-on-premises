<script>

$(function() {
	// add-start founder feihan
	$("#alert_m1").fSelect();
	$("#alert_m2").fSelect();

	$("#alert_m1").on('pulldownChange',function () {
		showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
		const $wrap = $(this).closest('.fs-wrap')
		$("#modal_message #groupsChangeModalBtnCancel").click(function () {
			$wrap.fSelectedValues($wrap.data('oldVal'))
			removeModal()
		})

		$("#modal_message #groupsChangeModalBtnOk").click(function () {
			const newVal = $wrap.fSelectedValues()
			$wrap.data('oldVal',newVal)
			const $deviceSelect = $("#alert_m2")
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
	// add-end founder feihan

});

function doAlertMailTest() {

	showModal("テストメールの送信", $("#testMailModalTemplate").html());
	$(".testMailModal_loading").show();
	$(".testMailModal_complete").hide();

	doAjax("./sendAlertTestMail", { alert_mail_1: $("input[name='alert_mail_1']").val(), alert_mail_2: $("input[name='alert_mail_2']").val(), alert_mail_3: $("input[name='alert_mail_3']").val() }, function(data) {

		if (data.error) {
			alert(data.error);
			removeModal();
			return;
		}

		$(".testMailModal_loading").hide();
		$(".testMailModal_complete").show();

	});

}

</script>

<!-- テストメール送信ダイアログ -->
<div id="testMailModalTemplate" style="display:none">

	<div class="testMailModal_loading">
		テストメールを送信中です。<br />
		このままでお待ちください。
		<div id="loading" class="loader">Loading...</div>
	</div>

	<div class="testMailModal_complete" style="display:none">
		メールの送信を行いました。<br />
		メールが到着しているかどうか、ご確認ください。<br />
		<br />
		メールが到着していない場合、入力ミスなどによりご利用頂けないメールアドレスの可能性があります。<br />
		<div class="btn_1">
			<a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
		</div>

	</div>

</div>



<div class="terminal_cnt set_detail">
	<h3 class="tit_cnt_main">アラーム設定</h3>
	<table class="form_cnt">
		<tr>
			<th>アラーム設定の選択</th>
			<td>
				<p class="select">
					<select onchange="location.href='./selectAlerm?tab=alerm&alert_no=' + this.value">
						<option value="">設定を選択</option>
						{if !Session::getLoginUser("getsysteminfo_time")}
							{for $i=1 to 5}
								{$alert=$alertList[$i]|default:null}
								<option {if $form.alert_no|default:"" == $i}selected{/if} value="{$i}">{nval($alert.alert_name|default:"", "アラーム設定`$i`")}</option>
							{/for}
						{else}
							{for $i=1 to 6}
								{if $i == 6}
								{$alert=$alertList[$i]|default:null}
								<option {if $form.alert_no|default:"" == $i}selected{/if} value="{$i}">接続確認アラーム</option>
								{else}
								{$alert=$alertList[$i]|default:null}
								<option {if $form.alert_no|default:"" == $i}selected{/if} value="{$i}">{nval($alert.alert_name|default:"", "アラーム設定`$i`")}</option>
								{/if}
							{/for}
						{/if}
					</select>
				</p>
			</td>
		</tr>

		{if $form.alert_no|default:""}
			{if $form.alert_no|default:"" == 6}
				{include file="./tab80_alerm_connect.tpl"}
			{else}
			<tr>
				<th>設定名</th>
				<td>
					<input type="hidden" name="alert_no" value="{$form.alert_no|default:""}" />
					<input type="text" name="alert_alert_name" value="{$form.alert_alert_name|default:""}" />
				</td>
			</tr>
			<tr>
				<th>アラーム時のメール発報機能</th>
				<td>
					<input type="radio" name="alert_enable_flag" value="1" {if !empty($form.alert_enable_flag)}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">する</label>
					<input type="radio" name="alert_enable_flag" value="0" {if empty($form.alert_enable_flag) }checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="radio">しない</label>
				</td>
			</tr>
			{assign var=devicesDisplay value=[]}
			<tr>
				<th rowspan="2" style="vertical-align: top;padding-top: 14px;">カメラを選択</th>
				<td>
					<p>
						{* mod-start founder feihan *}
						<select id="alert_m1" class="groups hidden" name="alert_group_ids[]" multiple="multiple">
							{foreach $groupsDisplay as $g=>$group}
								{$selected = ""}
								{if exists($form["alert_group_ids"]|default:"", $g)}
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
			<tr>
				<td>
					{* mod-start founder feihan *}
					<select id="alert_m2" class="devices hidden" name="alert_device_ids[]" multiple="multiple">
						{foreach $devices as $d=>$device}
							{if exists($devicesDisplay, $d)}
								<option value="{$d}" {if exists($form["alert_device_ids"], $d)}selected{/if}>{$device.name}</option>
							{/if}
						{/foreach}
					</select>
					{* mod-end founder feihan *}
				</td>
			</tr>

			<tr>
				<th>アラーム基準の設定</th>
				<td>
					<input type="checkbox" name="alert_nopass_flag" value="1" {if !empty($form.alert_nopass_flag)}checked{/if} id="{seqId()}"><label for="{seqId(1)}" class="checkbox">NO PASS</label>
					<input type="checkbox" name="alert_guest_flag"  value="1" {if !empty($form.alert_guest_flag)}checked{/if}  id="{seqId()}"><label for="{seqId(1)}" class="checkbox">未登録者</label>
					<input type="checkbox" name="alert_temp_flag"   value="1" {if !empty($form.alert_temp_flag)}checked{/if}   id="{seqId()}"><label for="{seqId(1)}" class="checkbox">温度異常</label>
					<input type="checkbox" name="alert_mask_flag"   value="1" {if !empty($form.alert_mask_flag)}checked{/if}   id="{seqId()}"><label for="{seqId(1)}" class="checkbox">マスク未装着</label>
				</td>
			</tr>
			<tr>
				<th>送信先メールアドレス1</th>
				<td>
					<input type="text" placeholder="mail@example.com" name="alert_mail_1" value="{$form.alert_mail_1|default:""}" />
				</td>
			</tr>
			<tr>
				<th>送信先メールアドレス2</th>
				<td>
					<input type="text" placeholder="mail@example.com" name="alert_mail_2" value="{$form.alert_mail_2|default:""}" />
				</td>
			</tr>
			<tr>
				<th>送信先メールアドレス3</th>
				<td>
					<input type="text" placeholder="mail@example.com" name="alert_mail_3" value="{$form.alert_mail_3|default:""}" />
				</td>
			</tr>
			<tr>
				<th>メールタイトル</th>
				<td>
					<input type="text" name="alert_mail_subject" value="{$form.alert_mail_subject}" />
				</td>
			</tr>
			<tr>
				<th style="vertical-align:top;">メール本文</th>
				<td>
					<textarea name="alert_mail_body">{$form.alert_mail_body}</textarea>
					<div style="text-align: right">
						<a href="javasciprt:void(0);" onclick="$(byName('alert_mail_body')).val($('#alertMailDefault').val())">デフォルトを復元する</a>
						<input type="hidden" id="alertMailDefault" value="{$alertMailDefault}" />
					</div>
				</td>
			</tr>
			{/if}
		{/if}
	</table>
</div>


{if $form.alert_no|default:""}
	<p class="cap_cnt_main">3つよりも多くのメールアドレスをご利用になりたい場合には、メーリングリストをご活用ください。</p>
	<a href="javascript:void(0);" class="btn_blue" onclick="doAlertMailTest()"><i class="fas fa-paper-plane"></i>メールをテスト送信</a>
	<a href="javascript:void(0);" class="btn_red" onclick="doPost('./registAlerm')">登録</a>
{/if}

			<!--
			<input type="checkbox" name="hoge" value="ブラックリスト" id="hoge" checked>
			<label for="hoge" class="checkbox">ブラックリスト</label>
			-->

