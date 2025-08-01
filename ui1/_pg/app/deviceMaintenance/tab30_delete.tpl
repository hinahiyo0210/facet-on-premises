{* dev founder feihan *}

<script>
    $(function() {
        $("input[name='delete_serialNo']").blur(function () {
            if($(this).val()){
                deletePulldownInit();
                $(".delete_condition .fs-label-wrap").addClass("fs-label-wrap_disabled");
            }else{
                $(".delete_condition .fs-label-wrap").removeClass("fs-label-wrap_disabled");
            }
        });
    });

    // 検索実行
    function deleteSearch() {
        if ($('.fs-dropdown').eq(3).hasClass('hidden')) {
            const $session_key = $('input[name="_form_session_key"]');
            const data = [$('#delete_m1'), $('#delete_m2')].map($item => {
                return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
            });
            doAjax('../session/setSession', {
                session_key: $session_key.val(),
                value: JSON.stringify(data)
            }, (res) => {
                if (!res.error) {
                    $session_key.val(res['session_key']);
                    $('input[name="delete_search_init"]').val(1);
                    $('input[name="connectionInit_search_init"]').val(null);
                    doGet('./deleteSearch', true);
                } else {
                    alert(JSON.stringify(res));
                }
            }, (errorRes) => {
                alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
            });
        }
    }

    // 検索条件をリセット
    function deleteSearchInit(){
        $(".delete_condition input").val('');
        // グループ選択を初期リセット
        deletePulldownInit();
    }

    // グループ選択を初期リセット
    function deletePulldownInit(){
        // グループ選択を初期リセット
        if(groupsInit.length>0) {
            const $groupSelect = $("#delete_m1");
            $groupSelect.empty();
            groupsInit.forEach(group => {
                {literal}
                $groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
                {/literal}
            });
            $groupSelect.data('fSelect').destroy();
            $groupSelect.data('fSelect').create();
        }
        // カメラ選択を初期リセット
        const $deviceSelect = $("#delete_m2");
        $deviceSelect.empty();
        devices.forEach(device=>{
            {literal}
            $deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
            {/literal}
        });
        $deviceSelect.data('fSelect').destroy();
        $deviceSelect.data('fSelect').create();
    }

    // open カメラ削除確認モーダル
    function deviceDelete(deviceId){
        $("input[name='delete_device_id']").val(deviceId);
        $("#device_delete_deviceId").text($("#delete_device_tr_" + deviceId + " .serialNo").text());
        $("#device_delete_deviceName").text($("#delete_device_tr_" + deviceId + " .deviceName").text());
        showModal("カメラの削除", $("#device_delete_modal_template").html());
    }

</script>

<!-- カメラ削除確認モーダル -->
<div id="device_delete_modal_template" style="display:none">
    シリアル番号：<span id="device_delete_deviceId"></span>、カメラ名称：<span id="device_delete_deviceName"></span>を削除します。<br>
    削除するとfacet{if ENABLE_AWS}Cloud{/if}にて該当カメラの操作ができなくあり、ログ情報も消去されます。<br>
    本当によろしいですか？<br>
    <br><br><br>

    <div class="dialog_btn_wrap" style="margin-top:2em">
        <a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
        <a href="javascript:void(0)" onclick="doPost('./deleteDevice',false)" class="enter-submit btn btn_red btn_regist"> 削除する</a>
    </div>
</div>

<h2 class="tit_cnt_main">カメラの削除</h2>
<p class="cap_cnt_main">
    <span style="color: red">※削除を行うと、facet{if ENABLE_AWS}Cloud{/if}にて該当カメラの操作ができなくなり、ログ情報も消去されます。実施する場合は十分注意ください。</span>
</p>

<div class="search_area">
    {include file="./search_area.tpl" prefix="delete_"}
    <div class="devicemaintenancebtn_wrap">
        <a href="javascript:void(0)" onclick="deleteSearch();" class="enter-submit btn_red"><i class="fas fa-search"></i>検索実行</a>
        <a href="javascript:void(0)" onclick="deleteSearchInit()" value="Reset" id="list_ResetBtn" class="btn_blue list_resetBtn" >条件リセット</a>
    </div>
</div>

<input type="hidden" id="delete_search_init" name="delete_search_init" value="{$form.delete_search_init|default:""}" />
<input type="hidden" id="delete_device_id" name="delete_device_id" />
<input type="hidden" name="delete_back" value="{$form.delete_back|default:""}" />

{if isset($delete_list)}
    <div class="search_results">
        <div class="tit_wrap">
            <h3 class="tit">検索結果</h3>
        </div>

        {include file="../_inc/pager_counter_deviceMaintenance.tpl" pageInfo=$delete_pageInfo topPager=true}

        <table class="search_results_table">
            <tr>
                <th class="">No</th>
                <th class="">シリアルNo</th>
                <th class="">カメラグループ</th>
                <th class="">カメラ名称</th>
                <th class="">削除</th>
            </tr>
            {foreach $delete_list as $item}
                <tr id="delete_device_tr_{$item.device_id}">
                    <td class="sortOrder">{$item.sort_order}</td>
                    <td class="serialNo">{$item.serial_no}</td>
                    <td class="groupName">{$item.group_name}</td>
                    <td class="deviceName">{$item.device_name}</td>
                    <td><a href="javascript:void(0)" onclick="deviceDelete('{$item.device_id}')" class="btn btn_small">削除</a></td>
                </tr>
            {/foreach}
        </table>

    </div>

    {include file="../_inc/pager_counter_deviceMaintenance.tpl" pageInfo=$delete_pageInfo topPager=false}

{/if}