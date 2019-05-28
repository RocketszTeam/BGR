<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Smsclass2 {
	var $CI;
	var $userID='SMS01';
	var $password='SMS01';
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