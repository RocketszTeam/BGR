<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class LoginCheck  extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->library('api/grandapi');	//緯來;
	}
	
	public function index(){
		if($this->input->post('Token') && $this->input->post('MemberAccount')){
			$hashCode=$this->grandapi->getHash();
			$Token=md5(trim($this->input->post('MemberAccount',true)).$hashCode);
			if(trim($this->input->post('Token',true))==$Token){
				echo json_encode(array('Result'=>0));
			}else{
				echo json_encode(array('Result'=>1));
			}
		}else{
			echo json_encode(array('Result'=>1));	
		}
			
		
	}
			
} 