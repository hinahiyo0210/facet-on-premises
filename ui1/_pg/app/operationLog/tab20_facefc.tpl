{* dev founder luyi *}
<script>
    $(function() {
        $("#m1").fSelect()
        $("#m2").fSelect()

        $("#m1").on('pulldownChange',function () {
            showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
            const $wrap = $(this).closest('.fs-wrap')
            $("#modal_message #groupsChangeModalBtnCancel").click(function () {
                $wrap.fSelectedValues($wrap.data('oldVal'))
                removeModal()
            })
            $("#modal_message #groupsChangeModalBtnOk").click(function () {
                const newVal = $wrap.fSelectedValues()
                $wrap.data('oldVal',newVal)
                const $deviceSelect = $("#m2")
                const dids = newVal.flatMap(gid=>groups[gid])
                $deviceSelect.empty()
                devices.forEach(device=>{
                    if (dids.indexOf(device.id)===-1) {
                        return
                    }
                    {literal}
                    $deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
                    {/literal}
                })
                $deviceSelect.data('fSelect').destroy()
                $deviceSelect.data('fSelect').create()
                removeModal()
            })
        })
    })

    // 検索実行
    function facefcSearch() {
        if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
            const $session_key = $('input[name="_form_session_key"]');
            const data = [$('#m1'), $('#m2')].map($item => {
                return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
            });
            doAjax('../session/setSession', {
                session_key: $session_key.val(),
                value: JSON.stringify(data)
            }, (res) => {
                if (!res.error) {
                    $session_key.val(res['session_key']);
                    $('input[name="searchInit"]').val(1);
                    doGet('./facefcSearch', true);
                } else {
                    alert(JSON.stringify(res));
                }
            }, (errorRes) => {
                alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
            });
        }
    }
</script>

<input type="hidden" name="_form_session_key" value="{$form._form_session_key|default:""}">
<input type="hidden" name="searchInit" value="{$form.searchInit|default:""}">

<div class="search_area">
    <table class="form_cnt regist_cnt operation_cnt">
        <tr>
            <th>期間選択 <span class="required">※</span></th>
            <td>
                <div class="period">
                    <div class="select calendar">
                        <i class="fas fa-calendar-week"></i>
                        <input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d')}" name="facefc_date_from" value="{$form.facefc_date_from|default:""}">
                    </div>
                    <span>〜</span>
                    <div class="select calendar">
                        <i class="fas fa-calendar-week"></i>
                        <input type="text" class="flatpickr" autocomplete="off" data-allow-input="true" placeholder="{date('Y/m/d')}" name="facefc_date_to" value="{$form.facefc_date_to|default:""}">
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>アカウントID</th>
            <td>
                <input type="text" name="facefc_account_id" value={$form.facefc_account_id|default:""}>
            </td>
        </tr>
        {assign var=devicesDisplay value=[]}
        {if empty(Session::getLoginUser("group_id"))}
        <tr>
            <th class="fs-select-th-center">カメラグループ</th>
            <td>
                <select id="m1" class="groups hidden" name="facefc_group_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
                    {foreach $groupsDisplay as $g=>$group}
                        {$selected = ""}
                        {if exists($form["facefc_group_ids"], $g)}
                            {$selected = "selected"}
                            {$devicesDisplay=array_merge($devicesDisplay,$group.deviceIds)}
                        {/if}
                        <option value="{$g}" {$selected}>{$group.group_name}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        {else}
            {$devicesDisplay=array_keys($devices)}
        {/if}
        <tr>
            <th class="fs-select-th-center">カメラ選択</th>
            <td>
                <select id="m2" class="devices hidden" name="facefc_device_ids[]" multiple="multiple" disabled="disabled">  {* setSessionの場合のみ送信 *}
                    {foreach $devices as $d=>$device}
                        {if exists($devicesDisplay, $d)}
                            <option value="{$d}" {if exists($form["facefc_device_ids"], $d)}selected{/if}>{$device.name}</option>
                        {/if}
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <th>メインタイプ</th>
            <td>
                <p class="select">
                    <select name="facefc_main_type">
                        <option value=""></option>
                        {foreach $facefc_main_types as $i=>$mainType}
                            <option value="{$i}" {if exists($form["facefc_main_type"]|default:"", $i)}selected{/if}>{$mainType}</option>
                        {/foreach}
                    </select>
                </p>
            </td>
        </tr>
        <tr>
            <th>サブタイプ</th>
            <td>
                <input type="text" name="facefc_sub_type" value={$form.facefc_sub_type|default:""}></p>
            </td>
        </tr>
    </table>
    <a href="javascript:void(0)" onclick="facefcSearch()" class="enter-submit btn_red">検索実行</a>
</div>

{if isset($facefc_list)}
<script>
    function postFacefcDetail(id) {
        $('form').append('<input type="hidden" name="facefc_operate_log_id" value="' + id + '">');
        doPost('./facefcDetail', true);
    }
</script>

<div class="search_results">
    {include file="../_inc/pager_counter_operationLog.tpl" pageInfo=$facefc_pageInfo topPager=true}

    <table class="search_results_table">
        <tr>
            <th class="facefc_results_datetime">日時</th>
            <th class="facefc_results_account_id">アカウントID</th>
            <th class="facefc_results_camera_group">カメラグループ</th>
            <th class="facefc_results_camera">カメラ</th>
            <th class="facefc_results_main_type">メインタイプ</th>
            <th class="facefc_results_sub_type">サブタイプ</th>
            <th class="facefc_results_detail">詳細</th>
        </tr>
        {foreach $facefc_list as $item}
            <tr>
                <td>{substr($item.operate_time, 0, 10)}<br>{substr($item.operate_time, 11)}</td>
                <td>{$item.operate_user}</td>
                <td>{$item.group_name}</td>
                <td>{$item.description}</td>
                <td>{$item.main_type}</td>
                <td>{$item.sub_type}</td>
                <td><a href="javascript:void(0)" onclick="postFacefcDetail({$item.operate_log_id})"><i class="fas fa-ellipsis-h"></i></a></td>
            </tr>
        {/foreach}
    </table>
</div>

<!-- Modal -->
{if !empty($facefc_detail)}
<script>
    $(function() {
        showModal("詳細", $('#facefc_detail').html(), 'operation_log_detail_modal', null, null, true, 'form');
    });
</script>

<div id="facefc_detail" style="display: none;">
    <span>{$facefc_detail}</span>
    <div>
        <a href="javascript:void(0);" onclick="removeModal()" class="btn_gray">閉じる</a>
    </div>
</div>
{/if}

{include file="../_inc/pager_counter_operationLog.tpl" pageInfo=$facefc_pageInfo topPager=false}
{/if}