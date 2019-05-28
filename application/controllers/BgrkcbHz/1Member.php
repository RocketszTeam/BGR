<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Member extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		//error_reporting(0);
		$this->data["member_group"]=array('0_一般會員','1_黃金會員','2_白金會員','3_藍鑽會員');
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `member` where 1=1";
		$f = '';
		//---所屬代理-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr .=" and `admin_num`=?";	
			$parameter[":admin_num"]=@$_REQUEST["find1"];
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
		//---IP-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$ipSql="select mem_num from member_ip where login_ip = ? GROUP BY mem_num";
			$rowIP=$this->webdb->sqlRowList($ipSql,array(trim(@$_REQUEST["find4"])));
			$ipList=array();
			if($rowIP!=NULL){
				foreach($rowIP as $row){
					array_push($ipList,$row["mem_num"]);	
				}
				$ipList=implode(',',$ipList);
				$sqlStr.=" and num in ($ipList)";
			}else{
				$sqlStr.=" and num is null";
			}
		}
		//---電話-------------------------------
		if(@$_REQUEST["find5"]!=""){
			$sqlStr.=" and `phone` like ?";
			$parameter[":phone"]="%".@$_REQUEST["find5"]."%";
		}
		
		
		//---狀態-------------------------------
		if(@$_REQUEST["find6"]!=""){
			$sqlStr.=" and `active`=?";
			$parameter[":active"]="".@$_REQUEST["find6"]."";
		}
		//登入日期
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `login_time` >=?";
			$parameter[":login_time"]=@$_REQUEST["find7"];
		}
		if(@$_REQUEST["find8"]!=""){			
			$sqlStr.=" and `login_time` <?";
			$parameter[":login_time2"]=date('Y-m-d',strtotime(@$_REQUEST["find8"]."+1 day"));
		}
		//註冊日期
		if(@$_REQUEST["find9"]!=""){
			$sqlStr.=" and `reg_time` >=?";
			$parameter[":reg_time"]=@$_REQUEST["find9"];
		}
		if(@$_REQUEST["find10"]!=""){			
			$sqlStr.=" and `reg_time` <?";
			$parameter[":reg_time2"]=date('Y-m-d',strtotime(@$_REQUEST["find10"]."+1 day"));
		}
		
		//儲值狀態
		if(@$_REQUEST["find11"]!=""){
			$eTime=now();
			switch ($_REQUEST["find11"]){
				case 'Y':	//7天內有儲值
					//儲值跟銀行匯款都算進去
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) and DATEDIFF('".$eTime."', buildtime) <= 7  GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$dList=array();
					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($dList,$row["mem_num"]);	
						}
						$dList=implode(',',$dList);
						$sqlStr.=" and num in ($dList)";
					}else{
						$sqlStr.=" and num is null";
					}
					break;
				case 'N':	//7天內沒儲值
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) and DATEDIFF('".$eTime."', buildtime) > 7  GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$d1List=array();
					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($d1List,$row["mem_num"]);	
						}
						
					}
					//排除掉 7 天內 有儲值的
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) and DATEDIFF('".$eTime."', buildtime) <= 7  GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$d2List=array();
					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($d2List,$row["mem_num"]);	
						}
					}
					$result=array_diff($d1List,$d2List);
					if(count($result)){
						$numList=implode(',',$result);
						$sqlStr.=" and num in ($numList)";	
					}else{
						$sqlStr.=" and num is null";
					}
					break;
				case 'W':	//未曾儲值
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$dList=array();
					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($dList,$row["mem_num"]);	
						}
						$dList=implode(',',$dList);
						$sqlStr.=" and num not in ($dList)";
					}else{
						$sqlStr.=" and num is null";
					}
					break;	
			}
		}
		
		
		//除了最高權限 能看到所有...否則只抓該使用者的分類
		if(in_array($this->web_root_u_power,array(4,5,6))){	
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,"admin").")";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Member/index?".$this->data["att"];//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 2;
		$config['page_query_string'] = TRUE;
		
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `login_time` DESC LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
								
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		

		//限權檢查		
		$this->data["editBTN"]=SYSTEM_URL."/Member/edit/";
		$this->data["viewBTN"]=SYSTEM_URL."/Member/view/";
		//限權檢查		
		$this->data["edit_auth"] = $this->chk_auth("Member/edit");
		$this->data["view_auth"] = $this->chk_auth("Member/view");
		

		//周拋售次數的起始日期(星期一開始)		
		$bt = date("Y/m/d");					
		for($i=0;$i<=7;$i++){
			if(date("w",strtotime($bt.' -'.$i.' days')) == 1){
				$bt = date("Y-m-d", strtotime($bt .' -'.$i.' days' ) )." 00:00:00";
				break;
			}
		}	
		$this->data["bt"] = $bt;

		
		//下拉用查詢
		if(!in_array($this->web_root_u_power,array(4,5,6))){	
			$sqlStr="select * from `admin` where `u_power`=6";
		}else{
			if($this->web_root_u_power < 6){	//股東或總代就往下抓出代理
				$sqlStr="select * from `admin` where `u_power`=6 and `root` in(".$this->web_root_num.kind_sql($this->web_root_num,'admin').")";
			}else{	//代理抓自己
				$sqlStr="select * from `admin` where `u_power`=6 and `num`=".$this->web_root_num; 
			}
		}
		$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);
							
		$this -> data["body"] = $this -> load -> view("admin/member/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	//銀行帳號
	public function bank($num=NULL){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `member_bank` where mem_num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '會員尚未建立銀行帳戶！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
		}else{
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="bank_num,bank_branch,bank_account,account_name";
				$sqlStr="UPDATE `member_bank` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where mem_num=?";
				$parameter[":bank_num"]=trim($this->input->post("bank_num",true));
				$parameter[":bank_branch"]=trim($this->input->post("bank_branch",true));
				$parameter[":bank_account"]=trim($this->input->post("bank_account",true));
				$parameter[":account_name"]=trim($this->input->post("account_name",true));
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$parameter=array();
				$colSql="mem_num,update_admin,ip,word,buildtime";
				$sqlStr="INSERT INTO `member_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$num;
				$parameter[":update_admin"]=$this->web_root_num;
				$parameter[":ip"]=$this->input->ip_address();
				$parameter[":word"]='修改銀行資料';
				$parameter[':buildtime']=now();
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				$msg = array("type" => "success", 'title' => '銀行資料修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
			}else{
				//撈出銀行列表下拉用
				$sqlStr="select * from `bank_list`";
				$this->data["bankList"]=$this->webdb->sqlRowList($sqlStr);
				
				$this->data["formAction"]=site_url(SYSTEM_URL."/Member/bank/".$row["mem_num"]."?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["subtitle"]=$this->getsubtitle();
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["todo"]="edit";
				$this->data["row"]=$row;
				$this -> data["body"] = $this -> load -> view("admin/member/bank", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
			}
		}
	}
	
	//黑名單
	function block(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `member_block` where 1=1";
		//---IP查詢-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `block` like ?";	
			$parameter[":find1"]="%".@$_REQUEST["find1"]."%";
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Member/block?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `num` DESC LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
		
		$this->data["addBTN"]=SYSTEM_URL."/Member/add_block";
		$this->data["editBTN"]=SYSTEM_URL."/Member/block_edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Member/delete_block/";
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
						
		$this -> data["body"] = $this -> load -> view("admin/member/block", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	//新增黑名單
	function add_block(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($_POST){
			$parameter=array();
			$colSql="type,block";
			$sqlStr="INSERT INTO `member_block` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":type"]=$this->input->post("type",true);
			$parameter[":block"]=$this->input->post("block",true);
			if(!$this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Member/block");
			}	
		}else{
			$this->data["formAction"]=site_url(SYSTEM_URL."/Member/add_block");	//表單送出連結
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Member/block");	
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/member/block_form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
	}
	//修改黑名單
	function block_edit($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `member_block` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/block");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="type,block";
				$sqlStr="UPDATE `member_block` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":type"]=$this->input->post("type",true);
				$parameter[":block"]=$this->input->post("block",true);
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Member/block");
			}else{
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱				
				$this->data["formAction"]=site_url(SYSTEM_URL."/Member/block_edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Member/block");
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/member/block_form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
		
	}
	
	
	//刪除黑名單
	function delete_block($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `member_block` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/block");
		}else{
			$sqlStr="delete from `member_block` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/block");
		}
		
	}
	
	
	function create(){	//新增選項
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($_POST){	//新增資料
			if($this->web_root_u_power!=4 && $this->web_root_u_power!=5){	//不允許股東and 總代新增會員		
				if($this->chk_id($this->input->post("u_id",true))){	//撿查帳號是否重複	
					$parameter=array();
					$colSql="nation,u_id,u_password,u_name,phone,line,email,m_group";
					$colSql.=",active,is_vaid,demo,admin_num,reg_time";
					$sqlStr="INSERT INTO `member` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":nation"]=trim($this->input->post("nation",true));
					$parameter[":u_id"]=trim($this->input->post("u_id",true));
					$parameter[":u_password"]=$this->encryption->encrypt($this->input->post("u_password",true));
					$parameter[":u_name"]=trim($this->input->post("u_name",true));
					$parameter[":phone"]=trim($this->input->post("phone",true));
					$parameter[":line"]=trim($this->input->post("line",true));
					$parameter[":email"]=trim($this->input->post("email",true));
					$parameter[":m_group"]=trim($this->input->post("m_group",true));
					$parameter[":active"]=trim($this->input->post("active",true));
					$parameter[":is_vaid"]=trim($this->input->post("is_vaid",true));
					$parameter[":demo"]=trim($this->input->post("demo",true));
					$parameter[":admin_num"]=($this->web_root_u_power ==6 ? $this->web_root_num : 5);	//非代理新增會員一律規那主站代理
					$parameter[":reg_time"]=now();
					$mem_num=$this->webdb->sqlExc($sqlStr,$parameter);
					
					
					if(!$mem_num){
						$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
						$this -> _setMsgAndRedirect($msg, current_url());
					}else{
						
						$parameter=array();
						$colSql="mem_num,update_admin,ip,word,buildtime";
						$sqlStr="INSERT INTO `member_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$mem_num;
						$parameter[":update_admin"]=$this->web_root_num;
						$parameter[":ip"]=$this->input->ip_address();
						$parameter[":word"]='建立新會員';
						$parameter[':buildtime']=now();
						$this->webdb->sqlExc($sqlStr,$parameter);
						
						$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
						$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Member/index");
					}	
				}else{
					$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'使用者已經存在');
					$this -> _setMsgAndRedirect($msg, current_url());
				}
			}else{
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'您不允許新增會員');
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
			$this->data["formAction"]=site_url(SYSTEM_URL."/Member/create");
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Member/index");
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/member/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `member` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num),"member");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="nation,u_name,phone,line,email,active,is_vaid,demo,m_group";
				$sqlStr="UPDATE `member` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":nation"]=trim($this->input->post("nation",true));
				$parameter[":u_name"]=trim($this->input->post("u_name",true));
				$parameter[":phone"]=trim($this->input->post("phone",true));
				$parameter[":line"]=trim($this->input->post("line",true));				
				$parameter[":email"]=trim($this->input->post("email",true));
				$parameter[":active"]=trim($this->input->post("active",true));
				$parameter[":is_vaid"]=trim($this->input->post("is_vaid",true));
				$parameter[":demo"]=trim($this->input->post("demo",true));
				$parameter[":m_group"]=trim($this->input->post("m_group",true));
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				$parameter=array();
				$colSql="mem_num,update_admin,ip,word,buildtime";
				$sqlStr="INSERT INTO `member_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$num;
				$parameter[":update_admin"]=$this->web_root_num;
				$parameter[":ip"]=$this->input->ip_address();
				$parameter[":word"]='修改會員基本資料';
				$parameter[':buildtime']=now();
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
				
			}else{
				
				$this->data["formAction"]=site_url(SYSTEM_URL."/Member/edit/".$row["num"]."?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["pwdAction"]=site_url(SYSTEM_URL."/Member/changepws/".$row["num"]."?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/member/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	//瀏覽會員資料
	public function view($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `member` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num),"member");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
		}else{	
		 
		  $this->data["cancelBtn"]=site_url(SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
		  $this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
		  $this->data["row"]=$row;	
		  $this -> data["body"] = $this -> load -> view("admin/member/view", $this -> data,true); 
		  $this -> load -> view("admin/main", $this -> data);	
		}
	}
	
	
	//查看遊戲帳號
	function games_account($mem_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `member` where num=?";
		$rowMember=$this->webdb->sqlRow($sqlStr,array(':num'=>$mem_num));
		if($rowMember==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,$this->agent->referrer());
		}else{
			$this->data["mem_num"]=$mem_num;
			$sqlStr="select * from `games_account` where mem_num=".$rowMember["num"];
			$this->data["sql"]= $sqlStr ;
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			
			//先將密碼解密在塞回陣列
			$dataList=array();
			if($rowAll!=NULL){
				foreach($rowAll as $row){				
					$data=array();
					$data["num"]=$row["num"];	
					$data["u_id"]=$row["u_id"];	
					$data["u_password"]=$this->encryption->decrypt($row["u_password"]);	
					$data["gamemaker_num"]=$row["gamemaker_num"];	
					$data["mem_num"]=$row["mem_num"];
					array_push($dataList,$data);
				}
			}
			$this -> data["result"] = $dataList;
			$this -> data["body"] = $this -> load -> view("admin/member/games_account", $this -> data,true);   
			$this -> load -> view("admin/main", $this -> data);  
				
		}
	}
	
	//登入歷程
	function login_list($mem_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `member_ip` where mem_num=?";
		$parameter[':mem_num']=$mem_num;
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		  
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Member/login_list/".$mem_num."?per_page=".@$_GET['per_page'].$this->data["att"].$this->data["satt"]);
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		$config['query_string_segment']='page';
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["page"]!=""){$nowpage=@$_GET["page"];}
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
		
		$this->data["mem_num"]=$mem_num;	
		$this -> data["body"] = $this -> load -> view("admin/member/login_list", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
		
	}
	
	//發送訊息
	function member_sms(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->isLogin()){
					if($this->input->post('sms_type') && $this->input->post('sms_body') && $this->input->post('mem_num') && $this->input->post('subject')){
						if($this->input->post('sms_type')==2){	//簡訊
							$this->load->library('smsclass');
							$phone=tb_sql("phone",'member',$this->input->post('mem_num'));
							if(preg_match('/^09[0-9]{8}$/',$phone)){
								$a = $this->smsclass->sendSMS($this->input->post('sms_body'), $phone,$this->input->post('subject')) ;

								if($a){
									echo json_encode(array('RntCode'=>1,'title'=>'發送成功','Msg'=>'簡訊已發送至會員！'));
								}else{
									echo json_encode(array('RntCode'=>0,'title'=>'發送失敗'.$a,'Msg'=>'簡訊發送失敗，請聯絡網管人員！'));	
								}
							}else{
								echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'會員電話不正確！'));	
							}
						}else{	//訊息
							$parameter=array();
							$colSql="mem_num,kind,subject,word,buildtime,admin_num";
							$sqlStr="INSERT INTO `member_service` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
							$parameter[":mem_num"]=$this->input->post("mem_num",true);
							$parameter[":kind"]=2;
							$parameter[":subject"]=$this->input->post("subject",true);
							$parameter[":word"]=$this->input->post("sms_body",true);
							$parameter[":buildtime"]=now();
							$parameter[":admin_num"]=$this->web_root_num;
							if($this->webdb->sqlExc($sqlStr,$parameter)){
								echo json_encode(array('RntCode'=>1,'title'=>'發送成功','Msg'=>'訊息已發送至會員！'));
							}else{
								echo json_encode(array('RntCode'=>0,'title'=>'發送失敗','Msg'=>'發送失敗，請聯絡網管人員！'));
							}
						}
					}else{
						echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'請檢查欄位是否輸入正確！'));	
					}
				}else{
					echo json_encode(array('RntCode'=>0,'title'=>'權限錯誤！','Msg'=>'很抱歉...您無此發送權限！'));	
				}
			}else{
				echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'不允許的方法！'));	
			}
		}else{
			echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'不允許跨域存取！'));
		}
	}
	
	
	public function ajax_balance(){	//AJAX取得遊戲廠商餘額
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$this->load->library('api/allgameapi');	//載入遊戲api
				$balance=$this->allgameapi->get_balance($this->input->post('mem_num',true),$this->input->post('makersnum',true));
				echo json_encode(array('makersnum'=>$this->input->post('makersnum',true),'balance'=>$balance));
			}
		}
	}

	
	//創建遊戲帳號
	function reg(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$this->load->library('api/allgameapi');	//載入遊戲API
		if($_POST){
			if($_POST["gamemaker_num"]!="" && $_POST["mem_num"]!="" && $_POST["u_password"]!=""){
				//檢查該會員是否已有此種遊戲帳號
				//print_r($this->input->post());die;
				
				$parameter=array();
				$sqlStr="select * from `games_account` where `gamemaker_num`=? and `mem_num`=?";
				$parameter[":gamemaker_num"]=$this->input->post("gamemaker_num",true);
				$parameter[":mem_num"]=$this->input->post("mem_num",true);
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if($row==NULL){
					$u_id=tb_sql("u_id","member",$this->input->post("mem_num",true));
					$log=$this->allgameapi->create_account2($u_id,$this->input->post("u_password",true),$this->input->post("mem_num",true),$this->input->post("gamemaker_num",true));
					if($log==NULL){
						$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '遊戲帳號已經建立');
						$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
					}else{
						if (isset($_REQUEST["forceadd"])){
										//先檢查是否已經於games_account 中
							$parameter=array();
							$sqlStr="select * from `games_account` where `u_id`=? and `gamemaker_num`=? and `mem_num`=?";
							$parameter[":gamemaker_num"]=$this->input->post("gamemaker_num",true);
							$parameter[":mem_num"]=$this->input->post("mem_num",true);
							$parameter[":u_id"]=account_prefix.$u_id;
							$rowCounts=$this->webdb->sqlRowCount($sqlStr,$parameter);

							if ($rowCounts == 0){
								// '無帳號，建立帳號資料在games_account 中';
								$parameter=array();
								$colSql="u_id,u_password,mem_num,gamemaker_num";
								$upSql="REPLACE INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
								$parameter=array();
								$parameter[':u_id']=account_prefix.trim($u_id);
								$parameter[':u_password']=md5($_POST["u_password"]);	//密碼加密
								$parameter[':mem_num']=$_POST["mem_num"];
								$parameter[':gamemaker_num']=$_POST["gamemaker_num"];
								$this->webdb->sqlExc($upSql,$parameter);		
								$log = '遊戲端會員帳號建立失敗，可能已經建立過。已在本地資料庫新增';


							}else{
								// '已有帳號';
								$log = '遊戲端會員帳號建立失敗。本地資料庫已有資料';
							}

						}
						$msg = array("type" => "danger", 'title' => '創建失敗！','content'=>$log);	
						$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
					}
				}else{
					$msg = array("type" => "danger", 'title' => '資料錯誤！','content'=>'該會員已有此種遊戲帳號');	
					$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
				}
			}else{
				$msg = array("type" => "danger", 'title' => '資料錯誤！','content'=>'請填寫相關資料');
				$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			}
		}else{
			//遊戲廠商下拉
			$sqlStr="select * from `game_makers`  order by `range`";	//排除黃金俱樂部 and 捕魚機
			$this->data["makers_data"]=$this->webdb->sqlRowList($sqlStr);
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
			$this->data["subtitle"]=$this->getsubtitle();
			$this->data["formAction"]=site_url(SYSTEM_URL."/Member/reg");
			$this -> data["body"] = $this -> load -> view("admin/member/reg", $this -> data,true);   
			$this -> load -> view("admin/main", $this -> data);  
			
		}
		
	}
	
	
	
	function wallet_list($mem_num=0){
		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$this->data["token"] = md5(uniqid(rand(), true));
		$this->session->set_userdata('wallet_token', $this->data["token"]);
		
		
		$this->data["returnBtn"]=($mem_num > 0 ? true : false);
		$this->data["walletBtn"]=($mem_num > 0 ? true : false);
		$this->data["mem_num"]=$mem_num;
		
		
		$parameter=array();
		$sqlStr="select * from `member_wallet` where 1=1";
		//---類型-------------------------------
		if(@$_REQUEST["sfind1"]!=""){
			$sqlStr.=" and kind=?";
			$parameter[":kind"]=@$_REQUEST["sfind1"];
		}
		
		//---帳號-------------------------------
		if(@$_REQUEST["sfind2"]!=""){
			$sqlStr.=" and mem_num in(select num from `member` where `u_id` like ?)";
			$parameter[":u_id"]="%".@$_REQUEST["sfind2"]."%";
		}
		
		//---代理-------------------------------
		if(@$_REQUEST["sfind3"]!=""){
			$sqlStr.=" and admin_num=?";
			$parameter[":admin_num"]=@$_REQUEST["sfind3"];
		}
		
		//開放時間
		if(@$_REQUEST["sfind7"]!=""){
			$sqlStr.=" and `buildtime` >=?";
			$parameter[":buildtime1"]=@$_REQUEST["sfind7"];
		}
		if(@$_REQUEST["sfind8"]!=""){			
			$sqlStr.=" and `buildtime` < ?";
			$parameter[":buildtime2"]=date('Y-m-d',strtotime(@$_REQUEST["sfind8"]."+1 day"));
		}	
		if($mem_num > 0){
			$sqlStr.=" and mem_num =?";
			$parameter[":mem_num"]=$mem_num;
		}		
		//除了最高權限 能看到所有...否則只抓該使用者的分類
		if(in_array($this->web_root_u_power,array(4,5,6))){	
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,"admin").")";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Member/wallet_list/".$mem_num."?per_page=".@$_GET['per_page'].$this->data["att"].$this->data["satt"]);
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		$config['query_string_segment']='page';
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["page"]!=""){$nowpage=@$_GET["page"];}
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

		//查詢用下拉分類
		$this->data["searchOption"]=array();
		$sqlStr="select * from `wallet_kind` where `root`=0 order by `range`";
		$rowAll=$this->webdb->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $key=>$row){
				$this->data["searchOption"][$key]=$row["num"].'_'.$row["kind"];	
			}
		}
		
		//下拉用查詢代理
		$sqlStr="select * from `admin` where `u_power`=6";
		$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);
		
							
		$this -> data["body"] = $this -> load -> view("admin/member/wallet_list", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	function ajaxWallet(){
		if(!$this->agent->is_referral()){	//判斷使用者是否別網站來的
			if($this->input->is_ajax_request()){	//檢查token是否正確
				if($this->session->userdata('wallet_token') && $this->input->post('token')==$this->session->userdata('wallet_token')){
					
					//定義不可重複的標籤
					$sysArray=array(6,8);	//會員註冊送 首儲優惠 推薦人優惠
					if(in_array($this->input->post('kind'),$sysArray)){	//當放點標籤 不可重複~~撿查會員錢包 是否已有此標籤
						$parameter=array();
						$sqlStr="select * from `member_wallet` where kind=? and mem_num=?";
						$parameter[':kind']=$this->input->post('kind',true);
						$parameter[':mem_num']=$this->input->post('mem_num',true);
						$row=$this->webdb->sqlRow($sqlStr,$parameter);
						if($row!=NULL){	//標籤存在
							echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'該會員已領取過此點數！','blance'=>getWalletTotal($this->input->post('mem_num'))));
							exit;
						}
					}
					
					$point_type=$this->input->post('point_type');
					$WalletTotal=getWalletTotal($this->input->post('mem_num',true));
					//判斷錢包點數是否足夠扣點
					if($point_type==2 && (float)$WalletTotal < (float)$this->input->post('points',true)){
						echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'錢包點數不足無法扣點！','blance'=>getWalletTotal($this->input->post('mem_num'))));
						exit;	
					}
					
					$points=$point_type==2 ? "-".$this->input->post('points',true) : $this->input->post('points',true);
					$real_points=round((float)$points * (tb_sql("point_percent","company",1) / 100),2);
					$u_power6=tb_sql("admin_num","member",$this->input->post('mem_num',true));	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					$u_power4_profit=round($real_points * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round($real_points * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round($real_points * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					//異動前點數
					$before_balance=(float)$WalletTotal;
					//異動後點數
					$after_balance=($point_type==2 ? (float)$before_balance-(float)$this->input->post('points',true) : (float)$before_balance+(float)$this->input->post('points',true));
					
					$parameter=array();
					$colSql="mem_num,kind,points,real_points,admin_num,admin_num1,admin_num2,update_num,word,buildtime";
					$colSql.=",before_balance,after_balance,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':mem_num']=$this->input->post('mem_num',true);
					$parameter[':kind']=$this->input->post('kind',true);
					$parameter[':points']=$points;
					$parameter[':real_points']=$real_points;
					$parameter[":admin_num"]= $u_power6;
					$parameter[":admin_num1"]= $u_power5;
					$parameter[":admin_num2"]= $u_power4;
					$parameter[":update_num"]= $this->web_root_num;
					$parameter[':word']=$this->input->post('word',true);
					$parameter[":buildtime"]= now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;
					$this->webdb->sqlExc($sqlStr,$parameter);
					echo json_encode(array('RntCode'=>1,'title'=>'處理成功','Msg'=>'請關閉視窗更新錢包點數！','blance'=>getWalletTotal($this->input->post('mem_num'))));
					
				}else{
					echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'Access Token錯誤或不存在！','blance'=>getWalletTotal($this->input->post('mem_num'))));
				}
			}
		}else{
			echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'不允許跨網域存取！','blance'=>getWalletTotal($this->input->post('mem_num'))));
		}
	}
	
	
	function ajaxWalletKind(){
		if($this->input->is_ajax_request()){
			if($this->input->post('point_type')){
				//定義系統處理類型
				$sysArray=array('3','4','5','9','10','11','12');
				$sysString=implode(',',$sysArray);
				$sqlStr="select * from `wallet_kind` where `root`=0 and num not in(".$sysString.")";	//排除系統自動處理類型
				if($this->input->post('point_type')==1){
					$sqlStr.=" and `type`=1";	
				}elseif($this->input->post('point_type')==2){
					$sqlStr.=" and `type`=2";
				}
				//只有網站管理者/站長管理群 才能使用管理者轉入
				if($this->web_root_u_power > 3){
					$sqlStr.=" and num <> 1";		
				}

				$sqlStr.=" order by `range`";
				$rowAll=$this->webdb->sqlRowList($sqlStr);
				echo '<option value="">請選擇</option>';
				if($rowAll!=NULL){


					foreach($rowAll as $row){
						echo '<option value="'.$row["num"].'">'.$row["kind"].'</option>';
					}
				}
			}else{
				echo '<option value="">請選擇</option>';
			}
		}
	}
	
	
	function changepws($num){	//修改密碼
		$sqlStr="select * from `member` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="u_password";
				$sqlStr="UPDATE `member` SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":u_password"]=$this->encryption->encrypt($this->input->post("u_password",true));		
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter,"member");
				
				$parameter=array();
				$colSql="mem_num,update_admin,ip,word,buildtime";
				$sqlStr="INSERT INTO `member_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$num;
				$parameter[":update_admin"]=$this->web_root_num;
				$parameter[":ip"]=$this->input->ip_address();
				$parameter[":word"]='修改密碼';
				$parameter[':buildtime']=now();
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				$msg = array("type" => "success", 'title' => '密碼修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Member/edit/".$row["num"]."?per_page=".@$_GET['per_page'].$this->data["att"]);
				
			}
		}
		
	}
	
	function delete($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->member->sqlRow($sqlStr,array(':num'=>$num),"member");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/index");
		}else{
			$sqlStr="delete from [myTable] where num=?";
			$this->member->sqlExc($sqlStr,array(':num'=>$num),"member");
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/index");
		}
		
	}
	
	//代理換線
	function agents_exchange($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `member` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/index");
		}else{		
			if($_POST){	//修改資料
				//更換會員代理
				$parameter=array();
				$upSql="UPDATE `member` SET `admin_num`=? where num=?";
				//新代理
				$admin_num = $this->input->post('admin_num',true);				
				$parameter[':admin_num']=$admin_num;
				$parameter[':num']=$num;
				$this->webdb->sqlExc($upSql,$parameter);
				//紅利單據換線處理
				if($this->input->post('points_exchange') && $this->input->post('points_exchange')=='Y'){
					$parameter=array();
					//新總代				
					$admin_num1 = tb_sql('root','admin',$admin_num);
					//新股東
					$admin_num2 = tb_sql('root','admin',$admin_num1);					
										
					$upSql="UPDATE `member_wallet` SET `admin_num`=?, `admin_num1`=?, `admin_num2`=?";	
					$parameter[':admin_num']=$admin_num;
					$parameter[':admin_num1']=$admin_num1;
					$parameter[':admin_num2']=$admin_num2;
					$upSql.=" where mem_num=?";
					$parameter[':num']=$num;
					
					if($this->input->post('points_time1')){
						$upSql.=" and `buildtime` >= ?";
						$parameter[':points_time1']=$this->input->post('points_time1',true);	
					}
					if($this->input->post('points_time2')){
						$upSql.=" and `buildtime` < ?";
						$parameter[':points_time2']=$this->input->post('points_time2',true);	
					}
					$this->webdb->sqlExc($upSql,$parameter);
				}
				//儲值單據換線處理
				if($this->input->post('orders_exchange') && $this->input->post('orders_exchange')=='Y'){
					$parameter=array();
					$upSql="UPDATE `orders` SET `admin_num`=?";	
					$parameter[':admin_num']=$this->input->post('admin_num',true);
					$upSql.=" where mem_num=?";
					$parameter[':num']=$num;
					
					if($this->input->post('orders_time1')){
						$upSql.=" and `buildtime` >= ?";
						$parameter[':orders_time1']=$this->input->post('orders_time1',true);	
					}
					if($this->input->post('orders_time2')){
						$upSql.=" and `buildtime` < ?";
						$parameter[':orders_time2']=$this->input->post('orders_time2',true);	
					}
					$this->webdb->sqlExc($upSql,$parameter);
				}
				//拋售單據換線處理
				if($this->input->post('sell_exchange') && $this->input->post('sell_exchange')=='Y'){
					$parameter=array();
					$upSql="UPDATE `member_sell` SET `admin_num`=?";	
					$parameter[':admin_num']=$this->input->post('admin_num',true);
					$upSql.=" where mem_num=?";
					$parameter[':num']=$num;
					
					if($this->input->post('orders_time1')){
						$upSql.=" and `buildtime` >= ?";
						$parameter[':sell_time1']=$this->input->post('sell_time1',true);	
					}
					if($this->input->post('orders_time2')){
						$upSql.=" and `buildtime` < ?";
						$parameter[':sell_time2']=$this->input->post('sell_time2',true);	
					}
					$this->webdb->sqlExc($upSql,$parameter);
				}
				//匯款單據換線作業
				if($this->input->post('bank_exchange') && $this->input->post('bank_exchange')=='Y'){
					$parameter=array();
					$upSql="UPDATE `member_bank_transfer` SET `admin_num`=?";	
					$parameter[':admin_num']=$this->input->post('admin_num',true);
					$upSql.=" where mem_num=?";
					$parameter[':num']=$num;
					
					if($this->input->post('bank_time1')){
						$upSql.=" and `buildtime` >= ?";
						$parameter[':bank_time1']=$this->input->post('bank_time1',true);	
					}
					if($this->input->post('bank__time2')){
						$upSql.=" and `buildtime` < ?";
						$parameter[':bank__time2']=$this->input->post('bank__time2',true);	
					}
					$this->webdb->sqlExc($upSql,$parameter);
				}
				
				//紀錄轉移紀錄
				$parameter=array();
				$colSql="mem_num,souce_agent,target_agent,points_exchange,points_time1,points_time2";
				$colSql.=",orders_exchange,orders_time1,orders_time2,sell_exchange,sell_time1,sell_time2";
				$colSql.=",bank_exchange,bank_time1,bank_time2,update_admin,buildtime";
				$sqlStr="INSERT INTO `agents_exchange_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':mem_num']=$num;
				$parameter[':souce_agent']=$this->input->post('souce_agent',true);
				$parameter[':target_agent']=$this->input->post('admin_num',true);
				$parameter[':points_exchange']=($this->input->post('points_exchange') ? 'Y' : 'N');
				$parameter[':points_time1']=($this->input->post('points_time1') ? $this->input->post('points_time1',true) : NULL);
				$parameter[':points_time2']=($this->input->post('points_time2') ? $this->input->post('points_time2',true) : NULL);
				$parameter[':orders_exchange']=($this->input->post('orders_exchange') ? 'Y' : 'N');
				$parameter[':orders_time1']=($this->input->post('orders_time1') ? $this->input->post('orders_time1',true) : NULL);
				$parameter[':orders_time2']=($this->input->post('orders_time2') ? $this->input->post('orders_time2',true) : NULL);
				$parameter[':sell_exchange']=($this->input->post('sell_exchange') ? 'Y' : 'N');
				$parameter[':sell_time1']=($this->input->post('sell_time1') ? $this->input->post('sell_time1',true) : NULL);
				$parameter[':sell_time2']=($this->input->post('sell_time2') ? $this->input->post('sell_time2',true) : NULL);
				$parameter[':bank_exchange']=($this->input->post('bank_exchange') ? 'Y' : 'N');
				$parameter[':bank_time1']=($this->input->post('bank_time1') ? $this->input->post('bank_time1',true) : NULL);
				$parameter[':bank_time2']=($this->input->post('bank_time2') ? $this->input->post('bank_time2',true) : NULL);
				$parameter[':update_admin']=$this->web_root_num;
				$parameter[':buildtime']=now();
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				$msg = array("type" => "success", 'title' => '換線作業成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Member/agents_exchange/".$row["num"]."?per_page=".@$_GET['per_page'].$this->data["att"]);
			}else{
				//下拉用查詢代理
				$sqlStr="select * from `admin` where `u_power`=6";
				$this->data["upAccount"]=$this->webdb->sqlRowList($sqlStr);
				$this->data["formAction"]=site_url(SYSTEM_URL."/Member/agents_exchange/".$row["num"]."?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Member/index?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	
				$this->data["row"]=$row;	
				
				$parameter=array();
				$sqlStr="select * from `agents_exchange_log` where mem_num=?";
				$parameter[':mem_num']=$num;
				$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
				
				//分頁相關
				$config['base_url'] = site_url(SYSTEM_URL."/Member/agents_exchange/".$num."?per_page=".@$_GET['per_page'].$this->data["att"]);
				$this->data["s_action"]=$config['base_url'];
				$config['total_rows'] = $total;
				$limit=5;	//每頁比數
				$config['query_string_segment']='page';
				$config['per_page'] = $limit;	
				$config['num_links'] = 2;
				$config['page_query_string'] = TRUE;
				$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
				$nowpage=1;
				if (@$_GET["page"]!=""){$nowpage=@$_GET["page"];}
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
				
				$this -> data["body"] = $this -> load -> view("admin/member/exchange", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
		}
		
	}
	
	
	//匯出excel
	public function excel(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=member_".time().".xls");		
		$parameter=array();
		$sqlStr="select * from `member` where phone regexp '^09[0-9]{8}$'";
		//---所屬代理-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr .=" and `admin_num`=?";	
			$parameter[":admin_num"]=@$_REQUEST["find1"];
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
		//---IP-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$ipSql="select mem_num from member_ip where login_ip = ? GROUP BY mem_num";
			$rowIP=$this->webdb->sqlRowList($ipSql,array(trim(@$_REQUEST["find4"])));
			$ipList=array();
			foreach($rowIP as $row){
				array_push($ipList,$row["mem_num"]);	
			}
			$ipList=implode(',',$ipList);
			//echo $ipList;
			$sqlStr.=" and num in ($ipList)";
			//$parameter[":find4-1"]=trim(@$_REQUEST["find4"]);
		}
		//---電話-------------------------------
		if(@$_REQUEST["find5"]!=""){
			$sqlStr.=" and `phone` like ?";
			$parameter[":phone"]="%".@$_REQUEST["find5"]."%";
		}
		
		
		//---狀態-------------------------------
		if(@$_REQUEST["find6"]!=""){
			$sqlStr.=" and `active`=?";
			$parameter[":active"]="".@$_REQUEST["find6"]."";
		}
		//登入日期
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `login_time` >=?";
			$parameter[":login_time"]=@$_REQUEST["find7"];
		}
		if(@$_REQUEST["find8"]!=""){			
			$sqlStr.=" and `login_time` <?";
			$parameter[":login_time2"]=date('Y-m-d',strtotime(@$_REQUEST["find8"]."+1 day"));
		}
		//註冊日期
		if(@$_REQUEST["find9"]!=""){
			$sqlStr.=" and `reg_time` >=?";
			$parameter[":reg_time"]=@$_REQUEST["find9"];
		}
		if(@$_REQUEST["find10"]!=""){			
			$sqlStr.=" and `reg_time` <?";
			$parameter[":reg_time2"]=date('Y-m-d',strtotime(@$_REQUEST["find10"]."+1 day"));
		}
		
		//儲值狀態
		if(@$_REQUEST["find11"]!=""){
			$eTime=now();
			switch ($_REQUEST["find11"]){
				case 'Y':	//7天內有儲值
					//儲值跟銀行匯款都算進去
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) and DATEDIFF('".$eTime."', buildtime) <= 7  GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$dList=array();
					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($dList,$row["mem_num"]);	
						}
						$dList=implode(',',$dList);
						$sqlStr.=" and num in ($dList)";
					}else{
						$sqlStr.=" and num is null";
					}
					break;
				case 'N':	//7天內沒儲值
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) and DATEDIFF('".$eTime."', buildtime) > 7  GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$d1List=array();
					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($d1List,$row["mem_num"]);	
						}
						
					}
					//排除掉 7 天內 有儲值的
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) and DATEDIFF('".$eTime."', buildtime) <= 7  GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$d2List=array();
					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($d2List,$row["mem_num"]);	
						}
					}
					$result=array_diff($d1List,$d2List);
					if(count($result)){
						$numList=implode(',',$result);
						$sqlStr.=" and num in ($numList)";	
					}else{
						$sqlStr.=" and num is null";
					}
					break;
				case 'W':	//未曾儲值
					$dSql="select mem_num from `member_wallet` where (kind=5 or kind=12) GROUP BY mem_num";	
					$rowD=$this->webdb->sqlRowList($dSql);
					$dList=array();

					if($rowD!=NULL){
						foreach($rowD as $row){
							array_push($dList,$row["mem_num"]);	
						}
						$dList=implode(',',$dList);
						$sqlStr.=" and num not in ($dList)";
					}else{
						$sqlStr.=" and num is null";
					}
					break;	
			}
		}
		
		//除了最高權限 能看到所有...否則只抓該使用者的分類
		if(in_array($this->web_root_u_power,array(4,5,6))){	
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,"admin").")";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);	
		$this -> data["result"] = $rowAll;
		$this -> load -> view("admin/member/excel", $this -> data);
	}	
	
	function ajaxGameAccount(){
		if(!$this->agent->is_referral()){	//判斷使用者是否別網站來的
			if($this->input->is_ajax_request()){
				$parameter=array();
				$sqlStr="select num from `games_account` where num=?";
				$parameter[':num']=$this->input->post('num',true);
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if($row!=NULL){
					if(preg_match('/^(?!([^a-zA-Z]+|\D+)$)[a-zA-Z0-9]{6,13}$/',$this->input->post('u_password',true))){
						if($this->input->post('u_password',true)==$this->input->post('u_password2',true)){
							$parameter=array();
							$upSql="UPDATE `games_account` SET `u_password`=? where num=".$row["num"];
							$parameter[":u_password"]=$this->encryption->encrypt($this->input->post("u_password",true));
							$this->webdb->sqlExc($upSql,$parameter);
							echo json_encode(array('RntCode'=>1,'title'=>'處理成功','Msg'=>'請關閉視窗！'));	
						}else{
							echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'密碼與確認密碼不同！'));
						}
					}else{
						echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'密碼請輸入6~13英文數字混合！'));
					}
				}else{
					echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'遊戲帳號資料不存在！'));
				}
			}else{
				echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'不允許存取的方法！'));
			}
		}else{
			echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'不允許跨網域存取！'));
		}
	}
	
	
	
	//AJAX改變訂單狀態
	function keyChange(){
		if($this->input->is_ajax_request()){
			$num=trim($this->input->post("num"));//table
			$value=trim($this->input->post("value"));//table
			if($num!='' && $value!=''){
				$parameter=array();
				$sqlStr="UPDATE `member` SET `active`=? where num=?";
				$parameter[":active"]=$value;
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				$parameter=array();
				$colSql="mem_num,update_admin,ip,word,buildtime";
				$sqlStr="INSERT INTO `member_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$num;
				$parameter[":update_admin"]=$this->web_root_num;
				$parameter[":ip"]=$this->input->ip_address();
				$parameter[":word"]='更改會員狀態';
				$parameter[':buildtime']=now();
				$this->webdb->sqlExc($sqlStr,$parameter);
			}
		}
	}
	
	//撿查帳號是否被註冊
	function chk_id($u_id){
		if($u_id!=""){
			$sqlStr="select * from `member` where u_id=?";	
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
			$sqlStr="select * from `member` where u_id=?";	
			$row=$this->webdb->sqlRow($sqlStr,array(':u_id'=>$this->input->post("u_id",true)));
			if($row==NULL){
				echo 'Y';	
			}else{
				echo 'N';
			}
		}
	}
	
	//語系類別連動
	function ajax_kind(){
		if($this->input->is_ajax_request()){
			//$table=$this->input->post("table");//table
			$ojbID=$this->input->post("ojbID");//ID
			//$nation=$this->input->post("nation");//語系
			$defValue=$this->input->post("defValue");//預設值
			$citynum=$this->input->post("citynum");//縣市Num
			$pleaseSelect=$this -> input -> post('pleaseSelect');//是否有請選擇
			$required=$this -> input -> post('required');//是否需要判斷必輸入
			$admin_num=($this->web_root_u_power > 1? $this->web_root_num : 0);	//大於0傳入當前使用者num，否則傳0
			echo '<select class="form-control selectpicker" name="'.$ojbID.'" id="'.$ojbID.'" data-live-search="true" '.($required =='true'? 'required' : '').'>';
			echo ($pleaseSelect ? '<option value="">請選擇</option>' : "");
			echo buildArea($citynum,$defValue);
			echo '</select>';
		}
	}

	
/*-----------------------------------------------------------------------------------------------*/	
	//會員需知設定畫面
	function method($nation=NULL) {
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->data["nation"]=($nation!=NULL ? $nation : $this->defaultNation());
		//文字編輯器====================================
		$this->_append_js('ckeditor/ckeditor.js');
		//============================================
		
		$kind="member";
		$nation=$this->data["nation"];
		$this->data["subtitle"]=$this->getsubtitle();
		
		$parameter=array();
		$sqlStr="select * from `method` where kind=? and nation=?";
		$parameter[":kind"]=$kind;
		$parameter[":nation"]=$nation;
		$row=$this->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){
			$parameter=array();
			$colSql="kind,nation";
			$sqlStr="INSERT INTO `method` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":kind"]=$kind;
			$parameter[":nation"]=$nation;
			$num=$this->webdb->sqlExc($sqlStr,$parameter,"method");
			$this->data['word']="";
			
			$this->data['formAction']=site_url(SYSTEM_URL."/Member/method/".$nation);
		
			$this -> data["body"] = $this -> load -> view("admin/member/method", $this -> data,true);
			$this -> load -> view("admin/main", $this -> data);
		}else{		
			if($_POST){	//修改資料
				$sqlStr="update `method` set word=? where kind=? and nation=?";
				$parameter=array(':word'=>array(@$_POST["word"]),':kind'=>$kind,':nation'=>$nation);
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg=array("type" => "success",'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Member/method/".$nation);
			}else{
				$this->data['word']=$row['word'];
				$this->data['formAction']=site_url(SYSTEM_URL."/Member/method/".$nation);
				$this -> data["body"] = $this -> load -> view("admin/member/method", $this -> data,true);
				$this -> load -> view("admin/main", $this -> data);
				
			}
			
		}
	}
	
	
} 