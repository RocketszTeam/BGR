<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Getcode extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('payclass');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {

		try{
			$arFeedback = $this->payclass->CheckOutFeedback();
			if (sizeof($arFeedback) > 0) {
				$parameter=array();
				$colSql="MerchantID,order_no,RtnCode,RtnMsg,TradeNo,TradeAmt,PaymentType,TradeDate,BankCode";
				$colSql.=",vAccount,PaymentNo,ExpireDate";
				$sqlStr="INSERT INTO `allpay_orders` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':MerchantID']=(@$arFeedback["MerchantID"]!="" ? @$arFeedback["MerchantID"] :NULL);
				$parameter[':order_no']=(@$arFeedback["MerchantTradeNo"]!="" ? @$arFeedback["MerchantTradeNo"] :NULL);
				$parameter[':RtnCode']=(@$arFeedback["RtnCode"]!="" ? @$arFeedback["RtnCode"] :NULL);
				$parameter[':RtnMsg']=(@$arFeedback["RtnMsg"]!="" ? @$arFeedback["RtnMsg"] :NULL);
				$parameter[':TradeNo']=(@$arFeedback["TradeNo"]!="" ? @$arFeedback["TradeNo"] :NULL);
				$parameter[':TradeAmt']=(@$arFeedback["TradeAmt"]!="" ? @$arFeedback["TradeAmt"] :NULL);
				$parameter[':PaymentType']=(@$arFeedback["PaymentType"]!="" ? @$arFeedback["PaymentType"] :NULL);
				$parameter[':TradeDate']=(@$arFeedback["TradeDate"]!="" ? @$arFeedback["TradeDate"] :NULL);
				$parameter[':BankCode']=(@$arFeedback["BankCode"]!="" ? @$arFeedback["BankCode"] :NULL);
				$parameter[':vAccount']=(@$arFeedback["vAccount"]!="" ? @$arFeedback["vAccount"] :NULL);
				$parameter[':PaymentNo']=(@$arFeedback["PaymentNo"]!="" ? @$arFeedback["PaymentNo"] :NULL);
				$parameter[':ExpireDate']=(@$arFeedback["ExpireDate"]!="" ? @$arFeedback["ExpireDate"] :NULL);				
				$this->webdb->sqlExc($sqlStr,$parameter);
				print '1|OK';
			}else{
				print '0|Fail';		
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
		
}
