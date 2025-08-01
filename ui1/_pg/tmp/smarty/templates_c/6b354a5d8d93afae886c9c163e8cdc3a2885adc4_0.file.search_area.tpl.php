<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:14:14
  from '/var/www/html/ui1/_pg/app/deviceMaintenance/search_area.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688188e6c92cf0_76176419',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '6b354a5d8d93afae886c9c163e8cdc3a2885adc4' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/deviceMaintenance/search_area.tpl',
      1 => 1721805127,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_688188e6c92cf0_76176419 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>
$(function() {
    $("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1").fSelect();
    $("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2").fSelect();
    $("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1").on('pulldownChange',function () {
        showModal("カメラ選択の初期化", $("#groupsChangeModalTemplate").html());
        const $wrap = $(this).closest('.fs-wrap')
        $("#modal_message #groupsChangeModalBtnCancel").click(function () {
            $wrap.fSelectedValues($wrap.data('oldVal'))
            removeModal()
        })
        $("#modal_message #groupsChangeModalBtnOk").click(function () {
            const newVal = $wrap.fSelectedValues()
            $wrap.data('oldVal',newVal)
            const $deviceSelect = $("#<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2")
            const dids = newVal.flatMap(gid=>groups[gid])
            $deviceSelect.empty()
            devices.forEach(device=>{
                if (dids.indexOf(device.id)===-1) {
                    return
                }
                
                $deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
                
            })
            $deviceSelect.data('fSelect').destroy()
            $deviceSelect.data('fSelect').create()
            removeModal()
        })
    });
    var serialNo = $("input[name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
serialNo']").val();
    if(serialNo){
        $(".<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition .fs-label-wrap").addClass("fs-label-wrap_disabled");
    }
});

<?php echo '</script'; ?>
>


<table class="form_cnt regist_cnt">
    <tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
        <th class="tit">シリアルNoから検索</th>
        <td style="height:23px;"></td>
    </tr>
    <tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
        <th >シリアルNo</th>
        <td><input type="text" maxlength="11" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
serialNo" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."serialNo"] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
"></td>
    </tr>
    <tr>
        <td colspan="2">
            <div style="border-top: dashed 2px #4b515e"></div>
        </td>
    </tr>
    <tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
        <th class="tit">グループ・カメラから選択</th>
        <td style="font-size: 12px">※シリアルNoを未入力にする必要があります</td>
    </tr>
    <?php $_smarty_tpl->_assignInScope('devicesDisplay', array());?>
    <?php if (empty(Session::getLoginUser("group_id"))) {?>
    <tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
        <th class="fs-select-th-center">グループ選択</th>
        <td colspan="2" style="font-size: 0;">
            <select id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m1" class="groups hidden" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
group_ids[]" multiple="multiple" disabled="disabled">                 <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'g');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['g']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
                    <?php $_smarty_tpl->_assignInScope('selected', '');?>
                    <?php if (exists($_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."group_ids"],$_smarty_tpl->tpl_vars['g']->value)) {?>
                        <?php $_smarty_tpl->_assignInScope('selected', "selected");?>
                        <?php $_smarty_tpl->_assignInScope('devicesDisplay', array_merge($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['group']->value['deviceIds']));?>
                    <?php }?>
                    <option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['g']->value ));?>
" <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['selected']->value ));?>
><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['group']->value['group_name'] ));?>
</option>
                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            </select>
        </td>
    </tr>
    <?php } else { ?>
    <?php $_smarty_tpl->_assignInScope('devicesDisplay', array_keys($_smarty_tpl->tpl_vars['devices']->value));?>
    <?php }?>
    <tr class="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
condition">
        <th class="fs-select-th-center">カメラ選択</th>
        <td colspan="2">
            <div class="fs-select-center">
                <select id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
m2" class="devices hidden" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['prefix']->value ));?>
device_ids[]" multiple="multiple" disabled="disabled">                     <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
                        <?php if (exists($_smarty_tpl->tpl_vars['devicesDisplay']->value,$_smarty_tpl->tpl_vars['d']->value)) {?>
                            <option value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
" <?php if (exists($_smarty_tpl->tpl_vars['form']->value[((string)$_smarty_tpl->tpl_vars['prefix']->value)."device_ids"],$_smarty_tpl->tpl_vars['d']->value)) {?>selected<?php }?>><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['device']->value['name'] ));?>
</option>
                        <?php }?>
                    <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                </select>
            </div>
        </td>
    </tr>
</table><?php }
}
