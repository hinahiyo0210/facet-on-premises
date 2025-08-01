	{if empty($noHeader)}

		</div>
		<!-- /コンテンツ -->

	{/if}
	
<script>
$(function() {
	flatpickr(".flatpickr", {
		locale:"ja",
		dateFormat: "Y/m/d",
	});
	flatpickr(".flatpickr_time", {
		enableTime: true,
		locale:"ja",
		dateFormat: "Y/m/d H:i",
	});
});

</script>

<script type="text/javascript">doPageLast();</script>

</body>
</html>
