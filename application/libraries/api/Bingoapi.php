<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Bingoapi{
	var $CI;
	var $timeout=6;	//curl允許等待秒數
	var $agentID='VIP666';	//經銷商
	var $key='zJur2R5mnF4tLrANkejeX9n3JL6maYkn';
	var $api_url='http://192.190.225.78/api';
	var $game_url='http://192.190.225.78/loginUrl';
	
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function create_account($u_id,$mem_num,$gamemaker_num=23){
		//先判定此會員是否已有帳號	
		
		$parameter=array();
		$sqlStr="select * from `games_account` where  `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api
			$data= json_encode(array('agent'=>$this->agentID,'client'=>trim($u_id),'name'=>trim($u_id)));
			$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
			//print_r($post_data);;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/CheckOrCreateAccount?".http_build_query($post_data));	
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
			$output=json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);	
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->error_code==0){
					$colSql="u_id,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);	
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					//$this->CheckOrSetLimit(trim($u_id));	//呼叫限額設定
					return NULL;
				}else{
					return 	$output->errcode;
				}
			}else{
				return '系統繁忙中，請稍候再試';	
			}
		}else{
			return '會員已有此類型帳號';
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$data= json_encode(array('agent'=>$this->agentID,'client'=>trim($u_id),'name'=>trim($u_id)));
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		//print_r($post_data);;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/GetBalance?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				return $output->balance;
			}else{
				return	'--';
			}
		}else{
			return	'--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=23){	//轉入點數到遊戲帳號內
		$sn=time().mt_rand(1111,9999);
		$data= json_encode(array('agent'=>$this->agentID,'client'=>trim($u_id),'sn'=>$sn,'type'=>1,'amount'=>trim($amount)));
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/TransferCredit?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
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
				$parameter[":makers_balance"]=$output->balance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
				//$output->point; 加點後的餘額欄位
			}else{
				return '轉點錯誤：'.$output->error_code;
			}
		}else{
			return $this->point_checking($sn,$mem_num,$gamemaker_num);
			//return	'系統繁忙中，請稍候再試';	
		}
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=23){	//遊戲點數轉出
		$sn=time().mt_rand(1111,9999);
		$data= json_encode(array('agent'=>$this->agentID,'client'=>trim($u_id),'sn'=>$sn,'type'=>0,'amount'=>trim($amount)));
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/TransferCredit?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
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
				$parameter[":makers_balance"]=$output->balance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
				//$output->point; 加點後的餘額欄位
			}else{
				return '轉點錯誤：'.$output->error_code;
			}
		}else{
			return $this->point_checking($sn,$mem_num,$gamemaker_num);
			//return	'系統繁忙中，請稍候再試';	
		}
	}
	
	//點數確認~~
	public function point_checking($sn,$mem_num,$gamemaker_num){
		$data= json_encode(array('agent'=>$this->agentID,'sn'=>$sn));
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/CheckTransferCredit?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				if($output->status==1){
					if($output->type==1){	//存款需要檢查錢包點數
						$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
						if((int)$WalletTotal >=(int)$output->amount){
							$before_balance=(float)$WalletTotal;//異動前點數
							$after_balance= (float)$before_balance - (float)$output->amount;//異動後點數
							$parameter=array();
							$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[":mem_num"]=$mem_num;
							$parameter[":kind"]=3;	//轉入遊戲
							$parameter[":points"]="-".$output->amount;
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
						$after_balance= (float)$before_balance + (float)$output->amount;//異動後點數
						$parameter=array();
						$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
						$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$mem_num;
						$parameter[":kind"]=4;	//遊戲轉出
						$parameter[":points"]=$output->amount;
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
					return '轉點失敗或無此紀錄';	
				}
			}else{
				return '轉點錯誤';
			}
		}else{
			return	'系統繁忙中，請稍候再試';
		}
	}
	
	
	public function forward_game($u_id){	//登入遊戲
		$data= json_encode(array('agent'=>$this->agentID,'client'=>trim($u_id),'ip'=>$this->CI->input->ip_address()));
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/ForwardGame?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				return $output->login_url;
			}else{
				return	'--';
			}
		}else{
			return	'系統繁忙中，請稍候再試';	
		}	
	}
	
	//取得限額
	public function getLimit($u_id=NULL){
		$data=array('agent'=>$this->agentID);
		if($u_id!=NULL){
			$data = array_merge($data, array("client"=>trim($u_id)));
		}
		$data= json_encode($data);
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/CheckOrSetLimit?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				return (array)$output->data;
			}else{
				return $output->error_message;
			}
		}
	}
	//設定限額
	public function setLimit($config=NULL,$u_id=NULL){
		$data=array('agent'=>$this->agentID);
		if($u_id!=NULL){
			$data = array_merge($data, array("client"=>trim($u_id)));
		}
		if($config!=NULL){
			$data = array_merge($data, $config);
		}
		$data= json_encode($data);
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/CheckOrSetLimit?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				return NULL;
			}else{
				return $output->error_message;
			}
		}else{
			return	'系統繁忙中，請稍候再試';		
		}
	}
	
	//取得賠率
	public function getOdds(){
		$data=array('agent'=>$this->agentID);
		$data= json_encode($data);
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/CheckOrSetOdds?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				return (array)$output->data;
			}else{
				return $output->error_message;
			}
		}
	}
	public function setOdds($config=NULL){
		$data=array('agent'=>$this->agentID);
		if($config!=NULL){
			$data = array_merge($data, $config);
		}
		$data= json_encode($data);
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/CheckOrSetOdds?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				return NULL;
			}else{
				return $output->error_message;
			}
		}else{
			return	'系統繁忙中，請稍候再試';
		}
	}
	
	public function reporter_all($sTime,$eTime,$u_id=NULL){	//群撈報表
		$data=array('agent'=>$this->agentID,'startTime'=>$sTime,'endTime'=>$eTime,'record'=>'BET');
		if($u_id!=NULL){
			$data = array_merge($data, array("client"=>trim($u_id)));
		}
		$data= json_encode($data);
		$post_data=array('agent'=>$this->agentID,'auth'=>md5($data),'data'=>$this->encrypt($data));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/GetReport?".http_build_query($post_data));	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,60);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);;
		if(!$curl_errno){
			if($http_code===200 && $output->error_code==0){
				return $output->data;
			}else{
				return NULL;	
			}
		}else{
			return NULL;	
		}
	}
	
	
	//***********************************************************************
	//加密用法  encode(字串,密鑰,'E');
	private function encrypt($data){
        $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
        $padding = 16 - (strlen($data) % 16);
        $data .= str_repeat(chr($padding), $padding);
        $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $data, MCRYPT_MODE_CBC, $iv);
		$data = base64_encode($iv . $data);
        return $data;
	}
	//***********************************************************************
	
	
	
}

?>