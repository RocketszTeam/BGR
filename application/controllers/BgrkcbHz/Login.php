<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");


class Login extends Core_controller{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		$this->data["token"] = md5(uniqid(rand(), true));
		$this->session->set_userdata('code_token', $this->data["token"]);
		if($this->chkDesignMode()){
			$this->data["DesignBtn"]="DesignMode";
		}
		
		//print_r($_SESSION);
		$this -> load -> view("admin/login.php", $this -> data);
	}
	
	
	public function logout(){
		$cookie=array('name'=>'login_root',
					  'value'=>'',
					  'expire'	=>time() - 86400,
					  'path'   => '/'
					 );
		$this->input->set_cookie($cookie);	
		$this->session->unset_userdata('ckfinderUrl');
		$this->session->sess_destroy();
		redirect(SYSTEM_URL."/Login");		 
	}
	
	
	public function adminLogin(){
		if(!$this->agent->is_referral()){	//判斷使用者是否別網站來的
			if(strtolower($this->input->post('chknum',true))==strtolower($this->session->userdata('checknum'))){	//通過驗證碼
				$parameter=array();
				$sqlStr="select * from `admin` where u_id=? and u_password=? and `enable`=1";
				$parameter[":u_id"]=$this->input->post('u_id',true);
				$parameter[":u_password"]=md5($this->input->post('u_password',true));
				$row=$this->webdb->sqlRow($sqlStr,$parameter,"admin");
				if($row!=NULL){
					$w=false;
					$login_code = md5(uniqid(rand(), true));	//登入識別碼
					$ckvalue=$row["num"];  //記錄流水號
					$ckvalue.="|".$row["u_id"];  //記錄帳號
					$ckvalue.="|".urlencode($row["u_name"]);  //記錄使用者
					$u_logintime = now();
					$ckvalue.="|".$u_logintime;   //記錄登入時間
					$u_loginip=$this->input->ip_address();		   //記錄IP
					
					$sqlStr="select * from  `admin_group` where u_power = ?";
					$parameter=array(':u_power' => $row["u_power"]);
					$row2=$this->webdb->sqlRow($sqlStr,$parameter,"admin_group");
					if($row2!=NULL){
						if ($row["active"]=="Y"){
							$ckvalue.="|".$row["u_power"];     //記錄權限代碼
							$ckvalue.="|".urlencode($row2["power_name"]);  //記錄權限名稱
							$ckvalue.="|".$row2["power_list"]; //記錄權限列表
							$ckvalue.="|".$login_code;	//登入識別碼
							$w=true;					
						}else{
							scriptMsg("帳號已停權，請洽管理者!",SYSTEM_URL."/Login");			
						}	
					}else{
						scriptMsg("權限未開放，請洽管理者!", SYSTEM_URL."/Login");
					}
					
					if ($row["u_power"]=="1"){ //設計師權限群組
						$this->load->library('smsclass');
						$subject='系統管理者登入認證';
						$SMSCODE=mt_rand(1111,9999);	//驗證碼
						$content='您的系統登入驗證碼為：【'.$SMSCODE.'】';
						if($this->smsclass->sendSMS($content, $row["phone"],$subject)){
							$this->session->set_userdata('sysSMSCode',$SMSCODE);	//記錄發送的驗證碼
							echo '<form id="sysForm" method="post" action="'.site_url(SYSTEM_URL."/Login").'">';
							echo '<input type="hidden" name="sys_num" value="'.$row["num"].'" />';
							echo '<input type="hidden" name="formAction" value="'.site_url(SYSTEM_URL."/Login/sysSMSLogin").'" />';
							echo '</form>';
							echo '<script type="text/javascript">document.getElementById("sysForm").submit();</script>';
							exit;
						}else{
							$this->session->unset_userdata('sysSMSCode');
							scriptMsg("系統驗證碼發送錯誤，請重新操作",SYSTEM_URL."/Login");
							exit;
						}
					}
									
					if ($w){								
						$sqlStr="UPDATE `admin` SET u_logintime=?, u_loginip=?,`login_code`=? WHERE num=?";
						$parameter=array(':u_logintime'=>$u_logintime, 
										 ':u_loginip'=>$u_loginip,
										 ':login_code'=>$login_code,
										 ':num'=>$row["num"]);
						$this->webdb->sqlExc($sqlStr,$parameter,"admin");
						//紀錄登入時間跟IP
						$sqlStr="INSERT INTO `admin_login`(`admin_num`,`login_ip`,`buildtime`)";
						$sqlStr.=" VALUES(".$row["num"].",'".$u_loginip."','".$u_logintime."')";
						$this->webdb->sqlExc($sqlStr);
							
						$cookie=array('name'=>'login_root',
									  'value'=>$this->encryption->encrypt($ckvalue),
									  'expire'	=>0,
									  'path'   => '/'
									 );
							
						$this->input->set_cookie($cookie);
						//設定編輯器選取圖片網址為網址路徑
						$this->session->set_userdata('ckfinderUrl', site_url().'upload/');
						scriptMsg("",SYSTEM_URL."/Char/index");
					}
				}else{
					scriptMsg("帳號或密碼錯誤,請重新輸入!!", SYSTEM_URL."/Login");
				}						
			}else{
				scriptMsg("驗證碼錯誤,請重新輸入!!", SYSTEM_URL."/Login");
			}
		}else{
			scriptMsg("Host Name Error", SYSTEM_URL."/Login");
		}
	}
	
	//設計者登入簡訊認證
	public function sysSMSLogin(){
		if(!$this->agent->is_referral()){	//判斷使用者是否別網站來的
			if(strtolower($this->input->post('sms_code',true))==strtolower($this->session->userdata('sysSMSCode'))){	//簡訊驗證碼
				$parameter=array();
				$sqlStr="select * from `admin` where num=?";
				$parameter[":sys_num"]=$this->input->post('sys_num',true);
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if($row!=NULL){
					$w=false;
					$login_code = md5(uniqid(rand(), true));	//登入識別碼
					$ckvalue=$row["num"];  //記錄流水號
					$ckvalue.="|".$row["u_id"];  //記錄帳號
					$ckvalue.="|".urlencode($row["u_name"]);  //記錄使用者
					$u_logintime = now();
					$ckvalue.="|".$u_logintime;   //記錄登入時間
					$u_loginip=$this->input->ip_address();		   //記錄IP
					
					$sqlStr="select * from  `admin_group` where u_power = ?";
					$parameter=array(':u_power' => $row["u_power"]);
					$row2=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row2!=NULL){
						if ($row["active"]=="Y"){
							$ckvalue.="|".$row["u_power"];     //記錄權限代碼
							$ckvalue.="|".urlencode($row2["power_name"]);  //記錄權限名稱
							$ckvalue.="|".$row2["power_list"]; //記錄權限列表
							$ckvalue.="|".$login_code;	//登入識別碼
							$w=true;					
						}else{
							$this->session->unset_userdata('sysSMSCode');
							scriptMsg("帳號已停權，請洽管理者!",SYSTEM_URL."/Login");	
							exit;		
						}	
					}else{
						$this->session->unset_userdata('sysSMSCode');
						scriptMsg("權限未開放，請洽管理者!", SYSTEM_URL."/Login");
						exit;
					}
					if ($w){								
						$sqlStr="UPDATE `admin` SET u_logintime=?, u_loginip=?,`login_code`=? WHERE num=?";
						$parameter=array(':u_logintime'=>$u_logintime, 
										 ':u_loginip'=>$u_loginip,
										 ':login_code'=>$login_code,
										 ':num'=>$row["num"]);
						$this->webdb->sqlExc($sqlStr,$parameter);	
						$cookie=array('name'=>'login_root',
									  'value'=>$this->encryption->encrypt($ckvalue),
									  'expire'	=>0,
									  'path'   => '/'
									 );
						$this->input->set_cookie($cookie);
						//設定編輯器選取圖片網址為網址路徑
						$this->session->set_userdata('ckfinderUrl', site_url().'upload/');
						$this->session->unset_userdata('sysSMSCode');
						scriptMsg("",SYSTEM_URL."/Char/index");
						exit;
					}
				}else{
					$this->session->unset_userdata('sysSMSCode');
					scriptMsg("系統管理者不存在!!", SYSTEM_URL."/Login");
					exit;
				}
			}else{
				$this->session->unset_userdata('sysSMSCode');
				scriptMsg("簡訊驗證碼錯誤，請重新登入", SYSTEM_URL."/Login");
				exit;
			}
		}else{
			$this->session->unset_userdata('sysSMSCode');
			scriptMsg("Host Name Error", SYSTEM_URL."/Login");
			exit;
		}
	}
	
	//設計者登入IP限定
	public function DesignLogin(){
		if($this->chkDesignIP()){
			if($this->input->post("designLogin")){
				$ckvalue="0";  //記錄流水號
				$ckvalue.="|Designer";  //記錄帳號
				$ckvalue.="|設計師";  //記錄使用者
				$ckvalue.="|".now();   //記錄登入時間
				$ckvalue.="|1";     //記錄權限代碼
				$ckvalue.="|EZ";  //記錄權限名稱
				$ckvalue.="|"; //記錄權限列表
				$ckvalue.="|";	//登入識別碼
				$cookie=array('name'=>'login_root',
							  'value'=>$this->encryption->encrypt($ckvalue),
							  'expire'	=>time() + 86400,
							  'path'   => '/'
							 );
				$this->input->set_cookie($cookie);	
				
				//設定編輯器選取圖片網址為網址路徑
				$this->session->set_userdata('ckfinderUrl', site_url().'upload/');
				/*
				$cookie2=array('name'=>'ckfinderUrl',
							   'value'=>site_url().'upload/',
							   'expire'=>time() + 86400,
							   'path'   => '/'
							   );
				$this->input->set_cookie($cookie2);
				*/
								 
				redirect(SYSTEM_URL."/Char/index");	
			}else{
				redirect(SYSTEM_URL."/Login");
			}
		}else{
			redirect(SYSTEM_URL."/Login");
		}
	}
	
	function refresh_token(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$token = md5(uniqid(rand(), true));
				$this->session->set_userdata('code_token', $token);
				echo json_encode(array('token'=>$token));
			}
		}
	}
	
	
} 