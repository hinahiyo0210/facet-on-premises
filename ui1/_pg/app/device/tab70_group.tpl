<div class="terminal_cnt">
	<script>

		function addGroup() {
			
			var $row = $($("#new_group_row").prop("outerHTML"));
			$row.hide();
			
			$("#groups").append($row);
			$row.fadeIn(200);
		}
		
		function saveGroup() {
			
			var checkNames = [];
			$(byNames("group_names[]")).each(function() {
				if ($(this).val() == "") {
					checkNames.push(escapeHtml($(this).attr("org-value")));
				}
			});

			
			if (checkNames.length == 0) {
				doPost('./registGroup', true);
				return;
			}

			var msg = "下記のグループは削除されます<br />よろしいですか？<br /><br />[" + checkNames.join(" / ") + "]<br />";
			msg += "<div class=\"btns\">";
			msg += "<a href=\"javascript:void(0);\" onclick=\"removeModal()\" class=\"btn btn_gray\">キャンセル</a>";
			msg += "<a href=\"javascript:void(0);\" onclick=\"doPost('./registGroup', true)\" class=\"btn btn_red\">OK</a>";
			msg += "</div>";
			showModal("削除の確認", msg);
		}
		
	</script>
	<h3 class="tit_cnt_main">カメラグループの設定</h3>
  {if ENABLE_AWS}
    <p class="cap_cnt_main">拠点ごとに複数台カメラを設置している場合、その拠点ごとにカメラを所属させることができます。<br>
      例えば東京支店内に複数台のカメラがある場合は「東京支店」グループにカメラ「A」、「B」、「C」を所属させ、異動があった場合などに一括で認証対象に設定することができます。</p>
  {else}
    <p class="cap_cnt_main">各フロアごとに複数台カメラを設置している場合、そのフロアごとにカメラを所属させることができます。<br>
      例えばAフロア内に複数台のカメラがある場合は「Aフロア」グループにカメラ「A」、「B」、「C」を所属させ、異動があった場合などに一括で認証対象に設定することができます。</p>
  {/if}
	<table id="groups" class="form_cnt">
		{foreach $form.group_ids|default:"" as $idx=>$id}
			{$group_name=$form.group_names[$idx]|default:""}
			<tr>
				<th>グループ名</th>
				<td>
					<input name="group_ids[]" type="hidden" value="{$id}" />
					<input class="update_group" name="group_names[]" type="text" placeholder="空にして登録すると削除されます" value="{$group_name}" org-value="{$group_name}" />
				</td>
			</tr>
		{/foreach}
		{foreach $form.new_group_names|default:"" as $new_name}
			{if !empty($new_name)}
				<tr>
					<th>グループ名</th>
					<td>
						<input class="new_group" name="new_group_names[]" type="text" placeholder="任意のグループ名をご入力ください" value="{$new_name}" />
					</td>
				</tr>
			{/if}
		{/foreach}
		<tr id="new_group_row">
			<th>グループ名</th>
			<td>
				<input class="new_group" name="new_group_names[]" type="text" placeholder="任意のグループ名をご入力ください" value="" />
			</td>
		</tr>
	</table>
	<a href="javascript:void(0);" onclick="addGroup()"  class="btn_plus"><i class="fas fa-plus-circle"></i>グループを追加</a>
	<a href="javascript:void(0);" onclick="saveGroup()" class="enter-submit btn_red">グループを登録</a>

</div>

{if Session::getLoginUser("apb_mode_flag")}
{*
	<div id="registApbGroup" class="terminal_cnt">

		<script>

			function addApbGroup() {
				
				var $row = $($("#new_apb_group_row").prop("outerHTML"));
				$row.hide();
				
				$("#apb_groups").append($row);
				$row.fadeIn(200);
			}
			
			function saveApbGroup() {
				
				var checkNames = [];
				$(byNames("apb_group_names[]")).each(function() {
					if ($(this).val() == "") {
						checkNames.push(escapeHtml($(this).attr("org-value")));
					}
				});

				
				if (checkNames.length == 0) {
					doPost('./registApbGroup', true);
					return;
				}

				var msg = "下記のAPBグループは削除されます<br />よろしいですか？<br /><br />[" + checkNames.join(" / ") + "]<br />";
				msg += "<div class=\"btns\">";
				msg += "<a href=\"javascript:void(0);\" onclick=\"removeModal()\" class=\"btn btn_gray\">キャンセル</a>";
				msg += "<a href=\"javascript:void(0);\" onclick=\"doPost('./registApbGroup', true)\" class=\"btn btn_red\">OK</a>";
				msg += "</div>";
				showModal("削除の確認", msg);
			}
			
		</script>

		<h3 class="tit_cnt_main">APBグループ設定</h3>
		<p class="cap_cnt_main">同一グループに属するカメラは入退室状況を共有します。<br>
			カメラごとに複数のグループに属する事が出来ます。</p>

		<table id="apb_groups" class="form_cnt">
			{foreach $form.apb_group_ids as $idx=>$id}
				{$apb_group_name=$form.apb_group_names[$idx]}
				<tr>
					<th>APBグループ名</th>
					<td>
						<input name="apb_group_ids[]" type="hidden" value="{$id}" />
						<input class="update_apb_group" name="apb_group_names[]" type="text" placeholder="空にして登録すると削除されます" value="{$apb_group_name}" org-value="{$apb_group_name}" />
					</td>
				</tr>
			{/foreach}
			{foreach $form.new_apb_group_names as $new_name}
				{if !empty($new_name)}
					<tr>
						<th>APBグループ名</th>
						<td>
							<input class="new_apb_group" name="new_apb_group_names[]" type="text" placeholder="任意のAPBグループ名をご入力ください" value="{$new_name}" />
						</td>
					</tr>
				{/if}
			{/foreach}
			<tr id="new_apb_group_row">
				<th>APBグループ名</th>
				<td>
					<input class="new_apb_group" name="new_apb_group_names[]" type="text" placeholder="任意のAPBグループ名をご入力ください" value="" />
				</td>
			</tr>
		</table>
		<a href="javascript:void(0);" onclick="addApbGroup()"  class="btn_plus"><i class="fas fa-plus-circle"></i>グループを追加</a>
		<a href="javascript:void(0);" onclick="saveApbGroup()" class="enter-submit btn_red">APBグループを登録</a>

	</div>
*}

{/if}
