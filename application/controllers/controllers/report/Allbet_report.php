<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Allbet_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		
	}
	
	
	public function index(){
		$eTime=date('Y-m-d',strtotime("-1 day"));
		$sTime=date('Y-m-d',strtotime($eTime."-1 day"));
		
		$result=$this->allbetapi->reporter_all2($sTime,$eTime);
		
		if(is_array($result) && count($result)){	//執行成功且有帳回來
			foreach($result as $row){
				//寫入DB
				//取出會員代理總代代理編號	
				$sqlStr="select mem_num from `games_account` where u_id='".$row->client."' and gamemaker_num=3";	
				$row_mem=$this->webdb->sqlRow($sqlStr);
				$mem_num=$row_mem["mem_num"];	//取出會員編號
				$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
				$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
				$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
				
				$u_power4_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
				$u_power5_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
				$u_power6_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤

				$parameter=array();
				$colSql="betNum,client,gameRoundId,gameType,betTime,betAmount,validAmount,winOrLoss,state,currency";
				$colSql.=",exchangeRate,betType,gameResult,gameRoundStartTime,gameRoundEndTime,tableName,commission";
				$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
				$sqlStr="REPLACE INTO `allbet_report` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':betNum']=$row->betNum;
				$parameter[':client']=$row->client;
				$parameter[':gameRoundId']=$row->gameRoundId;
				$parameter[':gameType']=$row->gameType;
				$parameter[':betTime']=$row->betTime;
				$parameter[':betAmount']=$row->betAmount;
				$parameter[':validAmount']=$row->validAmount;
				$parameter[':winOrLoss']=$row->winOrLoss;
				$parameter[':state']=$row->state;
				$parameter[':currency']=$row->currency;
				$parameter[':exchangeRate']=$row->exchangeRate;
				$parameter[':betType']=$row->betType;
				$parameter[':gameResult']=$row->gameResult;
				$parameter[':gameRoundStartTime']=$row->gameRoundStartTime;
				$parameter[':gameRoundEndTime']=$row->gameRoundEndTime;
				$parameter[':tableName']=$row->tableName;
				$parameter[':commission']=$row->commission;
				$parameter[':mem_num']=$mem_num;
				$parameter[':u_power4']=$u_power4;
				$parameter[':u_power5']=$u_power5;
				$parameter[':u_power6']=$u_power6;
				$parameter[':u_power4_profit']=$u_power4_profit;
				$parameter[':u_power5_profit']=$u_power5_profit;
				$parameter[':u_power6_profit']=$u_power6_profit;					
				$this->webdb->sqlExc($sqlStr,$parameter);
			
			}	
		}
		
	}
	
	
	public function get_report(){
		$eTime=now();
		//$sTime=date('Y-m-d H:i:s',strtotime($sTime."-$hour hour"));	
		$sTime=date('Y-m-d H:i:s',strtotime($eTime."-1 hour"));
		//echo $sTime.'<br>'.$eTime;
		$result=$this->allbetapi->reporter_all($sTime,$eTime);
		
		//print_r($result);
		if(is_array($result) && count($result)){	//執行成功且有帳回來
			foreach($result as $row){
				//寫入DB
				//if($this->check_betnum($row->betNum)){	//資料庫內沒有紀錄才新增
					
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->client."' and gamemaker_num=3";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$u_power4_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤

					
					$parameter=array();
					$colSql="betNum,client,gameRoundId,gameType,betTime,betAmount,validAmount,winOrLoss,state,currency";
					$colSql.=",exchangeRate,betType,gameResult,gameRoundStartTime,gameRoundEndTime,tableName,commission";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `allbet_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betNum']=$row->betNum;
					$parameter[':client']=$row->client;
					$parameter[':gameRoundId']=$row->gameRoundId;
					$parameter[':gameType']=$row->gameType;
					$parameter[':betTime']=$row->betTime;
					$parameter[':betAmount']=$row->betAmount;
					$parameter[':validAmount']=$row->validAmount;
					$parameter[':winOrLoss']=$row->winOrLoss;
					$parameter[':state']=$row->state;
					$parameter[':currency']=$row->currency;
					$parameter[':exchangeRate']=$row->exchangeRate;
					$parameter[':betType']=$row->betType;
					$parameter[':gameResult']=$row->gameResult;
					$parameter[':gameRoundStartTime']=$row->gameRoundStartTime;
					$parameter[':gameRoundEndTime']=$row->gameRoundEndTime;
					$parameter[':tableName']=$row->tableName;
					$parameter[':commission']=$row->commission;
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				//}
			}	
		}
	}
	
	public function egame_af($sTime=NULL,$eTime=NULL,$pageIndex=1,$pageSize=1000){
		//$eTime=date('Y-m-d').' 23:59:59';
		if($sTime==NULL && $eTime==NULL){
			$eTime=now();
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-12 hour"));
		}
		$result=$this->allbetapi->reporter_af($sTime,$eTime,$pageIndex,$pageSize);
		print_r($result);
		if(count($result)){	//執行成功且有帳回來
			$maxpage = (int)$result->count % $pageSize == 0 ? (int)$result->count/$pageSize : floor((int)$result->count/$pageSize)+1; //總頁數
			foreach($result->datas as $row){
				//取出會員代理總代代理編號	
				$sqlStr="select mem_num from `games_account` where u_id='".$row->client."' and gamemaker_num=3";	
				$row_mem=$this->webdb->sqlRow($sqlStr);
				$mem_num=$row_mem["mem_num"];	//取出會員編號
				//判斷該會員此局是否存在
				$sqlStr="select client,gameround from `allbet_egame_report` where `client`='".$row->client."' and `gameround`='".$row->gameround."'";
				$rowCheck=$this->webdb->sqlRow($sqlStr);
				if($rowCheck==NULL){	//不存在則寫入db
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					$u_power4_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$row->winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					$parameter=array();
					$colSql="betTime,client,egameType,gameType,gameround,betAmount,validAmount,winOrLoss,jackpotBetAmount,jackpotValidAmount,jackpotWinOrLoss";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="INSERT INTO `allbet_egame_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betTime']=$row->betTime;
					$parameter[':client']=$row->client;
					$parameter[':egameType']=$row->egameType;
					$parameter[':gameType']=$row->gameType;
					$parameter[':gameround']=$row->gameround;
					$parameter[':betAmount']=$row->betAmount;
					$parameter[':validAmount']=$row->validAmount;
					$parameter[':winOrLoss']=$row->winOrLoss;
					$parameter[':jackpotBetAmount']=$row->jackpotBetAmount;
					$parameter[':jackpotValidAmount']=$row->jackpotValidAmount;
					$parameter[':jackpotWinOrLoss']=$row->jackpotWinOrLoss;
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
			}
			//echo '總共：'.$maxpage.'頁，目前在第'.$pageIndex.'頁';
			if($pageIndex < $maxpage){	//繼續往下撈
				$this->egame_af($sTime,$eTime,($pageIndex+1),$pageSize);
			}
		}
	}
	
	private function check_betnum($betNum){	//檢查此筆資料是否已經存在
		$sqlStr="select `betNum` from `allbet_report` where `betNum`=?";	
		$row=$this->webdb->sqlRow($sqlStr,array(':betNum'=>$betNum));
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}
}

?>