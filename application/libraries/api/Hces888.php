<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hces888 {
	var $timeout=HC_timeout;	//curl允許等待秒數
	var $api_url = HC_api_url;
	var $agentID=HC_agentID;	//只需要換掉這個一個
	var $secret_key=HC_secret_key;	//secretKey 延續總代的~~除非換掉總代
	var $lang=HC_lang;
	var $token;
 	
	public function __construct(){
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
		$this->agentToken();
	}
	
	
	
	public function agentToken(){
		$post_data=array('agentID'=>$this->agentID,'secret_key'=>$this->secret_key);	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/auth");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){	
				$this->token=base64_encode(md5($this->agentID) . '|' . $output->data->token . '|' . md5($this->secret_key));
			}
		}
	}
	
	
	public function create_account($u_id,$mem_num,$gamemaker_num=36){
		$parameter=array();
		$sqlStr="select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//該館遊戲帳號不存在則呼叫API
			$post_data=array('token'=>$this->token,'username'=>trim($u_id));	
			$post_data=json_encode($post_data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/login");		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
			$output=json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);	
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code==200 && $output->code == 0){	
					$colSql="u_id,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);
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
		$post_data=array('token'=>$this->token,'username'=>trim($u_id));	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/balance");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){	
				return $output->data->balance;
			}else{
				return 	'--';
			}
		}else{
			return '--';	
		}
	}
	
	//轉入遊戲 type=1;轉出遊戲 type=2
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=36,$logID=NULL){	//轉入點數到遊戲帳號內
		$out_trade_no=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$out_trade_no."' where num=".$logID;
		//$this->CI->webdb->sqlExc($upSql);
	
		$post_data=array('token'=>$this->token,'username'=>trim($u_id),'money'=>trim($amount),'type'=>1,'out_trade_no'=>$out_trade_no);	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/transfer");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){	
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
				$parameter[":makers_balance"]=$output->data->currentMoney;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return '轉點錯誤：'.$output->code.':'.$output->msg;
			}
		}else{
			return $this->point_checking($out_trade_no,1,$u_id,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}
	
	}

	//轉入遊戲 type=1;轉出遊戲 type=2
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=36,$logID=NULL){	//轉入點數到遊戲帳號內
		$out_trade_no=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$out_trade_no."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
	
		$post_data=array('token'=>$this->token,'username'=>trim($u_id),'money'=>trim($amount),'type'=>2,'out_trade_no'=>$out_trade_no);	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/transfer");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){	
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
				$parameter[":makers_balance"]=$output->data->currentMoney;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return '轉點錯誤：'.$output->code.':'.$output->msg;
			}
			
		}else{
			return $this->point_checking($out_trade_no,2,$u_id,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}
	}
	
	public function point_checking($out_trade_no,$type,$u_id,$mem_num,$gamemaker_num){	//點查轉帳情況
		$post_data=array('token'=>$this->token,'username'=>trim($u_id),'out_trade_no'=>$out_trade_no);	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/transferinfo2");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){	
				$WalletTotal=getWalletTotal($mem_num);	//錢包餘額
				if($type==1){	//轉入遊戲
					$parameter=array();
					$before_balance=(float)$WalletTotal;	//異動前點數
					$after_balance= (float)$before_balance - (float)abs($output->data->amount);//異動後點數
					$parameter=array();
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=3;	//轉入遊戲
					$parameter[":points"]="-".abs($output->data->amount);
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=$output->data->balance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}else{	//轉出遊戲
					$parameter=array();
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance + (float)abs($output->data->amount);//異動後點數
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=4;	//遊戲轉出
					$parameter[":points"]=abs($output->data->amount);
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=$output->data->balance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
			}else{
				return '查詢轉點錯誤：'.$output->code.':'.$output->msg;
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	public function forward_game($u_id){	//登入遊戲
		$post_data=array('token'=>$this->token,'username'=>trim($u_id));	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/login");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){	
				if(!$this->CI->agent->is_mobile()){	//電腦版
					return  $output->data->pc_url.'?token='.$output->data->token.'&lang='.$this->lang;
				}else{
					return $output->data->h5_url.'?token='.$output->data->token.'&lang='.$this->lang;
				}
			}else{
				return $output->code.':'.$output->msg;	
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}

	public function fun_game(){	//試玩無法下注
		$post_data=array('token'=>$this->token,'username'=>time());	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/logintrial");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){	
				if(!$this->CI->agent->is_mobile()){	//電腦版
					return $output->data->pc_url.'?token='.$output->data->token.'&lang='.$this->lang;
				}else{
					return $output->data->h5_url.'?token='.$output->data->token.'&lang='.$this->lang;
				}
			}else{
				return $output->code.':'.$output->msg;	
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}


	//type 为 0 时为结算时间，为 1 时为注单生成时间
	public function reporter_all($sTime,$eTime,$type=0,$cursor=NULL){	
		date_default_timezone_set("Asia/Taipei");
		$post_data=array('token'=>$this->token,'startTime'=>$sTime,'endTime'=>$eTime,'type'=>$type,'cursor'=>$cursor);	
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/betdatelist");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//echo '<pre>';
		//print_r($output);
		if(!$curl_errno){
			if($http_code==200 && $output->code == 0){
				return $output->data;
			}
			
		}
	
	}
	
}
