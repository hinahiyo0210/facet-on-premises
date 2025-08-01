
<script>


function dispBulkFile() {

	$("#bulk_upload_btn_area").hide();
	$("#bulk_upload_btn").removeClass("btn_disabled");
	
	var formatBytes = function(bytes, decimals = 1) {
	    if (bytes === 0) return '0 Bytes';

	    const k = 1024;
	    const dm = decimals < 0 ? 0 : decimals;
	    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

	    const i = Math.floor(Math.log(bytes) / Math.log(k));

	    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
	}

	
	if (!byName("bulkFile").files.length) return;

	var fileName = byName("bulkFile").files[0].name;
	var fileSize = byName("bulkFile").files[0].size;
	
	var limitOver = "";
	if (fileSize > {$bulk_zip_limit}) {
		limitOver = true;
	}
	
	$("#bulk_upload_file_info").html(escapeHtml(fileName) + "(<span " + (limitOver ? "style='color:red; font-size: 1.1em;'" : "") + ">" + formatBytes(fileSize) + "<span>)");
	$("#bulk_upload_btn_area").fadeIn(400);
	if (limitOver) {
		$("#bulk_upload_btn").addClass("btn_disabled");
	}
	
}

function postBulk() {
	
	showModal("一括登録", $("#bulk_upload_modal_template").html(), null, function() {

		var checkProgress = function () {
			
			var started = false;
			
			var check = function() {
				
				doAjax("./bulkUploadCheckProgress", {}, function(result) {

					if (result && result.rowCount) {
						$(".bulk_progress").show();
						started = true;
						
					} else if (!started) {
						setTimeout(check, 1500);
						return;
						
					} else {
					
						$(".bulk_progress progress").attr("max",   "100");
						$(".bulk_progress progress").attr("value", "100");
						$(".bulk_progress").fadeOut(200, function() {
							removeModal();
						});
						return;
					}

					$(".bulk_progress progress").attr("max",   result.rowCount);
					$(".bulk_progress progress").attr("value", result.processed);
					$(".bulk_progress_info").text(result.info);
					
					setTimeout(check, 1500);
				});
			};
			
			setTimeout(check, 500);
			
		}
	
		$(".bulk_progress").fadeIn(400);
		$(".bulk_progress progress").attr("max", "100").attr("value", "0");

		doPost('./bulkFileUpload');
		checkProgress();

	});


	
}

</script>


<!-- ダウンロードモーダル -->
<div id="bulk_upload_modal_template" style="display:none">
	アップロード処理中です。<br >
	対象件数が多い場合、時間が掛かる場合があります。<br >
	<br />
	<div id="loading" class="loader">Loading...</div>
	<div class="bulk_progress" style="display:none"><progress style="width: 200px; "></progress></div>
	<div class="bulk_progress_info"></div>
</div>



<h2 class="tit_cnt_main">新規ユーザー一括登録</h2>

<table class="form_cnt regist_cnt">
	<tr>
		<th class="tit">データ一括登録</th>
		<td class="cap">
			CSV、エクセルファイルから一括登録が可能です。<br>
			CSV、エクセルのテンプレートをダウンロードのうえ、登録情報を入力したものをアップロードします。
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td class="btn_wrap">
			<a href="./downloadBulkTemplate?format=excel" class="btn_blue"><i class="fas fa-arrow-alt-from-top"></i>エクセルテンプレートダウンロード</a>
			<a href="./downloadBulkTemplate?format=csv" class="btn_blue"><i class="fas fa-arrow-alt-from-top"></i>CSVテンプレートダウンロード</a>
			<p style="font-size: 14px;">※CSVはFaceFCと互換性があるため、FaceFC管理画面で出力した内容を取り込むことが可能です</p>
			<a href="/ui1/static/manual/bulk_manual.pdf" target="_blank" class="link_pdf"><i class="fas fa-file-alt"></i>一括登録データの作り方について（PDF）</a>
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td id="bulk-file" class="btn_file">
			<label id="bulk_file_label" for="{seqId()}"><i class="fas fa-arrow-to-top"></i>ファイルを選択<input type="file" name="bulkFile" accept=".zip" onchange="dispBulkFile()" id="{seqId(1)}" error-target="#bulk_file_label"></label>
		</td>
	</tr>
	
	<tr>
		<td>
		</td>
		<td class="btn_wrap">
			<div id="bulk_upload_btn_area" style="display: none">
				<span id="bulk_upload_file_info" style="font-weight: bold;"></span>
				<a id="bulk_upload_btn" href="javascript:void(0)" onclick="postBulk()" class="btn_red">ファイルをアップロードして一括登録</a>
			</div>
		</td>
	</tr>
	
</table>
