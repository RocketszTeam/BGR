<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Getcode extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('spclass');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->spclass->CheckOutFeedback();
			$TradeInfo = $this->spclass->create_aes_decrypt($arFeedback['TradeInfo']);	//資料解密後轉陣列
			parse_str($TradeInfo, $arFeedback);
			if (sizeof($arFeedback) > 0) {
				if($arFeedback['Status']=='SUCCESS'){	//取號成功
					$parameter=array();
					$colSql="order_no,Status,Message,MerchantID,Amt,TradeNo,PaymentType,BankCode,CodeNo";
					$colSql.=",ExpireDate,ExpireTime";
					$sqlStr="INSERT INTO `sp_orders` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':order_no']=(@$arFeedback["MerchantOrderNo"]!="" ? @$arFeedback["MerchantOrderNo"] :NULL);
					$parameter[':Status']=(@$arFeedback["Status"]!="" ? @$arFeedback["Status"] :NULL);
					$parameter[':Message']=(@$arFeedback["Message"]!="" ? @$arFeedback["Message"] :NULL);
					$parameter[':MerchantID']=(@$arFeedback["MerchantID"]!="" ? @$arFeedback["MerchantID"] :NULL);
					$parameter[':Amt']=(@$arFeedback["Amt"]!="" ? @$arFeedback["Amt"] :NULL);
					$parameter[':TradeNo']=(@$arFeedback["TradeNo"]!="" ? @$arFeedback["TradeNo"] :NULL);
					$parameter[':PaymentType']=(@$arFeedback["PaymentType"]!="" ? @$arFeedback["PaymentType"] :NULL);
					$parameter[':BankCode']=(@$arFeedback["BankCode"]!="" ? @$arFeedback["BankCode"] :NULL);
					$parameter[':CodeNo']=(@$arFeedback["CodeNo"]!="" ? @$arFeedback["CodeNo"] :NULL);
					$parameter[':ExpireDate']=(@$arFeedback["ExpireDate"]!="" ? @$arFeedback["ExpireDate"] :NULL);
					$parameter[':ExpireTime']=(@$arFeedback["ExpireTime"]!="" ? @$arFeedback["ExpireTime"] :NULL);
					$this->webdb->sqlExc($sqlStr,$parameter);
					
					header("Location:".site_url("Manger/deposit_result?order_no=". @$arFeedback["MerchantOrderNo"]));
					exit;
				}
			}else{
				print '0|Fail';		
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
		
}
