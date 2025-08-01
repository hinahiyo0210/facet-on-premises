<tr style="display:none;">
	<th>設定名</th>
	<td>
		<input type="hidden" name="alert_no" value="{$form.alert_no}" />
		<input type="hidden" name="alert_alert_name" value="接続確認アラーム" />
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
					{if exists($form["alert_group_ids"], $g)}
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

<tr style="display:none;">
	<th>アラーム基準の設定</th>
	<td>
		<input type="hidden" name="alert_nopass_flag" value="0">
		<input type="hidden" name="alert_guest_flag"  value="0">
		<input type="hidden" name="alert_temp_flag"   value="0">
		<input type="hidden" name="alert_mask_flag"   value="0">
	</td>
</tr>
<tr>
	<th>送信先メールアドレス1</th>
	<td>
		<input type="text" placeholder="mail@example.com" name="alert_mail_1" value="{$form.alert_mail_1}" />
	</td>
</tr>
<tr>
	<th>送信先メールアドレス2</th>
	<td>
		<input type="text" placeholder="mail@example.com" name="alert_mail_2" value="{$form.alert_mail_2}" />
	</td>
</tr>
<tr>
	<th>送信先メールアドレス3</th>
	<td>
		<input type="text" placeholder="mail@example.com" name="alert_mail_3" value="{$form.alert_mail_3}" />
	</td>
</tr>
<tr>
	<th>メールタイトル(切断時)</th>
	<td>
		<input type="text" name="alert_mail_subject" value="{$form.alert_mail_subject}" />
	</td>
</tr>
<tr>
	<th style="vertical-align:top;">メール本文(切断時)</th>
	<td>
		<textarea name="alert_mail_body">{$form.alert_mail_body}</textarea>
		<div style="text-align: right">
			<a href="javasciprt:void(0);" onclick="$(byName('alert_mail_body')).val($('#alertMailDefault').val())">デフォルトを復元する</a>
			<input type="hidden" id="alertMailDefault" value="{$alertConnectMailDefault}" />
		</div>
	</td>
</tr>
<tr>
	<th>メールタイトル(復旧時)</th>
	<td>
		<input type="text" name="alert_mail_subject_r" value="{$form.alert_mail_subject_r}" />
	</td>
</tr>
<tr>
	<th style="vertical-align:top;">メール本文(復旧時)</th>
	<td>
		<textarea name="alert_mail_body_r">{$form.alert_mail_body_r}</textarea>
		<div style="text-align: right">
			<a href="javasciprt:void(0);" onclick="$(byName('alert_mail_body_r')).val($('#alertMailDefaultRe').val())">デフォルトを復元する</a>
			<input type="hidden" id="alertMailDefaultRe" value="{$alertReConnectMailDefault}" />
		</div>
	</td>
</tr>
