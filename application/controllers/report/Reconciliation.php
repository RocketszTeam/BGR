
<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Reconciliation extends Core_controller{
	public function __construct(){
		parent::__construct();
		error_reporting(0);
		date_default_timezone_set("Asia/Taipei");
	}
	public function get_report($sTime=NULL,$eTime=NULL,$Page=0,$Row=100){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d\TH:i:s');
			$sTime=date('Y-m-d\TH:i:s',strtotime($eTime."-30 min"));
		}
		//補帳 
		$sTime = '2017-09-13T00:00:00';
		$eTime = '2017-09-13T23:59:59';
		echo 'stime:'.$sTime .'<br>';
		echo 'etime:'.$eTime .'<br>';

		$result=$this->qtechapi->reporter_all($sTime,$eTime,$Page,$Row);
		print_r($result);
		if(isset($result)){	//執行成功且有帳回來
			if(count($result->items) > 0){	//有帳
				foreach($result->items as $row){
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->playerId."' and gamemaker_num=11";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$totalWinlose=(float)$row->totalPayout-(float)$row->totalBet;	//計算實際輸贏金額
					
					$u_power4_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					
					$parameter=array();
					$colSql="id,initiated,completed,playerId,totalBet,totalPayout,totalWinlose,gameId,gameCategory,status";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `qtech_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':id']=$row->id;
					$parameter[':initiated']=$row->initiated;
					$parameter[':completed']=$row->completed;
					$parameter[':playerId']=$row->playerId;
					$parameter[':totalBet']=$row->totalBet;
					$parameter[':totalPayout']=$row->totalPayout;
					$parameter[':totalWinlose']=$totalWinlose;
					$parameter[':gameId']=$row->gameId;
					$parameter[':gameCategory']=$row->gameCategory;
					$parameter[':status']=$row->status;
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
				if(isset($result->links)){
					//echo (int)$result->page_num;
					$this->get_report($sTime,$eTime,($Page+1),$Row);
				}
			}
		}
	}
	
	
	
	//取得分潤成數~~up_num 傳入上層,down_num傳入下成
	public function get_percent($up_num,$down_num){
		$up_percent=tb_sql('percent','admin',$up_num);
		$down_percent=tb_sql('percent','admin',$down_num);
		$profit=$up_percent-$down_percent;
		if($profit <=0){
			return 0;
		}else{
			return ($profit / 100);
		}
	}
	
	private function check_betnum($sn){	//檢查此筆資料是否已經存在
		$sqlStr="select `id` from `qtech_report` where `trans_id`=?";	
		$row=$this->webdb->sqlRow($sqlStr,array(':sn'=>$sn));
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}
}

?>