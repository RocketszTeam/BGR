<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class S9k168{
	var $CI;
	var $timeout=s9k_timeout;	//curl允許等待秒數
	var $BossID=s9k_BossID;
	var $api_url=s9k_api_url;
	var $ApiToken=s9k_ApiToken;
	
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");		
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=26){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where  `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api
			$post_data=array('BossID'=>$this->BossID,'MemberAccount'=>trim($u_id),'MemberPassword'=>$u_password);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url.$this->ApiToken.'/RegisterUser');	
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//echo $http_code;exit;
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->success==0){	//帳號創建成功
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
					return 	$output->success.':'.$output->msg;
				}
			}else{
				return '系統繁忙中，請稍候再試';
			}
		}else{
			return '會員已有此類型帳號';	
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$post_data=array('MemberAccount'=>trim($u_id));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url.$this->ApiToken.'/GetUserBalance');	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==0){	//帳號創建成功
				return $output->data->GetUserBalance->Balance;
			}else{
				return '--';
			}
		}else{
			return '--';
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=26,$logID=NULL){	//轉入點數到遊戲帳號內
		$TradeNo=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$TradeNo."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		
		$post_data=array('MemberAccount'=>trim($u_id),'Balance'=>trim($amount),'TradeNo'=>$TradeNo);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url.$this->ApiToken.'/BalanceTransfer');	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==0 && $output->data->BalanceTransfer->TransferStatus=='Y'){	//帳號創建成功
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
				$parameter[":makers_balance"]=$output->data->BalanceTransfer->AfterBalance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return 	$output->success.':'.$output->msg;
			}
		}else{
			return $this->point_checking($TradeNo,1,$u_id,$amount,$mem_num,$gamemaker_num);
		}
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=26,$logID=NULL){	//遊戲轉出到錢包
		$TradeNo=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$TradeNo."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		
		$post_data=array('MemberAccount'=>trim($u_id),'Balance'=>trim("-".$amount),'TradeNo'=>$TradeNo);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url.$this->ApiToken.'/BalanceTransfer');	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==0 && $output->data->BalanceTransfer->TransferStatus=='Y'){	//帳號創建成功
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
				$parameter[":makers_balance"]=$output->data->BalanceTransfer->AfterBalance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return 	$output->success.':'.$output->msg;
			}
		}else{
			return $this->point_checking($TradeNo,2,$u_id,$amount,$mem_num,$gamemaker_num);
		}
	
	}
	
	public function point_checking($TradeNo,$type,$u_id,$amount,$mem_num,$gamemaker_num){	//點查轉帳情況
		$post_data=array('MemberAccount'=>trim($u_id),'Balance'=>trim($amount),'TradeNo'=>$TradeNo);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url.$this->ApiToken.'/CheckTransfer');	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==0 && $output->data->CheckTransfer->TransferStatus=='Y'){	//帳號創建成功
				$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
				if($type==1){
					if((int)$WalletTotal >=(int)$amount){
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
						$parameter[":makers_balance"]=$output->data->CheckTransfer->AfterBalance;
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
					$parameter[":makers_balance"]=$output->data->CheckTransfer->AfterBalance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
			}else{
				return 	'查詢轉點錯誤：'.$output->success.':'.$output->msg;	
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	
	public function forward_game($u_id,$u_password){	//登入遊戲
		$post_data=array('MemberAccount'=>trim($u_id),'MemberPassword'=>$u_password);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url.$this->ApiToken.'/UserLogin');	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==0){
				return $output->data->UserLogin->GameUrl;
			}else{
				return 	$output->success.':'.$output->msg;
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	
	//報表
	public function reporter_all($sTime,$eTime,$Page){
		$post_data=array('StartTime'=>trim($sTime),'EndTime'=>$eTime,'Page'=>$Page,'BossID'=>$this->BossID);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url.$this->ApiToken.'/BetList');	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==0){
				return $output->data;
			}
		}
	}
	
	
	
}
?>