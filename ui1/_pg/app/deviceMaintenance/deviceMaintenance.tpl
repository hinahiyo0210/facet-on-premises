{* dev founder feihan *}

{$title="端末メンテナンス"}{$icon="fa-cogs"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}
<script src="/ui1/static/js/fselect/fSelect.js"></script>
<script>
    {* fSelect用定数 *}
    const groups = { }
    const groupsInit = []
    {if empty(Session::getLoginUser("group_id"))}
    {foreach $groupsDisplay as $gid=>$group}
    groups[{$gid}] = [ {foreach $group.deviceIds as $deviceId} '{$deviceId}', {/foreach} ]
    groupsInit.push( { id: '{$gid}', name: '{$group.group_name|escape:"javascript"}' } )
    {/foreach}
    {/if}
    const devices = []
    {foreach $devices as $d=>$device}
    devices.push( { id: '{$d}', name: '{$device.name|escape:"javascript"}' } )
    {/foreach}

</script>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">

<script>

    // タブ制御
    $(function() {
        $(".tab_btn li").click(function() {
            var url = "./";
            if ($(this).attr("tab-name")) url = "./?tab=" + $(this).attr("tab-name");
            history.pushState("", "", url);
            document.deviceMaintenanceForm.tab.value = $(this).attr("tab-name");
        });
    });
    // 送信。
    function doPost(action, scrollSave) {
        $(".no-req").val("");
        doFormSend(action, scrollSave, "post");
    }
    function doGet(action, scrollSave) {
        // URLが長くなりすぎないように、値の無いinput類はdisabledにする。
        $(".no-req").val("");
        disableEmptyInput($(document.deviceMaintenanceForm));
        doFormSend(action, scrollSave, "get");
    }

    function doFormSend(action, scrollSave, method) {

        if (scrollSave) {
            $("input[name='_p']").val(parseInt($(window).scrollTop()).toString(36)).prop("disabled", false);
        }

        $("input").each(function() {
            if ($(this).attr("post-only") && $(this).attr("post-only") != action) {
                $(this).val("");
            }
        });


        document.deviceMaintenanceForm.method = method;
        document.deviceMaintenanceForm.action = action;
        document.deviceMaintenanceForm.submit();
    }

</script>

<!-- カメラルダウン変更確認モーダル -->
<div id="groupsChangeModalTemplate" style="display:none">
    <div style="">
        <div style="height: 150px;">
            カメラ選択が初期化されますがよろしいですか？
        </div>
        <div class="dialog_btn_wrap btns center">
            <a href="javascript:void(0);" id="groupsChangeModalBtnCancel" class="btn btn_gray" >いいえ</a>
            <a href="javascript:void(0);" id="groupsChangeModalBtnOk"  class="btn btn_red">はい</a>
        </div>
    </div>
</div>

<form name="deviceMaintenanceForm" action="./" method="post">
    <input type="hidden" name="_form_session_key" value="{$form._form_session_key|default:""}" >
    <input type="hidden" name="tab" value="{$form.tab}" />
    <input type="hidden" name="_p" />
    <div class="tab_container">

        <ul class="tab_btn">
            <li tab-name=""       {if empty($form.tab) || $form.tab == "connectionInit"     }class="active"{/if}>カメラ接続初期化</li>
            <li tab-name="new"    {if $form.tab == "new"   }class="active"{/if}>カメラ新規登録</li>
            <li tab-name="delete" {if $form.tab == "delete"}class="active"{/if}>カメラ削除</li>
        </ul>
        <div class="tab_cnt_wrap">
            <div class="tab_cnt{if empty($form.tab) || $form.tab == "connectionInit"} show{/if}">{include file="./tab10_connectionInit.tpl"  }</div>
            <div class="tab_cnt{if $form.tab == "new"   } show{/if}">{include file="./tab20_new.tpl"   }</div>
            <div class="tab_cnt{if $form.tab == "delete"} show{/if}">{include file="./tab30_delete.tpl"}</div>
        </div>

    </div>
</form>

{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}