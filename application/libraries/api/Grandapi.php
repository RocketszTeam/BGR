<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Grandapi {
	var $CI;
	var $timeout=15;	//curl允許等待秒數
	var	$agent='PE1';
	var	$api_url='http://gd1.ncgsgs.com';
	var	$Currency='THB';
	var	$hash_code='6ejotSdWTS9U4V9P';
	
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function create_account($u_id,$mem_num,$gamemaker_num=6){	//創建遊戲帳號
		$parameter=array();
		$sqlStr="select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//沒有遊戲帳號才呼叫api
			$Params=json_encode(array('MemberAccount'=>trim($u_id),'IP'=>$_SERVER['REMOTE_ADDR']));
			$Params_data=$this->AESencode($Params);
			$Sign=md5($Params);
			$post_data=array('AgentCode'=>$this->agent,'Currency'=>$this->Currency,'Sign'=>$Sign,'Params'=>$Params_data);
			$post_data=json_encode($post_data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/AddMember");		
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
				if($http_code===200 && $output->Result==0){
					$colSql="u_id,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return $output->Result;
				}
			}else{
				return '系統繁忙中，請稍候再試';
			}
		}else{
			return '會員已有此類型帳號';	
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$Params=json_encode(array('MemberAccount'=>trim($u_id)));
		$Params_data=$this->AESencode($Params);
		$Sign=md5($Params);
		$post_data=array('AgentCode'=>$this->agent,'Currency'=>$this->Currency,'Sign'=>$Sign,'Params'=>$Params_data);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/SearchMember");		
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
			if($http_code===200 && $output->Result==0){
				return $output->Points;
			}else{
				return '--';
			}
		}else{
			return '--';
		}
	}
	
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=6,$logID=NULL){	//轉入點數到遊戲帳號內
		$TrsID=time().mt_rand();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$TrsID."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$Params=json_encode(array('MemberAccount'=>trim($u_id),'TrsID'=>$TrsID,'Type'=>'In','Points'=>$amount));
		$Params_data=$this->AESencode($Params);
		$Sign=md5($Params);
		$post_data=array('AgentCode'=>$this->agent,'Currency'=>$this->Currency,'Sign'=>$Sign,'Params'=>$Params_data);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/SetPointsOutGame");		
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
			if($http_code===200 && $output->Result==0){
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
				$parameter[":makers_balance"]=$output->Points;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				if($output->Result==503){
					return '轉點錯誤，請先退出遊戲';
				}else{
					return '轉點錯誤：'.$output->Result;	
				}
			}
		}else{
			//超時處理
			return '系統繁忙中，請稍候再試';
		}
	}
	
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=6,$logID=NULL){	//遊戲點數轉出
		$TrsID=time().mt_rand();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$TrsID."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$Params=json_encode(array('MemberAccount'=>trim($u_id),'TrsID'=>$TrsID,'Type'=>'Out','Points'=>$amount));
		$Params_data=$this->AESencode($Params);
		$Sign=md5($Params);
		$post_data=array('AgentCode'=>$this->agent,'Currency'=>$this->Currency,'Sign'=>$Sign,'Params'=>$Params_data);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/SetPointsOutGame");		
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
			if($http_code===200 && $output->Result==0){
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
				$parameter[":makers_balance"]=$output->Points;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				if($output->Result==503){
					return '轉點錯誤，請先退出遊戲';
				}else{
					return '轉點錯誤：'.$output->Result;	
				}
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	public function forward_game($u_id,$GameCode){	//登入遊戲
		$Params=json_encode(array('MemberAccount'=>trim($u_id),'GameID'=>$GameCode,'IP'=>$_SERVER['REMOTE_ADDR'],'Token'=>md5(trim($u_id).$this->hash_code)));
		$Params_data=$this->AESencode($Params);
		$Sign=md5($Params);
		$post_data=array('AgentCode'=>$this->agent,'Currency'=>$this->Currency,'Sign'=>$Sign,'Params'=>$Params_data);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/Login");		
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
			if($http_code===200 && $output->Result==0){
				return $output->Url;				
			}else{
				return $output->Result;
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	//試玩
	public function fun_game($GameCode){	
		$Params=json_encode(array('GameID'=>$GameCode));
		$Params_data=$this->AESencode($Params);
		$Sign=md5($Params);
		$post_data=array('AgentCode'=>$this->agent,'Currency'=>$this->Currency,'Sign'=>$Sign,'Params'=>$Params_data);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/GetDemoUrl");		
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
			if($http_code===200 && $output->Result==0){
				return $output->Url;				
			}else{
				return $output->Result;
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	
	}
	
	public function reporter_all($sTime,$eTime,$Page,$Row){	//群撈報表
		$Params=json_encode(array('Row'=>$Row,'Page'=>$Page,'StartDate'=>$sTime,'EndDate'=>$eTime));
		$Params_data=$this->AESencode($Params);
		$Sign=md5($Params);
		$post_data=array('AgentCode'=>$this->agent,'Currency'=>$this->Currency,'Sign'=>$Sign,'Params'=>$Params_data);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/GetRecord");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,60);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->Result==0){
				return $output;				
			}else{
				return NULL;
			}
		}else{
			return NULL;
		}
	}
	
	public function getHash(){
		return $this->hash_code;	
	}
	
	//AES 加密 ECB 模式
	private function AESencode($_values){
		$data =NULL;
		try{
			$size    = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
			$_values = $this->pkcs5_pad($_values, $size);
			$td      = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
			$iv      = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
			mcrypt_generic_init($td, $this->hash_code, $iv);
			$data    = mcrypt_generic($td, $_values);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			$data    = base64_encode($data);
		}
		catch(\Exception $e){

		}
		return $data;
	}
	
	//AES 解密 ECB 模式
	private function AESdecode($_values){
		$data = NULL;
		try{
			$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->hash_code, base64_decode($_values), MCRYPT_MODE_ECB);
			$dec_s     = strlen($decrypted);
			$padding   = ord($decrypted[$dec_s - 1]);
			$data      = substr($decrypted, 0, -$padding);
		}catch(\Exception $e){
			
		}
		return $data;
	}

	private function pkcs5_pad ($text, $blocksize){
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text. str_repeat(chr($pad), $pad);
    }
	
	
}
?>