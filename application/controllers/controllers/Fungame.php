<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Fungame extends Core_controller{
	private $url_match='/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i';	//遊戲url驗證
	public function __construct(){
		parent::__construct();
		$this->load->library('api/allgameapi');	//載入遊戲api
		
	}
	
	public function index(){
		if($this->input->get('gm')){
			$sqlStr="select * from `game_makers` where num=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$this->input->get('gm',true)));
			if($row!=NULL){
				$this->data["link"]=$this->allgameapi->fun_game($row["num"],$this->input->get('GameCode',true));
				if(!preg_match($this->url_match,$this->data["link"])){	//針對網址格式作驗證 格式錯誤代表api掛了
					scriptCloseMsg('取得遊戲連結錯誤！!!!');	
					exit;
				}
				//if($this->agent->is_mobile()){	//Combet不支援Iframe
					header("Location:".$this->data["link"]);
					exit;	
				//}
				$this -> load -> view("www/opengame", $this -> data);
			}else{
				scriptCloseMsg('該遊戲廠商不存在！');
			}
		}else{
			scriptCloseMsg('參數錯誤！');
		}
	}
} 