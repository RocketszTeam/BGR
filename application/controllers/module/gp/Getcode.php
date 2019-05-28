<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Getcode extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('gpclass');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {

		try{
			foreach ($_POST as $keys => $value) {
				$arFeedback[$keys]=$value;	
			}		
			if (sizeof($arFeedback) > 0) {
				$parameter=array();
				$colSql="OrderType,order_no,OrderID,e_payaccount,LimitDate,code1,code2,code3,";
				$colSql.="PayAmount,Status,PayDate";
				$sqlStr="INSERT INTO `gp_orders` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':OrderType']=(@$arFeedback["OrderType"]!="" ? @$arFeedback["OrderType"] :NULL);
				$parameter[':order_no']=(@$arFeedback["CustomerOrderID"]!="" ? @$arFeedback["CustomerOrderID"] :NULL);
				$parameter[':OrderID']=(@$arFeedback["OrderID"]!="" ? @$arFeedback["OrderID"] :NULL);
				
				$parameter[':e_payaccount']=(@$arFeedback["e_payaccount"]!="" ? @$arFeedback["e_payaccount"] :NULL);
				$parameter[':LimitDate']=(@$arFeedback["LimitDate"]!="" ? @$arFeedback["LimitDate"] :NULL);
				$parameter[':code1']=(@$arFeedback["code1"]!="" ? @$arFeedback["code1"] :NULL);
				$parameter[':code2']=(@$arFeedback["code2"]!="" ? @$arFeedback["code2"] :NULL);
				$parameter[':code3']=(@$arFeedback["code3"]!="" ? @$arFeedback["code3"] :NULL);
				$parameter[':PayAmount']=(@$arFeedback["PayAmount"]!="" ? @$arFeedback["PayAmount"] :NULL);
				$parameter[':Status']=(@$arFeedback["Status"]!="" ? @$arFeedback["Status"] :NULL);
				$parameter[':PayDate']=(@$arFeedback["PayDate"]!="" ? @$arFeedback["PayDate"] :NULL);			
				$this->webdb->sqlExc($sqlStr,$parameter);
				//echo $sqlStr;
				
				print '';//1|OK
			}else{
				print '0|Fail';		
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
		
}
