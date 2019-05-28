<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Message extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
		//$this -> load -> model("sysAdmin/news_model", "news", true);
		
	}
	
	
	public function index($page=1){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			scriptMsg($logMsg,"Index?rtn=".urlencode($this -> uri -> uri_string()));	//
			exit;
		}else{
			$this -> data["alert"]= $this -> session -> flashdata("alert");

			$parameter=array();
			$sqlStr="select * from `member_talk` where `mem_num`=?";
			$parameter[':mem_num']=$this->memberclass->num();
			$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
			
			$limit=5;//每頁筆數
			//init pagination		
			$config['base_url'] = site_url("Message/index");
			$config['per_page'] = $limit;	
			$config['num_links'] = 2;
			$config['total_rows'] = $total;
			$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
			$nowpage=1;
			if (@$page!=""){$nowpage=$page;}
			if ($nowpage>$maxpage){$nowpage=$maxpage;}	
			$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
			$this -> data["nowpage"]=$nowpage;
			$this -> data["maxpage"]=$maxpage;
			if($nowpage > 1){
				$prevpage = $nowpage - 1;
			} else {
				$prevpage = 1;						
			}
			if(($nowpage + 1 ) <  $maxpage){
				$nextpage = $nowpage + 1;	
			} else {
				$nextpage = $maxpage;
			}
			$this -> data["prevpage"]=$prevpage;
			$this -> data["nextpage"]=$nextpage;			
			$this -> data["result"] = $rowAll;
			
			//產生分頁連結
			$this -> load -> library("pagination");
			$this -> pagination -> doConfigMobile($config);
			$this -> data["pagination"] = $this -> pagination -> create_links();


			if(!$this->agent->is_mobile()){	//電腦版
				$this -> load -> view("www/message", $this -> data);
			}else{	//手機板			
				$this -> load -> view("mobile/message", $this -> data);
			}				
		}
	}
	
	public function send_message(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			scriptMsg($logMsg,"Index?rtn=".urlencode($this -> uri -> uri_string()));	//
			exit;
		}else{
			$this -> data["alert"]= $this -> session -> flashdata("alert");
			//取出分類
			$sqlStr="select * from `member_talk_kind` where `root`=0 and `nation`='TW' order by `range`";
			$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
			
			if(!$this->agent->is_mobile()){	//電腦版
				$this -> load -> view("www/message_send", $this -> data);
			}else{	//手機板			
				$this -> load -> view("mobile/message_send", $this -> data);
			}							
			
		}
	}
	
	public function send_do(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			scriptMsg($logMsg,"Index?rtn=".urlencode($this -> uri -> uri_string()));	//
			exit;
		}else{
			if(!$this->agent->is_referral()){
				if($this->input->post('kind') && $this->input->post('subject') && $this->input->post('word')){
					$parameter=array();
					$colSql="mem_num,kind,subject,word,buildtime";
					$sqlStr="INSERT INTO `member_talk` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':mem_num']=$this->memberclass->num();
					$parameter[':kind']=$this->input->post('kind',true);
					$parameter[':subject']=$this->input->post('subject',true);
					$parameter[':word']=$this->input->post('word',true);
					$parameter[':buildtime']=now();
					if($this->webdb->sqlExc($sqlStr,$parameter)){
						$this -> session -> set_flashdata("alert",'您的留言已經送出，我們將盡快回覆');
						scriptMsg("","Message/send_message");
						exit;
					}else{
						$this -> session -> set_flashdata("alert",'留言失敗，請重新填寫');
						scriptMsg("","Message/send_message");
						exit;
					}
				}else{
					$this -> session -> set_flashdata("alert",'請確認欄位是否確實填寫');
					scriptMsg("","Message/send_message");
					exit;
				}
			}else{
				$this -> session -> set_flashdata("alert",'您的網域不被允許');
				scriptMsg("","Message/send_message");
				exit;
			}
		}
	}
	
	
	//系統訊息
	public function service($page=1){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			scriptMsg($logMsg,"Index?rtn=".urlencode($this -> uri -> uri_string()));	//
			exit;
		}else{
			$this -> data["alert"]= $this -> session -> flashdata("alert");
			$parameter=array();
			$sqlStr="select * from `member_service` where `mem_num`=?";
			$parameter[':mem_num']=$this->memberclass->num();
			$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
			
			$limit=5;//每頁筆數
			//init pagination		
			$config['base_url'] = site_url("Message/service");
			$config['per_page'] = $limit;	
			$config['num_links'] = 2;
			$config['total_rows'] = $total;
			$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
			$nowpage=1;
			if (@$page!=""){$nowpage=$page;}
			if ($nowpage>$maxpage){$nowpage=$maxpage;}	
			$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
			$this -> data["nowpage"]=$nowpage;
			$this -> data["maxpage"]=$maxpage;
			$this -> data["result"] = $rowAll;
			
			//產生分頁連結
			$this -> load -> library("pagination");
			$this -> pagination -> doConfigMobile($config);
			$this -> data["pagination"] = $this -> pagination -> create_links();


			
			if(!$this->agent->is_mobile()){	//電腦版
				$this -> load -> view("www/service_list", $this -> data);
			}else{	//手機板			
				$this -> load -> view("mobile/service_list", $this -> data);
			}					
			
		}
	}
	
	public function ajax_read(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->get('num')){
					$parameter=array();
					$sqlStr="UPDATE `member_service` SET `is_read`=1,`updatetime`='".now()."'";
					$sqlStr.=" where num=?";
					$parameter[':num']=$this->input->get('num',true);
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
			}
		}
	}
	
	public function talk_check(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$r=0;
				if($this->input->get('num')){
					$sqlStr="select num from `member_talk` where (`re_word` is not null or `re_word` <>'') and `is_read`=0 and mem_num=?";
					$r+=$this->webdb->sqlRowCount($sqlStr,array('num'=>$this->input->get('num',true)));
					echo json_encode(array('RntCode'=>'Y','count'=>$r));
				}else{
					echo json_encode(array('RntCode'=>'N','count'=>0));	
				}
			}
		}
	}
	public function service_check(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$r=0;
				if($this->input->get('num')){
					$sqlStr="select num from `member_service` where `is_read`=0 and mem_num=?";
					$r+=$this->webdb->sqlRowCount($sqlStr,array('num'=>$this->input->get('num',true)));
					echo json_encode(array('RntCode'=>'Y','count'=>$r));
				}else{
					echo json_encode(array('RntCode'=>'N','count'=>0));	
				}
			}
		}
	}
	
	
	public function ajax_menu_message(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$r=0;
				if($this->input->get('num')){
					$sqlStr="select num from `member_talk` where (`re_word` is not null or `re_word` <>'') and `is_read`=0 and mem_num=?";
					$r+=$this->webdb->sqlRowCount($sqlStr,array('num'=>$this->input->get('num',true)));
					$sqlStr="select num from `member_service` where `is_read`=0 and mem_num=?";
					$r+=$this->webdb->sqlRowCount($sqlStr,array('num'=>$this->input->get('num',true)));
					echo json_encode(array('RntCode'=>'Y','count'=>$r));
				}else{
					echo json_encode(array('RntCode'=>'N','count'=>0));	
				}
			}
		}
	}
	
	public function ajax_talk_read(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->get('num')){
					$parameter=array();
					$sqlStr="UPDATE `member_talk` SET `is_read`=1,`updatetime`='".now()."'";
					$sqlStr.=" where num=?";
					$parameter[':num']=$this->input->get('num',true);
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
			}
		}
	}
	
		
} 