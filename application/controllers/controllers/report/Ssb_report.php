<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Ssb_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}
	public function get_report($maxModId=0,$checked=0){
		
		if($maxModId==0){
			$sqlStr="select `mrid` from `ssb_report` order by `mrid` DESC Limit 1";
			$row=$this->webdb->sqlRow($sqlStr);
			if($row!=NULL){
				$maxModId=$row["mrid"];	
			}
		}
		
		$result=$this->ssbapi->reporter_all($maxModId,$checked);
		//print_r($result);
		if(isset($result)){	//執行成功且有帳回來
			if(count($result->wgs) > 0){	//有帳
				foreach($result->wgs as $row){
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->meusername1."' and gamemaker_num=21";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					
					$u_power4_profit=round((float)$row->meresult * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$row->meresult * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$row->meresult * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					
					$parameter=array();
					$colSql="id,mrid,pr,status,stats,mark,meid,meusername,meusername1,gold,gold_c,meresult,io";
					$colSql.=",result,gtype,rtype,g_title,r_title,orderdate,added_date,modified_date,detail_1";
					$colSql.=",bet_txt_1,bet_txt_2,subw";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `ssb_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':id']=$row->id;
					$parameter[':mrid']=$row->mrid;
					$parameter[':pr']=$row->pr;
					$parameter[':status']=$row->status;
					$parameter[':stats']=$row->stats;
					$parameter[':mark']=$row->mark;
					$parameter[':meid']=$row->meid;
					$parameter[':meusername']=$row->meusername;
					$parameter[':meusername1']=$row->meusername1;
					$parameter[':gold']=$row->gold;
					$parameter[':gold_c']=$row->gold_c;
					$parameter[':meresult']=$row->meresult;
					$parameter[':io']=$row->io;
					$parameter[':result']=$row->result;
					$parameter[':gtype']=$row->gtype;
					$parameter[':rtype']=$row->rtype;
					$parameter[':g_title']=$row->g_title;
					$parameter[':r_title']=$row->r_title;
					$parameter[':orderdate']=$row->orderdate;
					$parameter[':added_date']=$row->added_date;
					$parameter[':modified_date']=$row->modified_date;
					$parameter[':detail_1']=$row->detail_1;
					$parameter[':bet_txt_1']=(isset($row->bet_txt_1) ? $row->bet_txt_1 : NULL);	//非過關才有
					$parameter[':bet_txt_2']=(isset($row->bet_txt_2) ? $row->bet_txt_2 : NULL);//非過關才有
					$parameter[':subw']=(isset($row->subw) ? serialize($row->subw) : NULL);//過關才有
					
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
				//繼續往下撈分頁
				if($result->more==1){
					$this->get_report($result->maxModId,$checked);
				}
			}
		}
	}
	
	public function get_report2($maxModId=1,$checked=0){
		
		if($maxModId==0){
			$sqlStr="select `mrid` from `ssb_report_copy` order by `mrid` DESC Limit 1";
			$row=$this->webdb->sqlRow($sqlStr);
			if($row!=NULL){
				$maxModId=$row["mrid"];	
			}
		}
		
		$result=$this->ssbapi->reporter_all($maxModId,$checked);
		//print_r($result);
		if(isset($result)){	//執行成功且有帳回來
			if(count($result->wgs) > 0){	//有帳
				foreach($result->wgs as $row){
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->meusername1."' and gamemaker_num=21";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					
					$u_power4_profit=round((float)$row->meresult * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$row->meresult * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$row->meresult * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					
					$parameter=array();
					$colSql="id,mrid,pr,status,stats,mark,meid,meusername,meusername1,gold,gold_c,meresult,io";
					$colSql.=",result,gtype,rtype,g_title,r_title,orderdate,added_date,modified_date,detail_1";
					$colSql.=",bet_txt_1,bet_txt_2,subw";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `ssb_report_copy` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':id']=$row->id;
					$parameter[':mrid']=$row->mrid;
					$parameter[':pr']=$row->pr;
					$parameter[':status']=$row->status;
					$parameter[':stats']=$row->stats;
					$parameter[':mark']=$row->mark;
					$parameter[':meid']=$row->meid;
					$parameter[':meusername']=$row->meusername;
					$parameter[':meusername1']=$row->meusername1;
					$parameter[':gold']=$row->gold;
					$parameter[':gold_c']=$row->gold_c;
					$parameter[':meresult']=$row->meresult;
					$parameter[':io']=$row->io;
					$parameter[':result']=$row->result;
					$parameter[':gtype']=$row->gtype;
					$parameter[':rtype']=$row->rtype;
					$parameter[':g_title']=$row->g_title;
					$parameter[':r_title']=$row->r_title;
					$parameter[':orderdate']=$row->orderdate;
					$parameter[':added_date']=$row->added_date;
					$parameter[':modified_date']=$row->modified_date;
					$parameter[':detail_1']=$row->detail_1;
					$parameter[':bet_txt_1']=(isset($row->bet_txt_1) ? $row->bet_txt_1 : NULL);	//非過關才有
					$parameter[':bet_txt_2']=(isset($row->bet_txt_2) ? $row->bet_txt_2 : NULL);//非過關才有
					$parameter[':subw']=(isset($row->subw) ? serialize($row->subw) : NULL);//過關才有
					
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
				//繼續往下撈分頁
				if($result->more==1){
					$this->get_report2($result->maxModId,$checked);
				}
			}
		}
	}
	

	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$report_count=$this->get_report();
				echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！'));
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
	
}

?>