<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class S7pk_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function get_report($sTime=NULL,$eTime=NULL){
	
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('YmdHis');
			$sTime=date('YmdHis',strtotime($eTime."-1 hour"));
		}
		$result=$this->s7pkapi->reporter_all($sTime,$eTime);
		if(count($result) > 0){	//有帳
			foreach($result as $row){
				//取出會員代理總代代理編號	
				$sqlStr="select mem_num from `games_account` where u_id='".$row->Account."' and gamemaker_num=22";	
				$row_mem=$this->webdb->sqlRow($sqlStr);
				$mem_num=$row_mem["mem_num"];	//取出會員編號
				$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
				$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
				$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
				
				
				$winOrLoss=(float)$row->Payoff-(float)$row->BetAmount;
				
				$u_power4_profit=round($winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
				$u_power5_profit=round($winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
				$u_power6_profit=round($winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
				
				
				$parameter=array();
				$colSql="WagersID,ID,Account,WagersDate,BetAmount,Payoff,winOrLoss";
				$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
				$sqlStr="REPLACE INTO `7pk_report` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter['WagersID']=$row->WagersID;
				$parameter['ID']=$row->ID;
				$parameter['Account']=$row->Account;
				$parameter['WagersDate']=$row->WagersDate;
				$parameter['BetAmount']=$row->BetAmount;
				$parameter['Payoff']=$row->Payoff;
				$parameter['winOrLoss']=$winOrLoss;
				$parameter['mem_num']=$mem_num;
				$parameter['u_power4']=$u_power4;
				$parameter['u_power5']=$u_power5;
				$parameter['u_power6']=$u_power6;
				$parameter['u_power4_profit']=$u_power4_profit;
				$parameter['u_power5_profit']=$u_power5_profit;
				$parameter['u_power6_profit']=$u_power6_profit;	
				
				
				$this->webdb->sqlReplace('7pk_report',$parameter);				
				//$this->webdb->sqlExc($sqlStr,$parameter);
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
	
}

?>