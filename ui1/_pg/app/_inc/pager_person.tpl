
{if $topPager}
	<input type="hidden" name="{$form.tab}_limit"  value="{$form["`$form.tab`_limit"]}">
	<input type="hidden" name="{$form.tab}_pageNo" value="1">
{/if}

<div class="list_nav">

	<p class="txt">表示件数 :</p>
	<p class="select">
		<select onchange="byName('{$form.tab}_pageNo').value = 1; byName('{$form.tab}_limit').value = this.value; doGet('./{$form.tab}Search', true)">
			{foreach Enums::pagerLimit() as $k=>$v}
				<option {if $form["`$form.tab`_limit"] == $k}selected{/if} value="{$k}">{$v}</option>
			{/foreach}
		</select>
	</p>
	<p class="txt">件ごと  <span>({formatNumber($pageInfo->getPageNo())}/{formatNumber($pageInfo->getPageCount())})</span></p>
	{if $pageInfo->isEnablePrevPage()}
		{$url=replaceUrl(["_p"=>"90", "`$form.tab`_pageNo"=>$pageInfo->getPrevPageNo(), "export_checkIdsKey"=>$form.export_checkIdsKey, "export_searchFormKey"=>$form.export_searchFormKey, "trans_checkIdsKey"=>$form.trans_checkIdsKey, "trans_searchFormKey"=>$form.trans_searchFormKey])}
		<a style="margin-left:1em" href="{$url}" {if $topPager}onclick="byName('{$form.tab}_pageNo').value = {$pageInfo->getPrevPageNo()}; doGet('./{$form.tab}Search', true); return false;"{/if}>前ページ</a>
	{/if}
	{if $pageInfo->isEnableNextPage()}
		{$url=replaceUrl(["_p"=>"90", "`$form.tab`_pageNo"=>$pageInfo->getNextPageNo(), "export_checkIdsKey"=>$form.export_checkIdsKey, "export_searchFormKey"=>$form.export_searchFormKey, "trans_checkIdsKey"=>$form.trans_checkIdsKey, "trans_searchFormKey"=>$form.trans_searchFormKey])}
		<a style="margin-left:1em" href="{$url}" {if $topPager}onclick="byName('{$form.tab}_pageNo').value = {$pageInfo->getNextPageNo()}; doGet('./{$form.tab}Search', true); return false;"{/if}>次ページ</a>
	{/if}
</div>

