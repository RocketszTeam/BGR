<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Orderclass {
	var $CI;
	public function __construct(){			
		$this->CI = &get_instance();
		
		date_default_timezone_set("Asia/Taipei");	
	}
	
	public function order_no(){	//取得訂單編號
		$order_no="AH".time();
		$sqlStr="INSERT INTO `orders_num`(`order_no`) VALUES ('".$order_no."')";
		$orders_num=$this->CI->webdb->sqlExc($sqlStr);
		$r = $order_no.$orders_num;
		if(strlen($r) >= 16){
			$r = substr($order_no,0,16-(strlen($orders_num))).$orders_num;
		}
		if(!$orders_num){	//取流水號失敗
			return NULL;
		}else{
			//return $order_no.$orders_num;
			return $r;
		}
	}
	
	//定義拋售訂單狀態
	public function sellKeyin1(){
		$key1=array('0_等待拍賣','1_拍賣成功','2_放棄拍賣','3_拍賣異常');	
		return $key1;
	}
	
	//定義轉移訂單狀態
	public function transferKeyin1(){
		$key1=array('0_待處理','1_處理完畢','2_放棄轉移');	
		return $key1;
	}
	
	//定義銀行匯款收款情況
	public function bankKeyin2(){
		$key2=array('0_未收款','1_已收款','2_已放棄');	
		return $key2;
	}
	
	//定義訂單處理情形===============================
	public function orderKeyin1(){
		$key1=array();	
		$key1['0']="未處理";
		$key1['1']="訂單處理中";
		$key1['2']="未連絡到客戶";
		$key1['3']="等待付款單據";
		$key1['4']="商品已寄出";
		$key1['5']="商品調貨中";
		$key1['6']="商品退貨";
		$key1['7']="未結案";
		$key1['8']="已結案";
		return $key1;
	}
	
	//定義訂單收款情形
	public function orderKeyin2($nation='TW'){
		$key2=array();
		$key2['0']="未收款";
		$key2['1']="已收款";
		return $key2;
	}
	
	//回傳訂單處理情形名稱
	public function returnKeyin1($value){
		$key1=$this->orderKeyin1();
		return $key1[$value];
	}

	//回傳收款情形名稱
	public function returnKeyin2($value){
		$key2=$this->orderKeyin2();
		return $key2[$value];
	}


	
}
?>