<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Agents extends Core_controller{
	public function __construct(){
		parent::__construct();
		//定義股東 總代 代理 群組陣列
		$this->data["agentsArray"]=array(4,5,6);
		
		//定義系統帳號 不可刪除
		$sysGroup=array(1,3,4,5);	//股東 總代 代理
		$this->data["sysGroup"]=$sysGroup;//系統管理者 股東 總代 代理
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	//股東管理
	public function agents1_list(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `admin` where u_power = 4";
		//----管理者能看到被刪除的帳號-----------
		if($this->web_root_u_power!='1'){
			$sqlStr.=" and `enable`=1";
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
				
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Agents/agents1_list?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `u_power`,`enable` DESC LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();		
		
		$this->data["editBTN"]=SYSTEM_URL."/Agents/agents1_edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Agents/agents1_delete/";
		$this->data["enableBTN"]=SYSTEM_URL."/Agents/agents1_enable/";
							
		$this -> data["body"] = $this -> load -> view("admin/agent/agent1_list", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	function agents1_add(){	//新增股東
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->data["min_percent"] = 0;		
		if($_POST){	//新增資料		
			if($this->chk_id($this->input->post("u_id",true))){	//撿查帳號是否重複	
				$parameter=array();
				$colSql="u_id,u_password,percent,u_power,u_name,active";
				$sqlStr="INSERT INTO `admin` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":u_id"]=$this->input->post("u_id",true);
				$parameter[":u_password"]=md5($this->input->post("u_password",true));
				$parameter[":percent"]=$this->input->post("percent",true);
				$parameter[":u_power"]=4;
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				if(!$this->webdb->sqlExc($sqlStr,$parameter)){
					$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
					$this -> _setMsgAndRedirect($msg, current_url());
				}else{
					$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
					$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents1_list");
				}	
			}else{
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'使用者已經存在');
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Agents/agents1_add");	//表單送出連結
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Agents/agents1_list");	
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/agent/agent1_form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function agents1_edit($num=NULL){	//修改股東
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
		}else{		
			//占成下壓
			$sqlAgent="select num,u_id,percent from `admin` where `root` in (".$num.kind_sql($num,'admin').")";
		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="u_name,active,percent";
				$sqlStr="UPDATE `admin` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				$parameter[":percent"]=$this->input->post("percent",true);
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
							
				$rowAgent=$this->webdb->sqlRowList($sqlAgent);		
				$max_percent = $this->input->post("percent",true);	//設定階層最大允許值
				for($i=0;$i<count($rowAgent);$i++){	
					if($rowAgent[$i]["percent"] > $max_percent){	
						$upSql="UPDATE `admin` SET `percent`=".$max_percent." where num=".$rowAgent[$i]["num"];
						$this->webdb->sqlExc($upSql);
					}
				}
				
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents1_list");
				
			}else{
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱				
				$this->data["formAction"]=site_url(SYSTEM_URL."/Agents/agents1_edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Agents/agents1_list");
				$this->data["pwdAction"]=site_url(SYSTEM_URL."/Agents/changepws/".$row["num"]);	
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				
				$this->data["min_percent"]=0;
				//抓出下線占成最高那一個
				$sqlAgent.=" order by `percent` DESC Limit 1";
				$rowMinPercent=$this->webdb->sqlRow($sqlAgent);
				if($rowMinPercent!=NULL){
					$this->data["min_percent"]=$rowMinPercent["percent"];
				}
								
				$this -> data["body"] = $this -> load -> view("admin/agent/agent1_form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	function agents1_delete($num=NULL){ //股東刪除
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		//有下線無法刪除資料
		$sqlStr="select num from `admin` where `root`=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row!=NULL){
			$msg = array("type" => "danger", 'title' => '刪除失敗！','content'=>'很抱歉...此帳號已有下線無法刪除');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
		}else{
			if(in_array($row["num"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
			}else{
				$sqlStr="update `admin` SET `enable`=0 where num=?";
				$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
				$msg=array("type" => "success",'title' => '刪除成功！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
			}
		}
		
	}
	
	function agents1_enable($num=NULL){ //股東帳號恢復
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
		}else{
			if(in_array($row["num"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
			}else{
				$sqlStr="update `admin` SET `enable`=1 where num=?";
				$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
				$msg=array("type" => "success",'title' => '帳號已經恢復刪除！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
			}
		}
		
	}
	
	
	function changepws($num=NULL){	//股東修改密碼
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents1_list");
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
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents1_edit/".$row["num"]);
				
			}
		}
		
	}
	
	
	
	//總代管理
	public function agents2_list(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `admin` where u_power = 5";
		//----管理者能看到被刪除的帳號-----------
		if($this->web_root_u_power!='1'){
			$sqlStr.=" and `enable`=1";
		}
		//---上層帳號-------------------------------
		if(@$_REQUEST["find1"]!=""){
			$sqlStr.=" and `root`=?";
			$parameter[":root"]=@$_REQUEST["find1"];
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
		
		if(in_array($this->web_root_u_power,$this->data["agentsArray"])){ //除了最高權限 能看到所有...否則只抓底下的
			$sqlStr.=" and `root`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		
				
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Agents/agents2_list?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `u_power` LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();		

		//撈出查詢下拉用
		if($this->web_root_u_power < 4){	//管理者才有的上級查詢
			$sqlStr="select * from `admin` where `u_power`=4";
			$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr);
		}
		
		$this->data["editBTN"]=SYSTEM_URL."/Agents/agents2_edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Agents/agents2_delete/";
		$this->data["enableBTN"]=SYSTEM_URL."/Agents/agents2_enable/";
							
		$this -> data["body"] = $this -> load -> view("admin/agent/agent2_list", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	
	
	function agents2_add(){	//新增總代
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->data["min_percent"] = 0;		
		if($_POST){	//新增資料		
			if($this->chk_id($this->input->post("u_id",true))){	//撿查帳號是否重複	
				$parameter=array();
				$colSql="u_id,u_password,percent,u_power,root,u_name,active";
				$sqlStr="INSERT INTO `admin` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":u_id"]=$this->input->post("u_id",true);
				$parameter[":u_password"]=md5($this->input->post("u_password",true));
				$parameter[":percent"]=$this->input->post("percent",true);
				$parameter[":u_power"]=5;
				$parameter[":root"]=($this->input->post('root') ? $this->input->post('root') : $this->web_root_num);
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				if(!$this->webdb->sqlExc($sqlStr,$parameter)){
					$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
					$this -> _setMsgAndRedirect($msg, current_url());
				}else{
					$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
					$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents2_list");
				}	
			}else{
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'使用者已經存在');
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			
			
			//上層帳號
			if(!in_array($this->web_root_u_power,$this->data["agentsArray"])){	//管理者群撈入所有
				$sqlStr="select * from `admin` where `u_power` = 4";
				$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);	
			}else{	//否則撈出自己
				$sqlStr="select * from `admin` where `num` = ".$this->web_root_num;
				$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);	
			}
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Agents/agents2_add");	//表單送出連結
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Agents/agents2_list");	
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/agent/agent2_form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	

	
	function agents2_edit($num){	//修改總代
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
		}else{		
		
			//占成下壓
			$sqlAgent="select num,u_id,percent from `admin` where `u_power` = 6 and `root` in (".$num.kind_sql($num,'admin').")";

		
			if($_POST){	//修改資料
				$rowAgent=$this->webdb->sqlRowList($sqlAgent);
				
				$parameter=array();
				$colSql="root,u_name,active,percent";
				$sqlStr="UPDATE `admin` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":root"]=($this->input->post('root') ? $this->input->post('root') : $this->web_root_num);
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				$parameter[":percent"]=$this->input->post("percent",true);
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				$max_percent = $this->input->post("percent",true);	//設定階層對大允許值
				for($i=0;$i<count($rowAgent);$i++){	
					if($rowAgent[$i]["percent"] > $max_percent){	
						$upSql="UPDATE `admin` SET `percent`=".$max_percent." where num=".$rowAgent[$i]["num"];
						$this->webdb->sqlExc($upSql);
					}
				}			
				
				
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents2_list");
				
			}else{
				
				
				//上層帳號
				if(!in_array($this->web_root_u_power,$this->data["agentsArray"])){	//管理者群撈入所有
					$sqlStr="select * from `admin` where `u_power` = 4";
					$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);	
					$this->data["upPercent"]=tb_sql("percent",'admin',$row["root"]);
				}else{	//否則撈出自己
					$sqlStr="select * from `admin` where `num` = ".$this->web_root_num;
					$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);	
				}
				
				$this->data["min_percent"]=0;
				//抓出下線占成最高那一個
				$sqlAgent.=" order by `percent` DESC Limit 1";
				$rowMinPercent=$this->webdb->sqlRow($sqlAgent);
				if($rowMinPercent!=NULL){
					$this->data["min_percent"]=$rowMinPercent["percent"];
				}				
				
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱				
				$this->data["formAction"]=site_url(SYSTEM_URL."/Agents/agents2_edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Agents/agents2_list");
				$this->data["pwdAction"]=site_url(SYSTEM_URL."/Agents/d_changepws/".$row["num"]);	
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/agent/agent2_form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}


	function agents2_delete($num=NULL){ //總代刪除
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		//有下線無法刪除資料
		$sqlStr="select num from `admin` where `root`=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row!=NULL){
			$msg = array("type" => "danger", 'title' => '刪除失敗！','content'=>'很抱歉...此帳號已有下線無法刪除');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
		}else{
			if(in_array($row["num"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
			}else{
				$sqlStr="update `admin` set `enable`=0 where num=?";
				$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
				$msg=array("type" => "success",'title' => '刪除成功！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
			}
		}
		
	}

	function agents2_enable($num=NULL){ //總代帳號恢復
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
		}else{
			if(in_array($row["num"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
			}else{
				$sqlStr="update `admin` SET `enable`=1 where num=?";
				$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
				$msg=array("type" => "success",'title' => '帳號已經恢復刪除！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
			}
		}
		
	}

	function d_changepws($num=NULL){	//總代修改密碼
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents2_list");
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
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents2_edit/".$row["num"]);
				
			}
		}
		
	}
	


	//代理管理
	public function agents3_list(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `admin` where u_power = 6";
		//----管理者能看到被刪除的帳號-----------
		if($this->web_root_u_power!='1'){
			$sqlStr.=" and `enable`=1";
		}
		//---上層帳號-------------------------------
		if(@$_REQUEST["find1"]!=""){
			$sqlStr.=" and `root`=?";
			$parameter[":root"]=@$_REQUEST["find1"];
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


		//---代理網址-------------------------------
		if(@$_REQUEST["find5"]!=""){
			$sqlStr.=" and `cust_url` like ?";
			$parameter[":cust_url"]="%".@$_REQUEST["find5"]."%";
		}	
		//---狀態-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `active`=?";
			$parameter[":active"]=@$_REQUEST["find4"];
		}
		
		if(in_array($this->web_root_u_power,$this->data["agentsArray"])){ //除了最高權限 能看到所有...否則只抓底下的
			$sqlStr.=" and `root` in(?".kind_sql($this->web_root_num,'admin').")";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		
				
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Agents/agents3_list?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `u_power` LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();		

		//撈出查詢下拉用
		if($this->web_root_u_power < 4){	//管理者才有的上級查詢
			$sqlStr="select * from `admin` where `u_power`=5";
			$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr);
		}
		
		$this->data["editBTN"]=SYSTEM_URL."/Agents/agents3_edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Agents/agents3_delete/";
		$this->data["enableBTN"]=SYSTEM_URL."/Agents/agents3_enable/";
							
		$this -> data["body"] = $this -> load -> view("admin/agent/agent3_list", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}


	function agents3_add(){	//新增代理
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->data["min_percent"] = 0;
		
		if($_POST){	//新增資料		
			if($this->chk_id($this->input->post("u_id",true))){	//撿查帳號是否重複	
				$parameter=array();
				$colSql="u_id,u_password,percent,u_power,root,u_name,active,cust_url";
				$sqlStr="INSERT INTO `admin` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":u_id"]=$this->input->post("u_id",true);
				$parameter[":u_password"]=md5($this->input->post("u_password",true));
				$parameter[":percent"]=$this->input->post("percent",true);
				$parameter[":u_power"]=6;
				$parameter[":root"]=($this->input->post('root') ? $this->input->post('root') : $this->web_root_num);
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				$parameter[":cust_url"]=trim($this->input->post("cust_url",true));															
				if(!$this->webdb->sqlExc($sqlStr,$parameter,"admin")){
					$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
					$this -> _setMsgAndRedirect($msg, current_url());
				}else{
					$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
					$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents3_list");
				}	
			}else{
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'使用者已經存在');
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			
			//上層帳號
			if(!in_array($this->web_root_u_power,$this->data["agentsArray"])){	//管理者群撈入所有
				$sqlStr="select * from `admin` where `u_power` = 5";
				$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);	
			}else{	//否則撈出底下的代理
				if($this->web_root_u_power < 5){	//若是股東 要開代理 則列出 股東抵下的總代
					$sqlStr="select * from `admin` where `u_power` = 5 and `root` in (".$this->web_root_num.kind_sql($this->web_root_num,'admin').")";
					$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);
				}else{	//當總代 要開代理 則抓出自己
					$sqlStr="select * from `admin` where `u_power` = 5 and `num`=".$this->web_root_num;
					$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);
				}
			}
			
			
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Agents/agents3_add");	//表單送出連結
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Agents/agents3_list");	
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/agent/agent3_form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}


	function agents3_edit($num=NULL){	//修改代理
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num),"admin");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="root,u_name,active,percent,cust_url";
				$sqlStr="UPDATE `admin` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":root"]=($this->input->post('root') ? $this->input->post('root') : $this->web_root_num);
				$parameter[":u_name"]=$this->input->post("u_name",true);
				$parameter[":active"]=$this->input->post("active",true);
				$parameter[":percent"]=$this->input->post("percent",true);
				$parameter[":cust_url"]=trim($this->input->post("cust_url",true));																
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents3_list");
				
			}else{
				
				
				if(!in_array($this->web_root_u_power,$this->data["agentsArray"])){	//管理者群撈入所有
					$sqlStr="select * from `admin` where `u_power` = 5";
					$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);	
					$this->data["upPercent"]=tb_sql("percent",'admin',$row["root"]);
				}else{	//否則撈出底下的代理
					if($this->web_root_u_power < 5){	//若是股東 要開代理 則列出 股東抵下的總代
						$sqlStr="select * from `admin` where `u_power` = 5 and `root` in (".$this->web_root_num.kind_sql($this->web_root_num,'admin').")";
						$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);
					}else{	//當總代 要開代理 則抓出自己
						$sqlStr="select * from `admin` where `u_power` = 5 and `num`=".$this->web_root_num;
						$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);
					}
				}
				
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱				
				$this->data["formAction"]=site_url(SYSTEM_URL."/Agents/agents3_edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Agents/agents3_list");
				$this->data["pwdAction"]=site_url(SYSTEM_URL."/Agents/c_changepws/".$row["num"]);	
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/agent/agent3_form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}


	function c_changepws($num=NULL){	//代理修改密碼
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
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
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Agents/agents3_edit/".$row["num"]);
				
			}
		}
		
	}

	function agents3_delete($num=NULL){ //代理刪除
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		//有下線無法刪除資料
		$sqlStr="select num from `member` where `admin_num`=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':admin_num'=>$num));
		if($row!=NULL){
			$msg = array("type" => "danger", 'title' => '刪除失敗！','content'=>'很抱歉...此帳號已有下線無法刪除');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
		}else{
			if(in_array($row["num"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
			}else{
				$sqlStr="update `admin` set enable=0 where num=?";
				$this->webdb->sqlExc($sqlStr,array(':num'=>$num),"admin");
				$msg=array("type" => "success",'title' => '刪除成功！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
			}
		}
		
	}
	
	function agents3_enable($num=NULL){ //代理帳號恢復
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `admin` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
		}else{
			if(in_array($row["num"],$this->data["sysGroup"])){
				$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
			}else{
				$sqlStr="update `admin` SET `enable`=1 where num=?";
				$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
				$msg=array("type" => "success",'title' => '帳號已經恢復刪除！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/agents3_list");
			}
		}
		
	}
	

	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `apply_agent` where 1 = 1";			
				
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Agents/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `buildtime` DESC LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();	
		
		
		$this->data["delBTN"]=SYSTEM_URL."/Agents/delete/";
		
			
									
		$this -> data["body"] = $this -> load -> view("admin/agent/agents", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	function delete($num){ 
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `apply_agent` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/index");
		}else{
			$sqlStr="delete from `apply_agent` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Agents/index");
		}
		
	}


	function Ajax_percent(){
		if($this->input->is_ajax_request()){
			$percent=tb_sql("percent","admin",$this->input->post('root'));
			echo json_encode(array('RntCode'=>'Y','percent'=>$percent));
		}
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
	
	public function ajax_chk_url(){
		if($this->input->is_ajax_request()){
			$check=0;
			$urlArray=explode(',',trim($this->input->post('cust_url',true)));
			foreach($urlArray as $value){
				$parameter=array();
				$sqlStr="select num from `admin` where `u_power`=6 and `root` > 0 and CONCAT(',',`cust_url`,',') like ?";
				$parameter[':cust_url']="%,".$value.",%";
				if($this->input->post('num')){
					$sqlStr.=" and num <> ?";
					$parameter[':num']=$this->input->post('num');	
				}
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if($row!=NULL){
					$check++;
				}
			}
			if($check > 0){
				echo 'N';
			}else{
				echo 'Y';
			}
		}
	}

	
} 