<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//金恆通
class Ufopay extends Gateway_tpl {
    public function __construct(){
        parent::__construct();
    }
    public function getData($orderNo, $amount, $member = NULL){
        $data['Merchent'] = $this->merchant;
        $data['OrderID'] = $orderNo;
        $data['Product'] = self::ITEM_NAME;
        $data['Total'] = $amount;
        $data['Name'] = (isset($member['name'])) ? $member['name'] : 'User';
        $data['MSG'] = self::ITEM_DESC;
        $data['ReAurl'] = $this->getCodeURL();
        $data['ReBurl'] = $this->getPayResultURL();
        return $data;
    }
    public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '012');
        $data = $this->getData($orderNo, $amount, $member);
        $this->submit($this->apiUrl.'VRACT/Gateway.asp', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $this->submit($this->apiUrl.'Paymain/Gateway.asp', $data);
    }
    public function checkValidate($data = NULL){
        return TRUE;
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