$(function(){

	$('.user_input,.user_textarea').bind('click', function(){
		$('.user_input,.user_textarea').removeClass('user_input_click');
		$(this).addClass('user_input_click');
	});
	$(".nav ul li").click(function(){					
		$(this).removeClass('nav_on').addClass('nav_on').siblings().removeClass('nav_on');
	});
	
	/*$(".con_fl_title a").click(function(){
		$('.con_fl_list').toggle(1000);
	});*/	
	
	$(".con_fl_list ul li").click(function(){					
		$(this).removeClass('fl_list_on').addClass('fl_list_on').siblings().removeClass('fl_list_on');
	});
	$(".user_table tbody tr").hover(
		
		function () {
			$(this).addClass("tr_hover");
		},
		function () {
			$(this).removeClass("tr_hover");
		}
	);
});
