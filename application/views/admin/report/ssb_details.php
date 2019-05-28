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
            <th class="text-center">類型/玩法</th>
            <th class="text-center">投注內容</th>
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
				$betAmountTotal+=$row["gold"];
				$validAmountTotal+=$row["gold_c"];
				$winOrLossTotal+=$row["meresult"];
        ?>
        <tr>
        	<td class="text-center">
                <div class="purple"><?php echo $row["added_date"]?></div>
                <div class="red"><?php echo $row["id"]?></div>
            </td>
            <td class="text-center"><?php echo $row["meusername"]?></td>
            <td class="text-center">
                <div class="green"><?php echo $row["g_title"]?></div>
                <div><?php echo $row["r_title"]?></div>
                <?php if($row["pr"]==1):?>
                <div class="red"><?php echo count(unserialize($row["subw"]))?>串1</div>
                <?php endif;?>
            </td>
            <td>
            	<?php if($row["pr"]==0):?>
                <div class="purple">[<?php echo $row["g_title"]?>]</div>
                <div>
					<?php echo $row["bet_txt_1"]?> 
                    
                    <div class="text-danger">
                    	<?php echo $row["bet_txt_2"]?> 
                    </div>
                    <div>
                        <?php if($row["result"]=='W') echo '<span class="text-success">[全贏]</span>'?>
                        <?php if($row["result"]=='WW') echo '<span class="text-success">[中洞贏]</span>'?>
                        <?php if($row["result"]=='L') echo '<span class="text-danger">[全輸]</span>'?>
                        <?php if($row["result"]=='LL') echo '<span class="text-danger">[中洞輸]</span>'?>
                        <?php if($row["result"]=='WL') echo '<span class="text-danger">[中洞全退]</span>'?>
                        <?php if($row["result"]=='N') echo '<span class="text-danger">[因賽事結果取消]</span>'?>
                        <?php if($row["result"]=='NC') echo '<span class="text-danger">[註銷]</span>'?>
                    </div> 
                </div>
                <?php else :	//過關單?>
                <?php $detail=unserialize($row["subw"])?>
                <table  class="table table-bordered table-hover">
                <?php foreach($detail as $row2):?>
                <?php $row2=(array)$row2?>
                	<tr><td>
                		<div class="purple">[<?php echo $row2["g_title"]?>]</div>
						
                        <?php echo $row2["bet_txt_1"]?> 
                        
                        <div class="text-danger">
                            <?php echo $row2["bet_txt_2"]?> 
                        </div>
                        <div>
							<?php if($row2["result"]=='W') echo '<span class="text-success">[全贏]</span>'?>
                            <?php if($row2["result"]=='WW') echo '<span class="text-success">[中洞贏]</span>'?>
                            <?php if($row2["result"]=='L') echo '<span class="text-danger">[全輸]</span>'?>
                            <?php if($row2["result"]=='LL') echo '<span class="text-danger">[中洞輸]</span>'?>
                            <?php if($row2["result"]=='WL') echo '<span class="text-danger">[中洞全退]</span>'?>
                            <?php if($row2["result"]=='N') echo '<span class="text-danger">[因賽事結果取消]</span>'?>
                            <?php if($row2["result"]=='NC') echo '<span class="text-danger">[註銷]</span>'?>
                        </div>
                	</td></tr>
                <?php endforeach;?>
                </table>
                <?php endif;?> 
            </td>
            <td class="text-center"><?php echo number_format($row["gold"],2,'.',',')?></td>
            <td class="text-center"><?php echo  number_format($row["gold_c"],2,'.',',')?></td>
            <td class="text-center">
				<?php
                    $spanClass='';
                    if($row["meresult"] < 0){
                        $spanClass='text-danger';
                    }elseif($row["meresult"] > 0){
                        $spanClass='text-success';
                    }
                ?>
                <span class="<?php echo $spanClass?>"><?php echo  number_format($row["meresult"],2,'.',',')?></span>
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




