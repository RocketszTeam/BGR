<script type="text/JavaScript">
	$(function(){
		$('#smsBTN').click(function(){
			if($('#pointsForm').valid()){
				$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
				$.ajax({
					type: "POST",
					url: CI_URL + "<?php echo SYSTEM_URL?>/Member/ajaxWallet",
					cache: false,
					async:false,
					dataType:"json",
					data: { 
							point_type : $('#point_type').val(),
							mem_num : $('#mem_num').val(),
							kind : $('#kind').val(),
							points : $('#points').val(),
							word : $('#word').val(),
							token: '<?php echo $token?>',
							ttt: $('select[name="kind"] :selected').attr('kind1'),//取得目前由"管理者輸入"多少點	
							tty: $('select[name="kind"] :selected').attr('kind1_point'),//取得"管理者輸入"預設點數
							ttp: $('select[name="kind"] :selected').attr('active'),//取得是否啟動.
						  }
				}).done(function( htmlData ) {  
					$.unblockUI();
					$('#alrtTitle').text(htmlData.title);
					$('#alertBody').text(htmlData.Msg)
					if(htmlData.RntCode==1){
						$('#alrtMsg').removeClass('alert-danger').addClass('alert-success');
						$(".DelVal").prop('value','');						
					}else{
						$('#alrtMsg').removeClass('alert-success').addClass('alert-danger');
					}
					$('#show_blance').html(htmlData.blance);
					$('#alrtMsg').show();
					//$('.selectpicker').selectpicker('refresh');//重新特效抓取
				});
			}
		});
		
		$('#member_sms').on('hidden.bs.modal', function (e) {	//關閉視窗促發事件
			location.href=location.href;
			//console.log('modal closed');
		});
		
		$('#show_modal').click(function(){
			$('#alrtMsg').hide();
			$(".DelVal").prop('value','');
			$('#member_sms').modal({ keyboard: false ,backdrop: 'static'});
			$('#member_sms').modal('show');
		});
		
	});
	
	function AjaxKind(){
		$.ajax({
			type: "POST",
			url: CI_URL + "<?php echo SYSTEM_URL?>/Member/ajaxWalletKind",
			cache: false,
			data: { 
					point_type: $('#point_type').val(),
					mem_num:<?php echo $mem_num?>,
				  }
		}).done(function( htmlData ) {  
			$('#kind').html(htmlData);
			//$('.selectpicker').selectpicker('refresh');//重新特效抓取
		});
	}
	
</script>
<!-- PAGE CONTENT BEGINS -->
<?php if($openFind=='Y'):	//啟用搜尋才顯示?>
<div class="page-header">
    <form class="form-horizontal" method="post" action="<?php echo $s_action ?>">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
            	<div id="accordion" class="accordion-style1 panel-group">
                	<div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title ">
                                <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                   <i class="ace-icon fa fa-angle-down bigger-110" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i>
                                    &nbsp;篩選條件
                                </a>
                            </h4>
                        </div>
                        <div class="panel-collapse collapse" id="collapseOne">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">類型</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="sfind1" id="sfind1" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <?php echo inSelectOption($searchOption,@$_REQUEST["sfind1"])?>
                                      </select>
                                      <?php if (@$_REQUEST["sfind1"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."類型=【".inNumberString($searchOption,@$_REQUEST["sfind1"])."】";} ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">代理</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="sfind3" id="find3" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <?php if(isset($upAccount)):?>
                                        <?php foreach($upAccount as $row):?>
                                        <option value="<?php echo $row["num"]?>" <?php if($row["num"]==@$_REQUEST["sfind3"]) echo ' selected'?>><?php echo $row["u_id"]?>(<?php echo $row["u_name"]?>)</option>
                                        <?php endforeach;?>
                                        <?php endif;?>		
                                      </select>
                                      <?php if (@$_REQUEST["sfind3"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."代理=【".tb_sql("u_name","admin",@$_REQUEST["sfind3"])."】";} ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">帳號</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="sfind2" name="sfind2" value="<?php echo @$_REQUEST["sfind2"]?>" placeholder="帳號" />
                                      <?php if (@$_REQUEST["sfind2"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."帳號=【".@$_REQUEST["sfind2"]."】";} ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">發放日期</label>
                                    <div class="col-xs-12 col-sm-5">
                                        <div class="input-daterange  input-group">
                                            <input type="text" class="input-sm form-control jqdate" name="sfind7" id="sfind7" value="<?php echo @$_REQUEST["sfind7"]?>" />
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
                                            <input type="text" class="input-sm form-control jqdate" name="sfind8" id="sfind8" value="<?php echo @$_REQUEST["sfind8"]?>"  />
                                            <?php if (@$_REQUEST["sfind7"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."發放日期-起=【".@$_REQUEST["sfind7"]."】";} ?> 
                                            <?php if (@$_REQUEST["sfind8"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."發放日期-訖=【".@$_REQUEST["sfind8"]."】";} ?> 
                                        </div>
                                    </div>
                                </div>   
                                <div class="text-center">
                                    <a href="<?php echo site_url(uri_string()) ?>" class="btn btn-yellow btn-sm">
                                        <span class="ace-icon fa fa-times icon-on-right bigger-110"></span>
                                        清除篩選
                                    </a>
                                    <button type="submit" class="btn btn-purple btn-sm">
                                        <span class="ace-icon fa fa-search icon-on-right bigger-110"></span>
                                        條件篩選
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
    if(@$find_msg!=""){	//列出搜尋條件
		$find_arr=explode('、',$find_msg);
	?>
    <div class="widget-box widget-color-green">
        <div class="widget-body">
            <div class="widget-main padding-8">
                <ul class="list-unstyled spaced">
                    <?php foreach($find_arr as $find_str):?>
                    <li><i class="ace-icon fa fa-search bigger-110 red"></i><?php echo $find_str?></li>
                    <?php endforeach;?>
                </ul>
            </div>
        </div>    
    </div>
    
    <?php
	}
	?>
</div>

<?php endif;?>
<?php if($returnBtn && $walletBtn):?>
<div class="row">
    <div class="col-xs-12 col-sm-5">
    	<div class="form-group">
            <div class="btn-group">
                <button type="button" class="btn  btn-lg btn-info" onClick="location.href='<?php echo site_url(SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$att) ?>'">返回會員</button>
                <button type="button" class="btn  btn-lg btn-danger" id="show_modal">點數管理</button>
            </div>
        </div>
        <h4 class="inline text-danger">
			<?php echo tb_sql("u_name","member",$mem_num)?>【<?php echo tb_sql("u_id","member",$mem_num)?>】
            ，錢包總點數【<?php echo number_format(getWalletTotal($mem_num),2,'.',',')?>】點
        </h4>
    </div>
</div>
<?php endif;?>

<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            <th>代理</th>
            <th>會員</th>
            <th>類型</th>
            <th>轉入/轉出</th>
            <th>備註</th>
            <th>異動管理者 / 日期</th>
           
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
        	<td>
            	<?php echo tb_sql("u_id","admin",$row["admin_num"])?>
                <div class="text-danger"><?php echo tb_sql("u_name","admin",$row["admin_num"])?></div> 
            </td>
            <td>
            	<?php echo tb_sql("u_id","member",$row["mem_num"])?>
                <div class="text-danger"><?php echo tb_sql("u_name","member",$row["mem_num"])?></div> 
            </td>
            <td>
            	<div><?php echo tb_sql("kind","wallet_kind",$row["kind"])?></div>
                <?php if($row['kind'] == 3 || $row['kind']==4):	//轉出轉入遊戲?>
                <div class="text-danger">
                	<?php echo ($row['kind']==3 ?'轉入' :'轉出').tb_sql("makers_name","game_makers",$row['makers_num'])?>
                </div>
                <span class="purple">剩餘點數：<?php echo number_format($row["makers_balance"],2,'.',',')?></span>
                <?php endif;?>
			    <?php if($row['kind']==5):	//會員儲值?> 
                <div class="text-info">
                    訂單編號：<?php echo $row["order_no"];?><br />
                    儲值方式：<?php echo (tb_sql2("payment","orders","order_no",$row["order_no"])=='CVS' ? '超商代碼' : 'ATM');?>
                    <?php if(tb_sql2("payment","orders","order_no",$row["order_no"])=='ATM'):?>
                    ，繳費後五碼：<?php echo tb_sql2("ATMAccNo","orders","order_no",$row["order_no"])?>
                    <?php endif;?>
                </div>    
                <?php endif;?>
			    <?php if($row['kind']==12):	//銀行匯款?> 
                <div class="text-info">
                    訂單編號：<?php echo $row["order_no"];?>
                </div>    
                <?php endif;?>
            </td>
            <td>
				<?php
                    $saveClass='';
                    if($row["points"] < 0){
                        $saveClass='text-danger';
                    }elseif($row["points"] > 0){
                        $saveClass='text-success';
                    }
                ?>
                <div class="<?php echo $saveClass?>"><?php echo number_format($row["points"],2,'.',',')?></div>
                <div class="pink">異動前：<?php echo number_format($row["before_balance"],2,'.',',')?></div>
                <div class="purple">異動後：<?php echo number_format($row["after_balance"],2,'.',',')?></div>
            </td>
            <td>
				<?php if($row['word']) echo br($row['word']);else echo '-';?>
            </td>
            <td>
            	<div class="text-info">
                	<?php echo ($row['update_num']!=0?'['.tb_sql("u_id","admin",$row['update_num']).']':'[系統]')?>
                </div>
                <div class="text-danger">
                	<?php echo $row["buildtime"]?>
                </div>
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

<!--會員訊息 -->
<div class="modal fade  in" tabindex="-1" role="dialog" id="member_sms">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>-->
                <h4 class='smaller'><i class='ace-icon fa fa-credit-card red'></i> 點數管理</h4>
            </div>
            <div class="modal-body">
            	<div class="alert alert-danger fade in" id="alrtMsg" style="display:none;">
            		<strong id="alrtTitle">請求失敗！</strong>&nbsp;&nbsp;<span id="alertBody">XXXX</span>
            	</div>
            	<form class="form-horizontal newoil-form" id="pointsForm" method="post">
                    <input type="hidden" name="mem_num" id="mem_num" value="<?php echo @$mem_num?>" />
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">會員</label>
                        <div class="col-xs-12 col-sm-10">
                          <p class="form-control-static" id="show_id"><?php echo tb_sql("u_id","member",$mem_num);?></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">點數</label>
                        <div class="col-xs-12 col-sm-10">
                          <p class="form-control-static" id="show_blance"><?php echo getWalletTotal($mem_num)?></p>
                        </div>
                    </div>
                    <div class="form-group">
                    	<label for="inputWarning" class="col-xs-12 col-sm-2 control-label">動作</label>
                        <div class="col-xs-12 col-sm-10">
                        	<select class="form-control DelVal" name="point_type" id="point_type" data-placeholder="請選擇" onchange="AjaxKind()" required>
                            	<option value="">請選擇</option>
                            	<option value="1">放點</option>
                                <option value="2">扣點</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                    	<label for="inputWarning" class="col-xs-12 col-sm-2 control-label">類型</label>
                        <div class="col-xs-12 col-sm-10">
                        	<select class="form-control DelVal" name="kind" id="kind" data-placeholder="請選擇" required>
                            	<option value="">請選擇</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">點數</label>
                        <div class="col-xs-12 col-sm-10">
                        	<input name="points" id="points" class="form-control DelVal number" placeholder="點數" required  />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">事由</label>
                        <div class="col-xs-12 col-sm-10">
                        	<textarea id="word" name="word" class="form-control limited autosize-transition DelVal" maxlength="70" placeholder="內容"></textarea>
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
<!--會員訊息 -->


