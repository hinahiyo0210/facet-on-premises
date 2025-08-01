{* dev founder zouzhiyuan *}

{if $topPager}
	<input type="hidden" name="device_search_limit"  value="{$form["device_search_limit"]}">
	<input type="hidden" name="device_search_pageNo" value="1">
{/if}

<div class="list_nav">
	<p class="txt">ヒット件数 :</p>
	<div class="format">
		<p class="txt">{formatNumber($pageInfo->getRowCount())} 件</p>
	</div>
	<p class="txt">表示件数 :</p>
	<p class="select">
		<select onchange="byName('device_search_pageNo').value = 1; byName('device_search_limit').value = this.value; doGet('./listSearch', true)">
			{foreach Enums::pagerLimit() as $k=>$v}
				<option {if $form["device_search_limit"] == $k}selected{/if} value="{$k}">{$v}</option>
			{/foreach}
		</select>
	</p>
	<p class="txt">件ごと <span>({formatNumber($pageInfo->getPageNo())}/{formatNumber($pageInfo->getPageCount())})</span></p>
	{if $pageInfo->isEnablePrevPage()}
		{$url=replaceUrl(["_p"=>"90", "device_search_pageNo"=>$pageInfo->getPrevPageNo()])}
		<a style="margin-left:1em" href="{$url}" onclick="byName('device_search_pageNo').value = {$pageInfo->getPrevPageNo()}; doGet('./listSearch', true); return false;">前ページ</a>
	{/if}
	{if $pageInfo->isEnableNextPage()}
		{$url=replaceUrl(["_p"=>"90", "device_search_pageNo"=>$pageInfo->getNextPageNo()])}
		<a style="margin-left:1em" href="{$url}" onclick="byName('device_search_pageNo').value = {$pageInfo->getNextPageNo()}; doGet('./listSearch', true); return false;">次ページ</a>
	{/if}
</div>