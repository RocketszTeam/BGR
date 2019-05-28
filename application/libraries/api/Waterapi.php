<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//Ebet
class Waterapi{
	var $api_url='http://111.oy33.net/api/system/';
	var $apikey='1a2e2e5a95384327b4f8c40abfb1601b';
	var $CI;
	var $timeout=10;	//curl允許等待秒數
	
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function login($u_id,$u_password){
		$post_data=array('act'=>'login','apikey'=>$this->apikey,'user'=>trim($u_id),'pwd'=>trim($u_password),
						 'lang'=>'zh-tw','ip'=>$this->CI->input->ip_address());
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code==104){	//帳號創建成功
				return array('uid'=>$output->data->uid,'cid'=>$output->data->cid);
			}else{
				return NULL;
			}
		}else{
			return NULL;
		}
	}
	
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=25){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api		
			$u_password='P'.$this->rnd_code(8);
			$post_data=array('act'=>'adduser','apikey'=>$this->apikey,'user'=>trim($u_id),'pwd'=>trim($u_password),
							 'lang'=>'zh-tw','ip'=>$this->CI->input->ip_address());	 
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url);		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->code==802){	//帳號創建成功
					$colSql="u_id,u_password,mem_num,cid,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);	
					$parameter[':u_password']=$this->CI->encryption->encrypt($u_password);	//密碼加密
					$parameter[':mem_num']=$mem_num;
					$parameter[':cid']=$output->data;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return '創建錯誤：'.$output->code;	
				}
			}else{
				return '系統繁忙中，請稍後再試'	;
			}
		}else{
			return '會員已有此類型帳號';
		}
		
	}
	
	public function get_balance($u_id,$u_password){	//餘額查詢
		$cid=$this->login($u_id,$u_password);
		if($cid!=NULL){
			$post_data=array('act'=>'getbalance','apikey'=>$this->apikey,'cid'=>trim($cid["cid"]),'ip'=>$this->CI->input->ip_address());
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url);		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->code==200){
					return $output->data;
				}else{
					return '--';	
				}
			}else{
				return '--';	
			}
			
		}else{
			return '--';
		}
	}
	
	public function deposit($u_id,$u_password,$amount,$mem_num,$gamemaker_num=25,$logID=NULL){	//轉入點數到遊戲帳號內
		$cid=$this->login($u_id,$u_password);
		if($cid!=NULL){
			$post_data=array('act'=>'chgpoint','apikey'=>$this->apikey,'cid'=>trim($cid["cid"]),'point'=>trim($amount),'ip'=>$this->CI->input->ip_address());
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url);		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,30); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->code==302){
					$parameter=array();
					$WalletTotal=getWalletTotal($mem_num);	//會員餘額
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance - (float)$amount;//異動後點數
					$makers_balance=$this->get_balance($u_id,$u_password);
					
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
					
					//將轉點編號寫入DB
					//$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$rechargeReqId."' where num=".$logID;
					//$this->CI->webdb->sqlExc($upSql);
					
					return NULL;
				}else{
					return "轉點錯誤：".$output->code;	
				}
			}else{
				//return $this->point_checking($rechargeReqId,1,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
				return '系統繁忙中，請稍候再試';	
			}	
		}else{
			return '遊戲登入失敗';
		}
	}
	
	public function withdrawal($u_id,$u_password,$amount,$mem_num,$gamemaker_num=25,$logID=NULL){	//遊戲點數轉出
		$cid=$this->login($u_id,$u_password);
		if($cid!=NULL){
			$post_data=array('act'=>'chgpoint','apikey'=>$this->apikey,'cid'=>trim($cid["cid"]),'point'=>trim("-".$amount),'ip'=>$this->CI->input->ip_address());
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url);		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,30); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->code==302){
					$parameter=array();
					$WalletTotal=getWalletTotal($mem_num);	//會員餘額
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance + (float)$amount;//異動後點數
					$makers_balance=$this->get_balance($u_id,$u_password);
					
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
					//將轉點編號寫入DB
					//$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$rechargeReqId."' where num=".$logID;
					//$this->CI->webdb->sqlExc($upSql);
					//return $this->point_checking($rechargeReqId,2,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
					return NULL;
				}elseif($http_code===200 && $output->code==307){
					return "遊戲點數不足";	
				}else{
					return "轉點錯誤：".$output->code;	
				}
			}else{
				//return $this->point_checking($rechargeReqId,2,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
				return '系統繁忙中，請稍候再試';	
			}	
		}else{
			return '遊戲登入失敗';
		}
	}
	
	public function forward_game($u_id,$u_password){	//登入遊戲
		$uid=$this->login($u_id,$u_password);
		if($uid!=NULL){
			$post_data=array('act'=>'openlobby','apikey'=>$this->apikey,'uid'=>trim($uid["uid"]));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url);		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,30); 
			$output = json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//print_r($output);exit;
			if(!$curl_errno){
				if($http_code===200 && $output->code==200){
					return $output->data;
				}else{
					return $output->code;	
				}
			}else{
				return '系統繁忙中，請稍候再試';	
			}
		}else{
			return '遊戲登入失敗';	
		}
	}
	
	//報表
	public function reporter_all($sTime,$eTime){
		$post_data=array('act'=>'gamereport','apikey'=>$this->apikey,'startdate'=>trim($sTime),'enddate'=>trim($eTime),'username'=>'');
						 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,180); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code==302){	
				return $output->data;
			}
		}
	}
	
	private function rnd_code($max){  //亂數產生器 $max=亂數長度    
		$new_rnd="";
		$str = "abcdefghijkmnpqrstuvwxyz1234567890";
		$l = strlen($str); //取得字串長度	
		mt_srand((double)microtime()*1000000);  // 設定亂數種子
		for($i=0; $i<$max; $i++){
		   $num=rand(0,$l-1);
		   $new_rnd.= $str[$num];
		}
		return $new_rnd;
	}


	
	
	
}
?>