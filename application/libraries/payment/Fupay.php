<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Gateway_tpl.php");
//Fupay
class Fupay extends Gateway_tpl {
    public function __construct(){
        parent::__construct();
        error_reporting(0);
    }
    public function getData($orderNo, $amount, $member = NULL){
        $no = $this->merchant.substr($orderNo,-9);
        $upSql = "UPDATE `orders` SET `order_no`='".$no."' WHERE `order_no`='".$orderNo."'";
        $this->CI->webdb->sqlExc($upSql);

        $upSql = "UPDATE `payment_order` SET `order_no`='".$no."' WHERE `order_no`='".$orderNo."'";
        $this->CI->webdb->sqlExc($upSql);

        $data['orderid'] = $no;
        $data['userid'] = (isset($member['id'])) ? $member['id'] : 'User';
        
        $data['amount'] = $amount;
        $data['date'] = date('Y-m-d H:i:s');
        $data['phone'] = '0900000000';

        $data['url'] = $this->getPayResultURL();
        return $data;
    }
    public function atmPay($orderNo, $amount, $type = NULL, $member = NULL){
        $this->saveData($orderNo, 'ATM', '810');
        $data = $this->getData($orderNo, $amount, $member);
        $data['type'] = 4;
        $data["sign"] = $this->sendValidate($data);
        $this->submit($this->apiUrl.'autocode.php', $data);
    }
    public function cvsPay($orderNo, $amount, $type = 'IBON', $member = NULL){
        $this->saveData($orderNo, 'IBON', $type);
        $data = $this->getData($orderNo, $amount, $member);
        $upSql = "UPDATE `orders` SET `payment`='IBON' WHERE `order_no`='".$data['orderid']."'";
        $this->CI->webdb->sqlExc($upSql);
        $data['type'] = 3;
        $data["sign"] = $this->sendValidate($data);
        $this->submit($this->apiUrl.'autocode.php', $data);
    }

    public function sendValidate($data = NULL){
        $validate = $data['orderid']."|".$data['amount']."|".$data['userid']."|".$data['type'] ."|".$data['url']."|".$this->merchant."|".$this->hashKey;
        return md5($validate);
    }
    public function receiveValidate($data = NULL){
        $validate = $data['orderid']."|".$data['number']."|".$data['paytime']."|".$data['type'] ."|".$this->merchant."|".$this->hashKey;
        return md5($validate);
    }
    private function toAry($data){
        if(gettype($data) == 'array')return $data;
        else return json_decode($data, TRUE);
    }


    public function checkValidate($data = NULL){
        $flag = FALSE;
        $data = $this->toAry($data);
        if($data['sign'] == $this->receiveValidate($data) && $data['type'] == 'OK')$flag = TRUE;
        return $flag;
    }
    public function getOrderNo($data){
        $data = $this->toAry($data);
        return $data["orderid"];
    }
    public function getAmount($data){
        $data = $this->toAry($data);

        $sqlStr = "select * from `orders` where `order_no`=?";
        $parameter = array(':order_no' => $this->getOrderNo($data));
        $row = $this->CI->webdb->sqlRow($sqlStr, $parameter);
        return $row["amount"];
    }
    public function getMsgSuccess(){
        return 'OK';
    }
    public function submit($url, $data){
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_exec($ch);
            curl_close($ch);

            $code = '';
            $str = $output;

            if($data["type"] == 4){ //星展atm
                $code =  str_replace('取號為 : ', '', $str);
                $code = strip_tags($code);
            } else if($data["type"] == 3){//ibon
                $str = $output;
                $a = explode('<br>',$str);
                if(count($a) > 0)$code =  str_replace('IBON碼 : ', '', $a[1]);
            }


            if($http_code==200 && !empty($code)){
                $upSql = "UPDATE `payment_order` SET `code`='".$code."' WHERE `order_no`='" . $data["orderid"]."'";
                $this->CI->webdb->sqlExc($upSql);


                header("Location:".site_url("Manger/deposit_result?order_no=". $data["orderid"]));
                exit();
            }else{
                header("Location:".site_url("Manger/deposit"));
                exit();
            }


        }catch (Exception $e){
            echo $e->getMessage();
        }
    }
}
?>