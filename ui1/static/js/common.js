//アカウントログアウト
//-------------------------------------------------------------------->
$(function(){
	$(".mainbar_info .account").on("click", function() {
		$(this).nextAll("a").slideToggle();
	});
});


//左メニュー
//-------------------------------------------------------------------->
$(function(){
	$('.menu_close').click(function(){
		//$('.adminmenuwrap').css('width','30px');
	});
	$('.menu_close').on('click', function () {
		$('.adminmenuwrap').toggleClass('menu_open');
		$('.main_container').toggleClass('menu_open');
	});
	
});


//タブ切り替え
//-------------------------------------------------------------------->
$(function() { 
  $('.tab_btn li').click(function() {
    var index = $('.tab_btn li').index(this); 
    $('.tab_btn li').removeClass('active');
    $(this).addClass('active');
    $('.tab_cnt').removeClass('show').eq(index).addClass('show'); 
  });
});


//ログリスト
//-------------------------------------------------------------------->
$(function(){   
  var height = $(".log_table").height();
	$('.log_table_imglist').css('height', height + 'px');
});