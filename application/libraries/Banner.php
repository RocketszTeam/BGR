<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Banner {
	var $CI;
	public function __construct(){			
		$this->CI = &get_instance();
	}
	
	public function banner_show($kind){
		$parameter=array();
		$sqlStr="select `subject`,`url`,`pic` from `banner` where `view`='Y' and `kind`=?".time_sql();
		$parameter[':kind']=$kind;
		//if($admin_num > 0){
			//$sqlStr.=" and `admin_num`=?";
			//$parameter[':admin_num']=$admin_num;
		//}
		$sqlStr.=" order by rand()";
		$rowAll=$this->CI->webdb->sqlRowList($sqlStr,$parameter);
		return $rowAll;			
	}
	
}
?>