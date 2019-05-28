<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Payclass {
	var $pay;
	var $CI;
	var $onLine=1;	//1=正式環境;0=測試環境
	public function __construct(){		
		require_once('allpay/AllPay.Payment.Integration.php');
		date_default_timezone_set("Asia/Taipei");
		$this->CI=&get_instance();
		$this->pay=new AllInOne;
		$sqlStr="select MerchantID,HashKey,HashIV from `allpay` where `active`='Y'";
		$row=$this->CI->webdb->sqlRow($sqlStr);
		if($row!=NULL && $this->onLine==1){
			if($row["MerchantID"]!="" && $row["HashKey"]!="" && $row["HashIV"]!=""){//若有設定歐付寶資料則為正式環境
				$this->pay->ServiceURL='https://payment.ecpay.com.tw/Cashier/AioCheckOut';
				$this->pay->MerchantID=$row["MerchantID"];
				$this->pay->HashKey=$row["HashKey"];
				$this->pay->HashIV=$row["HashIV"];
			}
		}else{
			/* 測試環境相關資訊 */
			/* 信用卡測試卡號：4311-9522-2222-2222  */
			/* 信用卡測試安全碼：222  */
			/* 信用卡測試有效年月：請設定大於測試時間的年月  */
			/* 測試後台：https://vendor-stage.ecpay.com.tw/  帳密資訊：StageTest/test1234 */
			$this->pay->ServiceURL='https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut';
			$this->pay->MerchantID='2000132';
			$this->pay->HashKey='5294y06JbISpM5x9';
			$this->pay->HashIV='v77hoKGq4kWxNNIS';
		}
	}
	
	public function load_config($m_group){
		$sqlStr="select * from `allpay` where `m_group`=? and `active`='Y' order by rand() Limit 1";
		$parameter=array(':m_group'=>$m_group);
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row!=NULL){
			$this->pay->MerchantID=$row["MerchantID"];
			$this->pay->HashKey=$row["HashKey"];
			$this->pay->HashIV=$row["HashIV"];
		}else{
			return '付款方式尚未設定';	
		}
	}


	
	public function getFormArray($order_no,$Amount,$Payment=''){
		try{
			$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
			$HostUrl.=$_SERVER['HTTP_HOST'];
			$now=date('Y/m/d H:i:s');
			switch ($Payment){	//設定付費方式
				case 'ATM':
					$this->pay->Send['ChoosePayment'] = PaymentMethod::ATM;
					$this->pay->Send['ChooseSubPayment']=PaymentMethodItem::ATM_ESUN; //玉山銀行
					$this->pay->SendExtend['PaymentInfoURL']=$HostUrl.'/module/Getcode.aspx';
					$this->pay->SendExtend['ClientRedirectURL']=$HostUrl.'/Manger/deposit_result.aspx?order_no='.$order_no;
					$this->pay->SendExtend['ExpireTime']='';	//繳費期限
					 break;	
				case 'CVS':
					$this->pay->Send['ChoosePayment'] = PaymentMethod::CVS; 
					$this->pay->Send['ChooseSubPayment']=PaymentMethodItem::CVS;
					$this->pay->SendExtend['PaymentInfoURL']=$HostUrl.'/module/Getcode.aspx';
					$this->pay->SendExtend['ClientRedirectURL']=$HostUrl.'/Manger/deposit_result.aspx?order_no='.$order_no;
					break;
				default:	//預設值刷卡
					$this->pay->Send['ChoosePayment'] = PaymentMethod::Credit;
					break;		 
			}
	  		
		   $this->pay->Send['NeedExtraPaidInfo']='Y';
		   $this->pay->Send['ClientBackURL']=$HostUrl.'/index.php?kanpos=buy&do=allpay_pay&order_no='.$order_no;	
		   $this->pay->Send['ReturnURL'] = $HostUrl.'/module/Payresult.aspx';	//交易結果付款完成用
		   $this->pay->Send['OrderResultURL'] = $HostUrl.'/index.php?kanpos=buy&do=allpay_pay&order_no='.$order_no;
		   $this->pay->Send['MerchantTradeNo']=$order_no;
		   $this->pay->Send['MerchantTradeDate'] = $now;
		   $this->pay->Send['TotalAmount']= (int)$Amount;
		   $this->pay->Send['TradeDesc'] = "<<您該筆訂單的描述>>"; 
		   $this->pay->Send['ItemName']='商品';
		   //$this->Send['Items']=array('Name'=>'商品','Price'=>(int)$Amount,'Currency'=>'TWD','Quantity'=>1);
		   $this->pay->CheckOut(); 
		   //echo  $this->CheckOutString(); 
		   $szHtml = $this->pay->CheckOutString();
		   
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