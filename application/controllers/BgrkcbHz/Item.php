<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Item extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->data["openFind"]="N";//是否啟用搜尋
	}
	
	public function index($root=0){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$sqlStr="select * from `item` where `root`=? order by `range` asc";
		$rowAll=$this->webdb->sqlRowList($sqlStr,array(':root'=>$root));
		$this->data["result"]=$rowAll;
				
		if (!empty($root)){
			$sqlStr="select num,root from `item` where `num`=? order by `range` asc";
			$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$root));	
			$this->data["item"]=$row;
		}
		$this->data["editBTN"]=SYSTEM_URL.'/item/edit/';
		$this->data["delBTN"]=SYSTEM_URL.'/item/delete/';
		
		$this -> data["body"] = $this -> load -> view("admin/item/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	function create($root=0){	//新增選項	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
	
		if($_POST){	//新增資料
			//取得排序最後值
			$sqlStr="select `range` from `item` where root=? order by `range` desc limit 0,1";
			$parameter=array(':root' => $root);
			$row=$this->webdb->sqlRow($sqlStr,$parameter);
			if ($row!=NULL){
				$max_r=$row["range"]+1;
			}else{
				$max_r=0;	
			}
			
			$parameter=array();
			$colSql="title,url,other_url,icon,isShow,root,range";
			$sqlStr="INSERT INTO `item` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":title"]=$this->input->post("title",true);
			$parameter[":url"]=$this->input->post("url",true);
			$parameter[":other_url"]=trim($this->input->post("other_url",true));
			$parameter[":icon"]=$this->input->post("icon",true);
			$parameter[":isShow"]=$this->input->post("isShow",true);
			$parameter[":root"]=($this->input->post("root") ? $this->input->post("root",true) : 0);
			$parameter[":range"]=$max_r;
			if(!$this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/item/index/".($root > 0 ? $root : ""));
			}	
		}else{
			
			//抓出第一層選單
			$sqlStr="select * from `item` where `root`=0 order by `range`";
			$this->data["rootMenu"]=$this->webdb->sqlRowList($sqlStr);
			
			
			$this->data["root"]=$root;	//上層分類
			$this->data["formAction"]=site_url(SYSTEM_URL."/item/create/".($root > 0 ? $root : ""));
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/item/index/".($root > 0 ? $root : ""));
			
			$this->data["subtitle"]=($root==0 ? "新增主選項" : "在【".tb_sql("title","item",$root)."】底下新增選項");
			
			$this -> data["body"] = $this -> load -> view("admin/item/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num=NULL){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$sqlStr="select * from `item` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/item/index");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="title,url,other_url,icon,isShow,root";
				$sqlStr="UPDATE `item` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":title"]=$this->input->post("title",true);
				$parameter[":url"]=$this->input->post("url",true);
				$parameter[":other_url"]=trim($this->input->post("other_url",true));
				$parameter[":icon"]=$this->input->post("icon",true);
				$parameter[":isShow"]=$this->input->post("isShow",true);
				$parameter[":root"]=($this->input->post("root") ? $this->input->post("root",true) : 0);
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/item/index/".($row["root"] > 0 ? $row["root"] : ""));
				
			}else{
				
				//抓出第一層選單
				$sqlStr="select * from `item` where `root`=0 order by `range`";
				$this->data["rootMenu"]=$this->webdb->sqlRowList($sqlStr);
				
				$this->data["root"]=$row["root"];	//上層分類
				$this->data["formAction"]=site_url(SYSTEM_URL."/item/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/item/index/".($row["root"] > 0 ?$row["root"] : ""));
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/item/form", $this -> data,true); 
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
		
		$sqlStr="select * from `item` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/item/index");
		}else{
			$sqlStr="delete from `item` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/item/index/".(empty($row["root"]) ? "" : $row["root"]));
		}
		
	}
	
	//狀態修改
	function AjaxActive(){
		if($this->input->is_ajax_request()){
			$parameter=array();
			$sqlStr="UPDATE `item` SET `isShow`=? where num=?";
			$parameter[":active"]=$this->input->post("active",true);
			$parameter[":num"]=$this->input->post("num",true);
			$this->webdb->sqlExc($sqlStr,$parameter);	
		}
	}
	
	
	function myRange($root=0){
		
		$this->isLogin();//檢查登入狀態
		
		//$this->_append_js2("sort/jquery.ui.core.js");
		//$this->_append_js2("sort/jquery-sortable.js");
		//$this->_append_css2("sort/sort.css");
		
		$parameter=array();
		$sqlStr="select num,title from `item` where `root`=?  order by `range`";
		$parameter[":root"]=$root;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		
		$this->data["rowAll"]=$rowAll;		
		$this->data["subtitle"]=($root==0 ? "主選項" : "【".tb_sql("title","item",$root)."】 - 子選項");
		
		
		$this -> data["body"] = $this -> load -> view("admin/item/range", $this -> data,true); 
		$this -> load -> view("admin/main", $this -> data);	
	}
	
	
	function load_view($root=0){
		$data=array();
		$sqlStr="select num,title from `item` where `root`=0 order by `range`";
		$rowAll=$this->webdb->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$options=array();
				$options["text"]=$row["title"];
				$options["href"]=site_url(SYSTEM_URL."/item/myRange/".$row["num"]);
				if($this->hasChilds($row["num"])){
					$options["nodes"]=$this->load_child_view($row["num"]);
				}
				array_push($data,$options);
			}
		}
		echo json_encode($data); 
	}
	
	
	function load_child_view($root){
		$data=array();
		$parameter=array();
		$sqlStr="select num,title from `item` where `root`=? order by `range`";
		$parameter[":root"]=$root;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$options=array();
				$options["text"]=$row["title"];
				$options["href"]=site_url(SYSTEM_URL."/item/myRange/".$row["num"]);
				if($this->hasChilds($row["num"])){
					$options["nodes"]=$this->load_child_view($row["num"]);
				}
				array_push($data,$options);
			}
		}
		return $data;
	}
	
	function hasChilds($root){
		$parameter=array();
		$sqlStr="select num from `item` where `root`=? order by `range`";
		$parameter[":root"]=$root;
		$row=$this->webdb->sqlRow($sqlStr,$parameter);
		if($row!=NULL){
			return true;	
		}else{
			return false;
		}
	}
	
	//排序
	function AjaxRange(){
		if($this->input->is_ajax_request()){
			$num=explode(",",$this->input->post("sortNum",true));
			for ($i=0;$i<count($num);$i++){
				$sqlStr="UPDATE `item` SET `range`=".$i." where num=?";
				$parameter=array(':num' => $num[$i]);
				$this->webdb->sqlExc($sqlStr,$parameter);	
			}
		}
	}
	
} 