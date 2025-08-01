{* dev founder feihan *}

{if $topPager}
	<input type="hidden" name="list_limit"  value="{$form["list_limit"]}">
	<input type="hidden" name="list_pageNo" value="1">
{/if}

<div class="list_nav">
	<p class="txt">ヒット件数 :</p>
	<div class="format">
		<p class="txt">{formatNumber($pageInfo->getRowCount())} 件</p>
	</div>
	<p class="txt">表示件数 :</p>
	<p class="select">
		<select onchange="byName('list_pageNo').value = 1; byName('list_limit').value = this.value; doGet('./listSearch', true)">
			{foreach Enums::pagerLimit() as $k=>$v}
				<option {if $form["list_limit"] == $k}selected{/if} value="{$k}">{$v}</option>
			{/foreach}
		</select>
	</p>
	<p class="txt">件ごと <span>({formatNumber($pageInfo->getPageNo())}/{formatNumber($pageInfo->getPageCount())})</span></p>
	{if $pageInfo->isEnablePrevPage()}
		{$url=replaceUrl(["_p"=>"90", "list_pageNo"=>$pageInfo->getPrevPageNo()])}
		<a style="margin-left:1em" href="{$url}" onclick="byName('list_pageNo').value = {$pageInfo->getPrevPageNo()}; doGet('./listSearch', true); return false;">前ページ</a>
	{/if}
	{if $pageInfo->isEnableNextPage()}
		{$url=replaceUrl(["_p"=>"90", "list_pageNo"=>$pageInfo->getNextPageNo()])}
		<a style="margin-left:1em" href="{$url}" onclick="byName('list_pageNo').value = {$pageInfo->getNextPageNo()}; doGet('./listSearch', true); return false;">次ページ</a>
	{/if}
</div>