{$title="入退管理"}{$icon="fas fa-door-open"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}

<script>

$(function() {

	$("#group_select").change(function() {
		changeSelect('./changeGroup', false);
	});
	
});

function saveTime(action, scrollSave) {

	document.saveTimeForm.method = "post";
	document.saveTimeForm.action = action;
	document.saveTimeForm.submit();

}

function changeSelect(action, scrollSave) {

	document.groupSelectForm.method = "get";
	document.groupSelectForm.action = action;
	document.groupSelectForm.submit();

}

</script>
<div class="flex_box search_content statistics_box">
	<form action="./" method="get" name="groupSelectForm" class="group_select_box flex_box">
		<p class="input_title">グループ</p>
		<p class="select" style="width:400px">
			<select id="group_select" name="device_group_id" {if !empty(Session::getLoginUser("group_id"))}style="pointer-events:none;background-color:#d8d8d8;"{/if}>
			{foreach $groups as $g => $group}
				{if isset($form['device_group_id']) && ($form['device_group_id'] == $g || Session::getLoginUser("group_id") == $g)}
				<option value="{$g}" selected>{$group.group_name}</option>
				{else}
				<option value="{$g}">{$group.group_name}</option>
				{/if}
			{/foreach}
			</select>
		</p>
	</form>
</div>

<div class="main_wrapper count_wrapper">
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title">入室者</h2>
				<p class="count_number">{$groupTotalCounts.1.count|default:0}人</p>
			</div>
		</div>
		<div class="icon_box enter_box"><i class="fas fa-portal-enter"></i></div>
	</div>
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title">退室者</h2>
				<p class="count_number">{$groupTotalCounts.2.count|default:0}人</p>
			</div>
		</div>
		<div class="icon_box exit_box"><i class="fas fa-portal-exit"></i></div>
	</div>
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title">在室者</h2>
				<p class="count_number">{$groupTotalCounts.1.count|default:0 - $groupTotalCounts.2.count|default:0}人</p>
			</div>
		</div>
		<div class="icon_box enter_exit_box"><i class="fas fa-users"></i></div>
	</div>
</div>
<div class="sub_wrapper count_wrapper">
	{foreach $personTypes as $personType}
	<div class="count_countainer">
		<div class="txt_box">
			<div class="txt_wrap">
				<h2 class="count_title">{$personType.person_type_name}在室者</h2>
				<p class="count_number">{$personTypeCounts.{$personType.person_type_code}.count|default:0}人</p>
			</div>
		</div>
	</div>
	{/foreach}
	{if (count($personTypes) % 3) == 2}
	<div class="count_countainer dummy_container"></div>
	{/if}
</div>

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}