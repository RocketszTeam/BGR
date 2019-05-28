<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Esafe {
	var $CI;
	var $web;	//商家代號（超商代收（代碼））（可登入商家專區至「服務設定」中查詢paycode服務的代碼）
	var $transPassword='O3IsEOE9O8R8G1iS';	//交易密碼（可登入商家專區至「密碼修改」處設定，此密碼非後台登入密碼）
	var $paymentURL='https://www.esafe.com.tw/Service/Etopm.aspx';
	
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	private function setWeb($Payment){
		if($Payment=='CVS')	{	//超商代碼繳費
			$this->web='S1801170331';
		}else{	//虛擬ATM帳號
			$this->web='S1801170356';
		}
	}
	
	public function getFormArray($order_no,$Amount,$Payment='CVS',$target="_self",$paymentButton=NULL){
		$this->setWeb($Payment);
		$post_data=array();
		$post_data['web']=$this->web;	//商家編號
		$post_data['MN']=$Amount;	//交易金額
		$post_data['Td']=$order_no;	//商家訂單編號
		$post_data['sna']=urlencode('消費者'.time());//消費者姓名有中文需要做urlencode
		$post_data['sdt']='0910123456';	//消費者電話
		$post_data['ProductName1'] = urlencode('網路銷售商品'); //產品名稱
		$post_data['ProductPrice1'] = $Amount; //產品單價
		$post_data['ProductQuantity1'] = 1; //產品數量
		$post_data['DueDate']=date('Ymd', strtotime("+1 days")); //繳款期限，最長 180 天(YYYYMMDD)
		$post_data['CargoFlag']=0;
		if($Payment=='ATM'){
			$post_data=array_merge($post_data,array('AgencyType'=>2,'AgencyBank'=>1));
			//$AgencyType = 1; //1 條碼、2 虛擬帳號
		}
		$ChkValue=$this->getChkValue($this->web.$this->transPassword.$Amount);//交易檢查碼 
		$post_data['ChkValue']=$ChkValue;
		
		$szHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$szHtml .= '<form id="__EsafeForm" method="post" target="' . $target . '" action="' . $this->paymentURL . '">';
		foreach ($post_data as $keys => $value) {
			$szHtml .="<input type='hidden' name='$keys' value='$value' />";
		}
		// 手動或自動送出表單。
		if (!isset($paymentButton)) {
			$szHtml .= '<script type="text/javascript">document.getElementById("__EsafeForm").submit();</script>';
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
	
	
	public function CheckCodeFeedback(){	//檢查取號結果
        // 變數宣告。
        $arErrors = array();
        $arFeedback = array();
        $szCheckMacValue = '';
		// 重新整理回傳參數。
		foreach ($_POST as $keys => $value) {
			if ($keys == 'ChkValue') {
				$szCheckMacValue = $value;	
				
			}
			$arFeedback[$keys] = $value;
		}
		// 驗證檢查碼。
		if (sizeof($arFeedback) > 0) {
			if(!empty($arFeedback['paycode'])){	//代碼繳費
				$this->setWeb('CVS');
				$szConfirmMacValue=$this->getChkValue($this->web.$this->transPassword.$arFeedback['buysafeno'].$arFeedback['MN'].$arFeedback['paycode']);
			}else{	//虛擬帳號
				$this->setWeb('ATM');
				$szConfirmMacValue=$this->getChkValue($this->web.$this->transPassword.$arFeedback['buysafeno'].$arFeedback['MN'].$arFeedback['EntityATM']);
			}
			if($szCheckMacValue!=$szConfirmMacValue){
				array_push($arErrors, 'CheckMacValue verify fail.');
			}
		}
        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }
        return $arFeedback;
	}
	
	
	public function CheckOutFeedback(){	//檢查繳費結果
        // 變數宣告。
        $arErrors = array();
        $arFeedback = array();
        $szCheckMacValue = '';
		// 重新整理回傳參數。
		foreach ($_POST as $keys => $value) {
			if ($keys == 'ChkValue') {
				$szCheckMacValue = $value;	
				
			}
			$arFeedback[$keys] = $value;			
		}
		// 驗證檢查碼。
		if (sizeof($arFeedback) > 0) {
			if($arFeedback['PayType']!='3'){	//代碼繳費
				$this->setWeb('CVS');
			}else{	//虛擬帳號
				$this->setWeb('ATM');
			}
			$szConfirmMacValue=$this->getChkValue($this->web.$this->transPassword.$arFeedback['buysafeno'].$arFeedback['MN'].$arFeedback['errcode'].$arFeedback['CargoNo']);
			if($szCheckMacValue!=$szConfirmMacValue){
				array_push($arErrors, 'CheckMacValue verify fail.');
			}
		}
        if (sizeof($arErrors) > 0) {
            throw new Exception(join('- ', $arErrors));
        }
        return $arFeedback;
	}
	
	
	public function getChkValue($string){
		return strtoupper(sha1($string));
	}	
}
?>