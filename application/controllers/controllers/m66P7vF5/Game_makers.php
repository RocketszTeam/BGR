<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Game_makers extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		$this -> load -> model("sysAdmin/game_makers_model", "game_makers");
		
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	

	public function index(){		
		$this->isLogin();
		$this->_append_js2("sort/jquery.ui.core.js");
		$this->_append_js2("sort/jquery-sortable.js");
		$this->_append_css2("sort/sort3.css");


		$parameter=array();
		$sqlStr="select * from [myTable] where 1 = 1";			
	
		$sqlStr.=" order by `range` ";
		$rowAll=$this->game_makers->sqlRowList($sqlStr,$parameter);		
		$this -> data["result"] = $rowAll;
						
							
		$this -> data["body"] = $this -> load -> view("sysAdmin/game_makers/index", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
	
	
	
} 