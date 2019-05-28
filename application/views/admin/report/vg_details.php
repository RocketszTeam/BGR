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
            <th class="text-center">遊戲類型</th>
            <th class="text-center">結果</th>
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
				$betAmountTotal+=$row["betamount"];
				$validAmountTotal+=$row["validbetamount"];
				$winOrLossTotal+=$row["winOrLoss"];
        ?>
        <tr>
        	<td class="text-center" >
                <div class="purple"><?php echo $row["createtime"]?></div>
                <div class="red"><?php echo $row["WagerID"]?></div>
            </td>
            <td class="text-center"><?php echo $row["username"]?></td>
            <td class="text-center">
                
                <div class="red">
				<?php if($row["gametype"]==1) echo "斗地主"?>
				<?php if($row["gametype"]==2) echo "二人麻將"?>
				<?php if($row["gametype"]==3) echo "抢庄牛牛"?>
				<?php if($row["gametype"]==4) echo "百人牛牛"?>
				<?php if($row["gametype"]==7) echo "楚漢德州"?>
				<?php if($row["gametype"]==8) echo "推筒子"?>
				<?php if($row["gametype"]==11) echo "血戰麻將"?>
				<?php if($row["gametype"]==999) echo "JP"?>
				</div>
            </td>
            <td class="text-left">
				<?php if($row["gameresult"]=='1') echo '<span class="text-success">[贏]</span>'?>
                <?php if($row["gameresult"]=='0') echo '<span class="text-danger">[輸]</span>'?>
				<?php if($row["gameresult"]=='2') echo '<span class="text-danger">[和]</span>'?>
                <?php// if($row["gameresult"]=='X') echo '<span class="text-danger">[未結算]</span>'?>
                <?php// if($row["gameresult"]=='C') echo '<span class="text-danger">[註銷]</span>'?>
            </td>
            <td class="text-center"><?php echo number_format($row["betamount"],2,'.',',')?></td>
            <td class="text-center"><?php echo  number_format($row["validbetamount"],2,'.',',')?></td>
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
        <td class="text-right" colspan="4">當前合計：</td>
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
        <td class="text-right text-danger" colspan="4">總計：</td>
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




