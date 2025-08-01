<?php
/* Smarty version 4.5.3, created on 2024-09-18 16:11:48
  from '/var/www/html/ui1/_pg/app/dashboard/dashboard.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66ea7d34864d91_89448275',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd87b70502cd1c743fccebc68fe53fa33522e5b66' => 
    array (
      0 => '/var/www/html/ui1/_pg/app/dashboard/dashboard.tpl',
      1 => 1721796026,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66ea7d34864d91_89448275 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('title', "ダッシュボード");
$_smarty_tpl->_assignInScope('icon', "fa-tachometer-alt-fast");
$_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/header.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

<style type="text/css">
	form { opacity: 0 }
</style>

<?php echo '<script'; ?>
>

$(function() {
	$("form").animate( { opacity: 1 } );

	$("input[name='statistics_type']").click(function() {
		if ($("input[name='<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['prefix']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
statistics_type']:checked").val() == "month") {
			$(".daily_container").hide();
			$(".month_container").show();
		} else {
			$(".month_container").hide();
			$(".daily_container").show();
		}
	});
	
});

function doGet(action, scrollSave) {
	
	if (scrollSave) {
		$("input[name='_p']").val(parseInt($(window).scrollTop()).toString(36)).prop("disabled", false);
	}

	document.dashboardForm.action = action;
	document.dashboardForm.submit();
}

<?php echo '</script'; ?>
>

<form aciton="" name="dashboardForm" method="get">
	<input type="hidden" name="_p" />
	<div style="border:5px solid #fff;border-radius:6px;">
		<div class="statistics_area_switch" style="padding:10px 20px">
			<div>
				<input <?php if (empty((($tmp = $_smarty_tpl->tpl_vars['form']->value['statistics_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp)) || (($tmp = $_smarty_tpl->tpl_vars['form']->value['statistics_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "month") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['prefix']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
statistics_type" type="radio" value="month"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">月次（<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date("Y年m月") ));?>
）</label>
				<input <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['statistics_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "day") {?>checked<?php }?> id="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId() ));?>
" name="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( (($tmp = $_smarty_tpl->tpl_vars['prefix']->value ?? null)===null||$tmp==='' ? '' ?? null : $tmp) ));?>
statistics_type" type="radio" value="day"><label for="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( seqId(1) ));?>
" class="radio">日次（<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( date("d日") ));?>
）</label>
			</div>
		</div>
		<div class="statistics_area daily_container" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['statistics_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) != "day") {?>style="display:none;"<?php }?>>
			<div id="total" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">合計認証数</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['sum']->value['thisDay']['total_count']) ));?>
</span>名</p>
				</div>
				<div class="icon"><i class="fas fa-users"></i></div>
			</div>
			<div id="success_rate" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">認証成功率</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisDay']['success_ratio'] ));?>
</span>%</p>
				</div>
				<div class="icon"><i class="fas fa-shield-check"></i></div>
			</div>
			<div id="abnormal" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">異常発熱検知数</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['sum']->value['thisDay']['temp_alert_count']) ));?>
</span>名</p>
				</div>
				<div class="icon"><i class="fas fa-thermometer-three-quarters"></i></div>
			</div>
			<div id="not_mask" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">マスク非着用検知数</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['sum']->value['thisDay']['mask_alert_count']) ));?>
</span>名</p>
				</div>
				<div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
			</div>
		</div>
		<div class="statistics_area month_container" <?php if ((($tmp = $_smarty_tpl->tpl_vars['form']->value['statistics_type'] ?? null)===null||$tmp==='' ? '' ?? null : $tmp) == "day") {?>style="display:none;"<?php }?>>
			<div id="total" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">合計認証数</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['sum']->value['thisMonth']['total_count']) ));?>
</span>名</p>
					<div class="comparison">
						<p class="txt <?php if ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['total_count_compType'] == 2) {?>low<?php } elseif ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['total_count_compType'] == 3) {?>high<?php }?>"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['compMonth']['total_count_ratio'] ));?>
</span>%</p>
						<p class="cap">前月比</p>
					</div>
				</div>
				<div class="icon"><i class="fas fa-users"></i></div>
			</div>
			<div id="success_rate" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">認証成功率</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['success_ratio'] ));?>
</span>%</p>
					<div class="comparison">
						<p class="txt <?php if ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['success_count_compType'] == 2) {?>low<?php } elseif ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['success_count_compType'] == 3) {?>high<?php }?>"><span>平均スコア</span></p>
						<p class="cap">前月比</p>
					</div>
				</div>
				<div class="icon"><i class="fas fa-shield-check"></i></div>
			</div>
			<div id="abnormal" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">異常発熱検知数</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['sum']->value['thisMonth']['temp_alert_count']) ));?>
</span>名</p>
					<div class="comparison">
						<p class="txt  <?php if ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['temp_alert_ratio_compType'] == 2) {?>low<?php } elseif ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['temp_alert_ratio_compType'] == 3) {?>high<?php }?>"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['compMonth']['temp_alert_ratio'] ));?>
</span>%</p>
						<p class="cap">前月比</p>
					</div>
				</div>
				<div class="icon"><i class="fas fa-thermometer-three-quarters"></i></div>
			</div>
			<div id="not_mask" class="statistics_box">
				<div class="txt_wrap">
					<h2 class="tit">マスク非着用検知数</h2>
					<p class="result"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( formatNumber($_smarty_tpl->tpl_vars['sum']->value['thisMonth']['mask_alert_count']) ));?>
</span>名</p>
					<div class="comparison">
						<p class="txt  <?php if ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['mask_alert_ratio_compType'] == 2) {?>high<?php } elseif ($_smarty_tpl->tpl_vars['sum']->value['compMonth']['mask_alert_ratio_compType'] == 3) {?>low<?php }?>"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['compMonth']['mask_alert_ratio'] ));?>
</span>%</p>
						<p class="cap">前月比</p>
					</div>
				</div>
				<div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
			</div>
		</div>
	</div>
		
								<div class="pie_graph_area">
			<div class="pie_graph_box registered_person">
				<h2 class="tit">登録者数割合</h2>
				<div class="graph"><canvas id="registered_person"></canvas></div>  
				<?php echo '<script'; ?>
>
				$(function () {
				    var container = $('.canvas-container');
				    var chart= $('#chart');
				    ctx.attr('width', container.width());
				    ctx.attr('height', 300);
				});	
				  var ctx = document.getElementById("registered_person");
				  var registered_person = new Chart(ctx, {
							type: 'doughnut',
				    data: {
				      labels: false,
				      datasets: [{
				          backgroundColor: [
				              "#689fc6",
				              "#21d59b"
				          ],
				          data: [<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['guest_rato'] ));?>
, <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['registed_rato'] ));?>
]
				      }]
				    },
					options: {
				  		responsive: true,
						maintainAspectRatio: false,
						cutoutPercentage: 80
				  	}
				  });
				  <?php echo '</script'; ?>
>
				<div class="txt_wrap">
					<span></span>
					<p class="txt">ゲスト</p>
					<p class="num"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['guest_rato'] ));?>
%</p>
				</div>
				<div class="txt_wrap">
					<span></span>
					<p class="txt">登録者</p>
					<p class="num"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['registed_rato'] ));?>
%</p>
				</div>
				<?php if (($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value['admin']) || ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value['user'])) {?>
				<a href="../person/">ユーザー登録・変更<i class="far fa-angle-right"></i></a>
				<?php }?>
			</div>

									<div class="pie_graph_box fever_abnormality">
				<h2 class="tit">発熱異常割合</h2>
				<div class="graph"><canvas id="fever_abnormality"></canvas></div>
				<?php echo '<script'; ?>
>
				  var ctx = document.getElementById("fever_abnormality");
				  var fever_abnormality = new Chart(ctx, {
							type: 'doughnut',
				    data: {
				      labels: false,
				      datasets: [{
				          backgroundColor: [
				              "#689fc6",
				              "#d71618"
				          ],
				          data: [<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['temp_ok_ratio'] ));?>
, <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['temp_alert_ratio'] ));?>
]
				      }]
				    },
					options: {
				  		responsive: true,
						maintainAspectRatio: false,
						cutoutPercentage: 80
				  	}
				  });
				  <?php echo '</script'; ?>
>
				<div class="txt_wrap">
					<span></span>
					<p class="txt">正常</p>
					<p class="num"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['temp_ok_ratio'] ));?>
%</p>
				</div>
				<div class="txt_wrap">
					<span></span>
					<p class="txt">発熱異常</p>
					<p class="num"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['temp_alert_ratio'] ));?>
%</p>
				</div>
				<?php if (($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value['admin']) || ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value['device'])) {?>
				<a href="../device/?tab=recog1">熱検知の設定<i class="far fa-angle-right"></i></a>
				<?php }?>
			</div>

									<div class="pie_graph_box mask_wearing">
				<h2 class="tit">マスク着用率</h2>
				<div class="graph"><canvas id="mask_wearing"></canvas></div>
				<?php echo '<script'; ?>
>
				  var ctx = document.getElementById("mask_wearing");
				  var mask_wearing = new Chart(ctx, {
							type: 'doughnut',
				    data: {
				      labels: false,
				      datasets: [{
				          backgroundColor: [
				              "#689fc6",
				              "#d71618"
				          ],
				          data: [<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['mask_ok_ratio'] ));?>
, <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['mask_alert_ratio'] ));?>
]
				      }]
				    },
					options: {
				  		responsive: true,
						maintainAspectRatio: false,
						cutoutPercentage: 80
				  	}
				  });
				  <?php echo '</script'; ?>
>
				<div class="txt_wrap">
					<span></span>
					<p class="txt">着用</p>
					<p class="num"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['mask_ok_ratio'] ));?>
%</p>
				</div>
				<div class="txt_wrap">
					<span></span>
					<p class="txt">非着用</p>
					<p class="num"><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['sum']->value['thisMonth']['mask_alert_ratio'] ));?>
%</p>
				</div>
				<?php if (($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value['admin']) || ($_smarty_tpl->tpl_vars['deviceTopMenuFlag']->value['device'])) {?>
				<a href="../device/?tab=recog1">マスク着用設定<i class="far fa-angle-right"></i></a>
				<?php }?>
			</div>
		</div>

									<div class="graph_box">
				<div class="top_wrap">
					<h2 class="tit">時間帯別統計</h2>
					<div class="setting_cnt_wrap">
						<span class="tit">日時指定</span><div class="setting_cnt">
							<div class="period">
								<div class="select calendar">
									<i class="fas fa-calendar-week"></i>
									<input type="text" class="flatpickr" name="day" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['day'] ));?>
" onchange="doGet('./', true)">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="result">
					<p class="date"><span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['day'] ));?>
</span></p>
					<p class="num">Total <span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( nval(formatNumber($_smarty_tpl->tpl_vars['sum']->value['dayTotalCount']),"0") ));?>
</span></p>
				</div>
				<div class="graph"><canvas id="summary_time"></canvas></div>
				<?php echo '<script'; ?>
>
						var barChartData = {
							labels: ['00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00'],
							datasets: [{
								type:'line',
								label: '発熱異常',
				                data: [<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['sum']->value['tempAlertCountList']) ));?>
],
								backgroundColor: '#D71518',
								borderColor: '#D71518',
								fill: false,
								
							}, {
								type:'bar',
								label: 'ゲスト',
				                data: [<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['sum']->value['guestCountList']) ));?>
],
								backgroundColor: "#3695D9",
							}, {
								type:'bar',
								label: '登録者',
				                data: [<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['sum']->value['registedCountList']) ));?>
],
								backgroundColor: "#25D59B",
							}]
						};
						window.onload = function() {
							var ctx = document.getElementById('summary_time').getContext('2d');
							window.myBar = new Chart(ctx, {
								type: 'bar',
								data: barChartData,
								options: {
									tooltips: {
										position: 'nearest',
										intersect: false,
							   		},
									responsive: true,
									scales: {
										xAxes: [{
											stacked: true,
										}],
										yAxes: [{
											stacked:true,
										     ticks: {
												beginAtZero: true,
								                min: 0
								             }
										}]
									}
								}
							});
						};
					<?php echo '</script'; ?>
>		
			</div>

									<div class="graph_box usagecomparison">
				<div class="top_wrap">
					<h2 class="tit">利用状況比較</h2>
					<div class="setting_cnt_wrap">
						<span class="tit">期間1</span><div class="setting_cnt">
							<div class="period">
								<div class="calendar">
									<i class="fas fa-calendar-week"></i>
									<input type="text" class="flatpickr" name="span1_from" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span1_from'] ));?>
" onchange="doGet('./', true)">
								</div>
								<span>〜</span>
								<div class="calendar">
									<input type="text" class="flatpickr" name="span1_to" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span1_to'] ));?>
" onchange="doGet('./', true)">
								</div>
							</div>
						</div>
					</div>
					<div class="setting_cnt_wrap">
						<span class="tit">期間2</span><div class="setting_cnt">
							<div class="period">
								<div class="calendar">
									<i class="fas fa-calendar-week"></i>
									<input type="text" class="flatpickr" name="span2_from" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span2_from'] ));?>
" onchange="doGet('./', true)">
								</div>
								<span>〜</span>
								<div class="calendar">
									<input type="text" class="flatpickr" name="span2_to" value="<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span2_to'] ));?>
" onchange="doGet('./', true)">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="result_wrap">
					<div class="result">
						<p class="date">期間1：<span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span1_from'] ));?>
</span> - <span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span1_to'] ));?>
</span></p>
						<p class="num">Total <span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( nval(formatNumber($_smarty_tpl->tpl_vars['span']->value['span1']['spanTotalCount']),"0") ));?>
</span></p>
					</div>
					<div class="result second">
						<p class="date">期間2：<span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span2_from'] ));?>
</span> - <span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['form']->value['span2_to'] ));?>
</span></p>
						<p class="num">Total <span><?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( nval(formatNumber($_smarty_tpl->tpl_vars['span']->value['span2']['spanTotalCount']),"0") ));?>
</span></p>
					</div>
				</div>
				<div class="graph"><canvas id="usagecomparison"></canvas></div>
			    <?php echo '<script'; ?>
>
			        var ctx = document.getElementById('usagecomparison').getContext('2d');
					ctx.canvas.height = 80;
			        var usagecomparison = new Chart(ctx, {
			            type: 'line',
			            data: {
							// '2018/01/01', '2018/01/02', '2018/01/03', '2018/01/04', '2018/01/05', '2018/01/06', '2018/01/07'
			                labels: [ 
			                	<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['span']->value['spanDays'], 'd', false, 'idx');
$_smarty_tpl->tpl_vars['d']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['idx']->value => $_smarty_tpl->tpl_vars['d']->value) {
$_smarty_tpl->tpl_vars['d']->do_else = false;
?>
			                		<?php if ($_smarty_tpl->tpl_vars['idx']->value != 0) {?>,<?php }?>'<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['d']->value ));?>
'
			                	<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
			                ],
							datasets: [{
			                    label: '期間1：発熱異常',
			                    fill: true,
			                    data: [ <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['span']->value['span1']['tempAlertCountList']) ));?>
 ],
			                    borderColor: "rgb(220, 20, 60)",
								backgroundColor:'rgba(220, 20, 60,0.8)',
			                },{
			                    label: '期間2：発熱異常',
			                    fill: true,
			                    data: [ <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['span']->value['span2']['tempAlertCountList']) ));?>
 ],
			                    borderColor: "rgb(255, 105, 180)",
								backgroundColor:'rgba(255, 105, 180 ,0.8)',
			                },{
			                    label: '期間1',
			                    fill: true,
			                    data: [ <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['span']->value['span1']['totalCountList']) ));?>
 ],
			                    borderColor: "rgb(123, 104, 238)",
								backgroundColor: "rgba(123, 104, 238, 0.5)",
			                },{
			                    label: '期間2',
			                    fill: true,
			                    data: [ <?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( join(",",$_smarty_tpl->tpl_vars['span']->value['span2']['totalCountList']) ));?>
 ],
			                    borderColor: "rgb(210, 180, 140)",
								backgroundColor: "rgba(210, 180, 140, 0.8)",
			                }]
			            },			
						options: {
								responsive: true,
								scales: {
									xAxes: [{
										stacked: false,
									}],
									yAxes: [{
										stacked:false,
									     ticks: {
											beginAtZero: true,
							                min: 0
							             }

									}]
								},
							tooltips: {
							   position: 'nearest',
							   intersect: false,
						   }
						}
			        });

			    <?php echo '</script'; ?>
>		

  
			</div>
		</div>
		
</form>

<?php $_smarty_tpl->_subTemplateRender(((defined('DIR_APP') ? constant('DIR_APP') : null)).('/_inc/footer.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
