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
	});
</script>

<div class="row">
	<form  method="get" action="">
    <div class="col-xs-12 col-sm-5">
        <div class="form-group">
            <div class="input-daterange  input-group">
                <input type="text" class="input-sm form-control jqdatetime" name="find7" id="find7" value="<?php echo (@$_REQUEST["find7"]!="" ? @$_REQUEST["find7"] : @$sDate) ?>" placeholder="開始日期"  />
                <span class="input-group-addon">
                    <i class="fa fa-exchange"></i>
                </span>
                <input type="text" class="input-sm form-control jqdatetime" name="find8" id="find8" value="<?php echo (@$_REQUEST["find8"]!="" ? @$_REQUEST["find8"] : @$eDate) ?>" placeholder="結束日期"   />
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
                <a class="btn btn-white btn-sm btn-danger" href="<?php echo site_url(SYSTEM_URL."/Trade/index".($att!="" ? "?p=1".$att : ""))?>">回上層</a>
            </div>
        </div>
    </div>
    </form>
</div>

<div class="widget-box widget-color-green">
    <div class="widget-body">
        <div class="widget-main padding-8">
            <ul class="list-unstyled spaced">
                <li><i class="ace-icon fa fa-user-circle red bigger-110 red"></i>代理:<?php echo tb_sql('u_id','admin',$agent_num)?>(<?php echo tb_sql('u_name','admin',$agent_num)?>)</li>
            </ul>
        </div>
    </div>    
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
            <th>會員</th>
            <th>ATM<br><span><?php echo number_format($ATMSUM,0)?></span></th>
            <th>超商繳款<br><span><?php echo number_format($MMKSUM,0)?></span></th>
            <th>銀行匯款<br><span><?php echo number_format($BANKSUM,0)?></span></th>
            <th>儲值小計<br><span><?php echo number_format($OrdersSUM,0)?></span></th>
            <th>寶物出售
                <br>
                <span class="<?php echo ($FEESUM > 0 ? 'text-danger' : '' )?>"><?php echo ($FEESUM > 0 ? "-".number_format($FEESUM,0) : $FEESUM)?></span> /
                <span class="<?php echo ($SELLSUM > 0 ? 'text-danger' : '' )?>"><?php echo ($SELLSUM > 0 ? "-".number_format($SELLSUM,0) : $SELLSUM)?></span>          
            </th>
            <th>小計
               <br>
               <span class="<?php echo ($ALLSUM < 0 ? 'text-danger' : '' )?>"><?php echo  number_format($ALLSUM,0) ?></span>
            </th>
            <th>紅利贈點
                <br>
                <span><?php echo number_format($POINTSUM,0)?></span>    
            </th>
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
            foreach($result as $row):
        ?>
        <tr>
        	<td>
            	<?php echo $row["u_id"]?>
                <div class="text-danger"><?php echo $row["u_name"]?></div> 
            </td>
            <td><?php echo number_format(@$row["ATM_TOTAL"],0)?></td>
            <td><?php echo  number_format(@$row["MMK_TOTAL"],0)?></td>
            <td><?php echo  number_format(@$row["BANK_TOTAL"],0)?></td>
            <td><?php echo  number_format(@$row["Orders_TOTAL"],0)?></td>
            <td>
                <span class="<?php echo (@$row["FEE_TOTAL"] > 0 ? 'text-danger' : '' )?>"><?php echo (@$row["FEE_TOTAL"] > 0 ? "-".number_format(@$row["FEE_TOTAL"],0) : number_format(@$row["FEE_TOTAL"],0))?></span> 
                            / 
                <span class="<?php echo (@$row["SELL_TOTAL"] > 0 ? 'text-danger' : '' )?>"><?php echo (@$row["SELL_TOTAL"] > 0 ? "-".number_format(@$row["SELL_TOTAL"],0) : number_format(@$row["SELL_TOTAL"],0))?></span>  
            </td>
            <td>
				 <span class="<?php echo (@$row["ALL_TOTAL"] < 0 ? 'text-danger' : '' )?>"><?php echo  number_format(@$row["ALL_TOTAL"],0) ?></span>                                                  
           </td>
           <td>
                <span><?php echo number_format(@$row["POINT_TOTAL"],0)?></span>  
          
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


