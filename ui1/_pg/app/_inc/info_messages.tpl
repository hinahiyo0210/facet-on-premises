<div id="info_message">
	{if is_array($messages)}
		
		{foreach from=$messages item=msg}
			<div class="info_msg_item">{$msg nofilter}</div>
		{/foreach}
		
	{else}
		<div class="info_msg_item">{$messages nofilter}</div>
	{/if}

</div>

<div id="mask"></div>

<script>

	$("#mask").fadeOut(500, function() {
		$("#info_message").fadeOut(2000);
	});
	
</script>
