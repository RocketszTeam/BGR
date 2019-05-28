<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Super_report2 extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function get_reporto($sTime=NULL,$eTime=NULL){
		$this->output->enable_profiler(TRUE);
		
		$eTime=date('Y-m-d H:i:s');
		$sTime=date('Y-m-d H:i:s',strtotime($eTime."-1 day"));
		/*
		$sTime = date('2017-12-01 00:00:00');
		$eTime = date('2017-12-31 23:59:59');
		$sDate='2018-01-04 00:00:00';
		$eDate='2018-01-04 23:59:59';
		*/		
		$sTime = date('2018-01-01 00:00:00');
		$eTime = date('2018-01-05 23:59:59');
		
		$result=$this->superapi->reporter_all($sTime,$eTime);		
		//print_r($result);
		if(count($result)){	//執行成功且有帳回來
			foreach($result as $row){
				//寫入DB
				//if($this->check_betnum($row->sn)){	//資料庫內沒有紀錄才新增
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->m_id."' and gamemaker_num=8";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					echo $mem_num."|";
					
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$u_power4_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					

					$parameter=array();
					$colSql="sn,m_id,m_name,up_no1,up_no2,m_date,count_date,team_no,fashion,g_type";
					$colSql.=",league,gold,bet_gold,sum_gold,result_gold,main_team,visit_team,mv_set";
					$colSql.=",mode,chum_num,compensate,status,score1,score2,status_note,matter,end,now,detail";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `super_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':sn']=$row->sn;
					$parameter[':m_id']=$row->m_id;
					$parameter[':m_name']=$row->m_name;
					$parameter[':up_no1']=$row->up_no1;
					$parameter[':up_no2']=$row->up_no2;
					$parameter[':m_date']=$row->m_date;
					$parameter[':count_date']=$row->count_date;
					$parameter[':team_no']=$row->team_no;
					$parameter[':fashion']=$row->fashion;
					$parameter[':g_type']=$row->g_type;
					$parameter[':league']=$row->league;
					$parameter[':gold']=$row->gold;
					$parameter[':bet_gold']=$row->bet_gold;
					$parameter[':sum_gold']=$row->sum_gold;
					$parameter[':result_gold']=$row->result_gold;
					$parameter[':main_team']=$row->main_team;
					$parameter[':visit_team']=$row->visit_team;
					$parameter[':mv_set']=$row->mv_set;
					$parameter[':mode']=$row->mode;
					$parameter[':chum_num']=$row->chum_num;
					$parameter[':compensate']=$row->compensate;
					$parameter[':status']=$row->status;
					$parameter[':score1']=$row->score1;
					$parameter[':score2']=$row->score2;
					$parameter[':status_note']=$row->status_note;
					$parameter[':matter']=$row->matter;
					$parameter[':end']=$row->end;
					$parameter[':now']=$row->now;
					$parameter[':detail']=(isset($row->detail) ? serialize($row->detail) : NULL);
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
	
	
	public function get_report2($sTime=NULL,$eTime=NULL){
		$this->output->enable_profiler(TRUE);
		/*
		if($sTime==NULL && $eTime==NULL){
			$sTime=date('Y-m-d H:i:s');
			$eTime=date('Y-m-d H:i:s',strtotime($sTime."+1 day"));
		}	
		*/		
		$eTime=date('Y-m-d H:i:s');
		$sTime=date('Y-m-d H:i:s',strtotime($eTime."-1 day"));
		$sTime = date('2018-01-01 00:00:00');
		$eTime = date('2018-01-05 23:59:59');
			
		$result=$this->superapi->reporter_all($sTime,$eTime);
		//print_r($result);
		$uid = "";
		if(count($result)){	//執行成功且有帳回來，先取出會員編號配u_id
			foreach($result as $row){
				if( !strpos(" ".$uid, "'".$row->m_id."'") ){
					$uid .= "'".$row->m_id."',";			
				}
			}
		}
		$uid = substr($uid,0,strlen($uid)-1);	
		if(count($result) && strlen($uid) > 1){	
			$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=8";	
			
			$rowAll=$this->webdb->sqlRowList($sqlStr);	
			
			$adminsql = "SELECT num,root,percent from admin";
			$AdminList = $this->webdb->sqlRowList($adminsql);
			
			if(count($result) && $rowAll!=NULL){	//執行成功且有帳回來
				foreach($result as $row){
					$u_power4 = 0;
					$u_power5 = 0;
					$u_power6 = 0;
					$u_power4_profit=0;
					$u_power5_profit=0;
					$u_power6_profit=0;	
					//寫入DB
					//if($this->check_betnum($row->sn)){	//資料庫內沒有紀錄才新增
						//取出會員代理總代代理編號	
						for($i=0; $i<count($rowAll); $i++){	
							//$this->data[$k]=$v;
							if($rowAll[$i]["u_id"] == $row->m_id){
								$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
								$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
								//$i = count($rowAll)+1;
								break;
							}
						}
						
						echo $mem_num."|";
						
					
						
						//代理分潤、總代編號
						for($i=0; $i<count($AdminList); $i++){
							if($AdminList[$i]["num"] == $u_power6){
								$u_power6_profit = round( (float)$row->result_gold * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
								$u_power5 = $AdminList[$i]["root"];	
								break;
							}
						}	
						
						//總代分潤、股東編號
						for($i=0; $i<count($AdminList); $i++){
							if($AdminList[$i]["num"] == $u_power5){
								$u_power5_profit = round( (float)$row->result_gold * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
								$u_power4 = $AdminList[$i]["root"];	
								break;
							}
						}
						//股東分潤
						for($i=0; $i<count($AdminList); $i++){
							if($AdminList[$i]["num"] == $u_power4){
								$u_power4_profit = round( (float)$row->result_gold * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
								break;
							}
						}
								
						/*
						$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
						$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
						$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
						$u_power4_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
						$u_power5_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
						$u_power6_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
						*/
						$parameter=array();
						$colSql="sn,m_id,m_name,up_no1,up_no2,m_date,count_date,team_no,fashion,g_type";
						$colSql.=",league,gold,bet_gold,sum_gold,result_gold,main_team,visit_team,mv_set";
						$colSql.=",mode,chum_num,compensate,status,score1,score2,status_note,matter,end,now,detail";
						$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
						$sqlStr="REPLACE INTO `super_report` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[':sn']=$row->sn;
						$parameter[':m_id']=$row->m_id;
						$parameter[':m_name']=$row->m_name;
						$parameter[':up_no1']=$row->up_no1;
						$parameter[':up_no2']=$row->up_no2;
						$parameter[':m_date']=$row->m_date;
						$parameter[':count_date']=$row->count_date;
						$parameter[':team_no']=$row->team_no;
						$parameter[':fashion']=$row->fashion;
						$parameter[':g_type']=$row->g_type;
						$parameter[':league']=$row->league;
						$parameter[':gold']=$row->gold;
						$parameter[':bet_gold']=$row->bet_gold;
						$parameter[':sum_gold']=$row->sum_gold;
						$parameter[':result_gold']=$row->result_gold;
						$parameter[':main_team']=$row->main_team;
						$parameter[':visit_team']=$row->visit_team;
						$parameter[':mv_set']=$row->mv_set;
						$parameter[':mode']=$row->mode;
						$parameter[':chum_num']=$row->chum_num;
						$parameter[':compensate']=$row->compensate;
						$parameter[':status']=$row->status;
						$parameter[':score1']=$row->score1;
						$parameter[':score2']=$row->score2;
						$parameter[':status_note']=$row->status_note;
						$parameter[':matter']=$row->matter;
						$parameter[':end']=$row->end;
						$parameter[':now']=$row->now;
						$parameter[':detail']=(isset($row->detail) ? serialize($row->detail) : NULL);
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
		}//有資料uid
	}
	
	
	//更新報表
	public function update_report($sTime=NULL,$eTime=NULL){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d');
			$sTime=date('Y-m-d',strtotime($eTime."-1 day"));
		}else{
			
			$eTime=date('Y-m-d',strtotime($eTime."+1 day"));
		}
		$result=$this->superapi->reporter_all($sTime,$eTime);
		if(count($result)){	//執行成功且有帳回來
			foreach($result as $row){
				$sqlStr="select mem_num from `games_account` where u_id='".$row->m_id."' and gamemaker_num=8";	
				$row_mem=$this->webdb->sqlRow($sqlStr);
				$mem_num=$row_mem["mem_num"];	//取出會員編號
				$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
				$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
				$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
				
				$u_power4_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
				$u_power5_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
				$u_power6_profit=round((float)$row->result_gold * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
				$parameter=array();
				$colSql="sn,m_id,m_name,up_no1,up_no2,m_date,count_date,team_no,fashion,g_type";
				$colSql.=",league,gold,bet_gold,sum_gold,result_gold,main_team,visit_team,mv_set";
				$colSql.=",mode,chum_num,compensate,status,score1,score2,status_note,matter,end,now,detail";
				$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
				$sqlStr="REPLACE INTO `super_report` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':sn']=$row->sn;
				$parameter[':m_id']=$row->m_id;
				$parameter[':m_name']=$row->m_name;
				$parameter[':up_no1']=$row->up_no1;
				$parameter[':up_no2']=$row->up_no2;
				$parameter[':m_date']=$row->m_date;
				$parameter[':count_date']=$row->count_date;
				$parameter[':team_no']=$row->team_no;
				$parameter[':fashion']=$row->fashion;
				$parameter[':g_type']=$row->g_type;
				$parameter[':league']=$row->league;
				$parameter[':gold']=$row->gold;
				$parameter[':bet_gold']=$row->bet_gold;
				$parameter[':sum_gold']=$row->sum_gold;
				$parameter[':result_gold']=$row->result_gold;
				$parameter[':main_team']=$row->main_team;
				$parameter[':visit_team']=$row->visit_team;
				$parameter[':mv_set']=$row->mv_set;
				$parameter[':mode']=$row->mode;
				$parameter[':chum_num']=$row->chum_num;
				$parameter[':compensate']=$row->compensate;
				$parameter[':status']=$row->status;
				$parameter[':score1']=$row->score1;
				$parameter[':score2']=$row->score2;
				$parameter[':status_note']=$row->status_note;
				$parameter[':matter']=$row->matter;
				$parameter[':end']=$row->end;
				$parameter[':now']=$row->now;
				$parameter[':detail']=(isset($row->detail) ? serialize($row->detail) : NULL);
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
		return count($result);
	}
	
	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('sTime')!='' && $this->input->post('eTime')!=''){
					date_default_timezone_set("Asia/Taipei");
					$report_count=$this->update_report($this->input->post('sTime',true),$this->input->post('eTime',true));
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
	
	private function check_betnum($sn){	//檢查此筆資料是否已經存在
		$sqlStr="select `sn` from `super_report` where `sn`=?";	
		$row=$this->webdb->sqlRow($sqlStr,array(':sn'=>$sn));
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}
}

?>