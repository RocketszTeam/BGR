<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Group extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->data["openFind"]="Y";//是否啟用搜尋
		//定義系統群組 不可刪除
		$sysGroup=array(4,5,6);	//股東 總代 代理
		$this->data["sysGroup"]=$sysGroup;

	}
	
	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
			
		$parameter=array();
		$sqlStr="select * from `admin_group` where `u_power` > ? ";
		$parameter[":u_power"]=$this->web_root_u_power;
		
		//---群組-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `power_name` like ?";	
			$parameter[":power_name"]="%".@$_REQUEST["find1"]."%";
		}		
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/group/index?".$this->data["att"];//site_url("admin/news/index");
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

		$this->data["editBTN"]=SYSTEM_URL.'/Group/edit/';
		$this->data["delBTN"]=SYSTEM_URL.'/Group/delete/';
		
							
		$this -> data["body"] = $this -> load -> view("admin/group/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	function create(){	//新增選項
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
	
		if($_POST){	//新增資料		
			if($this->chk_u_power($this->input->post("u_power",true))){	//撿查群組是否重複	
				$parameter=array();
				$colSql="u_power,power_name,power_list";
				$sqlStr="INSERT INTO `admin_group` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":u_power"]=$this->input->post("u_power",true);
				$parameter[":power_name"]=$this->input->post("power_name",true);
				$parameter[":power_list"]=$this->input->post("power_list",true);
				
				$this->webdb->sqlExc($sqlStr,$parameter,"admin_group");
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/group/index");
					
			}else{
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'群組已經存在');
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			
			//取得管理者能使用的權限	
			$my_power="";	//權限列表為空表示最大權限
			if ($this->web_root_u_power!="1"){	
				$sqlStr = "select power_list from `admin_group` where u_power=?";
				$parameter=array(':u_power' => $this->web_root_u_power);	
				$row_my=$this->webdb->sqlRow($sqlStr,$parameter);
				if ($row_my!=NULL){
					$my_power=$row_my["power_list"];	
				}		
			}
			
			//取得有權限的選單
			$this->data["ItemList"]=$this->getItemList(0,$my_power);
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/group/index");
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
			$this->data["formAction"]=site_url(SYSTEM_URL."/Group/create");
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/group/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($u_power=NULL){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin_group` where u_power=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':u_power'=>$u_power));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/group/index");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="power_name,power_list";
				$sqlStr="UPDATE `admin_group` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where u_power=?";
				$parameter[":power_name"]=$this->input->post("power_name",true);
				$parameter[":power_list"]=$this->input->post("power_list",true);
				$parameter[":u_power"]=$u_power;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/group/index");
				
			}else{
				
				//取得管理者能使用的權限	
				$my_power="";	//權限列表為空表示最大權限
				if ($this->web_root_u_power!="1"){	
					$sqlStr = "select power_list from `admin_group` where u_power=?";
					$parameter=array(':u_power' => $this->web_root_u_power);	
					$row_my=$this->webdb->sqlRow($sqlStr,$parameter);
					if ($row_my!=NULL){
						$my_power=$row_my["power_list"];	
					}		
				}
				
				//$my_power=($this->web_root_u_power!="1" ? $row["power_list"] : "");
				//取得有權限的選單
				$this->data["ItemList"]=$this->getItemList(0,$my_power);
				
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/Group/edit/".$row["u_power"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/group/index");
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["todo"]="edit";
				$this->data["result"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/group/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	
	
	function delete($u_power=NULL){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin_group` where `u_power`=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':u_power'=>$u_power));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/group/index");
		}else{
			if(in_array($row["u_power"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統群組無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/group/index");
			}else{
				if($this->web_root_u_power > $row["u_power"]){
					$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無法刪除上層權組');
					$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/group/index");
				}else{
					$sqlStr="delete from `admin_group` where `u_power`=?";
					$this->webdb->sqlExc($sqlStr,array(':u_power'=>$u_power));
					$msg=array("type" => "success",'title' => '刪除成功！');
					$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/group/index");
				}
			}
		}
		
	}
	


	
	//狀態修改
	function AjaxActive(){
		if($this->input->is_ajax_request()){
			$parameter=array();
			$sqlStr="UPDATE `admin_group` SET `active`=? where num=?";
			$parameter[":active"]=$this->input->post("active",true);
			$parameter[":num"]=$this->input->post("num",true);
			$this->webdb->sqlExc($sqlStr,$parameter);	
		}
	}
	
	//撿查群組是否被註冊
	function chk_u_power($u_power=NULL){
		if($u_power!=""){
			$sqlStr="select * from `admin_group` where u_power=?";	
			$row=$this->webdb->sqlRow($sqlStr,array(':u_power'=>trim($u_power)),"admin_group");
			if($row==NULL){
				return true;	
			}else{
				return false;
			}
		}
	}


	function ajax_chk_u_power(){
		if($this->input->is_ajax_request()){
			$sqlStr="select * from `admin_group` where u_power=?";	
			$row=$this->webdb->sqlRow($sqlStr,array(':u_power'=>$this->input->post("u_power",true)),"admin_group");
			if($row==NULL){
				echo 'Y';	
			}else{
				echo 'N';
			}
		}
	}


	//取得使用者有權限的選單
	function getItemList($root,$my_power=""){		
		
		$ItemList=array();
		//取得選單
		$sqlStr = "select * from `item` where `root`=".$root;
		if($my_power!=""){
			$sqlStr.=" and num in (".$my_power.")";	
		}
		$sqlStr.=" order by `range`";
		$rowAll=$this->webdb->sqlRowList($sqlStr);
		$i=0;
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$item=array();	
				$item["num"]=$row["num"];
				$item["title"]=$row["title"];
				$item["Nodes"]=$this->getItemList($row["num"],$my_power);
				$ItemList[$i]=$item;
				$i++;
			}
		}
		return $ItemList;
	}

	
} 