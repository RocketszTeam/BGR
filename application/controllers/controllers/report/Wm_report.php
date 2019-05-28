<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
error_reporting(0);
class Wm_report extends Core_controller{
	public function __construct(){ 
		parent::__construct();
		$this->load->library('api/wmapi');
		date_default_timezone_set("Asia/Taipei");
	}
	//補帳用 sTime起始時間 eTime 終止時間 相差頂多1小時
	public function index(){
		$this->get_report("20180625000000","20180625235959");
		
	}


	public function get_report($sdate=NULL,$edate=NULL){
		if(!isset($sdate)){
			//YmdHis
			$edate = date('YmdHis');
			$sdate = date('YmdHis',strtotime($edate . "-15 minutes"));
		} else {
			$edate = date('YmdHis',strtotime($edate));
			$sdate = date('YmdHis',strtotime($sdate));			
		}
		//echo $sdate."~".$edate."<br>";
		$result=$this->wmapi->reporter_all($sdate,$edate);
		$rate = 1;
		
		//print_r($result);
		
		
		$uid = "";
		if(isset($result) && count($result) > 0){	//執行成功且有帳回來，先取出會員編號配u_id
			foreach($result as $row){
				if( !strpos(" ".$uid, "'".$row->user."'") ){
					$uid .= "'".$row->user."',";			
				}
			}
		}
		
		$uid = substr($uid,0,strlen($uid)-1);		
		
		if(isset($result) && count($result) > 0){	//執行成功且有帳回來
			$idlist = array();
					
			$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=13";	
			
			$rowAll=$this->webdb->sqlRowList($sqlStr);	
			$adminsql = "SELECT num,root,percent from admin";
			$AdminList = $this->webdb->sqlRowList($adminsql);					
					
			foreach($result as $row){
				//寫入DB
				//if($this->check_betnum($row->BetID)){	//資料庫內沒有紀錄才新增
					//echo 'db:'.$row->id ;
					//取出會員代理總代代理編號	
					/*
					$sqlStra="select mem_num from `games_account` where u_id='".$row->user."' and gamemaker_num=13";	
					$row_mem=$this->webdb->sqlRow($sqlStra);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					*/

				
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
						if(strtolower($rowAll[$i]["u_id"]) == strtolower($row->user)){
							$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
							$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
							break;
						}
					}					
					
					//代理分潤、總代編號
					for($i=0; $i<count($AdminList); $i++){
						if($AdminList[$i]["num"] == $u_power6){
							$u_power6_profit = round( (float)$row->winLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
							$u_power5 = $AdminList[$i]["root"];	
							break;
						}
					}	
					
					//總代分潤、股東編號
					for($i=0; $i<count($AdminList); $i++){
						if($AdminList[$i]["num"] == $u_power5){
							$u_power5_profit = round( (float)$row->winLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
							$u_power4 = $AdminList[$i]["root"];	
							break;
						}
					}
					//股東分潤
					for($i=0; $i<count($AdminList); $i++){
						if($AdminList[$i]["num"] == $u_power4){
							$u_power4_profit = round( (float)$row->winLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
							break;
						}
					}
					$ValidAmount=$row->validbet;



					$parameter=array();
					$colSql = "user,betid,betTime,bet,validbet,water,result,betResult,waterbet,";
					$colSql .= "winLoss,gid,event,eventChild,tableId,gameResult,gname,mem_num,";
					$colSql .= "u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";

					//判斷紀錄是否存在
					/*
					$sqlStr="select `betId` from `WM_report` where `betId`=?";	
					$rowrecord=$this->webdb->sqlRow($sqlStr,array(':betId'=>$row->betId));
					*/
					$sqlStrs="REPLACE INTO `wm_report` (".sqlInsertString($colSql,0	).")";
					
				

					$sqlStrs.=" VALUES (".sqlInsertString($colSql,1).")";
					//echo $sqlStr ;
					$parameter[':user']=$row->user;
					$parameter[':betid']=$row->betId;
					$parameter[':betTime']=$row->betTime;
					$parameter[':bet']=$row->bet;
					$parameter[':validbet']=$row->validbet;
					$parameter[':water']=$row->water;
					$parameter[':result']=$row->result;
					$parameter[':betResult']=$row->betResult;
					$parameter[':waterbet']=$row->waterbet;
					$parameter[':winLoss']=$row->winLoss;

					$parameter[':gid']=$row->gid;
					$parameter[':event']=$row->event;
					$parameter[':eventChild']=$row->eventChild;
					$parameter[':tableId']=$row->tableId;
					$parameter[':gameResult']=$row->gameResult;
					$parameter[':gname']=$row->gname;

					$parameter[':mem_num']=$mem_num;

					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;	
					//echo 'sqlStr:'.$sqlStr ;				
					$this->webdb->sqlExc($sqlStrs,$parameter);
					//echo $sqlStr ;

			}	
		}


	}
	
	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$eTime=date('YmdHis',strtotime($this->input->post('eTime',true)) );
				$sTime=date('YmdHis',strtotime($this->input->post('sTime',true)) );				
				
				$report_count=$this->get_report($sTime,$eTime);				
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