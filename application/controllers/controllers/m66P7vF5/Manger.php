<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Manger extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		$this->data["openFind"]="Y";//是否啟用搜尋
		//定義系統帳號 不可刪除
		$this->data["sysGroup"]=array(1,3,4,5);//系統管理者 股東 總代 代理
		
	}
	
	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `admin` where 1=1";
		//---群組-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `u_power`=?";	
			$parameter[":u_power"]=@$_REQUEST["find1"];
		}
		//---帳號-------------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `u_id` like ?";
			$parameter[":u_id"]="%".@$_REQUEST["find2"]."%";
		}
		//---姓名-------------------------------
		if(@$_REQUEST["find3"]!=""){
			$sqlStr.=" and `u_name` like ?";
			$parameter[":u_name"]="%".@$_REQUEST["find3"]."%";
		}
		//---狀態-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `active`=?";
			$parameter[":active"]=@$_REQUEST["find4"];
		}
		
		//不列出股東總代跟代理
		$sqlStr.=" and u_power not in(4,5,6)";
		
		//----防止最高權限帳號被搜尋-------
		if($this->web_root_u_power!=1){
			$sqlStr.=" and u_power <>1 and u_power > ?";
			$parameter[":root_power"]=$this->web_root_u_power;	
		}		
		
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Manger/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `u_power` LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();

		$this->data["editBTN"]=SYSTEM_URL.'/Manger/edit/';
		$this->data["delBTN"]=SYSTEM_URL.'/Manger/delete/';

		//撈出群組查詢下拉用
		$sqlStr="select * from `admin_group` where `u_power`> ? and `u_power` not in(4,5,6) order by `u_power`";
		$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,array(':u_power'=>$this->web_root_u_power));
		
							
		$this -> data["body"] = $this -> load -> view("admin/manger/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	function create(){	//新增選項
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($_POST){	//新增資料		
			if($this->chk_id($this->input->post("u_id",true))){	//撿查帳號是否重複	
				$parameter=array();
				$colSql="u_id,u_password,u_power,u_name,active";
				$sqlStr="INSERT INTO `admin` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":u_id"]=$this->input->post("u_id",true);
				$parameter[":u_password"]=md5($this->input->post("u_password",true));
				$parameter[":u_power"]=$this->input->post("u_power",true);
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				if(!$this->webdb->sqlExc($sqlStr,$parameter,"admin")){
					$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
					$this -> _setMsgAndRedirect($msg, current_url());
				}else{
					$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
					$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Manger/index");
				}	
			}else{
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'使用者已經存在');
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			//撈出群組下拉用
			$sqlStr="select * from `admin_group` where `u_power`> ? and `u_power` not in(4,5,6) order by `u_power`";
			$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,array(':u_power'=>$this->web_root_u_power));
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
			$this->data["formAction"]=site_url(SYSTEM_URL."/Manger/create");
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Manger/index");
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/manger/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num=NULL){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Manger/index");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="u_power,u_name,active";
				$sqlStr="UPDATE `admin` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":u_power"]=$this->input->post("u_power",true);
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Manger/index");
				
			}else{
				//撈出群組下拉用
				$sqlStr="select * from `admin_group` where `u_power`> ? and `u_power` not in(4,5,6) order by `u_power`";
				$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,array(':u_power'=>$this->web_root_u_power));	
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/Manger/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Manger/index");
				$this->data["pwdAction"]=site_url(SYSTEM_URL."/Manger/changepws/".$row["num"]);
				
				$this->data["todo"]="edit";
				$this->data["row"]=$row;
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
				$this -> data["body"] = $this -> load -> view("admin/manger/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	
	function changepws($num){	//修改密碼
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Manger/index");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="u_password";
				$sqlStr="UPDATE `admin` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":u_password"]=md5($this->input->post("u_password",true));		
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '密碼修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Manger/edit/".$row["num"]);
				
			}
		}
		
	}
	
	function delete($num=NULL){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Manger/index");
		}else{
			if(in_array($row["num"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Manger/index");
			}else{
				$sqlStr="delete from `admin` where num=?";
				$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
				$msg=array("type" => "success",'title' => '刪除成功！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Manger/index");
			}
		}
		
	}
	
	//登入歷程
	function login_list($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `admin_login` where admin_num=?";
		$parameter[':admin_num']=$admin_num;
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		  
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Manger/login_list/".$admin_num."?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `buildtime` DESC LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		$this->data["admin_num"]=$admin_num;	
		$this -> data["body"] = $this -> load -> view("admin/manger/login_list", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
		
	}
	

	function user() {
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		//修改密碼===================
		if (@$_POST["todo"]=="pws" && $this->web_root_num !=""){
			$sqlStr="UPDATE `admin` SET u_password =? where num=?";
			$parameter=array(':u_password' => md5(@$_POST["u_password"]),':num' => $this->web_root_num);
			$this->webdb->sqlExc($sqlStr,$parameter);
			$msg=array("type" => "success",'title' => '密碼變更成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Manger/user");
		}

		$sqlStr="select * from `admin` where num=?";
		$parameter=array(':num' => $this->web_root_num);
		
		$row=$this->webdb->sqlRow($sqlStr,$parameter);
		
		if(!isset($row)){
			$msg = array("type" => "danger", 'title' => '帳號不存在！');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$this->data['formAction']=site_url(SYSTEM_URL."/Manger/user/");
		
		$this->data["row"]=$row;
		
		
		$sqlStr2 = "select * from `admin_group` where u_power=?";
		$parameter2=array(':u_power' => $this->web_root_u_power);
		$row2=$this->webdb->sqlRow($sqlStr2,$parameter2);
		$this -> data["power_name"]=$row2["power_name"];
		$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
		$this -> data["body"] = $this -> load -> view("admin/manger/user", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);
		
	}

	

	
	//狀態修改
	function AjaxActive(){
		if($this->input->is_ajax_request()){
			$parameter=array();
			$sqlStr="UPDATE `admin` SET `active`=? where num=?";
			$parameter[":active"]=$this->input->post("active",true);
			$parameter[":num"]=$this->input->post("num",true);
			$this->webdb->sqlExc($sqlStr,$parameter);	
		}
	}
	
	//撿查帳號是否被註冊
	function chk_id($u_id){
		if($u_id!=""){
			$sqlStr="select * from `admin` where u_id=?";	
			$row=$this->webdb->sqlRow($sqlStr,array(':u_id'=>trim($u_id)));
			if($row==NULL){
				return true;	
			}else{
				return false;
			}
		}
	}


	function ajax_chk_id(){
		if($this->input->is_ajax_request()){
			$sqlStr="select * from `admin` where u_id=?";	
			$row=$this->webdb->sqlRow($sqlStr,array(':u_id'=>$this->input->post("u_id",true)));
			if($row==NULL){
				echo 'Y';	
			}else{
				echo 'N';
			}
		}
	}

	
} 