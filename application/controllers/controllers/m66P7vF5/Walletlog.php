<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Walletlog extends Core_controller{

	public function __construct(){
		parent::__construct();
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `member_wallet_log` where 1=1";
		//---來源----------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `source` = ?";	
			$parameter[":find1"]=@$_REQUEST["find1"];
		}
		//---目的----------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `target`=?";
			$parameter[":find2"]=@$_REQUEST["find2"];
		}
		//---狀態----------------------------
		if(@$_REQUEST["find3"]!=""){
			$sqlStr.=" and `status`=?";
			$parameter[":find3"]=@$_REQUEST["find3"];
		}
		//---會員帳號----------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `mem_num` in (select num from `member` where `u_id` like ?)";
			$parameter[":u_id"]="%".@$_REQUEST["find4"]."%";
		}
		//日期區間
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and buildtime >= ?";
			$parameter[":find7"]=@$_REQUEST["find7"];
		}
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and buildtime <  ?";
			$parameter[":find8"]=date('Y-m-d',strtotime($_REQUEST["find8"]."+1 day"));
		}
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Walletlog/index?".$this->data["att"];//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
							
		$this -> data["body"] = $this -> load -> view("admin/member/walletlog", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	


	
} 