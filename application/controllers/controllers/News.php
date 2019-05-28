<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class News extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
	}
	
	public function index(){
		
		
		$this -> load -> view("www/news.php", $this -> data);
	}

	public function detail(){
		if(!$this->input->get('num')){
			$this -> session -> set_flashdata("alertMsg",'參數錯誤!!');
			scriptMsg('',$this->agent->referrer());
			exit;
		}else{
			$sqlStr="select * from `news` where num=?".time_sql();
			$parameter=array(':num'=>$this->input->get('num',true));
			$this->data["row"]=$this->webdb->sqlRow($sqlStr,$parameter);
			if($this->data["row"]!=NULL){
				$this -> load -> view("www/news_detail", $this -> data);
			}else{
				$this -> session -> set_flashdata("alertMsg",'最新消息不存在!!');
				scriptMsg('',$this->agent->referrer());
				exit;
			}
		}
	}
		
} 
