<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
class Htpay extends Gateway_tpl {
	public function __construct(){
	    parent::__construct();
	}
	public function getData($orderNo, $amount, $member = NULL){
        $data['StoreID'] = $this->merchant;
        $data['Amount'] = $amount;
        $data['PayInfo'] = self::ITEM_NAME;
        $data['StoreOrderId'] = $orderNo;
        $data['PayName'] = (isset($member['name'])) ? $member['name'] : 'User';
        $data['PayPhone'] = (isset($member['phone'])) ? $member['phone'] : '0900000'.rand(100,999);
        $data['PayEmail'] = (isset($member['email'])) ? $member['email'] : 'payment'.rand(10,999).'@gmail.com';
        //$data['ExpireDate'] = date('Ymd',time() + (5 * 24 * 60 * 60));//5天後付款
        $data["ExpireDate"] = 5;//5天後過期
        $data['Chksum'] = $this->sendValidate($data);
        $data['ReturnURL'] = $this->getPayResultURL();
        return $data;
    }
	public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '013'); // 指定收單銀行，空值代表不指定。013:國泰世華  812:台新銀行  822:中國信託
	    $data = $this->getData($orderNo, $amount, $member);
        $data['PayMethod'] = 'VBANK';
        $data['Bankid'] = '013';
        $this->submit($this->apiUrl.'gateway.php', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['PayMethod'] = 'CSPM';
        $data['StoreType']='5';
        switch ($type){
            case 'FAMI':
                $data['StoreType']='4';
                break;
            case 'OKGO':
                $data['StoreType']='6';
                break;
            case 'HILIFEET':
                $data['StoreType']='7';
                break;
            case 'IBON':
                $data['StoreType']='5';
                break;
        }
        $this->submit($this->apiUrl.'gateway.php', $data);
    }
    public function sendValidate($data = NULL){
        //Chksum = md5(StoreID+交易密碼+Amount)
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

    public function getCode($data){
        $code = NULL;
        if(isset($data['CSPayCode']))$code = $data['CSPayCode'];
        elseif(isset($data['VBankAccount']))$code = $data['VBankAccount'];
        return $code;
    }
    public function getType($data){
        $type = NULL;
        if(isset($data['PayType'])){
            switch($data['PayType']){
                case '4':
                    $type = 'FAMI';
                    break;
                case '5':
                    $type = 'IBON';
                    break;
                case '6':
                    $type = 'OKGO';
                    break;
                case '7':
                    $type = 'HILIFEET';
                    break;
            }
        }
        elseif(isset($data['VBankCode']))$type = $data['VBankCode'];
        return $type;
    }
    public function getMsgSuccess(){
        return '0000';
    }
}
?>