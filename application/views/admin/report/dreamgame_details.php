<?php

function result_replace($text,$gameResultArray){
	foreach($gameResultArray as $keys=>$value){
		$text=str_replace($keys,$value,$text);	
	}
	$text=str_replace(',-1','',$text);
	return $text;
}
//var_dump($this->_ci_cached_vars);
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
            <th class="text-center">局號/遊戲</th>
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


            foreach($result as $row):
				$betAmountTotal+=$row["betPoints"];
				$validAmountTotal+=$row["availableBet"];
				$winOrLossTotal+=$row["totalWinlose"];
        ?>
        <tr>
        	<td class="text-center">  <!-- 投注時間/單號 -->
                <div class="purple"><?php echo $row["betTime"]?></div>
                <div class="red"><?php echo $row["id"]?></div>
                
            </td>
            <td class="text-center"><?php echo $row["userName"]?></td>  <!-- 遊戲帳號 -->
            <td class="text-center">  <!-- 局號/遊戲 -->
                <div class="green"><?php echo $row["playId"]?></div>
                <div><?php echo $gameTypeArrays[$row["gameId"]]; ?></div>
            </td>
            <td class="text-center">  <!-- 投注類型 -->
                <div>
                <?php
                  if ($row['gameId']==1 || $row['gameId']==7|| $row['gameId']==9|| $row['gameId']==8 || $row['gameId']==3 ){

                   $betresult = (array) $row['betDetail'];
                   $x = explode(',',$betresult[0]);
                   $y = explode(':',$x[0]);
                   $poker = strpos($row['result'],'"poker"');
                   $pokers=explode(',', substr($row['result'],$poker+9,-2)) ;
				   
				   
                    
                   $betname = substr($y[0],-7,6);
                   if ($betname=='banker'){echo '<div class="text-danger">莊</div><span class="pink">';}
                   if ($betname=='player'){echo '<div class="text-danger">閒</div><span class="pink">';}
                   if ($betname=='community'){echo '<div class="text-danger">公牌</div><span class="pink">';}
				   if( strpos($row["betDetail"],"dragon") ||  strpos($row["betDetail"],"tiger")) { //龍虎
						
						$rr2 = json_decode($row["betDetail"]);										
						if (strpos($row["betDetail"],"dragon")) {
							$spanClass='';
							if($row["totalWinlose"] < 0){
								$spanClass='text-danger';
							}elseif($row["totalWinlose"] > 0){
								$spanClass='text-success';
							}
							echo "<span class='".$spanClass."'>投注：龍 ".$rr2->dragon."</span>";

							
						} else if( strpos($row["betDetail"],"tiger") ) {
							$spanClass='';
							if($row["totalWinlose"] < 0){
								$spanClass='text-danger';
							}elseif($row["totalWinlose"] > 0){
								$spanClass='text-success';
							}
							echo "<span class='".$spanClass."'>投注：虎 ".$rr2->tiger."</span>";							
						}		
						echo "<Br>";
						$rr = json_decode($row["result"]);
						$rra = explode(",",$rr->result);
						//[0]: 1:龙赢,2:虎赢,3:和赢
						//[1]: 赢方的点数						
						if($rra[0] == 1){
							echo "龍 贏(".$rra[1].")";
						} else if($rra[1] == 2){
							echo "虎 贏(".$rra[1].")";
						} else {
							echo "和局";	
						}										

				   }
				   
                    foreach ($pokers as $value) {
                        $playside = explode(':',$value);
                                         //echo $playside[0].'<br>';
                        if ($playside[0] == '"banker"'){
                            echo '莊牌{';
                            $cards = explode('-',str_replace('"','',$playside[1]));
                            echo $card[$cards[0]].'-'.$card[$cards[1]].'-'.$card[$cards[2]];
                            echo '}';
                        }
                        if ($playside[0] == '"player"'){
                            echo '閒牌{';
                            $cards = explode('-',str_replace('"','',$playside[1]));
                            echo $card[$cards[0]].'-'.$card[$cards[1]].'-'.$card[$cards[2]];
                            echo '}';
                        } 
                    } 
                    echo '</span>';
                } else if($row["gameId"]==5){//骰宝
					if(strlen($row["betDetail"]) > 0){
						$bt = $row["betDetail"];
						$bt = str_replace("oddW","單贏",$bt);
						$bt = str_replace("evenW","雙贏",$bt);	
						$bt = str_replace("odd","單",$bt);
						$bt = str_replace("even","雙",$bt);							
						$bt = str_replace("bigW","大贏",$bt);
						$bt = str_replace("smallW","小贏",$bt);											
						$bt = str_replace("big","大",$bt);
						$bt = str_replace("small","小",$bt);
						$bt = str_replace("threeForcesW","三军贏",$bt);						
						$bt = str_replace("threeForces","三军",$bt);

						$bt = str_replace("allDicesW","全围贏",$bt);						
						$bt = str_replace("allDices","全围",$bt);
						$bt = str_replace("nineWayGardsW","段牌贏",$bt);						
						$bt = str_replace("nineWayGards","段牌",$bt);
						$bt = str_replace("pairsW","长牌贏",$bt);						
						$bt = str_replace("pairs","长牌",$bt);
						$bt = str_replace("surroundDicesW","围骰贏",$bt);						
						$bt = str_replace("surroundDices","围骰",$bt);
						$bt = str_replace("pointsW","點數贏",$bt);						
						$bt = str_replace("points","點數",$bt);																								

						$bt = str_replace("{","",$bt);
						$bt = str_replace("}","",$bt);
						$bt = str_replace('"',"",$bt);
						$bt = str_replace('"',"",$bt);
						
						echo $bt;
						/*
						for($si =0;$si < strlen($row["betDetail"]);$si++){
							echo substr($row["betDetail"],$si,1),",";
						}
						*/						
					}
				}
                ?></div>
            </td>
            <td class="text-center"><?php echo number_format($row["betPoints"],2,'.',',')?></td>  <!-- 投注 -->
            <td class="text-center"><?php echo  number_format($row["availableBet"],2,'.',',')?></td>  <!-- 洗碼 -->
            <td class="text-center">  <!-- 輸贏 -->
				<?php
                    $spanClass='';
                    if($row["totalWinlose"] < 0){
                        $spanClass='text-danger';
                    }elseif($row["totalWinlose"] > 0){
                        $spanClass='text-success';
                    }
                ?>

                <span class="<?php echo $spanClass?>"><?php echo  number_format($row["totalWinlose"],2,'.',',')?></span>
            </td>
        </tr>
        <?php endforeach;
		?>
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
        
        <?php  endif; ?>
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




