<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dreamgame {

	var $timeout=DG_timeout;	//curl允許等待秒數
	var $api_url=DG_api_url;
	var $agentName=DG_agentName;	//代理账号
	var $key=DG_key;	//key
	var $currencyName=DG_currencyName;
	var $iden_suffix=DG_iden_suffix;	//手机APP登入后缀
	var $token;

	
	public function __construct(){

		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
		$this->token=md5($this->agentName.$this->key);
	}
	
	public function index(){
		$u_id='mytestplayer';
		$u_password='mytestplayer';
		//$this->create_account($u_id,$u_password,999);	
		//$this->get_balance($u_id);
		//$this->deposit($u_id,10000,999);
		//$this->withdrawal($u_id,200,999);//
		$this->forward_game($u_id,$u_password);
		//$this->updateLimit($u_id);
		//$this->reporter_all();
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=12){

		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//如果帳號不存在則把帳號寫入資料庫
			$member=array('username'=>trim($u_id),'password'=>trim(md5($u_password)),'currencyName'=>$this->currencyName,'winLimit'=>200000);
			$post_data=array('token'=>$this->token,'data'=>'D','member'=>$member);
			$post_data=json_encode($post_data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/signup/".$this->agentName);		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
			$output=json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);	
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			//print_r($output);
			if(!$curl_errno){
				if($http_code===200 && $output->codeId==0){
					
						$colSql="u_id,u_password,mem_num,gamemaker_num";
						$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
						$parameter=array();
						$parameter[':u_id']=trim($u_id);
						$parameter[':u_password']=md5($u_password);	//密碼加密
						$parameter[':mem_num']=$mem_num;
						$parameter[':gamemaker_num']=$gamemaker_num;
						$this->CI->webdb->sqlExc($upSql,$parameter);
						return NULL;
				}else{
					return 	$output->codeId;
				}
			}else{
				return '系統繁忙中，請稍候再試';	
			}
		}else{
			return '會員已有此類型帳號';	
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$member=array('username'=>trim($u_id));
		$post_data=array('token'=>$this->token,'member'=>$member);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/getBalance/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				return $output->member->balance;
			}else{
				return 	'--';
			}
		}else{
			return '--';	
		}
	}
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=12,$logID=NULL){	//轉入點數到遊戲帳號內
		$member=array('username'=>trim($u_id),'amount'=>$amount);
		$SN=time().mt_rand();
		
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$SN."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('token'=>$this->token,'data'=>$SN,'member'=>$member);
		$post_data=json_encode($post_data);
		//echo $post_data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/account/transfer/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				$makers_balance=($this->get_balance($u_id)!='--' ? $this->get_balance($u_id) : 0);
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
				return '轉點錯誤：'.$output->codeId;
			}
		}else{
			return $this->point_checking($SN,1,$u_id,$amount,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}
	
	}


	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=12,$logID=NULL){	//轉入點數到遊戲帳號內
		$member=array('username'=>trim($u_id),'amount'=>"-".$amount);
		$SN=time().mt_rand();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$SN."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);
		
		$post_data=array('token'=>$this->token,'data'=>$SN,'member'=>$member);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/account/transfer/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,6);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				$makers_balance=($this->get_balance($u_id)!='--' ? $this->get_balance($u_id) : 0);
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
				return '轉點錯誤：'.$output->codeId;
			}
		}else{
			return $this->point_checking($SN,2,$u_id,$amount,$mem_num,$gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}
	}
	
	public function point_checking($SN,$type,$u_id,$amount,$mem_num,$gamemaker_num){	//點查轉帳情況
		$SN=time().mt_rand();
		$post_data=array('token'=>$this->token,'data'=>$SN);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/account/checkTransfer/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,6);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
				if($type==1){
					if((int)$WalletTotal >=(int)$amount){
						$parameter=array();
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
						$parameter[":makers_balance"]=$this->get_balance($u_id);
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
					$before_balance=(float)$WalletTotal;//異動前點數
					$after_balance= (float)$before_balance + (float)$amount;//異動後點數
					$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"]=$mem_num;
					$parameter[":kind"]=4;	//遊戲轉出
					$parameter[":points"]=$amount;
					$parameter[":makers_num"]=$gamemaker_num;
					$parameter[":makers_balance"]=$this->get_balance($u_id);
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
			}else{
				return '查詢轉點錯誤：'.$output->codeId;
			}
		}else{
			return '系統繁忙中，請稍候再試';
		}
	
	}
	
	
	
	/*

 会员登入


名称	说明	示例
path	接口地址	
 host/user/login/{agentName}/ 
method	请求方法	
 POST 
header	请求头信息	
 Content-Type : application/json 
RequestBody	请求参数	
{
    "token":KEY,
    "lang":"en",
    "member":{
        "username":"会员账号",
        "password":"会员密码"//可以不传,如果密码不同,将自动修改DG数据库保存的密码
    }
} 
ResponseBody	接口返回数据	
{
    "codeId":CODE,
    "token":"登入游戏的Token",
    "list":["flash 登入地址","wap 登入地址","直接打开APP地址"]
} 

*/

	public function forward_game($u_id,$u_password){	//登入遊戲
		//$this->update($u_id);
		$this->updateLimit($u_id);
		$member=array('username'=>trim($u_id),'password'=>trim(md5($u_password)));
		$post_data=array('token'=>$this->token,'lang'=>'tw','member'=>$member);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/login/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				if(!$this->CI->agent->is_mobile()){		//電腦版 回傳flash登入網址
					return $output->list[0].$output->token;
					//exit;
				}else{	//手機板回傳H5登入網址
					return $output->list[1].$output->token;
					//exit;
				}
			}else{
				$output->codeId;
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}
/*
2.10 标记已抓取注单





名称	说明	示例
path	接口地址	
 host/game/markReport/{agentName} 
method	请求方法	
 POST 
header	请求头信息	
 Content-Type : application/json 
RequestBody	请求参数	
{
    "token":"KEY",
    "list":[待标记注单id集合]
} 
ResponseBody	接口返回数据	
{
    "codeId":CODE,
    "token":"KEY"
} 
remark	接口备注	
 



*/

	public function markReport($list){	//標記注單
		$member=array('token'=>$this->token,'list'=>$list);
		$post_data=json_encode($member);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/game/markReport/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //注單 list
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,30);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
					return $output;
			}else{
				$output->codeId;
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}

	/*

 会员登入


名称	说明	示例
path	接口地址	
 host/user/login/{agentName}/ 
method	请求方法	
 POST 
header	请求头信息	
 Content-Type : application/json 
RequestBody	请求参数	
{
    "token":KEY,
    "lang":"en",
    "member":{
        "username":"会员账号",
        "password":"会员密码"//可以不传,如果密码不同,将自动修改DG数据库保存的密码
    }
} 
ResponseBody	接口返回数据	
{
    "codeId":CODE,
    "token":"登入游戏的Token",
    "list":["flash 登入地址","wap 登入地址","直接打开APP地址"]
} 

*/


public function getapp($u_id,$u_password){	//登入遊戲
		$member=array('username'=>trim($u_id),'password'=>trim(md5($u_password)));
		$post_data=array('token'=>$this->token,'lang'=>'tw','member'=>$member);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/login/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
					return $output->list[2].$output->token;

			}else{
				$output->codeId;
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}

	public function updateLimit($u_id){	//修改限紅
		$member=array('username'=>trim($u_id));
		$post_data=array('token'=>$this->token,'data'=>'E','member'=>$member);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/game/updateLimit/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				//return $output->member->balance;
			}else{
				return '限紅修改失敗';
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}
	
	public function reporter_all(){	//群撈報表
		$post_data=array('token'=>$this->token);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/game/getReport/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: '. strlen($post_data) );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,60);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				return $output->list;
			}
		}
	}
	
	
	//補撈報表(這只是用來漏單補單，注意:日期年月日要一樣)
	public function makeup_reporter_all($beginTime,$endTime){	
		$post_data=array('token'=>$this->token,'beginTime'=>$beginTime,'endTime'=>$endTime,'agentName'=>$this->agentName);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://report.dg99.info/game/getReport");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: '. strlen($post_data) );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,60);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				return $output->data->records;
			}
		}
	}
	

/*

修改会员限红组


名称	说明	示例
path	接口地址	
 host/game/updateLimit/{agentName} 
method	请求方法	
 POST 
header	请求头信息	
 Content-Type : application/json 
RequestBody	请求参数	
{
    "token":"KEY",
    "data":"目标限红组",
    "member":{"username":"DG66777"}
} 
ResponseBody	接口返回数据	
{
    "token":"KEY",
    "codeId":CODE
} 

*/



	public function update($u_id){

		$member=array('username'=>trim($u_id));
		$post_data=array('token'=>$this->token,'data'=>'D','member'=>$member);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/game/updateLimit/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);



		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		//exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code=='999'){
				return NULL;
			}else{
				return '限紅設定失敗！';
				//return $output->code.':'.$output->msg;	
			}
		}else{
			return '系統繁忙中，請稍後再試';
		}
	}


/*

2.11 重新缓存注单


名称	说明	示例
path	接口地址	
 host/game/initTickets/{agentName} 
method	请求方法	
 POST 
header	请求头信息	
 Content-Type : application/json 
RequestBody	请求参数	
{
    "token":"KEY",
    "list":["起始日期", "结束日期"],
    "data":需要重新缓存的注单的ID
} 
ResponseBody	接口返回数据	
{
    "codeId":CODE,
    "token":"KEY"
} 
remark	接口备注	
开始时间与结束时间应限定在同一天之内 
日期格式：yyyy-MM-dd HH:mm:ss 
请求间隔 1 分钟
data与list有一个条件就行,两个都传处理data


*/
	public function initTicketsbydata($data){	//重新緩衝
		//$listdate = '["'.$start_date.'","'.$end_date.'"]';
		//$listdate =  '["'.$start_date.'","'.$end_date.'"]' ;//array($start_date,$end_date);
		$listdate=array($data);
		//echo $listdate;
		$post_data=array('token'=>$this->token,'data'=>$listdate);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/game/initTickets/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				//return $output->member->balance;
				return 'ok';
			}else{
				return '重新緩沖失敗'.$http_code.' $output->codeId:'.$output->codeId;
			}
		}else{
			return '系統繁忙中，請稍候再試'.$curl_errno;	
		}
	}

	public function initTickets($start_date,$end_date){	//重新緩衝
		//$listdate = '["'.$start_date.'","'.$end_date.'"]';
		//$listdate =  '["'.$start_date.'","'.$end_date.'"]' ;//array($start_date,$end_date);
		$listdate=array($start_date,$end_date);
		//echo $listdate;
		$post_data=array('token'=>$this->token,'list'=>$listdate);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/game/initTickets/".$this->agentName);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,10);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				//return $output->member->balance;
				return 'ok';
			}else{
				echo '<pre>output';
				print_r($output);
				echo '</pre>';
				echo '重新緩沖失敗'.$http_code.' $output->codeId:'.$output->codeId;
			}
		}else{
			return '系統繁忙中，請稍候再試'.$curl_errno;	
		}
	}
	
public function getBetType(){
		//定義百家樂投注類型
		$betType["bac"][0]='合';
		$betType["bac"][1]='閒';
		$betType["bac"][2]='莊';
		$betType["bac"][3]='閒對';
		$betType["bac"][4]='莊對';
		$betType["bac"][5]='閒單點';
		$betType["bac"][6]='莊單點';
		$betType["bac"][7]='總單點';
		$betType["bac"][8]='閒雙點';
		$betType["bac"][9]='莊雙點';
		$betType["bac"][10]='總雙點';
		$betType["bac"][11]='閒點小';
		$betType["bac"][12]='莊點小';
		$betType["bac"][13]='總點小';
		$betType["bac"][14]='閒點大';
		$betType["bac"][15]='莊點大';
		$betType["bac"][16]='總點大';
		
		//骰寶
		$betType["sicbo"][0]='小';
		$betType["sicbo"][1]='大';
		$betType["sicbo"][2]='單';
		$betType["sicbo"][3]='雙';
		
		//龍虎
		$betType["dtx"][0]='合';
		$betType["dtx"][1]='龍';
		$betType["dtx"][2]='虎';
		
		return $betType;
	}
function stdToArray($obj){
  $reaged = (array)$obj;
  foreach($reaged as $key => &$field){
    if(is_object($field))$field = stdToArray($field);
  }
  return $reaged;
}	
	
}
