<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Memberclass {
	var $CI;
	var $loginType='S';	//C=cookies;S=session
	var $mem_login;
	public function __construct(){	
		$this->CI =&get_instance();
		$this->CI->load->library('api/allgameapi');	//載入遊戲api
		date_default_timezone_set("Asia/Taipei");
		if($this->loginType=='C'){
			$this->mem_login=$this->CI->input->cookie('mem_login',true);
		}else{
			$this->mem_login=$this->CI->session->userdata('mem_login');
		}
	}
	
	public function getMemLogin(){
		return $this->mem_login;
	}
	public function num(){
		return $this->cookie("num");
	}
	public function u_id(){
		return $this->cookie("u_id");
	}
	public function u_name(){
		return $this->cookie("u_name");
	}
	public function login_time(){
		return $this->cookie("login_time");
	}

	//取得cookie的欄位值
	private function cookie($column){
		if ($this->mem_login){
			$mem_cookie_tmp=explode("|",$this->CI->encryption->decrypt($this->mem_login));
			if (count($mem_cookie_tmp)==5){
				$cookieValue=array();
				$cookieValue["num"]=$mem_cookie_tmp[0];  //會員流水號
				$cookieValue["u_id"]=$mem_cookie_tmp[1];  //會員帳號
				$cookieValue["u_name"]=urldecode($mem_cookie_tmp[2]);  //會員姓名
				$cookieValue["login_time"]=$mem_cookie_tmp[3];  //登入時間
				$cookieValue["login_code"]=$mem_cookie_tmp[4];  //登入驗證碼
				if (!empty($cookieValue[$column])){
					return $cookieValue[$column];
				}
			}
		}
		return NULL;
	}	


	public function isLogin(){
		$logMsg=$this->logMsg();
		if (!$this->mem_login){
			return $logMsg["noLogin"];
		}else{
			$log=$this->chkLoginStatus();
			if($log!=NULL) $this->logout();
			return $log;
		}
	}
	
	
	public function login($u_id,$u_password,$admin_num,$remember="N"){
		if ($remember=="Y"){
			$cookie=array('name'=>'mem2',
						  'value'=>$this->CI->encryption->encrypt($u_id),
						  'expire'	=>time()+31536000,
						  'path'   => '/'
						 );
			$this->CI->input->set_cookie($cookie);			 
		}else{
			$cookie=array('name'=>'mem2',
						  'value'=>'',
						  'expire'	=>time()-31536000,
						  'path'   => '/'
						 );
			$this->CI->input->set_cookie($cookie);			 
		}					
		
		$logMsg=$this->logMsg();
		if($this->check_ip()){	//撿查IP是否允許
			$sqlStr="select * from `member` where `u_id`=?";
			$parameter=array(':u_id' => $u_id);	
			$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
			if ($row!=NULL){
				if($this->CI->encryption->decrypt($row["u_password"])==$u_password){	//將資料庫密碼解密	
					//if($row["admin_num"]==$admin_num){
						if ($row["active"]=="Y"){
							$loginHour=8;  //設定登入8小時
							$loginTime=now();
							$loginIP=$this->CI->input->ip_address();	//登入IP
							$login_code=md5(uniqid(rand(), true));	//登入驗證碼
							$ckvalue=trim($row["num"]);			//會員流水號
							$ckvalue.="|".trim($row["u_id"]);		//會員帳號
							$ckvalue.="|".urlencode($row["u_name"]);	//會員姓名
							$ckvalue.="|".$loginTime;  //登入時間
							$ckvalue.="|".$login_code;  //登入驗證碼
							if($this->loginType=='C'){
								$cookie=array('name'=>'mem_login',
											  'value'=>$this->CI->encryption->encrypt($ckvalue),
											  //'expire'	=>time()+((int)$loginHour*60*60),
											  'expire'=>0,
											  'path'   => '/'
											 );
								$this->CI->input->set_cookie($cookie);						 
							}else{
								$cookie=array('mem_login'=>$this->CI->encryption->encrypt($ckvalue));
								$this->CI->session->set_userdata($cookie);	
							}
							$upSql="update `member` set `login_time`='".$loginTime."',`login_ip`='".$loginIP."',`login_code`='".$login_code."' where num=".$row["num"];
							$this->CI->webdb->sqlExc($upSql);
							
							//紀錄登入IP
							$parameter=array();
							$colSql="mem_num,login_ip,buildtime";
							$sqlStr="INSERT INTO `member_ip` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':mem_num']=$row['num'];
							$parameter[':login_ip']=$loginIP;
							$parameter[':buildtime']=$loginTime;
							$this->CI->webdb->sqlExc($sqlStr,$parameter);
							
							//API創建遊戲帳號
							//$this->CI->allgameapi->create_account(trim($u_id),$u_password,$row["num"]);
							
							//領取黃金帳號
							//$this->CI->allgameapi->getAccount($row["num"],$row["admin_num"]);
							
							return NULL;
						}elseif($row["active"]=="W"){
							return $logMsg["noActive"];
						}else{
							return $logMsg["suspended"];
						}
					//}else{
						//return $logMsg["custLoginFail"];
					//}
				}else{
					return $logMsg["loginFail"];
				}
			}else{
				return $logMsg["loginFail"];
			}
		}else{	//被加入黑名單
			return $logMsg["blockIP"];
		}
	}

	//檢查登入狀態
	private function chkLoginStatus(){
		$logMsg=$this->logMsg();
		if($this->check_ip()){	//撿查IP是否允許						
			if ($this->mem_login){
				$mem_cookie_tmp=explode("|",$this->CI->encryption->decrypt($this->mem_login));
				if(count($mem_cookie_tmp)==5){
					$num=$this->cookie("num");
					$u_id=$this->cookie("u_id");
					$login_code=$this->cookie("login_code");
					if ($num!=NULL && $u_id!=NULL){
						$sqlStr="select `active`,`login_code` from member where num=? and u_id=?";
						$parameter=array(':num' => $num, ':u_id' => $u_id);	
						$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
						if ($row!=NULL){
							if ($row["active"]=="Y"){
								if($row['login_code']==$login_code){
									return NULL;
								}else{
									return $logMsg["loginAgin"];	
								}
							}elseif ($row["active"]=="W"){							
								return $logMsg["noActive"];
							}else{							
								return $logMsg["suspended"];
							}
						}else{
							return $logMsg["abnormal"];
						}
					}else{
						return $logMsg["abnormal"];
					}
				}else{
					return $logMsg["abnormal"];
				}
			}
			return NULL;
		}else{	//被加入黑名單
			return $logMsg["blockIP"];
		}
	}

	//撿查IP是否已遭列入黑名單
	private function check_ip(){
		$sqlStr="select * from `member_block` where `type`=1 and `block`=?";
		$parameter=array(':block' => $this->CI->input->ip_address());	
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}


	public function getWalletTotal($mem_num){	//取得會員錢包總點數
		$WalletTotal=0;
		if($mem_num!=""){
			$sqlStr="select SUM(points) as WalletTotal from `member_wallet` where mem_num=?";
			$row=$this->CI->webdb->sqlRow($sqlStr,array(':mem_num'=>$mem_num));
			if($row!=NULL){
				if($row['WalletTotal']!=NULL){
					$WalletTotal=$row['WalletTotal'];
				}
				if($row['WalletTotal'] < 0){	//判斷點數被扣到小於0就視為0
					$WalletTotal=0;
				}
			}
		}
		return $WalletTotal;
	}

	//登出
	public function logout(){
		$cookie=array('name'=>'mem_login',
					  'value'=>'',
					  'expire'	=>time() - 86400,
					  'path'   => '/'
					 );
		$this->CI->input->set_cookie($cookie);	
		$this->CI->session->unset_userdata('mem_login');
		$this->CI->session->sess_destroy();
	}
	
	
	//定義訊息
	public function logMsg(){
		$logMsg=array();
		$logMsg["noLogin"]="請先登入會員";
		$logMsg["noActive"]="帳號尚未啟用";	
		$logMsg["suspended"]="帳號已停權";
		$logMsg["abnormal"]="登入異常";		
		$logMsg["validation"]="驗證錯誤";		
		$logMsg["isActive"]="帳號已啟用，請勿重複啟用";
		$logMsg["loginFail"]="帳號或密碼錯誤";
		$logMsg["forgotFail"]="會員帳號或取款密碼錯誤";
		$logMsg['loginAgin']='會員已在其他地方登入';
		$logMsg['blockIP']='您的IP已遭拒絕登入';
		$logMsg['custLoginFail']='非本站會員禁止登入';
		return $logMsg;
	}
	
}
?>