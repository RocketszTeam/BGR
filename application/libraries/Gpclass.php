<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Gpclass{
	var $CI;
	var $paymentURL='https://gomypay.asia/Cathaybk/Cathaybk_pay.asp';
	var $SHOP_ID='42816104';	//商店代號
	var $SYS_TRUST_CODE='ntptjl9ny4juckrw5qla5m3hd6pimdy9';		 //交易驗證碼
	var $SubID = "91D";		//商店名稱
	var $CURRENCY='TWD';	//幣別
	
	public function __construct(){		
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function getFormArray($order_no,$Amount,$Payment='ATM',$target="_self",$paymentButton=NULL){
		$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$HostUrl.=$_SERVER['HTTP_HOST'];
				
		$data=array();

		$data['Customer_no']=$this->SHOP_ID;
		$data["str_check"]= $this->SYS_TRUST_CODE;
		$data["CustomerOrderID"] = $order_no;//訂單編號
		$data["SubID"] = $this->SubID;//商店代號
		$data['Amount']=$Amount;//金額
		if($Payment=='ATM'){//付款方式
			$data['OrderType']='2';
		}else{
			switch($Payment){
				case 'FAMI':	//全家
					$data['OrderType']='3';
					break;
				case 'HILIFEET':	//萊爾富
					$data['OrderType']='3';
					break;
				case 'IBON':	//7-11
					$data['OrderType']='3';
					break;
				case 'CVS':
					$data['OrderType']='3';
					break;				
			}
		}
		$data['ExpriesDay']='5';//繳費期限
		$data["BuyerName"] = "";
		$data["BuyerEmail"] = time().'@gmail.com';
		$data["BuyerTelm"] = "";//"09".substr(time(),0,8);
		$data['Remark']="此為商城虛擬商品購買，並非實體商品交易，請勿受騙上當，並請勿代他人繳款儲值，小心觸法";//urlencode('商城商品');	
		$data["CallBackUrl"] = $HostUrl."/module/gp/Payresult?order_no=".$order_no;//對帳網址
		//$data["ReturnUrl"] = $HostUrl."/module/gp/Getcode?order_no=".$order_no;	//超商 or ATM 取號後 回傳網址
		$data["ReturnUrl"] = "";	//超商 or ATM 取號後 回傳網址
		
		////接到這
		$szHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$szHtml .= '<form id="__PepayForm" method="post" target="' . $target . '"  action="' . $this->paymentURL . '">';
		foreach ($data as $keys => $value) {
			$szHtml .="<input type='hidden' name='$keys' value='$value' />";
		}
		// 手動或自動送出表單。
		if (!isset($paymentButton)) {
			$szHtml .= '<script type="text/javascript">document.getElementById("__PepayForm").submit();</script>';
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
	
}

?>