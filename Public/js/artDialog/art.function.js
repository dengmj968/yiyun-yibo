var title = "ÌבÊ¾";
var time = 2;
function showSuccess(content){
	art.dialog({
		titie: title,
		icon: 'succeed',
		time: time,
		content: content,
		disabled: true,
		lock: true
	});
}
var showWarning = function(content){
	art.dialog({
		titie: title,
		icon: 'warning',
		time: time,
		content: content,
		disabled: true,
		lock: true
	});
}
var showError = function(content){
	art.dialog({
		titie: title,
		icon: 'error',
		time: time,
		content: content,
		disabled: true,
		lock: true
	});
}