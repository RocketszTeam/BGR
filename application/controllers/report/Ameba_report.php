<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Ameba_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this->load->library('api/ameba');	//Ameba
	}
	/*
	public function index(){
		$eTime=date('Y-m-d\TH:i:s+08:00');
		//$eTime=date('2017-12-29\T11:00:00+08:00');
		$sTime=date('Y-m-d\TH:i:s+08:00',strtotime($eTime."-15 minutes"));
		$this->get_report($sTime,$eTime);
	}
	*/

	public function get_report($sTime=NULL,$eTime=NULL){
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d\TH:i:s+08:00');
			$sTime=date('Y-m-d\TH:i:s+08:00',strtotime($eTime."-15 minutes"));
		}
		$result=$this->ameba->reporter_all($sTime,$eTime);
		//print_r($result);exit();
		if(isset($result)){	//執行成功且有帳回來
			if(count($result) > 0){	//有帳
				foreach($result as $row){
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->account_name."' and gamemaker_num=27";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$winOrLoss=0;	//預設輸贏金額
					$winOrLoss=(float)$row->payout_amt - (float)$row->bet_amt + (float)$row->jp_pc_win_amt ;//預設輸贏金額
					
					$u_power4_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					$parameter=array();
					$colSql="WagerID,WagerDate,MemberAccount,TypeCode,BetAmount,winOrLoss,PayOff,jp_pc_win_amt,jp_jc_win_amt";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `ameba_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':WagerID']=$row->round_id;
					$parameter[':WagerDate']=date('Y-m-d H:i:s',strtotime($row->completed_at));
					$parameter[':MemberAccount']=$row->account_name;
					$parameter[':TypeCode']=$row->game_id;
					$parameter[':BetAmount']=$row->bet_amt;
					$parameter[':winOrLoss']=$winOrLoss;
					$parameter[':PayOff']=$row->payout_amt;
					$parameter[':jp_pc_win_amt']=isset($row->jp_pc_win_amt) ? $row->jp_pc_win_amt : 0;
					$parameter[':jp_jc_win_amt']=isset($row->jp_jc_win_amt) ? $row->jp_jc_win_amt : 0;
					
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
	
	

	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$report_count=$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true));
				echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！'));
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'網域不被允許'));
		}
	}
	
	
	
	
}

?>