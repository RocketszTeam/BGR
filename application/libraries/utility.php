<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Utility{
	
	var $CI;

	function __construct()
	{
		//CI初始化
		$this->CI =& get_instance();
	}

	function alert_redirect($msg, $end_page)
	{
		header("Content-Type: text/html; charset=utf-8");		
		echo "<script language='javascript'>alert('".$msg."'); window.location = '".site_url($end_page)."';</script>";
		exit;		
	}

	/**
	 * 說明:根據日期取得季編號
	 */
	function getSeasonNum(/*String*/ $Date){
		$m = intval(date("m",strtotime($Date)));
		if ($m >0 and $m <= 3){
			return 1;
		}elseif ($m > 3 and $m <= 6 ){
			return 2;
		}elseif ($m > 6 and $m <= 9 ){
			return 3;
		}else{
			return 4;
		}				
	}
	
	/**
	 * 說明：取得中文星期
	 */
	function getWeeks(){
		$r = array("日","ㄧ","二","三","四","五","六");
		return $r;
	} 
	
	/**
	 * 說明:根據日期取得中文星期幾
	 * @param $date 格式：datetime
	 */
	function getWeek(/*string*/ $date){
		$w = date("w",strtotime($date));
		$ws = $this->getWeeks();
		return $ws[$w];
	}
	
}


