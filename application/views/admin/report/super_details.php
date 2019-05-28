<?php
function result_replace($text,$gameResultArray){
	foreach($gameResultArray as $keys=>$value){
		$text=str_replace($keys,$value,$text);	
	}
	$text=str_replace(',-1','',$text);
	return $text;
}
?>
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
				$validAmountTotal+=$row["bet_gold"];
				$winOrLossTotal+=$row["result_gold"];
        ?>
        <tr>
        	<td class="text-center">
                <div class="purple"><?php echo $row["m_date"]?></div>
                <div class="red"><?php echo $row["sn"]?></div>
            </td>
            <td class="text-center"><?php echo $row["m_id"]?></td>
            <td class="text-center">
                <div class="green"><?php echo $gType[$row["g_type"]]?></div>
                <div><?php echo $fashionArr[$row["fashion"]]?></div>
                <?php if($row["detail"] && $row["fashion"]=='20'):?>
                <div class="red"><?php echo count(unserialize($row["detail"]))?>串1</div>
                <?php endif;?>
            </td>
            <td>
            	<?php if($row["detail"]==NULL && $row["fashion"]!='20'):?>
                <div class="purple">[<?php echo $row["league"]?>]</div>
                <div>
					<?php if($row["visit_team"]!=NULL && $row["score2"]!="") echo '['.$row["score2"].']'	//客隊分數?>
                    <?php if($row["visit_team"]!=NULL) echo $row["visit_team"]	//客隊?>
                    <?php if($row["mode"]==2) echo '(<span class="pink">讓</span>)'	//客隊讓分?>
                    &nbsp; <?php echo ($row["chum_num"]!='' ? $row["chum_num"] : 'VS')?> &nbsp;
                    
                    <?php if($row["main_team"]!=NULL) echo $row["main_team"] //主隊?>
                    <?php if($row["main_team"]!=NULL && $row["score1"]!="") echo '['.$row["score1"].']'	//主隊分數?>
                    <span class="text-info">(主)</span>
                    <?php if($row["mode"]==1) echo '(<span class="pink">讓</span>)'	//主隊讓分?>
                    <div class="text-danger">
                        <?php if($row["fashion"]==4) echo ($row["mv_set"]==0 ? '單' : '雙') //單雙?>
                        <?php if($row["fashion"]==2) echo ($row["mv_set"]==0 ? '大' : '小') //大小?>
                        <?php 
                            if(!in_array($row["fashion"],array(2,4))){
                                if($row["mv_set"]==0) echo $row["visit_team"];
                                if($row["mv_set"]==1) echo $row["main_team"];
                                if($row["mv_set"]==2) echo '和局';
                            }
                        ?>
                        &nbsp;
                        <?php echo $row["compensate"]?>
                    </div>
                    <div>
                        <?php if($row["end"]==1) echo '<span class="text-success">[已結]</span>'?>
                        <?php if($row["status"]=='f') echo '<span class="text-danger">[退組]</span>'?>
                        <?php if($row["status"]=='d') echo '<span class="text-danger">[刪除單]</span>'?>
                    </div> 
                </div>
                <?php else :	//過關單?>
                <?php $detail=unserialize($row["detail"])?>
                <table  class="table table-bordered table-hover">
                <?php foreach($detail as $row2):?>
                <?php $row2=(array)$row2?>
                	<tr><td>
                		<div class="purple">[<?php echo $row["league"]?>]</div>
						<?php if($row2["visit_team"]!=NULL && $row2["score2"]!="") echo '['.$row2["score2"].']'	//客隊分數?>
                        <?php if($row2["visit_team"]!=NULL) echo $row2["visit_team"]	//客隊?>
                        <?php if($row2["mode"]==2) echo '(<span class="pink">讓</span>)'	//客隊讓分?>
                        &nbsp; <?php echo ($row2["chum_num"]!='' ? $row2["chum_num"] : 'VS')?> &nbsp;
                        
                        <?php if($row2["main_team"]!=NULL) echo $row2["main_team"] //主隊?>
                        <?php if($row2["main_team"]!=NULL && $row2["score1"]!="") echo '['.$row2["score1"].']'	//主隊分數?>
                        <span class="text-info">(主)</span>
                        <?php if($row2["mode"]==1) echo '(<span class="pink">讓</span>)'	//主隊讓分?>
                        <div class="text-danger">
                            <?php if($row2["fashion"]==4) echo ($row2["mv_set"]==0 ? '單' : '雙') //單雙?>
                            <?php if($row2["fashion"]==2) echo ($row2["mv_set"]==0 ? '大' : '小') //大小?>
                            <?php 
                                if(!in_array($row2["fashion"],array(2,4))){
                                    if($row2["mv_set"]==0) echo $row2["visit_team"];
                                    if($row2["mv_set"]==1) echo $row2["main_team"];
                                    if($row2["mv_set"]==2) echo '和局';
                                }
                            ?>
                            &nbsp;
                            <?php echo $row2["compensate"]?>
                        </div>
                        <div>
                            <?php if($row2["end"]==1) echo '<span class="text-success">[已結]</span>'?>
                        </div>
                	</td></tr>
                <?php endforeach;?>
                </table>
                <?php endif;?> 
            </td>
            <td class="text-center"><?php echo number_format($row["gold"],2,'.',',')?></td>
            <td class="text-center"><?php echo  number_format($row["bet_gold"],2,'.',',')?></td>
            <td class="text-center">
				<?php
                    $spanClass='';
                    if($row["result_gold"] < 0){
                        $spanClass='text-danger';
                    }elseif($row["result_gold"] > 0){
                        $spanClass='text-success';
                    }
                ?>
                <span class="<?php echo $spanClass?>"><?php echo  number_format($row["result_gold"],2,'.',',')?></span>
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




