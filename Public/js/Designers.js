$(function() {		
			var oContainer = $('#ad_container');
			var dCells = 0;     //div列数
			var dSpace = 10;    //div间距
			var iSpace = 10;    //img间距
			var iWidth = parseInt($("input[name='width']").val()); //img宽度
			iWidth = (iWidth > 200) ? iWidth : 200;
			var dWidth =iWidth + iSpace*2;   //div宽度
			var dOuterWidth = dWidth + dSpace;
			var dHeight;
			var sUrl = '/Stylist/getAdList';
			var arrT = [];
			var arrL = [];
			var iPage = 0;
			var iBtn = true;
			var uid = $("#userIds").val();
			oContainer.css('height','100px');
			function setCell() {
				dCells = Math.floor($(window).innerWidth() / dOuterWidth);
				if (dCells < 1) {
					dCells = 1;
				} else if (dCells > 6) {
					dCells = 6;
				}
				oContainer.css('width', dCells * dOuterWidth) - 10;
			}
			setCell();
			
			for (var i=0; i<dCells; i++) {
				arrT[i] = 0;
				arrL[i] = dOuterWidth * i;
			}
			//console.log(dCells);
			//console.log(arrL);
			
			function getData() {

				var size_id = $("input[name='size_id']").val();
				var title = $("input[name='title']").val();
				if (!iBtn) {
					return ;
				}
				iBtn = false;
				iPage++;
				
				$.getJSON(sUrl, {page:iPage,size_id:size_id,title:title}, function(jData) {
					if(jData == ''){
						iBtn = false;
						//var iH = $(window).scrollTop() + $(window).innerHeight();
						//oContainer.css( "height",iH)
						$(window).unbind('scroll');
						$(".loadend").show();
						return false;
					}
					$('#page_loading').show();
					$.each(jData, function(index, obj) {
						 var Div = $('<div class="imgbox masonry-brick clearfix" style="position:absolute;" onmouseover="$(this).find(\'.care\').show()" onmouseout="$(this).find(\'.care\').hide();">'+
										 '<div class="care" >'+
											 '<a class="donate" href="javascript:void(0);" target="_blank">赞助</a>'+
											 '<a class="heart"  ><img src="/Public/images/yibo_pic5.png" width="27" height="27"/></a>'+
										 '</div>'+
										'<a class="lightbox" title="" target="_blank" alt="" href="javascript:void(0);">'+
											'<img src="" border="0" />'+
										'</a>'+
										 '<p class="impressions_like clearfix">'+
											// '<span class="impressions" ></span>'+ 
											'<span class="like">赞：<span class="likes_nums"></span></span>'+
										'</p>'+
										 '<p class="img_like">'+
										 '</p>'+
									'</div>'
						); 
						var img = Div.find('img:eq(1)');
						var p1 = Div.find('p:eq(0)');
						var p2 = Div.find('p:eq(1)');
						var a1 = Div.find('a:eq(1)');
						var a0 = Div.find('a:eq(0)');
						var a2 = Div.find('a:eq(2)');
						//a2.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/buy/id/'+obj.id+'\')');
						if(obj.type == 5){
							a0.addClass('donateyi');
							//a0.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/buy/id/'+obj.id+'\')');
							a0.attr('href','/Stylist/detail/id/'+obj.id);
							a0.text('已售出');
							a2.attr('href','/Stylist/detail/id/'+obj.id);
							//a2.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/buy/id/'+obj.id+'\')');
						}else{
							if(obj.status == 3 && obj.type == 4){ 
								a0.text('购买');
								a0.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/detail/id/'+obj.id+'\');return false;');
								a2.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/detail/id/'+obj.id+'\');return false;');
							}else if(obj.status == 3){
								a0.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/detail/id/'+obj.id+'\');return false;');
								a0.text('赞助');
								a2.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/detail/id/'+obj.id+'\');return false;');
							}else if(obj.status == 2){
								a0.addClass('donateyi');
								a0.attr('href','/Stylist/detail/id/'+obj.id);
								//a0.attr('onclick','login('+uid+',\''+obj.yiboUrl+'/Home/Stylist/buy/id/'+obj.id+'\')');
								a0.text('已赞助');
								a2.attr('href','/Stylist/detail/id/'+obj.id);
							}
						}
						var messageImg = Div.find(".message_img");
						if(parseInt(obj.width) <200){
							iWidth = 200;
							var iHeight = (parseInt(obj.height)) * (iWidth / obj.width);			
						}else{
							iWidth = parseInt(iWidth);
							var iHeight = parseInt(obj.height);
						}
						img.css({
							width	:	iWidth,
							height	:	iHeight
						});

						var numHeight = 34;
						var liuHeight = 27;
						var pingHeight = 40;


						//div宽高
						dHeight = iHeight + numHeight+pingHeight+10 ;
						//dHeight = iHeight + numHeight+10 ;
						
						var _index = getMin();
						Div.css({
							left	:	arrL[_index],
							top		:	arrT[_index]
						});


						arrT[_index] += dHeight + 10;
						
						oContainer.append(Div);

						var objImg = new Image();
						objImg.onload = function() {
							img.attr('src',this.src);
							img.attr('alt',this.alt);
						}
						objImg.src = obj.pic;
						objImg.alt = obj.title+obj.id;
						p1.find('.likes_nums').text(obj.likes)
						//p1.find('.impressions').text('展现量：0');
						//img.on('click',function(){
						//	window.open('/Stylist/buy/id/'+obj.id);
						//}) 
						a1.on('click',function(e){
							$.ajax({
								url:'/Stylist/addLikes',
								type:'post',
								data:{id:obj.id},
								dataType:'json',
								success:function(res){
									if(res.status == 1){
										var likes_nums = parseInt(p1.find('.likes_nums').text());
										var new_likes_nums = likes_nums + 1;
										p1.find('.likes_nums').text(new_likes_nums);
										anp(e);
									}else{
										dialogs('',res.info,2);
									}
								}
							})
						
						})
						
						setTimeout(function() {
							$('#page_loading').hide();
						},1000)
						
						iBtn = true;
					})
					
				});
				
			}
			getData();	
			function getMin() {
				var v = arrT[0];
				var _index = 0;
				
				for (var i=1; i<arrT.length; i++) {
					if (arrT[i] < v) {
						v = arrT[i];
						_index = i;
					}
				}
				return _index;
			}
			
			$(window).on('scroll', function() {
				var _index =getMin();
				var iH = $(window).scrollTop() + $(window).innerHeight(); 
				if (arrT[_index] + 50 < iH) {
					if(iBtn){
						getData();
						oContainer.css("height",arrT[_index] + dHeight + 50);
					}
				}
			})
			
			$(window).on('resize', function() {
				var dLen = dCells;
				setCell();
				if (dLen == dCells) {
					return ;
				}
				
				arrT = [];
				arrL = [];
				
				for (var i=0; i<dCells; i++) {      
					arrT[i] = 0;
					arrL[i] = dOuterWidth * i;
				}
				
				oContainer.children('div').each(function() {
					
					var _index = getMin();

					$(this).animate({
						left	:	arrL[_index],
						top		:	arrT[_index]
					}, 1000);                                               
					arrT[_index] += $(this).height() + 40;
					
				});				
				var _index =getMin();
				oContainer.css("height",arrT[_index] + dHeight+ 50 );

			});


			$("#category").click(function(){
				$(this).siblings("#category_ul").show();
			});
			$("#category_ul li ").click(function(){
				$("#category").html($(this).html());

				$("#category_ul").hide();

			});
				
			$("body").click(function(e) {
				if(e.target.tagName != "A") {
					$("#category_ul").hide();
				}
			});

			$("#size").click(function(){
				$(this).siblings("#size_ul").show();
			});
			
			$("#size_ul li ").click(function(){
				$("#size").html($(this).html());

				$("#size_ul").hide();

			});
				
			$("body").click(function(e) {
				if(e.target.tagName != "A") {
					$("#size_ul").hide();
				}
			
			});		

	 function publish(id,size_id,title){
        art.dialog.open("/Stylist/adInfos/id/"+id+"/size_id/"+size_id, {title:title,id:'publish',opacity:0.6,height:'80%',width:1000,fixed:true},false).lock();
    }
	function dialogs(title,content,time){
		var time = time || 2;
		var title = title || '提示信息';
		var content = content || '操作成功';
		art.dialog({
			title:title,
			drag:false,
			time:time,
			lock:true,
			content:content
		})	
	}
	
	//点赞特效
	function anp(e){
		var $i=$("<b>").text("+1");
		var x=e.pageX,y=e.pageY;
		$i.css({top:y-20,left:x,position:"absolute",color:"#E94F06"});
		$("body").append($i);
		$i.animate({top:y-180,opacity:0,"font-size":"3.0em"},1500,function(){
			$i.remove();
		});
		e.stopPropagation();
	}
		
	
});
		