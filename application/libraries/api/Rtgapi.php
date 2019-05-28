<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rtgapi {
    
	public function __construct(){
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	//取得token
	public function index_token(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,rtg_api_url."/start/token?username=".rtg_id."&password=".rtg_key);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if(!$curl_errno){
            if($http_code==200){
                return $output->token;
            } else {
                return "http_code : ".$http_code;
            }
        }
	}

	//取得參數 ，取得的參數是提供使用API时的参数
	public function index_agent(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,rtg_api_url."/start");
		//curl_setopt($ch, CURLOPT_POST, 1);//CURLOPT_POST 為1或true 表示用post方式輸出
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers=array('Authorization: '.$this->index_token(),'Content-Type: application/json', 'Content-Length: ');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
		curl_setopt($ch, CURLOPT_TIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$output=json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if(!$curl_errno){
            if($http_code==200){
                return $output;
            } else {
                return "http_code : ".$http_code;
            }
        }
	}

    public function RTGConnect($post_data, $path, $method){
        $post_data = json_encode($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rtg_api_url . $path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);  //方法
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Authorization: '. $this->index_token(), 'Content-Length: ' . strlen($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, rtg_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array('curl_errno' => $curl_errno, 'httpCode' => $httpCode, 'response' => $output);
    }

	//建立遊戲帳號
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=51){  //$u_id,$u_password,$mem_num,$gamemaker_num=51
		$parameter=array();
		$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
		$parameter[':u_id']=trim($u_id);
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=51;//$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		
		if($row==NULL){	//如果帳號不存在則把帳號寫入資料庫
			//email需要跟着Email的format，gender可以放1，birthdate需要放日子的format及countryID可以放TW currency可以放TWD
			$post_data=array('agentId'=>$this->index_agent()->agentId,'username'=>$u_id,'firstName'=>'','lastName'=>'','email'=>'TesT@tESt.ccc','gender'=>1,'birthdate'=>NOW(),'countryId'=>'TW','currency'=>'TWD');
			$post_data=json_encode($post_data);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,rtg_api_url."/player");
            curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$headers=array('Authorization: '.$this->index_token(),'Content-Type: application/json', 'Content-Length: '.strlen($post_data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  	
			curl_setopt($ch, CURLOPT_TIMEOUT,rtg_timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$output=json_decode(curl_exec($ch));
			$curl_errno = curl_errno($ch);	
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if(!$curl_errno){
				if($http_code===201){
					$colSql="u_id,u_password,mem_num,gamemaker_num,cid";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);
					$parameter[':u_password']=md5($u_password);	//密碼加密
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$parameter[':cid'] = $output->id;//取得API 遊戲帳號id
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
                    return '創建失敗：'.$output -> errorCode.':'.$output -> message;
				}
			}else{
				return '系統繁忙中，請稍候再試';	
			}
		}else{
			return '會員已有此類型帳號';	
		}
	}
	
	//餘額查詢.
	public function get_balance($u_id){

		$post_data=array('playerLogin'=>$u_id,'agentId'=>$this->index_agent()->agentId);
		$post_data=json_encode($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,rtg_api_url."/wallet");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers=array('Authorization: '.$this->index_token(),'Content-Type: application/json', 'Content-Length: '.strlen($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output=json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

		if(!$curl_errno){
			if($http_code===200 ){
				return $output;
			}else{
				return 	"--";
			}
		}else{
			return "--";
		}
	}
	
	//轉入點數到遊戲帳號內
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num,$logID=NULL){	
		//var_dump(getWalletTotal($mem_num));exit;
		//$member=array('username'=>trim($u_id),'amount'=>$amount);

		$SN=time().mt_rand();
		
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$SN."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);

        $path = '/wallet/deposit/'.$amount;
        $method = "POST";
        $post_data = array(
            "agentId" => $this->index_agent()->agentId,
            "playerLogin" => $u_id,
            //"amount" => $amount,
            "trackingOne" => $SN
        );
        $output = $this -> RTGConnect($post_data, $path, $method);
        $outputJson = json_decode($output['response']);
		if(!$output['curl_errno']){
			if($output['httpCode']==200 && $outputJson->errorMessage=="OK"){
				$makers_balance=($this->get_balance($u_id)!='--' ? $this->get_balance($u_id) : 0);
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance - (float)$amount;//異動後點數
                $parameter = array();
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter[":mem_num"] = $mem_num;
                $parameter[":kind"] = 3;    //轉入遊戲
                $parameter[":points"] = "-" . $amount;
                $parameter[":makers_num"] = $gamemaker_num;
                $parameter[":makers_balance"] = $makers_balance;
                $parameter[":admin_num"] = tb_sql("admin_num", "member", $mem_num);
                $parameter[":buildtime"] = now();
                $parameter[':before_balance'] = $before_balance;
                $parameter[':after_balance'] = $after_balance;
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                return NULL;
			}else{
				//return '轉點錯誤：'.$output['httpCode'].'Msg：'. $outputJson->error;
                return '轉點錯誤：'.$output['httpCode'];
			}
		}else{
			//return $this->point_checking($SN,1,$u_id,$amount,$mem_num,$gamemaker_num);
			return '系統繁忙中，請稍候再試';
		}

	}

	//轉入點數到遊戲帳號內
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=51,$logID=NULL){	
		//$member=array('username'=>trim($u_id),'amount'=>"-".$amount);
		$SN=time().mt_rand();
		//將轉點編號寫入DB
		$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$SN."' where num=".$logID;
		$this->CI->webdb->sqlExc($upSql);

        $path = '/wallet/withdraw/'.$amount.'?trackingOne='.$SN;
        $method = "POST";
        $post_data = array(
            "agentId" => trim($this->index_agent()->agentId),
            "playerLogin" => trim($u_id)
            //"trackingOne" => $SN
        );
        $output = $this -> RTGConnect($post_data, $path, $method);
        $outputJson = json_decode($output['response']);
        //成功 Array ( [curl_errno] => 0 [httpCode] => 200 [response] => {"errorMessage":"OK","transactionId":73085.0000,"errorCode":"False"} )
        //失敗 Array ( [curl_errno] => 0 [httpCode] => 500 [response] => )
        //print_r($path);
        //print_r($post_data);
        //print_r($output);
        if(!$output['curl_errno']){
            if($output['httpCode']==200 && $outputJson->errorMessage=="OK"){
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
				//return '餘額 : '.$makers_balance;
                return NULL;
			}else{
                //return '轉點錯誤：'.$output['httpCode'].'Msg：'. $outputJson->error;
                return '轉點錯誤：'.$output['httpCode'];
			}
		}else{
		//	return $this->point_checking($SN,2,$u_id,$amount,$mem_num,$gamemaker_num);
			return '系統繁忙中，請稍候再試';
		}
	}

	
	//取得遊戲資訊列表，包含遊戲名稱 遊戲代號
	public function gamestrings(){		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,rtg_api_url."/gamestrings?locale=zh-CN");
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers=array('Authorization: '.$this->index_token(),'Content-Type: application/json', 'Content-Length: ');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output=json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if(!$curl_errno){
            if($http_code===200){
                return $output;
            }else{
                return $http_code;
            }
        }else{
            return $curl_errno;
        }

	}

	//登入遊戲
	public function forward_game($u_id,$gameId){
        $post_data=array('player'=>array('playerLogin'=>$u_id,'agentId'=>$this->index_agent()->agentId),'gameId'=>$gameId,'locale'=>'zh-CN','returnUrl'=>"",'isDemo'=>false);
        $post_data=json_encode($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,rtg_api_url."/GameLauncher");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers=array('Authorization: '.$this->index_token(),'Content-Type: application/json', 'Content-Length: '.strlen($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output=json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if(!$curl_errno){
            if($http_code===200){
                return $output->instantPlayUrl;
            }else{
                return $http_code;
            }
        }else{
            return $curl_errno;
        }
    }

	//試玩遊戲
	public function fun_game($u_id,$gameId){
		
		$post_data=array('player'=>array('playerLogin'=>$u_id,'agentId'=>$this->index_agent()->agentId),'gameId'=>$gameId,'locale'=>'zh-CN','returnUrl'=>"",'isDemo'=>true);
		$post_data=json_encode($post_data);
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,rtg_api_url."/GameLauncher");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers=array('Authorization: '.$this->index_token(),'Content-Type: application/json', 'Content-Length: '.strlen($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT,rtg_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output=json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
		if(!$curl_errno){
			if($http_code===200){
				/*
				if(!$this->CI->agent->is_mobile()){		//電腦版 回傳flash登入網址
					return $output->list[0].$output->token;
					//exit;
				}else{	//手機板回傳H5登入網址
					return $output->list[1].$output->token;
					//exit;
				}
				*/
				return $output->instantPlayUrl;
			}else{
				"系統繁忙中，請稍候再試";
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}

	//報表
	public function reporter_all($sTime,$eTime){
		$post_data=array('params'=>array('agentId'=>$this->index_agent()->agentId,'fromDate'=>$sTime,'toDate'=>$eTime));
		$post_data=json_encode($post_data);
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,rtg_api_url."/report/playergame");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers=array('Authorization: '.$this->index_token(),'Content-Type: application/json', 'Content-Length: '.strlen($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT,20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output=json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

		if(!$curl_errno){
			if($http_code===200){
				return $output;
			}
		}
	}
	
	
}
