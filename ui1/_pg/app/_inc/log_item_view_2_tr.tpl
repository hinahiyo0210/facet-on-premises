
	<tr rid="{$item.recog_log_id|default:""}" class="device_{$item.device_id} recog_row r_{$item.recog_log_id|default:""} {if empty($item.pass)}notauth{/if}">
		<td>{formatDate($item.recog_time|default:"", "Y/m/d")}<br>{formatDate($item.recog_time|default:"", "H:i:s")}</td>
		{* add-start founder feihan *}
		<td>{$item.deviceGroupName|default:""}</td>
		{* add-end founder feihan *}
		<td>{$item.deviceName}</td>
		<td>{UiRecogLogService::accessTypeNameMap($item.accessType|default:"",$item.cardType|default:"")}</td>
		{if Session::getLoginUser("enter_exit_mode_flag") == 1}
		<td>{if $item.enter_exit_type_flag|default:false == 1}入室{elseif $item.enter_exit_type_flag|default:false == 2}退室{/if}</td>
		{/if}
		<td>{if empty($item.personCode)}ゲスト{else}{$item.personCode}{/if}</td>
		<td style="word-break:break-all">{$item.personName|default:""}</td>
		<td style="word-break:break-all">{$item.cardNo|default:""}</td>
		{if Session::getLoginUser("enter_exit_mode_flag") == 1}
		<td>{$personTypes[$item.person_type_code|default:""]|default:""}</td>
		<td>{$item.person_description1|default:""}</td>
		<td>{$item.person_description2|default:""}</td>
		{/if}
		{* add-start founder feihan *}
		<td>{$item.passStr|default:""}</td>
		{* add-end founder feihan *}
		<td {if $item.temperature_alarm|default:false == 2}class="abnormal"{/if}>{if !is_null($item.temp|default:null) && (int)$item.temp === 0}測定失敗{elseif 0 < $item.temp|default:null}{h($item.temp, "", "℃") nofilter}{else}-{/if}</td>
		<td {if $item.mask|default:false == 2}nomask{/if}>{if $item.mask|default:false == 1}着用{elseif $item.mask|default:false == 2}未着用{/if}</td>
		<td>{if $item.search_score|default:false}{sprintf('%.1f', $item.search_score)}%{/if}</td>
		{if !empty($recogPassFlags)}
			<td>{$item.passFlagName}</td>
		{/if}
		{* add-start founder feihan *}
		<td><a href="javascript:void(0)" onclick="showPic(this)" class="person_picture_view" person-picture-url="{if $item.cardPicture|default:false}{$item.cardPicture}{else}{$item.pictureUrl|default:"/ui1/static/images/gray.png"}{/if}" recog-log-detail="{$item.detail|default:""}"><i class="fas fa-portrait"></i></a></td>
		{* add-end founder feihan *}
	</tr>
