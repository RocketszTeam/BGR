<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Neweb extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->data["member_group"]=array('0_一般會員','1_黃金會員','2_白金會員');
		
		$this->data["openFind"]="N";//是否啟用搜尋
	}
	

	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `neweb` where 1 = 1";			
				
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Neweb/index?".$this->data["att"];//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=0;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `active` DESC,`num` LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();		
		
		$this->data["editBTN"]=SYSTEM_URL."/Neweb/edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Neweb/delete/";
		
							
		$this -> data["body"] = $this -> load -> view("admin/neweb/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	function create(){	//
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($_POST){	//		
			$parameter=array();
			$colSql="merchantnumber,code,next_amount,m_group,active,reg_time";
			$sqlStr="INSERT INTO `neweb` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":merchantnumber"]=$this->input->post("merchantnumber",true);
			$parameter[":code"]=$this->input->post("code",true);
			$parameter[":next_amount"]=$this->input->post("next_amount",true);
			$parameter[":m_group"]=$this->input->post("m_group",true);
			$parameter[":active"]=$this->input->post("active",true);
			$parameter[":reg_time"]=now();
			if(!$this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Neweb/index");
			}	
		}else{
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Neweb/create");	//表單送出連結
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Neweb/index");	
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/neweb/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){	//修改股東
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `neweb` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Neweb/index");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="merchantnumber,code,m_group,active,up_time";
				$sqlStr="UPDATE `neweb` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":merchantnumber"]=$this->input->post("merchantnumber",true);
				$parameter[":code"]=$this->input->post("code",true);
				$parameter[":m_group"]=$this->input->post("m_group",true);
				$parameter[":active"]=$this->input->post("active",true);
				$parameter[":up_time"]=now();
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Neweb/index");
				
			}else{
				
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱				
				$this->data["formAction"]=site_url(SYSTEM_URL."/Neweb/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Neweb/index");
				
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/neweb/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	function delete($num){ //股東刪除
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `neweb` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Neweb/index");
		}else{
			$sqlStr="delete from `neweb` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Neweb/index");
		}
		
	}
	
	
	
	public function keyChange(){
		if($this->input->is_ajax_request()){
			$parameter=array();
			$sqlStr="UPDATE `neweb` SET `active`=? where num=?";
			$parameter[":value"]=$this->input->post("value",true);
			$parameter[":num"]=$this->input->post("num",true);
			$this->webdb->sqlExc($sqlStr,$parameter);	
		}
	}

} 