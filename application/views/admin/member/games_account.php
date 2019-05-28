<script type="text/JavaScript">
	var pass_reg=/^(?!([^a-zA-Z]+|\D+)$)[a-zA-Z0-9]{6,13}$/;	//6~13英文數字混合
	$(function(){
		
		var balance_length=$('.balance_span').length;
		
		for(i=0;i < balance_length;i++){
			var mekers_num=$('.balance_span').eq(i).attr('data-makersnum');
			var mem_num=$('.balance_span').eq(i).attr('data-memnum');
			ajax_balance(mem_num,mekers_num);
		}
		
		
		//
		
		jQuery.validator.addMethod("PasswordFormat", function( value, element ) {
		  var str = value;
		  var result = false;
		  return (pass_reg.test(value));
		}, '密碼請輸入6~13碼英文數字混合');	
	   $('#u_password').addClass('PasswordFormat');
		
		$('.refresh-btn').click(function(){
			var balanceSpan=$(this).parent().find('.balance_span');
			var mekers_num=$(balanceSpan).attr('data-makersnum');
			var mem_num=$(balanceSpan).attr('data-memnum');
			ajax_balance(mem_num,mekers_num);
		});
		
		$('.show_modal').click(function(){
			$('#alrtMsg').hide();
			$(".DelVal").prop('value','');
			$('#num').attr('value',$(this).attr('data-num'));
			$('#show_makers').html($(this).attr('data-makers'));
			$('#show_id').html($(this).attr('data-id'));
			$('#member_sms').modal({ keyboard: false ,backdrop: 'static'});
			$('#member_sms').modal('show');
		});
		
		
		$('#member_sms').on('hidden.bs.modal', function (e) {	//關閉視窗促發事件
			location.href=location.href;
			//console.log('modal closed');
		});
		
		$('#smsBTN').click(function(){
			if($('#pointsForm').valid()){
				$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
				$.ajax({
					type: "POST",
					url: CI_URL + "<?php echo SYSTEM_URL?>/Member/ajaxGameAccount",
					cache:false,
					async:false,
					dataType:"json",
					data:{
						num : $('#num').val(),
						u_password : $('#u_password').val(),
						u_password2 : $('#u_password2').val()	
					}
				}).done(function( htmlData ) {  
					$.unblockUI();
					$('#alrtTitle').text(htmlData.title);
					$('#alertBody').text(htmlData.Msg);
					if(htmlData.RntCode==1){
						$('#alrtMsg').removeClass('alert-danger').addClass('alert-success');
						$(".DelVal").prop('value','');						
					}else{
						$('#alrtMsg').removeClass('alert-success').addClass('alert-danger');
					}
					$('#alrtMsg').show();
				});
			}
		});
		
		
		
		
	});

	function ajax_balance(mem_num,makersnum){
		var balanceSpan=$('[data-makersnum=' + makersnum + ']');
		var icon=$(balanceSpan).parent().find('.fa-refresh');
		$(icon).addClass('fa-spin');
		balanceSpan.html('<i class="ace-icon fa fa-spinner fa-spin red"></i>');
		$.ajax({
			type: "POST",
			url:  CI_URL + "<?php echo SYSTEM_URL?>/Member/ajax_balance",
			cache: false,
			dataType:"json",
			data:{'mem_num':mem_num,'makersnum':makersnum}
		}).done(function( msg ) { 
			$(icon).removeClass('fa-spin');
			$(balanceSpan).html(msg.balance);
		});
	}
	
	function refreshall(){
		$('.refresh-btn').click();
	}
</script>
<div class="page-header">
	<h4 class="inline text-danger"><?php echo tb_sql("u_name","member",$mem_num)?>【<?php echo tb_sql("u_id","member",$mem_num)?>】</h4>
	<a href="javascript:refreshall()" class="btn btn-primary btn-sm">一鍵查詢遊戲點數</a>
	<a href="<?php echo site_url(SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$att) ?>"  class="btn btn-info btn-sm">返回會員</a>
</div>

<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            <th class="text-center">遊戲廠商</th>
            <th class="text-center">遊戲餘額</th>
            <th class="text-center">遊戲帳號</th>
            <th class="text-center">遊戲密碼</th>
            
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
        	<td class="text-center"><?php echo tb_sql("makers_name","game_makers",$row["gamemaker_num"])?></td>
            <td class="text-center">
            	
            	<div style="width:85px;" class="text-success balance_span inline" data-makersnum="<?php echo $row["gamemaker_num"]?>" data-memnum="<?php echo $row["mem_num"]?>"></div>
                <button class="refresh-btn btn btn-xs btn-white btn-primary" data-rel="tooltip" title="更新餘額"><i class="ace-icon fa fa-refresh"></i></button>  
               
            </td>
            <td class="text-center"><?php echo $row["u_id"]?></td>
            <td class="text-center">
				<?php echo $row["u_password"]?>
                <?php if($row["u_password"]!=NULL):?>
                <button class="btn btn-xs btn-white btn-primary show_modal" data-rel="tooltip" title="修改密碼" data-id="<?php echo $row["u_id"]?>" data-num="<?php echo $row["num"]?>" data-makers="<?php echo tb_sql("makers_name","game_makers",$row["gamemaker_num"])?>">
                	<i class="ace-icon fa fa-pencil"></i>
                </button>
                <?php endif;?>
            </td>
        </tr>
        <?php endforeach;
		endif;
		?>
    </tbody>
</table> 

<div class="text-center">
	<?php echo @$pagination ?>
    <!--<ul class="pagination">
        <li><a href="#"><i class="ace-icon fa fa-angle-double-left"></i></a></li>
        <li><a href="#">1</a></li>
        <li class="active"><a href="#">2</a></li>
        <li><a href="#"><i class="ace-icon fa fa-angle-double-right"></i></a></li>
    </ul>-->
</div>


<!--修改視窗 -->
<div class="modal fade  in" tabindex="-1" role="dialog" id="member_sms">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>-->
                <h4 class='smaller'>修改遊戲密碼</h4>
            </div>
            <div class="modal-body">
            	<div class="alert alert-danger fade in" id="alrtMsg" style="display:none;">
            		<strong id="alrtTitle">請求失敗！</strong>&nbsp;&nbsp;<span id="alertBody">XXXX</span>
            	</div>
            	<form class="form-horizontal newoil-form" id="pointsForm" method="post">
                    <input type="hidden" name="num" id="num"  />
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">遊戲廠商</label>
                        <div class="col-xs-12 col-sm-10">
                          <p class="form-control-static" id="show_makers"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">遊戲帳號</label>
                        <div class="col-xs-12 col-sm-10">
                          <p class="form-control-static" id="show_id"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">遊戲密碼</label>
                        <div class="col-xs-12 col-sm-10">
                        	<input name="u_password" id="u_password" type="password" class="form-control DelVal" placeholder="遊戲密碼" required  />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">確認密碼</label>
                        <div class="col-xs-12 col-sm-10">
                        	<input name="u_password2" id="u_password2" type="password" equalTo="#u_password" class="form-control DelVal" placeholder="確認密碼" required  />
                        </div>
                    </div>
                </form>   
            </div>
            <div class="modal-footer center">
            	<button type="button" id="smsBTN" class="btn btn-lg btn-white btn-pink btn-round"><i class="ace-icon fa fa-check bigger-110"></i>&nbsp;送出</button>
                <button data-dismiss="modal" type="button"  class=" btn-white btn-lg btn btn-default btn-round">
                    <i class='ace-icon fa fa-times bigger-110'></i>&nbsp; 關閉
                </button>
            </div>
       </div>
   </div>
</div>        
<!--修改視窗 -->
