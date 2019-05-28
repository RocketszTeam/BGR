<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Service extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		//定義星期
		$this->data["weekList"]=array('1_星期一','2_星期二','3_星期三','4_星期四','5_星期五','6_星期六','0_星期日');
		
		$this->data["openFind"]="N";//是否啟用搜尋
	}
	
	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `game_makers` where `online`=1";
		
		//---狀態-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `active`=?";
			$parameter[":active"]=@$_REQUEST["find4"];
		}
		//開放時間
		if(!empty($_REQUEST['find5'])){
			$sqlStr.=" and `selltime1` >=?";
			$parameter[":selltime1"]=@$_REQUEST["find5"];
		}
		if(!empty($_REQUEST['find6'])){			
			$sqlStr.=" and `selltime2` <=?";
			$parameter[":selltime2"]=@$_REQUEST["find6"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Service/index?".$this->data["att"];//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=15;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=0;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `range`  LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();

		
		

		
		$this->data["editBTN"]=SYSTEM_URL."/Service/edit/";
							
		$this -> data["body"] = $this -> load -> view("admin/service/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	
	
	function edit($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `game_makers` where `online`=1 and  num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Service/index");
		}else{		
			if($_POST){	//修改資料	
				
				
				
				$parameter=array();
				$sqlStr="UPDATE `game_makers` SET `active`=?";
				$parameter[":active"]=($this->input->post("active")=='Y'?'Y':'N');
				if($this->input->post('sDate1') && $this->input->post('sTime1')){
					$selltime1=	$this->input->post('sDate1',true).str_replace(':','',$this->input->post('sTime1',true));
					$sqlStr.=",`selltime1`=?";
					$parameter[":selltime1"]=$selltime1;
				}else{
					$sqlStr.=",`selltime1`=?";
					$parameter[":selltime1"]=NULL;
				}
				if($this->input->post('sDate2') && $this->input->post('sTime2')){
					$selltime2=	$this->input->post('sDate2',true).str_replace(':','',$this->input->post('sTime2',true));
					$sqlStr.=",`selltime2`=?";
					$parameter[":selltime2"]=$selltime2;
				}else{
					$sqlStr.=",`selltime2`=?";
					$parameter[":selltime2"]=NULL;
				}
				
				$sqlStr.=" where num=?";
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/service/index");
				
			}else{
				
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/Service/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Service/index");
				$this->data["todo"]="edit";
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/service/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	

	
	

	
	//AJAX改變訂單狀態
	function keyChange(){
		if($this->input->is_ajax_request()){
			$num=trim($this->input->post("num"));//table
			$value=trim($this->input->post("value"));//table
			if($num!='' && $value!=''){
				$parameter=array();
				$sqlStr="UPDATE `game_makers` SET `active`=? where num=?";
				$parameter[":active"]=$value;
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
			}
		}
	}
		
	//AJAX改變訂單狀態
	function keyChange_trans(){
		if($this->input->is_ajax_request()){
			$num=trim($this->input->post("num"));//table
			$value=trim($this->input->post("value"));//table
			$type=trim($this->input->post("type"));//table
			if($num!='' && $value!=''){
				$parameter=array();
				$sqlStr="UPDATE `game_makers` SET `deposit`=? where num=?";
				if($type == 2){
					$sqlStr="UPDATE `game_makers` SET `withdraw`=? where num=?";
				}
				$parameter[":active"]=$value;
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
			}
		}
	}

	
} 