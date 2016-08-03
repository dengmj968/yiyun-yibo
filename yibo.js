var myServer = "http://yibo.iyiyun.com";// 益播域名
var RandCC =Math.round(Math.random()*10000);
if ("undefined" != typeof sizeid){
    document.writeln("<script src=\"" + myServer + "/Home/Distribute/mapAdBySizeId?randomCC="+RandCC+"&size_id=" + sizeid + "\" type=\"text/javascript\" charset=\"utf-8\"></script>");
}else if ("undefined" != typeof ad_id) { // 单一显示固定广告入口
    document.writeln("<script src=\"" + myServer + "/Home/Distribute/simple?randomCC="+RandCC+"&ad_id=" + ad_id + "\" type=\"text/javascript\" charset=\"utf-8\"></script>");
} else { // 随机匹配显示广告入口
    document.writeln("<script src=\"" + myServer + "/Home/Distribute/index?placeId=" + yibo_id + "\" type=\"text/javascript\" charset=\"utf-8\"></script>");
}

