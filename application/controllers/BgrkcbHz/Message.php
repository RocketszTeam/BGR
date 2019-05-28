<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Message extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->data["openFind"]="N";//是否啟用搜尋
	}

	public function index(){
		$this->isLogin();//檢查登入狀態
		$parameter=array();
		$sqlStr="select * from `member_talk` where 1=1";
		//---訂單編號----------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `order_no` like ?";	
			$parameter[":order_no"]="%".@$_REQUEST["find1"]."%";
		}
		//---付款情況----------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `keyin2`=?";
			$parameter[":keyin2"]=@$_REQUEST["find2"];
		}
		
		//---會員帳號----------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `mem_num` in (select num from `member` where `u_id` like ?)";
			$parameter[":u_id"]="%".@$_REQUEST["find4"]."%";
		}
		//日期區間
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `buildtime`>=?";
			$parameter[":buildtime"]=@$_REQUEST["find8"];
		}
		if(@$_REQUEST["find9"]!=""){
			$sqlStr.=" and `buildtime`<=?";
			$parameter[":buildtime"]=@$_REQUEST["find9"];
		}
		
		
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Message/index?".$this->data["att"];//site_url("admin/news/index");
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
							
		$this -> data["body"] = $this -> load -> view("admin/message/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	//會員訊息回覆
	public function edit($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from `member_talk` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Message/index");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="re_word,updatetime,admin_num";
				$sqlStr="UPDATE `member_talk` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":re_word"]=$this->input->post("re_word",true);
				$parameter[":updatetime"]=now();
				$parameter[":admin_num"]=$this->web_root_num;
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Message/index");
			}else{
				$this->data["formAction"]=site_url(SYSTEM_URL."/Message/edit/".$row["num"]);
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	;
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/message/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	//客服訊息新增
	public function reg(){
		$this->isLogin();//檢查登入狀態
		if($_POST){	//修改資料
			$parameter=array();
			$colSql="mem_num,kind,subject,word,buildtime,admin_num";
			$sqlStr="INSERT INTO `member_service` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":mem_num"]=$this->input->post("mem_num",true);
			$parameter[":kind"]=$this->input->post("kind",true);
			$parameter[":subject"]=$this->input->post("subject",true);
			$parameter[":word"]=$this->input->post("word",true);
			$parameter[":buildtime"]=now();
			$parameter[":admin_num"]=$this->web_root_num;
			if($this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Message/service_list");
			}else{
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			//會員下拉
			$memberList=array();
			$sqlStr="select * from `admin` where `u_power`=6 and `root` > 0 order by `u_id`";
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$admin_data=array();
					$admin_data["u_id"]=$row["u_id"];
					$admin_data["u_name"]=$row["u_name"];
					$sqlStr2="select num,u_id,u_name from `member` where admin_num=".$row["num"]." order by u_id";
					$admin_data["member_data"]=$this->webdb->sqlRowList($sqlStr2);
					array_push($memberList,$admin_data);
				}
			}
			$this->data["memberList"]=$memberList;
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Message/reg");
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	;
			$this->data["todo"]="edit";
			$this->data["row"]=$row;	
			$this -> data["body"] = $this -> load -> view("admin/message/reg", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
			
		}
	}
	
	public function service_list(){
		$this->isLogin();//檢查登入狀態
		$parameter=array();
		$sqlStr="select * from `member_service` where 1=1";
		//---訂單編號----------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `order_no` like ?";	
			$parameter[":order_no"]="%".@$_REQUEST["find1"]."%";
		}
		//---付款情況----------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `keyin2`=?";
			$parameter[":keyin2"]=@$_REQUEST["find2"];
		}
		
		//---會員帳號----------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `mem_num` in (select num from `member` where `u_id` like ?)";
			$parameter[":u_id"]="%".@$_REQUEST["find4"]."%";
		}
		//日期區間
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `buildtime`>=?";
			$parameter[":buildtime"]=@$_REQUEST["find8"];
		}
		if(@$_REQUEST["find9"]!=""){
			$sqlStr.=" and `buildtime`<=?";
			$parameter[":buildtime"]=@$_REQUEST["find9"];
		}
		
		
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Message/service_list?".$this->data["att"];//site_url("admin/news/index");
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
							
		$this -> data["body"] = $this -> load -> view("admin/message/service_list", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}

}
?>