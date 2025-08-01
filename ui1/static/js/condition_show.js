
$(function() {
	
	// document.getElementsByName()[0]の短縮形
	function byName(name) {
		var elms = document.getElementsByName(name);
		if (elms == null || elms.length == 0) return null;
		return elms[0];
	}

	function byNames(name) {
		var elms = document.getElementsByName(name);
		if (elms == null || elms.length == 0) return null;
		return elms;
	}
	
	// null, undeinfedなどを空文字に変換する
	function clean(val) {
		
		if (val == null || val == undefined || val == "null" || val == "undefined") {
			return "";
		}
		
		return val;
	}

	var doConditionShow = function (jq, targetJq, valuesArr, animate, isSelect) {
		
		var isEmptySelectedValue = function(arr) {
			if (arr.length == 0) return true;
			if (arr.length == 1 && clean(arr[0]) == "") return true;
			
			return false;
		};
		
		var selectedValue = [];
		if (isSelect) {
			selectedValue.push(targetJq.val());
			
		} else {
			targetJq.filter(":checked").each(function(idx) {
				selectedValue.push($(this).val());
			});
		}
		
		var show = false;
		for (var i = 0; i < valuesArr.length; i++) {
			var value = clean(valuesArr[i]);
			if (value == "") continue;
			
			if (value == "EMPTY" && isEmptySelectedValue(selectedValue)) {
				show = true;
				break;				
			}
			if (value == "!EMPTY" && !isEmptySelectedValue(selectedValue)) {
				show = true;
				break;				
			}
			
			for (var j = 0; j < selectedValue.length; j++) {

				if (value.substr(0, 1) == "!") {
					if (selectedValue[j] != value.substr(1, value.length)) {
						show = true;
						break;
					}
				} else if (value.substr(0, 2) == ">=") {
					if (selectedValue[j] >= value.substr(2, value.length)) {
						show = true;
						break;
					}					

				} else if (value.substr(0, 1) == ">") {
					if (selectedValue[j] > value.substr(1, value.length)) {
						show = true;
						break;
					}
					
				} else if (value.substr(0, 2) == "<=") {
					if (selectedValue[j] <= value.substr(2, value.length)) {
						show = true;
						break;
					}					

				} else if (value.substr(0, 1) == "<") {
					if (selectedValue[j] < value.substr(1, value.length)) {
						show = true;
						break;
					}					

					
				} else {
					if (selectedValue[j] == value) {
						show = true;
						break;
					}
				}
				
			}
			
			if (show) break;
		}

		if (show) {
			if (animate) {
				jq.fadeIn(animate ? 500 : 0);
			} else {
				jq.show();
				
			}
			//jq.fadeIn(animate ? 500 : 0);
			
			// showになった要素内のinputを再度有効化する。
			jq.find('input[type="text"]').removeAttr("disabled");
			jq.find('select').removeAttr("disabled");
		
		} else {
			if (animate) {
				jq.hide();
			} else {
				jq.hide();
			}
			//jq.fadeOut(animate ? 500 : 0);	
			
			// サーバへ値が飛ばないようにdisabledにする。
			jq.find('input[type="text"]').attr("disabled", "disabled");
			jq.find('select').attr("disabled", "disabled");
			
		}
		
	}
	
	
	$(".condition_show").each(function(idx, elm) {
		
		var jq = $(elm);
		var targetName = jq.attr("conditionTarget");
		var targetJq = $(byNames(targetName));
		var values = jq.attr("conditionValues");
		var valuesArr = values.split(",");
		var isSelect = byName(targetName).tagName.toLowerCase() == "select";
		
		// select, checkbox, radioに対応している
		if (isSelect) {
			targetJq.change(function() {
				doConditionShow(jq, targetJq, valuesArr, true, isSelect);
			});
			
		} else {
			var type = $(byName(targetName)).attr("type");
			if (type == "radio" || type == "checkbox") {
				targetJq.change(function() {
					doConditionShow(jq, targetJq, valuesArr, true, isSelect);
				});
			}
			
		}
		
		// 初期呼び出し。
		doConditionShow(jq, targetJq, valuesArr, false, isSelect);
		
	});
	
	
});


