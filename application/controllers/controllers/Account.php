<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Account extends Core_controller{

	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
		$this->load->library('orderclass');	//載入訂單函式庫

		$this->data["orderKeyin1"]=$this->orderclass->orderKeyin1();//處理情形
		$this->data["orderKeyin2"]=$this->orderclass->orderKeyin2();//收款情形
		$this->data["paymentType"]=array('ATM_ATM','CVS_超商代碼','FAMI_全家','HILIFEET_萊爾富','IBON_7-11','Credit_信用卡');
		//拋售情形
		$this->data["sellKeyin1"]=$this->orderclass->sellKeyin1();
	}

	//銀行匯款
	public function bank(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode($this -> uri -> uri_string()));
			exit;
		}else{
			$parameter=array();
			$sqlStr="select * from `member_bank_transfer` where `mem_num`=?";
			$parameter[':mem_num']=$this->memberclass->num();
			$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數

			$limit=5;//每頁筆數
			//init pagination
			$config['base_url'] = site_url("Account/bank");
			$config['per_page'] = $limit;
			$config['num_links'] = 2;
			$config['total_rows'] = $total;
			$config['page_query_string'] = TRUE;
			$config['query_string_segment']='page';
			$config['first_link'] = FALSE ;	//關閉顯示第一頁
			$config['last_link'] = FALSE;	//關閉顯示最末頁

			$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
			$nowpage=1;
			if (@$page!=""){$nowpage=$page;}
			if ($nowpage>$maxpage){$nowpage=$maxpage;}
			$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			$this -> data["nowpage"]=$nowpage;
			$this -> data["maxpage"]=$maxpage;
			$this -> data["result"] = $rowAll;

			//產生分頁連結
			$this -> load -> library("pagination");
			$this -> pagination -> doConfig2($config);
			$this -> data["pagination"] = $this -> pagination -> create_links();
			$this -> load -> view("www/account_bank", $this -> data);
		}
	}
} 
