<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Egamebs extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		//$this->load->library('memberclass');	//載入會員函式庫
		
	}
	
	public function index(){
		if(!$this->input->get('gm')){
			$this -> session -> set_flashdata("alertMsg",'系統參數錯誤');
			scriptMsg('',"Index");
			exit;
		}else{
			//抓出廠商遊戲分類
			$parameter=array();
			$dataList=array();	
			$sqlStr="select num,kind,makers_num from `game_kind` where `root`=0 and `makers_num`=?";
			$parameter[':makers_num']=$this->input->get('gm',true);
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$row["num"];
					$data["kind"]=$row["kind"];
					
					//抓出遊戲資料
					$sqlStr2="select * from `games` where `active`='Y' and makers_num=".$row["makers_num"]." and kind=".$row["num"];
					if(!$this->agent->is_mobile()){	//電腦版資料
						$sqlStr2.=" and (`device`=1 or `device`=2)";
					}else{	//手機版資料
						$sqlStr2.=" and (`device`=1 or `device`=3)";
					}
					$data["nodes"]=$this->webdb->sqlRowList($sqlStr2);
					array_push($dataList,$data);
				}
			}
		}
		
		$this->load->library('banner');
		if($this->input->get('gm')==11){	//qtBaneer
			$this->data["gameBanner"]=$this->banner->banner_show(3);
		}
		
		
		//print_r($dataList);exit;
		$this->data["result"]=$dataList;
		
		$this -> load -> view("www/egame-bs.php", $this -> data);
		
	}
	

		
} 