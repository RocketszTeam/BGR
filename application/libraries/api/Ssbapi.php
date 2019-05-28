<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ssbapi{
	var $agent='penhfun';
	var $api_url='http://dm33m.sxb168.win/api';
	var $api_key='dm3|SH';
	var $login_url='http://dm33m.sxb168.win/app/m_index.php?lid=';
	var $m_login_url='http://dm33m.sxb168.win/m/spt_index.php?lid=';
	var $CI;
	var $timeout=10;	//curl允許等待秒數
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
		$this->api_key=md5(md5(date('Ym').$this->api_key));	
		//$api_key = md5(md5(date("Ym")."{$apikey}"));
		
	}

	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=21){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api		
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
			curl_setopt($ch, CURLOPT_TIMEOUT,10); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && ($output->code=='001' || $output->code=='002')){	//帳號創建成功 002=帳號重複 避免漏掉 還是寫入
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
				}
			}else{
				return '系統繁忙中，請稍後再試'	;
			}
		}else{
			return '會員已有此類型帳號';
		}
		
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
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=21,$logID=NULL){	//轉入點數到遊戲帳號內
		$billno=mt_rand(1,9).date('his').floor(microtime() * 1000);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$billno."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('username'=>trim($u_id),'money'=>$amount,'billno'=>$billno);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/memTransfer.php");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				$before_balance=(float)$WalletTotal;//異動前點數
				$after_balance= (float)$before_balance - (float)$amount;//異動後點數
				$parameter=array();
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=3;	//轉入遊戲
				$parameter[":points"]="-".$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]=$output->money;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return "轉點錯誤:".$output->code;	
			}
		}else{
			return $this->point_checking($billno,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}	
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=21,$logID=NULL){	//遊戲點數轉出
		$billno=mt_rand(1,9).date('his').floor(microtime() * 1000);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$billno."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		
		$post_data=array('username'=>trim($u_id),'money'=>"-".$amount,'billno'=>$billno);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/memTransfer.php");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				$before_balance=(float)$WalletTotal;//異動前點數
				$after_balance= (float)$before_balance + (float)$amount;//異動後點數
				$parameter=array();
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=4;	//遊戲轉出
				$parameter[":points"]=$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]=$output->money;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return "轉點錯誤:".$output->code;
				//return $output->ErrorMessage;	
			}
		}else{
			return $this->point_checking($billno,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}	
	}
	
	//轉點確認
	public function point_checking($billno,$mem_num,$gamemaker_num){
		$post_data=array('username'=>trim($u_id),'billno'=>$billno);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/chkTransfer.php");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				if($output->money > 0){	//正數代表存款
					$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
					if((int)$WalletTotal >=(int)$output->money){
						$before_balance=(float)$WalletTotal;//異動前點數
						$after_balance= (float)$before_balance - (float)$output->money;//異動後點數
						$parameter=array();
						$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
						$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$mem_num;
						$parameter[":kind"]=3;	//轉入遊戲
						$parameter[":points"]="-".$output->money;
						$parameter[":makers_num"]=$gamemaker_num;
						$parameter[":makers_balance"]=$output->after;
						$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
						$parameter[":buildtime"]=now();
						$parameter[':before_balance']=$before_balance;
						$parameter[':after_balance']=$after_balance;
						$this->CI->webdb->sqlExc($sqlStr,$parameter);
						return NULL;
					}else{
						return '錢包點數不足';
					}
				}else{
					$WalletTotal=getWalletTotal($mem_num);	//會員餘額
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance + (float)abs($output->money);//異動後點數
					$parameter=array();
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=4;	//遊戲轉出
					$parameter[":points"]=abs($output->money);
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=$output->after;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
			}else{
				return "查詢轉點錯誤:".$output->code;
			}
		}else{
			return '系統繁忙中，請稍候再試';
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
		//print_r($output);;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				//echo $this->login_url.$output->lid;
				if($this->CI->agent->is_mobile()){
					return $this->m_login_url.$output->lid;
				}else{
					return $this->login_url.$output->lid;
				}
				
				//exit;
			}else{
				return $output->msg;
			}
		}else{
			//超時處理	
			return '系統繁忙中，請稍候再試';	
		}
	}
	
	//報表
	public function reporter_all($maxModId=0,$checked=0){
		$post_data=array('maxModId'=>trim($maxModId),'checked'=>trim($checked),'agent'=>$this->agent);
		//print_r($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/getTix.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,60); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				return $output;
			}
		}
				
	}
	
	//不含代理
	public function reporter_all2($maxModId=0,$checked=0){
		$post_data=array('maxModId'=>trim($maxModId),'checked'=>trim($checked));
		//print_r($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/getTix.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('api_key:'.$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,60); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='001'){
				return $output;
			}
		}
				
	}
	
	
	
	
}
?>