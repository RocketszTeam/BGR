<?php
defined('BASEPATH') OR exit('No direct script access allowed');


define("ALLBET_Agent", AB_Agent);
define("ALLBET_PROPERTY_ID", AB_PROPERTY_ID);
define("ALLBET_DES_KEY", AB_DES_KEY);
define("ALLBET_MD5_KEY", AB_MD5_KEY);
define("ALLBET_API_URL", AB_API_URL);
define("ALLBET_IDEN_CODE",AB_IDEN_CODE);	

class Allbetapi {
	var $CI;
	var $timeout=AB_timeout;	//curl允許等待秒數.
	public function __construct(){	
		$this->CI =&get_instance();
		
	}
	public function query_handicaps(){
		$real_param = http_build_query(array('agent' => ALLBET_Agent, 'random' => mt_rand()));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/query_handicap?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		echo $http_code.'<br>'.$curl_errno;
		curl_close($ch);
		print_r( $output);
		if($http_code===200 && $output->error_code=='OK'){
			print_r($output->handicaps);	
		}
	}

    /*
     * Array ( [0] => stdClass Object ( [handicapType] => 0 [id] => 7 [lowerLimit] => 500.00 [name] => G [upperLimit] => 50000.00 )
     * [1] => stdClass Object ( [handicapType] => 1 [id] => 158 [lowerLimit] => 5000.00 [name] => VIP_Y2 [upperLimit] => 500000.00 )
     * [2] => stdClass Object ( [handicapType] => 0 [id] => 5 [lowerLimit] => 300.00 [name] => E [upperLimit] => 30000.00 )
     * [3] => stdClass Object ( [handicapType] => 0 [id] => 3 [lowerLimit] => 100.00 [name] => C [upperLimit] => 10000.00 )
     * [4] => stdClass Object ( [handicapType] => 0 [id] => 4 [lowerLimit] => 200.00 [name] => D [upperLimit] => 20000.00 ) )
     */
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=3){	//創建遊戲帳號.
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api
			$real_param = http_build_query(array('agent' => ALLBET_Agent, 
												 'random' => mt_rand(),
												 'client'=>$u_id,
												 'password'=>$u_password,
												 'vipHandicaps'=>158,
												 'orHandicaps'=>'3,5,7',
												 'orHallRebate'=>0,
												 'maxWin'=>200000));
			$data = base64_encode($this->encryptText($real_param));
			$sign = $this->getSignCode($data);	
			$ch = curl_init(ALLBET_API_URL."/check_or_create?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = json_decode(curl_exec($ch));
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errno = curl_errno($ch);
			curl_close($ch);
			//print_r($output);
			if(!$curl_errno){
				if($http_code===200 && $output->error_code=='OK'){
					//把帳號寫入資料庫
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
					return $output->error_code.':'.$output->message;
				}	
			}else{
				return '系統繁忙中，請稍候再試';
			}
		}else{
			return '會員已有此類型帳號';	
		}
	}
	
	public function get_balance($u_id,$u_password){	//餘額查詢
		$real_param = http_build_query(array('random' => mt_rand(),
											 'client'=>$u_id,
											 'password'=>$u_password));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/get_balance?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				return $output->balance;
			}else{
				return '--';	
			}
		}else{
			//超時處理
			return '--';	
		}
	}
	
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=3,$logID=NULL){	//轉入點數到遊戲帳號內
		$sn=ALLBET_PROPERTY_ID.time().mt_rand(111,999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$sn."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		$real_param = http_build_query(array('agent' => ALLBET_Agent, 
											 'random' => mt_rand(),
											 'sn'=>$sn,	//共20碼
											 'client'=>$u_id,
											 'operFlag'=>1,	//1=存入;0=提出
											 'credit'=>$amount));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/agent_client_transfer?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				$makers_balance=$this->check_transfer($sn);
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
				return $output->error_code;
			}
		}else{
			//超時處理
			return $this->point_checking($sn,1,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
			//return '系統繁忙中，請稍候再試';
		}
	}
	
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=3,$logID=NULL){	//遊戲點數轉出
		$sn=ALLBET_PROPERTY_ID.time().mt_rand(111,999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$sn."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		$real_param = http_build_query(array('agent' => ALLBET_Agent, 
											 'random' => mt_rand(),
											 'sn'=>$sn,	//共20碼
											 'client'=>$u_id,
											 'operFlag'=>0,	//1=存入;0=提出
											 'credit'=>$amount));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/agent_client_transfer?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				$makers_balance=$this->check_transfer($sn);
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
				return $output->error_code;	
			}
		}else{
			return $this->point_checking($sn,2,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
			//return '系統繁忙中，請稍候再試';
		}
	}
	
	public function forward_game($u_id,$u_password,$GameCode=NULL){	//登入遊戲
		//$this->maxWin($u_id);
		$real_param=array('random' => mt_rand(),
						  'client'=>$u_id,
						  'password'=>$u_password,'language'=>'zh_TW',
						  'gameHall'=>$GameCode
						);
		if($this->CI->agent->is_mobile()){	//手機板改用h5		
			$real_param=array_merge($real_param,array('targetSite'=>'https://www.allbetgame.net/h5'));
		}
		$real_param = http_build_query($real_param);
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/forward_game?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				return $output->gameLoginUrl;	//回傳遊戲連結
			}else{
				return $output->error_code;	
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	//設定最高贏額 & 限紅
	public function maxWin($u_id){
		$real_param = http_build_query(array('agent' => ALLBET_Agent, 
											 'random' => mt_rand(),
											 'client'=>trim($u_id),
											 'vipHandicaps'=>158,
											 'orHandicaps'=>'1,5,44',
											 'maxWin'=>200000));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/modify_client?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				
			}
		}
	}
	
	
	public function check_transfer($sn){	//查詢轉帳情況
		$real_param = http_build_query(array('random' => mt_rand(),
											 'sn'=>$sn
											 ));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/query_transfer_state?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				return $output->clientCreditAfter;	//回傳轉帳後額度
			}else{
				return "0";	
			}
		}else{
			return '0';
		}
		
	}
	
	public function point_checking($sn,$type,$u_id,$amount,$mem_num,$gamemaker_num,$logID=NULL){
		$real_param = http_build_query(array('random' => mt_rand(),
											 'sn'=>$sn
											 ));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/query_transfer_state?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				if($output->transferState==1){
					$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
					if($type==1){
						if((int)$WalletTotal >=(int)$amount){
							$parameter=array();
							$before_balance=(float)$WalletTotal;	//異動前
							$after_balance= (float)$before_balance - (float)$amount;//異動後點數
							$parameter=array();
							$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[":mem_num"]=$mem_num;
							$parameter[":kind"]=3;	//轉入遊戲
							$parameter[":points"]="-".$amount;
							$parameter[":makers_num"]=$gamemaker_num;
							$parameter[":makers_balance"]=$output->clientCreditAfter;
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
						$parameter=array();
						$WalletTotal=getWalletTotal($mem_num);	//會員餘額
						$before_balance=(float)$WalletTotal;//異動前點數
						$after_balance= (float)$before_balance + (float)$amount;//異動後點數
						$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
						$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$mem_num;
						$parameter[":kind"]=4;	//遊戲轉出
						$parameter[":points"]=$amount;
						$parameter[":makers_num"]=$gamemaker_num;
						$parameter[":makers_balance"]=$output->clientCreditAfter;
						$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
						$parameter[":buildtime"]=now();
						$parameter[':before_balance']=$before_balance;
						$parameter[':after_balance']=$after_balance;
						$this->CI->webdb->sqlExc($sqlStr,$parameter);
						return NULL;
					}
					//將轉點編號寫入DB
					$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$sn."' where num=".$logID;
					$this->CI->webdb->sqlExc($upSql);
				}else{
					return '無此轉點紀錄：'.$output->transferState;	
				}
			}else{
				return "查詢轉點錯誤：".$output->error_code.$output->message;	
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	
	}
	
	public function reporter_all($sTime,$eTime){	//群撈報表
		$real_param = http_build_query(array('random' => mt_rand(),
											 'startTime'=>$sTime,
											 'endTime'=>$eTime));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/betlog_pieceof_histories_in30days?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,180);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				return $output->histories;
			}else{
				return $output->error_code;	
			}	
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	public function reporter_all2($sTime,$eTime){	//群撈報表
		$real_param = http_build_query(array('random' => mt_rand(),
											 'startDate'=>$sTime,
											 'endDate'=>$eTime,
											 ));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/betlog_daily_histories?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_TIMEOUT,180);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		print_r($output);
		//if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				return $output->histories;
			}else{
				
			}	
		//}else{
			//return '系統繁忙中，請稍候再試';
		//}
	}
	
	//電子遊戲報表-空戰
	/** 调用次数限制: 8 次/10 分钟 (以每个 propertyId 计算)*/
	public function reporter_af($sTime,$eTime,$pageIndex=1,$pageSize=1000){	//群撈報表
		$real_param = http_build_query(array('random' => mt_rand(),
			                                 'egameType' => 'af',
											 'startTime'=>$sTime,
											 'endTime'=>$eTime,
											 'pageIndex'=>$pageIndex,
											 'pageSize'=>$pageSize
											 ));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/egame_betlog_histories?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_TIMEOUT,180);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		//print_r($output);
		//echo '<hr>';
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				return $output->page;
			}else{
				//return $output->error_code;	
			}	
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	
	
	public function check($u_id,$u_password){
		$real_param = http_build_query(array('random' => mt_rand(),
											 'username'=>$u_id,
											 'password'=>$u_password
											 ));
		$data = base64_encode($this->encryptText($real_param));
		$sign = $this->getSignCode($data);	
		$ch = curl_init(ALLBET_API_URL."/check?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		print_r($output);
		print_r($http_code);
		if(!$curl_errno){
			if($http_code===200 && $output->error_code=='OK'){
				
			}
		}
	}
	
	
	public function getIdenCode(){
		return 	ALLBET_IDEN_CODE;
	}
	
	private function getSignCode($data){
		$to_sign = $data.ALLBET_MD5_KEY;
		return base64_encode(md5($to_sign, TRUE));	
	}
	
	
	private function pkcs5Pad($text, $blocksize) {
	    $pad = $blocksize - (strlen($text) % $blocksize);
	    return $text . str_repeat(chr($pad), $pad);
	}

	private function pkcs5Unpad($text) {
	    $pad = ord($text{strlen($text)-1});
	    if ($pad > strlen($text)) return false;
	    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
	    return substr($text, 0, -1 * $pad);
	}

	private function encryptText($plain_text) {
	    $padded = $this->pkcs5Pad($plain_text, mcrypt_get_block_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_CBC));
		return mcrypt_encrypt(MCRYPT_TRIPLEDES, base64_decode(ALLBET_DES_KEY), $padded, MCRYPT_MODE_CBC, base64_decode("AAAAAAAAAAA="));
	}

	private function decryptText($cipher_text) {
	    $plain_text = mcrypt_decrypt(MCRYPT_TRIPLEDES, base64_decode(ALLBET_DES_KEY), $cipher_text, MCRYPT_MODE_CBC, base64_decode("AAAAAAAAAAA="));
	    return $this->pkcs5Unpad($plain_text);
	}
	
	
	//定義投注類型
	public function get_betType(){
		//百家樂
		$betType['1001']='莊';
		$betType['1002']='閒';
		$betType['1003']='和';
		$betType['1004']='大';
		$betType['1005']='小';
		$betType['1006']='莊對';
		$betType['1007']='閒對';
		//龍虎
		$betType['2001']='龍';
		$betType['2002']='虎';
		$betType['2003']='和';
		//骰寶
		$betType['3001']='小';
		$betType['3002']='單';
		$betType['3003']='雙';
		$betType['3004']='大';
		$betType['3005']='圍一';
		$betType['3006']='圍二';
		$betType['3007']='圍三';
		$betType['3008']='圍四';
		$betType['3009']='圍五';
		$betType['3010']='圍六';
		$betType['3011']='全圍';
		$betType['3012']='對1';
		$betType['3013']='對2';
		$betType['3014']='對3';
		$betType['3015']='對4';
		$betType['3016']='對5';
		$betType['3017']='對6';
		$betType['3018']='和值：4';
		$betType['3019']='和值：5';
		$betType['3020']='和值：6';
		$betType['3021']='和值：7';
		$betType['3022']='和值：8';
		$betType['3023']='和值：9';
		$betType['3024']='和值：10';
		$betType['3025']='和值：11';
		$betType['3026']='和值：12';
		$betType['3027']='和值：13';
		$betType['3028']='和值：14';
		$betType['3029']='和值：15';
		$betType['3030']='和值：16';
		$betType['3031']='和值：17';
		$betType['3033']='牌九式：12';
		$betType['3034']='牌九式：13';
		$betType['3035']='牌九式：14';
		$betType['3036']='牌九式：15';
		$betType['3037']='牌九式：16';
		$betType['3038']='牌九式：23';
		$betType['3039']='牌九式：24';
		$betType['3040']='牌九式：25';
		$betType['3041']='牌九式：26';
		$betType['3042']='牌九式：34';
		$betType['3043']='牌九式：35';
		$betType['3044']='牌九式：36';
		$betType['3045']='牌九式：45';
		$betType['3046']='牌九式：46';
		$betType['3047']='牌九式：56';
		$betType['3048']='單骰：1';
		$betType['3049']='單骰：2';
		$betType['3050']='單骰：3';
		$betType['3051']='單骰：4';
		$betType['3052']='單骰：5';
		$betType['3053']='單骰：6';
		//輪盤
		$betType['4001']='小';
		$betType['4002']='雙';
		$betType['4003']='紅';
		$betType['4004']='黑';
		$betType['4005']='單';
		$betType['4006']='大';
		$betType['4007']='第一打';
		$betType['4008']='第二打';
		$betType['4009']='第三打';
		$betType['4010']='第一列';
		$betType['4011']='第二列';
		$betType['4012']='第三列';
		for($i=0;$i<37;$i++){
			$betType[("4013"+$i)]='直接注：'.$i;
		}
		$betType['4050']='三數：(0/1/2)';
		$betType['4051']='三數：(0/2/3)';
		$betType['4052']='四數：(0/1/2/3)';
		for($i=0;$i<3;$i++){
			$betType[("4053"+$i)]='分注：(0/'.($i+1).')';
		}
		$betType["4056"]='分注：(1/2)';
		$betType["4057"]='分注：(2/3)';
		$betType["4058"]='分注：(4/5)';
		$betType["4059"]='分注：(5/6)';
		$betType["4060"]='分注：(7/8)';
		$betType["4061"]='分注：(8/9)';
		$betType["4062"]='分注：(10/11)';
		$betType["4063"]='分注：(11/12)';
		$betType["4064"]='分注：(13/14)';
		$betType["4065"]='分注：(14/15)';
		
		
		return $betType;	
	}
	
	//百家 & 龍虎開牌結果
	public function get_gameResult(){
		for($i=1;$i<=13;$i++){
			$sTitle['1'.str_pad($i,2,'0',STR_PAD_LEFT)]='黑桃'.str_pad($i,2,'0',STR_PAD_LEFT);
		}
		for($i=1;$i<=13;$i++){
			$sTitle['2'.str_pad($i,2,'0',STR_PAD_LEFT)]='紅桃'.str_pad($i,2,'0',STR_PAD_LEFT);
		}
		for($i=1;$i<=13;$i++){
			$sTitle['3'.str_pad($i,2,'0',STR_PAD_LEFT)]='梅花'.str_pad($i,2,'0',STR_PAD_LEFT);
		}
		for($i=1;$i<=13;$i++){
			$sTitle['4'.str_pad($i,2,'0',STR_PAD_LEFT)]='方塊'.str_pad($i,2,'0',STR_PAD_LEFT);
		}
		return $sTitle;
	}
	
}
?>