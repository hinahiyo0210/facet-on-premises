{* dev founder feihan *}

<script>
    // データ登録
    function insertDevice(){
        if(check()){
            showModal("カメラの新規登録", $("#device_newErr_modal_template").html());
        }else{
            $("#device_new_serialNo").text($("input[name=new_serialNo]").val());
            var option = $("select[name='new_device_type_id']").find("option:selected");
            $("#device_new_deviceType").text(option.text());
            showModal("カメラの新規登録", $("#device_new_modal_template").html());
        }
    }

    // 入力チェック
    function check(){
        $("#serialNoMsg").text("");
        $("#deviceTypeMsg").text("")
        var result = false;
        var serialNoErr = false;
        var serialNo = $("input[name=new_serialNo]").val();
        if(!serialNo || !isHanEisu(serialNo) || serialNo.length !== 11){
            $("#serialNoMsg").text("シリアルNoを正しく入力してください。");
            $("#serialNoMsg").append("<br>");
            serialNoErr = true;
            result = true;
        }
        var option = $("select[name='new_device_type_id']").find("option:selected");
        if(!option.val()){
            if(serialNoErr){
                $("#deviceTypeMsg").text("もしくは、型番を指定してください。");
            }else{
                $("#deviceTypeMsg").text("型番を指定してください。");
                $("#deviceTypeMsg").append("<br>");
            }
            result = true;
        }
        return result;
    }

    // 半角英数チェック
    function isHanEisu(str){
        str = (str==null)?"":str;
        if(str.match(/^[A-Za-z0-9]*$/)){
            return true;
        }else{
            return false;
        }
    }

    // selectフォームUI追加
    $(function() {
        $("#set_device_group_id").fSelect();
    });
</script>

<!-- カメラエラーメッセージモーダル -->
<div id="device_newErr_modal_template" style="display:none">
    <span id="serialNoMsg"></span>
    <span id="deviceTypeMsg"></span><br>
    <br><br><br><br>

    <div class="dialog_btn_wrap" style="margin-top:2em">
        <a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
    </div>
</div>

<!-- カメラ新規登録確認モーダル -->
<div id="device_new_modal_template" style="display:none">
    シリアル番号：<span id="device_new_serialNo"></span>、型番：<span id="device_new_deviceType"></span>を登録します。<br>
    本当によろしいですか？<br>
    <br><br><br><br>

    <div class="dialog_btn_wrap" style="margin-top:2em">
        <a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
        <a href="javascript:void(0)" onclick="doPost('./insertDevice', false)" class="enter-submit btn btn_red btn_regist"> 登録する</a>
    </div>
</div>

<h2 class="tit_cnt_main">カメラの新規登録</h2>
{if !$form.newFlag }
    <p class="cap_cnt_main">
        登録できるカメラ台数がfacet{if ENABLE_AWS}Cloud{/if}の契約台数の上限に達しているため新規登録ができません。<br>
        現在の契約台数は{$allowDeviceNum}台です。登録台数を増加させる場合は、営業担当者までご連絡ください。<br>
        故障によるカメラ交換を行う場合は、「カメラ削除」にて故障したカメラを削除してから実施してください。
    </p>
{else}
    <p class="cap_cnt_main">
        カメラの新規登録を行います。
    </p>
    {if !empty($allowDeviceNum)}
        <p class="cap_cnt_main">
            現在の契約台数は{$allowDeviceNum}台です。残り{$allowDeviceNum - count($devices)}台の新規登録ができます
        </p>
    {/if}
    <input type="hidden" name="newFlag" value="{$form.newFlag}"/>
    <table class="form_cnt regist_cnt id_manage_new_tbl">
        <tr>
            <th>シリアルNo<span class="required">※</span></th>
            <td>
                <input type="text" name="new_serialNo" value="{$form.new_serialNo|default:""}" maxlength="11">
            </td>
        </tr>
        <tr>
            <th>型番<span class="required">※</span></th>
            <td>
                <p class="select">
                    <select name="new_device_type_id">
                        <option value=""></option>
                        {foreach $deviceTypeList as $device_type_id=>$deviceType}
                            <option {if $form.new_device_type_id|default:"" == $device_type_id}selected{/if} value="{$device_type_id}" >{$deviceType.device_type}</option>
                        {/foreach}
                    </select>
                </p>
            </td>
        </tr>
        <tr>
            <th>カメラ名称</th>
            <td>
                <input type="text" name="new_deviceName" value="{$form.new_deviceName|default:""}">
            </td>
        </tr>
        <tr><th class="fs-select-th-center">カメラグループ</th>
            <td>
                <select id="set_device_group_id" name="device_group_id">
                    <option value="">&nbsp;</option>
                    {foreach $deviceGroupList as $device_group_id=>$deviceGroup}
                        <option {if $form.device_group_id|default:"" == $device_group_id}selected{/if} value="{$device_group_id}" >{$deviceGroup.group_name}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        {*  add-start verson3.0 founder feihan *}
        {if Session::getLoginUser("enter_exit_mode_flag") == 1}
            <tr>
                <th>カメラ機能</th>
                <td>
                    <p class="select">
                        <select name="new_device_role">
                            <option value="">&nbsp;</option>
                            {foreach $deviceRoles as $device_role=>$deviceRole}
                                <option {if $form.new_device_role|default:"" == $device_role}selected{/if} value="{$device_role}" >{$deviceRole.device_role_name}</option>
                            {/foreach}
                        </select>
                    </p>
                </td>
            </tr>
        {/if}
        {*  add-end verson3.0 founder feihan *}
    </table>
    <a href="javascript:void(0)" onclick="insertDevice()" class="enter-submit btn_red btn_regist">登録</a>
{/if}
