<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//Ebet
class Ebetapi{
	var $api_url='http://pujing.ebet.im:8888/api';
	var $publicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC9oN0AVZDKLzqTtedk+68s+vIVNCfM8pUk86iMMz7sz8L7DOpnKYXUeSOUbGLoopOV4u41N8kXadZuBCjJLS69brXtg2qQCuPOzhYRZAn+hIPY0BhnRU16Z0an9PhF2TZRNPVU6oydw8YUMopcg6movVi3I18njQC60VwOT5Kt1QIDAQAB';
	var $privateKey='MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAL2g3QBVkMovOpO152T7ryz68hU0J8zylSTzqIwzPuzPwvsM6mcphdR5I5RsYuiik5Xi7jU3yRdp1m4EKMktLr1ute2DapAK487OFhFkCf6Eg9jQGGdFTXpnRqf0+EXZNlE09VTqjJ3DxhQyilyDqai9WLcjXyeNALrRXA5Pkq3VAgMBAAECgYAlNzLoY+Kcq5Q1dRfKq9J/Y2irXKcLA/jdXayQh2YsF8JOfwRp5q5LOtMOyA7JVU7dtcHGVAJ1Q+I/iTVv9hwb3oL1Wp2tq7q8T7rAwMWmQGuypjjY+kbqj4oUpxZzeJ5xuZLXFuzsg+v3+SEU82WSNaEBOJVdxT1qPWjHMI0b6QJBAPKBmuENVEK8Uv9oN2PFyvavry/4CjIsyIDUBkPUAqGXv5kKGskO+dEIvHM1TfJ2tEdaDeY6+BDAvz47VnIjkYcCQQDILgkyzc1yuE0k4Vo6vDMdX91RFFFjilOWgI9v4C5gdph0ni/df/XaxByxBT3tnvWryjfWG9Gl0ykEozvrM4zDAkEA2SQHxGAlBKSQRLXScvoWZJCm8vLMXmUPG5u+CFn8CSlRm/0aQtGwCuYhp58hLmvvvLv8GhzPJmEQXO7Q1t7WXQJBAKfjXFmcm6OMiT7WNeuu7hvDzAV1SfF3ETXXqvVEiwDiVmjwRuq5qEQLWJjq8Y56VEb5Oa079a/jErLOCLHxsSsCQQCbAfIt8ZtIIVrm8czNtnYOI7SPLe5GwRr3i4uCaFi99RYFGvbvkmdSDO1zfIIDRQB7vz7hMXs3WYmfYPt2JnLp';
	var $channelId='354';
	var $subChannelId=0;	//子渠道ID
	var $game_url='http://pujing.sdfd.rocks/h5/q8flp5?';
	var $CI;
	var $timeout=10;	//curl允許等待秒數
	
	public function __construct(){	
		$this->CI =&get_instance();
		$this->CI->load->library('rsa');	//載入加密解密涵式
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function create_account($u_id,$mem_num,$gamemaker_num=24){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api		
			//先判定此會員是否已有帳號	
			$post_data=array('username'=>strtolower(trim($u_id)),'lang'=>2,'signature'=>$this->sign(strtolower(trim($u_id))),
					         'channelId'=>$this->channelId,'subChannelId'=>$this->subChannelId);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/syncuser");		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->status==200){	//帳號創建成功
					$colSql="u_id,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);	
					//$parameter[':u_password']=$this->CI->encryption->encrypt($u_password);	//密碼加密
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return '創建錯誤：'.$output->status;	
				}
			}else{
				return '系統繁忙中，請稍後再試'	;
			}
		}else{
			return '會員已有此類型帳號';
		}
		
	}
	
	public function get_balance($u_id){	//餘額查詢
		$timestamp=time();
		$post_data=array('username'=>strtolower(trim($u_id)),'timestamp'=>$timestamp,'signature'=>$this->sign(strtolower(trim($u_id.$timestamp))),
						 'channelId'=>$this->channelId);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/userinfo");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->status==200){
				return $output->money;
			}else{
				return '--';	
			}
		}else{
			return '--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=24,$logID=NULL){	//轉入點數到遊戲帳號內
		$timestamp=time();
		$rechargeReqId=time().mt_rand(1111,9999);
		$post_data=array('username'=>strtolower(trim($u_id)),'money'=>trim($amount),'timestamp'=>$timestamp,'signature'=>$this->sign(strtolower(trim($u_id.$timestamp))),
						 'channelId'=>$this->channelId,'rechargeReqId'=>$rechargeReqId);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/recharge");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,30); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $rechargeReqId;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->status==200){
				//$parameter=array();
				//$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				//$before_balance=(float)$WalletTotal;//異動前點數
				//$after_balance= (float)$before_balance - (float)$amount;//異動後點數
				
				//$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				//$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				//$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				//$parameter[":mem_num"]=$mem_num;
				//$parameter[":kind"]=3;	//轉入遊戲
				//$parameter[":points"]="-".$amount;
				//$parameter[":makers_num"]=$gamemaker_num;
				//$parameter[":makers_balance"]=$output->money;
				//$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				//$parameter[":buildtime"]=now();
				//$parameter[':before_balance']=$before_balance;
				//$parameter[':after_balance']=$after_balance;
				//$this->CI->webdb->sqlExc($sqlStr,$parameter);
				//將轉點編號寫入DB
				//$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$rechargeReqId."' where num=".$logID;
				//$this->CI->webdb->sqlExc($upSql);
				return $this->point_checking($rechargeReqId,1,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
				//return NULL;
			}else{
				return "轉點錯誤：".$output->status;	
			}
		}else{
			return $this->point_checking($rechargeReqId,1,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
			//return '系統繁忙中，請稍候再試';	
		}	
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=24,$logID=NULL){	//遊戲點數轉出
		$timestamp=time();
		$rechargeReqId=time().mt_rand(1111,9999);
		$post_data=array('username'=>strtolower(trim($u_id)),'money'=>trim("-".$amount),'timestamp'=>$timestamp,'signature'=>$this->sign(strtolower(trim($u_id.$timestamp))),
						 'channelId'=>$this->channelId,'rechargeReqId'=>$rechargeReqId);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/recharge");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,30); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $rechargeReqId;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->status==200){
				//$parameter=array();
				//$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				//$before_balance=(float)$WalletTotal;//異動前點數
				//$after_balance= (float)$before_balance + (float)$amount;//異動後點數
				
				//$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				//$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				//$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				//$parameter[":mem_num"]=$mem_num;
				//$parameter[":kind"]=4;	//遊戲轉出
				//$parameter[":points"]=$amount;
				//$parameter[":makers_num"]=$gamemaker_num;
				//$parameter[":makers_balance"]=$output->money;
				//$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				//$parameter[":buildtime"]=now();
				//$parameter[':before_balance']=$before_balance;
				//$parameter[':after_balance']=$after_balance;
				//$this->CI->webdb->sqlExc($sqlStr,$parameter);
				//將轉點編號寫入DB
				//$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$rechargeReqId."' where num=".$logID;
				//$this->CI->webdb->sqlExc($upSql);
				return $this->point_checking($rechargeReqId,2,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
				//return NULL;
			}else{
				return "轉點錯誤：".$output->status;	
			}
		}else{
			return $this->point_checking($rechargeReqId,2,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
			//return '系統繁忙中，請稍候再試';	
		}	
	}
	
	//type=1 ;代表 錢包轉入遊戲 需先檢查 錢包餘額
	public function point_checking($rechargeReqId,$type,$u_id,$amount,$mem_num,$gamemaker_num,$logID=NULL){	//點查轉帳情況
		$post_data=array('signature'=>$this->sign($rechargeReqId),'channelId'=>$this->channelId,'rechargeReqId'=>$rechargeReqId);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/rechargestatus");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,30); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $rechargeReqId;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->status==200){
				$makers_balance=$this->get_balance($u_id);
				$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
				if($type==1){
					if((int)$WalletTotal >=(int)$amount){
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
					$parameter[":makers_balance"]=$makers_balance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$parameter[":buildtime"]=now();
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
				}
				//將轉點編號寫入DB
				$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$rechargeReqId."' where num=".$logID;
				$this->CI->webdb->sqlExc($upSql);
				return NULL;
			}elseif($http_code===200 && $output->status=='-1'){
				return '轉點失敗';
			}else{
				return "轉點錯誤：".$output->status;	
			}
		}else{
			return '系統繁忙中，請稍後再試';
		}
	}
	
	
	public function forward_game($u_id){	//登入遊戲
		$accessToken=md5(uniqid(rand(), true));
		$ts=time();
		$data=array('ts'=>$ts,'username'=>strtolower(trim($u_id)),'accessToken'=>$accessToken);
		$post_data=http_build_query($data);
		$expiretime=$ts + 60 ;	//有效時間 一分鐘
		$parameter=array();
		$colSql="username,accessToken,timestamp,expiretime";
		$sqlStr="INSERT INTO `ebet_login_token` (".sqlInsertString($colSql,0).")";
		$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
		$parameter[":username"]=strtolower(trim($u_id));
		$parameter[":accessToken"]=$accessToken;	//轉入遊戲
		$parameter[":timestamp"]=$ts;
		$parameter[":expiretime"]=$expiretime;
		$this->CI->webdb->sqlExc($sqlStr,$parameter);

		return $this->game_url.$post_data;
	}
	
	//報表
	public function reporter_all($sTime,$eTime,$pageNum=1,$pageSize=5000){
		$timestamp=time();
		$post_data=array('username'=>'','startTimeStr'=>$sTime,'endTimeStr'=>$eTime,'channelId'=>$this->channelId,'subChannelId'=>0,
						 'pageNum'=>$pageNum,'pageSize'=>$pageSize,'timestamp'=>$timestamp,'signature'=>$this->sign($timestamp));
						 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/userbethistory");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,30); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->status==200){
				return $output;
			}
		}
				
	}
	
	public function getBetType(){
		$betType[1]=array('60'=>'閒','66'=>'閒對','68'=>'和','80'=>'庒','88'=>'庄對');		//百家樂
		$betType[2]=array('10'=>'龍','11'=>'虎','68'=>'和');	//龍虎
		$betType[3]=array('100'=>'單','101'=>'雙','102'=>'大','103'=>'小',
						  '104'=>'對子:1','105'=>'對子:2','106'=>'對子:3','107'=>'對子:4','108'=>'對子:5','109'=>'對子:6',
						  '110'=>'圍骰:1','111'=>'圍骰:2','112'=>'圍骰:3','113'=>'圍骰:4','114'=>'圍骰:5','115'=>'圍骰:6','116'=>'全圍',
						  '117'=>'4點','118'=>'5點','119'=>'6點','120'=>'7點','121'=>'8點','125'=>'9點','126'=>'10點','127'=>'11點',
						  '128'=>'12點','129'=>'13點','130'=>'14點','131'=>'15點','132'=>'16點','133'=>'17點',
						  '134'=>'單點數','135'=>'單點數:2','136'=>'單點數:3','137'=>'單點數:4','138'=>'單點數:5','139'=>'單點數:6',
						  '140'=>'組合 1-2','141'=>'組合 1-3','142'=>'組合 1-4','143'=>'組合 1-5','144'=>'組合 1-6',
						  '145'=>'組合 2-3','146'=>'組合 2-4','147'=>'組合 2-5','148'=>'組合 2-6',
						  '149'=>'組合 3-4','150'=>'組合 3-5','151'=>'組合 3-6',
						  '152'=>'組合 4-5','153'=>'組合 4-6','154'=>'組合 5-6','155'=>'二同號','156'=>'三同號'
						  );	//骰寶
		$betType[4]=array('200'=>'直接注','201'=>'分注','202'=>'街注','203'=>'角注','204'=>'三數','205'=>'4個號碼',
						  '206'=>'線注','207'=>'列注','208'=>'打注','209'=>'紅','210'=>'黑','211'=>'單','212'=>'雙','213'=>'大','214'=>'小'
						  );	//輪盤
		return $betType;
	}
	
	
	public function getPublicKey(){
		return $this->publicKey;	
	}
	
	public function getPrivateKey(){
		return $this->privateKey;	
	}
	


	//加密
	public function sign($plaintext){
		$this->CI->rsa->loadKey($this->privateKey);
		$this->CI->rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
		$this->CI->rsa->setHash("md5");
		$signature = $this->CI->rsa->sign($plaintext);
		return base64_encode($signature);
	}

	
	
	
}
?>