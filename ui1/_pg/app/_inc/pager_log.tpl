
<div class="list_nav">
	<p class="txt">表示形式 :</p>
	<div class="format">
		<a href="./{replaceUrl('view=1')}" {if $form.view == 1}class="active" style="pointer-events:none"{/if}><i class="fas fa-th-large"></i></a>
		<a href="./{replaceUrl('view=2')}" {if $form.view == 2}class="active" style="pointer-events:none"{/if}><i class="fas fa-list"></i></a>
	</div>
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
		<a style="margin-left:1em" href="{replaceUrl(concat('pageNo=', $pageInfo->getPrevPageNo()))}" onclick="byName('pageNo').value = {$pageInfo->getPrevPageNo()}; doGet('./', true); return false;">前ページ</a>
	{/if}
	{if $pageInfo->isEnableNextPage()}
		<a style="margin-left:1em" href="{replaceUrl(concat('pageNo=', $pageInfo->getNextPageNo()))}" onclick="byName('pageNo').value = {$pageInfo->getPrevPageNo()}; doGet('./', true); return false;">次ページ</a>
	{/if}
</div>

