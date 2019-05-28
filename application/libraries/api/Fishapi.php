<?php
defined('BASEPATH') OR exit('No direct script access allowed');


define("FISH_Agent", "HS037");
define("FISH_DES_KEY", "Nw4ijo69");
define("FISH_MD5_KEY", "84598uQ40bsBytjs6d");
define("FISH_API_URL", "https://api.gg626.com/api/doLink.do?");
define("FISH_REPORT_URL", "http://betrec.gg626.com/api/doReport.do?");
class Fishapi {
	var $CI;
	var $timeout=10;	//curl允許等待秒數
	public function __construct(){	
		$this->CI =&get_instance();
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=4){	//創建遊戲帳號
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//如果帳號不存在則把帳號寫入資料庫
			$params = http_build_query(array('cagent' => FISH_Agent, 
												 'loginname'=>$u_id,
												 'password'=>$u_password,
												 'method'=>'ca',
												 'actype'=>1,'cur'=>'TWD'));
			$params=$this->encrypt(str_ireplace('&', '/\\\\/',$params));									 
			$key=md5($params.FISH_MD5_KEY);		
			$ch = curl_init(FISH_API_URL.http_build_query(array('params' => $params, 'key' => $key)));
			$headers = array();
			$headers[]="GGaming:WEB_GG_GI_" . FISH_Agent;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if(!$curl_errno){
				if($http_code===200 && $output->code==0){
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
					return $output->code;
				}
			}else{
				//超時處理
				return '系統繁忙中，請稍候再試';	
			}
		}else{
			return '會員已有此類型帳號';	
		}
	
	}
	
	public function get_balance($u_id,$u_password){	//餘額查詢
		$params = http_build_query(array('cagent' => FISH_Agent, 
											 'loginname'=>$u_id,
											 'password'=>$u_password,
											 'method'=>'gb',
											 'cur'=>'TWD'));
		$params=$this->encrypt(str_ireplace('&', '/\\\\/',$params));									 
		$key=md5($params.FISH_MD5_KEY);		
		$ch = curl_init(FISH_API_URL.http_build_query(array('params' => $params, 'key' => $key)));
		$headers = array();
		$headers[]="GGaming:WEB_GG_GI_" . FISH_Agent;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code==0){
				return (float)$output->balance;
			}else{
				return '--';
			}	
		}else{
			//超時處理
			return '--';	
		}
	}
	
	
	public function deposit($u_id,$u_password,$amount,$mem_num,$gamemaker_num=4,$logID=NULL){	//轉入點數到遊戲帳號內
		$billno=FISH_Agent.time().mt_rand(111111,999999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$billno."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		$params = http_build_query(array('cagent' => FISH_Agent, 
											 'loginname'=>$u_id,
											 'password'=>$u_password,
											 'method'=>'tc',
											 'billno'=>$billno,//除了代理編號額外需要13~16位數
											 'type'=>'IN',	//IN=轉入;OUT=轉出
											 'credit'=>$amount,
											 'cur'=>'TWD',
											 'ip'=>$this->CI->input->ip_address()));	
		$params=$this->encrypt(str_ireplace('&', '/\\\\/',$params));									 
		$key=md5($params.FISH_MD5_KEY);		
		$ch = curl_init(FISH_API_URL.http_build_query(array('params' => $params, 'key' => $key)));
		$headers = array();
		$headers[]="GGaming:WEB_GG_GI_" . FISH_Agent;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code==0){
				$makers_balance=($this->get_balance($u_id,$u_password)!='--' ? $this->get_balance($u_id,$u_password) : 0);
				
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				//異動前點數
				$before_balance=(float)$WalletTotal;
				//異動後點數
				$after_balance= (float)$before_balance - (float)$amount;
				
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
				return "轉點錯誤，Error：".$output->code;
			}
		}else{
			//超時處理
			return '系統繁忙中，請稍候再試';
		}
	}
	
	
	public function withdrawal($u_id,$u_password,$amount,$mem_num,$gamemaker_num=4,$logID=NULL){	//遊戲點數轉出
		$billno=FISH_Agent.time().mt_rand(111111,999999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$billno."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		$params = http_build_query(array('cagent' => FISH_Agent, 
											 'loginname'=>$u_id,
											 'password'=>$u_password,
											 'method'=>'tc',
											 'billno'=>$billno,//除了代理編號額外需要13~16位數
											 'type'=>'OUT',	//IN=轉入;OUT=轉出
											 'credit'=>$amount,
											 'cur'=>'TWD',
											 'ip'=>$this->CI->input->ip_address()));	
		$params=$this->encrypt(str_ireplace('&', '/\\\\/',$params));									 
		$key=md5($params.FISH_MD5_KEY);		
		$ch = curl_init(FISH_API_URL.http_build_query(array('params' => $params, 'key' => $key)));
		$headers = array();
		$headers[]="GGaming:WEB_GG_GI_" . FISH_Agent;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code==0){
				$makers_balance=($this->get_balance($u_id,$u_password)!='--' ? $this->get_balance($u_id,$u_password) : 0);
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				//異動前點數
				$before_balance=(float)$WalletTotal;
				//異動後點數
				$after_balance= (float)$before_balance + (float)$amount;
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
			}elseif($http_code===200 && $output->code==5){
				return "遊戲點數不足";
			}else{
				return "轉點錯誤，Error：".$output->code;
			}
		}else{
			//超時處理
			return '系統繁忙中，請稍候再試';
		}
	}
	
	public function forward_game($u_id,$u_password,$gametype=101){	//登入遊戲
		/*gametype說明101=捕魚天下;102=水果機;103=單挑王;104=金鲨银鲨*/
	
		$params = http_build_query(array('cagent' => FISH_Agent, 
											 'loginname'=>$u_id,
											 'password'=>$u_password,
											 'method'=>'fw',
											 'sid'=>FISH_Agent.time().mt_rand(111111,999999),//除了代理編號額外需要13~16位數
											 'lang'=>'zh-HK',	
											 'gametype'=>$gametype,	
											 'ip'=>$this->CI->input->ip_address()));	
		$params=$this->encrypt(str_ireplace('&', '/\\\\/',$params));									 
		$key=md5($params.FISH_MD5_KEY);		
		$ch = curl_init(FISH_API_URL.http_build_query(array('params' => $params, 'key' => $key)));
		$headers = array();
		$headers[]="GGaming:WEB_GG_GI_" . FISH_Agent;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code==0){
				return $output->url;
			}else{
				return "獲取遊戲連結錯誤，Error：".$output->code;
			}
		}else{
			//超時處理
			return '系統繁忙中，請稍候再試';
		}
	}
	
	
	//報表
	public function reporter_all($sTime,$eTime){
		/*$params = http_build_query(array('cagent' => FISH_Agent, 
											 'startdate'=>$sTime,
											 'enddate'=>$eTime,
											 'method'=>'tr'
									));	
		*/
		$params='cagent='.FISH_Agent.'&startdate='.$sTime.'&enddate='.$eTime.'&method=tr';									
		$params=$this->encrypt(str_ireplace('&', '/\\\\/',$params));									 
		$key=md5($params.FISH_MD5_KEY);		
		$ch = curl_init(FISH_REPORT_URL.http_build_query(array('params' => $params, 'key' => $key)));
		$headers = array();
		$headers[]="GGaming:WEB_GG_GI_" . FISH_Agent;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,60); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code==0){
				return $output->betlist;
			}else{
				$output->code;
			}	
		}
	}
	
    private function encrypt($input) {          
        $size = mcrypt_get_block_size('des', 'ecb');            
        $input = $this->pkcs5_pad($input, $size);            
        $key = FISH_DES_KEY;           
        $td = mcrypt_module_open('des', '', 'ecb', '');         
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);        
         @mcrypt_generic_init($td, $key, $iv);           
        $data = mcrypt_generic($td, $input);            
         mcrypt_generic_deinit($td);        
         mcrypt_module_close($td);           
        $data = base64_encode($data);           
        return preg_replace("/\s*/", '',$data);      
     }           
   private function decrypt($encrypted) {          
        $encrypted = base64_decode($encrypted);         
        $key =FISH_DES_KEY;            
        $td = mcrypt_module_open('des','','ecb','');    
        //使用MCRYPT_DES算法,cbc模式                  
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);              
        $ks = mcrypt_enc_get_key_size($td);                 
         @mcrypt_generic_init($td, $key, $iv);           
        //初始处理                  
        $decrypted = mdecrypt_generic($td, $encrypted);           
        //解密                
         mcrypt_generic_deinit($td);           
        //结束              
         mcrypt_module_close($td);                   
        $y=$this->pkcs5_unpad($decrypted);            
        return $y;      
     }           
	
   private function pkcs5_pad ($text, $blocksize) {            
        $pad = $blocksize - (strlen($text) % $blocksize);           
        return $text . str_repeat(chr($pad), $pad);    
     }       
   private function pkcs5_unpad($text) {           
        $pad = ord($text{strlen($text)-1});         
        if ($pad > strlen($text))                
            return false;           
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)                 
            return false;           
        return substr($text, 0, -1 * $pad);    
     }    
	
}
?>