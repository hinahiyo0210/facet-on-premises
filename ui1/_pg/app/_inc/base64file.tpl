<div id="file_area"></div>

{if $isConfirm}
	<input type='hidden' name='prev_{$fileFormName}'     value="{arr($form, $fileFormName)}" />
	<input type='hidden' name='prev_{$filenameFormName}' value="{arr($form, $filenameFormName)}" />
	<input type='hidden' name='prev_{$filesizeFormName}' value="{arr($form, $filesizeFormName)}" />

{else}
	<input type='hidden' name='{$fileFormName}'     value="{arr($form, $fileFormName)}" />
	<input type='hidden' name='{$filenameFormName}' value="{arr($form, $filenameFormName)}" />
	<input type='hidden' name='{$filesizeFormName}' value="{arr($form, $filesizeFormName)}" />

{/if}

<script>
	// ----------------------------------------------------------------------------------- 添付ファイル BEGIN
	var _file_index = 0;
	var _dispImgCallback = null;
	var _removeBtnCallback = null;

	$(function() {

		{if arr($form, $fileFormName)}

			addFile();

			var $fileHidden      = $("input[name='{$fileFormName}']");
			var $filenameHidden  = $("input[name='{$filenameFormName}']");
			var $filesizeHidden  = $("input[name='{$filesizeFormName}']");

			dispImg(
				$("input[name='{$fileFormName}']").val()
				, $("input[name='{$filenameFormName}']").val()
				, $filesizeHidden.val()
				, $("div.file_data_0")
				, $("input.file_data_0")
				, $("input[name='{$fileFormName}']")
				, $("input[name='{$filenameFormName}']")
			);

		{else}

			addFile();

		{/if}


	});

	function addFile() {

		var limitSize = {$limitSize};

		var $fileArea = $("#file_area");

		$(".file_data_" + (_file_index - 2)).remove();

		var $fileHidden      = $("input[name='{$fileFormName}']");
		var $filenameHidden  = $("input[name='{$filenameFormName}']");
		var $filesizeHidden  = $("input[name='{$filesizeFormName}']");


		var $input           = $("<input class='file_data_" + _file_index + "' type='file' id='file_btn_{$fileFormName}' />");
//		var $input           = $("<label class='form-input-file button button-normal button-blue'>ファイル選択<input class='file_data_" + _file_index + "' type='file' id='file_btn_{$fileFormName}' /></label>")
		var $info            = $("<div class='file_data_" + _file_index + "' ></div>");
		_file_index++;

		$fileArea.append($input).append($info);

		$input.change(function() {
			readFileData($fileHidden, $filenameHidden, $filesizeHidden, $fileArea, $input, limitSize, $info);
		});

		{if $isConfirm}
			$input.hide();
		{/if}


	}

	function readFileData($fileHidden, $filenameHidden, $filesizeHidden, $fileArea, $fileInput, limitSize, $infoArea) {

		var file = $fileInput.get(0).files[0];

		// 0バイトファイルはエラーにする。
		if (file.size == 0) {
			var msg = ""
				+ "ファイルサイズが0バイトです。\n"
				+ file.name + "\n";
			alert(msg);
			return;
		}


		if (limitSize && file.size > limitSize - 1024) {

			var msg = ""
				+ "アップロード可能なファイルサイズを超えています。\n"
				+ file.name + " (" + Math.floor(file.size / 1024 / 1024) + "MB)\n"
				+ "許容されるファイルサイズは "+ Math.floor(limitSize / 1024 / 1024) + "MBまでです。";

			alert(msg);

			return;
		}

		// 拡張子チェック。
		var allowExts = "{$allowExts}";
		if (allowExts != "") {
			var arr = file.name.split(".");
			var ext = arr[arr.length - 1];
			allowExts = allowExts.split(",");
			var exists = false;
			for (var i = 0; i < allowExts.length; i++) {
				if (allowExts[i].toLowerCase() == ext.toLowerCase()) {
					exists = true;
					break;
				}
			}
			if (!exists) {
				var msg = ""
					+ "アップロード可能なファイル形式は。\n"
					+ "「{$allowExts}」のいずれかです。";

				alert(msg);

				return;
			}

		}



		$infoArea.text('読み込み中...');

		var reader = new FileReader();
		reader.upfile = file;
		reader.addEventListener('error', function(e) {

		    switch(e.target.error.code) {
				case e.target.error.NOT_FOUND_ERR:
					$infoArea.text('File Not Found!');
					break;
				case e.target.error.NOT_READABLE_ERR:
					$infoArea.text('File is not readable');
					break;
				case e.target.error.ABORT_ERR:
					break;
				default:
					$infoArea.text('An error occurred reading this file.');
		    };

		}, false);

		reader.addEventListener('progress', function(e) {
		  	if (e.lengthComputable) { //全体のサイズがわかっているかどうか
		  		var percentLoaded = Math.round((e.loaded / e.total) * 100);
		  		if (percentLoaded > 0) {
	//	  			$infoArea.text(Math.floor(percentLoaded) + '%  ' + this.upfile.name);
		  		}

		  		// java側の処理の進捗を得るタイマーを開始する。
		  		if (percentLoaded == 100) {

		  		}
		  	}

		}, false);

		reader.onabort = function(e) {
			alert('File read cancelled');
		};

		reader.onload = function(e) {

			var base64 = arrayBufferToBase64(this.result);
			var fileName = this.upfile.name;
			var size = this.upfile.size;

			$fileHidden.val(base64);
			$filenameHidden.val(fileName);
			$filesizeHidden.val(size);

			dispImg(base64, fileName, size, $infoArea, $fileInput, $fileHidden, $filenameHidden, $filesizeHidden);

	    };

		reader.readAsArrayBuffer(file);

	}

	function dispImg(base64, fileName, size, $infoArea, $fileInput, $fileHidden, $filenameHidden, $filesizeHidden) {

//		var $img = $("<img />").css("width", "500px").attr("src", "data:image/png;base64," + base64);


		// 削除ボタン
		var $removeBtn = $("<input type='button' value='×' style='width:20px; margin-left:10px; padding:0; ' />").click(function() {

			$infoArea.remove();
			$fileHidden.val("");
			$filenameHidden.val("");
			$filesizeHidden.val("");

			if (_removeBtnCallback) _removeBtnCallback();

			if ($("#file_btn_{$fileFormName}").length == 0) {
				addFile();
			}
		});

		{if $isConfirm}
			$removeBtn.hide();
		{/if}

		var $bytes = $("<span style='margin-left:10px; '></span>").text(formatBytes(size));

		//var $a = $("<a href=''></a>");
	//	var $img = $("");
		var $fileNameText = $("<span style='font-size:15px; display:inline;'></span>").text(fileName);


		var $a = $("<a href='javascript:void(0)'></a>")
			.append("<img src='/assets/img/ico_attach.png' style='width:32px; display:inline;' />")
			.append($fileNameText)
			.click(function() {

			var blob = toBlob(base64, "application/octet-stream");

			if (window.navigator.msSaveBlob) {
				// IEやEdgeの場合、Blob URL Schemeへと変換しなくともダウンロードできる
				window.navigator.msSaveOrOpenBlob(blob, fileName);
			} else {

				$(document.body).append($("<a id='file_dl'></a>").attr("download", fileName));

				// BlobをBlob URL Schemeへ変換してリンクタグへ埋め込む
				$("#file_dl").prop("href", window.URL.createObjectURL(blob));

				// リンクをクリックする
				document.getElementById("file_dl").click();

				$("#file_dl").remove();
			}

		});


	//	$a.append($img);
	//	$a.append($fileNameText);

		$infoArea.empty().append($a).append($bytes).append($removeBtn);

		$fileInput.remove();

		addFile();

		if (_dispImgCallback) _dispImgCallback();


	}


	function formatBytes(a,b){ b=1;if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}

	function arrayBufferToBase64(buffer) {
	  var binary = '';
	  var bytes = new Uint8Array(buffer);
	  var len = bytes.byteLength;
	  for (var i = 0; i < len; i++) {
	    binary += String.fromCharCode(bytes[i]);
	  }
	  return window.btoa(binary);
	}

	function toBlob(base64, mime_ctype) {
	    // 日本語の文字化けに対処するためBOMを作成する。
	    var bin = atob(base64.replace(/^.*,/, ''));
	    var buffer = new Uint8Array(bin.length);
	    for (var i = 0; i < bin.length; i++) {
	        buffer[i] = bin.charCodeAt(i);
	    }
	    // Blobを作成
	    try{
	        var blob = new Blob([buffer.buffer], {
//	            type: 'image/png'
				type: 'application/octet-stream'
	        });
	    }catch (e){
	        return false;
	    }
	    return blob;
    }
	// ----------------------------------------------------------------------------------- 添付ファイル END
</script>
