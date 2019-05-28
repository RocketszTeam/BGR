<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Daily extends CI_Controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this -> load -> model("admin/Daily_model", "daily", true);
	}
	
	public function aa(){
		
		$Date='2017-08-24';
		
		for($i=1;$i<=2;$i++){
			$myDate=date('Y-m-d',strtotime($Date."+ $i day"));//設定歸帳日期為 前一天
			$this->update_daily($myDate);	
		}
		
		
	}
	
	
	public function index(){
		$myDate=date('Y-m-d',strtotime("-1 day"));//設定歸帳日期為 前一天
		$this->update_daily($myDate);
	}
	
	public function d2(){
		$myDate=date('Y-m-d',strtotime("-2 day"));//設定歸帳日期為 前2天
		$this->update_daily($myDate);
		$this->d3($myDate);
	}
	
	public function d3($myDate){
		$myDate=date('Y-m-d',strtotime($myDate."-1 day"));//設定歸帳日期為 前3天
		$this->update_daily($myDate);
	}
	
	public function update_daily($myDate,$makers_num=NULL){
		$sTime=$myDate.' 00:00:00';	
		$eTime=$myDate.' 23:59:59';
		//echo '歸帳開始日期：'.$sTime.'<br>';
		//echo '歸帳結束日期：'.$eTime.'<hr>';
		
		$this->daily->allbet($sTime,$eTime);	//歐伯歸帳
		$this->daily->allbet_egame($sTime,$eTime);	//歐伯電子歸帳
		$this->daily->sagame($sTime,$eTime);	//沙龍歸帳
		$this->daily->super($sTime,$eTime);	//super體育
		$this->daily->fish($sTime,$eTime);	//捕魚機
		$this->daily->qt($sTime,$eTime);	//qt
		$this->daily->dg($sTime,$eTime);	//dg
		$this->daily->eb($sTime,$eTime);	//eb真人
		$this->daily->ssb($sTime,$eTime);	//ssb贏家體育
		$this->daily->s7pk($sTime,$eTime);	//7pk
		$this->daily->bingo($sTime,$eTime);	//bingo bingo
		$this->daily->water($sTime,$eTime);	//水立方
	}
	
	public function update_now(){
		if($this->input->post('sTime') && $this->input->post('eTime')){
			$date1=date_create($this->input->post('sTime'));
			$date2=date_create($this->input->post('eTime'));
			$diff=date_diff($date1,$date2);
			$datCount=$diff->days;
			for($i=0;$i<=$datCount;$i++){
				$myDate=date('Y-m-d',strtotime($this->input->post('sTime')."+ $i day"));//設定歸帳日期為 前一天
				$this->update_daily($myDate);	
			}
			echo json_encode(array('sTime'=>$this->input->post('sTime'),'eTime'=>$this->input->post('eTime')));
		}else{
			$myDate=date('Y-m-d');//設定歸帳日期為 前一天
			$this->update_daily($myDate);
		}
	}
	
}
?>