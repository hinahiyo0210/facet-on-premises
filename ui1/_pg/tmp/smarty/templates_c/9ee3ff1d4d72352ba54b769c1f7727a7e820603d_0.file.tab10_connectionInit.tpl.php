<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:14:14
  from '/var/www/html/ui1/_pg/app/deviceMaintenance/tab10_connectionInit.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688188e6c773a4_07038126',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9ee3ff1d4d72352ba54b769c1f7727a7e820603d' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/deviceMaintenance/tab10_connectionInit.tpl',
      1 => 1725590513,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./search_area.tpl' => 1,
    'file:../_inc/pager_counter_deviceMaintenance.tpl' => 2,
  ),
),false)) {
function content_688188e6c773a4_07038126 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>
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
                
                $groupSelect.append(`<option value="${group.id}" selected >${group.name}</option>`)
                
            });
            $groupSelect.data('fSelect').destroy();
            $groupSelect.data('fSelect').create();
        }
        // カメラ選択を初期リセット
        const $deviceSelect = $("#connectionInit_m2");
        $deviceSelect.empty();
        devices.forEach(device=>{
            
            $deviceSelect.append(`<option value="${device.id}" selected >${device.name}</option>`)
            
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
<?php echo '</script'; ?>
>

<!-- カメラ初期化確認モーダル -->
<div id="device_init_modal_template" style="display:none">
    シリアル番号：<span id="device_init_deviceId"></span>、カメラ名称：<span id="device_init_deviceName"></span>を初期化します。<br>
    初期化するとfacet<?php if (ENABLE_AWS) {?>Cloud<?php }?>の接続が再セットアップするまで出来なくなります。<br>
    本当によろしいですか？<br>
    <br><br><br>

    <div class="dialog_btn_wrap" style="margin-top:2em">
        <a href="javascript:void(0);" onclick="removeModal()" class="btn btn_gray">閉じる</a>
        <a href="javascript:void(0)" onclick="doPost('./initDevice',false)" class="enter-submit btn btn_red btn_regist"> 初期化する</a>
    </div>
</div>

<h2 class="tit_cnt_main">カメラの接続初期化</h2>
<p class="cap_cnt_main">
    <span>facet<?php if (ENABLE_AWS) {?>Cloud<?php }?>の接続設定を初期化します。初期化することにより、FaceFCを初期セットアップ手順にて再セットアップ可能になります。</span><br>
    <span style="color: red">※初期化を行うと、再セットアップを行うまでfacet<?php if (ENABLE_AWS) {?>Cloud<?php }?>との接続ができなくなります。実施する場合は十分ご注意ください。</span>
</p>

<div class="search_area">
    <?php $_smarty_tpl->_subTemplateRender("file:./search_area.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('prefix'=>"connectionInit_"), 0, false);
?>
    <div class="devicemaintenancebtn_wrap">
        <a href="javascript:void(0)" onclick="connectionInitSearch();" class="enter-submit btn_red"><i class="fas fa-search"></i>検索実行</a>
        <a href="javascript:void(0)" onclick="connectionInitSearchInit()" value="Reset" id="list_ResetBtn" class="btn_blue list_resetBtn" >条件リセット</a>
    </div>
</div>

<input type="hidden" id="connectionInit_search_init" name="connectionInit_search_init" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['connectionInit_search_init'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />
<input type="hidden" id="connectionInit_device_id" name="connectionInit_device_id" />
<input type="hidden" name="connectionInit_init_back" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['connectionInit_init_back'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" />

<?php if ((isset($_smarty_tpl->tpl_vars['connectionInit_list']->value))) {?>
    <div class="search_results">
        <div class="tit_wrap">
            <h3 class="tit">検索結果</h3>
        </div>

        <?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_deviceMaintenance.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['connectionInit_pageInfo']->value,'topPager'=>true), 0, false);
?>

        <table class="search_results_table">
            <tr>
                <th class="">No</th>
                <th class="">シリアルNo</th>
                <th class="">カメラグループ</th>
                <th class="">カメラ名称</th>
                <th class="">初期化</th>
            </tr>
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['connectionInit_list']->value, 'item');
$_smarty_tpl->tpl_vars['item']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['item']->do_else = false;
?>
                <tr id="connectionInit_device_tr_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
">
                    <td class="sortOrder"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['sort_order'] ));?>
</td>
                    <td class="serialNo"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['serial_no'] ));?>
</td>
                    <td class="groupName"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['group_name'] ));?>
</td>
                    <td class="deviceName"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_name'] ));?>
</td>
                    <td><a href="javascript:void(0)" onclick="deviceInit('<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['item']->value['device_id'] ));?>
')" class="btn btn_small">初期化</a></td>
                </tr>
            <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        </table>

    </div>

    <?php $_smarty_tpl->_subTemplateRender("file:../_inc/pager_counter_deviceMaintenance.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('pageInfo'=>$_smarty_tpl->tpl_vars['connectionInit_pageInfo']->value,'topPager'=>false), 0, true);
?>

<?php }
}
}
