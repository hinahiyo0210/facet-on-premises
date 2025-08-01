<?php
/* Smarty version 4.5.3, created on 2025-07-24 10:14:14
  from '/var/www/html/ui1/_pg/app/deviceMaintenance/deviceMaintenance.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_688188e6c5cde1_76214232',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4aff18274ad8000bbfac62959be32192d86de828' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/deviceMaintenance/deviceMaintenance.tpl',
      1 => 1721804972,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:./tab10_connectionInit.tpl' => 1,
    'file:./tab20_new.tpl' => 1,
    'file:./tab30_delete.tpl' => 1,
  ),
),false)) {
function content_688188e6c5cde1_76214232 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "端末メンテナンス");
$_smarty_tpl->_assignInScope('icon', "fa-cogs");
$_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/header.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
echo '<script'; ?>
 src="/ui1/static/js/fselect/fSelect.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
        const groups = { }
    const groupsInit = []
    <?php if (empty(Session::getLoginUser("group_id"))) {?>
    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['groupsDisplay']->value, 'group', false, 'gid');
$_smarty_tpl->tpl_vars['group']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['gid']->value => $_smarty_tpl->tpl_vars['group']->value) {
$_smarty_tpl->tpl_vars['group']->do_else = false;
?>
    groups[<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['gid']->value ));?>
] = [ <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['group']->value['deviceIds'], 'deviceId');
$_smarty_tpl->tpl_vars['deviceId']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['deviceId']->value) {
$_smarty_tpl->tpl_vars['deviceId']->do_else = false;
?> '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['deviceId']->value ));?>
', <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?> ]
    groupsInit.push( { id: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['gid']->value ));?>
', name: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtr((string)$_smarty_tpl->tpl_vars['group']->value['group_name'], array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", 
                       "\n" => "\\n", "</" => "<\/", "<!--" => "<\!--", "<s" => "<\s", "<S" => "<\S",
                       "`" => "\\`", "\${" => "\\\$\{")) ));?>
' } )
    <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
    <?php }?>
    const devices = []
    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['devices']->value, 'device', false, 'd');
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['d']->value => $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
?>
    devices.push( { id: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
', name: '<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( strtr((string)$_smarty_tpl->tpl_vars['device']->value['name'], array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", 
                       "\n" => "\\n", "</" => "<\/", "<!--" => "<\!--", "<s" => "<\s", "<S" => "<\S",
                       "`" => "\\`", "\${" => "\\\$\{")) ));?>
' } )
    <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

<?php echo '</script'; ?>
>
<link href="/ui1/static/css/fselect/fSelect.css" rel="stylesheet" type="text/css">

<?php echo '<script'; ?>
>

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

<?php echo '</script'; ?>
>

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
    <input type="hidden" name="_form_session_key" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['form']->value['_form_session_key'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
" >
    <input type="hidden" name="tab" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['tab'] ));?>
" />
    <input type="hidden" name="_p" />
    <div class="tab_container">

        <ul class="tab_btn">
            <li tab-name=""       <?php if (empty($_smarty_tpl->tpl_vars['form']->value['tab']) || $_smarty_tpl->tpl_vars['form']->value['tab'] == "connectionInit") {?>class="active"<?php }?>>カメラ接続初期化</li>
            <li tab-name="new"    <?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "new") {?>class="active"<?php }?>>カメラ新規登録</li>
            <li tab-name="delete" <?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "delete") {?>class="active"<?php }?>>カメラ削除</li>
        </ul>
        <div class="tab_cnt_wrap">
            <div class="tab_cnt<?php if (empty($_smarty_tpl->tpl_vars['form']->value['tab']) || $_smarty_tpl->tpl_vars['form']->value['tab'] == "connectionInit") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab10_connectionInit.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
            <div class="tab_cnt<?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "new") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab20_new.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
            <div class="tab_cnt<?php if ($_smarty_tpl->tpl_vars['form']->value['tab'] == "delete") {?> show<?php }?>"><?php $_smarty_tpl->_subTemplateRender("file:./tab30_delete.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></div>
        </div>

    </div>
</form>

<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
