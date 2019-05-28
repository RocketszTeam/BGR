<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Heroes extends Core_controller{
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
		$sqlStr="select * from `heroes` where 1=1";
	
		//---玩家名稱-------------------------------
		if(@$_REQUEST["find1"]!=""){
			$sqlStr.=" and `heroes_name` like ?";
			$parameter[":heroes_name"]="%".@$_REQUEST["find3"]."%";
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
		$config['base_url'] = base_url().SYSTEM_URL."/Heroes/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `range`,num desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();


		
		$this->data["editBTN"]=SYSTEM_URL."/Heroes/edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Heroes/delete/";
							
		$this -> data["body"] = $this -> load -> view("admin/heroes/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	function create(){	//新增選項
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($_POST){	//新增資料
			
			$parameter=array();
			$colSql="heroes_name,heroes_word,selltime1,selltime2,range";
			$sqlStr="INSERT INTO `heroes` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":heroes_name"]=$this->input->post("heroes_name");
			$parameter[":heroes_word"]=trim($this->input->post("heroes_word"));
			$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
			$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);
			$parameter[":range"]=$this->input->post("range");
			if(!$this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Heroes/index");
			}
		}else{
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Heroes/create");
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Heroes/index");
			$this->data["todo"]="add";
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
			$this -> data["body"] = $this -> load -> view("admin/heroes/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `heroes` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Heroes/index");
		}else{		
			if($_POST){	//修改資料	
				
				
				$parameter=array();
				$colSql="heroes_name,heroes_word,selltime1,selltime2,range";
				$sqlStr="UPDATE `heroes` SET ".sqlUpdateString($colSql);
				
				$parameter[":heroes_name"]=$this->input->post("heroes_name");
				$parameter[":heroes_word"]=trim($this->input->post("heroes_word"));
				$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
				$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);
				$parameter[":range"]=$this->input->post("range");
				$sqlStr.=" where num=?";
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Heroes/index");
				
			}else{
				
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/Heroes/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Heroes/index");
				$this->data["todo"]="edit";
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/heroes/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	

	
	function delete($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `heroes` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Heroes/index");
		}else{
			
			$sqlStr="delete from `heroes` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Heroes/index");
		}
		
	}
	

	

	
} 