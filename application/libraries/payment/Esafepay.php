<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//紅陽
class Esafepay extends Gateway_tpl {
    public function __construct(){
        parent::__construct();
    }
    public function getData($orderNo, $amount, $member = NULL){
        $data['web'] = $this->merchant;
        $data['MN'] = $amount;
        $data['OrderInfo'] = self::ITEM_DESC;
        $data['Td'] = $orderNo;
        $data['sna'] = (isset($member['id'])) ? $member['id'] : 'User';
        $data['sdt'] =  (isset($member['phone'])) ? $member['phone'] : '0900000000';
        $data['ProductName1'] = self::ITEM_NAME;
        $data['ProductPrice1'] = $amount;
        $data['ProductQuantity1'] = 1;
        $data['ChkValue'] = $this->sendValidate($data);
        return $data;
    }
    public function creditPay($orderNo, $amount, $member = NULL){	//信用卡
        $this->saveData($orderNo, 'Credit', NULL);
        $data = $this->getData($orderNo, $amount, $member);
        $data['Card_Type'] = '0';

        $this->submit($this->apiUrl.'Service/Etopm.aspx', $data);
    }
    public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '000');
        $data = $this->getData($orderNo, $amount, $member);
        $data['AgencyType'] = 2;
        $data['DueDate'] = date('Ymd', strtotime('+3 days'));
        $this->submit($this->apiUrl.'Service/Etopm.aspx', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['AgencyType'] = 1;
        $data['DueDate'] = date('Ymd', strtotime('+3 days'));
        $this->submit($this->apiUrl.'Service/Etopm.aspx', $data);
    }

    public function sendValidate($data = NULL){
        $validate = $this->merchant.$this->hashKey.$data['MN'];
        return strtoupper(sha1($validate));
    }
    public function receiveValidate($data = NULL){
        $validate = $data['web'].$this->hashKey.$data['buysafeno'].$data['MN'].$data['errcode'];
        return sha1($validate);
    }
    public function checkValidate($data = NULL){
        $flag = FALSE;
        if($this->receiveValidate($data) == $data['ChkValue'] && $data['errcode'] == '00')$flag = TRUE;
        return $flag;
    }
    public function getOrderNo($data){
        return $data["Td"];
    }
    public function getAmount($data){
        return $data["MN"];
    }
    public function getCode($data){
        $code = NULL;
        if(isset($data['paycode']) && !empty($data['paycode']))$code = $data['paycode'];
        elseif(isset($data['EntityATM']) && !empty($data['EntityATM']))$code = $data['EntityATM'];
        return $code;
    }
    public function getType($data){
        $type = NULL;

        if(isset($data['PayType']) && !empty($data['PayType'])){
            $str = explode(',', $data['PayType']);
            foreach($str as $k => $v){
                $t = $v;
                break;
            }
            switch($t){
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
        elseif(isset($data['BankCode']) && !empty($data['BankCode']))$type = substr($data['BankCode'],0,3);
        return $type;
    }
}
?>