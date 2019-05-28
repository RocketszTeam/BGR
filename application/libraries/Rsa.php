<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Rsa {
	var $rsa;	
	public function __construct(){			
		require_once('phpseclib/Crypt/RSA.php');
		$CI =&get_instance();
		$this->rsa = new Crypt_RSA();
		
	}
	
	public function loadKey($key, $type = false){
		$this->rsa->loadKey($key,$type);
	}
	
	public function setSignatureMode($mode){
		$this->rsa->setSignatureMode($mode);
	}
	
	public function setHash($hash){
		$this->rsa->setHash($hash);
	}
	
	public function sign($message){
		return $this->rsa->sign($message);
	}
	
	public function decrypt($message){
		return $this->rsa->decrypt($message);
	}
	
	public function verify($message, $signature){
		return $this->rsa->verify($message, $signature) ;	
	}
	
}
?>