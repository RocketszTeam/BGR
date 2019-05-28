<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//綠界
class Ecpay extends Gateway_tpl {
    public function __construct(){
        parent::__construct();
        require_once('ECPayAIO/ECPay.Payment.Integration.php');
    }
    public function getData($orderNo, $amount, $member = NULL){
        $data['MerchantID'] = $this->merchant;
        $data['MerchantTradeNo'] = $orderNo;
        $data['MerchantTradeDate'] = date('Y/m/d H:i:s');
        $data['TotalAmount'] = $amount;
        $data['TradeDesc'] = self::ITEM_DESC;
        $data['ItemName'] =  self::ITEM_NAME;
        $data['ReturnURL'] = $this->getPayResultURL();
        $data['EncryptType'] = 1;
        $data['PaymentType'] = 'aio';
        $data['NeedExtraPaidInfo'] = 'Y';
        return $data;
    }
    public function creditPay($orderNo, $amount, $member = NULL){	//信用卡
        $this->saveData($orderNo, 'Credit', NULL);
        $data = $this->getData($orderNo, $amount, $member);
        $data['ChoosePayment'] = 'Credit';
        $data['CheckMacValue'] = $this->sendValidate($data);
        $this->submit($this->apiUrl.'Cashier/AioCheckOut/V5', $data);
    }
    public function atmPay($orderNo, $amount, $type = 'ESUN', $member = NULL){
        $bankCode = '808';
        switch($type){
            case 'TAISHIN': //台新
                $bankCode = '812';
            break;
            case 'BOT': //台銀
                $bankCode = '004';
            break;
            case 'FUBON': //富邦
                $bankCode = '012';
            break;
            case 'CHINATRUST': //中信
                $bankCode = '822';
            break;
            case 'FIRST': //第一
                $bankCode = '812';
            break;
            case 'LAND': //土地
                $bankCode = '005';
            break;
            case 'CATHAY': //國泰
                $bankCode = '013';
            break;
            case 'TACHONG': //大眾銀行
                $bankCode = '814';
            break;
        }
        $this->saveData($orderNo, 'ATM', $bankCode);
        $data = $this->getData($orderNo, $amount, $member);
        $data['ChoosePayment'] = 'ATM';
        $data['ChooseSubPayment'] = 'ESUN';
        $data['PaymentInfoURL'] = $this->getCodeURL();
        $data['CheckMacValue'] = $this->sendValidate($data);
        $this->submit($this->apiUrl.'Cashier/AioCheckOut/V5', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['ChoosePayment'] = 'CVS';
        $data['PaymentInfoURL'] = $this->getCodeURL();
        $data['CheckMacValue'] = $this->sendValidate($data);
        $this->submit($this->apiUrl.'Cashier/AioCheckOut/V5', $data);
    }

    public function sendValidate($data = NULL){
        return ECPay_CheckMacValue::generate($data, $this->hashKey, $this->hashIV, $data['EncryptType']);
    }
    public function receiveValidate($data = NULL){
        unset($data['CheckMacValue']);
        return ECPay_CheckMacValue::generate($data, $this->hashKey, $this->hashIV, 1);
    }
    public function checkValidate($data = NULL){
        $flag = FALSE;
        if($this->receiveValidate($data) == $data['CheckMacValue'] && $data['RtnCode'] == 1)$flag = TRUE;
        return $flag;
    }
    public function getOrderNo($data){
        return $data["MerchantTradeNo"];
    }
    public function getAmount($data){
        return $data["TradeAmt"];
    }
    public function getCode($data){
        $code = NULL;
        if(isset($data['PaymentNo']))$code = $data['PaymentNo'];
        elseif(isset($data['vAccount']))$code = $data['vAccount'];
        return $code;
    }
    public function getMsgSuccess(){
        return '1|OK';
    }
}
?>