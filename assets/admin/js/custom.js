$(function(){
	_modal();
	_validate();
	_info();
	_tooltip();
	$('[data-rel="tooltip"]').tooltip();
	
	var colorbox_params = {
		rel: 'colorbox',
		reposition:true,
		scalePhotos:true,
		scrolling:false,
		previous:'<i class="ace-icon fa fa-arrow-left"></i>',
		next:'<i class="ace-icon fa fa-arrow-right"></i>',
		close:'&times;',
		current:'{current} of {total}',
		maxWidth:'60%',
		maxHeight:'60%',
		onOpen:function(){
			$overflow = document.body.style.overflow;
			document.body.style.overflow = 'hidden';
		},
		onClosed:function(){
			document.body.style.overflow = $overflow;
		},
		onComplete:function(){
			$.colorbox.resize();
		}
	};

	
	$('[data-rel="colorbox"],[data-toggle="colorbox"]').colorbox(colorbox_params);
});

/*表單驗證*/
function _validate(){
	//覆寫說明訊息
	$.extend($.validator.messages,{
		required : "此欄位為必填",
		remote : "請修正此欄位",
		email : "電子郵件格式不正確",
		url : "請輸入正確的網址",
		date : "請輸入正確的日期",
		dateISO : "請輸入正確的日期 (ISO).",
		number : "請輸入數值",
		digits : "請輸入整數",
		creditcard : "請輸入正確的信用卡號碼",
		equalTo : "請再次輸入相同的密碼",
		maxlength : $.validator.format("最多只能輸入 {0} 個字"),
		minlength : $.validator.format("請至少輸入 {0} 個字"),
		rangelength : $.validator.format("輸入內容長度介於 {0} 到 {1} 之間"),
		range : $.validator.format("請輸入一個介於 {0} 到 {1} 之間的值"),
		max : $.validator.format("請輸入一個小於或等於 {0} 的值"),
		min : $.validator.format("請輸入一個大於或等於 {0} 的值")
	});
	$(".newoil-form").each(function(){
		$(this).validate({
			ignore: [],
			onkeyup: false,
			errorElement: "span",	//顯示錯誤訊息的方式
			//錯誤訊息樣式名稱
			errorClass : "label label-danger label-white middle help-block",
			// overwrite 為每個驗證對象填加錯誤訊息
			errorPlacement : function(error, element){
				element.parents(".form-group").addClass("has-error");
				if (element.parents(".input-group").size() > 0){
					element.parents(".input-group").after(error);
				}else{
					
					/*if(!$(element).is("select")){	//如果為下拉選單 和隱藏欄位 就不加入
						element.parent().append('<i class="ace-icon fa fa-exclamation-triangle"></i>');
					}*/
					
					element.parent().append(error);
				}
			},
			// overwrite 每一個驗證對象驗證失敗時
			highlight : function(element, errorClass, validClass){
				
				$(element).parents(".form-group").addClass("has-error");			
				$(element).parents(".form-group").find(".form-control-feedback").show();
			},
			// overwrite 每一個驗證對象驗證成功時
			unhighlight : function(element, errorClass, validClass){
				$(element).parents(".form-group").removeClass("has-error");
				//$(element).parents(".form-group").find(".form-control-feedback").hide();
				
			}
		});
	});

	$('[class*="has-"]').on("keyup input change", function(){
		$(this).removeClass("has-error").removeClass("has-error");
		//$(this).find(".form-control-feedback").remove();
		$(this).unbind("keyup input change");
	});
	
}


/*  modal
 *
 * 所需屬性說明
 * data-toggle 			= modal 			//不可改
 * data-target 			= #[modal's id]
 * data-action			= [action url]
 * data-action-function	= [action funciton()]
 * data-msg-title 		= [modal header]
 * data-msg-content 	= [modal content]
 *
 * */
function _modal(){
	$("*[data-toggle=modal]").each(function(){
		var _ele = $(this);
		var _modal_id = (_ele.attr("data-target").indexOf("#") == -1) ? "#" + _ele.attr("data-target") : _ele.attr("data-target");
		if ($(_modal_id).size() < 1){
			_ele.bind("click", function(){
				var _this = $(this);
				$(_modal_id).remove();
				var _modalContent = '<div class="modal fade" id="' + _modal_id.replace("#", "") + '" tabindex="-1" role="dialog" aria-hidden="true">';
				_modalContent += '<div class="modal-dialog" role="document">';
				_modalContent += '<div class="modal-content">';
				_modalContent += '<div class="modal-header">';
				_modalContent += '<button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>';
				_modalContent += '<h4 class="smaller modal-title"><i class="ace-icon fa fa-exclamation-triangle red"></i> 系統警告</h4>';
				_modalContent += '</div>';
				_modalContent += '<div class="modal-body"><div class="alert alert-danger bigger-110">執行刪除後無法回復，確認刪除?</div></div>';
				_modalContent += '<div class="modal-footer">';
				_modalContent += '<a class="btn btn-danger modal-action" href="#"><i class="ace-icon fa fa-trash-o bigger-110"></i>&nbsp; 確認刪除</a>';
				_modalContent += '<button type="button" class="btn btn-default" data-dismiss="modal"><i class="ace-icon fa fa-times bigger-110"></i>&nbsp; 取消</button>';
				_modalContent += '</div>';
				_modalContent += '</div>';
				_modalContent += '</div>';
				_modalContent += '</div>';
				$("body").append(_modalContent);
				
				if (_this.is("[data-msg-title]")){
					$(".modal-title").html(_this.attr("data-msg-title"));
				}
				if (_this.is("[data-msg-content]")){
					$(".modal-body").html(_this.attr("data-msg-content"));
				}
				if (_this.is("[data-action]")){
					$(".modal-action").attr("href", _this.attr("data-action"));
				}
				if (_this.is("[data-action-function]")){
					$(".modal-action").attr("onclick", _this.attr("data-action-function"));
				}
			});
		}
	});
}


/*  info
 *
 * 所需屬性說明
 * data-toggle 			= info 			//不可改
 * data-msg-title 		= [modal header]
 * data-msg-content 	= [modal content]
 * data-action			= [action url]
 *
 * */
function _info(){
	$("*[data-toggle=info]").each(function(){
		var _ele = $(this);
		_ele.bind("click", function(){
			var _this = $(this);
			$('#modal-info').remove();
			var _modalContent = '<div class="modal fade" id="modal-info" tabindex="-1" role="dialog" aria-hidden="true">';
			_modalContent += '<div class="modal-dialog" role="document">';
			_modalContent += '<div class="modal-content">';
			_modalContent += '<div class="modal-header">';
			_modalContent += '<button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>';
			_modalContent += '<h4 class="smaller"><i class="ace-icon fa fa-info blue"></i> <span class="modal-title">系統提示</span></h4>';
			_modalContent += '</div>';
			_modalContent += '<div class="modal-body"><div class="msg-content alert alert alert-info bigger-110">執行刪除後無法回復，確認刪除?</div></div>';
			_modalContent += '<div class="modal-footer">';
			//_modalContent += '<a class="btn btn-danger modal-action" href="#"><i class="ace-icon fa fa-trash-o bigger-110"></i>&nbsp; 確認刪除</a>';
			_modalContent += '<button type="button" class="btn btn-lg btn-white btn-info" data-dismiss="modal"><i class="ace-icon fa fa-check bigger-110"></i>&nbsp; 確定</button>';
			_modalContent += '</div>';
			_modalContent += '</div>';
			_modalContent += '</div>';
			_modalContent += '</div>';
			$("body").append(_modalContent);

			if (_this.is("[data-msg-title]")){
				$(".modal-title").html(_this.attr("data-msg-title"));
			}
			if (_this.is("[data-msg-content]")){
				var str=_this.attr("data-msg-content").replace(/\n|\r/g, '<br />');
				$(".msg-content").html(str);
			}
			if (_this.is("[data-action]")){
				//$(".modal-action").attr("href", _this.attr("data-action"));
				$("#modal-info").on('hidden.bs.modal', function () {
					location.href=_this.attr("data-action");
				});
			}
			if (_this.is("[data-action-function]")){
				$(".modal-action").attr("onclick", _this.attr("data-action-function"));
			}
			$('#modal-info').modal('show');
		});
	});
}

//tooltip
function _tooltip(){
	$("*[data-toggle=tooltip]").each(function(){
		$(this).tooltip({
			show: null,
			position: {
				my: "left top",
				at: "left bottom"
			},
			open: function( event, ui ) {
				ui.tooltip.animate({ top: ui.tooltip.position().top + 10 }, "fast" );
			}
		});
	});
}

function modalMsg(msg,href){
	href = href || '';
	$('#modal-msg').remove();
	var _modalContent = '<div class="modal fade" id="modal-msg" tabindex="-1" role="dialog" aria-hidden="true">';
	_modalContent += '<div class="modal-dialog" role="document">';
	_modalContent += '<div class="modal-content">';
	_modalContent += '<div class="modal-header">';
	_modalContent += '<button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>';
	_modalContent += '<h4 class="smaller"><i class="ace-icon fa fa-info blue modal-title"></i> 系統提示</h4>';
	_modalContent += '</div>';
	_modalContent += '<div class="modal-body"><div class="msg-content alert alert alert-info bigger-110">'+ msg +'</div></div>';
	_modalContent += '<div class="modal-footer">';
	//_modalContent += '<a class="btn btn-danger modal-action" href="#"><i class="ace-icon fa fa-trash-o bigger-110"></i>&nbsp; 確認刪除</a>';
	_modalContent += '<button type="button" class="btn btn-lg btn-white btn-info" data-dismiss="modal"><i class="ace-icon fa fa-check bigger-110"></i>&nbsp; 確定</button>';
	_modalContent += '</div>';
	_modalContent += '</div>';
	_modalContent += '</div>';
	_modalContent += '</div>';
	$("body").append(_modalContent);
	
	
	if(href!=''){
		$('#modal-msg').on('hidden.bs.modal', function (e) {	//關閉視窗促發事件
			location.href=href;
			//console.log('modal closed');
		});
	}
	$('#modal-msg').modal({ keyboard: false ,backdrop: 'static'});
	$('#modal-msg').modal('show');
	
}
