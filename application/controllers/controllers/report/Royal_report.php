<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Royal_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		
	}
	
	
	public function get_report(){
		$result=$this->royalapi->reporter_all();
		//print_r($result);
		if(count($result) > 0){	//執行成功且有帳回來
			foreach($result as $row){
				//寫入DB
				if($this->check_Id($row->Id)){	//資料庫內沒有紀錄才新增
					
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->UserId."' and gamemaker_num=2";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$u_power4_profit=round($row->WinLost * ($this->get_percent($u_power4,$u_power5)),2);	//股東分潤
					$u_power5_profit=round($row->WinLost * ($this->get_percent($u_power5,$u_power6)),2);	//股東分潤
					$u_power6_profit=round($row->WinLost * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					
					$parameter=array();
					$colSql="Id,UserId,UserName,RoyalUserID,Ip,Datetime,GameId,ServerType,NoRun,NoActive";
					$colSql.=",MaHao,YaMa,StakeScore,WinLost,Odds,Active,OpenPai";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="INSERT INTO `royal_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':Id']=$row->Id;
					$parameter[':UserId']=$row->UserId;
					$parameter[':UserName']=$row->UserName;
					$parameter[':RoyalUserID']=$row->RoyalUserID;
					$parameter[':Ip']=$row->Ip;
					$parameter[':Datetime']=$row->Datetime;
					$parameter[':GameId']=$row->GameId;
					$parameter[':ServerType']=$row->ServerType;
					$parameter[':NoRun']=$row->NoRun;
					$parameter[':NoActive']=$row->NoActive;
					$parameter[':MaHao']=$row->MaHao;
					$parameter[':YaMa']=$row->YaMa;
					$parameter[':StakeScore']=$row->StakeScore;
					$parameter[':WinLost']=$row->WinLost;
					$parameter[':Odds']=$row->Odds;
					$parameter[':Active']=$row->Active;
					$parameter[':OpenPai']=$row->OpenPai;
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
	}
	
	
	
	
	
	private function check_Id($Id){	//檢查此筆資料是否已經存在
		$sqlStr="select `Id` from `royal_report` where `Id`=?";	
		$row=$this->webdb->sqlRow($sqlStr,array(':Id'=>$Id));
		if($row==NULL){
			return true;	
		}else{
			return false;
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