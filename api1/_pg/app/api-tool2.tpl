<html>
<head>
<meta charset="utf-8" />
<script src="../static/jquery.js"></script>
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
<script>

$(function() {
	
	// 共通パラメータをhiddenで自動セットする。
	$(".api form").submit(function() {
		var frm = this;
		
		$(frm).find(".common_parameters").remove();
		
		$("#common_parameters input").each(function() {
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


</script>
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

{if $isLocal}
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
				<div class="item">json: <textarea name="json">{literal}{"method":"personManager.getPersons","params":{"Condition":{"Type":1,"CodeLike":"","NameLike":"","Offset":0,"Limit":1000}},"id":14}{/literal}</textarea></div>
				<br />
				<input type="submit" value="submit" />
			</form>
			<iframe src="about:blank" style="display: block" name="/ws/api/ws"></iframe>
		</section>
		
		
	</div>
{/if}


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

</div>

<div class="api">
	<strong>TEST 2020-11-16 Face Regist Loop</strong>

	<section>
		<h2>Face Regist -> Face Delete(300 Loop)</h2>
		<input type="button" value="execute" />
	</section>

</div>

	
</body>
</html>