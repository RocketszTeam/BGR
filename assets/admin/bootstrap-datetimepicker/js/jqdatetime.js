$(function(){
	
	
	var configs={
		language: 'zh-TW',
		autoclose: true,
		keyboardNavigation: false,
		//todayBtn: "linked",
		format: 'yyyy-mm-dd hh:ii:ss',
		minuteStep: 1,
		clearBtn: true,
		todayHighlight: true
	};
	$('.jqdatetime').css('cursor','pointer');
	
	//$('.jqdatetime').datetimepicker(configs);
	
	/*
	$('.jqdatetime').on('click',function(){
		laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})
	})
	*/
	
	
	$('.jqdatetime').each(function(){
		laydate.render({
		  elem: this,
		  type: 'datetime',
		  istime: true	
		});	
	});
	
});