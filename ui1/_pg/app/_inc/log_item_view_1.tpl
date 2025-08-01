
	<li class="device_{$item.device_id}">
		<p class="camera">{$item.deviceName}</p>
		<div class="img"><div class="recog_picture" style="background-image:url('{if $item.cardPicture}{$item.cardPicture}{elseif $item.pictureUrl}{$item.pictureUrl}{else}/ui1/static/images/gray.png{/if}')"></div></div>
		<div class="txt_wrap">
			<p class="temperature {if $item.temperature_alarm == 2}abnormal{/if}">{if !is_null($item.temp) && (int)$item.temp === 0}温度測定失敗{elseif 0 < $item.temp}{h($item.temp, "", "℃") nofilter}{else}-{/if}</p>
			<p class="mask {if $item.mask == 2}nomask{/if}">{if $item.mask == 1}マスク着用{elseif $item.mask == 2}マスク未着用{/if}</p>
			<div class="desc">
				<p class="time">{formatDate($item.recog_time, "Y/m/d H:i:s")}</p>
				{if empty($item.personCode)}
					<p class="id">ゲスト</p>
				{else}
					<p class="id">ID:{$item.personCode}</p>
					<p class="name">{$item.personName}</p>
				{/if}
				{if Session::getLoginUser("enter_exit_mode_flag") == 1}
					{if $item.person_description1}<p>{$item.person_description1}</p>{/if}
					{if $item.person_description2}<p>{$item.person_description2}</p>{/if}
				{/if}
				{if !empty($item.search_score)}
					<p class="score">SCORE<span>{sprintf('%.1f', $item.search_score)}%</span></p>
				{/if}
			</div>
		</div>
		{if empty($item.pass)}<p class="notauth_tag">NO PASS</p>{/if}
	</li>

