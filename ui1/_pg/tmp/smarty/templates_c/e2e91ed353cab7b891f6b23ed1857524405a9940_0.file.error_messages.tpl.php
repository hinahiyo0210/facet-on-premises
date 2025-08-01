<?php
/* Smarty version 4.5.3, created on 2024-09-18 16:11:43
  from '/var/www/html/ui1/_pg/app/_inc/error_messages.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ea7d2fad8bd3_70884714',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e2e91ed353cab7b891f6b23ed1857524405a9940' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/_inc/error_messages.tpl',
      1 => 1703242896,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ea7d2fad8bd3_70884714 (Smarty_Internal_Template $_smarty_tpl) {
?><div id="error_message">
	
	<div class="err_msg_title">
		確認を行ってください。<span class="material-icons">expand</span>
	</div>
	
	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['messages']->value, 'msgArr', false, 'name');
$_smarty_tpl->tpl_vars['msgArr']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['name']->value => $_smarty_tpl->tpl_vars['msgArr']->value) {
$_smarty_tpl->tpl_vars['msgArr']->do_else = false;
?>
		<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['msgArr']->value, 'msg');
$_smarty_tpl->tpl_vars['msg']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['msg']->value) {
$_smarty_tpl->tpl_vars['msg']->do_else = false;
?>
			<?php if (!empty($_smarty_tpl->tpl_vars['msg']->value)) {?>
				<div class="err_msg_item">
					<?php echo $_smarty_tpl->tpl_vars['msg']->value;?>

				</div>
			<?php }?>
		<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

</div>

<div id="mask"></div>

<?php echo '<script'; ?>
>
	$(function() {
		$(".main_container").addClass("errored");

		$(".err_msg_title").click(function() {
			
			if ($("#error_message").hasClass("min")) {
				
				$("#error_message").removeClass("min");
				$(".main_container").addClass("errored");
				
			} else {
				$("#error_message").addClass("min");
				$(".main_container").removeClass("errored");
				
			}
			
		});

	});
	
	$("#mask").fadeOut(1000, function() {
		$("#error_message").addClass("to_bottom");
		setTimeout(function() {
			<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['messages']->value, 'msgArr', false, 'name');
$_smarty_tpl->tpl_vars['msgArr']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['name']->value => $_smarty_tpl->tpl_vars['msgArr']->value) {
$_smarty_tpl->tpl_vars['msgArr']->do_else = false;
?>
				<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['msgArr']->value, 'msg');
$_smarty_tpl->tpl_vars['msg']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['msg']->value) {
$_smarty_tpl->tpl_vars['msg']->do_else = false;
?>
					<?php if (!empty($_smarty_tpl->tpl_vars['msg']->value)) {?>
						$(function() {
							$(byMessageName("<?php echo hjs($_smarty_tpl->tpl_vars['name']->value);?>
")).each(function() {
								if ($(this).prop("tagName").toLowerCase() == "select") {
									$(this).closest("p.select").addClass("error").hide().fadeIn(2000);
									
								} else if ($(this).prop("type").toLowerCase() == "hidden" || $(this).prop("type").toLowerCase() == "file") {
									$($(this).attr("error-target")).addClass("error").hide().fadeIn(2000);
								} else if ($(this).prop("type").toLowerCase() == "checkbox" || $(this).prop("type").toLowerCase() == "radio") {
									$(this).next().addClass("error").hide().fadeIn(2000);
								} else {
									$(this).addClass("error").hide().fadeIn(2000);
								}

							});
							
							
						});
					<?php }?>
				<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			
		}, 200);
	});

	
<?php echo '</script'; ?>
>

<?php }
}
