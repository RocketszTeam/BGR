<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Openav extends Core_controller{
	private $url_match='/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i';	//遊戲url驗證
	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
		
		$this->load->library('api/avapi');	//AV		
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Manger/register");
			exit;	
		}
	}
	
	public function index(){
		$sqlStr="select mem_num from `av_point_log` where mem_num=".$this->memberclass->num();
		$row=$this->webdb->sqlRow($sqlStr);
		
		$loginUrl=$this->avapi->forward_game($this->memberclass->u_id());	//取得登入網址 並創建帳號
		if(preg_match($this->url_match,$loginUrl)){
			if($row==NULL){	//會員還沒領過av點數
				$logMsg=$this->avapi->deposit($this->memberclass->u_id(),300,$this->memberclass->num());
				if($logMsg==NULL){	//點數發送成功紀錄下來已領
					$parameter["mem_num"]=$this->memberclass->num();
					$parameter["u_id"]=$this->memberclass->u_id();
					$parameter["point"]=300;
					$parameter["buildtime"]=now();
					$this->db->insert('av_point_log', $parameter); 
					//$this->db->affected_rows()
				}
			}
			//最後不管放點成功還是失敗~~直接轉向AV~~~如果創建帳號失敗~~~關掉本頁面 再重開就可以再次創建 若沒領到點數也是如此操作
			header("Location:".$loginUrl);
			exit;	
		}else{
			scriptCloseMsg('取得入口連結失敗');	
			exit;
		}
		
	}
	
	
	
} 