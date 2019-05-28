<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Active extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
	}
	
	public function index(){
		
		$sqlStr="select * from `active_kind` where `root`=0 order by `range`";
		$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
		$parameter=array();
		$sqlStr="select * from `active` where 1=1".time_sql();
		if($this->input->get('kind')){
			$sqlStr.=" and kind in(?".kind_sql($this->input->get('kind',true),"active_kind").")";
			$parameter[":kind"]=$this->input->get('kind',true);
		}
		$sqlStr.=" order by buildtime DESC,num DESC";
		$this->data["activeList"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/active.php", $this -> data);
	}

	public function detail(){
		if(!$this->input->get('num')){
			$this -> session -> set_flashdata("alertMsg",'參數錯誤!!');
			scriptMsg('',$this->agent->referrer());
			exit;
		}else{
			$sqlStr="select * from `active` where num=?".time_sql();
			$parameter=array(':num'=>$this->input->get('num',true));
			$this->data["row"]=$this->webdb->sqlRow($sqlStr,$parameter);
			if($this->data["row"]!=NULL){
				$this -> load -> view("www/active_detail", $this -> data);
			}else{
				$this -> session -> set_flashdata("alertMsg",'最新優惠不存在!!');
				scriptMsg('',$this->agent->referrer());
				exit;
			}
		}
	}
		
} 
