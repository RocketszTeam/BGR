<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Avia_report extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('api/avia');
		date_default_timezone_set("Asia/Taipei");
	}
	
	//透過下注時間去補前日的單據
	public function getDayreport(){
		$sTime=date('Y-m-d',strtotime("-1 day")).' 00:00:00';
		$eTime=date('Y-m-d',strtotime("-1 day")).' 23:59:59';
		$this->get_report($sTime,$eTime,100,1,$type='CreateAt');	
	}
	
	public function get_report($sTime=NULL,$eTime=NULL,$PageSize=100,$PageNum=1,$type='UpdateAt'){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-50 min"));
		}
		$result=$this->avia->reporter_all($sTime,$eTime,$PageSize,$PageNum,$type);
		//$this->intoDB($result);
		$uid = "";
		if(isset($result)){
			if(count($result->list) > 0){
				
				$maxpage = $result->RecordCount % $result->PageSize == 0 ? $result->RecordCount/$result->PageSize : floor($result->RecordCount/$result->PageSize)+1; //計算總頁數
				foreach($result->list as $row){
					if( !strpos(" ".$uid, "'".$row->UserName."'") ){
						$uid .= "'".$row->UserName."',";			
					}
				}
				$uid = substr($uid,0,strlen($uid)-1);
				if(count($result->list) && strlen($uid) > 1){
					$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=35";
					
					$rowAll=$this->webdb->sqlRowList($sqlStr);
					$adminsql = "SELECT num,root,percent from admin";
					$AdminList = $this->webdb->sqlRowList($adminsql);
					if($rowAll!=NULL){
						foreach($result->list as $row){
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
								if(strcasecmp($rowAll[$i]["u_id"],$row->UserName) == 0){	//因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
									$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
									$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
									break;
								}
							}					
							//代理分潤、總代編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power6){
									$u_power6_profit = round( (float)$row->Money * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power5 = $AdminList[$i]["root"];	
									break;
								}
							}	
							//總代分潤、股東編號
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power5){
									$u_power5_profit = round( (float)$row->Money * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									$u_power4 = $AdminList[$i]["root"];	
									break;
								}
							}
							//股東分潤
							for($i=0; $i<count($AdminList); $i++){
								if($AdminList[$i]["num"] == $u_power4){
									$u_power4_profit = round( (float)$row->Money * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
									break;
								}
							}
							//寫入DB
							$parameter['OrderID']=$row->OrderID;
							$parameter['UserName']=$row->UserName;
							$parameter['Category']=$row->Category;
							$parameter['League']=$row->League;
							$parameter['Match']=$row->Match;
							$parameter['Bet']=$row->Bet;
							$parameter['Content']=$row->Content;
							$parameter['Result']=$row->Result;
							$parameter['BetAmount']=$row->BetAmount;
							$parameter['Money']=$row->Money;
							$parameter['Status']=$row->Status;
							$parameter['CreateAt']=date('Y-m-d H:i:s',strtotime($row->CreateAt));
							$parameter['UpdateAt']=date('Y-m-d H:i:s',strtotime($row->UpdateAt));
							$parameter['StartAt']=date('Y-m-d H:i:s',strtotime($row->StartAt));
							$parameter['EndAt']=date('Y-m-d H:i:s',strtotime($row->EndAt));
							$parameter['mem_num']=$mem_num;
							$parameter['u_power4']=$u_power4;
							$parameter['u_power5']=$u_power5;
							$parameter['u_power6']=$u_power6;
							$parameter['u_power4_profit']=$u_power4_profit;
							$parameter['u_power5_profit']=$u_power5_profit;
							$parameter['u_power6_profit']=$u_power6_profit;					
							$this->webdb->sqlReplace('avia_report',$parameter);
						}
					}
				}
				//繼續往下撈分頁
				if($PageNum < $maxpage){
					$this->get_report($sTime,$eTime,$PageSize,($PageNum + 1),$type);
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
					$report_count=$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true),100,1,'CreateAt');
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