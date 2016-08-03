function isNumbers(num){
    var reNum =/^\d*$/;
    return (reNum.test(num));
}

/**
 * 控制网站信息是否展示
 * @param flag 1/2 展示/不展示
 */
function web_show(flag){
	if(flag==1){
		$("#zhandian_info").show();
	}else{
		$("#zhandian_info").hide();
		//清空信息,并更新
		$("#web_host").val("");
		$("#web_name").val("");
		updateConfigs();
	}
}

/**
 * height ------检测高度 是否是数字? 成功则对应高度更换右侧div
 */
function check_height(o){
	var num = o.value;
	var l=281 , h=456;
	if(!isNumbers(num)){
		$(o).removeClass("input_default");
		$(o).addClass("input_error");
		alert("高度请输入数字!"); 
		return false;
	}
	if(num<l || num>h){
		$(o).addClass("input_error");
		$(o).removeClass("input_default");
		alert("高度数字在281~456之间");
		return false;
	}
	$(o).removeClass("input_error");
	$(o).addClass("input_default");
	change_right(num); // 更改高度
	updateConfigs();
	return true;
}

/**
 * 当输入时的事件
 * @returns
 */
function  check_height_press(o){
	if(o.value.length<3){
		return '';
	}
	check_height(o);
}


/**
 * 根据高度不同更改右侧的div 内容
 * @param h
 */
function change_right(h){

	var h_footer = 74,h_header = 101,h_content = 280; 
	var content = 0;// 设置最终内容高度
	
	if(h >= (h_footer+h_header+h_content)){ //头部+底部+内容。
		content = h_content-40;
		$(".bottom_link").show();
		$(".main_center").show();
	
	}else if(h >= (h_content+h_header) ){ //内容 +头部
		content = h - h_header-40;
		$(".bottom_link").hide();
		$(".main_center").show();		
	
	}else if(h >= (h_content+h_footer)){ //内容+底部
		content = h - h_footer-40;
		$(".bottom_link").show();
		$(".main_center").hide();		
	
	}else if(h >= h_content){ // 内容
		$(".bottom_link").hide();
		$(".main_center").hide();		
		content = h-40;
	}else{
		console.error("h must be more 265");
	}
	$(".right").css("height",h);
	$(".infor_detail").css("height",content+"px");		
	// 更改ifream code todo
	//var wall_h = parseInt(h)+4;
	//var b_code = "<iframe scrolling='no' frameborder='0' src='http://yibo.iyiyun.com/new404/show/key/"+ $("#insert_id").val()+"'  width='640' height='"+wall_h+"' style='display:block;'></iframe>";
	//$("#code").val(b_code);
}



/**
 *  切换样式
 */
function tab_color(o,type){
	// 标记当前已选中	
	$(".choise_color").hide();
	$(o).next().show();
	$("#colors").val(type);
	var color = type;
	var project_id = $("#project_id").val();
	getTemplate(project_id,color);
	//更换具体样式 
/* 	var css_head = "head_"+type;
	var css_center = "center_"+type;
	var css_foot = "foot_"+type;
	$(".main_top").attr("class","main_center "+css_head);
	$(".main_center").attr("class","main_center "+css_head);
	$(".infor_detail ").attr("class","infor_detail  "+css_center);
	$(".bottom_link").attr("class","bottom_link "+css_foot); */
	updateConfigs();
}

/**
 * 当前内容的修改配置
 */
function updateConfigs(){
		var projectId = [];
		$("input[name='projectId']").each(function(){
			if(this.checked){
				projectId.push(this.value);
			}
		})
			var is_push = $("input[name='is_push']:checked").val();
			var listId = projectId.join();
			var web_name = $("#web_name").val();
			$.ajax({
				type:'post',
				url:'/Place404/editConfs',
				cache:false,
				data:{id:$("#insert_id").val(),pid:$('#pid').val(),web_name:web_name,colors:$("#colors").val(),mid:listId,is_push:is_push},
				dataType:'json',
				success:function(data){
					return true;
					//$("#codes").val(data.data.codes);
/* 					var detailInfo = data.data.projects.info;
					var t_id = data.data.projects.template_id;
					var t_img = data.data.projects.img;
					var t_url = data.data.projects.url;
					var t_project_desc = data.data.projectInfo.desc;
					var t_project_name = data.data.projectInfo.name;
					var htmls = '';
					$.each(detailInfo,function(key,val){
						htmls += '<div>'+key+'<span>：'+val+'</span></div>';
					})
					//htmls += '</div>';
					$("#main_"+t_id+"_cen_desc").empty().html(htmls);
					$("#main_"+t_id+"_cen_img").attr('src',t_img);
					$("#main_"+t_id+"_cen_url").attr('href',t_url);
					$("#main_"+t_id+'_project_desc').text(t_project_desc);
					$("#main_title_"+t_id).text(t_project_name); */
				}
			})


}



$(function(){
	$('#copyBtn').zclip({
	    path: "/Public/js/zclip/ZeroClipboard.swf",
	    copy: function(){
	        return $('#code').val();
		},
		afterCopy: function () { alert("复制成功!"); }
	});
})

//调用模板
function getModels(obj){
	if(obj.checked){
		var color = $("#colors").val();
		var project_id = obj.value;
		var is_show_color = obj.getAttribute('show_color');
		if(parseInt(is_show_color) == 1){
			$("#color_page_style").css({'height':'120px','display':'block'});
		}else{
			$("#color_page_style").css({'height':'120px','display':'none'});
		}
		$("#project_id").val(project_id)
		getTemplate(project_id,color)
	}
	updateConfigs();
}
function getTemplate(project_id,color){
			$.ajax({
				url:'/Home/Index/getTemplate',
				type:'post',
				data:{project_id:project_id,color:color},
				dataType:'html',
				success:function(data){
					$("#templates").empty().append(data);
				}
			})
	}

