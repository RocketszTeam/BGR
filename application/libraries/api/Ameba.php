<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//Ameba
require_once(dirname(dirname(__FILE__)).'/jwt/JWT.php');
class Ameba{
	var $CI;
	var $timeout=10;	//curl允許等待秒數
	var $api_url='https://api.fafafa3388.com/ams/api';
	var $report_url='https://api.fafafa3388.com/dms/api';
	var $site_id = "8260";
	var $key='urEXyNnNmmgKYuvlpgvFL3LYJMXgsUYq';
	var $currency='TWD';
	var $lang='zhTW';
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}

	public function create_account($u_id,$mem_num,$gamemaker_num=27){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api		
			$post_data=array('action'=>'create_account','site_id'=>$this->site_id,'account_name'=>trim($u_id),'currency'=>$this->currency);
			$output=$this->curl($post_data);
			//print_r($output);exit;
			if($output){
				if($output->error_code=='OK'){
					$colSql="u_id,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);	
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return '創建失敗:'.$output->error_code.':'.$output->error_msg;
				}
			}else{
				return '系統繁忙中，請稍後再試';	
			}
		}else{
			return '會員已有此類型帳號';
		}
	}

	public function get_balance($u_id){	//餘額查詢
		$post_data=array('action'=>'get_balance','site_id'=>$this->site_id,'account_name'=>trim($u_id));
		$output=$this->curl($post_data);
		//print_r($output);exit;
		if($output){
			if($output->error_code=='OK'){
				return $output->balance;
			}else{
				return '--';
			}
		}else{
			return '--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=27,$logID=NULL){	//轉入點數到遊戲帳號內
		$tx_id=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$tx_id."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('action'=>'deposit','site_id'=>$this->site_id,'account_name'=>trim($u_id),'amount'=>$amount,'tx_id'=>$tx_id);
		$output=$this->curl($post_data);
		//print_r($output);exit;
		if($output){
			if($output->error_code=='OK'){
				$parameter=array();
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				$before_balance=(float)$WalletTotal;//異動前點數
				$after_balance= (float)$before_balance - (float)$amount;//異動後點數
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
			}else{
				return '轉點失敗:'.$output->error_code;
			}
		}else{
			//return '系統繁忙中，請稍後再試';
			return $this->point_checking($tx_id,'deposit',$u_id,$amount,$mem_num,$gamemaker_num,$logID);
		}
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=27,$logID=NULL){	//遊戲點數轉出
		$tx_id=time().mt_rand(1111,9999);
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$tx_id."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('action'=>'withdraw','site_id'=>$this->site_id,'account_name'=>trim($u_id),'amount'=>$amount,'tx_id'=>$tx_id);
		$output=$this->curl($post_data);
		//print_r($output);exit;
		if($output){
			if($output->error_code=='OK'){
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
				$parameter[":makers_balance"]=$output->balance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return '轉點失敗:'.$output->error_code;
			}
		}else{
			//return '系統繁忙中，請稍後再試';
			return $this->point_checking($tx_id,'withdraw',$u_id,$amount,$mem_num,$gamemaker_num,$logID);
		}
	}
	
	//type=deposit ;代表 錢包轉入遊戲 需先檢查 錢包餘額;type=withdraw 代表遊戲轉入錢包
	public function point_checking($tx_id,$type,$u_id,$amount,$mem_num,$gamemaker_num,$logID=NULL){	//點查轉帳情況
		$post_data=array('action'=>'get_transaction','site_id'=>$this->site_id,'type'=>trim($type),'tx_id'=>$tx_id);
		$output=$this->curl($post_data);
		if($output){
			if($output->error_code=='OK'){
				if($output->state=='completed'){
					$makers_balance=$this->get_balance($u_id);
					$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
					if($type=='deposit'){	//檢查錢包點數
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
					$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$tx_id."' where num=".$logID;
					$this->CI->webdb->sqlExc($upSql);
					return NULL;
				}else{	//當單據狀態不是已完成 再次呼叫確認
					return $this->point_checking($tx_id,$type,$u_id,$amount,$mem_num,$gamemaker_num,$logID);
				}
			}else{
				return '查詢轉點失敗:'.$output->error_code;
			}
		}else{
			return '系統繁忙中，請稍後再試';
		}
	}
	
	public function forward_game($u_id,$game_id=NULL){	//登入遊戲
		$post_data=array('action'=>'register_token','site_id'=>$this->site_id,'account_name'=>trim($u_id),'game_id'=>$game_id,'lang'=>$this->lang);
		$output=$this->curl($post_data);
		//print_r($output);exit;
		if($output){
			if($output->error_code=='OK'){
				return $output->game_url;
			}	
		}
	}
	
	public function fun_game($game_id){
		$post_data=array('action'=>'request_demo_play','site_id'=>$this->site_id,'account_name'=>'GUEST'.time(),'game_id'=>$game_id,'lang'=>$this->lang,'currency'=>$this->currency);
		$output=$this->curl($post_data);
		//print_r($output);exit;
		if($output){
			if($output->error_code=='OK'){
				return $output->game_url;
			}	
		}
	}
	
	//報表
	public function reporter_all($sTime,$eTime){
		$post_data=array('action'=>'get_bet_histories','site_id'=>$this->site_id,'from_time'=>$sTime,'to_time'=>$eTime);
        $headers = [];
        $headers[] = "Authorization: Bearer ".$this->getAuthorizationHeaders($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->report_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,180);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = json_decode(curl_exec($ch));
        $curl_error = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r($output);exit;
		if(!$curl_error){
			if($http_code==200 && $output->error_code=='OK'){
				return $output->bet_histories;
			}
		}
	}
	

    public function curl($post_data){
        $headers = [];
        $headers[] = "Authorization: Bearer ".$this->getAuthorizationHeaders($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = json_decode(curl_exec($ch));
        $curl_error = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code==200 && !$curl_error){
        	return $output;
		}
    }
	
	
    public function JWT_encode($data){
        $Id_token = new JOSE_JWT($data);
        $sign_string = $Id_token->sign($this->key);
        $encode_data = $Id_token -> toString();
        return $encode_data.base64_encode($sign_string->signature);
    }

    private function getAuthorizationHeaders($data) {
        $hash = $this->JWT_encode($data);
        return $hash;
    }
	
	
}
?>