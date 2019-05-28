<div class="row">
    <div class="col-xs-12 col-sm-5">
        <div class="form-group">
            <div class="input-daterange  input-group">
                <input type="text" class="input-sm form-control jqdatetime" name="find7" id="find7" value="<?php echo  @$_REQUEST["find7"] ?>" placeholder="開始日期"  />
                <span class="input-group-addon">
                    <i class="fa fa-exchange"></i>
                </span>
                <input type="text" class="input-sm form-control jqdatetime" name="find8" id="find8" value="<?php echo @$_REQUEST["find8"]?>" placeholder="結束日期"   />
            </div>
        </div>
    </div>
    
</div>

<?php if(isset($root) && $root!=""):?>
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
            <th class="text-center">投注時間/單號</th>
            <th class="text-center">遊戲帳號</th>
            <th class="text-center">局號/遊戲</th>
            <th class="text-center">投注類型</th>
            <th class="text-center">開牌結果</th>
            <th class="text-center">投注</th>
            <th class="text-center">洗碼</th>
            <th class="text-center">輸贏</th>
        </tr>
    </thead>
    <tbody>
		<?php 
        if(isset($result)):
			$betAmountTotal=0;
			$validAmountTotal=0;
			$winOrLossTotal=0;
            foreach($result as $row):
				$betAmountTotal+=$row["betAmount"];
				$validAmountTotal+=$row["validAmount"];
				$winOrLossTotal+=$row["winOrLoss"];
        ?>
        <tr>
        	<td class="text-center">
                <div class="purple"><?php echo $row["payoutTime"]?></div>
                <div class="red"><?php echo $row["betHistoryId"]?></div>
            </td>
            <td class="text-center"><?php echo $row["username"]?></td>
            <td class="text-center">
                <div class="green"><?php echo $row["roundNo"]?></div>
                <div class="red"><?php echo $gameType[$row["gameType"]]?></div>
            </td>
            <td class="text-left">
            	<?php $betMap=unserialize($row["betMap"])?>
                <table  class="table table-bordered table-hover">
                	<tr>
                    	<th class="text-center">投注項目</th>
                		<th class="text-center">投注金額</th>
                    </tr>
                     <?php foreach($betMap as $row2):?>
                     <?php $row2=(array)$row2?>
                    <tr>
                    	<td class="text-center red"><?php echo $betType[$row["gameType"]][$row2["betType"]]?></td>
                        <td class="text-center pink"><?php echo number_format($row2["betMoney"],2,'.',',')?></td>
                    </tr>
                     <?php endforeach;?> 
                </table>
               
                    
               
            </td>
            <td class="text-center">
                <?php $judgeResult=unserialize($row["judgeResult"])?>
                <?php foreach($judgeResult as $row2):?>	
                	<div class="text-danger"><?php echo $betType[$row["gameType"]][$row2]?></div>
                <?php endforeach;?>   
            </td>
            <td class="text-center"><?php echo number_format($row["betAmount"],2,'.',',')?></td>
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
        </tr>
        <?php endforeach;
		endif;
		?>
		<?php if(count($result) > 0):?>
        <tr>
        <td class="text-right" colspan="5">當前合計：</td>
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
        </tr>
        <tr>
        <td class="text-right text-danger" colspan="5">總計：</td>
        <td class="text-center"><?php echo number_format($rowSum['TotalbetAmount'],2,'.',',')?></td>
        <td class="text-center"><?php echo number_format($rowSum['TotalvalidAmount'],2,'.',',')?></td>
        <td class="text-center">
        <?php
            $spanClass='';
            if($rowSum['TotalwinOrLoss'] < 0){
                $spanClass='text-danger';
            }elseif($rowSum['TotalwinOrLoss'] > 0){
                $spanClass='text-success';
            }
        ?>
        <span class="<?php echo $spanClass?>"><?php echo  number_format($rowSum['TotalwinOrLoss'],2,'.',',')?></span>
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




