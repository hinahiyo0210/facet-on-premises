{* dev founder feihan *}

<script>
    $(function() {
        $("input[name='connectionInit_serialNo']").blur(function () {
            if($(this).val()){
                connectionPulldownInit();
                $(".connectionInit_condition .fs-label-wrap").addClass("fs-label-wrap_disabled");
            }else{
                $(".connectionInit_condition .fs-label-wrap").removeClass("fs-label-wrap_disabled");
            }
        });
    });

    // 検索実行
    function connectionInitSearch() {
        if ($('.fs-dropdown').eq(0).hasClass('hidden')) {
            const $session_key = $('input[name="_form_session_key"]');
            const data = [$('#connectionInit_m1'), $('#connectionInit_m2')].map($item => {
                return { key: $item.attr('name').replaceAll(/\[\]$/g, ''), value: $item.fSelectedValues() };
            });
            doAjax('../session/setSession', {
                session_key: $session_key.val(),
                value: JSON.stringify(data)
            }, (res) => {
                if (!res.error) {
                    $session_key.val(res['session_key']);
                    $('input[name="connectionInit_search_init"]').val(1);
                    $('input[name="delete_search_init"]').val(null);
                    doGet('./connectionInitSearch', true);
                } else {
                    alert(JSON.stringify(res));
                }
            }, (errorRes) => {
                alert("セッションが切れました。\nブラウザ更新を行い、再度ログインを行ってください。");
            });
        }
    }

    // 検索条件をリセット
    function connectionInitSearchInit(){
        $(".connectionInit_condition input").val('');
        // グループ選択を初期リセット
        connectionPulldownInit();
    }

    // グループ選択を初期リセット
    function connectionPulldownInit(){
        // グループ選択を初期リセット
        if(groupsInit.length>0) {
            const $groupSelect = $("#connectionInit_m1");
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
        const $deviceSelect = $("#connectionInit_m2");
        $deviceSelect.empty();
        devices.forEach(device=>{
            {literal}
            $deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
            {/literal}
        });
        $deviceSelect.data('fSelect').destroy();
        $deviceSelect.data('fSelect').create();
    }

    // open カメラ初期化確認モーダル
    function deviceInit(deviceId){
        $("input[name='connectionInit_device_id']").val(deviceId);
        $("#device_init_deviceId").text($("#connectionInit_device_tr_" + deviceId + " .serialNo").text());
        $("#device_init_deviceName").text($("#connectionInit_device_tr_" + deviceId + " .deviceName").text());
        showModal("カメラ接続の初期化", $("#device_init_modal_template").html());
    }
</script>

<!-- カメラ初期化確認モーダル -->
<div id="device_init_modal_template" style="display:none">
    シリアル番号：<span id="device_init_deviceId"></span>、カメラ名称：<span id="device_init_deviceName"></span>を初期化します。<br>
    初期化するとfacet{if ENABLE_AWS}Cloud{/if}の接続が再セットアップするまで出来なくなります。<br>
    本当によろしいですか？<br>
    <br><br><br>

    <div class="dialog_btn_wrap" style="margin-top:2em">
        <a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
        <a href="javascript:void(0)" onclick="doPost('./initDevice',false)" class="enter-submit btn btn_red btn_regist"> 初期化する</a>
    </div>
</div>

<h2 class="tit_cnt_main">カメラの接続初期化</h2>
<p class="cap_cnt_main">
    <span>facet{if ENABLE_AWS}Cloud{/if}の接続設定を初期化します。初期化することにより、FaceFCを初期セットアップ手順にて再セットアップ可能になります。</span><br>
    <span style="color: red">※初期化を行うと、再セットアップを行うまでfacet{if ENABLE_AWS}Cloud{/if}との接続ができなくなります。実施する場合は十分ご注意ください。</span>
</p>

<div class="search_area">
    {include file="./search_area.tpl" prefix="connectionInit_"}
    <div class="devicemaintenancebtn_wrap">
        <a href="javascript:void(0)" onclick="connectionInitSearch();" class="enter-submit btn_red"><i class="fas fa-search"></i>検索実行</a>
        <a href="javascript:void(0)" onclick="connectionInitSearchInit()" value="Reset" id="list_ResetBtn" class="btn_blue list_resetBtn" >条件リセット</a>
    </div>
</div>

<input type="hidden" id="connectionInit_search_init" name="connectionInit_search_init" value="{$form.connectionInit_search_init|default:""}" />
<input type="hidden" id="connectionInit_device_id" name="connectionInit_device_id" />
<input type="hidden" name="connectionInit_init_back" value="{$form.connectionInit_init_back|default:""}" />

{if isset($connectionInit_list)}
    <div class="search_results">
        <div class="tit_wrap">
            <h3 class="tit">検索結果</h3>
        </div>

        {include file="../_inc/pager_counter_deviceMaintenance.tpl" pageInfo=$connectionInit_pageInfo topPager=true}

        <table class="search_results_table">
            <tr>
                <th class="">No</th>
                <th class="">シリアルNo</th>
                <th class="">カメラグループ</th>
                <th class="">カメラ名称</th>
                <th class="">初期化</th>
            </tr>
            {foreach $connectionInit_list as $item}
                <tr id="connectionInit_device_tr_{$item.device_id}">
                    <td class="sortOrder">{$item.sort_order}</td>
                    <td class="serialNo">{$item.serial_no}</td>
                    <td class="groupName">{$item.group_name}</td>
                    <td class="deviceName">{$item.device_name}</td>
                    <td><a href="javascript:void(0)" onclick="deviceInit('{$item.device_id}')" class="btn btn_small">初期化</a></td>
                </tr>
            {/foreach}
        </table>

    </div>

    {include file="../_inc/pager_counter_deviceMaintenance.tpl" pageInfo=$connectionInit_pageInfo topPager=false}

{/if}