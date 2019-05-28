<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Verify extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this -> load -> model("sysAdmin/webdb_model", "webdb", true);
		$this->load->library('api/sagamingapi');	//沙龍	
	}
	
	
	public function index(){
		$Checkkey=$this->sagamingapi->getCheckkey();
		if($this->input->get('checkkey')){
			if($this->input->get('checkkey')==$Checkkey){
				echo 'checkkeyok';
			}else{
				echo 'checkkeyfailed';
			}
		}else{
			echo 'checkkeyfailed';	
		}
	}
	
}

?>