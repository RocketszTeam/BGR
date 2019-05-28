<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pk10_report extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('api/pk10');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function index(){
		$sTime='2018-06-05 12:00:00';
		$eTime='2018-06-05 23:00:00';
		$this->get_report($sTime,$eTime);	
	}
	
	public function getDayreport(){
		$sTime=date('Y-m-d',strtotime("-1 day")).' 00:00:00';
		$eTime=date('Y-m-d',strtotime("-1 day")).' 23:59:59';
		$this->get_report($sTime,$eTime);	
	}
	
	public function get_report($sTime=NULL,$eTime=NULL,$Page=1,$Row=50){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-15 minute"));
		}
		$uid = "";
		$result=$this->pk10->reporter_all($sTime,$eTime,$Page);
		//print_r($result);
		if(isset($result->message)){
			if(count($result->message->bets) > 0){
				foreach($result->message->bets as $row){
					if( !strpos(" ".$uid, "'".$row->username."'")){
						$uid .= "'".$row->username."',";			
					}
				}
				$uid = substr($uid,0,strlen($uid)-1);
				if(count($result) && strlen($uid) > 1){
					$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=31";
					
					$rowAll=$this->webdb->sqlRowList($sqlStr);
					$adminsql = "SELECT num,root,percent from admin";
					$AdminList = $this->webdb->sqlRowList($adminsql);
					if($rowAll!=NULL){
						foreach($result->message->bets as $row){
							$u_power4 = 0;
							$u_power5 = 0;
							$u_power6 = 0;
							$u_power4_profit=0;
							$u_power5_profit=0;
							$u_power6_profit=0;	
							$mem_num=0;
							$row->win_lose = $row->bonus-$row->amount ;
							
							//取出會員代理總代代理編號	
							for($i=0; $i<count($rowAll); $i++){	
								//$this->data[$k]=$v;
								if(strcasecmp($rowAll[$i]["u_id"],$row->username) == 0){	//因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
									$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
									$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
									break;
								}
							}			
							//代理分潤、總代編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power6){
									$u_power6_profit = round( (float)$row->win_lose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power5 = $AdminList[$i]["root"];	
									break;
								}
							}	
							//總代分潤、股東編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power5){
									$u_power5_profit = round( (float)$row->win_lose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power4 = $AdminList[$i]["root"];	
									break;
								}
							}
							//股東分潤
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power4){
									$u_power4_profit = round( (float)$row->win_lose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									break;
								}
							}
							//寫入DB
							//echo 'mem_num:'.$mem_num ;
							$parameter=array();
							$parameter["id"]=$row->id;
							$parameter["wjorderId"]=$row->wjorderId;
							$parameter["username"]=$row->username;
							$parameter["actionNo"]=$row->actionNo;
							$parameter["actionTime"]=$row->actionTime;
							$parameter["playedId"]=$row->playedId;
							$parameter["name"]=$row->name;
							$parameter["type"]=$row->type;
							$parameter["actionData"]=$row->actionData;
							$parameter["beiShu"]=$row->beiShu;
							$parameter["mode"]=$row->mode;
							$parameter["amount"]=$row->amount;
							$parameter["zhuiHao"]=$row->zhuiHao;
							$parameter["actionNum"]=$row->actionNum;
							$parameter["WinorLoss"]=$row->win_lose;
							$parameter["lotteryNo"]=$row->lotteryNo;
							$parameter["bonus"]=$row->bonus;
							$parameter["isDelete"]=$row->isDelete;
							$parameter["kjTime"]=$row->kjTime;
							$parameter['mem_num']=$mem_num;
							$parameter['u_power4']=$u_power4;
							$parameter['u_power5']=$u_power5;
							$parameter['u_power6']=$u_power6;
							$parameter['u_power4_profit']=$u_power4_profit;
							$parameter['u_power5_profit']=$u_power5_profit;
							$parameter['u_power6_profit']=$u_power6_profit;					
							$this->webdb->sqlReplace('pk10_report',$parameter);
							
							
						}
					}
				}
				
				
				//繼續往下撈分頁
				if($Page < $result->pages){
					$this->get_report($sTime,$eTime,($Page+1),$Row);
				}
				
			}
		}
		
	}
	
	
	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('sTime')!='' && $this->input->post('eTime')!=''){
					$report_count=$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true),1,50);
					echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢'));
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