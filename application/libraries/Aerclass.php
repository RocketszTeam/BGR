<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Aerclass {
	var $CI;
	var $Merchent='BB';
	var $APIURL;
	public function __construct(){	
		
	}
	
	public function getFormArray($order_no,$Amount,$Payment='ATM'){
		try{
			if($Payment=='ATM'){
				$this->APIURL='http://store.ufo.com.tw/VRACT/Gateway.asp';
			}else{
				$this->APIURL='http://store.ufo.com.tw/Paymain/Gateway.asp';
			}
			$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
			$HostUrl.=$_SERVER['HTTP_HOST'];
			$szHtml='<form id="AerForm" method="post" action="'.$this->APIURL.'">';
			$szHtml.='<input name="Merchent" type="hidden" value="'.$this->Merchent.'" />';
			$szHtml.='<input name="OrderID" type="hidden" value="'.$order_no.'" />';
			$szHtml.='<input name="Product" type="hidden" value="'.iconv("UTF-8","BIG5", '點數商品').'" />';
			$szHtml.='<input name="Total" type="hidden" value="'.$Amount.'" />';
			$szHtml.='<input name="Name" type="hidden" value="'.iconv("UTF-8","BIG5", '消費者').'" />';
			if($Payment=='CVS'){
				$szHtml.='<input name="Hour" type="hidden" value="24" />';
			}
		   	$szHtml.='<input name="ReAUrl" type="hidden" value="'.$HostUrl.'/module/Aercode.aspx'.'" />';	//代碼回傳URL
			$szHtml.='<input name="ReBUrl" type="hidden" value="'.$HostUrl.'/module/Aerresult.aspx'.'" />';	//交易結果回傳URL
			$szHtml.='</form>';
			$szHtml.='<script type="application/javascript">document.getElementById("AerForm").submit();</script>';
			print $szHtml;
			exit();
			die();
			flush();
			return NULL;
		}catch (Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	public function CheckOutFeedback(){
		$arErrors = array();
		$arFeedback=array();		
		foreach ($_POST as $keys => $value) {
			$arFeedback[$keys]=$value;	
		}
		if (sizeof($arFeedback) > 0) {
			$parameter=array(':MerchantID'=>$arFeedback['MerchantID']);
			$sqlStr="select * from `allpay` where `MerchantID`=".$arFeedback['MerchantID'];
			$row=$this->CI->webdb->sqlRow($sqlStr);
			if($row!=NULL){
				$this->pay->MerchantID=$row["MerchantID"];
				$this->pay->HashKey=$row["HashKey"];
				$this->pay->HashIV=$row["HashIV"];				
			}else{
				 array_push($arErrors, 'Pay type Error');
			}
		}
		if (sizeof($arErrors) > 0) {
			throw new Exception(join('- ', $arErrors));
		}
		return $this->pay->CheckOutFeedback();
	}
	
}
?>