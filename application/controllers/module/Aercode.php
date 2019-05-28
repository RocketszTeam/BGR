<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aercode extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('aerclass');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {

		try{
			$arFeedback = $this->input->get();
			if (sizeof($arFeedback) > 0) {
				$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
				$HostUrl.=$_SERVER['HTTP_HOST'];
				$parameter=array();
				$colSql="order_no,Payment,ACID,StoreCode,Total,buildtime";
				$sqlStr="INSERT INTO `aer_orders` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':order_no']=(isset($arFeedback["Ordernum"]) ? $arFeedback["Ordernum"] :NULL);
				$parameter[':Payment']=(isset($arFeedback['ACID']) ? 'ATM' : 'CVS');
				$parameter[':ACID']=(isset($arFeedback["ACID"]) ? $arFeedback["ACID"] :NULL);
				$parameter[':StoreCode']=(isset($arFeedback["StoreCode"]) ? $arFeedback["StoreCode"] :NULL);
				$parameter[':Total']=(isset($arFeedback["Total"]) ? $arFeedback["Total"] :NULL);
				$parameter[':buildtime']=now();
				$this->webdb->sqlExc($sqlStr,$parameter);
				header("Location:".$HostUrl.'/Manger/deposit_result.aspx?order_no='.$arFeedback["Ordernum"]);
				exit;
			}else{
				print '0|Fail';		
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
		
}
