<?php
/* Smarty version 4.5.3, created on 2024-09-03 17:07:13
  from '/var/www/html/api1/_pg/app/api-tool.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.3',
  'unifunc' => 'content_66d6c3b1b7aec3_25449067',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '92791ae407a21dcca8ef95508637ddf8b79942b0' => 
    array (
      0 => '/var/www/html/api1/_pg/app/api-tool.tpl',
      1 => 1725348282,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_66d6c3b1b7aec3_25449067 (Smarty_Internal_Template $_smarty_tpl) {
?><html>
<head>
<meta charset="utf-8" />
<?php echo '<script'; ?>
 src="../static/jquery.js"><?php echo '</script'; ?>
>
<style type="text/css">

	section {
		border: solid 1px #aaa;
		padding: 10px;
		margin-bottom: 20px;
		margin-left: 20px;
		background-color: #efefef;
	}

	.common {
		background-color: #ffefef;
/*
		width: 98.8%;
		position: fixed;
		zoom: 0.5;
*/
		
	}
	
	.ws {
		background-color: #efefff;
	}
	
	.api {
		background-color: #efffef;
	}
	

	h2 {
		font-size: 16px;
		margin:0;
	}
	form {
		margin-left: 25px;
		margin-right: 25px;
		padding: 10px;
		background-color: #fff;
		width: 95%;
	}
	
	iframe {
		display: none;
		width: 96.5%;
		margin-left: 25px;
		border-style: none;
		height: 300px;
		background-color: #fff;
	}
	
	input {
		width: 200px;
	}

	textarea {
		width: 400px;
		height: 100px;
	}

	input[type="submit"], select {
		width: 400px;
		margin-left: 150px;
		display: block;
	}
	
	select {
		font-size: 1.5em;
	}
	
	.settingBtn {
		width: 200px;
	}
	
	.item {
		width: 550px;
		text-align: right;
	}
	
	
	strong {
		font-size: 18px;
	}
	
	#settingList {
		display: none;
		
	}

	.preview_image {
		width: 400px;
		display: inline;
	}
	
	
	.preview_image img {
		width: 200px;
		border: solid 1px #ccc;
	}
	
</style>
<?php echo '<script'; ?>
>

$(function() {
	
	// 共通パラメータをhiddenで自動セットする。
	$(".api form").submit(function() {
		var frm = this;
		
		$(frm).find(".common_parameters").remove();
		
		$("#common_parameters input").each(function() {
			if ($(frm).find("[name='" + $(this).attr("name") + "']").length) {
				return;
			}
			
			var $input = $("<input class='common_parameters' type='hidden' />");
			$input.attr("name", $(this).attr("name"));
			$input.val($(this).val());
			$(frm).append($input);
		});
		
		var $iframe = $(frm).next("iframe");
		$iframe.get(0).contentWindow.document.write("Loading...");
		$iframe.css("display", "block");
		
	});
	
	// getConfig実行時。
	$("#form_getConfig").submit(function() {
		$("#set_config_form").empty();
	
		$("#get_config_iframe").unbind().load(function() {
			
			var text = $($("#get_config_iframe").get(0).contentWindow.document).text();
			
			var config = JSON.parse(text);

			if (config.errors) return;
			
				
			for (var name in config) {
				if (name == "error" || name == "netDefaultNetCard" || name == "netEth0MacAddress") continue;
				
				var val = config[name];
				
				var $input = $('<input type="text" name="' + name + '"  />').val(val);
				var $item = $('<div class="item">' + name + ': </div>').append($input);
				$item.append($input);
				
				$("#set_config_form").append($item);
			}
			
			$("#set_config").show(200, function() {
				$("html,body").animate({ scrollTop: $(this).offset().top - 100 });
			});

		});
	
	});
	
	// getRebootSchedule実行時。
	$("#form_getRebootSchedule").submit(function() {

		$("#set_reboot_form").empty();
		
		$("#get_reboot_iframe").unbind().load(function() {
			
			var text = $($("#get_reboot_iframe").get(0).contentWindow.document).text();
			
			var reboot = JSON.parse(text);

			if (reboot.errors) return;
			
				
			for (var name in reboot) {
				var val = reboot[name];
				
				var $input = $('<input type="text" name="' + name + '"  />').val(val);
				var $item = $('<div class="item">' + name + ': </div>').append($input);
				$item.append($input);
				
				$("#set_reboot_form").append($item);
			}
			
			$("#set_reboot").show(200, function() {
				$("html,body").animate({ scrollTop: $(this).offset().top - 100 });
			});

		});
	});
	


	// input type="file"の内容をbase64エンコードし、対象にセットする。
	$(".api form input[type='file']").change(function() {
		 var reader = new FileReader();
		 var file = this.files[0]
		 var $setTarget = $(this).closest("form").find("[name='" + $(this).attr("set-target") + "']");
		 var $imgTarget = $(this).closest("form").find("[name='" + $(this).attr("img-target") + "']");
		 reader.addEventListener("load", function () {
			 var idx = reader.result.indexOf(",");
			 $imgTarget.attr("src", reader.result);
			 $setTarget.val(reader.result.substring(idx + 1));
		 }, false);
	 
		 reader.readAsDataURL(file);
	});
	
	
	// 接続先設定
	var _settings;
	
	// 接続先設定を表示。
	var showSettingsList = function() {
		
		$("#settingList").empty();
		$("#settingList").append($("<option></option>").text("").attr("setting", "{}"));
		for (var i = 0; i < _settings.length; i++) {
			$("#settingList").append($("<option></option>").text(_settings[i].name).attr("setting", JSON.stringify(_settings[i])));
		}
		
		$("#settingList").unbind().change(function() {
			var json = $(this).find(":selected").attr("setting");
			var p = JSON.parse(json);
			$("#localStorageSaveName").val(p.name);
			$("#common_parameters [name='ds-api-token']").val(p.token);
			$("#common_parameters [name='json-debug']").val(p.debug);
			$("#common_parameters [name='serialNo']").val(p.serialNo);
		}).show();
		
	};

	// 接続先情報をlocalStorageから取得。
	_settings = localStorage.getItem("ds-api-tool-settings");
	if (_settings) {
		_settings = JSON.parse(_settings);
		if (_settings.length > 0) {
			showSettingsList();
			$("#settingList").val(_settings[0].name).change();
		}
	} else {
		_settings = [];
	}

	// localStorageに接続先を保存。
	$("#saveLocalStorage").click(function() {
		var name = $("#localStorageSaveName").val();
		if (name == "") {
			alert("localStorageSaveNameを入力して下さい。");
			return;
		}
		
		var target = {};
		target.name = name;
		target.token = $("#common_parameters [name='ds-api-token']").val();
		target.debug = $("#common_parameters [name='json-debug']").val();
		target.serialNo = $("#common_parameters [name='serialNo']").val();
		
		var existed = false;
		for (var i = 0; i < _settings.length; i++) {
			if (_settings[i].name == name) {
				_settings[i] = target;
				existed = true;
				break;
			}
		}
		
		if (!existed) _settings.push(target);
		
		_settings.sort(function(a, b) {
			var nameA = a.name.toUpperCase(); 
			var nameB = b.name.toUpperCase(); 
			if (nameA < nameB) {
				return -1;
			}
			if (nameA > nameB) {
				return 1;
			}
			return 0;
		});
		
		localStorage.setItem("ds-api-tool-settings", JSON.stringify(_settings));
		showSettingsList();
		
	
	});
	
	// 接続先設定を削除。
	$("#deleteLocalStorage").click(function() {
		var selected = $("#settingList").val();
		if (selected == "") return;
		if (!confirm(selected + "を削除してもよろしいですか？")) return;
		
		var newList = [];
		
		for (var i = 0; i < _settings.length; i++) {
			if (_settings[i].name != selected) {
				newList.push(_settings[i]);
			}
		}
		
		_settings = newList;
		localStorage.setItem("ds-api-tool-settings", JSON.stringify(_settings));
		showSettingsList();
	});
	
});


<?php echo '</script'; ?>
>
</head>
<body>

<div class="common">
	<strong>Common-Paramters</strong>

	<section>
		<form id="common_parameters">
			<select id="settingList"></select>
			<br />
			<div class="item">ds-api-token: <input type="text" name="ds-api-token" value="" /></div>
			<div class="item">json-debug: <input type="text" name="json-debug" value="1" /></div>
			<div class="item">serialNo: <input type="text" name="serialNo" value="" /></div>
			<br />
			<div class="item">localStorageSaveName: <input type="text" id="localStorageSaveName" value="" /></div>
			<div class="item">
				<input id="saveLocalStorage" type="button" class="settingBtn" value="Save localStorage"  />
				<input id="deleteLocalStorage" type="button" class="settingBtn" value="Delete localStorage"  />
			</div>
		</form>
	</section>

</div>

<?php if ($_smarty_tpl->tpl_vars['isLocal']->value) {?>
<!--
	<div class="ws">
		<strong>WS-Server</strong>
		
		<section>
			<h2>/ws/api/serialNos</h2>
			<form action="/ws/api/serialNos" method="get" target="/ws/api/serialNos">
				<input type="submit" value="submit" />
			</form>
			<iframe src="about:blank" style="display: block" name="/ws/api/serialNos"></iframe>
		</section>
		
		<section>
			<h2>/ws/api/ws</h2>
			<form action="/ws/api/ws" method="get" target="/ws/api/ws">
				<div class="item">timeoutMs: <input type="text" name="timeoutMs" value="5000" /></div>
				<div class="item">json: <textarea name="json">{"method":"personManager.getPersons","params":{"Condition":{"Type":1,"CodeLike":"","NameLike":"","Offset":0,"Limit":1000}},"id":14}</textarea></div>
				<br />
				<input type="submit" value="submit" />
			</form>
			<iframe src="about:blank" style="display: block" name="/ws/api/ws"></iframe>
		</section>
		
	</div>
-->
<?php }?>

<div class="api">
	<strong>API-Server /device</strong>
	<section>
		<h2>/api1/device/getDevice</h2>
		<form action="../device/getDevice" method="get" target="device/getDevice">
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="device/getDevice"></iframe>
	</section>

	<section>
		<h2>/api1/device/sync</h2>
		<form action="../device/sync" method="get" target="device/sync">
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="device/sync"></iframe>
	</section>

	<section>
		<h2>/api1/device/apbRepaire</h2>
		<form action="../device/apbRepaire" method="get" target="device/apbRepaire">
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="device/apbRepaire"></iframe>
	</section>

	<section>
		<h2>/api1/device/getPushUrl</h2>
		<form action="../device/getPushUrl" method="get" target="device/getPushUrl">
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="device/getPushUrl"></iframe>
	</section>

	<section>
		<h2>/api1/device/registPushUrl</h2>
		<form action="../device/registPushUrl" method="get" target="device/registPushUrl">
			<div class="item">pushUrl: <input type="text" name="pushUrl" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="device/registPushUrl"></iframe>
	</section>

	
</div>

<div class="api">
	<strong>API-Server /log</strong>

	<section>
		<h2>/api1/log/getRecogLog</h2>
		<form action="../log/getRecogLog" method="get" target="log/getRecogLog">
			<div class="item">latest: <input type="text" name="latest" value="" /></div>
			<div class="item">recogTimeFrom: <input type="text" name="recogTimeFrom" value="" /></div>
			<div class="item">recogTimeTo: <input type="text" name="recogTimeTo" value="" /></div>
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">personDescription1: <input type="text" name="personDescription1" value="" /></div>
			<div class="item">personDescription2: <input type="text" name="personDescription2" value="" /></div>
			<div class="item">pageNo: <input type="text" name="pageNo" value="" /></div>
			<div class="item">pictureExpires: <input type="text" name="pictureExpires" value="" /></div>
			<div class="item">pictureAllowIp: <input type="text" name="pictureAllowIp" value="" /></div>
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="log/getRecogLog"></iframe>
	</section>

	<section>
		<h2>/api1/log/deleteRecogLog</h2>
		<form action="../log/deleteRecogLog" method="get" target="log/deleteRecogLog">
			<div class="item">ids: <input type="text" name="ids" value="" /></div>
			<div class="item">recogTimeFrom: <input type="text" name="recogTimeFrom" value="" /></div>
			<div class="item">recogTimeTo: <input type="text" name="recogTimeTo" value="" /></div>
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="log/deleteRecogLog"></iframe>
	</section>

	<section>
		<h2>/api1/log/getOperateLog</h2>
		<form action="../log/getOperateLog" method="get" target="log/getOperateLog">
			<div class="item">operateTimeFrom: <input type="text" name="operateTimeFrom" value="" /></div>
			<div class="item">operateTimeTo: <input type="text" name="operateTimeTo" value="" /></div>
			<div class="item">operateUser: <input type="text" name="operateUser" value="" /></div>
			<div class="item">mainType: <input type="text" name="mainType" value="" /></div>
			<div class="item">subType: <input type="text" name="subType" value="" /></div>
			<div class="item">pageNo: <input type="text" name="pageNo" value="" /></div>
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="log/getOperateLog"></iframe>
	</section>

</div>

<div class="api">
	<strong>API-Server /person</strong>

	<section>
		<h2>/api1/person/getPerson</h2>
		<form action="../person/getPerson" method="get" target="person/getPerson">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">includePicture: <input type="text" name="includePicture" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/getPerson"></iframe>
	</section>

	<section>
		<h2>/api1/person/checkPersonPicture</h2>
		<form action="../person/checkPersonPicture" method="post" target="person/checkPersonPicture">
			<div class="item">file:<input type="file" img-target="preview" set-target="picture" /></div>
			<div class="item">picture: <div class="preview_image"><img name="preview" /><input type="hidden" name="picture" value="" /></div></div>
			<div class="item">algorithmVersion: <input type="text" name="algorithmVersion" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/checkPersonPicture"></iframe>
	</section>
	
	<section>
		<h2>/api1/person/getPersonFromCloud</h2>
		<form action="../person/getPersonFromCloud" method="get" target="person/getPersonFromCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">personName: <input type="text" name="personName" value="" /></div>
			<div class="item">personTypeCode: <input type="text" name="personTypeCode" value="" /></div>
			<div class="item">personDescription1: <input type="text" name="personDescription1" value="" /></div>
			<div class="item">personDescription2: <input type="text" name="personDescription2" value="" /></div>
			<div class="item">registSerialNo: <input type="text" name="registSerialNo" value="" /></div>
			<div class="item">pageNo: <input type="text" name="pageNo" value="" /></div>
			<div class="item">pictureExpires: <input type="text" name="pictureExpires" value="" /></div>
			<div class="item">pictureAllowIp: <input type="text" name="pictureAllowIp" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/getPersonFromCloud"></iframe>
	</section>

	<section>
		<h2>/api1/person/registPersonForCloud</h2>
		<form action="../person/registPersonForCloud" method="post" target="person/registPersonForCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">personName: <input type="text" name="personName" value="" /></div>
			<div class="item">sex: <input type="text" name="sex" value="" placeholder="male / female" /></div>
			<div class="item">birthday: <input type="text" name="birthday" value="" /></div>
			<div class="item">memo: <input type="text" name="memo" value="" /></div>
			<div class="item">personTypeCode: <input type="text" name="personTypeCode" value="" /></div>
			<div class="item">personDescription1: <input type="text" name="personDescription1" value="" /></div>
			<div class="item">personDescription2: <input type="text" name="personDescription2" value="" /></div>
			<div class="item">recogLogSerialNo: <input type="text" name="recogLogSerialNo" value="" /></div>
			<div class="item">recogLogId: <input type="text" name="recogLogId" value="" /></div>
			<div class="item">file:<input type="file" img-target="preview" set-target="picture" /></div>
			<div class="item">picture:<div class="preview_image"><img name="preview" /><input name="picture" type="hidden" /></div></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/registPersonForCloud"></iframe>
	</section>

	<section>
		<h2>/api1/person/toDevice</h2>
		<form action="../person/toDevice" method="get" target="person/toDevice">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">override  : <input type="text" name="override" value="" /></div>
			<div class="item">noImage   : <input type="text" name="noImage" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/toDevice"></iframe>
	</section>

	<section>
		<h2>/api1/person/deletePersonFromCloud</h2>
		<form action="../person/deletePersonFromCloud" method="get" target="person/deletePersonFromCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/deletePersonFromCloud"></iframe>
	</section>

	<section>
		<h2>/api1/person/deletePersonAssociation</h2>
		<form action="../person/deletePersonAssociation" method="get" target="person/deletePersonAssociation">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">serialNo: <input type="text" name="serialNo" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/deletePersonAssociation"></iframe>
	</section>


	<section>
		<h2>/api1/person/getPersonFromDevice</h2>
		<form action="../person/getPersonFromDevice" method="get" target="person/getPersonFromDevice">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">personName: <input type="text" name="personName" value="" /></div>
			<div class="item">pageNo: <input type="text" name="pageNo" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/getPersonFromDevice"></iframe>
	</section>

	<section>
		<h2>/api1/person/toCloud</h2>
		<form action="../person/toCloud" method="get" target="person/toCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">override: <input type="text" name="override" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/toCloud"></iframe>
	</section>

	<section>
		<h2>/api1/person/deletePersonFromDevice</h2>
		<form action="../person/deletePersonFromDevice" method="get" target="person/deletePersonFromDevice">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/deletePersonFromDevice"></iframe>
	</section>
	
	<section>
		<h2>/api1/person/clearPersonFromDevice</h2>
		<form action="../person/clearPersonFromDevice" method="get" target="person/clearPersonFromDevice">
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/clearPersonFromDevice"></iframe>
	</section>
	
	<section>
		<h2>/api1/person/getPersonAcessTimeFromCloud</h2>
		<form action="../person/getPersonAcessTimeFromCloud" method="get" target="person/getPersonAcessTimeFromCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/getPersonAcessTimeFromCloud"></iframe>
	</section>
	
	<section>
		<h2>/api1/person/registPersonAcessTimeForCloud</h2>
		<form action="../person/registPersonAcessTimeForCloud" method="get" target="person/registPersonAcessTimeForCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">serialNo: <input type="text" name="serialNo" value="" /></div>
			<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);$_smarty_tpl->tpl_vars['i']->step = 1;$_smarty_tpl->tpl_vars['i']->total = (int) ceil(($_smarty_tpl->tpl_vars['i']->step > 0 ? 10+1 - (1) : 1-(10)+1)/abs($_smarty_tpl->tpl_vars['i']->step));
if ($_smarty_tpl->tpl_vars['i']->total > 0) {
for ($_smarty_tpl->tpl_vars['i']->value = 1, $_smarty_tpl->tpl_vars['i']->iteration = 1;$_smarty_tpl->tpl_vars['i']->iteration <= $_smarty_tpl->tpl_vars['i']->total;$_smarty_tpl->tpl_vars['i']->value += $_smarty_tpl->tpl_vars['i']->step, $_smarty_tpl->tpl_vars['i']->iteration++) {
$_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->iteration === 1;$_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration === $_smarty_tpl->tpl_vars['i']->total;?>
				<div class="item time_input" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 3) {?>style="display:none"<?php }?>>accessFlag_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
: <input type="text" name="accessFlag_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
" value="" /></div>
				<div class="item time_input" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 3) {?>style="display:none"<?php }?>>accessTimeFrom_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
: <input type="text" name="accessTimeFrom_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
" value="" /></div>
				<div class="item time_input" <?php if ($_smarty_tpl->tpl_vars['i']->value >= 3) {?>style="display:none"<?php }?>>accessTimeTo_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
: <input type="text" name="accessTimeTo_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
" value="" /></div>
			<?php }
}
?>
			<a style="padding-left:31.5em;" href="javascript:void(0);" onclick="$(this).hide(); $('.time_input').slideDown(200)">more</a>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/registPersonAcessTimeForCloud"></iframe>
	</section>

	<section>
		<h2>/api1/person/getPersonCardInfoFromCloud</h2>
		<form action="../person/getPersonCardInfoFromCloud" method="get" target="person/getPersonCardInfoFromCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/getPersonCardInfoFromCloud"></iframe>
	</section>

	<section>
		<h2>/api1/person/registPersonCardInfoForCloud</h2>
		<form action="../person/registPersonCardInfoForCloud" method="get" target="person/registPersonCardInfoForCloud">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<?php
$_smarty_tpl->tpl_vars['i'] = new Smarty_Variable(null, $_smarty_tpl->isRenderingCache);$_smarty_tpl->tpl_vars['i']->step = 1;$_smarty_tpl->tpl_vars['i']->total = (int) ceil(($_smarty_tpl->tpl_vars['i']->step > 0 ? 3+1 - (1) : 1-(3)+1)/abs($_smarty_tpl->tpl_vars['i']->step));
if ($_smarty_tpl->tpl_vars['i']->total > 0) {
for ($_smarty_tpl->tpl_vars['i']->value = 1, $_smarty_tpl->tpl_vars['i']->iteration = 1;$_smarty_tpl->tpl_vars['i']->iteration <= $_smarty_tpl->tpl_vars['i']->total;$_smarty_tpl->tpl_vars['i']->value += $_smarty_tpl->tpl_vars['i']->step, $_smarty_tpl->tpl_vars['i']->iteration++) {
$_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->iteration === 1;$_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration === $_smarty_tpl->tpl_vars['i']->total;?>
				<div class="item time_input" >card_no_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
: <input type="text" name="card_no_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
" value="" /></div>
				<div class="item time_input" >validityDateFrom_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
: <input type="text" name="validityDateFrom_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
" value="" /></div>
				<div class="item time_input" >validityDateTo_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
: <input type="text" name="validityDateTo_<?php echo call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'smarty_default_htmlspecialchars' ][ 0 ], array( $_smarty_tpl->tpl_vars['i']->value ));?>
" value="" /></div>
			<?php }
}
?>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/registPersonCardInfoForCloud"></iframe>
	</section>

	<section>
		<h2>/api1/person/capturePersonPicture</h2>
		<form action="../person/capturePersonPicture" method="post" target="person/capturePersonPicture">
			<div class="item">getCount: <input type="text" name="getCount" value="" /></div>
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<div class="item">override: <input type="text" name="override" value="" /></div>
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/capturePersonPicture"></iframe>
	</section>

	<section>
		<h2>/api1/person/checkSimilarityInDevice</h2>
		<form action="../person/checkSimilarityInDevice" method="post" target="person/checkSimilarityInDevice">
			<div class="item">file:<input type="file" img-target="preview" set-target="picture" /></div>
			<div class="item">picture: <div class="preview_image"><img name="preview" /><input type="hidden" name="picture" value="" /></div></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/checkSimilarityInDevice"></iframe>
	</section>

	<section>
		<h2>/api1/person/getPersonPictureFromDevice</h2>
		<form action="../person/getPersonPictureFromDevice" method="post" target="person/getPersonPictureFromDevice">
			<div class="item">personCode: <input type="text" name="personCode" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/getPersonPictureFromDevice"></iframe>
	</section>

	<section>
		<h2>/api1/person/getPersonType</h2>
		<form action="../person/getPersonType" method="get" target="person/getPersonType">
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/getPersonType"></iframe>
	</section>
	
	<section>
		<h2>/api1/person/registPersonType</h2>
		<form action="../person/registPersonType" method="get" target="person/registPersonType">
			<div class="item">personTypeCode: <input type="text" name="personTypeCode" value="" /></div>
			<div class="item">personTypeName: <input type="text" name="personTypeName" value="" /></div>
			<div class="item">override: <input type="text" name="override" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/registPersonType"></iframe>
	</section>

	<section>
		<h2>/api1/person/deletePersonType</h2>
		<form action="../person/deletePersonType" method="get" target="person/deletePersonType">
			<div class="item">personTypeCode: <input type="text" name="personTypeCode" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="person/deletePersonType"></iframe>
	</section>
	
	
</div>

<div class="api">
	<strong>API-Server /config</strong>

	<section>
		<h2>/api1/config/getConfig</h2>
		<form id="form_getConfig" action="../config/getConfig" method="get" target="config/getConfig">
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe id="get_config_iframe" src="about:blank" name="config/getConfig"></iframe>
	</section>

	<section id="set_config" style="display:none">
		<h2>/api1/config/setConfig</h2>
		<form action="../config/setConfig" method="get" target="config/setConfig">
			<div id="set_config_form"></div>
			<br />
			
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="config/setConfig"></iframe>
	</section>

	<section>
		<h2>/api1/config/getLogo</h2>
		<form action="../config/getLogo" method="get" target="config/getLogo">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="config/getLogo"></iframe>
	</section>

	<section>
		<h2>/api1/config/setLogo</h2>
		<form action="../config/setLogo" method="post" target="config/setLogo">
			<div class="item">file:<input type="file" img-target="preview" set-target="data" /></div>
			<div class="item">data:<div class="preview_image"><img name="preview" /><input name="data" type="hidden" /></div></div>
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="config/setLogo"></iframe>
	</section>

</div>

<div class="api">
	<strong>API-Server /system</strong>

	<section>
		<h2>/api1/system/getSystemInfo</h2>
		<form action="../system/getSystemInfo" method="post" target="system/getSystemInfo">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/getSystemInfo"></iframe>
	</section>

	<section>
		<h2>/api1/system/reboot</h2>
		<form action="../system/reboot" method="post" target="system/reboot">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/reboot"></iframe>
	</section>

	<section>
		<h2>/api1/system/openOnce</h2>
		<form action="../system/openOnce" method="post" target="system/openOnce">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/openOnce"></iframe>
	</section>

	<section>
		<h2>/api1/system/setHibernateMode</h2>
		<form action="../system/setHibernateMode" method="post" target="system/setHibernateMode">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/setHibernateMode"></iframe>
	</section>

	<section>
		<h2>/api1/system/setOperationMode</h2>
		<form action="../system/setOperationMode" method="post" target="system/setOperationMode">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/setOperationMode"></iframe>
	</section>

	<section>
		<h2>/api1/system/getCurrentMode</h2>
		<form action="../system/getCurrentMode" method="post" target="system/getCurrentMode">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/getCurrentMode"></iframe>
	</section>

	<section>
		<h2>/api1/system/displayMessage</h2>
		<form action="../system/displayMessage" method="post" target="system/displayMessage">
		<div class="item">Customtip      : <textarea name="Customtip" wrap="hard" style="width:200px;height:50px" value=""></textarea></div>
		<div class="item">Tipstime       : <input type="text" name="Tipstime" value="" /></div>
		<div class="item">BorderColor    : <input type="text" name="BorderColor" value="" placeholder="R,G,B,(A)"/></div>
		<div class="item">BackgroundColor: <input type="text" name="BackgroundColor" value="" placeholder="R,G,B,(A)"/></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/displayMessage"></iframe>
	</section>

	<section>
		<h2>/api1/system/getRebootSchedule</h2>
		<form id="form_getRebootSchedule" action="../system/getRebootSchedule" method="post" target="system/getRebootSchedule">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe id="get_reboot_iframe" src="about:blank" name="system/getRebootSchedule"></iframe>
	</section>

	<section id="set_reboot" style="display:none">
		<h2>/api1/system/setRebootSchedule</h2>
		<form action="../system/setRebootSchedule" method="get" target="system/setRebootSchedule">
			<div id="set_reboot_form"></div>
			<br />
			
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/setRebootSchedule"></iframe>
	</section>

	<section>
		<h2>/api1/system/exportConfig</h2>
		<form action="../system/exportConfig" method="post" target="system/exportConfig">
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/exportConfig"></iframe>
	</section>
	
<!--
	<section>
		<h2>/api1/system/importConfig</h2>
		<form action="../system/importConfig" method="post" target="system/importConfig">
			<div class="item">file:<input type="file" set-target="data" /></div>
			<div class="item">data(base64):<input name="data" type="text" /></div>
			<br />

			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/importConfig"></iframe>
	</section>
-->

<?php if (ENABLE_AWS) {?>
	<section>
		<h2>/api1/system/updateFirmware</h2>
		<form action="../system/updateFirmware" method="post" target="system/updateFirmware">
			<div class="item">version:<input name="version" value="" /></div>
			<br />
			<input type="submit" value="submit" />
		</form>
		<iframe src="about:blank" name="system/updateFirmware"></iframe>
	</section>
<?php }?>


</div>
	
</body>
</html><?php }
}
