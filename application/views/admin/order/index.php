
<script type="text/javascript">
	function checkPay(order_no){
		$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
		$.ajax({
			type: "POST",
			url: CI_URL + '/module/Checkpay/ajaxCheck',
			cache: false,
			dataType:"json",
			data: {order_no: order_no}
		}).done(function( msg ) {  
			$.unblockUI();
			if(msg.RntCode=='Y'){
				modalMsg(msg.Msg,location.href);
			}else{
				modalMsg(msg.Msg);
			}
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
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">訂單編號</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find1" name="find1" value="<?php echo @$_REQUEST["find1"]?>" placeholder="訂單編號" />
                                      <?php if (@$_REQUEST["find1"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."訂單編號=【".@$_REQUEST["find1"]."】";} ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">收款情況</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="find2" id="find2" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <?php echo inSelectOption2($orderKeyin2,@$_REQUEST["find2"])?>
                                      </select>
                                      <?php if (@$_REQUEST["find2"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."收款情況=【".returnKeyin2(@$_REQUEST["find2"])."】";} ?>
                                    </div>
                                </div>   
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">繳費方式</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <select class="form-control select2" name="find3" id="find3" data-placeholder="請選擇">
                                        <option value="">請選擇</option>
                                        <?php echo inSelectOption($paymentType,@$_REQUEST["find3"])?>
                                      </select>
                                      <?php if (@$_REQUEST["find3"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."繳費方式=【".inNumberString($paymentType,@$_REQUEST["find3"])."】";} ?>
                                    </div>
                                </div>                                 <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">繳費代碼</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find9" name="find9" value="<?php echo @$_REQUEST["find9"]?>" placeholder="繳費帳號" />
                                      <?php if (@$_REQUEST["find9"]!=""){$find_msg.=($find_msg!="" ? "、" : "");} ?>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">會員帳號</label>
                                    <div class="col-xs-12 col-sm-5">
                                      <input type="text" class="form-control" id="find4" name="find4" value="<?php echo @$_REQUEST["find4"]?>" placeholder="會員帳號" />
                                      <?php if (@$_REQUEST["find4"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."會員帳號=【".@$_REQUEST["find4"]."】";} ?>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label for="inputWarning" class="col-xs-12 col-sm-3 control-label no-padding-right">日期區間</label>
                                    <div class="col-xs-12 col-sm-5">
                                        <div class="input-daterange  input-group">
                                            <input type="text" class="input-sm form-control jqdatetime" name="find7" id="find7" value="<?php echo @$_REQUEST["find7"]?>" />
                                            <span class="input-group-addon">
                                                <i class="fa fa-exchange"></i>
                                            </span>
                                            <input type="text" class="input-sm form-control jqdatetime" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>"  />
                                            <?php if (@$_REQUEST["find7"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."日期區間-起=【".@$_REQUEST["find7"]."】";} ?> 
                                            <?php if (@$_REQUEST["find8"]!=""){$find_msg.=($find_msg!="" ? "、" : "")."日期區間-訖=【".@$_REQUEST["find8"]."】";} ?> 
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


<table id="simple-table" class="table table-bordered table-hover table-responsive">
    <thead>
        <?php if(isset($sumTotal)):?>
        <tr>
        	<th class="text-right" colspan="2">合計收款金額：</th>
            <th class="text-left text-danger" colspan="3"><?php echo '$'.number_format($sumTotal["TotalAmount"],0)?></th>
        </tr>
        <?php endif;?>
        <tr>
            <!--<th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>-->
            <th>代理</th>
            <th>訂單編號/日期</th>
            <th>儲值會員</th>
            <th>儲值方式/金額</th>
            <th>收款情況</th>
           
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
				<div class="text-info"><?php echo $row["order_no"]?></div>
                <div class="text-danger"><?php echo $row["buildtime"]?></div>
                
            </td>
            <td>
            	<a href="<?php echo  site_url(SYSTEM_URL."/Member/index?find2=".tb_sql("u_id","member",$row["mem_num"]))?>">
					<?php echo tb_sql("u_id","member",$row["mem_num"])?>
                </a>    
                <div class="text-danger"><?php echo tb_sql("u_name","member",$row["mem_num"])?></div>
			
            
            </td>
            <td>
                <div class="text-success"><?php echo inNumberString($paymentType,$row["payment"])?></div>
                <div class="text-info">
                	<?php 
					if($row["pay_mode"]==1){	//綠界
						echo $row["payment"]=='CVS' ? tb_sql2("PaymentNo","allpay_orders","order_no",$row["order_no"]) : tb_sql2("BankCode","allpay_orders","order_no",$row["order_no"]).'-'.tb_sql2("vAccount","allpay_orders","order_no",$row["order_no"]);
					}elseif($row["pay_mode"]==2){
						echo $row["payment"]=='CVS' ? tb_sql2("CodeNo","sp_orders","order_no",$row["order_no"]) : tb_sql2("BankCode","sp_orders","order_no",$row["order_no"]).'-'.tb_sql2("CodeNo","sp_orders","order_no",$row["order_no"]);
					}else{
                        echo $row["payment"]=='ATM' ? tb_sql2("type","payment_order","order_no",$row["order_no"]).'-': '';
                        echo tb_sql2("code","payment_order","order_no",$row["order_no"]);
                    }
					?>
                </div>
                <div class="text-danger"><?php echo '$'.number_format($row["amount"],0)?></div>
            </td>
            <td>
                <div class="text-<?php echo ($row['keyin2']==1 ? 'success' : 'danger')?>">
					<?php echo returnKeyin2($row['keyin2'])?>
					
                    
                </div>
				<?php if($row["ATMAccBank"]!=NULL):?>
                <div class="purple">
                    銀行後五碼：<?php echo $row["ATMAccNo"]?>
                </div>
                <?php endif;?>
                <?php if($row["PayFrom"]!=NULL):?>
                <div class="purple">
                    繳費方式：<?php echo @$PayFrom[$row["PayFrom"]]?>
                </div>
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




