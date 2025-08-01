
<div class="list_nav">
	<p class="txt">表示件数 :</p>
	<p class="select">
		<select onchange="byName('pageNo').value = 1; byName('limit').value = this.value; document.searchForm.submit()">
			{foreach $pagerLimit as $k=>$v}
				<option {if $form.limit == $k}selected{/if} value="{$k}">{$v}</option>
			{/foreach}
		</select>
	</p>
	<p class="txt">件ごと  <span>({formatNumber($pageInfo->getPageNo())}/{formatNumber($pageInfo->getPageCount())})</span></p>
	{if $pageInfo->isEnablePrevPage()}
		<a style="margin-left:1em" href="{replaceUrl(concat('pageNo=', $pageInfo->getPrevPageNo()))}">前ページ</a>
	{/if}
	{if $pageInfo->isEnableNextPage()}
		<a style="margin-left:1em" href="{replaceUrl(concat('pageNo=', $pageInfo->getNextPageNo()))}">次ページ</a>
	{/if}
</div>

