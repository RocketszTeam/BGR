<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//幣安
class Cgpay extends Gateway_tpl {
    public function __construct(){
        parent::__construct();
    }
    public function getData($orderNo, $amount, $member = NULL){
        $data['Merchent'] = $this->merchant;
        $data['OrderID'] = $orderNo;
        $data['Total'] = $amount;
        $data['Name'] = (isset($member['id'])) ? $member['id'] : 'User';
        $data['Product'] = self::ITEM_NAME;
        $data['MSG'] = self::ITEM_DESC;
        return $data;
    }
    public function creditPay($orderNo, $amount, $member = NULL){	//信用卡
        $this->saveData($orderNo, 'Credit', NULL);
        $data = $this->getData($orderNo, $amount, $member);
        $data['BGConfirmUrl'] = $this->getPayResultURL();

        $this->submit($this->apiUrl.'api/getway03/auto_gate.ashx', $data);
    }
    public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '012');
        $data = $this->getData($orderNo, $amount, $member);
        $data['ReAUrl'] =$this->getCodeURL();
        $data['ReBUrl'] = $this->getPayResultURL();
        $this->submit($this->apiUrl.'api/getway02/VracRequest.ashx', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['ReAUrl'] = $this->getCodeURL();
        $data['ReBUrl'] = $this->getPayResultURL();
        $this->submit($this->apiUrl.'api/getway01/CodeRequest.ashx', $data);
    }
    public function checkValidate($data = NULL){
        $flag = FALSE;
        if(!empty($data['Status'])){
            if($data['Status'] == '0000')$flag = TRUE;
        }
        if(!empty($data['FinalCode'])){
            if($data['FinalCode'] == '0000')$flag = TRUE;
        }
        return $flag;
    }
    public function getOrderNo($data){
        return $data["Ordernum"];
    }
    public function getAmount($data){
        return $data["Total"];
    }
    public function getCode($data){
        $code = NULL;
        if(isset($data['StoreCode']))$code = $data['StoreCode'];
        elseif(isset($data['ACID']))$code = $data['ACID'];
        return $code;
    }
}
?>