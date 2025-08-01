<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:14:14
  from '/var/www/html/ui1/_pg/app/deviceMaintenance/tab30_delete.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688188e6cc28e8_77760471',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7fda6b7a5ab2493e7c917b0b29a872f20c048349' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/deviceMaintenance/tab30_delete.tpl',
      1 => 1725590436,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./search_area.tpl' => 1,
    'file:../_inc/pager_counter_deviceMaintenance.tpl' => 2,
  ),
),false)) {
function content_688188e6cc28e8_77760471 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>
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
                
                $groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
                
            });
            $groupSelect.data('fSelect').destroy();
            $groupSelect.data('fSelect').create();
        }
        // カメラ選択を初期リセット
        const $deviceSelect = $("#delete_m2");
        $deviceSelect.empty();
        devices.forEach(device=>{
            
            $deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
            
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

<?php echo '</script'; ?>
>

<!-- カメラ削除確認モーダル -->
<div id="device_delete_modal_template" style="display:none">
    シリアル番号：<span id="device_delete_deviceId"></span>、カメラ名称：<span id="device_delete_deviceName"></span>を削除します。<br>
    削除するとfacet<?php if (ENABLE_AWS) {?>Cloud<?php }?>にて該当カメラの操作ができなくあり、ログ情報も消去されます。<br>
    本当によろしいですか？<br>
    <br><br><br>

    <div class="dialog_btn_wrap" style="margin-top:2em">
        <a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
        <a href="javascript:void(0)" onclick="doPost('./deleteDevice',false)" class="enter-submit btn btn_red btn_regist"> 削除する</a>
    </div>
</div>

<h2 class="tit_cnt_main">カメラの削除</h2>
<p class="cap_cnt_main">
    <span style="color: red">※削除を行うと、facet<?php if (ENABLE_AWS) {?>Cloud<?php }?>にて該当カメラの操作ができなくなり、ログ情報も消去されます。実施する場合は十分注意ください。</span>
</p>

<div class="search_area">
    <?php $_smarty_tpl->_subTemplateRender("file:./search_area.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('prefix'=>"delete_"), 0, false);
?>
    <div class="devicemaintenancebtn_wrap">
        <a href="javascript:void(0)" onclick="deleteSearch();" class="enter-submit btn_red"><i class="fas fa-search"></i>検索実行</a>
        <a href="javascript:void(0)" onclick="deleteSearchInit()" value="Reset" id="list_ResetBtn" class="btn_blue list_resetBtn" >条件リセット</a>
    </div>
</div>

<input type="hidden" id="delete_search_init" name="delete_search_init" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['delete_search_init'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
<input type="hidden" id="delete_device_id" name="delete_device_id" />
<input type="hidden" name="delete_back" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['delete_back'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />

<?php if ((isset($_smarty_tpl->tpl_vars['delete_list']->value))) {?>
    <div class="search_results">
        <div class="tit_wrap">
            <h3 class="tit">検索結果</h3>
        </div>

        <?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_deviceMaintenance.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['delete_pageInfo']->value,'topPager'=>true), 0, false);
?>

        <table class="search_results_table">
            <tr>
                <th class="">No</th>
                <th class="">シリアルNo</th>
                <th class="">カメラグループ</th>
                <th class="">カメラ名称</th>
                <th class="">削除</th>
            </tr>
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['delete_list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
                <tr id="delete_device_tr_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
">
                    <td class="sortOrder"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['sort_order'] ));?>
</td>
                    <td class="serialNo"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['serial_no'] ));?>
</td>
                    <td class="groupName"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['group_name'] ));?>
</td>
                    <td class="deviceName"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_name'] ));?>
</td>
                    <td><a href="javascript:void(0)" onclick="deviceDelete('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
')" class="btn btn_small">削除</a></td>
                </tr>
            <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        </table>

    </div>

    <?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_deviceMaintenance.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['delete_pageInfo']->value,'topPager'=>false), 0, true);
?>

<?php }
}
}
