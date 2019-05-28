<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Avia {
	var $timeout=AV_timeout;	//curl允許等待秒數
	var $api_url = AV_api_url;
	var $Authorization=AV_Authorization;
 	
	public function __construct(){
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=35){
		$parameter=array();
		$sqlStr="select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//該館遊戲帳號不存在則呼叫API
			$post_data=array('UserName'=>trim($u_id),'Password'=>trim($u_password));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->api_url.'/user/register');
			$headers=array('Authorization:'.$this->Authorization);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			$output=json_decode(curl_exec($ch),true);
			$curl_errno = curl_errno($ch);	
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			/*
			echo "<!--";
			print_r($output);
			echo "'".$output["success"]."'";
			echo "-->";
			
			print_r($curl_errno);
			exit;
			*/
			if(!$curl_errno){
				if($http_code==200 && $output["success"] == 1){
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
					return '創建失敗：'.$output->msg;
				}
			}else{
				return '系統繁忙中，請稍後再試！';
			}
		}else{
			return '會員已有此類型帳號';	
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$post_data=array('UserName'=>trim($u_id));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/balance");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization:'.$this->Authorization);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->success == 1){
				return $output->info->Money;
			}else{
				return 	'--';
			}
		}else{
			return '--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=35,$logID=NULL){	//轉入點數到遊戲帳號內
		$ID=uniqid();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$ID."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('UserName'=>trim($u_id),'Money'=>$amount,'Type'=>'IN','ID'=>$ID);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/transfer");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization:'.$this->Authorization);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->success == 1){
				$makers_balance=($this->get_balance($u_id)!='--' ? $this->get_balance($u_id) : 0);
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
				$parameter[":makers_balance"]=$makers_balance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return '轉點錯誤：'.$output->msg;
			}
		}else{
			return $this->point_checking($ID,$post_data["Type"],$u_id,$amount,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}
	
	}


	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=35,$logID=NULL){	//轉入點數到遊戲帳號內
		$ID=uniqid();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$ID."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('UserName'=>trim($u_id),'Money'=>$amount,'Type'=>'OUT','ID'=>$ID);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/transfer");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization:'.$this->Authorization);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->success == 1){
				$makers_balance=($this->get_balance($u_id)!='--' ? $this->get_balance($u_id) : 0);
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
				$parameter[":makers_balance"]=$makers_balance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				
				return NULL;
			}else{
				return '轉點錯誤：'.$output->msg;
			}
			
		}else{
			return $this->point_checking($ID,$post_data["Type"],$u_id,$amount,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}
	}
	
	public function point_checking($ID,$type,$u_id,$amount,$mem_num,$gamemaker_num){	//點查轉帳情況
		$post_data=array('ID'=>$ID);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/transferinfo");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization:'.$this->Authorization);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->success == 1){
				$WalletTotal=getWalletTotal($mem_num);	//錢包餘額
				$makers_balance=($this->get_balance($u_id)!='--' ? $this->get_balance($u_id) : 0);
				if($type=='IN'){	//轉入遊戲
					$parameter=array();
					$before_balance=(float)$WalletTotal;	//異動前點數
					$after_balance= (float)$before_balance - (float)$amount;//異動後點數
					$parameter=array();
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=3;	//轉入遊戲
					$parameter[":points"]="-".$amount;
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=$makers_balance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}else{	//轉出遊戲
					$parameter=array();
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance + (float)$amount;//異動後點數
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=4;	//遊戲轉出
					$parameter[":points"]=$amount;
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=$makers_balance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
			}else{
				return '查詢轉點錯誤：'.$output->msg;
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	public function forward_game($u_id){	//登入遊戲
		$post_data=array('UserName'=>trim($u_id));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/login");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization:'.$this->Authorization);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->success == 1){
				return $output->info->Url;
			}else{
				return $output->msg;
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}

	public function fun_game(){	//試玩無法下注
		$post_data=array();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/guest");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization:'.$this->Authorization);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->success == 1){
				return $output->info->Url;
			}else{
				return $output->msg;
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}

	public function reporter_all($sTime,$eTime,$PageSize=100,$PageNum=1,$type='UpdateAt'){	//
		date_default_timezone_set("Asia/Taipei");
		$post_data=array('Type'=>$type,'StartAt'=>$sTime,'EndAt'=>$eTime,'PageSize'=>$PageSize,'PageIndex'=>$PageNum);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/log/get");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization:'.$this->Authorization);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,60);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==1){
					return $output->info;
			}
			
		}
	
	}
	
}
