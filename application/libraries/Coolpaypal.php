<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Coolpaypal {
	var $HashKey='3GF6J5YG9X8FG9R8SPKU58W4D4';
	var $HashIV='LHCHYNCU93LVQ7XCXSCR4YR6';
	var $ValidateKey='YQJSH8VRB5';
	var $MerProductID='Product';	//購買的商品名稱 我統一設定
	var $MerUserID='buyUser';	//購買者帳號 我統一設定
	var $API_HOST='https://payflow.goldpay88.com/';
	var $CI;
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
		
	}
	
	public function getCreditPay($order_no,$Amount){	//信用卡
		try{
			$url=$this->API_HOST."/Payment/Credit.php";
			$szHtml='<form id="Coolpaypal" method="post" action="'.$url.'" enctype="application/x-www-form-urlencoded">';
			$szHtml.='<input name="HashKey" type="hidden" value="'.$this->HashKey.'" />';
			$szHtml.='<input name="HashIV" type="hidden" value="'.$this->HashIV.'" />';
			$szHtml.='<input name="MerTradeID" type="hidden" value="'.$order_no.'" />';
			$szHtml.='<input name="MerProductID" type="hidden" value="'.$this->MerProductID.'" />';
			$szHtml.='<input name="MerUserID" type="hidden" value="'.$this->MerUserID.'" />';
			$szHtml.='<input name="Amount" type="hidden" value="'.$Amount.'" />';
			$szHtml.='<input name="TradeDesc" type="hidden" value="交易描述" />';
			$szHtml.='<input name="ItemName" type="hidden" value="商城商品" />';
			$szHtml.='<input name="UnionPay" type="hidden" value="0" />';
		    $szHtml.='<input name="Validate" type="hidden" value="'.$this->OrderValidate($order_no,$Amount).'" />';
			$szHtml.='</form>';
			$szHtml.='<script type="application/javascript">document.getElementById("Coolpaypal").submit();</script>';
			print $szHtml;
			exit();
			die();
			flush();
			return NULL;
			
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	
	
	public function getFormArray($order_no,$Amount,$Payment='ATM'){
		try{
			if($Payment=='ATM'){
				$url=$this->API_HOST."/Payment/ATM.php";
			}else{
				$url=$this->API_HOST."/Payment/Store.php";
			}
			
			$szHtml='<form id="Coolpaypal" method="post" action="'.$url.'">';
			$szHtml.='<input name="HashKey" type="hidden" value="'.$this->HashKey.'" />';
			$szHtml.='<input name="HashIV" type="hidden" value="'.$this->HashIV.'" />';
			$szHtml.='<input name="MerTradeID" type="hidden" value="'.$order_no.'" />';
			$szHtml.='<input name="MerProductID" type="hidden" value="'.$this->MerProductID.'" />';
			$szHtml.='<input name="MerUserID" type="hidden" value="'.$this->MerUserID.'" />';
			$szHtml.='<input name="Amount" type="hidden" value="'.$Amount.'" />';
			$szHtml.='<input name="TradeDesc" type="hidden" value="交易描述" />';
			$szHtml.='<input name="ItemName" type="hidden" value="點數商品" />';
			if($Payment=='ATM'){
				$szHtml.='<input name="Type" type="hidden" value="CTCB" />';
			}else{
				$szHtml.='<input name="Type" type="hidden" value="'.$Payment.'" />';
			}
		    $szHtml.='<input name="Validate" type="hidden" value="'.$this->OrderValidate($order_no,$Amount).'" />';
			$szHtml.='</form>';
			$szHtml.='<script type="application/javascript">document.getElementById("Coolpaypal").submit();</script>';
			print $szHtml;
			exit();
			die();
			flush();
			return NULL;
		}catch (Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	private function OrderValidate($order_no,$Amount){
		$Validate=$this->ValidateKey.$this->HashKey.$order_no.$this->MerProductID.$this->MerUserID.$Amount;
		return md5($Validate);
	}
	
	
	public function CheckOutFeedback(){
		$arErrors = array();
		$arFeedback=array();		
		foreach ($_POST as $keys => $value) {
			$arFeedback[$keys]=$value;	
		}
		if (sizeof($arFeedback) > 0) {
			$Validate=$this->ValidateKey.$this->HashKey.$arFeedback['RtnCode'].$arFeedback['MerTradeID'].$arFeedback['MerProductID'].$arFeedback['MerUserID'].$arFeedback['Amount'];
            if ($arFeedback['Validate'] != md5($Validate)) {
                array_push($arErrors, 'Validate verify fail.');
            }
			
		}
		if (sizeof($arErrors) > 0) {
			throw new Exception(join('- ', $arErrors));
		}
		return $arFeedback;
	}
}
?>