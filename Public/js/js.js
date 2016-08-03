$(function() {	
	//轮播图
	$("ul.tab").tabs(".yibo_banner .yibo_banner_wrap_content > a", {effect:'fade',rotate: true,tabs:"li"}).slideshow({
		next:".btnR",
		prev:".btnL",
		autoplay:true,
		autopause:true,
		interval:4000
	});
        
	$("#menu-main-on").hover(function(){
		$(this).find($("ul")).show();
	},function(){
		$(this).find($("ul")).hide();
	});
	
	$("#spread_ad").click(function(){
		$("#aul").show();		  
	});
	
		$("#menu-main-onn").hover(function(){
		$(this).find($("ul")).show();
	},function(){
		$(this).find($("ul")).hide();
	});
	
	$("#spread_add").click(function(){
		$("#aull").show();		  
	});
	
			$("#menu-main-onnn").hover(function(){
		$(this).find($("ul")).show();
	},function(){
		$(this).find($("ul")).hide();
	});
	
	$("#spread_addd").click(function(){
		$("#aulll").show();		  
	});
	
	//捐赠
	$(".yibo_recommend_content ul li").hover(function(){
		$(this).find($(".care")).show();
	},function(){
		$(this).find($(".care")).hide();
	});
	
	//页面回到顶端

	showScroll();
	function showScroll() {
		$(window).scroll(function () {
			var scrollValue = $(window).scrollTop();
			scrollValue > 100 ? $('div[class=scroll]').fadeIn() : $('div[class=scroll]').fadeOut();
		});
		$('#scroll').click(function () {
			$("html,body").animate({scrollTop: 0}, 500);
		});
	}
   
	// $(".nav li").click(function(){
		// $(this).removeClass('nav_on').addClass('nav_on').siblings().removeClass('nav_on');
	// });
	// $('#menu-main li').click(function(){
		// $(this).removeClass('nav_on').addClass('nav_on').siblings().removeClass('nav_on');
	// })
	
})
