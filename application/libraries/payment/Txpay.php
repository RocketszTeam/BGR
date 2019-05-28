<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//鈦鑫
class Txpay extends Gateway_tpl {
	public function __construct(){
	    parent::__construct();
	}
	public function getData($orderNo, $amount, $member = NULL){
        $data['StoreID'] = $this->merchant;
        $data['Amount'] = $amount;
        $data['Currency'] = 'TWD';
        $data['PayInfo'] = self::ITEM_NAME;
        $data['StoreOrderId'] = $orderNo;
        $data['PayName'] = (isset($member['name'])) ? $member['name'] : 'User';
        $data['PayPhone'] = (isset($member['phone'])) ? $member['phone'] : '0900000000';
        $data['ExpireDate'] = date('Ymd',time() + (5 * 24 * 60 * 60));//5天後付款
        $data['Chksum'] = $this->sendValidate($data);
        $data['ReturnURL'] = $this->getPayResultURL();
        return $data;
    }
	public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '822'); // 007 812 822
	    $data = $this->getData($orderNo, $amount, $member);
        $data['PayMethod'] = 'VBANK';
        $this->submit($this->apiUrl.'api3/gateway.php', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['PayMethod'] = 'CSPM';
        $this->submit($this->apiUrl.'api3/gateway.php', $data);
    }
    public function sendValidate($data = NULL){
        $validate = $this->merchant.$this->hashKey.$data['Amount'];
        return md5($validate);
    }
    public function receiveValidate($data = NULL){
        $validate = $data['StoreID'].$this->hashKey.$data['id'].$data['Amount'].$data['errorcode'];
        return md5($validate);
    }
    public function checkValidate($data = NULL){
        $flag = FALSE;
        if($this->receiveValidate($data) == $data['Chksum'] && $data['errorcode'] == '00')$flag = TRUE;
        return $flag;
    }
    public function getOrderNo($data){
        return $data["StoreOrderId"];
    }
    public function getAmount($data){
        return $data["Amount"];
    }
    public function getMsgSuccess(){
        return '0000';
    }
}
?>