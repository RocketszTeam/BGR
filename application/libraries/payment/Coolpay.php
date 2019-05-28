<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//湘鈞
class Coolpay extends Gateway_tpl {
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

        $this->submit($this->apiUrl.'Payment/Credit.php', $data);
	}
	public function atmPay($orderNo, $amount, $type = 'CTCB', $member = NULL){
	    $bankCode = '822'; //中國信託
	    switch ($type){
            case 'SCSB':
                $bankCode = '011'; //上海商銀
            break;
            case 'HNCB':
                $bankCode = '008'; //華南
            break;
            case 'POST':
                $bankCode = '700'; //郵局
            break;
        }
        $this->saveData($orderNo, 'ATM', $bankCode);
	    $data = $this->getData($orderNo, $amount, $member);
        $data['Type'] = $type;
        $this->submit($this->apiUrl.'Payment/ATM.php', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'CVS', $member = NULL){
        $this->saveData($orderNo, 'CVS', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $data['Type'] = $type;
        $this->submit($this->apiUrl.'Payment/Store.php', $data);
    }
    public function sendValidate($data = NULL){
        $validate = $this->validate.$this->hashKey.$data['MerTradeID'].$data['MerProductID'].$data['MerUserID'].$data['Amount'];
        return md5($validate);
    }
    public function receiveValidate($data = NULL){
        $validate = $this->validate.$this->hashKey.$data['RtnCode'].$data['MerTradeID'].$data['MerProductID'].$data['MerUserID'].$data['Amount'];
        return md5($validate);
    }
    public function checkValidate($data = NULL){
        $flag = FALSE;
        if($this->receiveValidate($data) == $data['Validate'] && $data['RtnCode'] == '1')$flag = TRUE;
        return $flag;
    }
    public function getOrderNo($data){
        return $data["MerTradeID"];
    }
    public function getAmount($data){
        return $data["Amount"];
    }
}
?>