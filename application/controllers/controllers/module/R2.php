<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class R2 extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('pepay');
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		$this->pepay->CheckR2Feedback();
	}
}
