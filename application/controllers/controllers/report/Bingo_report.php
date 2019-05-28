<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bingo_report extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('api/bingo');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function index(){
		$sTime='2018-03-01 12:00:00';
		$eTime='2018-03-01 23:00:00';
		$this->get_report();	
	}
	
	public function getDayreport(){
		$sTime=date('Y-m-d',strtotime("-1 day")).' 00:00:00';
		$eTime=date('Y-m-d',strtotime("-1 day")).' 23:59:59';
		$this->get_report($sTime,$eTime);	
	}
	
	public function get_report($sTime=NULL,$eTime=NULL,$Page=1,$Row=100){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-30 minute"));
		}
		$uid = "";
		$result=$this->bingo->reporter_all($sTime,$eTime,$Page,$Row);
		//print_r($result);
		if(isset($result)){
			if($result->total > 0){
				$BetDate=array();
				foreach($result->data as $row){
					if( !strpos(" ".$uid, "'".$row->player->account."'") ){
						$uid .= "'".$row->player->account."',";			
					}
				}
				$uid = substr($uid,0,strlen($uid)-1);
				if(count($result) && strlen($uid) > 1){
					$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=28";
					
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
							
							//取出會員代理總代代理編號	
							for($i=0; $i<count($rowAll); $i++){	
								//$this->data[$k]=$v;
								if(strcasecmp($rowAll[$i]["u_id"],$row->player->account) == 0){	//因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
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
							$parameter=array();
							$parameter["serial_no"]=$row->serial_no;
							$parameter["bingo_no"]=$row->bingo_no;
							$parameter["bet_suit"]=$row->bet_suit;
							$parameter["bet_type_group"]=$row->bet_type_group;
							$parameter["numbers"]=(isset($row->numbers) ? $row->numbers : NULL);
							$parameter["account"]=$row->player->account;
							$parameter["bet"]=$row->bet;
							$parameter["real_bet"]=$row->real_bet;
							$parameter["win_lose"]=$row->win_lose;
							$parameter["real_rebate"]=$row->real_rebate;
							$parameter["bingo_type"]=$row->bingo_type;
							$parameter["bingo_odds"]=$row->bingo_odds;
							$parameter["result"]=$row->result;
							$parameter["status"]=$row->status;
							$parameter["created_at"]=$row->created_at;
							$parameter["updated_at"]=$row->updated_at;
							$parameter["root_serial_no"]=$row->root_serial_no;
							$parameter["root_created_at"]=$row->root_created_at;
							$parameter["duplicated"]=$row->duplicated;
							$parameter["results"]=(isset($row->results) ? serialize($row->results) : NULL);
							$parameter["history"]=(isset($row->history) ? serialize($row->history) : NULL);
							$parameter['mem_num']=$mem_num;
							$parameter['u_power4']=$u_power4;
							$parameter['u_power5']=$u_power5;
							$parameter['u_power6']=$u_power6;
							$parameter['u_power4_profit']=$u_power4_profit;
							$parameter['u_power5_profit']=$u_power5_profit;
							$parameter['u_power6_profit']=$u_power6_profit;					
							$this->webdb->sqlReplace('bingo_report',$parameter);
							if(!in_array(date('Ymd',strtotime($row->created_at)),$BetDate)){
								array_push($BetDate,date('Ymd',strtotime($row->created_at)));	
							}
							
						}
					}
				}
				
				//歸帳
				if(count($BetDate)){
					//$this->daily_report($BetDate);	
				}
				
				//繼續往下撈分頁
				if(isset($result->next_page_url)){
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
					$report_count=$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true));
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
	
	private function daily_report($betTime,$makers_num=28){
		if(is_array($betTime)){
			$betTime=implode(',',$betTime);	
		}
		$sqlStr="select DATE_FORMAT(created_at,'%Y%m%d') as BetDate,mem_num,count(*) as betCount,SUM(bet) as betAmount,SUM(real_bet) as validAmount,SUM(win_lose) as winOrLoss,u_power4,u_power5,u_power6,SUM(u_power4_profit)as u_power4_profit,SUM(u_power5_profit) as u_power5_profit,SUM(u_power6_profit) as u_power6_profit  from bingo_report GROUP BY BetDate,mem_num,u_power4,u_power5,u_power6 HAVING BetDate in (".$betTime.")";
		$rowAll=$this->webdb->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			//刪除歸帳報表內 今天的數據
			$delSql="DELETE from `daily_report` where `makers_num`=".$makers_num." and `betTime` in (".$betTime.")";	
			$this->webdb->sqlExc($delSql);
			//重新歸帳
			foreach($rowAll as $row){
				$parameter=array();
				$parameter["betTime"]=$row["BetDate"];
				$parameter["betAmount"]=round($row["betAmount"],2);
				$parameter["validAmount"]=round($row["validAmount"],2);
				$parameter["winOrLoss"]=round($row["winOrLoss"],2);
				$parameter["mem_num"]=$row["mem_num"];
				$parameter["betCount"]=$row["betCount"];
				$parameter["makers_num"]=$makers_num;
				$parameter["u_power4"]=$row["u_power4"];
				$parameter["u_power5"]=$row["u_power5"];
				$parameter["u_power6"]=$row["u_power6"];
				$parameter["u_power4_profit"]=round($row["u_power4_profit"],2);
				$parameter["u_power5_profit"]=round($row["u_power5_profit"],2);
				$parameter["u_power6_profit"]=round($row["u_power6_profit"],2);
				$this->db->insert('daily_report',$parameter);		
			}
		}
	}
	
	
}


?>