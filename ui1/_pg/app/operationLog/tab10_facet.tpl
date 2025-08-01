{* dev founder luyi *}

<script>
</script>

<div class="search_area">
    <table class="form_cnt regist_cnt operation_cnt">
        <tr>
            <th>期間選択 <span class="required">※</span></th>
            <td>
                <div class="period">
                    <div class="select calendar">
                        <i class="fas fa-calendar-week"></i>
                        <input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d')}" name="facet_date_from" value="{$form.facet_date_from}">
                    </div>
                    <span>〜</span>
                    <div class="select calendar">
                        <i class="fas fa-calendar-week"></i>
                        <input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d')}" name="facet_date_to" value="{$form.facet_date_to}">
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>ログインID</th>
            <td>
                <input type="text" name="facet_account_id" value={$form.facet_account_id|default:""}>
            </td>
        </tr>
        <tr>
            <th>氏名</th>
            <td>
                <input type="text" name="facet_account_name" value={$form.facet_account_name|default:""}>
            </td>
        </tr>
        <tr>
            <th>操作区分</th>
            <td>
                <p class="select operation_select">
                    <select name="facet_operate_type">
                        <option value=""></option>
                        {foreach $facet_operate_types as $k=>$v}
                            <option {if exists($form["facet_operate_type"]|default:"", $k)}selected {/if}value={$k}>{$v}</option>
                        {/foreach}
                    </select>
                </p>
            </td>
        </tr>
    </table>
    <a href="javascript:void(0)" onclick="doGet('./facetSearch', true)" class="enter-submit btn_red">検索実行</a>
</div>

{if isset($facet_list)}
<script>
    function postFacetDetail(id) {
        $('form').append('<input type="hidden" name="facet_operate_log_id" value="' + id + '">');
        doPost('./facetDetail', true);
    }
</script>

<div class="search_results">
{include file="../_inc/pager_counter_operationLog.tpl" pageInfo=$facet_pageInfo topPager=true}

    <table class="search_results_table">
        <tr>
            <th class="facet_results_datetime">日時</th>
            <th class="facet_results_operate_id">ログインID</th>
            <th class="facet_results_account_name">操作者</th>
            <th class="facet_results_operate_type">操作区分</th>
            <th class="facet_results_detail">詳細</th>
        </tr>
        {foreach $facet_list as $item}
            <tr>
                <td>{substr($item.operate_time, 0, 10)}<br>{substr($item.operate_time, 11)}</td>
                <td>{$item.operate_user_id}</td>
                <td>{$item.operate_user_name}</td>
                <td>{$item.operate_type}</td>
                <td><a href="javascript:void(0)" onclick="postFacetDetail({$item.operate_log_id})"><i class="fas fa-ellipsis-h"></i></a></td>
            </tr>
        {/foreach}
    </table>
</div>

<!-- Modal -->
{if !empty($facet_detail)}
    <script>
        $(function() {
            showModal("詳細", $('#facet_detail').html(), 'operation_log_detail_modal', null, null, true, 'form');
        });
    </script>

    <div id="facet_detail" style="display: none;">
        <span>{$facet_detail}</span>
        <div>
            <a href="javascript:void(0);" onclick="removeModal()" class="btn_gray">閉じる</a>
        </div>
    </div>
{/if}

{include file="../_inc/pager_counter_operationLog.tpl" pageInfo=$facet_pageInfo topPager=false}
{/if}