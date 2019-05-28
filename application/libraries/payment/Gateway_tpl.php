<?php
defined('BASEPATH') OR exit('No direct script access allowed');
abstract class Gateway_tpl{
	var $merchant='';
    var $hashKey='';
	var $hashIV='';
	var $validate='';
    const ITEM_NAME = '商城商品';
    const ITEM_DESC = '此為商城虛擬商品購買，並非實體商品交易，請勿受騙上當，並請勿代他人繳款儲值，小心觸法';
    var $host = '';
	var $apiUrl='';
	var $CI;
	var $m_group = 0;
	var $uid = NULL;
	public function __construct(){	
		$this->CI =&get_instance();
        $this->CI->load->library('memberclass');
		$this->m_group = tb_sql('m_group','member',$this->CI->memberclass->num());
		$this->host = (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']).'/';
		date_default_timezone_set("Asia/Taipei");
	}
    /*
     * member{
     *      id
     *      phone
     *      name
     *      mail
     * }
     */
	public function creditPay($orderNo, $amount, $member = NULL){return NULL;}
    public function atmPay($orderNo, $amount, $type, $member = NULL){return NULL;}
    public function cvsPay($orderNo, $amount, $type, $member = NULL){return NULL;}

    public function setUid($uid){
	    $this->uid = $uid;
        $this->apiUrl = tb_sql3('gatewayUrl', 'payment_gateway', array('libs'=> $this->getClassName()));
        $this->merchant = tb_sql3('merchant', 'payment_config', array('uid' => $this->uid, 'libs'=> $this->getClassName()));
        $this->hashKey = tb_sql3('HashKey', 'payment_config', array('uid' => $this->uid, 'libs'=> $this->getClassName()));
        $this->hashIV = tb_sql3('HashIV', 'payment_config', array('uid' => $this->uid, 'libs'=> $this->getClassName()));
        $this->validate = tb_sql3('validate', 'payment_config', array('uid' => $this->uid, 'libs'=> $this->getClassName()));
	}
    public function sendValidate($data = NULL){}
    public function receiveValidate($data = NULL){}
    public function checkValidate($data = NULL){}
    public function saveData($orderNo, $paymentType, $type){
        $colSql="order_no,payment,type,libs,config_uid";
        $sqlStr="INSERT INTO `payment_order` (".sqlInsertString($colSql,0).")";
        $sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
        $parameter[':order_no'] = $orderNo;
        $parameter[':payment'] = strtoupper($paymentType);
        $parameter[':type'] = strtoupper($type);
        $parameter[':libs'] = $this->getClassName();
        $parameter[':config_uid'] = $this->uid;
        $this->CI->webdb->sqlExc($sqlStr,$parameter);
    }
    public function getData($orderNo, $amount, $member = NULL){}
    public function getClassName(){
	    return strtolower(get_class($this));
    }
    protected function submit($url, $data){
        try{
            $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $html .= '<form id="payForm" method="POST" action="' . $url . '">';
            foreach ($data as $keys => $value) {
                $html .="<input type='hidden' name='$keys' value='$value' />";
            }
            // 手動或自動送出表單。
            if (!isset($paymentBtn)) {
                $html .= '<script type="text/javascript">document.getElementById("payForm").submit();</script>';
            } else {
                $html .= '<input type="submit" id="__paymentButton" value="' . $paymentBtn . '" />';
            }
            $html .= '</form>';
            print $html;
            exit();
            die();
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }
	public function receiveData(){
	    $data = $_POST;
	    if(!empty($_GET)){
	        $data = $_GET;
        }
        $this->saveLog($data);
        if (sizeof($data) < 0) {
            echo 'No Data';
            exit();
        }
        return $data;
    }
    public function getOrderNo($data){}
    public function getAmount($data){}
    public function getCode($data){return NULL;}
    public function getType($data){return NULL;}
    public function getMsgSuccess(){
	    return 'SUCCESS';
    }
    public function saveLog($data){
        $myfile = fopen("/var/www/html/application/controllers/payment/log/".$this->getClassName().'-'.$this->getOrderNo($data).".txt", "w+") or die("Unable to open file!");
        $txt = json_encode($data);
        fwrite($myfile, $txt);
        fclose($myfile);
    }
    public function getPayResultURL(){return $this->host.'payment/payresult/'.$this->getClassName();}
    public function getCodeURL(){return $this->host.'payment/getcode/'.$this->getClassName();}
}
?>