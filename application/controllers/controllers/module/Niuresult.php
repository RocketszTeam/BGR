<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(dirname(__FILE__))."/Core_controller.php");
class Niuresult extends Core_controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('api/niu');
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->niu->CheckOutFeedback();
			if (sizeof($arFeedback) > 0) {
				if($arFeedback["error_code"]==0){  //
					$data['text']=http_build_query($arFeedback);
					$this->webdb->sqlInsert('text',$data);
				}else{
					//$data['text']='error_code Error';
					//$this->webdb->sqlInsert('text',$data);
					echo '0|error_code Error';
				}
			}else{
				//$data['text']='request Error';
				//$this->webdb->sqlInsert('text',$data);
				print '0|request Error';		
			}
		}catch (Exception $e){
			//$data['text']=$e->getMessage();
			//$this->webdb->sqlInsert('text',$data);
			echo '0|' . $e->getMessage();
		}
	}
	
		
}
