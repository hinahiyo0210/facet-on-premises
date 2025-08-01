
CKEDITOR.plugins.add( 'filelib', {
	lang: 'ja', // %REMOVE_LINE_CORE%
	icons: 'filelib', // %REMOVE_LINE_CORE%
	hidpi: true, // %REMOVE_LINE_CORE%
	init: function( editor ) {

		editor.addCommand('filelib' , {
		    exec: function( editor ) {
				
				$("#ckeditor-filelib-panel .filelib-item").removeClass("filelib-item-delete");
				
				if ($("#ckeditor-filelib-panel").is(":visible")) {
					_fileLibDialog_itemClickFunction = null;
					$("#ckeditor-filelib-panel").slideUp(200);
					
				} else {
					var pos = $(".cke_button__filelib").position();
					$("#ckeditor-filelib-panel").css("top", pos.top + 30).css("left", pos.left).slideDown(200);
					
				}
				
				fileLibDialog_dispFiles();
				
		    }
		});

		editor.ui.addButton( 'FileLib', {
		    label: 'ファイルライブラリ',
		    command: 'filelib',
		    toolbar: 'insert'
		});
	
	}

});


function fileLibDialog_deleteFile(name) {
	
	$.ajax({
		type: "POST"
		, url: "/filelib/deleteFile"
		, dataType: "json"
		, data: {name: name}
		, cache: false		// キャッシュ無効
		, error: function(request, textStatus, errorThrown) {
			console.log(request); console.log(textStatus); console.log(errorThrown);
			alert("エラーが発生しました。\n" + textStatus + "\n" + errorThrown);
		}
		, success: function(data, dataType) {
			if (data.error) { alert(data.error); return; }
			
//			$("#ckeditor-filelib-panel").removeClass("filelib-item-delete");
//			$("#ckeditor-filelib-delete-btn").text("[削除]");
			
			fileLibDialog_dispFiles();
		}
	});
	
}


var _fileLibDialog_itemClickFunction = null;

function fileLibDialog_dispFiles(callback) {
	
	$.ajax({
		type: "POST"
		, url: "/filelib/getFileList"
		, dataType: "json"
		, data: {}
		, cache: false		// キャッシュ無効
		, error: function(request, textStatus, errorThrown) {
			console.log(request); console.log(textStatus); console.log(errorThrown);
			alert("エラーが発生しました。\n" + textStatus + "\n" + errorThrown);
		}
		, success: function(data, dataType) {
			if (data.error) { alert(data.error); return; }
			
			$("#ckeditor_filelib_size").text(String(Math.floor(data.size / 1024 / 1024)).replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
			$("#ckeditor_filelib_limit").text(String(Math.floor(data.limit / 1024 / 1024)).replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
			
			$(".filelib-files").html("");
			data.files.forEach(function(file) {
				$(".filelib-files").append(''
					+ '<div class="filelib-item" file-name="' + file.fileName + '" file-size=' + file.size + '>'
					+ 	'<div class="notes">' + file.size + '</div>'
					+ 	'<img data-original="' + data.url + "/" + file.fileName + '" />'
					+ '</div>'
				);
			});
			$(".filelib-files .filelib-item img").lazyload({
				container: $('.filelib-files')
				, effect : "fadeIn"
			});
			
			$(".filelib-files .filelib-item").click(function() {
				
				if ($("#ckeditor-filelib-panel").hasClass("filelib-item-delete")) {
					// ファイル削除
					fileLibDialog_deleteFile($(this).attr("file-name"));
				} else {
					// html差し込み
					var img = $(this).find("img");
					if (_fileLibDialog_itemClickFunction) {
						_fileLibDialog_itemClickFunction(img);
					} else {
						CKEDITOR.instances["ckeditor_text"].insertHtml('<img src="' + img.attr("data-original") + '" />');
					}

					_fileLibDialog_itemClickFunction = null;
					$("#ckeditor-filelib-panel").fadeOut(200);
				}
			});
			
			if (callback) callback();
		}
	});
	
}

$(function() {
	
	$("#ckeditor-filelib-delete-btn").click(function() {
		
		if ($("#ckeditor-filelib-panel").hasClass("filelib-item-delete")) {
			$("#ckeditor-filelib-panel").removeClass("filelib-item-delete");
			$(this).text("[削除]");
			
		} else {
			$("#ckeditor-filelib-panel").addClass("filelib-item-delete");
			$(this).text("[削除モードを終了]");
			
		}
		
	});
	
	$("#ckeditor-filelib-close-btn").click(function() {
		_fileLibDialog_itemClickFunction = null;
		$("#ckeditor-filelib-panel").removeClass("filelib-item-delete");
		$("#ckeditor-filelib-delete-btn").text("[削除]");
		$("#ckeditor-filelib-panel").slideUp(200);
	});

	$(document).on('change','input[name="ckeditor_filelib_file"]',function(){
		
		if ($("input[name='ckeditor_filelib_file']").prop("files") == null) return;
		
		var file = $("input[name='ckeditor_filelib_file']").prop("files")[0];
		
		var data = new FormData();
		if ($("input[name='ckeditor_filelib_file']").val() !== '') {
			data.append("ckeditor_filelib_file", file);
		}
		
		$("#ckeditor-filelib-uploading").show(200, function() {
			$.ajax({
				type: "POST"
				, url: "/filelib/uploadFile"
				, dataType: "json"
				, data: data
				, cache: false		// キャッシュ無効
				, processData : false
				, contentType : false
				, error: function(request, textStatus, errorThrown) {
					console.log(request); console.log(textStatus); console.log(errorThrown);
					alert("エラーが発生しました。\n" + textStatus + "\n" + errorThrown);
					$("#ckeditor-filelib-uploading").hide();
				}
				, success: function(data, dataType) {
					$("#ckeditor-filelib-uploading").fadeOut(200);
					if (data.error) { alert(data.error); return; }
					fileLibDialog_dispFiles();
				}
			});
		
		});
		
		
	});

});


