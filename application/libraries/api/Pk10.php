<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pk10 {
	var $timeout=10;	//curl允許等待秒數
	//var $api_url='https://api.dg99api.com';
	var $api_url = 'https://rest.pn-api.com/v1/game/';
	var $Authorization='cG5fZ2FtZV93YWxsZXRfc2Vzc2lvbjpkOWIzNTRjN2RkY2I3M2I2NDI2NjQ2ZDAwYjI1ZTliOTk3ZDk2Yjgx';	
 	var	$api_key = '8ogs48c4w48w84o0kgwkoks08w8oks80w44wk8sc';
	var $currencyName='NTD';
	var $access_token;
	var $mode = 1 ; //0測試模式  1 正式環境
	var $flag = '9d' ; //營運商代碼
	var $external_ip = '103.197.69.21';
	var $H5_URL = 'https://game.pn-api.com/pk10h5/app.php?access_token=';
	var $PC_URL = 'https://game.pn-api.com/pk10h5/index.php?access_token=';
	var $agentName = '';
	var $token = '';

	public function __construct(){

		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
		//$this->$external_ip = exec('curl http://ipecho.net/plain; echo');
	}
	

	

	public function create_account($u_id,$mem_num,$gamemaker_num=30){

		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//如果帳號不存在則把帳號寫入資料庫
			$post_data=array(	'grant_type' => 'client_credentials',
								'flag' => $this->flag,
								'login_user' => $u_id,
								'currency' => 'NTD',
								'mode' => $this->mode,
								'lang' => 'tw',
			 					'Ip' => $this->external_ip,
			 					'max_played_coin' => '20000',
			 					'max_period_coin' => '20000'
							);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->api_url.'/login');
			curl_setopt($ch, CURLOPT_HTTPHEADER,
			array(
			 'Authorization:Basic '. $this->Authorization  ,
			 'Content-Type: application/x-www-form-urlencoded',
			 'x-api-key: '.$this->api_key
			 )
			);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
			$output=json_decode(curl_exec($ch));
			//print_r($output);exit;
			$curl_errno = curl_errno($ch);	
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if(!$curl_errno){
				if($http_code===200 && $output->status==1){
					if ($this->mode==1){
						$colSql="u_id,mem_num,gamemaker_num";
						$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
						$parameter=array();
						$parameter[':u_id']=trim($u_id);
						$parameter[':mem_num']=$mem_num;
						$parameter[':gamemaker_num']=$gamemaker_num;
						$this->CI->webdb->sqlExc($upSql,$parameter);
						return NULL;
					}
				}else{
					return 'pk10 帳號建立失敗，未在本地資料庫新增會員遊戲帳號！';
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
		$post_data=array('login_user'=>trim($u_id),'lang'=>'tw');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/balance");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . sizeof($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			 array(
			 'Authorization:Basic '. $this->Authorization  ,
			 'Content-Type: application/x-www-form-urlencoded',
			 'x-api-key: '.$this->api_key
			 )
			);	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 &&  $output->status == 1){
				return $output->message->amount;
			}else{
				return 	'--';
			}
		}else{
			return '--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=30){	//轉入點數到遊戲帳號內
		$post_data=array('login_user'=>trim($u_id),'lang'=>'tw','amount'=>$amount);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/account_deposit");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . count($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			 array(
			 'Authorization:Basic '. $this->Authorization  ,
			 'Content-Type: application/x-www-form-urlencoded',
			 'x-api-key: '.$this->api_key
			 )
			);	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->status == 1){
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
				return '轉點錯誤：'.$output->message;
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	
	}


	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=30){	//轉入點數到遊戲帳號內
		$member=array('username'=>trim($u_id),'amount'=>"-".$amount);
		$SN=time().mt_rand();

		$post_data=array('login_user'=>trim($u_id),'lang'=>'tw','amount'=>$amount);
		//$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/account_withdraw");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . count($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			 array(
			 'Authorization:Basic '. $this->Authorization  ,
			 'Content-Type: application/x-www-form-urlencoded',
			 'x-api-key: '.$this->api_key
			 )
			);	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->status==1){
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
				return '轉點錯誤：'.$output->message;
			}
		}else{
			//return $this->point_checking($SN,2,$u_id,$amount,$mem_num,$gamemaker_num);
			return '系統繁忙中，請稍候再試';	
		}
	}
	
	
	
	


	public function forward_game($u_id){	//登入遊戲
		//$logMsgA=$this->updateLimit(trim($u_id));	
		$post_data=array(	'grant_type' => 'client_credentials',
							'flag' =>  $this->flag,
							'login_user' => $u_id,
							'currency' => 'NTD',
							'mode' => $this->mode,
							'lang' => 'tw',
							'Ip' => $this->external_ip,
							'max_played_coin' => '1000',
							'max_period_coin' => '5000'
						);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url.'/login');
		curl_setopt($ch, CURLOPT_HTTPHEADER,
		 array(
		 'Authorization:Basic '. $this->Authorization  ,
		 'Content-Type: application/x-www-form-urlencoded',
		 'x-api-key: '.$this->api_key
		 )
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$this->access_token = $output->message->access_token;
		if(!$curl_errno){
			if($http_code===200 && $output->status==1){
				if(!$this->CI->agent->is_mobile()){		//電腦版 回傳flash登入網址
					return $this->PC_URL.$output->message->access_token;
					//exit;
				}else{	//手機板回傳H5登入網址
					return $this->H5_URL.$output->message->access_token;
					//exit;
				}
			}else{
				$output->message;
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



	public function updateLimit($u_id){	//修改限紅
		$post_data=array('login_user'=>$member,'max_played_coin'=>'20000','max_period_coin'=>'5000');//,"26,27,28,29,67,68,69,70"
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/update_user_all_limitations");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
		 array(
		 'Authorization:Basic '. $this->Authorization  ,
		 'Content-Type: application/x-www-form-urlencoded',
		 'x-api-key: '.$this->api_key
		 )
		);	 	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->status==1){
				return NULL;
			}else{
				return '限紅修改失敗';
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}
	
	public function getallgame(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/get_all_played_ids/20");		
		curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//$headers=array('Content-Type: application/json','Content-Length: ' . strlen($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER,
		 array(
		 'Authorization:Basic '. $this->Authorization  ,
		 'Content-Type: application/x-www-form-urlencoded',
		 'x-api-key: '.$this->api_key
		 )
		);	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);

		
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($http_code."|".$curl_errno."|".$output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				//return NULL;
				print_r($output);
			}else{
				return '取得遊戲類型失敗';
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}		
	}
	
	public function get_user_limitations($uid){
		$ch = curl_init();
		//$member=array('username'=>trim($uid));
		$post_data=array('login_user'=>$uid);
		$post_data=json_encode($post_data);		
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/get_user_limitations");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
		 array(
		 'Authorization:Basic '. $this->Authorization  ,
		 'Content-Type: application/x-www-form-urlencoded',
		 'x-api-key: '.$this->api_key
		 )
		);	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);

		
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		print_r($http_code."|".$curl_errno."|".$output);
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				//return NULL;
				pirnt_r($output);
			}else{
				return '取得遊戲限額失敗';
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}			
	}
	
	
	public function reporter_all($sTime='',$eTime='',$pageNum=1){	//
		date_default_timezone_set("Asia/Taipei");
		if ($sTime != ''){
			$sTime =  strtotime($sTime) ;
		}
		if ($eTime != ''){
			$eTime =  strtotime($eTime) ;
		}
		$post_data=array('lang'=>'tw','start'=>$sTime,'end'=>$eTime,'page'=>$pageNum);
		//$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/bet_history");		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Content-Type: application/json','Content-Length: ' . count($post_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			 array(
			 'Authorization:Basic '. $this->Authorization  ,
			 'Content-Type: application/x-www-form-urlencoded',
			 'x-api-key: '.$this->api_key
			 )
			);	
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);


		//echo '<pre>';
		//print_r($output);
		//echo '</pre>';		
		if(!$curl_errno){
			if($http_code===200 && $output->status==1){
				if(isset($output->message)){
					return $output;
				}
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

		$member=array('username'=>trim($u_id),'winLimit'=>500000);
		$post_data=array('token'=>$this->access_token,'member'=>$member);
		$post_data=json_encode($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/user/update/".$this->agentName);		
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
		//echo $http_code;
		
		//print_r($output);exit;
		
		//exit;
		if(!$curl_errno){
			if($http_code===200 && $output->codeId==0){
				return NULL;
			}else{
				return '會員更新設定失敗！';
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
