<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Checkorder extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this -> load -> model("sysAdmin/webdb_model", "webdb", true);
	}
	
	
	public function index(){
		if($this->input->is_ajax_request()){
			$dataList=array();
			$i=0;
			//檢查會員提款
			$sqlStr="select * from `member_sell` where `keyin1`=0";
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			if($rowAll!=NULL){
				$rowCount=count($rowAll);
				$rowCell=array();
				$rowCell['objID']="order_sell";
				$rowCell['rowCount']=$rowCount;
				$rowCell['details']=array();
				foreach($rowAll as $keys=>$row){
					$details=array();
					$details['order_no']=$row["order_no"];
					$details['no_sound']=$row["no_sound"];	
					$rowCell['details'][$keys]=$details;
				}
				$rowCell['rowTitle']='寶物拋售';
				$rowCell['link']='sysAdmin/Order/sell';	
				$dataList[]=$rowCell;
				$i++;
			}
			
			/*
			//檢查會員點數轉移
			$sqlStr="select * from `member_transfer` where `keyin1`=0";
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			if($rowAll!=NULL){
				$rowCount=count($rowAll);
				$rowCell=array();
				$rowCell['objID']="order_transfer";
				$rowCell['rowCount']=$rowCount;
				$rowCell['details']=array();
				foreach($rowAll as $keys=>$row){
					$details=array();
					$details['order_no']=$row["order_no"];
					$details['no_sound']=$row["no_sound"];	
					$rowCell['details'][$keys]=$details;
				}
				$rowCell['rowTitle']='點數轉移';
				$rowCell['link']='sysAdmin/Order/transfer';	
				$dataList[]=$rowCell;
				$i++;
			}
			*/
			
			//檢查銀行匯款
			$sqlStr="select * from `member_bank_transfer` where `keyin2`=0";
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			if($rowAll!=NULL){
				$rowCount=count($rowAll);
				$rowCell=array();
				$rowCell['objID']="bank_transfer";
				$rowCell['rowCount']=$rowCount;
				$rowCell['details']=array();
				foreach($rowAll as $keys=>$row){
					$details=array();
					$details['order_no']=$row["order_no"];
					$details['no_sound']=$row["no_sound"];	
					$rowCell['details'][$keys]=$details;
				}
				$rowCell['rowTitle']='銀行匯款';
				$rowCell['link']='sysAdmin/Order/bank';	
				$dataList[]=$rowCell;
				$i++;
			}
			
			//檢查會員留言
			$sqlStr="select * from `member_talk` where (`re_word` is null or re_word='')";
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			if($rowAll!=NULL){
				$rowCount=count($rowAll);
				$rowCell=array();
				$rowCell['objID']="member_talk";
				$rowCell['rowCount']=$rowCount;
				$rowCell['details']=array();
				foreach($rowAll as $keys=>$row){
					$details=array();
					$details['order_no']=$row["num"];
					$details['no_sound']=$row["no_sound"];	
					$rowCell['details'][$keys]=$details;
				}				
				$rowCell['rowTitle']='會員提問';
				$rowCell['link']='sysAdmin/Message/index';	
				$dataList[]=$rowCell;
				$i++;
			}
			
			/*
			//檢查黃金帳號是否不足
			$sqlStr="select * from `games_account` where `gamemaker_num`=6 and `mem_num` is null";
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			if($rowAll==NULL){	//有資料代表還有帳號 沒資料代表帳號用光了
				$rowCount=1;
				$rowCell=array();
				$rowCell['objID']="glod_account";
				$rowCell['rowCount']=$rowCount;
				$rowCell['details']=array();
				$details=array();
				$details['order_no']=1;
				$details['no_sound']=0;	
				$rowCell['details'][0]=$details;
				$rowCell['rowTitle']='黃金帳號不足';
				$rowCell['link']='sysAdmin/Member/gold_index';	
				$dataList[]=$rowCell;
				$i++;
			}
			*/
			echo json_encode($dataList);
		}
	}
	
	
}

?>