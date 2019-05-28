<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Guide extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
	}
	
	public function index(){
		
		$this->data["withoutMemberCSS"] = true;
		$this -> load -> view("www/guide.php", $this -> data);
	}
	

		
} 