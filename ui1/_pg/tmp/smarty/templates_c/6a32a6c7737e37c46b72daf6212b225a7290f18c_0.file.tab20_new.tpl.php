<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:14:14
  from '/var/www/html/ui1/_pg/app/deviceMaintenance/tab20_new.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688188e6cae063_46578681',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '6a32a6c7737e37c46b72daf6212b225a7290f18c' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/deviceMaintenance/tab20_new.tpl',
      1 => 1725590302,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688188e6cae063_46578681 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>
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
<?php echo '</script'; ?>
>

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
<?php if (!$_smarty_tpl->tpl_vars['form']->value['newFlag']) {?>
    <p class="cap_cnt_main">
        登録できるカメラ台数がfacet<?php if (ENABLE_AWS) {?>Cloud<?php }?>の契約台数の上限に達しているため新規登録ができません。<br>
        現在の契約台数は<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['allowDeviceNum']->value ));?>
台です。登録台数を増加させる場合は、営業担当者までご連絡ください。<br>
        故障によるカメラ交換を行う場合は、「カメラ削除」にて故障したカメラを削除してから実施してください。
    </p>
<?php } else { ?>
    <p class="cap_cnt_main">
        カメラの新規登録を行います。
    </p>
    <?php if (!empty($_smarty_tpl->tpl_vars['allowDeviceNum']->value)) {?>
        <p class="cap_cnt_main">
            現在の契約台数は<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['allowDeviceNum']->value ));?>
台です。残り<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['allowDeviceNum']->value-count($_smarty_tpl->tpl_vars['devices']->value) ));?>
台の新規登録ができます
        </p>
    <?php }?>
    <input type="hidden" name="newFlag" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['newFlag'] ));?>
"/>
    <table class="form_cnt regist_cnt id_manage_new_tbl">
        <tr>
            <th>シリアルNo<span class="required">※</span></th>
            <td>
                <input type="text" name="new_serialNo" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_serialNo'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" maxlength="11">
            </td>
        </tr>
        <tr>
            <th>型番<span class="required">※</span></th>
            <td>
                <p class="select">
                    <select name="new_device_type_id">
                        <option value=""></option>
                        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['deviceTypeList']->value, 'deviceType', false, 'device_type_id');
$_smarty_tpl->tpl_vars['deviceType']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['device_type_id']->value => $_smarty_tpl->tpl_vars['deviceType']->value) {
$_smarty_tpl->tpl_vars['deviceType']->do_else = false;
?>
                            <option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['new_device_type_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['device_type_id']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device_type_id']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['deviceType']->value['device_type'] ));?>
</option>
                        <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                    </select>
                </p>
            </td>
        </tr>
        <tr>
            <th>カメラ名称</th>
            <td>
                <input type="text" name="new_deviceName" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['new_deviceName'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
">
            </td>
        </tr>
        <tr><th class="fs-select-th-center">カメラグループ</th>
            <td>
                <select id="set_device_group_id" name="device_group_id">
                    <option value="">&nbsp;</option>
                    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['deviceGroupList']->value, 'deviceGroup', false, 'device_group_id');
$_smarty_tpl->tpl_vars['deviceGroup']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['device_group_id']->value => $_smarty_tpl->tpl_vars['deviceGroup']->value) {
$_smarty_tpl->tpl_vars['deviceGroup']->do_else = false;
?>
                        <option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['device_group_id'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['device_group_id']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device_group_id']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['deviceGroup']->value['group_name'] ));?>
</option>
                    <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                </select>
            </td>
        </tr>
                <?php if (Session::getLoginUser("enter_exit_mode_flag") == 1) {?>
            <tr>
                <th>カメラ機能</th>
                <td>
                    <p class="select">
                        <select name="new_device_role">
                            <option value="">&nbsp;</option>
                            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['deviceRoles']->value, 'deviceRole', false, 'device_role');
$_smarty_tpl->tpl_vars['deviceRole']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['device_role']->value => $_smarty_tpl->tpl_vars['deviceRole']->value) {
$_smarty_tpl->tpl_vars['deviceRole']->do_else = false;
?>
                                <option <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['new_device_role'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == $_smarty_tpl->tpl_vars['device_role']->value) {?>selected<?php }?> value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device_role']->value ));?>
" ><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['deviceRole']->value['device_role_name'] ));?>
</option>
                            <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                        </select>
                    </p>
                </td>
            </tr>
        <?php }?>
            </table>
    <a href="javascript:void(0)" onclick="insertDevice()" class="enter-submit btn_red btn_regist">登録</a>
<?php }
}
}
