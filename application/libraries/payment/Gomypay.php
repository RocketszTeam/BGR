<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//萬事達
class Gomypay extends Gateway_tpl {
    public function __construct(){
        parent::__construct();
    }
    public function getData($orderNo, $amount, $member = NULL){
        $data["Pay_Mode_No"] = 2;
        $data['CustomerId'] = $this->merchant;
        $data['Order_No'] = $orderNo;
        $data['Amount'] = $amount;

        $data['Buyer_Name'] = (isset($member['name'])) ? $member['name'] : 'User';
        $data['Buyer_Telm'] =  (isset($member['phone'])) ? $member['phone'] : '0900000000';
        $data['Buyer_Mail'] =  (isset($member['mail'])) ? $member['mail'] : 'paymoney159@gmail.com';
        $data['Buyer_Memo'] = self::ITEM_DESC;
        $data["Callback_Url"] = $this->getPayResultURL();//對帳網址
        $data["Return_url"] = $this->getCodeURL(); //超商 or ATM 取號後 回傳網址
        return $data;
    }
    public function creditPay($orderNo, $amount, $member = NULL){	//信用卡
        $this->saveData($orderNo, 'Credit', NULL);
        $data = $this->getData($orderNo, $amount, $member);
        $data['Send_Type'] = '0';
        $data['TransCode'] = '00';
        $data['TransMode'] = '1';
        $data['Installment'] = '0';
        $this->submit($this->apiUrl.'ShuntClass.aspx', $data);
    }
    public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '013');
        $data = $this->getData($orderNo, $amount, $member);
        $data['Send_Type'] = '4';
        $this->submit($this->apiUrl.'ShuntClass.aspx', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'IBON', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['Send_Type'] = '6';
        $data['StoreType']='3';
        switch ($type){
            case 'FAMI':
                $data['StoreType']='0';
            break;
            case 'OKGO':
                $data['StoreType']='1';
                break;
            case 'HILIFEET':
                $data['StoreType']='2';
            break;
            case 'IBON':
                $data['StoreType']='3';
            break;
        }
        $this->submit($this->apiUrl.'ShuntClass.aspx', $data);
    }
    public function checkValidate($data = NULL){
        $flag = FALSE;
        if($data['result'] == '1' && $data['e_money'] == $data['PayAmount'])$flag = TRUE;
        return $flag;
    }
    public function getOrderNo($data){
        return $data["e_orderno"];
    }
    public function getAmount($data){
        return $data["PayAmount"];
    }
    public function getCode($data){
        $code = NULL;
        if(isset($data['PinCode']))$code = $data['PinCode'];
        elseif(isset($data['e_payaccount'])){
            $str = explode('-', $data['e_payaccount']);
            $code = $str[2];
        }
        return $code;
    }
    public function getMsgSuccess(){
        return 'SUCCESS';
    }
}

?>