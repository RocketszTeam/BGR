<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Fish_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		
	}
	
	
	public function index(){
		//echo FISH_Agent;
	}
	
	
	public function get_report(){
		$eTime=now();
		$sTime=date('Y-m-d H:i:s',strtotime($eTime."-10 min"));
		//$sTime ='2017-08-01 21:40:00';
		//$eTime = '2017-08-01 21:49:59';
		//echo 'start:'.$sTime.'<br>';
		$result=$this->fishapi->reporter_all($sTime,$eTime);
		//echo 'end:'.$eTime.'<br>';
		print_r($result);
		//echo 'end';

		if(count($result) > 0){	//執行成功且有帳回來
			foreach($result as $row){
				foreach($row->details as $row2){
					//寫入DB
					//if($this->check_Id($row2->autoid)){	//資料庫內沒有紀錄才新增
						//取出會員代理總代代理編號	
						$sqlStr="select mem_num from `games_account` where u_id='".str_ireplace(FISH_Agent, '',$row->accountno)."' and gamemaker_num=4";	
						$row_mem=$this->webdb->sqlRow($sqlStr);
						$mem_num=$row_mem["mem_num"];	//取出會員編號
						$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
						$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
						$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
						
						$u_power4_profit=round((float)$row2->profit * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
						$u_power5_profit=round((float)$row2->profit * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
						$u_power6_profit=round((float)$row2->profit * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
						//echo str_ireplace(FISH_Agent, '',$row->accountno);
						//echo 'u_power4:'.tb_sql('percent','admin',$u_power4).'<br>';
						
						
						$parameter=array();
						$colSql="autoid,cuuency,accountno,bet,profit,bettimeStr,gameId";
						$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
						$sqlStr="REPLACE INTO `fish_report` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[':autoid']=$row2->autoid;
						$parameter[':cuuency']=$row->cuuency;
						$parameter[':accountno']=str_ireplace(FISH_Agent, '',$row->accountno);
						$parameter[':bet']=$row2->bet;
						$parameter[':profit']=$row2->profit;
						$parameter[':bettimeStr']=$row2->bettimeStr;
						$parameter[':gameId']=$row2->gameId;
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
	
	
	private function check_Id($autoid){	//檢查此筆資料是否已經存在
		$sqlStr="select `autoid` from `fish_report` where `autoid`=?";	
		$row=$this->webdb->sqlRow($sqlStr,array(':autoid'=>$autoid));
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}
}

?>