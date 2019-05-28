<script type="text/JavaScript">
	$(function(){
		$('#toDay').click(function(){	//本日
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
		
		$('#yeDay').click(function(){	//昨日
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
		
		$('#toWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
	
		$('#yeWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
		$('#toMonth').click(function(){	//本月
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
		$('#ymMonth').click(function(){	//上月
			$('#find7').val($(this).attr('data-d1'));
			$('#find8').val($(this).attr('data-d2'));
		});
		
		$('#auto_report').click(function(){
			$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/www/images/009.gif" ><br><span class="purple">結算中請稍候‧‧‧</span>',baseZ: 2000});
			$.ajax({
				type: "POST",
				url: CI_URL + "report/Daily/update_now",
				cache: false,
			}).done(function( htmlData ) {  
				$.unblockUI();
				location.href=location.href;
			});
			
		});
		
		$('#auto_daily').click(function(){	//結算指定日期 
			if($('#find9').val()!="" && $('#find10').val()!=""){
				if(Date.parse($('#find10').val()) >= Date.parse($('#find9').val())){
					$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/www/images/009.gif" ><br><span class="purple">結算中請稍候‧‧‧</span>',baseZ: 2000});
					$.ajax({
						type: "POST",
						url: CI_URL + "report/Daily/update_now",
						cache: false,
						dataType:"json",
						data:{
							'sTime' : $('#find9').val(),
							'eTime' : $('#find10').val()
						}
					}).done(function( htmlData ) {  
						$.unblockUI();
						var msg='結算開始日期：' + htmlData.sTime + '<br>結算結束日期：' + htmlData.eTime;
						modalMsg(msg);
						//location.href=location.href;
					});
				}else{
					modalMsg('開始日期不得大於結束日期');
				}
			}else{
				modalMsg('請輸入結算起始日期');	
			}
			
		});
		
		
		
		
		$('.number-info').each(function() {
		   var txt=parseFloat($(this).html());
		  // console.log(txt);
		   if(txt < 0){
			   $(this).removeClass('text-success').addClass('text-danger');
			   spanClass='';
		   }else if(txt > 0){
			  $(this).removeClass('text-danger').addClass('text-success');
		   }
        });
		
	});
</script>
<!-- PAGE CONTENT BEGINS -->

<div class="row">
	<form  method="get" action="">
        <div class="col-xs-12 col-sm-5">
            <div class="form-group">
                <div class="input-daterange  input-group">
                    <input type="text" class="input-sm form-control jqdate" name="find7" id="find7" value="<?php echo  @$_REQUEST["find7"] ?>" placeholder="開始日期"  />
                    <span class="input-group-addon">
                        <i class="fa fa-exchange"></i>
                    </span>
                    <input type="text" class="input-sm form-control jqdate" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>" placeholder="結束日期"   />
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-5">
            <div class="form-group">
                <div class="btn-group">
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary">搜尋</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="toDay" data-d1="<?php echo date('Y-m-d')?>" data-d2="<?php echo date('Y-m-d')?>">本日</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="yeDay" data-d1="<?php echo date('Y-m-d',strtotime("-1 day"))?>" data-d2="<?php echo date('Y-m-d',strtotime("-1 day"))?>">昨日</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="toWeek" data-d1="<?php echo $toWeek['d1']?>" data-d2="<?php echo $toWeek['d2']?>">本週</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="yeWeek" data-d1="<?php echo $yeWeek['d1']?>" data-d2="<?php echo $yeWeek['d2']?>">上週</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="toMonth" data-d1="<?php echo $toMonth['d1']?>" data-d2="<?php echo $toMonth['d2']?>">本月</button>
                    <button type="sbumit" class="btn btn-white btn-sm btn-primary" id="ymMonth" data-d1="<?php echo $ymMonth['d1']?>" data-d2="<?php echo $ymMonth['d2']?>">上月</button>
                    <button type="button" class="btn btn-white btn-sm btn-pink" id="auto_report">立即結算</button>
                </div>
            </div>
        </div>
    </form>    
</div>

<?php if($web_root_u_power==1):?>
<div class="row">
	<form  method="get" action="">
        <div class="col-xs-12 col-sm-5">
            <div class="form-group">
                <div class="input-daterange  input-group">
                    <input type="text" class="input-sm form-control jqdate" name="find9" id="find9"  placeholder="結算開始日期"  />
                    <span class="input-group-addon">
                        <i class="fa fa-exchange"></i>
                    </span>
                    <input type="text" class="input-sm form-control jqdate" name="find10" id="find10"  placeholder="結算結束日期"   />
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-5">
            <div class="form-group">
                <div class="btn-group">
                    <button type="button" class="btn btn-white btn-sm btn-pink" id="auto_daily">立即結算</button>
                </div>
            </div>
        </div>
    </form>    
</div>
<?php endif;?>


<?php if(isset($root) && $root!="" && (tb_sql('u_power','admin',$root) >= $web_root_u_power || $web_root_num==0)):?>
<div class="page-header">
	<a  class="btn btn-danger" href="<?php echo $backBTN?>">回上層</a>
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
            <th class="text-center">身份</th>
            <th class="text-center">帳號</th>
            <th class="text-center">投注</th>
            <th class="text-center">洗碼</th>
            <th class="text-center">輸贏</th>
            <th class="text-center">分潤</th>
            <th class="text-center">紅利</th>
            <th class="text-center">結果</th>
        </tr>
    </thead>
    <?php //var_dump($this->_ci_cached_vars); ?>
    <tbody>
		<?php 
        if(isset($result)):
			$betAmountTotal=0;
			$validAmountTotal=0;
			$winOrLossTotal=0;
			$ProfitTotal=0;//分潤
			$PointsTotal=0;//紅利
			$ResultTotal=0;//結果
            foreach($result as $keys=>$row):
				$betAmount=0;
				$validAmount=0;
				$winOrLoss=0;
				$Profit=0;
				foreach($prefix as $value){
					$betAmount+=$row[$value[0]."_betAmount"];
					$validAmount+=$row[$value[0]."_validAmount"];
					$winOrLoss+=$row[$value[0]."_winOrLoss"];
					$Profit+=$row[$value[0]."_Profit"];	
				}
				$PointsProfit=$row["PointsProfit"];	//紅利
				$Result=$Profit + $PointsProfit;	//結果
				//計算總合
				$betAmountTotal+=$betAmount;
				$validAmountTotal+=$validAmount;
				$winOrLossTotal+=$winOrLoss;
				$PointsTotal+=$PointsProfit;
				$ProfitTotal+=@$Profit;
				$ResultTotal+=$Result;
				
        ?>
        <tr>
        	<td class="text-center">
				<?php if(isset($row["u_power"]) && $row["u_power"]!=NULL):?>
                	<a href="#"  data-toggle="modal"  data-target="#report_info<?php echo $keys?>"><?php echo  tb_sql2("power_name","admin_group","u_power",$row["u_power"]) ?></a>
                <?php elseif(isset($row["u_power"]) && $row["u_power"]==0):?>
                	<a href="#" class="text-danger"  data-toggle="modal"  data-target="#report_info<?php echo $keys?>">未歸帳</a>
                <?php else: ?>
                    <a href="#"  data-toggle="modal"  data-target="#report_info<?php echo $keys?>">代理</a>
                <?php endif; ?>
                
                <div class="modal fade in" tabindex="-1" role="dialog" id="report_info<?php echo $keys?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <?php echo $row["u_id"]?>
                                 (<span class="text-success"><?php echo tb_sql('percent','admin',$row['num'])?>%</span>)
                                 <div class="text-danger"><?php echo tb_sql('u_name','admin',$row['num'])?></div>
                            </div>
                            <div class="modal-body">
                             	<table class="table table-bordered table-hover table-responsive">
                                	<tr>
                                    	<th class="text-center">廠商</th>
                                        <th class="text-center">投注</th>
                                        <th class="text-center">洗碼</th>
                                        <th class="text-center">輸贏</th>
                                        <th class="text-center">分潤</th>
                                    </tr>
                                    <?php foreach($prefix as $value):?>
                                    <tr>
                                    	<td class="text-center"><?php echo $value[1]?></td>
                                        <td class="text-center"><?php echo number_format($row[$value[0]."_betAmount"],2,'.',',')?></td>
                                        <td class="text-center"><?php echo number_format($row[$value[0]."_validAmount"],2,'.',',')?></td>
                                        <td class="text-center number-info"><?php echo number_format($row[$value[0]."_winOrLoss"],2,'.',',')?></td>
                                        <td class="text-center number-info"><?php echo number_format($row[$value[0]."_Profit"],2,'.',',')?></td>
                                    </tr>
                                    <?php endforeach;?> 
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
			</td>
            <td class="text-center">
                <div>
                <?php if(isset($row["u_power"]) && $row["u_power"]!=NULL):?>
                	<a href="<?php echo site_url($baseURL.$row["num"].($att!="" ? "?p=1".$att : ""))?>"><?php echo $row["u_id"]?></a>
                <?php elseif(isset($row["u_power"]) && $row["u_power"]==0):?>
                	<div class="text-danger">未歸帳</div>
				<?php else:?>
               		<?php echo $row["u_id"]?>
                <?php endif;?>
                (<span class="text-success"><?php echo tb_sql('percent','admin',$row['num'])?>%</span>)
                <div class="text-danger"><?php echo tb_sql('u_name','admin',$row['num'])?></div>
                </div>
            </td>
            <td class="text-center"><?php echo number_format($betAmount,2,'.',',')?></td>
            <td class="text-center"><?php echo  number_format($validAmount,2,'.',',')?></td>
            <td class="text-center">
				<?php
                    $spanClass='';
                    if($winOrLoss < 0){
                        $spanClass='text-danger';
                    }elseif($winOrLoss > 0){
                        $spanClass='text-success';
                    }
                ?>
                <span class="<?php echo $spanClass?>"><?php echo  number_format($winOrLoss,2,'.',',')?></span>
            </td>
            <td class="text-center">
				<?php
                    $spanClass='';
                    if(@$Profit < 0){
                        $spanClass='text-danger';
                    }elseif(@$Profit > 0){
                        $spanClass='text-success';
                    }
                ?>
                <span class="<?php echo $spanClass?>"><?php echo  number_format(@$Profit,2,'.',',')?></span>
            </td>
            <td class="text-center"><?php echo  number_format($PointsProfit,2,'.',',')?></td>          
            <td class="text-center">
				<?php
                    $spanClass='';
                    if(@$Result < 0){
                        $spanClass='text-danger';
                    }elseif(@$Result > 0){
                        $spanClass='text-success';
                    }
                ?>
                <span class="<?php echo $spanClass?>"><?php echo  number_format(@$Result,2,'.',',')?></span>
            </td>
                                       
        </tr>
        <?php endforeach;
		endif;
		?>
		<?php if(count($result) > 0):?>
        <tr>
        <td class="text-right" colspan="2">合計：</td>
        <td class="text-center"><?php echo number_format($betAmountTotal,2,'.',',')?></td>
        <td class="text-center"><?php echo number_format($validAmountTotal,2,'.',',')?></td>
        <td class="text-center">
        <?php
            $spanClass='';
            if($winOrLossTotal < 0){
                $spanClass='text-danger';
            }elseif($winOrLossTotal > 0){
                $spanClass='text-success';
            }
        ?>
        <span class="<?php echo $spanClass?>"><?php echo  number_format($winOrLossTotal,2,'.',',')?></span>
        </td>
        <td class="text-center">
			<?php
                $spanClass='';
                if($ProfitTotal < 0){
                    $spanClass='text-danger';
                }elseif($ProfitTotal > 0){
                    $spanClass='text-success';
                }
            ?>
            <span class="<?php echo $spanClass?>"><?php echo  number_format(@$ProfitTotal,2,'.',',')?></span>
        </td>
        <td class="text-center"><?php echo  number_format($PointsTotal,2,'.',',')?></td>
        <td class="text-center">
        <?php
            $spanClass='';
            if($ResultTotal < 0){
                $spanClass='text-danger';
            }elseif($ResultTotal > 0){
                $spanClass='text-success';
            }
        ?>
        <span class="<?php echo $spanClass?>"><?php echo  number_format(@$ResultTotal,2,'.',',')?></span>
        </td>
        
        </tr>
        <?php endif;?>
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




