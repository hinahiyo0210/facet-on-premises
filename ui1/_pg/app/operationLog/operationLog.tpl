{* dev founder luyi *}

{$title="操作ログ"}{$icon="fa-user-clock"}{include file=$smarty.const.DIR_APP|cat:'/_inc/header.tpl'}
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">
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
<script>
    // タブ制御。
    $(function() {
        $(".tab_btn li").click(function() {
            var url = "./";
            if ($(this).attr("tab-name")) url = "./?tab=" + $(this).attr("tab-name");
            history.pushState("", "", url);
            document.operationLogForm.tab.value = $(this).attr("tab-name");
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
        disableEmptyInput($(document.operationLogForm));
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
        document.operationLogForm.method = method;
        document.operationLogForm.action = action;
        document.operationLogForm.submit();
    }
</script>

<form name="operationLogForm" action="./" method="post">
    <input type="hidden" name="tab" value="{$form.tab}" />
    <input type="hidden" name="_p" />
    <div class="tab_container">

        <ul class="tab_btn">
            <li tab-name="facet"{if $form.tab == "facet"}class="active"{/if}>facetログ</li>
            <li tab-name="facefc"{if $form.tab == "facefc"}class="active"{/if}>FaceFCログ</li>
        </ul>
        <div class="tab_cnt_wrap">
            <div class="tab_cnt{if $form.tab == "facet"} show{/if}">{include file="./tab10_facet.tpl" }</div>
            <div class="tab_cnt{if $form.tab == "facefc"} show{/if}">{include file="./tab20_facefc.tpl"}</div>
        </div>

    </div>
</form>
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
{include file=$smarty.const.DIR_APP|cat:'/_inc/footer.tpl'}