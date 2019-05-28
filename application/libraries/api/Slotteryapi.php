<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Slotteryapi{
	var $up_acc=slottery_up_acc;
	var $up_pwd=slottery_up_pwd;
	var $api_url=slottery_api_url;
	var	$cipher = slottery_cipher;
	var	$mode = slottery_mode;
	var $copy_target=slottery_copy_target; //要複製的會員帳號
	var $key=slottery_key;
	var $iv=slottery_iv;
	var $CI;
	var $account_prefix;
	var $timeout=15;	//curl允許等待秒數
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");	
	}
	
	public function create_agent ()
	{
		$post_data = [
				'act' => 'create_cp', 'account' => $this->getAesEncrypt($this->up_acc), 'nickname' => 'em66', 'passwd' => $this->getAesEncrypt($this->up_pwd), 'up_acc' => $this->getAesEncrypt('F'), 'up_pwd' => $this->getAesEncrypt('E6Hs0wnkq'), 'copy_target' => 'Fltex5'
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url . "/account");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		print_r($output);
		exit;

	}	
	

	public function create_exampleaccount(){	//創建遊戲帳號
		$u_password = $this->up_pwd;
		$post_data=array('act'=>'create','account'=>$this->getAesEncrypt(slottery_copy_target),'nickname'=>slottery_copy_target,'passwd'=>$this->getAesEncrypt($u_password),
							'up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/account");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		print_r($post_data);
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && ($output->code=='999' || $output->code=='912')){	//帳號創建成功

				echo NULL;
			}else{
				echo $output->msg;	
			}
		}else{
			echo '系統繁忙中，請稍後再試'	;
		}
	}

	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=20){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where  `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	////先判定此會員是否已有帳號	無帳號才呼叫api
			//針對帳號密碼 超過13碼的啟用 自動配發帳密
			if(strlen(trim($u_id)) > 13 || strlen(trim($u_password)) >13){
				$this->account_prefix=substr($u_id,0,2);	//根據傳入帳號取得遊戲帳號前墜
				$u_id=$this->auto_account();
				$u_password=$u_id;	//密碼同帳號
				if($u_id==NULL){
					return '遊戲帳號生成失敗！';
					exit;	
				}
			}
			$post_data=array('act'=>'create_cp','account'=>$this->getAesEncrypt($u_id),'nickname'=>$u_id,'passwd'=>$this->getAesEncrypt($u_password),
							 'up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd),'copy_target'=>$this->copy_target);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/account");		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			//print_r($post_data);
			//print_r($output);

			if(!$curl_errno){
				if($http_code===200 && ($output->code=='999' || $output->code=='912')){	//帳號創建成功
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
	
	private function copy_settings($u_id){
		$post_data=array('act'=>'copy_settings','account'=>$this->getAesEncrypt($u_id),
						 'up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd),'copy_target'=>$this->copy_target);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/account?tk=df2c7cf2a1b211e79c18000c29231b4b");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){	//帳號創建成功
			
			}
		}
		
	}
	
	
	public function get_balance($u_id){	//餘額查詢
		$post_data=array('act'=>'read','account'=>$this->getAesEncrypt($u_id),'up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/points");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
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
	
	public function deposit_agent ()
	{    //初始動作 - 轉入點數到遊戲帳號內
		$track_id = time() . mt_rand(1111, 9999);
		$amount = 2000000;
		$post_data = [
				'act'     => 'add', 'up_acc' => $this->getAesEncrypt('F'), 'up_pwd' => $this->getAesEncrypt('E6Hs0wnkq'),
				'account' => $this->getAesEncrypt($this->up_acc), 'Point' => $amount, 'track_id' => $track_id
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url . "/points");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		print_r($output);
		curl_close($ch);
		
	}
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=20,$logID=NULL){	//轉入點數到遊戲帳號內
		$track_id=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$track_id."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('act'=>'add','up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd),
		                 'account'=>$this->getAesEncrypt($u_id),'Point'=>$amount,'track_id'=>$track_id);
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
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
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
				$parameter[":makers_balance"]=$output->data->point;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}elseif($http_code===200 && $output->code=='422'){
				return "上層餘額不足";	
			}else{
				return "轉點錯誤:".$output->code.$output->msg;
			}
		}else{
			return $this->point_checking($track_id,1,$u_id,$amount,$mem_num,$gamemaker_num);	
		}	
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=20,$logID=NULL){	//遊戲點數轉出
		$track_id=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$track_id."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('act'=>'sub','up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd),
		                 'account'=>$this->getAesEncrypt($u_id),'Point'=>$amount,'track_id'=>$track_id);
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
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
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
				$parameter[":makers_balance"]=$output->data->point;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}elseif($http_code===200 && $output->code=='422'){
				return "餘額不足";	
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
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				if($output->data->result==1){
					if($type==1){
						$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
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
							$parameter[":makers_balance"]=$output->data->after;
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
						$after_balance= (float)$before_balance - (float)$amount;//異動後點數
						$parameter=array();
						$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
						$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$mem_num;
						$parameter[":kind"]=4;	//遊戲轉出
						$parameter[":points"]=$amount;
						$parameter[":makers_num"]=$gamemaker_num;
						$parameter[":makers_balance"]=$output->data->after;
						$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
						$parameter[":buildtime"]=now();
						$parameter[':before_balance']=$before_balance;
						$parameter[':after_balance']=$after_balance;
						$this->CI->webdb->sqlExc($sqlStr,$parameter);
						return NULL;
					}
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
	
	public function points_record($u_id,$Date){
		$post_data=array('act'=>'record','up_acc'=>$this->getAesEncrypt($this->up_acc),'up_pwd'=>$this->getAesEncrypt($this->up_pwd),
		                 'account'=>$this->getAesEncrypt($u_id),'Date'=>$Date);
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
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				return $output->data;
			}
		}
		
	}
	
	
	public function forward_game($u_id,$u_password){	//登入遊戲
		$this->copy_settings($u_id);
		$post_data=array('account'=>$this->getAesEncrypt($u_id),'passwd'=>$this->getAesEncrypt($u_password));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/login");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output) ;
		//exit();
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				
				echo '<form id="sallbetForm" method="post" action="'.$output->data->PostHost.'">';
				echo '<input type="hidden" name="PostData" value="'.$output->data->PostData.'" />';
				echo '<script type="application/javascript">document.getElementById("sallbetForm").submit();</script>';
				echo '</form>';
				exit;
			}elseif($output->code=='503'){	//維修
				scriptCloseMsg('系統維修中！');
				exit;
				//return $output->code;
			}else{
				scriptCloseMsg('取得遊戲連結錯誤！ :'.$output->code);
				exit;
			}
		}else{
			//超時處理
			scriptCloseMsg('遊戲載入超時，請重新載入！');
			exit;	
		}
	}
	
	//報表
	public function reporter_all($sDate,$eDate){
		$post_data=array('account'=>$this->getAesEncrypt($this->up_acc),'passwd'=>$this->getAesEncrypt($this->up_pwd),
		                 'start_date'=>$sDate,'end_date'=>$eDate);	
		//print_r($post_data);				 			 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/report");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,60); 		
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//echo '<hr>回應：';
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				return $output->data;
			}
		}
				
	}
	
	public function getLoginUrl(){
		//return SUPERT_API_URL."/login";
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
				$this->key,
				base64_decode($encryptedData),
				$this->mode,
				$this->iv
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
				$this->key,
				$xml_data . str_repeat(chr($pad), $pad),
				$this->mode,
				$this->iv
		));
	}	
	
	
}
?>