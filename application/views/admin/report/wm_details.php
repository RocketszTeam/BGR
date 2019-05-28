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
            <th class="text-center">局號(桌號)/遊戲</th>
            <th class="text-center">投注類型</th>
            <th class="text-center">投注</th>
            <th class="text-center">洗碼</th>
            <th class="text-center">輸贏</th>
        </tr>
    </thead>
    <tbody>
		<?php 

        //echo '<pre>';
        //print_r($result);
        //echo '</pre>';


        if(isset($result)):
			$betAmountTotal=0;
			$validAmountTotal=0;
			$winOrLossTotal=0;
            $card[0] = "";
            $card[1] = "♠A";
            $card[2] = "♠2";
            $card[3] = "♠3";
            $card[4] = "♠4";
            $card[5] = "♠5";
            $card[6] = "♠6";
            $card[7] = "♠7";
            $card[8] = "♠8";
            $card[9] = "♠9";
            $card[10] = "♠10";
            $card[11] = "♠J";
            $card[12] = "♠Q";
            $card[13] = "♠K";
            $card[14] = "♥A";
            $card[15] = "♥2";
            $card[16] = "♥3";
            $card[17] = "♥4";
            $card[18] = "♥5";
            $card[19] = "♥6";
            $card[20] = "♥7";
            $card[21] = "♥8";
            $card[22] = "♥9";
            $card[23] = "♥10";
            $card[24] = "♥J";
            $card[25] = "♥Q";
            $card[26] = "♥K";
            $card[27] = "♣A";
            $card[28] = "♣2";
            $card[29] = "♣3";
            $card[30] = "♣4";
            $card[31] = "♣5";
            $card[32] = "♣6";
            $card[33] = "♣7";
            $card[34] = "♣8";
            $card[35] = "♣9";
            $card[36] = "♣10";
            $card[37] = "♣J";
            $card[38] = "♣Q";
            $card[39] = "♣K";
            $card[40] = "♦A";
            $card[41] = "♦2";
            $card[42] = "♦3";
            $card[43] = "♦4";
            $card[44] = "♦5";
            $card[45] = "♦6";
            $card[46] = "♦7";
            $card[47] = "♦8";
            $card[48] = "♦9";
            $card[49] = "♦10";
            $card[50] = "♦J";
            $card[51] = "♦Q";
            $card[52] = "♦K";
           // echo '<pre>';
           // print_r($result);
           // echo '</pre>';
            foreach($result as $row):
				$betAmountTotal+=$row["bet"];
				$validAmountTotal+=$row["validbet"];
				$winOrLossTotal+=$row["winLoss"];
            //    echo '<br>'.$winOrLossTotal.'</br>';
        ?>
        <tr>
        	<td class="text-center">  <!-- 投注時間/單號 -->
                <div class="purple"><?php echo $row["betTime"]?></div>
                <div class="red"><?php echo $row["betid"]?></div>
            </td>
            <td class="text-center"><?php echo $row["user"]?></td>  <!-- 遊戲帳號 -->
            <td class="text-center">  <!-- 局號/遊戲 -->
                <div class="green"><?php echo $row["gid"]?>(<?php echo $row["tableId"] ?>)</div>
                <div><?php echo $row["gname"]; ?></div>
            </td>
            <td class="text-center">  <!-- 投注類型 -->
                <div>
                <?php //echo @$betTypeArray[$row["gameType"]][$row["BetType"]];
                  echo $row["betResult"].'<br>'.$row["gameResult"];

                ?></div>
            </td>
            <td class="text-center"><?php echo number_format($row["bet"],2,'.',',')?></td>  <!-- 投注 -->
            <td class="text-center"><?php echo  number_format($row["validbet"],2,'.',',')?></td>  <!-- 洗碼 -->
            <td class="text-center">  <!-- 輸贏 -->
				<?php
                    $spanClass='';
                    if($row["winLoss"] < 0){
                        $spanClass='text-danger';
                    }elseif($row["winLoss"] > 0){
                        $spanClass='text-success';
                    }
                ?>

                <span class="<?php echo $spanClass?>"><?php echo  number_format($row["winLoss"],2,'.',',')?></span>
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
            if($rowSum['TotalwinOrLoss']-$rowSum['TotalbetAmount'] < 0){
                $spanClass='text-danger';
            }elseif($rowSum['TotalwinOrLoss']-$rowSum['TotalbetAmount'] > 0){
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




