<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//Gifubow
class Gifubow extends Gateway_tpl {
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
    //http://www.gifubow.com/api/getway05/VracRequest.ashx
    public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '809');  //809凱基 0692市政分行
        $data = $this->getData($orderNo, $amount, $member);
        $this->submit($this->apiUrl.'api/getway05/VracRequest.ashx', $data);
    }
    //http://www.gifubow.com/api/getway01/CodeRequest.ashx
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $this->submit($this->apiUrl.'api/getway01/CodeRequest.ashx ', $data);
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