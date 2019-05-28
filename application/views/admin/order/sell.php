<script type="text/javascript">
	$(function(){
		$('#feeBTN').click(function(){
			if($('#feeForm').valid()){
				$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
				$.ajax({
					type: "POST",
					url: CI_URL + "<?php echo SYSTEM_URL?>/Order/ajaxFee",
					cache: false,
					async:false,
					dataType:"json",
					data: { 
							order_no : $('#order_no').val(),
							fee : $('#fee').val()
						  }
				}).done(function( htmlData ) {  
					$.unblockUI();
					$('#alrtTitle').text(htmlData.title);
					$('#alertBody').text(htmlData.Msg)
					if(htmlData.RntCode==1){
						$('#alrtMsg').removeClass('alert-danger').addClass('alert-success');
						//$(".DelVal").prop('value','');						
					}else{
						$('#alrtMsg').removeClass('alert-success').addClass('alert-danger');
					}
					$('#alrtMsg').show();
					//$('.selectpicker').selectpicker('refresh');//重新特效抓取
				});
			}
		});
		
		
		
		$('#fee_modal').on('hidden.bs.modal', function (e) {	//關閉視窗促發事件
			location.href=location.href;
			//console.log('modal closed');
		});
		
		$('.show_fee').click(function(){
			$('#alrtMsg').hide();
			$(".DelVal").prop('value','');
			$('#show_order_no').html($(this).attr('data-orderno'));
			$('#order_no').prop('value',$(this).attr('data-orderno'));
			$('#fee').prop('value',$(this).attr('data-fee'));
			$('#fee_modal').modal({ keyboard: false ,backdrop: 'static'});
			$('#fee_modal').modal('show');
		});
		
		
	});


	function keyChange(number,value){
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/order/keyChange',
			cache: false,
			data: { 
					order_no: number,
					value: value
				  }
		}).done(function( msg ) {  			
			$('#tr' + number).remove();
		});	
	}
	
	function toCloseSound(tb,column,value){
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/Order/ajaxCloseSound',
			cache: false,
			data: { 
					tb: tb,
					column: column,
					value: value
				  }
		}).done(function( msg ) {  			
			$('#soundBTN' + value).hide();
		});	
	}
	
	function showReport(orderDate,mem_num){
		$('#mem_num').val(mem_num);
		$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/Order/sellReport',
			cache: false,
			dataType:"json",
			data: { 
					orderDate: orderDate,
					mem_num: mem_num
				  }
		}).done(function( msg ) {
			console.log(msg);
			build_json(msg.data,msg.prefix);
			$.unblockUI();
			$('#report_info').modal('show');
		});	
	}
	
	
	
	function upDateReport(){
		$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/admin/images/loadingbar/009.gif" >',baseZ: 2000});
		$.ajax({
			type: "POST",
			url: CI_URL + '<?php echo SYSTEM_URL?>/Order/sellReport',
			cache: false,
			dataType:"json",
			data: { 
					sTime: $('#sDate').val(),
					eTime: $('#eDate').val(),
					mem_num: $('#mem_num').val()
				  }
		}).done(function( msg ) {  
			build_json(msg.data,msg.prefix);
			$.unblockUI();
			$('#report_info').modal('show');
		});	
	}
	
	
	function build_json(msg,tb){
		
		$('#report_info_header').html(msg.u_id +'(' + msg.u_name + ')');
		$('#sDate').val(msg.sDate);
		$('#eDate').val(msg.eDate);
		
		var tb_values = Object.values(tb);
		
		var array_keys = Object.keys(msg);
		var array_values = Object.values(msg);
		$('#report_body').html('');
		for(i=0;i < tb_values.length;i++){
			var tb_txt='<tr>';
			tb_txt+='<td class="text-center">'+ tb_values[i][1] +'</td>';
            tb_txt+='<td class="text-center formatNumber" id="' + tb_values[i][0] +'_betAmount"></td>';
			tb_txt+='<td class="text-center formatNumber" id="' + tb_values[i][0] +'_validAmount"></td>';
            tb_txt+='<td class="text-center number-info" id="' + tb_values[i][0] +'_winOrLoss"></td>';           
            tb_txt+='</tr>';          
			$('#report_body').append(tb_txt);
		}
		
				
		for(i=5;i < array_keys.length;i++){
			$('#' + array_keys[i]).html(array_values[i]);
			//console.log(tb[i]);	
		}

		
		number_info();
	}
	function number_info(){
		$('.number-info').each(function() {
		   var txt=parseFloat($(this).html());
		   //console.log(txt);
		   if(txt < 0){
			   $(this).removeClass('text-success').addClass('text-danger');
			   spanClass='';
		   }else if(txt > 0){
			  $(this).removeClass('text-danger').addClass('text-success');
		   }else{
			  $(this).removeClass('text-danger').removeClass('text-success'); 
		   }
		   $(this).html(formatNumber(txt));
        });
		
		$('.formatNumber').each(function() {
		   var txt=parseFloat($(this).html());
		   $(this).html(formatNumber(txt));
        });
		
	}
	
	function formatNumber(number){
		number = number.toFixed(2) + '';
		x = number.split('.');
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
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
<div class="tabbable">
    <ul class="nav nav-tabs" id="myTab">
        <li class="<?php echo (@$keyin1=='0' ? ' active' : '')?>">
            <a href="<?php echo site_url(SYSTEM_URL."/Order/sell/0")?>">
                等待處理
            </a>
        </li>
        <li class="<?php echo (@$keyin1=='1' ? ' active' : '')?>">
            <a  href="<?php echo site_url(SYSTEM_URL."/Order/sell/1")?>">
                處理完畢
            </a>
        </li>
        <li class="<?php echo (@$keyin1=='2' ? ' active' : '')?>">
            <a  href="<?php echo site_url(SYSTEM_URL."/Order/sell/2")?>">
                放棄轉點
            </a>
        </li>

        <li class="<?php echo (@$keyin1=='3' ? ' active' : '')?>">
            <a  href="<?php echo site_url(SYSTEM_URL."/Order/sell/3")?>">
                會員異常
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
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
                        <th>訂單編號/日期</th>
                        <th>出售會員</th>
                        <th>出售金額</th>
                        <th>手續費用</th>
                        <th>目的資訊</th>
                        <?php if(@$keyin1=='0'):?>
                        <th>處理情況</th>
                        <th>管理</th>
                        <?php else:?>
                        <th>處理人員</th>
                        <th>異動時間</th>
                        <?php endif;?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(isset($result)):
                        foreach($result as $row):
                    ?>
                    <tr id="tr<?php echo $row["order_no"]?>">
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
                            <div class="text-danger">
                            	<a href="javascript:void(0)" class="text-danger" onClick="showReport('<?php echo $row["buildtime"]?>','<?php echo $row["mem_num"]?>')"><?php echo tb_sql("u_name","member",$row["mem_num"])?></a>
                            </div>
                        </td>
                        <td><?php echo number_format($row["amount"],0)?></td>
                        <td>
                        	<?php if(@$keyin1=='0'):?>
                            	<a href="javascript:void(0)" class="show_fee" data-orderno="<?php echo $row["order_no"]?>" data-fee="<?php echo $row["fee"]?>" data-rel="tooltip" title="修改手續費">
									<?php echo number_format($row["fee"],0)?>
                                </a>
                            <?php else:?>
								<?php echo number_format($row["fee"],0)?>
                            <?php endif;?>
                        </td>
                        <td>
                        	<div class="text-success"><?php echo $row["bank_name"]?></div>
                            <div class="text-success">分行：<?php echo $row["bank_branch"]?></div>
                            <div class="text-success">帳號：<?php echo $row["bank_account"]?></div>
                            <div class="text-success">戶名：<?php echo $row["account_name"]?></div>
                            <div class="text-danger">實際金額：<?php echo number_format(($row["amount"]-$row["fee"]),0)?></div>
                            
                        </td>
                        <?php if(@$keyin1=='0'):?>
                        <td>
                        <?php if(@$keyin1=='0' && !in_array($web_root_u_power,array(4,5,6))):?>
                       		<select class="select2" onchange="keyChange('<?php echo $row["order_no"]?>',this.value)" data-allow-clear="false"> 
                       		<?php echo inSelectOption($sellKeyin1,$row["keyin1"])?>
                            </select>
                        <?php else:?>
                        	<?php echo inNumberString($sellKeyin1,$row["keyin1"])?>
                        <?php endif;?>
                        </td>
                        <td>
                        	<div class="btn-group">
                                <?php if($row["keyin1"]==0 && $row["no_sound"]==0 && !in_array($web_root_u_power,array(4,5,6))):?>
                                <button type="button" class="btn btn-danger" id="soundBTN<?php echo $row["order_no"]?>" onClick="toCloseSound('member_sell','order_no','<?php echo $row["order_no"]?>')">靜音</button>
                                <?php endif;?>
                            </div>
                        </td>
                        <?php else:?>
                        <td>
                        	<?php 
							if($row["update_admin"]!="" && $row["update_admin"] > 0){
								echo tb_sql("u_id","admin",$row["update_admin"]).'<br>('.tb_sql("u_name","admin",$row["update_admin"]).')';
							}elseif($row["update_admin"]!="" && $row["update_admin"]==0){
								echo '<span class="text-danger">[系統]</span>';	
							}
							?>
                        </td>
                        <td><div class="text-danger"><?php echo $row["updatetime"]?></div></td>
                        <?php endif;?>
                                                     
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
           
        </div>

    </div>
</div>


<div class="modal fade in" tabindex="-1" role="dialog" id="report_info">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header center h4 red" id="report_info_header">
                
            </div>
            <div class="modal-body">
            	<div class="row">
                	<input type="hidden" id="mem_num" />
                    <div class="col-xs-12 col-sm-8">
                        <div class="form-group">
                            <div class="input-daterange  input-group">
                                <input type="text" class="input-sm form-control jqdatetime" name="sDate" id="sDate"  placeholder="開始日期"  />
                                <span class="input-group-addon">
                                    <i class="fa fa-exchange"></i>
                                </span>
                                <input type="text" class="input-sm form-control jqdatetime" name="eDate" id="eDate"  placeholder="結束日期"   />
                            </div>
                        </div>
                	</div>
                    <div class="col-xs-12 col-sm-3">
                    	<div class="form-group">
                        	<div class="btn-group">
                            	<button type="button" class="btn btn-white btn-sm btn-primary" onClick="upDateReport()">更新日期</button>
                            </div>
                        </div>
                    </div>
                </div>
            
                
                <table class="table table-bordered table-hover">
                    <tr>
                        <th class="text-center">廠商</th>
                        <th class="text-center">投注</th>
                        <th class="text-center">洗碼</th>
                        <th class="text-center">輸贏</th>
                    </tr>
                    <tbody id="report_body">
                    
                    </tbody>
                    
                    <tr>
                    	<td class="text-center red">總計</td>
                        <td class="text-center formatNumber" id="total_betAmount"></td>
                        <td class="text-center formatNumber" id="total_validAmount"></td>
                        <td class="text-center number-info" id="total_winOrLoss"></td>
                    </tr>    
                </table>
                	       
            </div>
            <div class="modal-footer center">
                <button data-dismiss="modal" type="button"  class=" btn-white btn-lg btn btn-default btn-round">
                    <i class='ace-icon fa fa-times bigger-110'></i>&nbsp; 關閉
                </button>
            </div>
       </div>
    </div>
</div>

<!--修改手續費 -->
<div class="modal fade  in" tabindex="-1" role="dialog" id="fee_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true">×</button>-->
                <h4 class='smaller'> 修改手續費</h4>
            </div>
            <div class="modal-body">
            	<div class="alert alert-danger fade in" id="alrtMsg" style="display:none;">
            		<strong id="alrtTitle">請求失敗！</strong>&nbsp;&nbsp;<span id="alertBody">XXXX</span>
            	</div>
            	<form class="form-horizontal newoil-form" id="feeForm" method="post">
                	<input type="hidden" class="DelVal" id="order_no" />
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">訂單編號</label>
                        <div class="col-xs-12 col-sm-10">
                          <p class="form-control-static" id="show_order_no"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputWarning" class="col-xs-12 col-sm-2 control-label">手續費</label>
                        <div class="col-xs-12 col-sm-10">
                        	<input name="fee" id="fee" class="form-control DelVal digits" placeholder="點數" required  />
                        </div>
                    </div>
                    
                                    
                </form>   
            </div>
            <div class="modal-footer center">
            	<button type="button" id="feeBTN" class="btn btn-lg btn-white btn-pink btn-round"><i class="ace-icon fa fa-check bigger-110"></i>&nbsp;送出</button>
                <button data-dismiss="modal" type="button"  class=" btn-white btn-lg btn btn-default btn-round">
                    <i class='ace-icon fa fa-times bigger-110'></i>&nbsp; 關閉
                </button>
            </div>
       </div>
   </div>
</div>        
<!--修改手續費 -->

