<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Errors extends CI_Controller{
	
	public function index(){
		header("Location:".base_url());
		exit;
		
	}
	

		
} 