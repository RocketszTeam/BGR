<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hces888_report extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('api/hces888');
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function index(){
		$sTime='2018-07-10 00:00:00';
		$eTime='2018-07-10 23:00:00';
		$this->get_report($sTime,$eTime);
	}
	
	//透過下注時間補單據
	public function get_report($sTime=NULL,$eTime=NULL,$type=1,$cursor=NULL){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-50 min"));
		}
		$result=$this->hces888->reporter_all($sTime,$eTime,$type,$cursor);
		//print_r($result);exit;
		//$this->intoDB($result);
		$uid = "";
		if(isset($result)){
			if(count($result->data) > 0){
				foreach($result->data as $row){
					//回傳帳號需要去掉尾墜
					$user_name=explode('@',$row->user_name);
					$user_name=reset($user_name);
					if( !strpos(" ".$uid, "'".$user_name."'") ){
						$uid .= "'".$user_name."',";			
					}
				}
				$uid = substr($uid,0,strlen($uid)-1);
				if(count($result->data) && strlen($uid) > 1){
					$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=36";
					
					$rowAll=$this->webdb->sqlRowList($sqlStr);
					$adminsql = "SELECT num,root,percent from admin";
					$AdminList = $this->webdb->sqlRowList($adminsql);
					if($rowAll!=NULL){
						foreach($result->data as $row){
							$u_power4 = 0;
							$u_power5 = 0;
							$u_power6 = 0;
							$u_power4_profit=0;
							$u_power5_profit=0;
							$u_power6_profit=0;	
							$mem_num=0;
							
							//回傳帳號需要去掉尾墜
							$user_name=explode('@',$row->user_name);
							$user_name=reset($user_name);
							
							$winOrLoss=0;	//預設輸贏=0
							if(isset($row->returnamount)){	//無派彩金額 代表未結單 有派彩金額才算出輸贏金額
								$winOrLoss=(float)$row->returnamount - (float)$row->betamount;
							}
							
							
							//取出會員代理總代代理編號	
							for($i=0; $i<count($rowAll); $i++){	
								//$this->data[$k]=$v;
								if(strcasecmp($rowAll[$i]["u_id"],$user_name) == 0){	//因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
									$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
									$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
									break;
								}
							}					
							//代理分潤、總代編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power6){
									$u_power6_profit = round( (float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power5 = $AdminList[$i]["root"];	
									break;
								}
							}	
							//總代分潤、股東編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power5){
									$u_power5_profit = round( (float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power4 = $AdminList[$i]["root"];	
									break;
								}
							}
							//股東分潤
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power4){
									$u_power4_profit = round( (float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									break;
								}
							}
							//寫入DB
							$parameter['bet_id']=$row->bet_id;
							$parameter['user_name']=$row->user_name;
							$parameter['agent_id']=$row->agent_id;
							$parameter['agent_name']=$row->agent_name;
							$parameter['betip']=$row->betip;
							$parameter['betsrc']=$row->betsrc;
							$parameter['part_id']=$row->part_id;
							$parameter['part_odds']=$row->part_odds;
							$parameter['bettime']=$row->bettime;
							$parameter['betamount']=$row->betamount;
							$parameter['returnamount']=(isset($row->returnamount) ? $row->returnamount : NULL);
							$parameter['winOrLoss']=$winOrLoss;
							$parameter['refundamount']=(isset($row->refundamount) ? $row->refundamount : NULL);
							$parameter['game_id']=$row->game_id;
							$parameter['game_name']=$row->game_name;
							$parameter['match_name']=$row->match_name;
							$parameter['race_name']=$row->race_name;
							$parameter['han_name']=$row->han_name;
							$parameter['part_name']=$row->part_name;
							$parameter['team1_name']=$row->team1_name;
							$parameter['team2_name']=$row->team2_name;
							$parameter['round']=$row->round;
							$parameter['result']=(isset($row->result) ? $row->result : NULL);
							$parameter['reckondate']=(isset($row->reckondate) ? $row->reckondate : NULL);
							$parameter['result_name']=(isset($row->result_name) ? $row->result_name : NULL);
							$parameter['han_info']=$row->han_info;
							$parameter['bet_detail']=$row->bet_detail;
							$parameter['status2']=$row->status2;
							$parameter['mem_num']=$mem_num;
							$parameter['u_power4']=$u_power4;
							$parameter['u_power5']=$u_power5;
							$parameter['u_power6']=$u_power6;
							$parameter['u_power4_profit']=$u_power4_profit;
							$parameter['u_power5_profit']=$u_power5_profit;
							$parameter['u_power6_profit']=$u_power6_profit;	
							
							if($type==0){	//已結算注單撈取複寫原本的
								$this->webdb->sqlReplace('hces888_report',$parameter);
							}else{	//未結算單據則不複寫
								$this->webdb->sqlReplace('hces888_report',$parameter);
								//$this->webdb->sqlIgnore('hces888_report',$parameter);
							}
						}
					}
				}
				//繼續往下撈分頁
				if(count($result->data) > $result->page_size && isset($result->cursor)){
					$this->get_report($sTime,$eTime,$type,$result->cursor);
				}
			}
		}
		

	}
	
	
	
	//透過結算時間去補單據
	public function update_report($sTime=NULL,$eTime=NULL,$type=0,$cursor=NULL){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-60 min"));
		}
		$result=$this->get_report($sTime,$eTime,$type,$cursor);
	}
	
	
	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('sTime')!='' && $this->input->post('eTime')!=''){
					date_default_timezone_set("Asia/Taipei");
					$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true));	//先撈未結單
					$this->update_report($this->input->post('sTime',true),$this->input->post('eTime',true));	//在撈已結單
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