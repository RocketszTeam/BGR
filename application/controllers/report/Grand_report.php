<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Grand_report extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('api/grandapi');	//緯來(PG電子)
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function get_report($sTime=NULL,$eTime=NULL,$Page=1,$Row=200){
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-1 hour"));
		}
		$result=$this->grandapi->reporter_all($sTime,$eTime,$Page,$Row);
		//print_r($result);
		$uid = "";
		if(isset($result)){
			if(count($result->Record) > 0){
				foreach($result->Record as $row){
					if( !strpos(" ".$uid, "'".$row->account."'") ){
						$uid .= "'".$row->account."',";			
					}
				}
				$uid = substr($uid,0,strlen($uid)-1);
				if(count($result) && strlen($uid) > 1){
					$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=6";
					
					$rowAll=$this->webdb->sqlRowList($sqlStr);
					$adminsql = "SELECT num,root,percent from admin";
					$AdminList = $this->webdb->sqlRowList($adminsql);
					if($rowAll!=NULL){
						foreach($result->Record as $row){
							$u_power4 = 0;
							$u_power5 = 0;
							$u_power6 = 0;
							$u_power4_profit=0;
							$u_power5_profit=0;
							$u_power6_profit=0;	
							$mem_num=0;
							
							//取出會員代理總代代理編號	
							for($i=0; $i<count($rowAll); $i++){	
								//$this->data[$k]=$v;
								if(strcasecmp($rowAll[$i]["u_id"],$row->account) == 0){	//因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
									$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
									$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
									break;
								}
							}					
							//代理分潤、總代編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power6){
									$u_power6_profit = round( (float)$row->winlose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power5 = $AdminList[$i]["root"];	
									break;
								}
							}	
							//總代分潤、股東編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power5){
									$u_power5_profit = round( (float)$row->winlose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power4 = $AdminList[$i]["root"];	
									break;
								}
							}
							//股東分潤
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power4){
									$u_power4_profit = round( (float)$row->winlose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									break;
								}
							}
							//寫入DB
							$parameter['recordID']=$row->recordID;
							$parameter['account']=$row->account;
							$parameter['bdate']=$row->bdate;
							$parameter['gameID']=$row->gameID;
							$parameter['tablename']=$row->tablename;
							$parameter['step']=$row->step;
							$parameter['bureau']=$row->bureau;
							$parameter['bet']=$row->bet;
							$parameter['winlose']=$row->winlose;
							$parameter['prize']=$row->prize;
							$parameter['jpmode']=$row->jpmode;
							$parameter['jppoints']=$row->jppoints;
							$parameter['before']=$row->before;
							$parameter['after']=$row->after;
							$parameter['mem_num']=$mem_num;
							$parameter['u_power4']=$u_power4;
							$parameter['u_power5']=$u_power5;
							$parameter['u_power6']=$u_power6;
							$parameter['u_power4_profit']=$u_power4_profit;
							$parameter['u_power5_profit']=$u_power5_profit;
							$parameter['u_power6_profit']=$u_power6_profit;					
							$this->webdb->sqlReplace('grand_report',$parameter);
							
						}
					}
				}
				//繼續往下撈分頁
				if($Page < $result->Page ){
					$this->get_report($sTime,$eTime,($Page+1),$Row)	;
				}
			}
		}
	}
	
	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('sTime')!='' && $this->input->post('eTime')!=''){
					date_default_timezone_set("Asia/Taipei");
					$report_count=$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true));
					echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！'));
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
	

}

?>