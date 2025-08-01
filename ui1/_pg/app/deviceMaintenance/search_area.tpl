{* dev founder feihan *}

<script>
$(function() {
    $("#{$prefix}m1").fSelect();
    $("#{$prefix}m2").fSelect();
    $("#{$prefix}m1").on('pulldownChange',function () {
        showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
        const $wrap = $(this).closest('.fs-wrap')
        $("#modal_message #groupsChangeModalBtnCancel").click(function () {
            $wrap.fSelectedValues($wrap.data('oldVal'))
            removeModal()
        })
        $("#modal_message #groupsChangeModalBtnOk").click(function () {
            const newVal = $wrap.fSelectedValues()
            $wrap.data('oldVal',newVal)
            const $deviceSelect = $("#{$prefix}m2")
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
    });
    var serialNo = $("input[name='{$prefix}serialNo']").val();
    if(serialNo){
        $(".{$prefix}condition .fs-label-wrap").addClass("fs-label-wrap_disabled");
    }
});

</script>


<table class="form_cnt regist_cnt">
    <tr class="{$prefix}condition">
        <th class="tit">シリアルNoから検索</th>
        <td style="height:23px;"></td>
    </tr>
    <tr class="{$prefix}condition">
        <th >シリアルNo</th>
        <td><input type="text" maxlength="11" name="{$prefix}serialNo" value="{$form["`$prefix`serialNo"]|default:""}"></td>
    </tr>
    <tr>
        <td colspan="2">
            <div style="border-top: dashed 2px #4b515e"></div>
        </td>
    </tr>
    <tr class="{$prefix}condition">
        <th class="tit">グループ・カメラから選択</th>
        <td style="font-size: 12px">※シリアルNoを未入力にする必要があります</td>
    </tr>
    {assign var=devicesDisplay value=[]}
    {if empty(Session::getLoginUser("group_id"))}
    <tr class="{$prefix}condition">
        <th class="fs-select-th-center">グループ選択</th>
        <td colspan="2" style="font-size: 0;">
            <select id="{$prefix}m1" class="groups hidden" name="{$prefix}group_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
                {foreach $groupsDisplay as $g=>$group}
                    {$selected = ""}
                    {if exists($form["`$prefix`group_ids"], $g)}
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
    <tr class="{$prefix}condition">
        <th class="fs-select-th-center">カメラ選択</th>
        <td colspan="2">
            <div class="fs-select-center">
                <select id="{$prefix}m2" class="devices hidden" name="{$prefix}device_ids[]" multiple="multiple" disabled="disabled"> {* setSessionの場合のみ送信 *}
                    {foreach $devices as $d=>$device}
                        {if exists($devicesDisplay, $d)}
                            <option value="{$d}" {if exists($form["`$prefix`device_ids"], $d)}selected{/if}>{$device.name}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </td>
    </tr>
</table>