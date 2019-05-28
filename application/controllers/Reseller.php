<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Reseller extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		//$this->load->library('memberclass');	//載入會員函式庫
		
	}
	
	public function index(){
		$parameter=array();
		$sqlStr="select * from `games` where `active`='Y'";
		if(!$this->agent->is_mobile()){	//電腦版資料
			$sqlStr.=" and (`device`=1 or `device`=2)";
		}else{	//手機版資料
			$sqlStr.=" and (`device`=1 or `device`=3)";
		}
		if($this->input->get('gm')){
			$sqlStr.=" and `makers_num`=?";
			$parameter[':makers_num']=$this->input->get('gm',true);	
		}
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this->data["result"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		
		$this -> load -> view("www/reseller.php", $this -> data);
		
	}
	

		
} 