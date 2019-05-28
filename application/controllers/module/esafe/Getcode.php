<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Getcode extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('esafe');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->esafe->CheckCodeFeedback();
			if (sizeof($arFeedback) > 0) {
				$parameter=array();
				$parameter['buysafeno']=$arFeedback['buysafeno'];
				$parameter['order_no']=$arFeedback['Td'];
				$parameter['web']=$arFeedback['web'];
				$parameter['MN']=$arFeedback['MN'];
				$parameter['Payment']=(@$arFeedback['paycode']!="" ? 'CVS' : 'ATM');
				$parameter['SendType']=$arFeedback['SendType'];
				$parameter['paycode']=(isset($arFeedback['paycode']) ? $arFeedback['paycode'] : NULL);
				$parameter['PayType']=(isset($arFeedback['PayType']) ? $arFeedback['PayType'] : NULL);
				$parameter['BankCode']=(isset($arFeedback['BankCode']) ? $arFeedback['BankCode'] : NULL);
				$parameter['EntityATM']=(isset($arFeedback['EntityATM']) ? $arFeedback['EntityATM'] : NULL);
				$parameter['ChkValue']=$arFeedback['ChkValue'];
				$parameter['DueDate']=date('Y-m-d',strtotime(now()."+1 days"));
				$parameter['buildtime']=now();
				$this->webdb->sqlReplace('esafe_orders',$parameter);
				//print_r($arFeedback);exit;
				header("Location:".site_url("Manger/deposit_result?order_no=".$arFeedback['Td']));
				print '1|OK';
				exit;
			}else{
				print '0|Fail';
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
		
}
