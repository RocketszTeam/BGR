<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define("up_account",SUPER_up_account);
define("up_passwd",SUPER_up_passwd);
define("SUPERT_API_URL",SUPER_API_URL);
define("SUPER_KEY",SUPER_key);
define("SUPER_IV",SUPER_iv);
//define("SUPERT_GAME_URL","http://api.funds.uc8slots.com/GameLauncher");
class Superapi{
	var	$cipher = MCRYPT_RIJNDAEL_128;
	var	$mode = MCRYPT_MODE_CBC;
	var $copy_target=SUPER_copy_target; //要複製的會員帳號
	var $CI;
	var $timeout=SUPER_timeout;	//curl允許等待秒數
	var $account_prefix;
	public function __construct(){	
		$this->CI =&get_instance();
		//測試ID：171228 
	}
	
	//修改帳號資料(複製設定) 
	public function update($u_id){
		$post_data=array('act'=>'cpSettings','account'=>$this->getAesEncrypt($u_id), 'level'=>1,'up_account'=>$this->getAesEncrypt(up_account),'up_passwd'=>$this->getAesEncrypt(up_passwd),'copy_target'=>$this->copy_target);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/account");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){	
			
			}
		}
	}
	
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=8){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where  `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api	
			//針對帳號密碼 超過13碼的啟用 自動配發帳密
			if(strlen(trim($u_id)) > 13 || strlen(trim($u_password)) > 13){
				$this->account_prefix=substr($u_id,0,2);	//根據傳入帳號取得遊戲帳號前墜
				$u_id=$this->auto_account();
				$u_password=$u_id;	//密碼同帳號
				if($u_id==NULL){
					return '遊戲帳號生成失敗！';
					exit;	
				}
			}
			$post_data=array('act'=>'cpAdd','account'=>$this->getAesEncrypt($u_id),'nickname'=>$u_id,'passwd'=>$this->getAesEncrypt($u_password),
							 'level'=>1,'up_account'=>$this->getAesEncrypt(up_account),'up_passwd'=>$this->getAesEncrypt(up_passwd),'copy_target'=>$this->copy_target);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/account");		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
			//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);
			if(!$curl_errno){
				if($http_code===200 && ($output->code=='999' || $output->code=='912')){	//帳號創建成功 或者 帳號已存在對方資料庫 判斷斷建成功
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
		$post_data=array('act'=>'search','account'=>$this->getAesEncrypt($u_id),'up_account'=>$this->getAesEncrypt(up_account),'up_passwd'=>$this->getAesEncrypt(up_passwd));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/points");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				return $output->data->point;
			}else{
				return '--';	
			}
		}else{
			return '--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=8,$logID=NULL){	//轉入點數到遊戲帳號內
		$track_id=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$track_id."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		$post_data=array('act'=>'add','account'=>$this->getAesEncrypt($u_id),'point'=>$amount,'track_id'=>$track_id,'up_account'=>$this->getAesEncrypt(up_account),'up_passwd'=>$this->getAesEncrypt(up_passwd));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/points");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				$parameter=array();
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				//異動前點數
				$before_balance=(float)$WalletTotal;
				//異動後點數
				$after_balance= (float)$before_balance - (float)$amount;
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=3;	//轉入遊戲
				$parameter[":points"]="-".$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]=$output->point;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				
				return "轉點錯誤:".$output->code.$output->msg;	
			}
		}else{
			return $this->point_checking($track_id,1,$u_id,$amount,$mem_num,$gamemaker_num);	
		}	
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=8,$logID=NULL){	//遊戲點數轉出
		$track_id=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$track_id."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
				
		$post_data=array('act'=>'sub','account'=>$this->getAesEncrypt($u_id),'point'=>$amount,'track_id'=>$track_id,'up_account'=>$this->getAesEncrypt(up_account),'up_passwd'=>$this->getAesEncrypt(up_passwd));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/points");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				$parameter=array();
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				//異動前點數
				$before_balance=(float)$WalletTotal;
				//異動後點數
				$after_balance= (float)$before_balance + (float)$amount;
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=4;	//遊戲轉出
				$parameter[":points"]=$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]=$output->point;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return "轉點錯誤:".$output->code.$output->msg;
				//return $output->ErrorMessage;	
			}
		}else{
			return $this->point_checking($track_id,2,$u_id,$amount,$mem_num,$gamemaker_num);	
		}
	}
	//type=1 ;代表 錢包轉入遊戲 需先檢查 錢包餘額
	public function point_checking($track_id,$type,$u_id,$amount,$mem_num,$gamemaker_num){	//點查轉帳情況
		$post_data=array('act'=>'checking','account'=>$this->getAesEncrypt($u_id),'track_id '=>$track_id,'up_account'=>$this->getAesEncrypt(up_account),'up_passwd'=>$this->getAesEncrypt(up_passwd));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/points");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				if($output->data->result==1){
					if($type==1){
						$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
						if((int)$WalletTotal >=(int)$amount){
							$parameter=array();
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
							$parameter[":makers_balance"]=$output->data->o2;
							$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
							$parameter[":buildtime"]=now();
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$this->CI->webdb->sqlExc($sqlStr,$parameter);
							
						}else{
							return '錢包點數不足';
						}
					}else{
						$parameter=array();
						$WalletTotal=getWalletTotal($mem_num);	//會員餘額
						//異動前點數
						$before_balance=(float)$WalletTotal;
						//異動後點數
						$after_balance= (float)$before_balance + (float)$amount;
						$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
						$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$mem_num;
						$parameter[":kind"]=4;	//遊戲轉出
						$parameter[":points"]=$amount;
						$parameter[":makers_num"]=$gamemaker_num;
						$parameter[":makers_balance"]=$output->data->o2;
						$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
						$parameter[":buildtime"]=now();
						$parameter[':before_balance']=$before_balance;
						$parameter[':after_balance']=$after_balance;
						$this->CI->webdb->sqlExc($sqlStr,$parameter);
						
					}
					return NULL;
				}else{
					return '無此記錄';	
				}
			}else{
				return "查詢轉點錯誤:".$output->code.$output->msg;
			}
		}else{
			return '系統繁忙中，請稍後再試';	
		}
	
	}
	
	public function forward_game($u_id,$u_password){	//登入遊戲
		//$logMsg=$this->update($u_id);
		$post_data=array('account'=>$this->getAesEncrypt($u_id),'passwd'=>$this->getAesEncrypt($u_password),'responseFormat'=>'json');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/login");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				return 	$output->data->login_url;
			}
		}
	}
	
	//報表
	public function reporter_all($sTime,$eTime){
		$sDate=date('Y-m-d',strtotime($sTime));
		$eDate=date('Y-m-d',strtotime($eTime));
		$sTime=date('H:i:s',strtotime($sTime));
		$eTime=date('H:i:s',strtotime($eTime));
		
		$post_data=array('act'=>'detail','account'=>$this->getAesEncrypt(up_account),'level'=>2,
						 's_date'=>$sDate,'e_date'=>$eDate,'start_time'=>$sTime,'end_time'=>$eTime,'ball'=>0,'type'=>0);
		//print_r($post_data);				 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,SUPERT_API_URL."/report");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,60); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($http_code);
		//print_r($curl_errno);
		//print_r($output);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				return $output->data;
			}
		}
	}
	
	public function getLoginUrl(){
		return SUPERT_API_URL."/login";
	}
	
	
	//定義類型
	public function gTypeArr(){
		$array=array(0=>'全場',1=>'上半場',2=>'下半場',3=>'第一節',4=>'第二節',5=>'第三節',6=>'第四節',7=>'滾球',
					 8=>'滾球上半場',9=>'滾球下半場',10=>'多種玩法');
		return $array;	
	}
	
	//定義玩法
	public function fashionArr(){
		$array=array(1=>'讓分',2=>'大小',3=>'獨贏',4=>'單雙',5=>'一輸二贏',10=>'搶首分',11=>'搶尾分',12=>'波膽',13=>'單節最高分',20=>'過關');
		return $array;	
	}
	
	
	//補零
	private function add_zero($str,$len){
		if($str!='' && $len!=''){
			return str_pad($str,$len,'0',STR_PAD_LEFT);	
		}
	}
	
	//自動生成帳號
	private function auto_account(){
		$SN=NULL;
		$this->CI->db->trans_begin();
		$query=$this->CI->db->query('select `AccNo` from `auto_account` order by `AccNo` DESC Limit 1');
		if ($query->num_rows() == 0){	//沒資料
			$SN=$this->account_prefix.chr(65).$this->add_zero(1,10);
		}else{
			$row=$query->row_array();
			$s_asc=ord(substr($row["AccNo"],2,1)); //取得第二碼的ASCII代碼
			$s_num=substr($row["AccNo"],3,10); //後10碼流水號
			if(strlen((int)$s_num+1) == 11){ //如果流水號用光了
				$SN=$this->account_prefix.chr($s_asc+1).$this->add_zero(1,10);
			}else{
				$SN=$this->account_prefix.chr($s_asc).$this->add_zero(((int)$s_num+1),10);
			}
		}
		$this->CI->db->query("INSERT INTO `auto_account` (`AccNo`) VALUES ('".$SN."')");
		if ($this->CI->db->trans_status() === FALSE){
			$this->CI->db->trans_rollback();
		}else{
			 $this->CI->db->trans_commit();
		}
		return $SN;
	}
	
	//解密
	function aesDecrypt($encryptedData) {
		$initializationVectorSize = mcrypt_get_iv_size($this->cipher, $this->mode);
		$data =  mcrypt_decrypt(
				$this->cipher,
				SUPER_KEY,
				base64_decode($encryptedData),
				$this->mode,
				SUPER_IV
		);
		$pad = ord($data[strlen($data) - 1]);
		return substr($data, 0, -$pad);
	}
	// 加密
	public function getAesEncrypt($xml_data) {
		$blockSize = mcrypt_get_block_size($this->cipher, $this->mode);
		$pad = $blockSize - (strlen($xml_data) % $blockSize);
		return base64_encode(mcrypt_encrypt(
				$this->cipher,
				SUPER_KEY,
				$xml_data . str_repeat(chr($pad), $pad),
				$this->mode,
				SUPER_IV
		));
	}	
	
	
}
?>