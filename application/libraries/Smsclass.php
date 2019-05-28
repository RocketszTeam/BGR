<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Smsclass {
	var $CI;
	var $userID='0917459840';
	var $password='vfhm';
	var $sms;

	public function __construct(){		
		require_once('ev8d/SMSHttp.php');
		$this->CI=&get_instance();
		$this->sms=new SMSHttp();
	}
	
	public function sendSMS($content, $mobile,$subject=NULL,$sendTime=NULL){
		return $this->sms->sendSMS($this->userID,$this->password,$content, $mobile,$subject,$sendTime);
	}
}
?>