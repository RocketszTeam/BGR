<script type="text/JavaScript">
	$(function(){
		$('#toDay').click(function(){	//本日
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		
		$('#yeDay').click(function(){	//昨日
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		
		$('#toWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
	
		$('#yeWeek').click(function(){	//本週
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		$('#toMonth').click(function(){	//本月
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		$('#ymMonth').click(function(){	//上月
			$('#find7').val($(this).attr('data-d1') + ' 00:00:00');
			$('#find8').val($(this).attr('data-d2') + ' 23:59:59');
		});
		
		$('#auto_report').click(function(){
			if($('#find7').val()==''){
				alert('請設定手動補帳日期');
				$('#find7').focus();
				return false;	
			}
			if($('#find8').val()==''){
				alert('請設定手動補帳日期');
				$('#find8').focus();
				return false;	
			}
			if ($('#find7').val()!="" && $('#find8').val()!=""){
				if ( Date.parse($('#find8').val()) < Date.parse($('#find7').val())){
					alert("起迄日期錯誤！");
					$('#find7').focus();
					return false;
				}
			}
			$.blockUI({ message: '<img src="<?php echo ASSETS_URL?>/www/images/009.gif" ><br><span class="purple">補帳中請稍候‧‧‧</span>',baseZ: 2000});
			$.ajax({
				type: "POST",
				url: CI_URL + "report/Bingo_report/auto_report",
				cache: false,
				dataType:"json",
				data: { 'sTime' : $('#find7').val(),'eTime' : $('#find8').val() }
			}).done(function( htmlData ) {  
				$.unblockUI();
				if(htmlData.RntCode=='Y'){
					modalMsg(htmlData.Msg,location.href);
					//modalMsg(htmlData.Msg,location.href);
					//location.href=location.href;
				}else{
					modalMsg(htmlData.Msg);
				}
			});
			
		});
		
	});
</script>
<!-- PAGE CONTENT BEGINS -->

<div class="row">
	<form  method="get" action="">
        <div class="col-xs-12 col-sm-5">
            <div class="form-group">
                <div class="input-daterange  input-group">
                    <input type="text" class="input-sm form-control jqdatetime" name="find7" id="find7" value="<?php echo  @$_REQUEST["find7"] ?>" placeholder="開始日期"  />
                    <span class="input-group-addon">
                        <i class="fa fa-exchange"></i>
                    </span>
                    <input type="text" class="input-sm form-control jqdatetime" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>" placeholder="結束日期"   />
                    <span class="input-group-addon">帳號</span>
                    <input type="text" class="input-sm form-control" name="find9" id="find9" value="<?php echo @$_REQUEST["find9"]?>" placeholder="帳號(搭配日期)"   />
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
                    <button type="button" class="btn btn-white btn-sm btn-pink" id="auto_report" >手動補帳</button>
                </div>
            </div>
        </div>
    </form>    
</div>


<?php if(!$showself || isset($root) && $root!="" && (tb_sql('u_power','admin',$root) >= $web_root_u_power || $web_root_num==0)):?>
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
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
			$betAmountTotal=0;
			$validAmountTotal=0;
			$winOrLossTotal=0;
			$ProfitTotal=0;
			$total_all = 0 ;
            foreach($result as $row):
				$betAmountTotal+=$row["betAmount"];
				$validAmountTotal+=$row["validAmount"];
				$winOrLossTotal+=$row["winOrLoss"];
				$ProfitTotal+=@$row["Profit"];
				$total_all = $total_all + $row["totals"];
        ?>
        <tr>
        	<td class="text-center">
				<?php 
                if(isset($row["u_power"]) && $row["u_power"]!=NULL){
                    echo tb_sql2("power_name","admin_group","u_power",$row["u_power"]);
                }elseif(isset($row["u_power"]) && $row["u_power"]==0){
					echo '<div class="text-danger">未歸帳</div>';
				}else{
                    echo '會員';
                }
                ?>
            </td>
            <td class="text-center">
                <div>
                <?php if(isset($row["u_power"]) && $row["u_power"]!=NULL):?>
                <a href="<?php echo site_url($baseURL.$row["num"].($att!="" ? "?p=1".$att : ""))?>"><?php echo $row["u_id"]?></a>
                (<span class="text-success"><?php echo tb_sql('percent','admin',$row['num'])?>%</span>)
                <div class="text-danger"><?php echo tb_sql('u_name','admin',$row['num'])?></div>
                <?php elseif(isset($row["u_power"]) && $row["u_power"]==0):?> 
                <div class="text-danger">未歸帳</div> 
                <?php else:?>
                <a href="<?php echo site_url($memberURL.$row["num"].($att!="" ? "?p=1&find1=".$row["mem_num"]."".$att : ""))?>"><?php echo $row["u_id"]?></a>
                <div class="text-danger"><?php echo tb_sql('u_name','member',$row['mem_num'])?></div>
                <?php endif;?> 
                </div>
            
            </td>
            <td class="text-center">
				<?php echo number_format($row["betAmount"],2,'.',',')?>
                <div>(<?php echo $row["totals"]?>筆)</div>
            </td>
            <td class="text-center"><?php echo  number_format($row["validAmount"],2,'.',',')?></td>
            <td class="text-center">
				<?php
                    $spanClass='';
                    if($row["winOrLoss"] < 0){
                        $spanClass='text-danger';
                    }elseif($row["winOrLoss"] > 0){
                        $spanClass='text-success';
                    }
                ?>
                <span class="<?php echo $spanClass?>"><?php echo  number_format($row["winOrLoss"],2,'.',',')?></span>
            </td>
            <td class="text-center">
				<?php
                    $spanClass='';
                    if(@$row["Profit"] < 0){
                        $spanClass='text-danger';
                    }elseif(@$row["Profit"] > 0){
                        $spanClass='text-success';
                    }
                ?>
                <span class="<?php echo $spanClass?>"><?php echo  number_format(@$row["Profit"],2,'.',',')?></span>
            </td>
                                                  
        </tr>
        <?php endforeach;
		endif;
		?>
		<?php if(count($result) > 0):?>
        <tr>
        <td class="text-right" colspan="2">合計：<?php echo $total_all; ?>筆</td>
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




