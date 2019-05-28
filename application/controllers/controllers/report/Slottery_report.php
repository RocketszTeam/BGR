<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Slottery_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function get_report($sTime=NULL,$eTime=NULL){
		if($sTime==NULL && $eTime==NULL){
			$sTime=date('Y-m-d');
			$eTime=$sTime;
		}
		$result=$this->slotteryapi->reporter_all($sTime,$eTime);
		$total_row=0;
		if(isset($result) && count($result)){	//執行成功且有帳回來
			foreach($result as $row){
				if($row->level=='1'){	//level=1 代表會員的帳
					$total_row++;
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->account."' and gamemaker_num=20";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$u_power4_profit=round((float)($row->up_no2_rake - $row->up_no1_rake) * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)($row->up_no2_rake - $row->up_no1_rake) * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)($row->up_no2_rake - $row->up_no1_rake) * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					if($this->check_betnum($row->Bet_date,$row->account,$row->game_id)){	//帳目不存在就寫入，否則就更新帳目
						$parameter=array();
						$colSql="Bet_date,account,game_id,ccount,cmount,bmount,m_result,up_no1_result,up_no2_result,up_no1_rake,up_no2_rake,up_no1,up_no2,update_time";
						$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
						$sqlStr="REPLACE INTO `slottery_report` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[':Bet_date']=$row->Bet_date;
						$parameter[':account']=$row->account;
						$parameter[':game_id']=$row->game_id;
						$parameter[':ccount']=$row->ccount;
						$parameter[':cmount']=$row->cmount;
						$parameter[':bmount']=$row->bmount;
						$parameter[':m_result']=$row->m_result;
						$parameter[':up_no1_result']=$row->up_no1_result;
						$parameter[':up_no2_result']=$row->up_no2_result;
						$parameter[':up_no1_rake']=$row->up_no1_rake;
						$parameter[':up_no2_rake']=$row->up_no2_rake;
						$parameter[':up_no1']=$row->up_no1;
						$parameter[':up_no2']=$row->up_no2;
						$parameter[':update_time']=now();
						$parameter[':mem_num']=$mem_num;
						$parameter[':u_power4']=$u_power4;
						$parameter[':u_power5']=$u_power5;
						$parameter[':u_power6']=$u_power6;
						$parameter[':u_power4_profit']=$u_power4_profit;
						$parameter[':u_power5_profit']=$u_power5_profit;
						$parameter[':u_power6_profit']=$u_power6_profit;					
						$this->webdb->sqlExc($sqlStr,$parameter);
					}else{
						$parameter=array();
						$colSql="ccount,cmount,bmount,m_result,up_no1_result,up_no2_result,up_no1_rake,up_no2_rake,up_no1,up_no2,update_time";
						$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
						$sqlStr="UPDATE `slottery_report` SET ".sqlUpdateString($colSql);
						$parameter[':ccount']=$row->ccount;
						$parameter[':cmount']=$row->cmount;
						$parameter[':bmount']=$row->bmount;
						$parameter[':m_result']=$row->m_result;
						$parameter[':up_no1_result']=$row->up_no1_result;
						$parameter[':up_no2_result']=$row->up_no2_result;
						$parameter[':up_no1_rake']=$row->up_no1_rake;
						$parameter[':up_no2_rake']=$row->up_no2_rake;
						$parameter[':up_no1']=$row->up_no1;
						$parameter[':up_no2']=$row->up_no2;
						$parameter[':update_time']=now();
						$parameter[':mem_num']=$mem_num;
						$parameter[':u_power4']=$u_power4;
						$parameter[':u_power5']=$u_power5;
						$parameter[':u_power6']=$u_power6;
						$parameter[':u_power4_profit']=$u_power4_profit;
						$parameter[':u_power5_profit']=$u_power5_profit;
						$parameter[':u_power6_profit']=$u_power6_profit;
						
						$sqlStr.=" where `Bet_date`=? and `account`=? and `game_id`=?";
						$parameter[':Bet_date']=$row->Bet_date;
						$parameter[':account']=$row->account;
						$parameter[':game_id']=$row->game_id;
						$this->webdb->sqlExc($sqlStr,$parameter);
					}
				}
			}
		}
		return $total_row;
	}
	
	public function update_report($sTime=NULL,$eTime=NULL){	//報表自動更新排程
		if($sTime==NULL && $eTime==NULL){
			$sTime=date('Y-m-d');
			$eTime=$sTime;
		}
		$this->slotteryapi->reporter_all($sTime,$eTime);
	}
	
	
	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('sTime')!='' && $this->input->post('eTime')!=''){
					date_default_timezone_set("Asia/Taipei");
					$sTime=date('Y-m-d',strtotime($this->input->post('sTime',true)));
					$eTime=date('Y-m-d',strtotime($this->input->post('eTime',true)));
					$report_count=$this->get_report($sTime,$eTime);
					echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！共更新了'. $report_count .'筆帳目','data_count'=> $report_count));
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'請設定補帳日期'));
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'網域不被允許'));	
		}
	}
	
	private function check_betnum($Bet_date,$account,$game_id){	//檢查此筆資料是否已經存在
		$parameter=array();
		$sqlStr="select * from `slottery_report` where `Bet_date`=? and `account`=? and `game_id`=?";	
		$parameter[':Bet_date']=$Bet_date;
		$parameter[':account']=$account;
		$parameter[':game_id']=$game_id;
		$row=$this->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}


}
?>