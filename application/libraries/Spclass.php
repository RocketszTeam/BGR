<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//智付通
class Spclass{
	var $CI;
	
	var $MerchantID='MS357039786';
	var $ServiceURL='https://core.spgateway.com/MPG/mpg_gateway';
	var $HashKey='wPGQp5qRnTWX6AfFUOSOSuaeKAc78U0t';
	var $HashIV='8ofRzwLdu2fx1SLS';
	/*
	var $MerchantID='MS356601465';
	var $ServiceURL='https://core.spgateway.com/MPG/mpg_gateway';
	var $HashKey='yx9zE7rV3gXpCJPQoiOAVwFD0vEiQY7M';
	var $HashIV='hkAHNo98lGVpyySB';
	*/
	var $Version='1.4';
	public function __construct(){		
		$this->CI=&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}

	public function getFormArray($order_no,$Amount,$Paymenttype='ATM',$target='_self',$paymentButton=NULL){
		$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$HostUrl.=$_SERVER['HTTP_HOST'];
		$data=array('MerchantID' => $this->MerchantID,
					'RespondType' => 'String',
					'TimeStamp' => time(),
					'Version' => $this->Version,
					'MerchantOrderNo' => $order_no,
					'Amt' => $Amount,
					'ItemDesc' => 'ProductInfo',
					'Email' => time().'@gmail.com',
					'LoginType' => 0,
					'NotifyURL' => $HostUrl.'/module/sp/Payresult',	//付款結果接收網址
					'CustomerURL' => $HostUrl.'/module/sp/Getcode?order_no='.$order_no	//超商 or ATM 取號後 返回網址
					);
					
		if($Paymenttype=='ATM'){	//ATM
			$data=array_merge($data,array('VACC'=>1,'ExpireDate'=>date('Y-m-d',strtotime("+1 days"))));
		}elseif($Paymenttype=='CVS'){	//超商代碼繳費
			$data=array_merge($data,array('CVS'=>1,'ExpireDate'=>date('Y-m-d',strtotime("+1 days"))));
		}
		//AES加密			
		$TradeInfo=$this->create_mpg_aes_encrypt($data);
		//SHA256加密
		$TradeSha=$this->sha256($TradeInfo);
		$data=array_merge($data,array('TradeInfo'=>$TradeInfo,'TradeSha'=>$TradeSha));
		$szHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$szHtml .= '<form id="__Spgateway" method="post" target="' . $target . '"  action="' . $this->ServiceURL . '">';
		foreach ($data as $keys => $value) {
			$szHtml .="<input type='hidden' name='$keys' value='$value' />";
		}
		// 手動或自動送出表單。
		if (!isset($paymentButton)) {
			$szHtml .= '<script type="text/javascript">document.getElementById("__Spgateway").submit();</script>';
		} else {
			$szHtml .= '<input type="submit" id="__paymentButton" value="' . $paymentButton . '" />';
		}
		$szHtml .= '</form>';
        print $szHtml;
        exit();
        die();
        flush();
        return;
	}
	
	//AES加密
	private function create_mpg_aes_encrypt ($parameter) { 
		$return_str = '';
		if (!empty($parameter)) { 
			// 將參數經過 URL ENCODED QUERY STRING 
			$return_str = http_build_query($parameter); 
		}
		return trim(bin2hex(openssl_encrypt($this->addpadding($return_str), 'aes-256-cbc', $this->HashKey, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->HashIV)));
	}
	
	private function addpadding($string, $blocksize = 32) { 
		$len = strlen($string); 
		$pad = $blocksize - ($len % $blocksize); 
		$string .= str_repeat(chr($pad), $pad); 
		return $string;
	}
	
	//AES解密
	public function create_aes_decrypt($parameter) { 
		 return $this->strippadding(openssl_decrypt(hex2bin($parameter),'AES-256-CBC', $this->HashKey, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->HashIV));
	}
	
	private function strippadding($string) {
		$slast = ord(substr($string, -1));
		$slastc = chr($slast);
		$pcheck = substr($string, -$slast);
		if (preg_match("/$slastc{" . $slast . "}/", $string)) {
			$string = substr($string, 0, strlen($string) - $slast);
			return $string;
		}else{
			return false;
		}
	}
	
	//SHA256加密
	private function sha256($data){
		$encode_str='HashKey='.$this->HashKey.'&'.$data.'&HashIV='.$this->HashIV;
		return  strtoupper(hash("sha256", $encode_str)); 	
	}
	
	public function CheckOutFeedback(){
		$arErrors = array();
		$arFeedback=array();		
		foreach ($_POST as $keys => $value) {
			$arFeedback[$keys]=$value;	
		}
		if (sizeof($arFeedback) > 0) {
			 //壓碼組合
			 $TradeSha=$this->sha256($arFeedback["TradeInfo"]);
			 //壓碼錯誤
			 if($TradeSha != $arFeedback["TradeSha"]){
				array_push($arErrors, 'TradeSha verify fail.'); 
			 }
		}
		if (sizeof($arErrors) > 0) {
			throw new Exception(join('- ', $arErrors));
		}
		return $arFeedback;
	}
	
	
}

?>