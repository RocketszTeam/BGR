<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Ebet_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function index(){
		$sTime=date('Y-m-d',strtotime("-1 day")).' 00:00:00';
		$eTime=date('Y-m-d',strtotime("-1 day")).' 23:59:59';
		$this->get_report($sTime,$eTime);
	}
	
	
	public function get_report($sTime=NULL,$eTime=NULL,$pageNum=1,$pageSize=5000){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-1 hour"));
		}
		$result=$this->ebetapi->reporter_all($sTime,$eTime,$pageNum,$pageSize);
		print_r($result);
		if(isset($result)){	//執行成功且有帳回來
			if($result->count > 0){	//有帳
				$maxpage = $result->count % $pageSize == 0 ? $result->count/$pageSize : floor($result->count/$pageSize)+1; //總頁數
				foreach($result->betHistories as $row){
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->username."' and gamemaker_num=24";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$betAmount=0;
					//計算投注金額
					foreach($row->betMap as $rowBet){
						$betAmount+=$rowBet->betMoney;	
					}
					$winOrLoss=(float)$row->payout-(float)$betAmount;	//計算實際輸贏金額
					
					$u_power4_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					
					$parameter=array();
					$colSql="betHistoryId,userId,username,createTime,payoutTime,gameType,roundNo,betAmount,validAmount,payout";
					$colSql.=",winOrLoss,betMap,bankerCards,playerCards,allDices,dragonCard,tigerCard,number,judgeResult";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `ebet_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':betHistoryId']=$row->betHistoryId;
					$parameter[':userId']=$row->userId;
					$parameter[':username']=$row->username;
					$parameter[':createTime']=date('Y-m-d H:i:s',$row->createTime);
					$parameter[':payoutTime']=date('Y-m-d H:i:s',$row->payoutTime);
					$parameter[':gameType']=$row->gameType;
					$parameter[':roundNo']=$row->roundNo;
					$parameter[':betAmount']=$betAmount;
					$parameter[':validAmount']=$row->validBet;
					$parameter[':payout']=$row->payout;
					$parameter[':winOrLoss']=$winOrLoss;
					$parameter[':betMap']=(isset($row->betMap) ? serialize($row->betMap) : NULL);
					$parameter[':bankerCards']=(isset($row->bankerCards) ? implode(',',$row->bankerCards) : NULL);
					$parameter[':playerCards']=(isset($row->playerCards) ? implode(',',$row->playerCards) : NULL);
					$parameter[':allDices']=(isset($row->allDices) ? implode(',',$row->allDices) : NULL);
					$parameter[':dragonCard']=(isset($row->dragonCard) ? $row->dragonCard : NULL);
					$parameter[':tigerCard']=(isset($row->tigerCard) ? $row->tigerCard : NULL);
					$parameter[':number']=(isset($row->number) ? $row->number : NULL);
					$parameter[':judgeResult']=(isset($row->judgeResult) ? serialize($row->judgeResult) : NULL);
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
				//繼續往下撈分頁
				if($pageNum < $maxpage){
					//echo (int)$result->page_num;
					$this->get_report($sTime,$eTime,($pageNum+1),$pageSize);
				}
			}
		}
	}
	
	

}

?>