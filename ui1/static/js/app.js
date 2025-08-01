// Base64による画像ファイルアップロード。
function setBae64FileInput() {

	$(".base64_picture").unbind().each(function() {

		 var $setTarget = $($(this).attr("set-target"));
		 var $imgTarget = $($(this).attr("img-target"));
		 
		 if ($setTarget.val()) {
			 $imgTarget.css("background-image", "url(\"data:image/jpeg;base64," + $setTarget.val() + "\")");
			 
		 }
		 
		 $(this).change(function() {
			 var $setTarget = $($(this).attr("set-target"));
			 var $imgTarget = $($(this).attr("img-target"));
			 $imgTarget.css("background-image", "url(\"/ui1/static/images/circles.svg\")");

			 var reader = new FileReader();
			 var file = this.files[0]
			 reader.addEventListener("load", function () {
				 if (reader.result.indexOf("data:image/jpeg") != 0) {
					 $imgTarget.css("background-image", "url(\"/ui1/static/images/pleaseJpeg.png\")").hide().fadeIn(200);
					 $setTarget.val("");
					 return;
				 }
				 
				 var idx = reader.result.indexOf(",");
				 $imgTarget.css("background-image", "url(\"" + reader.result + "\")").hide().fadeIn(200);
				 $setTarget.val(reader.result.substring(idx + 1));
			 }, false);

			 reader.readAsDataURL(file);
		});
		
		 
	});

	
}
	
$(function() {
	
	// 「全てチェック」の同期処理。
	$("input.sync_all_check").each(function() {
		
		var $that = $(this);
		var $target = $($(this).attr("sync-target"));

		$(this).click(function() {
			$target.prop("checked", $(this).prop("checked"));
		});
		
		$target.click(function() {
			$that.prop("checked", $target.length == $target.filter(":checked").length);
		});
		
		$that.prop("checked", $target.length == $target.filter(":checked").length);
	});

	// デバイスグループのプルダウン同期。
	$(".device_group_select").change(function() {
		
		var $target = $($(this).attr("device-select"));
		if ($(this).val() == "") {
			$target.find("option").show();
			return;
		}
		
		var idsArr = [];
		var ids = $(this).find("option:selected").attr("device-ids").split(",");
		for (var i = 0; i < ids.length; i++) {
			idsArr[ids[i]] = 1;
		}
		
		$target.val("");
		$target.find("option").each(function() {
			if (idsArr[$(this).val()]) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
		
		
	});
	
	// ボタンのdisabled制御
	$(".btn_disable_switch").change(function() {
		var $target = $($(this).attr("disable-target"));
		if ($(this).val()) {
			$target.removeClass("btn_disabled");
		} else {
			$target.addClass("btn_disabled");
		}
	}).change();
	

	// Base64による画像ファイルアップロード。
	setBae64FileInput();
		
	 
});



