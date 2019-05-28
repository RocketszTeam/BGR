<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Water_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		
	}
	
	
	public function index(){
		$eTime=date('Y-m-d H:i:s');
		$sTime=date('Y-m-d H:i:s',strtotime($eTime."-6 hour"));
		$this->get_report($sTime,$eTime);
	}
	
	
	public function get_report($sTime=NULL,$eTime=NULL){
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-1 hour"));
		}
		$result=$this->waterapi->reporter_all($sTime,$eTime);
		print_r($result);
		if(isset($result) && count($result) > 0){	//執行成功且有帳回來
			foreach($result as $row){
				//取出會員代理總代代理編號	
				$sqlStr="select mem_num from `games_account` where `cid`=".$row->Cid." and gamemaker_num=25";	
				$row_mem=$this->webdb->sqlRow($sqlStr);
				$mem_num=$row_mem["mem_num"];	//取出會員編號
				$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
				$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
				$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
				
				$u_power4_profit=round((float)$row->WinGold * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
				$u_power5_profit=round((float)$row->WinGold * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
				$u_power6_profit=round((float)$row->WinGold * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
	
				$parameter=array();
				$parameter['Wid']=$row->Wid;
				$parameter['BetDate']=$row->BetDate;
				$parameter['ResultDate']=$row->ResultDate;
				$parameter['ComID']=$row->ComID;
				$parameter['Cid']=$row->Cid;
				$parameter['Sid']=$row->Sid;
				$parameter['Gid']=$row->Gid;
				$parameter['GMid']=$row->GMid;
				$parameter['FWid']=$row->FWid;
				$parameter['BetGold']=$row->BetGold;
				$parameter['RealBetPoint']=$row->RealBetPoint;
				$parameter['WinGold']=$row->WinGold;
				$parameter['Fee']=$row->Fee;
				$parameter['FGold']=$row->FGold;
				$parameter['PublicPoint']=$row->PublicPoint;
				$parameter['Result']=$row->Result;
				$parameter['IP']=$row->IP;
				$parameter['Cry']=$row->Cry;
				$parameter['CryDef']=$row->CryDef;
				$parameter['UserType']=$row->UserType;
				$parameter['mem_num']=$mem_num;
				$parameter['u_power4']=$u_power4;
				$parameter['u_power5']=$u_power5;
				$parameter['u_power6']=$u_power6;
				$parameter['u_power4_profit']=$u_power4_profit;
				$parameter['u_power5_profit']=$u_power5_profit;
				$parameter['u_power6_profit']=$u_power6_profit;					
				$this->webdb->sqlReplace('water_report',$parameter);
				
			}	
		}
	}
	
}

?>