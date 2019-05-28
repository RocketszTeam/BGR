<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class R1 extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('pepay');
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		//$myPost=json_encode($_POST);
		//$sqlStr="INSERT INTO `tmp`(tmp) VALUES('".$myPost."')";
		//$this->webdb->sqlExc($sqlStr);
		$this->pepay->CheckR1Feedback();	
	}
}
