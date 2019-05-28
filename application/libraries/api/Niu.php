<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Niu {
	private $CI;
	private $timeout=30;	//curl允許等待秒數
	private $agentcode=421344;	//代理推薦碼
	private $agent_prefix='vp';	//代理推薦代碼
    private $url = 'http://124.150.129.148:8805/api';
	private $callbackurl;	//轉點成功回傳網址
    private $signKey = 'PiOLumB03Tingt3R3ULdvFzS';
	private $errorMsg=array();
	
	public function __construct(){	
		$this->CI =&get_instance();
		$this->callbackurl=site_url('module/Niuresult');	//定義轉點成功回傳路徑
		date_default_timezone_set("Asia/Taipei");
		
		//定義錯誤代碼
		$this->errorMsg[1000]='簽證遺失';
		$this->errorMsg[1001]='簽證驗證失敗';	
		$this->errorMsg[1002]='請求格式缺失';	
		$this->errorMsg[1003]='請求參數格式錯誤';
		$this->errorMsg[1004]='無符合查詢的帳號';	
		$this->errorMsg[1005]='請求筆數過多';
		$this->errorMsg[1007]='帳號餘額不足';
		$this->errorMsg[1008]='不存在的紀錄編號';
		$this->errorMsg[1009]='資料庫存取異常';
		$this->errorMsg[1010]='已存在相同暱稱';
		$this->errorMsg[1011]='已存在相同的自訂義資料';
		$this->errorMsg[1012]='會員帳號已存在';
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=99){	//創建遊戲帳號
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api	
			$url = $this->url . "/memberInsert";
			$accArr = [
				[
					'account' => trim($u_id),
					'password'=> md5(trim($u_password)),
					'name' => trim($u_id),
					'agentcode' => $this->agentcode
				]
			];
			$post_data['data'] = json_encode($accArr);
			$output=$this->curl($url,$post_data);
			print_r($output);exit;
			if($output){
				if($output->error_code==0){
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
					return '創建失敗:'.$output->error_code.$this->errorMsg[$output->error_code];
				}
			}else{
				return '系統繁忙中，請稍後再試';	
			}
		}else{
			return '會員已有此類型帳號';
		}
	}
	
	
	public function get_balance($u_id){	//餘額查詢
		$url = $this->url . "/memberWallet";
        $accArr = [
            ['account' => $u_id,'agentcode' => $this->agentcode]
        ];
        $post_data['data'] = json_encode($accArr);
		$output=$this->curl($url,$post_data);
		print_r($output);
		if($output){
			if($output->error_code==0){
				return (isset($output->successes[0]->wallet) ? $output->successes[0]->wallet : 0);
			}else{
				return '--';
			}
		}else{
			return '--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=28,$logID=NULL){	//轉入點數到遊戲帳號內
        $url = $this->url . "/transfer";
        $post_data = [
            'account'  => $u_id ,
            'adjust'   => $amount ,
            'custom'   => $logID ,
			'agentcode' => $this->agentcode,
            'callbackurl' => $this->callbackurl
        ];
		$output=$this->curl($url,$post_data);
		print_r($output);exit;
		if($output){
			if($output->error_code==0){
				return '等待轉帳';
			}else{
				return '轉點失敗:'.$output->error_code.$this->errorMsg[$output->error_code];
			}
		}else{
			return '系統繁忙中，請稍後再試';	
		}
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=28,$logID=NULL){	//遊戲點數轉出
        $url = $this->url . "/transfer";
        $post_data = [
            'account'  => $u_id ,
            'adjust'   => (int)("-".$amount) ,
            'custom'   => $logID ,
			'agentcode' => $this->agentcode,
            'callbackurl' => $this->callbackurl
        ];
		$output=$this->curl($url,$post_data);
		print_r($output);exit;
		if($output){
			if($output->error_code==0){
				return '等待轉帳';
			}else{
				return '轉點失敗:'.$output->error_code.$this->errorMsg[$output->error_code];
			}
		}else{
			return '系統繁忙中，請稍後再試';	
		}
	}
	
	//代理營收報表
	public function agentReport($sTime,$eTime){
        $url = $this->url . "/agentReport";
		$unix_start = strtotime($sTime);        
		$unix_end = strtotime($eTime);		
        $post_data = [
            'unix_start'  => "$unix_start",
            'unix_end'   => "$unix_end" ,
            'agentcode'   => $this->agentcode 
        ];
		$output=$this->curl($url,$post_data);
		print_r($output);
		if($output){
			if($output->error_code==0){
				
			}
		}	
	}
	
	//會員報表
	public function accountReport($sTime,$eTime,$u_id=NULL){
        $url = $this->url . "/accountReport";
		$unix_start = strtotime($sTime);        
		$unix_end = strtotime($eTime);		
        $post_data = [
            'unix_start'  => "$unix_start" ,
            'unix_end'   => "$unix_end" ,
            //'account'   => $u_id,
			'agentcode' => $this->agentcode,
			'report_type'=> 'all' 
        ];
		$output=$this->curl($url,$post_data);
		print_r($output);exit;
		if($output){
			if($output->error_code==0){
				
			}
		}	
	}
	
	public function CheckOutFeedback(){
        // 變數宣告。
        $arErrors = array();
        $arFeedback = $this->CI->input->get(NULL,true);
		if (sizeof($arFeedback) > 0) {
			if(!$this->checkCallBackSign($arFeedback)){
				array_push($arErrors, 'sign verify fail.');
			}
		}
        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }
        return $arFeedback;
	}
	
    // 檢查  callback Sign 是否正確
    public function checkCallBackSign($request){
        $code = "account=" . $request['account'] . "&adjust=" . $request['adjust'] .
            "&callbackurl=" .  urlencode($request['callbackurl'])  . "&custom=" . $request['custom']  .
            "&error_code=" . $request['error_code'] . "&record_id=" .  $request['record_id'] . "&wallet=" . $request['wallet'] . $this->signKey ;
        if(  md5( $code ) == $request['sign'] ){
            return true;
        }else{
            return false ;
        }
    }
	
	
    private function curl($url, $params = array()){
		ksort($params); /* 進行排序 */
		$params['sign'] = md5(json_encode($params) . $this->signKey);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code.'<br>'.$curl_errno;
		if($http_code==200 && !$curl_errno){
        	return $output;
		}
    }
}
?>