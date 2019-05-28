$(function(){
	var configs={
		language: 'zh-TW',
		autoclose: true,
		keyboardNavigation: false,
		clearBtn: true,
		//todayBtn: "linked",
		orientation: "bottom right",
		todayHighlight: true
	};
	$('.jqdate').css('cursor','pointer');
	//$('.jqdate').attr('readonly','readonly');
	//$('.jqdate input').attr('readonly','readonly');
	$('.jqdate input').css('cursor','pointer');
	$('.jqdate').datepicker(configs);	
	//$('.input-daterange').datepicker(configs);
});