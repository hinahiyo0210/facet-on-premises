





/**
 * Mathオブジェクトを拡張 
 */
var Math = Math || {};
 
 
/**
 * 与えられた値の小数点以下の桁数を返す 
 * multiply, subtractで使用
 * 
 * 例)
 *   10.12  => 2  
 *   99.999 => 3
 *   33.100 => 1
 */
Math._getDecimalLength = function(value) {
    var list = (value + '').split('.'), result = 0;
    if (list[1] !== undefined && list[1].length > 0) {
        result = list[1].length;
    }
    return result;
};
 
 
/**
 * 乗算処理
 *
 * value1, value2から小数点を取り除き、整数値のみで乗算を行う。 
 * その後、小数点の桁数Nの数だけ10^Nで除算する
 */
Math.multiply = function(value1, value2) {
    var intValue1 = +(value1 + '').replace('.', ''),
        intValue2 = +(value2 + '').replace('.', ''),
        decimalLength = Math._getDecimalLength(value1) + Math._getDecimalLength(value2),
        result;
 
    result = (intValue1 * intValue2) / Math.pow(10, decimalLength);
 
    return result;
};
 
 
/**
 * 減算処理
 *
 * value1,value2を整数値に変換して減算
 * その後、小数点の桁数分だけ小数点位置を戻す
 */
Math.subtract = function(value1, value2) {
    var max = Math.max(Math._getDecimalLength(value1), Math._getDecimalLength(value2)),
        k = Math.pow(10, max);
    return (Math.multiply(value1, k) - Math.multiply(value2, k)) / k;
};
 
 



//ページの最後に仕掛けれた処理
var __lastInvokes = [];
function doPageLast() {
	
	$(function() {
		for (var i = 0; i < __lastInvokes.length; i++) {
			__lastInvokes[i]();
		}
	});
	
}



// -----------------------------------------------------

// 同期でajax取得
function getAjax(url, callback, data) {
	
	$.ajax({
		type: "POST"
		, url: url
		, dataType: "json"
		, data: data
		, async: false		// 同期通信
		, cache: false		// キャッシュ無効
		, error: function(request, textStatus, errorThrown) {
		
			alert("エラーが発生しました。\n" + textStatus + "\n" + errorThrown);
		}
		, success: function(data, dataType) {

//			if (data.error) {
//				alert(data.error);
//				return;
//			}
			callback(data);
			
		}
	});
	
	
}



/*
 *  日数または月数を加算
 *
 *  dt: 基準となる Date オブジェクト
 *  dd: 日数または月数
 *   u: 'D': dd は日数
 *      'M': dd は月数
 *
 */
function addDate(dt, dd, u) {
	if (typeof u == 'undefined') u = 'D';
		var y = dt.getFullYear();
		var m = dt.getMonth();
		var d = dt.getDate();
		var r = new Date(y, m, d);
		if (u == 'D') {
		r.setDate(d + dd);
	} else if (u == 'M') {
		var e1 = (new Date(y, m+1, 0)).getDate();
		m += dd;
		y += parseInt(m/12);
		m %= 12;
		var e2 = (new Date(y, m+1, 0)).getDate();
		r.setFullYear(y, m, (d == e1 || d > e2 ? e2 : d));
	}
	return r;
}

/*
 *  経過年・月・日数の計算
 *
 *  dt1: 開始年月日の Date オブジェクト
 *  dt2: 終了年月日の Date オブジェクト
 *    u:  'Y': 経過年数を求める
 *        'M': 経過月数を求める
 *        'D': 経過日数を求める
 *       'YM': 1年に満たない月数
 *       'MD': 1ヶ月に満たない日数
 *       'YD': 1年に満たない日数
 *    f: true: 初日算入
 *      false: 初日不算入
 */
function getDateDiff(dt1, dt2, u, f) {
	if (typeof u == 'undefined') u = 'D';
	if (typeof dt2 == 'undefined') dt2 = new Date;
	if (f) dt1 = addDate(dt1, -1, 'D');
	var y1 = dt1.getFullYear();
	var m1 = dt1.getMonth();
	var y2 = dt2.getFullYear();
	var m2 = dt2.getMonth();
	var dt3, r = 0;
	if (u == 'Y') {
		r = parseInt(dateDiff(dt1, dt2, 'M') / 12);
	} else if (u == 'M') {
		r = (y2 * 12 + m2) - (y1 * 12 + m1);
		dt3 = addDate(dt1, r, 'M');
		if (dateDiff(dt3, dt2, 'D') < 0) --r;
	} else if (u == 'D') {
		dt1 = new Date(y1, m1, dt1.getDate());
		dt2 = new Date(y2, m2, dt2.getDate());
		r = parseInt((dt2-dt1)/(24*3600*1000));
	} else if (u == 'YM') {
		r = dateDiff(dt1, dt2, 'M') % 12;
	} else if (u == 'MD') {
		r = dateDiff(dt1, dt2, 'M');
		dt3 = addDate(dt1, r, 'M');
		r = dateDiff(dt3, dt2, 'D');
	} else if (u == 'YD') {
		r = dateDiff(dt1, dt2, 'Y');
		dt3 = addDate(dt1, r*12, 'M');
		r = dateDiff(dt3, dt2, 'D');
	}
	return r;
}




// 日付の差分日数を取得する
function getSpanDays(date1, date2) {
	
	var diff = (date2.getTime() - date1.getTime()) / (1000 * 60 * 60 * 24);
	
	diff = Math.floor(diff);
	
	return diff;
}


// htmlをエスケープする
function escapeHtml(val){

	return $('<div />').text(val).html();

}


// jqeuryで背景色を点滅
function jqFlashing(query, bgColor1, bgColor2, depth) {
/*
	if (!depth) depth = 1;
	
	$(query).animate(
		{backgroundColor: (depth % 2 == 0 ? bgColor1 : bgColor2 )}
		, {duration: 'fast', complete: function() {
			
			if (depth >= 4) return;
			jqFlashing(query, bgColor1, bgColor2, depth + 1);
			
		}}
	);
*/

	$(query).animate(
		{backgroundColor: bgColor1}
	);
	
}



/**
 * 年齢を計算
 * @param birthday
 * @return
 */
function getAge(y, m, d) {
	
	if (clean(y) == "" || clean(m) == "" || clean(d) == "" ) {
		return null;
	}
	
	var birth = [y, m, d];
	var today = new Date();
	if ( parseInt(birth[1], 10) * 100 + parseInt(birth[2], 10) > (today.getMonth() + 1) * 100 + today.getDate() ) {
		return today.getFullYear() - parseInt(birth[0], 10) - 1;
	}
	return today.getFullYear() - parseInt(birth[0], 10);
}


/*
 *  日数または月数を加算
 *
 *  dt: 基準となる Date オブジェクト
 *  dd: 日数または月数
 *   u: 'D': dd は日数
 *      'M': dd は月数
 *
 */
var dateAdd = function(dt, dd, u) {
  if (typeof u == 'undefined') u = 'D';
  var y = dt.getFullYear();
  var m = dt.getMonth();
  var d = dt.getDate();
  var r = new Date(y, m, d);
  if (u == 'D') {
    r.setDate(d + dd);
  } else if (u == 'M') {
    var e1 = (new Date(y, m+1, 0)).getDate();
    m += dd;
    y += parseInt(m/12);
    m %= 12;
    var e2 = (new Date(y, m+1, 0)).getDate();
    r.setFullYear(y, m, (d == e1 || d > e2 ? e2 : d));
  }
  return r;
};

/*
 *  経過年・月・日数の計算
 *
 *  dt1: 開始年月日の Date オブジェクト
 *  dt2: 終了年月日の Date オブジェクト
 *    u:  'Y': 経過年数を求める
 *        'M': 経過月数を求める
 *        'D': 経過日数を求める
 *       'YM': 1年に満たない月数
 *       'MD': 1ヶ月に満たない日数
 *       'YD': 1年に満たない日数
 *    f: true: 初日算入
 *      false: 初日不算入
 */
var dateDiff = function(dt1, dt2, u, f) {
  if (typeof u == 'undefined') u = 'D';
  if (typeof dt2 == 'undefined') dt2 = new Date;
  if (f) dt1 = dateAdd(dt1, -1, 'D');
  var y1 = dt1.getFullYear();
  var m1 = dt1.getMonth();
  var y2 = dt2.getFullYear();
  var m2 = dt2.getMonth();
  var dt3, r = 0;
  if (u == 'Y') {
    r = parseInt(dateDiff(dt1, dt2, 'M') / 12);
  } else if (u == 'M') {
    r = (y2 * 12 + m2) - (y1 * 12 + m1);
    dt3 = dateAdd(dt1, r, 'M');
    if (dateDiff(dt3, dt2, 'D') < 0) --r;
  } else if (u == 'D') {
    dt1 = new Date(y1, m1, dt1.getDate());
    dt2 = new Date(y2, m2, dt2.getDate());
    r = parseInt((dt2-dt1)/(24*3600*1000));
  } else if (u == 'YM') {
    r = dateDiff(dt1, dt2, 'M') % 12;
  } else if (u == 'MD') {
    r = dateDiff(dt1, dt2, 'M');
    dt3 = dateAdd(dt1, r, 'M');
    r = dateDiff(dt3, dt2, 'D');
  } else if (u == 'YD') {
    r = dateDiff(dt1, dt2, 'Y');
    dt3 = dateAdd(dt1, r*12, 'M');
    r = dateDiff(dt3, dt2, 'D');
  }
  return r;
};


/**
 * プレフィックスを除去
 */
function excludePrefix(val, prefix) {
	
	if (val == null || val == "" || prefix == null || prefix == "") return val;

	
	if (!startsWith(val, prefix)) {
		return val;
	}
	
	var idx = prefix.length;
	
	return val.substr(idx, val.length - idx);
}


/**
 * 指定文字列から開始される場合にtrue
 * @param $val
 * @param $test
 */
function startsWith(val, test) {
	if (val == null || val == "") return false;
	return val.indexOf(test) == 0;
}

/**
 * 指定文字列で終了する場合にtrue
 * @param $val
 * @param $test
 */
function endsWith(val, test) {
	if (val == null || val == "") return false;
	var sub = val.length - test.length;
	return (sub >= 0) && (val.lastIndexOf(test) === sub);
}



/**
 * 本日の日付を返す
 * @return
 */
function getJaToday() {
	var d = new Date();
	var m = d.getMonth() + 1;
	return d.getFullYear() + "年" + m + "月" + d.getDate() + "日";
}


/**
 * PHP配列のnameを考慮して要素を取得
 * @param name
 * @return
 */
function byNameIfArray(name) {
	
	var ret = byName(name);
	
	if (ret == null) {
		return byName(name + "[]");
	}
	return ret;
}

/**
 * disabledでは無い要素を取得
 * @param name
 * @return
 */
function byNameEnabled(name) {
	
	var elms = document.getElementsByName(name);
	if (elms == null) return null;
	
	for (var i = 0; i < elms.length; i++) {
		if (elms[i].disabled) continue;
		return elms[i];
	}
	
	return null;
}

/**
 * 指定クラスを持つテーブルのnodeが該当するtrの背景色を変更
 * @param tableClass
 * @return
 */
function highlightTr(node, tableClass, color) {
	
	var tr = null;
	
	while (true) {
		var tagName = null;
		try {
			tagName = node.tagName.toLowerCase();
		} catch (e) {
			return;
		}
		
		if (tagName == "tr") {
			tr = node;

		} else if (tagName == "table") {
			if ($(node).hasClass(tableClass)) {
				$("td", tr).css("backgroundColor", color);
				$("td", tr).closest(".toggle_area").find(".toggle_title").css("backgroundColor", color);

				return;
			}

		}
		
		try {
			node = node.parentNode;
			if (node) {
			} else {
				break;
			}
		
		} catch (e) {
			break;
		}
		
	}
	
	
}

/**
 * 指定名の場所へスクロール
 * @param name
 * @return
 */
function scrollById(id) {
	
	try {
		$.scrollTo($("#" + id).position().top - 130, 800);
	} catch (e) {}
}


/**
 * 指定名の場所へスクロール
 * @param name
 * @return
 */
function scrollByName(name1, name2) {
	
//	try {
		var elm = $(byNameIfArray(name1));
		if (elm.css("display") == "none") {
			elm = elm.next();
		}
		
		$.scrollTo(elm.position().top - 130, 800);
		
//	} catch (e) {
//		
//		try {
//			var elm = $(byNameIfArray(name2));
//			if (elm.css("display") == "none") {
//				elm = elm.next();
//			}
//			$.scrollTo(elm.position().top - 130, 800);
//		} catch (e) {}
//	}
}

// 全てreplace
function replaceAll(value, find, replaceValue) {
	
	while (value.indexOf(find) != -1) {
		value = value.replace(find, replaceValue);
	}
	return value;
}

// 文字充填(左)
function leftPad(value, len, char) {
	
	value = value + "";
	
	while (value.length < len) {
		value = char + value;
	}
	
	return value;
}

// 文字充填(右)
function rightPad(value, len, char) {
	
	value = value + "";
	
	while (value.length < len) {
		value = value + char;
	}
	
	return value;
}

// document.getElementById()の短縮形
function byId(id) {
	return document.getElementById(id);
}

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

// optionsの再構成
function replaceOptions(select, options) {
	
	while (select.options.length != 0) {
		select.options[0] = null;
	}
	
	if (options == null) return;
	
	for (var i = 0; i < options.length; i++) {
		select.options[i] = options[i];
	}
}

//optionsの再構成(optionはコピーを生成)
function replaceOptionsByCopy(select, options) {
	
	while (select.options.length != 0) {
		select.options[0] = null;
	}
	
	if (options == null) return;
	
	for (var i = 0; i < options.length; i++) {
		select.options[i] = new Option(options[i].text, options[i].value);
	}
}


// null, undeinfedなどを空文字に変換する
function clean(val) {
	
	if (val == null || val == undefined || val == "null" || val == "undefined") {
		return "";
	}
	
	return val;
}



// 数値に3桁カンマを挿入する
function formatNumber(str) {
	if (clean(str) == "") return "";
	var num = new String(str).replace(/,/g, "");
	while(num != (num = num.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
	return num;
}
	
// 日付のフォーマットを行う
function formatDate(millis) {
	if (millis == null) return "";
	
	var date = new Date(millis);
	
	var m = date.getMonth() + 1;
	if (m < 10) m = "0" + m;
	
	var d = date.getDate();
	if (d < 10) d = "0" + d;
	
	var h = date.getHours();
	if (h < 10) h = "0" + h;
	
	var mi = date.getMinutes();
	if (mi < 10) mi = "0" + mi;
	
	var s = date.getSeconds();
	if (s < 10) s = "0" + s;
	
	return date.getFullYear() + "/" + m + "/" + d + " " + h + ":" + mi + ":" + s;
	
}
//optionsの再構成
function replaceOptions(select, options) {
	
	while (select.options.length != 0) {
		select.options[0] = null;
	}
	
	if (options == null) return;
	
	for (var i = 0; i < options.length; i++) {
		select.options[i] = options[i];
	}
}


//フォーム内容を復元
function restoreForm(q) {
	
	var vars = [];
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

	for(var i = 0; i < hashes.length; i++) {
		var hash = hashes[i].split('=');
		
		var name = decodeURI(hash[0]);
		var value = decodeURI(hash[1]);
		
		if (vars[name] == null) {
			vars[name] = [];
		}
		
		vars[name].push(value);
	}
	
	$(q + " input").each(function() {
		var elm = $(this);
		var name = elm.attr("name");
		
		if (clean(name) == "") return;
		if (vars[name] == null) return;

		if (elm.attr("type") == "checkbox") {
			
			var val = elm.val();
			
			for (var i = 0; i < vars[name].length; i++) {
				if (val == vars[name][i]) {
					elm.attr("checked", "checked");
					return;
				}					
			}
			
		} else {
			elm.val(vars[name][0]);
		}
		
	});
	
	
}


function replaceForm(query) {

	$(query + " input").each(function(idx, elm) {
		
		var jq = $(elm);
		var type = jq.attr("type").toLowerCase();
		
		// ------------------------------- [input type="text"]
		if (type == "text") {
			var text = $("<span></span>").text(jq.val());
			jq.attr("type", "hidden").after(text);
			return;
		}
		
		// ------------------------------- [input type="radio"]
		if (type == "radio") {
			var labelTag = jq.parent();

			if (!elm.checked) {
				labelTag.html("");
				labelTag.hide();
				return;
			}
			
//				var text = labelTag.text();
//				labelTag.html("<input type='hidden' name='" + elm.name + "' value='" + elm.value + "' />" + text);
			
			var lbl = labelTag.html();
		
			jq.attr("disabled", "disabled");
			jq.hide();
			
			labelTag.append("<input type='hidden' name='" + elm.name + "' value='" + elm.value + "' />");
			
			return;
		}

		// ------------------------------- [input type="checkbox"]
		if (type == "checkbox") {
			var labelTag = jq.parent();

			var lbl = labelTag.html();
			
			jq.attr("disabled", "disabled");
			jq.hide();
			
			labelTag.append("<input type='hidden' name='" + elm.name + "' value='" + elm.value + "' />");

			var html = labelTag.html();

			if (elm.checked) {
				html = "✓ " + html;
			} else {
				html = "<span style='text-decoration:line-through; color: #aaa;'>" + html + "</span>";
			}
			
			labelTag.html(html);
			
			return;
		}
	});
	
	
	$(query + " select").each(function(idx, elm) {
		
		var jq = $(elm);
		var value = jq.val();
		var name = elm.name;
		
		var options = elm.options;
		var text = "";
		
		for (var i = 0; i < options.length; i++) {
			if (options[i].value == value) {
				text = options[i].text;
				break;
			}
		}
		
		jq.attr("name", "_____").hide();
		jq.after("<input type='hidden' name='" + name + "' value='" + value + "' />" + text);
		
	});
	
	
	$(query + " textarea").each(function(idx, elm) {
		
		var jq = $(elm);
		var value = jq.val();
		var name = elm.name;
		
		jq.hide();
		
		if (!jq.hasClass("ckeditor")) {
			value = escapeHtml(value);
		}
		
		value = replaceAll(value, "\r\n", "\n");
		value = replaceAll(value, "\n", "<br />");
		
		jq.after('<div>' +  value + '</div>');
	});
		
	
}



$(function() {
	
	var $openedElm = null;
	var animate = false;
	var idCounter = 1;
	var cookieName = "toggle_opened_" + _ACTION;
	$(".toggle_title").each(function() {

		if (!$(this).attr("id")) {
			$(this).attr("id", "___toggle_title_" + idCounter);
			idCounter++;
		}
		
		$(this).click(function() {
			
			if (animate) return;
			animate = true;
			
			var close = function($elm, callback) {
				// 閉じる。
				if (!_IS_SP) {
					$("#mask").remove();
				}
				
				var $area = $elm.closest(".toggle_area");
				$area.find(".toggle_content").slideUp(300, function() {
					$area.removeClass("opened");
					if ($elm.attr("onclose")) {
						eval($elm.attr("onclose"));
					}
					if (callback) callback();
				});
				$.cookie(cookieName, "");
			};
			
			
			$(".toggle_area.opened").not("#" + $(this).attr("id")).each(function() {
				close($(this));
			});
			
			var $area = $(this).closest(".toggle_area");
			var $that = $(this);
			if ($area.hasClass("opened")) {
				close($(this), function() {
					animate = false;
				});
				
			} else {
				$area.addClass("opened");
				$.cookie(cookieName, $(this).attr("id"));
				
				if (!_IS_SP) {
					var $mask = $("<div id='mask'></div>");
					$mask.click(function() { $that.click(); });
					$(document.body).append($mask);
				}
					
				setTimeout(function() {
					// 開く。
					$area.find(".toggle_content").slideDown(300, function() {
						if ($that.attr("onopen")) {
							eval($that.attr("onopen"));
						}
						animate = false;
					});
				}, _IS_SP ? 1 : 400);
				
			}
			
		});
		
	});
	
//	if ($.cookie(cookieName)) {
//		var id = $.cookie(cookieName);
//
//		var $mask = $("<div id='mask'></div>");
//		$mask.click(function() { $("#" + id).click(); });
//		$(document.body).append($mask);
//
//		var $area = $("#" + id).closest(".toggle_area");
//		$area.addClass("init").addClass("opened").find(".toggle_content").show();
//		$area.removeClass("init");
//	}
	
	
});



$(function() {
	
	$(".submit").each(function() {
		var $submit = $(this);
		
		$(this).closest("form").keypress(function(e) {
			var key = e.keyCode || e.charCode || 0;
			if (key == 13) {
				$submit.click();
			}
		});
		
	});
	

});

function doSpMenuOpen() {
	

	if ($(document.body).hasClass("sp_menu_open")) {
		$(document.body).removeClass("sp_menu_open")
		$("#sp_menu i").animate({left: 0});

	} else {
		$(document.body).addClass("sp_menu_open")
		$("#sp_menu i").animate({left: 120});

	}


}


$(function() {
	
	if (_IS_SP) {
		$(".no-sp-support").attr("href", "javascript:void(0)").unbind().click(function() {
			alert("まだスマホ版は未対応です。PCから行ってください。");
		});
	}
	

});
