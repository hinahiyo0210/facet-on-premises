// viewport
var ua = navigator.userAgent;
if((ua.indexOf('iPhone') > 0) || ua.indexOf('iPod') > 0 || (ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0)){
	document.write('<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0,user-scalable=no">');
} else {
	document.write('<meta name="viewport" content="width=1200">');
}