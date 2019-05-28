<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pchomeclass{
	var $CI;
	
	//API 正式環境串接帳密	
	var $tokenURL='https://api.pchomepay.com.tw/v1/token';//token 網址
	var $paymentURL='https://api.pchomepay.com.tw/v1/payment/atmva';//正式
	var $APPID='ED62291D5B3C78D425664C8FCBE1';
	var $SECRET='3lNX856GkdUeB5Du8ieNZQqvvrBxypMTGCTuPjOe';
	/*
	//API 測試環境串接帳密 		
	var $tokenURL='https://sandbox-api.pchomepay.com.tw/v1/token';//token 網址	
	var $paymentURL='https://sandbox-api.pchomepay.com.tw/v1/payment/atmva'; 	//測試環境
	var $APPID='ED62291D5B3C78D425664C8FCBE1';
	var $SECRET='PfJHLCQ_XXSBk_VzYKQLMYvDSFp3guWhdx4Nf_LT';
	*/
	/*
	var $SHOP_ID='42816104';	//商店代號
	var $SYS_TRUST_CODE='ntptjl9ny4juckrw5qla5m3hd6pimdy9';		 //交易驗證碼
	var $SubID = "91D";		//商店名稱
	var $CURRENCY='TWD';	//幣別
	*/
	public function __construct(){		
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function getFormArray($order_no,$Amount,$Payment='ATM',$bank=NULL){
		$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$HostUrl.=$_SERVER['HTTP_HOST'];
				
		//取得新的 token, 如果 token 還在有效期內的話請不要重複取得 
		//將帳號密碼以 base64 encode 後帶在 header 中取得 token 		
		$headers = array( 
			'Content-Type:application/json', 
			'Authorization: Basic '. 
			base64_encode($this->APPID.":".$this->SECRET) 
		); 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_URL,$this->tokenURL); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, null); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
		$result = curl_exec($ch); 
		curl_close($ch); 
		//$result 裡會有可用的 token, 在失效時間 [expired_timestamp] 之前 token 都有效 
		//這裡用指定的方式來展示 json 的格式 
		//print_r($result);
		//use the token to call api 
		$token = json_decode($result); 
		$headers = array( 
			'Content-Type:application/json', 
			'pcpay-token:'.$token->token);
			
		$data=array();
		$data['order_id']=$order_no;//訂單編號
		$data['amount']=$Amount;//金額
		$data['item_name'] = "此為商城虛擬商品購買，並非實體商品交易，請勿受騙上當，並請勿代他人繳款儲值，小心觸法";
		$data['item_url'] = '';
		$data['expire_days']='5';//繳費期限
		if($bank==NULL){
			$bank = "808";	
		}
		$data['atm_bank'] = $bank;//銀行的虛擬帳 011 上海商銀, 808 玉山銀行, 812 台新銀行, 013 國泰世華
		$Payment=='ATM';//付款方式			 
		$requestPayload = '{ 
		  "order_id":"'.$order_no.'", 
		  "pay_type":["'.$Payment.'"], 
		  "amount":'.$Amount.', 
		  "atm_bank":"'.$bank.'",
		  "return_url":"'.$HostUrl."/module/pchome/Payresult?order_no=".$order_no.'", 
		  "item_name": "此為商城虛擬商品購買，並非實體商品交易，請勿受騙上當，並請勿代他人繳款儲值，小心觸法", 
		  "item_url": "" 
		}'; 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_URL,$this->paymentURL); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestPayload); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
		
		$result = curl_exec($ch); 
		curl_close($ch); 
		/** 
		 * result when success 
		 * {"order_id":"J2016101000005","payment_url":"https:\/\/pay.pchomepay.com.tw\/ 
		 * ppwf?_pwfkey_=rVKHID0O1KIltnH-MHD5vHtheBCUYLEQtYZxrGsjG9K6IXPSyp6lnx4nOQ__"} 
		 */ 
		/** 
		 * result when fail 
		 * {"error_type":"invalid_request_error","code":20001,"message":"order id duplicate"} 
		 */ 
		//echo $result;	
		$r = json_decode($result);
		$sqlStr = "insert into pchome_orders(order_no,bank_id,virtual_account,expire_date) value('".$r->order_id."','".$r->bank_id."','".$r->virtual_account."','".$r->expire_date."')";
		//echo $sqlStr;
		$this->CI->webdb->sqlExc($sqlStr);
		
        return;
	}	
	
}

?>