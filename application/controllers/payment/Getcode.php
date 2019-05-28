<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(dirname(__FILE__))."/Core_controller.php");
class Getcode extends CI_Controller {
	function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}
	public function index($gatewayName = NULL) {
		try{
            if(!empty($gatewayName)) {
                $this->load->library('payment/' . $gatewayName, '', 'gateway');
                $arFeedback = $this->gateway->receiveData();
                if (sizeof($arFeedback) > 0) {
                    $where = "WHERE `order_no`='" . $this->gateway->getOrderNo($arFeedback)."'";

                    $upSql = "UPDATE `payment_order` SET `code`='".$this->gateway->getCode($arFeedback)."' ";
                    $upSql .= $where;
                    $this->webdb->sqlExc($upSql);
                    if(!empty($this->gateway->getType($arFeedback))){
                        $upSql = "UPDATE `payment_order` SET `type`='".$this->gateway->getType($arFeedback)."' ";
                        $upSql .= $where;
                        $this->webdb->sqlExc($upSql);
                    }
                    header("Location:".site_url("Manger/deposit_result?order_no=". $this->gateway->getOrderNo($arFeedback)));
                    ///////////////////////////////////////////////////跳轉
                    exit();
                }else {
                    print '0|Fail';
                }
            }
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
}
