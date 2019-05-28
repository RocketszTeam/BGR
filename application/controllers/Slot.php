<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Slot extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
	}
	
	public function index(){
		
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/slot.php", $this -> data);
	}
	
	//qt 電子
	public function qt(){
		//抓出遊戲分類
		$sqlStr="select num,kind,makers_num from `game_kind` where `root`=0 and `makers_num`=11";
		$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
		
		//抓出遊戲
		$parameter=array();
		$sqlStr="select * from `games` where `active`='Y' and makers_num=11";
		if(!$this->agent->is_mobile()){	//電腦版資料
			$sqlStr.=" and (`device`=1 or `device`=2)";
		}else{	//手機版資料
			$sqlStr.=" and (`device`=1 or `device`=3)";
		}
		if($this->input->get('kind')){
			$sqlStr.=" and `kind`=?";
			$parameter[':kind']=$this->input->get('kind',true);
		}
		$sqlStr .= 'order by num desc';
		$this->data["gameList"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/qt.php", $this -> data);
	}
	//沙龍電子
	public function sa(){
		//抓出遊戲分類
		$sqlStr="select num,kind,makers_num from `game_kind` where `root`=0 and `makers_num`=9";
		$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
		
		//抓出遊戲
		$parameter=array();
		$sqlStr="select * from `games` where `active`='Y' and makers_num=9";
		if(!$this->agent->is_mobile()){	//電腦版資料
			$sqlStr.=" and (`device`=1 or `device`=2)";
		}else{	//手機版資料
			$sqlStr.=" and (`device`=1 or `device`=3)";
		}
		if($this->input->get('kind')){
			$sqlStr.=" and `kind`=?";
			$parameter[':kind']=$this->input->get('kind',true);
		}
		$this->data["gameList"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/sa.php", $this -> data);
	}
		
	//Ameba
	public function ameba(){
		//抓出遊戲分類
		$sqlStr="select num,kind,makers_num from `game_kind` where `root`=0 and `makers_num`=27";
		$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
		
		//抓出遊戲
		$parameter=array();
		$sqlStr="select * from `games` where `active`='Y' and makers_num=27";
		if(!$this->agent->is_mobile()){	//電腦版資料
			$sqlStr.=" and (`device`=1 or `device`=2)";
		}else{	//手機版資料
			$sqlStr.=" and (`device`=1 or `device`=3)";
		}
		if($this->input->get('kind')){
			$sqlStr.=" and `kind`=?";
			$parameter[':kind']=$this->input->get('kind',true);
		}
		$sqlStr .= 'order by num desc';
		$this->data["gameList"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/ameba.php", $this -> data);
	}
	
	//pg 電子
	public function pg(){
		//抓出遊戲分類
		$sqlStr="select num,kind,makers_num from `game_kind` where `root`=0 and `makers_num`=6";
		$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
		
		//抓出遊戲
		$parameter=array();
		$sqlStr="select * from `games` where `active`='Y' and makers_num=6";
		if(!$this->agent->is_mobile()){	//電腦版資料
			$sqlStr.=" and (`device`=1 or `device`=2)";
		}else{	//手機版資料
			$sqlStr.=" and (`device`=1 or `device`=3)";
		}
		if($this->input->get('kind')){
			$sqlStr.=" and `kind`=?";
			$parameter[':kind']=$this->input->get('kind',true);
		}
		$sqlStr .= 'order by num desc';
		$this->data["gameList"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/pg.php", $this -> data);
	}
	
	
	public function vg(){
		//抓出遊戲分類
		$sqlStr="select num,kind,makers_num from `game_kind` where `root`=0 and `makers_num`=38";
		$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
		
		//抓出遊戲
		$parameter=array();
		$sqlStr="select * from `games` where `active`='Y' and makers_num=38";
		/*
		if(!$this->agent->is_mobile()){	//電腦版資料
			$sqlStr.=" and (`device`=1 or `device`=2)";
		}else{	//手機版資料
			$sqlStr.=" and (`device`=1 or `device`=3)";
		}
		if($this->input->get('kind')){
			$sqlStr.=" and `kind`=?";
			$parameter[':kind']=$this->input->get('kind',true);
		}
		*/
		$sqlStr .= ' order by num desc';
		//var_dump($sqlStr);
		$this->data["gameList"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/vg.php", $this -> data);
	}
	
	
	public function rtg(){
		//抓出遊戲分類
		$sqlStr="select num,kind,makers_num from `game_kind` where `root`=0 and `makers_num`=51";
		$this->data["kindList"]=$this->webdb->sqlRowList($sqlStr);
		
		//抓出遊戲
		$parameter=array();
		$sqlStr="select * from `games` where `active`='Y' and makers_num=51";
		/*
		if(!$this->agent->is_mobile()){	//電腦版資料
			$sqlStr.=" and (`device`=1 or `device`=2)";
		}else{	//手機版資料
			$sqlStr.=" and (`device`=1 or `device`=3)";
		}
		if($this->input->get('kind')){
			$sqlStr.=" and `kind`=?";
			$parameter[':kind']=$this->input->get('kind',true);
		}
		*/
		$sqlStr .= ' order by num desc';
		//var_dump($sqlStr);
		$this->data["gameList"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);
		$this -> load -> view("www/rtg.php", $this -> data);
	}
	
	

} 