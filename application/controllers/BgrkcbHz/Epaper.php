<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Epaper extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		$this->load->library('mailer');
		$this -> load -> model("sysAdmin/epaper_model", "epaper");
		
		$this->data["openFind"]="Y";//是否啟用搜尋
		
		
	}
	
	public function index(){		
		$this->isLogin();//檢查登入狀態
		$parameter=array();
		$sqlStr="select * from [myTable] where 1=1";
		//---語系-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `nation`=?";	
			$parameter[":nation"]=@$_REQUEST["find1"];
		}
		//---主題-------------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `subject` like ?";
			$parameter[":subject"]="%".@$_REQUEST["find2"]."%";
		}
		/*//---狀態-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `active`=?";
			$parameter[":active"]=@$_REQUEST["find4"];
		}
		
		//----防止最高權限帳號被搜尋-------
		if($this->web_root_u_power!=1){
			$sqlStr.=" and u_power <>1 and u_power > ?";
			$parameter[":root_power"]=$this->web_root_u_power;	
		}*/		
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		
		$total=$this->epaper->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL),"epaper"); //總筆數
		//分頁相關
		$config['base_url'] = base_url()."index.php/sysAdmin/epaper/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->admin->sqlRowList($sqlStr,$parameter,"epaper");		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
					
		$this -> data["body"] = $this -> load -> view("sysAdmin/epaper/index", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
	
	
	function create(){	//新增選項
		$this->isLogin();//檢查登入狀態
		//文字編輯器====================================
		$this->_append_js('ckeditor/ckeditor.js');
		//============================================
		if($_POST){	//新增資料
			$parameter=array();
			$colSql="subject,word,buildtime,sendcount,admin_num,nation";
			$sqlStr="INSERT INTO [myTable] (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":subject"]=trim($this->input->post("subject",true));
			$parameter[":word"]=trim($this->input->post("word"));
			$parameter[":buildtime"]=now();
			$parameter[":sendcount"]=0;
			$parameter[":admin_num"]=($this->web_root_u_power > 1 ? $this->web_root_num : 0);
			$parameter[":nation"]=$this->input->post("nation",true);
			if(!$this->admin->sqlExc($sqlStr,$parameter,"epaper")){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, "sysAdmin/epaper/index");
			}
		}else{
			$this->data["formAction"]=site_url("sysAdmin/epaper/create");
			$this->data["todo"]="add";
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
			$this -> data["body"] = $this -> load -> view("sysAdmin/epaper/form", $this -> data,true); 
			$this -> load -> view("sysAdmin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){
		$this->isLogin();//檢查登入狀態
		//文字編輯器====================================
		$this->_append_js('ckeditor/ckeditor.js');
		//============================================
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->admin->sqlRow($sqlStr,array(':num'=>$num),"epaper");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/epaper/index");
		}else{		
			if($_POST){	//修改資料	
				$parameter=array();
				$colSql="subject,word,nation";
				$sqlStr="UPDATE [myTable] SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":subject"]=trim($this->input->post("subject",true));
				$parameter[":word"]=trim($this->input->post("word"));
				$parameter[":nation"]=$this->input->post("nation",true);
				$parameter[":num"]=$num;
				$this->admin->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, "sysAdmin/epaper/index");
				
			}else{
				$this->data["nation"]=($row['nation']!=NULL ? $row['nation'] : $this->defaultNation());			
				$this->data["formAction"]=site_url("sysAdmin/epaper/edit/".$row["num"]);
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this -> data["body"] = $this -> load -> view("sysAdmin/epaper/form", $this -> data,true); 
				$this -> load -> view("sysAdmin/main", $this -> data);	
				
			}
			
		}		
	}
		
	function delete($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->admin->sqlRow($sqlStr,array(':num'=>$num),"epaper");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/epaper/index");
		}else{
			$sqlStr="delete from [myTable] where num=?";
			$this->admin->sqlExc($sqlStr,array(':num'=>$num),"epaper");
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/epaper/index");
		}
		
	}
	
	function send($num=0){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->admin->sqlRow($sqlStr,array(':num'=>$num),"epaper");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/epaper/index");
		}else{				
			$this->data["row"]=$row;	
				
			$this-> data["subtitle"]=$this->getsubtitle();
			
			$this -> data["body"] = $this -> load -> view("sysAdmin/epaper/send", $this -> data,true); 
			$this -> load -> view("sysAdmin/main", $this -> data);	
				
			
		}		
	}
	
	//AJAX批次發送電子報
	function sendAjax(){
		if ($this -> input -> is_ajax_request()) {
			$target="";
			if ($this -> input -> post('service_name')!=""){$com_name=$this -> input -> post('service_name');}
			if ($this -> input -> post('service_mail')!=""){$com_mail=$this -> input -> post('service_mail');}
			$nation=($this -> input -> post('nation')!=""?trim($this -> input -> post('nation')):$this->defaultNation());
			
			$sqlStr="select * from [myTable] where num=?";
			$row=$this->epaper->sqlRow($sqlStr,array(trim($this -> input -> post('num'))));
			if($row==NULL){
				echo json_encode(array('RndCode'=>999,'desc'=>'資料不存在!'),"epaper");
			}else{
				$sendCount=$this -> input -> post('sendCount');	//發送成功次數
				$failCount=$this -> input -> post('failCount');	//發送失敗
			
				$subject = $row["subject"];  //主旨
				/* 內容 */
				$message ='<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'.$com_name.'電子報</title></head><body>';
				$message .=$row["word"];
				$message .='</body></html>';
				
				$toMail=array();	
				if (@$_POST["mail_target"]=="1" || @$_POST["mail_target"]=="2"){	//指定會員
					if (trim(@$_POST["custom_mail"])!=""){
						$toMail2=explode(chr(10),str_replace(chr(13),"",@$_POST["custom_mail"]));
						for ($i=0;$i<count($toMail2);$i++){
							if($toMail2[$i]!=""){
								array_push($toMail,$toMail2[$i]);	//將要發電子報 的人 加入清單
							}
						}
						$target.=($target!=''?'、':'').'指定信箱';
					}
				}
				
				if (@$_POST["mail_target"]=="1" || @$_POST["mail_target"]=="3"){	//電子報 訂閱者
					$sqlStr="select `email` from [myTable] where `nation`=?";
					$rowAll=$this->epaper->sqlRowList($sqlStr,array($nation),'epaper_order');
					if($rowAll!=NULL){
						foreach($rowAll as $row){
							array_push($toMail,$row["email"]);	//將要發電子報 的人 加入清單
						}
						$target.=($target!=''?'、':'').'電子報訂閱者';
					}
				}
				
				if (@$_POST["mail_target"]=="1" || @$_POST["mail_target"]=="4"){	//電閱電子報會員
					$sqlStr="select `email` from [myTable] where `nation`=? and `epaper`='Y' and `active`='Y'";
					$rowAll=$this->epaper->sqlRowList($sqlStr,array($nation),'member');
					if($rowAll!=NULL){
						foreach($rowAll as $row){
							array_push($toMail,$row["email"]);	//將要發電子報 的人 加入清單
						}
						$target.=($target!=''?'、':'').'訂閱電子報的會員';
					}
				}
				
				if(count($toMail) > 0){			
					$limit=(int)trim($this -> input -> post('mail_limit'));
					$nowpage=(int)trim($this -> input -> post('nowpage'));
					$total=count($toMail);
					$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
					$index=($nowpage-1) * $limit;
										
					for($i=$index;$i < ($nowpage*$limit);$i++){
						if($i==count($toMail)){break;}
						if($toMail[$i]!=""){
							$log=mailSend($subject,$message,$toMail[$i]);
							if ($log==NULL){$sendCount++;}	//發送成功
							else{$failCount++;}	
							//$failCount++;
						}
					}
					
					if($nowpage==$maxpage){	//所有信件發送完畢
						//寫入發送紀錄
						if ($sendCount==0 && $failCount==0){
							$msg='無發送目標';
						}else{
							$msg='共發送了'.$sendCount.'封';
							if ($failCount!=0){$msg.='，共失敗'.$failCount.'封';}
						}
						
						$parameter=array();
						
						$reg_time=now();
						//發送紀錄
						$colSql="epaper_num,reg_time,demo,admin_num";
						$sqlStr="INSERT INTO [myTable] (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
						array_push($parameter,$this -> input -> post('num'));
						array_push($parameter,$reg_time);
						array_push($parameter,($this->isMultiLanguage() ? $this->languageText($nation).'-' : '').'發送對象：'.$target.'。'.$msg);
						array_push($parameter,($this->web_root_u_power > 1 ? $this->web_root_num : 0));
						$this->epaper->sqlExc($sqlStr,$parameter,'epaper_log');
						
						////更新發送次數
						$parameter=array();
						$sqlStr="UPDATE [myTable] set lastsend=?,sendcount=sendcount+1 where num=?";
						array_push($parameter,$reg_time);
						array_push($parameter,$this -> input -> post('num'));
						$this->epaper->sqlExc($sqlStr,$parameter,'epaper');
						
						echo json_encode(array('RndCode'=>0,'desc'=>'發送完畢，'.$msg));
					}else{
						$nowpage++;
						echo json_encode(array('RndCode'=>1,
												'desc'=>'發送中.....',
												'nowpage'=>$nowpage,
												'sendCount'=>$sendCount,
												'failCount'=>$failCount,
												'maxpage'=>$maxpage
											));
					}
				}else{
					echo json_encode(array('RndCode'=>999,'sendCount'=>$sendCount,'failCount'=>$failCount,'desc'=>'無任何發送清單!'));
				}	
			}
		}
	}
	
	function order(){		
		$this->isLogin();//檢查登入狀態
		$parameter=array();
		$sqlStr="select * from [myTable] where 1=1";
		//---語系-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `nation`=?";	
			$parameter[":nation"]=@$_REQUEST["find1"];
		}
		//---email-------------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `email` like ?";
			$parameter[":subject"]="%".@$_REQUEST["find2"]."%";
		}
			
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		
		$total=$this->epaper->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL),"epaper_order"); //總筆數
		//分頁相關
		$config['base_url'] = base_url()."index.php/sysAdmin/epaper/order?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `reg_time` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->admin->sqlRowList($sqlStr,$parameter,"epaper_order");		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
				
		$this -> data["body"] = $this -> load -> view("sysAdmin/epaper/order", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
	
	function order_delete($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->admin->sqlRow($sqlStr,array(':num'=>$num),"epaper_order");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/epaper/order");
		}else{
			$sqlStr="delete from [myTable] where num=?";
			$this->admin->sqlExc($sqlStr,array(':num'=>$num),"epaper_order");
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/epaper/order");
		}
		
	}
	
	
	function epaper_log($num=NULL){		
		$this->isLogin();//檢查登入狀態
		$parameter=array();
		$sqlStr="select * from [myTable] where 1=1";
		//---語系-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `nation`=?";	
			$parameter[":nation"]=@$_REQUEST["find1"];
		}
		//---email-------------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `email` like ?";
			$parameter[":subject"]="%".@$_REQUEST["find2"]."%";
		}
			
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		
		if($num!=""){
			$sqlStr.=" and `epaper_num`=?";
			$parameter[":epaper_num"]=$num;
		}
		
		$total=$this->epaper->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL),"epaper_log"); //總筆數
		//分頁相關
		$config['base_url'] = base_url()."index.php/sysAdmin/epaper/epaper_log?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `reg_time` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->admin->sqlRowList($sqlStr,$parameter,"epaper_log");		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
				
		$this -> data["body"] = $this -> load -> view("sysAdmin/epaper/log", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
} 