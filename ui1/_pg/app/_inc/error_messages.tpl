<div id="error_message">
	
	<div class="err_msg_title">
		確認を行ってください。<span class="material-icons">expand</span>
	</div>
	
	{foreach from=$messages key=name item=msgArr}
		{foreach from=$msgArr item=msg}
			{if !empty($msg)}
				<div class="err_msg_item">
					{$msg nofilter}
				</div>
			{/if}
		{/foreach}
	{/foreach}

</div>

<div id="mask"></div>

<script>
	$(function() {
		$(".main_container").addClass("errored");

		$(".err_msg_title").click(function() {
			
			if ($("#error_message").hasClass("min")) {
				
				$("#error_message").removeClass("min");
				$(".main_container").addClass("errored");
				
			} else {
				$("#error_message").addClass("min");
				$(".main_container").removeClass("errored");
				
			}
			
		});

	});
	
	$("#mask").fadeOut(1000, function() {
		$("#error_message").addClass("to_bottom");
		setTimeout(function() {
			{foreach from=$messages key=name item=msgArr}
				{foreach from=$msgArr item=msg}
					{if !empty($msg)}
						$(function() {
							$(byMessageName("{hjs($name) nofilter}")).each(function() {
								if ($(this).prop("tagName").toLowerCase() == "select") {
									$(this).closest("p.select").addClass("error").hide().fadeIn(2000);
									
								} else if ($(this).prop("type").toLowerCase() == "hidden" || $(this).prop("type").toLowerCase() == "file") {
									$($(this).attr("error-target")).addClass("error").hide().fadeIn(2000);
{* add-start founder luyi *}
								} else if ($(this).prop("type").toLowerCase() == "checkbox" || $(this).prop("type").toLowerCase() == "radio") {
									$(this).next().addClass("error").hide().fadeIn(2000);
{* add-end founder luyi *}
								} else {
									$(this).addClass("error").hide().fadeIn(2000);
								}

							});
							
							
						});
					{/if}
				{/foreach}
			{/foreach}
			
		}, 200);
	});

	
</script>

