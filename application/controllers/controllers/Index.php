<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Index extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
		$this->data["mem2"]=$this->encryption->decrypt($this->input->cookie('mem2',true));
		
		$this->data["notIndex"]='N';
	}
	
	public function index(){
		
		$logMsg=$this->memberclass->isLogin();
		//針對會員登入異常作處理防止串改cookies 以及重複登入作處理
		if($logMsg=='abnormal' || $logMsg=='loginAgin'){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index");
			exit;
		}
	    
		
		// if(!$this->agent->is_mobile()){	//電腦版
			$this -> load -> view("www/index", $this -> data);
		// }else{	//手機板
		// 	$this -> load -> view("mobile/index", $this -> data);
		// }
	}
	
	public function active_show(){
		if(!$this->input->get('num')){
			$this -> session -> set_flashdata("alertMsg",'參數錯誤');
			scriptMsg("","Index");
			exit;
		}else{
			$parameter=array(':num'=>$this->input->get('num',true));
			$sqlStr="select * from `active` where nation='TW'".time_sql()." and `num`=?";
			$this->data["rowActive"]=$this->webdb->sqlRow($sqlStr,$parameter);
			if($this->data["rowActive"]==NULL){
				$this -> session -> set_flashdata("alertMsg",'優惠訊息不存在');
				scriptMsg("","Index");
				exit;
			}
			// if(!$this->agent->is_mobile()){	//電腦版
				$this -> load -> view("www/active_show.php", $this -> data);
			// }else{	//手機板
				// $this -> load -> view("mobile/active_show.php", $this -> data);
			// }
		}
	}
	
	
	
	
	public function check_login(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$logMsg=$this->memberclass->isLogin();
				if($logMsg==NULL){
					echo json_encode(array('RntCode'=>'Y','Msg'=>''));	
				}else{
					$this -> session -> set_flashdata("alertMsg",$logMsg);
					echo json_encode(array('RntCode'=>'N','Msg'=>$logMsg));	
				}
			}
		}
	}
	
	public function ajax_login(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('chknum') && strtolower($this->input->post('chknum',true))==strtolower($this->session->userdata('checknum'))){	//驗證碼檢查
					if($this->input->post('login_u_id') && $this->input->post('login_u_password')){
						$u_id=$this->input->post('login_u_id',true);
						$u_password=$this->input->post('login_u_password',true);
						//$logMsg=$this->memberclass->login($u_id,$u_password,$this->data["Web_CustNum"]);
						$logMsg=$this->memberclass->login($u_id,$u_password,$this->data["Web_CustNum"],$this->input->post('remember'));
						if($logMsg==NULL){	//登入成功
							$rtn=$this->input->post('rtn');
							echo json_encode(array('RntCode'=>'Y','Msg'=>'','rtn'=>($rtn ? $rtn : "/Index")));
						}else{
							echo json_encode(array('RntCode'=>'N','Msg'=>$logMsg,'rtn'=> "/Index"));
						}
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'帳號或密碼不允許空白'));
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'驗證碼錯誤'));
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'網域不被允許'));
		}
	}
	
	
	
	public function logout(){
		$this->memberclass->logout();
		scriptMsg('',"Index");
		exit;
	}
		
} 