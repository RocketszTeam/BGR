<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Qtechapi {
	var $CI;
	var $timeout=15;	//curl允許等待秒數
	var $Checkkey='O3IsEOE9O8R8G1iS';	//自行設定
	var $api_url='https://api.qtplatform.com';	//API URL
	var	$Username='api_91d';	//取得token帳號
	var	$Password='1xpTOtwv';	//取得token帳號
	var	$token;	//token
	
	public function __construct(){	
		$this->CI =&get_instance();
		
	}
	
	private function getToken(){
		$data=array('grant_type'=>'password','response_type'=>'token','username'=>$this->Username,'password'=>$this->Password);
		$post_data=http_build_query($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/auth/token?".$post_data);
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		//$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200){
				return $output->access_token;		
			}
		}
		
	}
	
	
	public function create_account($u_id,$mem_num,$gamemaker_num=11){	//創建遊戲帳號
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api
			$token=$this->getToken();
			$data=array('playerId'=>trim($u_id),'currency'=>'TWD','country'=>'TW','lang'=>'zh_TW','mode'=>'real','device'=>'desktop');
			$post_data=json_encode($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/games/QS-volcanoriches/launch-url");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
			$headers=array('Content-Type: application/json','Content-Length:'.strlen($post_data),'Authorization:Bearer '.$token);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
			$output = json_decode(curl_exec($ch));
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errno = curl_errno($ch);
			curl_close($ch);	
			//print_r($output);
			if(!$curl_errno){
				if($http_code===200){
					$colSql="u_id,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);	
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return '會員創建失敗：'.$http_code;
				}
			}else{
				return '系統繁忙中，請稍後再試'	;
			}
		}else{
			return '會員已有此類型帳號';
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$token=$this->getToken();
		//$data=array('playerId'=>trim($u_id));
		//$post_data=json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/wallet/ext/".trim($u_id));
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$headers=array('Content-Type: application/json','Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200){
				return $output->amount;
			}else{
				return '--'	;	
			}
		}else{
			return '--'	;	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=11,$logID=NULL){	//轉入點數到遊戲帳號內
		$token=$this->getToken();
		$referenceId=time().mt_rand();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$referenceId."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		$data=array('type'=>'CREDIT','playerId'=>trim($u_id),'amount'=>$amount,'currency'=>'TWD','referenceId'=>$referenceId);
		$post_data=json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/fund-transfers");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$headers=array('Content-Type: application/json','Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===201){
				return $this->Complete_Transfer($output->id,$mem_num,$gamemaker_num);
			}else{
				return "轉點錯誤";
			}
		}else{
			return '系統繁忙中，請稍後再試'	;
		}
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=11,$logID=NULL){	//遊戲點數轉出
		$token=$this->getToken();
		$referenceId=time().mt_rand();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$referenceId."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$data=array('type'=>'DEBIT','playerId'=>trim($u_id),'amount'=>$amount,'currency'=>'TWD','referenceId'=>$referenceId);
		$post_data=json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/fund-transfers");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$headers=array('Content-Type: application/json','Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//print_r($output);
		if(!$curl_errno){
			if($http_code===201){
				return $this->Complete_Transfer($output->id,$mem_num,$gamemaker_num);
			}else{
				return "轉點錯誤";
			}
		}else{
			return '系統繁忙中，請稍後再試'	;
		}
	}
	
	private function Complete_Transfer($id,$mem_num,$gamemaker_num){	//完成轉帳
		$token=$this->getToken();
		$data=array('status'=>'COMPLETED');
		$post_data=json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/fund-transfers/".$id."/status");
		//curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$headers=array('Content-Type: application/json','Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200){
				$Balance=$this->get_balance($output->playerId);	//取得遊戲餘額<br>
				if($output->type=='CREDIT'){	//轉入遊戲
					$WalletTotal=getWalletTotal($mem_num);	//會員餘額
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance - (float)round($output->amount,0);//異動後點數
					$parameter=array();
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=3;	//轉入遊戲
					$parameter[":points"]="-".round($output->amount,0);
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=(float)$Balance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}else{	//遊戲轉出
					$WalletTotal=getWalletTotal($mem_num);	//會員餘額
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance + (float)round($output->amount,0);//異動後點數
					$parameter=array();
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=4;	//遊戲轉出
					$parameter[":points"]=round($output->amount,0);
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=(float)$Balance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
				
			}else{
				//echo $http_code;
				return "確認轉點錯誤";
			}
		}else{
			//echo $curl_errno;
			return '系統繁忙中，請稍後再試'	;
		}
	}
	
	
	//取得遊戲token
	public function forward_game($u_id,$GameCode='OGS-1can2can'){		//試玩mode=demo
		$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$HostUrl.=$_SERVER['HTTP_HOST'];
		$token=$this->getToken();
		$data=array('playerId'=>trim($u_id),'currency'=>'TWD','country'=>'TW','lang'=>'zh_TW','mode'=>'real',
					'device'=>($this->CI->agent->is_mobile() ? 'mobile' : 'desktop'),
					'returnUrl'=> ($this->CI->agent->is_mobile() ? $HostUrl : ''));
		$post_data=json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/games/".$GameCode."/launch-url");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$headers=array('Content-Type: application/json','Content-Length:'.strlen($post_data),'Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200){
				return $output->url;
			}else{
				return '--'	;	
			}
		}else{
			return '--'	;	
		}
	}
	//試玩
	public function fun_game($GameCode='OGS-1can2can'){		//試玩mode=demo;真錢=real
		$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$HostUrl.=$_SERVER['HTTP_HOST'];
		$token=$this->getToken();
		$data=array('currency'=>'TWD','country'=>'TW','lang'=>'zh_TW','mode'=>'demo',
					'device'=>($this->CI->agent->is_mobile() ? 'mobile' : 'desktop'),
					'returnUrl'=> ($this->CI->agent->is_mobile() ? $HostUrl : ''));
		$post_data=json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/games/".$GameCode."/launch-url");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$headers=array('Content-Type: application/json','Content-Length:'.strlen($post_data),'Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//echo $curl_errno;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200){
				return $output->url;
			}else{
				return '--'	;	
			}
		}else{
			return '--'	;	
		}
	}
	
	//報表
	public function reporter_all($sTime,$eTime,$Page=0,$Row=100){
		$token=$this->getToken();
		$data=array('from'=>$sTime,'to'=>$eTime,'size'=>$Row,'page'=>$Page);
		$post_data=http_build_query($data);
		//echo $post_data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/game-rounds?".$post_data);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,60);
		$headers=array('Content-Type: application/json','Time-Zone:Asia/Taipei','Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200){
				return $output;			
			}else{
				return NULL;	
			}
		}else{
			return NULL;	
		}
	}
	
	
	
}

?>