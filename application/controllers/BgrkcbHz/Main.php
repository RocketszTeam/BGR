<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Main extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		
	}
	
	public function index(){
		$this->isLogin();//檢查登入狀態和葉面權限，需要用在乎叫
		
		$this -> data["body"] = $this -> load -> view("sysAdmin/admin_index", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
	
	
	public function mytest(){
		if ($this->agent->is_referral()){	//檢查網域
			echo 'Y';	
		}else{
			echo 'N';
		}
		
	}
	
	function adwidth_change(){
		if($this->input->post('adwidth')){
			if ($this->web_root_login_status){
				if($this->web_root_u_power > 1){
					$parameter=array();
					$sqlStr="update [myTable] set `adwidth`=? where u_id=? and num=?";
					$parameter[':adwidth']=$this->input->post('adwidth',true);
					$parameter[':u_id']=$this->web_root_u_id;
					$parameter[':num']=$this->web_root_num;
					$this->my_config->sqlExc($sqlStr,$parameter,"admin");
				}else{
					$sqlStr="update [myTable] set `adwidth`=?";
					$this->my_config->sqlExc($sqlStr,array(':adwidth'=>$this->input->post('adwidth',true)),"company");
				}
			}
		}
	}
} 