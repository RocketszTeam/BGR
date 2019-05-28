<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Service extends CI_Controller{
	
	public function __construct(){
		parent::__construct();
		//$this->load->library('memberclass');	//載入會員函式庫
		
	}
	
	public function index(){
		
		echo '<img src="'.ASSETS_URL.'/service.png">';
		
		//$this -> load -> view("www/egame.php", $this -> data);
		
	}
	

		
} 