<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
class Mycashpay extends Gateway_tpl {
	public function __construct(){
        parent::__construct();
	}
	public function getData($orderNo, $amount, $member = NULL){
        $data['HashKey'] = $this->hashKey;
        $data['HashIV'] = $this->hashIV;
        $data['MerTradeID'] = $orderNo;
        $data['MerProductID'] = self::ITEM_NAME;
        $data['MerUserID'] = (isset($member['id'])) ? $member['id'] : 'User';
        $data['Amount'] = $amount;
        $data['TradeDesc'] = self::ITEM_DESC;
        $data['ItemName'] = self::ITEM_NAME;
        $data['Validate'] = $this->sendValidate($data);
        return $data;
    }
    public function creditPay($orderNo, $amount, $member = NULL){	//信用卡
        $this->saveData($orderNo, 'Credit', NULL);
        $data = $this->getData($orderNo, $amount, $member);
        $data['UnionPay'] = '0';

        $this->submit($this->apiUrl.'CreditPayment.php', $data);
	}
	public function atmPay($orderNo, $amount, $type = 'ATM', $member = NULL){
        $this->saveData($orderNo, 'ATM', $type);
	    $data = $this->getData($orderNo, $amount, $member);
        $this->submit($this->apiUrl.'VirAccountPayment.php', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['ChoosePayment'] = $type;
        $this->submit($this->apiUrl.'StorePayment.php', $data);
    }
    public function sendValidate($data = NULL){
        $validate = $this->validate.$this->hashKey.$data['MerTradeID'].$data['MerProductID'].$data['MerUserID'].$data['Amount'];
        return md5($validate);
    }
    public function receiveValidate($data = NULL){
        $validate = "ValidateKey=".$this->validate."&HashKey=".$this->hashKey."&RtnCode=".$data['RtnCode']."&TradeID=".$data['MerTradeID']."&UserID=".$data['MerUserID']."&Money=".$data['Amount'];

        $this->saveLog($validate);
        return strtoupper(md5($validate));
    }
    public function checkValidate($data = NULL){
        $flag = FALSE;
        if($this->receiveValidate($data) == strtoupper($data['Validate']) && $data['RtnCode'] == '1')$flag = TRUE;
        return $flag;
    }
    public function getOrderNo($data){
        return $data["MerTradeID"];
    }
    public function getAmount($data){
        return $data["Amount"];
    }
    public function getCode($data){
        return (!empty($data['VatmAccount'])) ? $data['VatmAccount'] : $data['CodeNo'];
    }
    public function getType($data){
	    return (!empty($data['VatmBankCode'])) ? $data['VatmBankCode'] : NULL;
    }
}
?>