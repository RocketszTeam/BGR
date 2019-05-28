<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(dirname(__FILE__))."/".SYSTEM_URL."/Core_controller.php");

class Kind extends Core_controller{
	public function __construct(){
		parent::__construct();
		
	}
	
	//語系類別連動後台用
	function ajax_kind(){
		if($this->input->is_ajax_request()){
			$table=$this->input->post("table");//table
			$ojbID=$this->input->post("ojbID");//ID
			$nation=$this->input->post("nation");//語系
			$defValue=$this->input->post("defValue");//預設值
			$pleaseSelect=$this -> input -> post('pleaseSelect');//是否有請選擇
			$required=$this -> input -> post('required');//是否需要判斷必輸入
			$hasSearch=$this->input->post("hasSearch");//預設值
			$change=$this -> input -> post('change');
			if (@$change!=""){
				@$changeJS=' onchange="'.@$change.'"';
			}
			$admin_num=($this->web_root_u_power > 1? $this->web_root_num : 0);	//大於0傳入當前使用者num，否則傳0
			echo '<select class="form-control '.($hasSearch =='true'? ' selectpicker' : '').'" name="'.$ojbID.'" id="'.$ojbID.'" data-live-search="true" '.($required =='true'? ' required' : '').@$changeJS.'>';
			echo ($pleaseSelect ? '<option value="">請選擇</option>' : "");
			if($nation!=""){
				echo getKindOption($table,$defValue,$nation);
			}
			echo '</select>';
		}
	}		
}

?>