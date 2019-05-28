<?php
include_once (dirname(__FILE__)."/Core_model.php");

class Daily_model extends Core_model {
	protected $table ;
	
	public function __construct(){ 
        parent::__construct();
		date_default_timezone_set("Asia/Taipei");
    }
	
	
	//歐博歸帳
	public function allbet($sTime,$eTime,$makers_num=3){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(betAmount) as betAmount,SUM(validAmount) as validAmount,SUM(winOrLoss) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `allbet_report` where 1=1";
		$sqlStr.=" and betTime >='".$sTime."' and betTime <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}

	public function allbet_egame($sTime,$eTime,$makers_num='3-1'){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(betAmount) as betAmount,SUM(validAmount) as validAmount,SUM(winOrLoss) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `allbet_egame_report` where 1=1";
		$sqlStr.=" and betTime >='".$sTime."' and betTime <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num='".$makers_num."' and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num='".$rowCheck["makers_num"]."' and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}
	
	//沙龍歸帳
	public function sagame($sTime,$eTime,$makers_num=9){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(BetAmount) as betAmount,SUM(ValidAmount) as validAmount,SUM(ResultAmount) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `sagame_report` where 1=1";
		$sqlStr.=" and PayoutTime >='".$sTime."' and PayoutTime <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
					
					//$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}
	
	//super體育
	public function super($sTime,$eTime,$makers_num=8){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(gold) as betAmount,SUM(bet_gold) as validAmount,SUM(result_gold) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `super_report` where 1=1";
		$sqlStr.=" and m_date >='".$sTime."' and m_date <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}

	//捕魚機歸帳
	public function fish($sTime,$eTime,$makers_num=4){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(bet) as betAmount,SUM(profit) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `fish_report` where 1=1";
		$sqlStr.=" and bettimeStr >='".$sTime."' and bettimeStr <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=0;
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=0;
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}

	//qt歸帳
	public function qt($sTime,$eTime,$makers_num=11){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(totalBet) as betAmount,SUM(totalWinlose) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `qtech_report` where 1=1";
		$sqlStr.=" and initiated >='".$sTime."' and initiated <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=0;
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=0;
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}

	//dg
	public function dg($sTime,$eTime,$makers_num=12){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(betPoints) as betAmount,SUM(availableBet) as validAmount,SUM(totalWinlose) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `dreamgame_report` where 1=1";
		$sqlStr.=" and betTime >='".$sTime."' and betTime <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}

	//wm
	public function wm($sTime,$eTime,$makers_num=13){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(bet) as betAmount,SUM(validbet) as validAmount,SUM(winLoss) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `WM_report` where 1=1";
		$sqlStr.=" and betTime >='".$sTime."' and betTime <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}
	
	//ssb贏家體育
	public function ssb($sTime,$eTime,$makers_num=21){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(gold) as betAmount,SUM(gold_c) as validAmount,SUM(meresult) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `ssb_report` where 1=1";
		$sqlStr.=" and orderdate >='".date('Ymd',strtotime($sTime))."' and orderdate <='".date('Ymd',strtotime($eTime))."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
					
				}
			}
		}
	}
	
	//pkqt歸帳
	public function s7pk($sTime,$eTime,$makers_num=22){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(BetAmount) as betAmount,SUM(winOrLoss) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `7pk_report` where 1=1";
		$sqlStr.=" and `WagersDate` >='".$sTime."' and `WagersDate` <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=0;
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=0;
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}
	
	//SUM bingo
	public function bingo($sTime,$eTime,$makers_num=23){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(money) as betAmount,SUM(valid) as validAmount,SUM(winOrLoss) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `bingo_report` where 1=1";
		$sqlStr.=" and bet_time >='".$sTime."' and bet_time <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}
	
	//EB真人歸帳
	public function eb($sTime,$eTime,$makers_num=24){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(betAmount) as betAmount,SUM(validAmount) as validAmount,SUM(winOrLoss) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `ebet_report` where 1=1";
		$sqlStr.=" and payoutTime >='".$sTime."' and payoutTime <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}
	
	//水立方真人歸帳
	public function water($sTime,$eTime,$makers_num=25){
		$myDate=date('Y-m-d',strtotime($sTime));//設定歸帳日期為 前一天
		$sqlStr="select SUM(BetGold) as betAmount,SUM(RealBetPoint) as validAmount,SUM(WinGold) as winOrLoss,mem_num,u_power4,u_power5,u_power6,count(*) as betCount";
		$sqlStr.=",SUM(u_power4_profit) as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit";
		$sqlStr.=" from `water_report` where 1=1";
		$sqlStr.=" and BetDate >='".$sTime."' and BetDate <='".$eTime."' GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$checkSql="select * from daily_report where makers_num=".$makers_num." and mem_num=".$row["mem_num"]." and betTime='".$myDate."'";
				$rowCheck=$this->sqlRow($checkSql);
				if($rowCheck==NULL){	//該遊戲該會員的日帳不存在則新增
					$parameter=array();
					$colSql="betTime,betAmount,validAmount,winOrLoss,makers_num,mem_num,u_power4,u_power5,u_power6,betCount";
					$colSql.=",u_power4_profit,u_power5_profit,u_power6_profit,update_time";
					$inSql="INSERT INTO `daily_report` (".sqlInsertString($colSql,0).")";
					$inSql.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$myDate;
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':makers_num']=$makers_num;
					$parameter[':mem_num']=$row["mem_num"];
					$parameter[':u_power4']=$row["u_power4"];
					$parameter[':u_power5']=$row["u_power5"];
					$parameter[':u_power6']=$row["u_power6"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':update_time']=now();
					$this->sqlExc($inSql,$parameter);
				}else{	//存在則更新日帳
					$parameter=array();
					$colSql="betAmount,validAmount,winOrLoss,u_power4_profit,u_power5_profit,u_power6_profit,betCount,update_time";
			
					$upSql="UPDATE `daily_report` SET ".sqlUpdateString($colSql);
					$parameter[':betAmount']=round($row["betAmount"],2);
					$parameter[':validAmount']=round($row["validAmount"],2);
					$parameter[':winOrLoss']=round($row["winOrLoss"],2);
					$parameter[':u_power4_profit']=$row["u_power4_profit"];
					$parameter[':u_power5_profit']=$row["u_power5_profit"];
					$parameter[':u_power6_profit']=$row["u_power6_profit"];
					$parameter[':betCount']=$row["betCount"];
					$parameter[':update_time']=now();
					$upSql.=" where betTime='".$rowCheck["betTime"]."' and makers_num=".$rowCheck["makers_num"]." and mem_num=".$rowCheck["mem_num"];
					$this->sqlExc($upSql,$parameter);
				}
			}
		}
	}
	
}
