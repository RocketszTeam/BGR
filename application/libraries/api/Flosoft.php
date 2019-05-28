<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//法老王
class Flosoft{
	var $api_url='http:// www.sc.wlb99.com/api/system/';
	var $api_key='243c15e9762d4f51b38e242267785794';
	var $CI;
	var $timeout=5;	//curl允許等待秒數
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=3){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		//$parameter=array();
		//$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		//$parameter[':u_id']=trim($u_id);
		//$parameter[':mem_num']=$mem_num;
		//$parameter[':gamemaker_num']=$gamemaker_num;
		//$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		//if($row==NULL){	//無帳號才呼叫api		
			//先判定此會員是否已有帳號	
			$post_data=array('username'=>trim($u_id),'alias'=>trim($u_id),'pwd'=>md5(trim($u_password)),
							 'top'=>$this->agent);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/createMem.php");		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$headers=array('api_key:'.$this->api_key);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			print_r($output);exit;
			if(!$curl_errno){
				/*if($http_code===200 && ($output->code=='001')){	//帳號創建成功
					$colSql="u_id,u_password,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);	
					$parameter[':u_password']=$this->CI->encryption->encrypt($u_password);	//密碼加密
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return $output->msg;	
				}*/
			}else{
				return '系統繁忙中，請稍後再試'	;
			}
		//}else{
			//return '會員已有此類型帳號';
		//}
		
	}
	
	public function get_balance($u_id){	//餘額查詢
		$post_data=array('username'=>trim($u_id));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/memGetMoney.php");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				return $output->money;
			}else{
				return '--';	
			}
		}else{
			return '--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=3){	//轉入點數到遊戲帳號內
		$post_data=array('username'=>trim($u_id),'money'=>$amount);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/memTransfer.php");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				$parameter=array();
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=3;	//轉入遊戲
				$parameter[":points"]="-".$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]=$output->money;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return "轉點錯誤";	
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}	
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=3){	//遊戲點數轉出
		$post_data=array('username'=>trim($u_id),'money'=>"-".$amount);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/memTransfer.php");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				$parameter=array();
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=4;	//遊戲轉出
				$parameter[":points"]=$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]=$output->money;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return "轉點錯誤";
				//return $output->ErrorMessage;	
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}	
	}
	
	//type=1 ;代表 錢包轉入遊戲 需先檢查 錢包餘額
	public function point_checking($track_id,$type,$u_id,$amount,$mem_num,$gamemaker_num){	//點查轉帳情況
		$post_data=array('act'=>'log','account'=>$this->getAesEncrypt($u_id),'track_id'=>$track_id,
		'up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/points");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				if($output->data->result==1){
					if($type==1){
						$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
						if((int)$WalletTotal >=(int)$amount){
							$parameter=array();
							$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[":mem_num"]=$mem_num;
							$parameter[":kind"]=3;	//轉入遊戲
							$parameter[":points"]="-".$amount;
							$parameter[":makers_num"]=$gamemaker_num;
							$parameter[":makers_balance"]=$output->data->point;
							$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
							$parameter[":buildtime"]=now();
							$this->CI->webdb->sqlExc($sqlStr,$parameter);
							return NULL;
						}else{
							return '錢包點數不足';
						}
					}else{
						$parameter=array();
						$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime";
						$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$mem_num;
						$parameter[":kind"]=4;	//遊戲轉出
						$parameter[":points"]=$amount;
						$parameter[":makers_num"]=$gamemaker_num;
						$parameter[":makers_balance"]=$output->data->point;
						$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
						$parameter[":buildtime"]=now();
						$this->CI->webdb->sqlExc($sqlStr,$parameter);
						return NULL;
					}
				}else{
					return '無此記錄';
				}
			}else{
				return "轉點錯誤";
			}
		}else{
			return '系統繁忙中，請稍後再試';
		}
	}
	
	
	public function forward_game($u_id,$u_password){	//登入遊戲
		$post_data=array('username'=>trim($u_id),'pwd'=>md5(trim($u_password)));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/memLogin.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		print_r($output);;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				echo $this->login_url.$output->lid;
				return $this->login_url.$output->lid;
				//exit;
			}else{
				//return $output->msg;
			}
		}else{
			//超時處理	
		}
	}
	
	//報表
	public function reporter_all($maxModId=0,$checked=0){
		$post_data=array('maxModId'=>trim($maxModId),'checked'=>trim($checked));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/getTix.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				return $output->data;
			}
		}
				
	}
	
	
	
	
	
	
}
?>