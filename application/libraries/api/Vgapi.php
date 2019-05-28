<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vgapi{
	var $CI;
	var $timeout=15;	//curl允許等待秒數
	var $channel='RSG';
	var $channelPassword = '12E1o#46Ue*hjiI90';
	var $agent='VG10';
	var $api_url='http://api.osachina.com:7000/webapi/interface.aspx';//http://223.27.38.244:7000/webapi/interface.aspx
	var $gamerecordid_api_url='http://223.27.38.244:7000/webapi/gamerecordid.aspx';
	var $trygame_api_url = 'http://223.27.38.244:7000/webapi/trygame.aspx';
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");		
	}
	
	
	
	
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=38){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		$string = $u_id."create".$this->channel.$this->agent.$this->channelPassword;
		//$string = "e123createRSGVG312E1o#46Ue*hjiI90";
		//$string = "e123create12E1o#46Ue*hjiI90";
		$md5encrypt_upper = strtoupper(md5($string));
		
		if($row==NULL){	//無帳號才呼叫api
			//$post_data=array('BossID'=>$this->BossID,'MemberAccount'=>trim($u_id),'MemberPassword'=>$u_password);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."?username=".$u_id."&action=create&channel=".$this->channel."&agent=".$this->agent."&verifyCode=".$md5encrypt_upper);	
			//curl_setopt($ch, CURLOPT_POST, 1);//CURLOPT_POST 為1或true 表示用post方式輸出
			//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = simplexml_load_string(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//return $http_code;
			//return $output;
			//return $curl_errno;
			
			if(!$curl_errno){
				if($http_code===200 && $output->errtext=="success" && $output->errcode=="0"){	//帳號創建成功
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
					return 	$output->errtext.':'.$output->errcode;
				}
			}else{
				return '系統繁忙中，請稍候再試';
			}
			
		}else{
			return '會員已有此類型帳號';	
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		
		$string = strtoupper($u_id)."balance".$this->channel.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."?username=".strtoupper($u_id)."&action=balance&channel=".$this->channel."&verifyCode=".$md5encrypt_upper);	
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		//return $output;
		//var_dump($output);
		
		if(!$curl_errno){
			if($http_code===200 && $output->errcode=="0" && $output->errtext =="success"){	//帳號創建成功
				//返回遊戲幣 ，除100後為實際幣值
				return ((int)$output->coins)/100;
				
			}else{
				return '--';
			}
		}else{
			return '--';
		}
		
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=38){	//轉入點數到遊戲帳號內 ， 遊戲幣 = 實際金額 *100 
		$TradeNo=time().mt_rand(1111,9999);
		$string = strtoupper($u_id)."deposit".$TradeNo.$amount.$this->channel.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		
		//$post_data=array('MemberAccount'=>trim($u_id),'Balance'=>trim($amount),'TradeNo'=>$TradeNo);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."?username=".strtoupper($u_id)."&action=deposit&serial=".$TradeNo."&amount=".$amount."&channel=".$this->channel."&verifyCode=".$md5encrypt_upper);	
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//var_dump($output);
		
		if(!$curl_errno){
			if($http_code===200 && $output->errcode=="0" && $output->errtext=="success"){	//帳號創建成功
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
				$parameter[":makers_num"]=$gamemaker_num;//遊戲號碼
				$parameter[":makers_balance"]=$output->result;//遊戲剩多少錢
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				//return $output;
				return null;
			}else{
				return 	$output->errcode.':'.$output->errtext;
			}
		}else{
			return $this->point_checking($TradeNo,1,$u_id,$amount,$mem_num,$gamemaker_num);
		}
		
		
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=38){	//遊戲轉出到錢包
		$TradeNo=time().mt_rand(1111,9999);
		$string = strtoupper($u_id)."withdraw".$TradeNo.$amount.$this->channel.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		//$post_data=array('MemberAccount'=>trim($u_id),'Balance'=>trim("-".$amount),'TradeNo'=>$TradeNo);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."?username=".strtoupper($u_id)."&action=withdraw&serial=".$TradeNo."&amount=".$amount."&channel=".$this->channel."&verifyCode=".$md5encrypt_upper);	
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		//return $output;
		
		if(!$curl_errno){
			if($http_code===200 && $output->errcode=="0" && $output->errtext=='success'){	//帳號創建成功
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
				$parameter[":makers_balance"]=$output->result;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return 	$output->errcode.':'.$output->errtext;
			}
		}else{
			return $this->point_checking($TradeNo,2,$u_id,$amount,$mem_num,$gamemaker_num);
		}
		
	
	}
	
	public function point_checking($TradeNo,$type,$u_id,$amount,$mem_num,$gamemaker_num){	//點查轉帳情況
		//$post_data=array('MemberAccount'=>trim($u_id),'Balance'=>trim($amount),'TradeNo'=>$TradeNo);
		//$TradeNo=time().mt_rand(1111,9999);
		$string = strtoupper($u_id)."transRecord".$this->channel.$TradeNo.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."?username=".strtoupper($u_id)."&action=transRecord&channel=".$this->channel."&serial=".$TradeNo."&verifyCode=".$md5encrypt_upper);	
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->errcode=="0" && $output->errtext=="success"){	//帳號創建成功
				$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
				if($type==1){
					if((int)$WalletTotal >=(int)$amount){ //若轉到遊戲的錢大於錢包的錢，則顯示錢包點數不足
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
						$parameter[":makers_balance"]=$output->result;
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
					$parameter[":makers_balance"]=$output->result;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
			}else{
				return $output->errcode.':'.$output->errtext;
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	}
	
	
	public function forward_game($u_id,$gameType){	//登入遊戲大廳 1=斗地主 2=麻将 3=牛牛 4=百人牛牛 7=楚汉德州 8=推筒子  1000=游戏大厅
		//$gameType=1000;
		$gameversion=1;
		$string = strtoupper($u_id)."loginWithChannel".$this->channel.$gameType.$gameversion.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."?username=".strtoupper($u_id)."&action=loginWithChannel&channel=".$this->channel."&gameType=".$gameType."&gameversion=".$gameversion."&verifyCode=".$md5encrypt_upper);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if(!$curl_errno){
			
			if(strpos($output,"&")){ //若 回復回來的 result有& ，要置換   //$gameType ==1 || $gameType ==2
				
				//為避免simplexml_load_string對於"&"有錯誤訊息，所以先置換"&"為"@"
				//$aaa = strpos($output,"&"); //在xml字串若有"&"
				//if($aaa){
					$aaab = str_replace("&","@",$output); //xml字串若有"&"，先置換為"@"
					$aaac = simplexml_load_string($aaab);
					$aaad = str_replace("@","&",$aaac->result);//變成物件後 再把"@"置換回"&"
				//}
			
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					return $aaad;
				}else{
					return 	$aaac->errcode.':'.$aaac->errtext;
				}
			}else{  //若遊戲回復回來的 result 沒有& ，所以不用置換  if($gameType ==3 || $gameType ==4 || $gameType ==7 || $gameType==8 )
				$aaac = simplexml_load_string($output);
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					
					return $aaac->result;
				}else{
					return $aaac->errcode.':'.$aaac->errtext;
				}
			}
			
		}else{
			return '系統繁忙中，請稍候再試';
		}
		
	}
	
	public function mobile_forward_game($u_id,$gameType){	//登入遊戲大廳 gameType = 1000
		//$gameType=1000;
		$gameversion=2;
		$string = strtoupper($u_id)."loginWithChannel".$this->channel.$gameType.$gameversion.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."?username=".strtoupper($u_id)."&action=loginWithChannel&channel=".$this->channel."&gameType=".$gameType."&gameversion=".$gameversion."&verifyCode=".$md5encrypt_upper);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if(!$curl_errno){
			
			if(strpos($output,"&")){ //若 回復回來的 result有& ，要置換   //$gameType ==1 || $gameType ==2
				
				//為避免simplexml_load_string對於"&"有錯誤訊息，所以先置換"&"為"@"
				//$aaa = strpos($output,"&"); //在xml字串若有"&"
				//if($aaa){
					$aaab = str_replace("&","@",$output); //xml字串若有"&"，先置換為"@"
					$aaac = simplexml_load_string($aaab);
					$aaad = str_replace("@","&",$aaac->result);//變成物件後 再把"@"置換回"&"
				//}
			
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					return $aaad;
				}else{
					return 	$aaac->errcode.':'.$aaac->errtext;
				}
			}else{  //若遊戲回復回來的 result 沒有& ，所以不用置換  if($gameType ==3 || $gameType ==4 || $gameType ==7 || $gameType==8 )
				$aaac = simplexml_load_string($output);
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					
					return $aaac->result;
				}else{
					return $aaac->errcode.':'.$aaac->errtext;
				}
			}
			
		}else{
			return '系統繁忙中，請稍候再試';
		}
		
	}
	
	
	//報表
	public function reporter_all($id){ //$sTime="",$eTime="",$Page=""
		//$post_data=array('StartTime'=>trim($sTime),'EndTime'=>$eTime,'Page'=>$Page,'BossID'=>$this->BossID);
		$string = $this->channel.$this->agent.$id.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->gamerecordid_api_url."?channel=".$this->channel."&agent=".$this->agent."&id=".$id."&verifyCode=".$md5encrypt_upper);	
					     //http://ew68.cn/webapi/gamerecordid.aspx?channel=RSG&id=0&verifyCode=300F441062EB3E2A8DE659A9E8AA9EC1
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 ){
				return $output;
			}
		}
	}
	
	
	
	public function fun_game($gametype){  //免費試玩
		//若試玩位斗地主或是搶莊牛牛則
		if($gametype == 1 || $gametype == 3){
				$gameversion = 2;
		}else{
			$gameversion = 1;
		}
		$string = $this->channel.$gametype.$gameversion.$this->channelPassword;
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->trygame_api_url."?channel=".$this->channel."&gametype=".$gametype."&gameversion=".$gameversion."&verifyCode=".$md5encrypt_upper);	
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
		if(!$curl_errno){
			
			if(strpos($output,"&")){ //若 回復回來的 result有& ，要置換   //$gameType ==1 || $gameType ==2
				
				//為避免simplexml_load_string對於"&"有錯誤訊息，所以先置換"&"為"@"
				//$aaa = strpos($output,"&"); //在xml字串若有"&"
				//if($aaa){
					$aaab = str_replace("&","@",$output); //xml字串若有"&"，先置換為"@"
					$aaac = simplexml_load_string($aaab);
					$aaad = str_replace("@","&",$aaac->result);//變成物件後 再把"@"置換回"&"
				//}
			
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					return $aaad;
				}else{
					return 	$aaac->errcode.':'.$aaac->errtext;
				}
			}else{  //若遊戲回復回來的 result 沒有& ，所以不用置換  if($gameType ==3 || $gameType ==4 || $gameType ==7 || $gameType==8 )
				$aaac = simplexml_load_string($output);
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					
					return $aaac->result;
				}else{
					return $aaac->errcode.':'.$aaac->errtext;
				}
			}
			
		}else{
			return '系統繁忙中，請稍候再試';
		}
		
		
	}
	
	
	
}
?>