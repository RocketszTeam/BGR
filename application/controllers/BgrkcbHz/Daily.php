<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Daily extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		error_reporting(0);			
		//本週
		$toWeek=reportDate('tw');
		$this->data["toWeek"]=array('d1'=>$toWeek['d1'],'d2'=>$toWeek['d2']);
		//上周
		$yeWeek=reportDate('yw');
		$this->data["yeWeek"]=array('d1'=>$yeWeek['d1'],'d2'=>$yeWeek['d2']);
		//本月
		$toMonth=reportDate('m');
		$this->data["toMonth"]=array('d1'=>$toMonth['d1'],'d2'=>$toMonth['d2']);
		//上月
		$ymMonth=reportDate('ym');
		$this->data["ymMonth"]=array('d1'=>$ymMonth['d1'],'d2'=>$ymMonth['d2']);
		$this->load->model('admin/Daily_report_model','report');

	}
	

	public function report_all($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		
		$parameter=array();
		$Cust_array=array(4,5,6);	//股東 總代 代理				
		$whereSql='';
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			//---身份判定---------------------------
			if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代
				 $root=($root > 0 ? $root : $this->web_root_num);
			}
			
			$this->data["prefix"]=array();
			
			//取得歐博
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],3);
			$dataList=$this->report->buildData($dataList,$rowAll,'Allbet');
			array_push($this->data["prefix"],array('Allbet','歐博真人'));
			
			//取得歐博電子
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],'3-1');
			$dataList=$this->report->buildData($dataList,$rowAll,'AllbetE');
			array_push($this->data["prefix"],array('AllbetE','歐博電子'));
			
			
			//取得沙龍
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],9);
			$dataList=$this->report->buildData($dataList,$rowAll,'Sagame');
			array_push($this->data["prefix"],array('Sagame','沙龍真人'));
			
			
			//取得Ebet真人
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],24);
			$dataList=$this->report->buildData($dataList,$rowAll,'Ebet');
			array_push($this->data["prefix"],array('Ebet','EB真人'));
			
			
			//取得水立方真人
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],25);
			$dataList=$this->report->buildData($dataList,$rowAll,'Water');
			array_push($this->data["prefix"],array('Water','水立方真人'));
			
			//取得Super體育
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],8);
			$dataList=$this->report->buildData($dataList,$rowAll,'Super');
			array_push($this->data["prefix"],array('Super','Super體育'));
			
			
			//取得捕魚機
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],4);
			$dataList=$this->report->buildData($dataList,$rowAll,'Fish');
			array_push($this->data["prefix"],array('Fish','GG捕魚機'));
			
			
			//取得QT電子
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],11);
			$dataList=$this->report->buildData($dataList,$rowAll,'QT');
			array_push($this->data["prefix"],array('QT','QT電子'));
			
			
			//取得DG真人
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],12);
			$dataList=$this->report->buildData($dataList,$rowAll,'DG');
			array_push($this->data["prefix"],array('DG','DG真人'));
			
			
			//取得贏家體育
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],21);
			$dataList=$this->report->buildData($dataList,$rowAll,'SSB');
			array_push($this->data["prefix"],array('SSB','贏家體育'));
			
			
			//取得7PK
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],22);
			$dataList=$this->report->buildData($dataList,$rowAll,'S7PK');
			array_push($this->data["prefix"],array('S7PK','7PK'));
			
			//取得賓果
			$rowAll=$this->report->report_all($root,$_REQUEST["find7"],$_REQUEST["find8"],23);
			$dataList=$this->report->buildData($dataList,$rowAll,'Bingo');
			array_push($this->data["prefix"],array('Bingo','賓果'));
			
			
			//紅利計算
			if(count($dataList) > 0){
				foreach($dataList as $keys=>$row){
					$row=$this->report->member_points($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$dataList[$keys]["PointsProfit"]=($row["PointsProfit"]!="" ? $row["PointsProfit"] : 0);	//紅利分潤
					$dataList[$keys]["TotalRealPoints"]=($row["TotalRealPoints"]!="" ? $row["TotalRealPoints"] : 0);
				}
			}
			
			
			
			//根據帳號排序
			foreach($dataList as $key=>$row){
				$rowTotal[$key]  = $row['u_id'];
			}
			@array_multisort($rowTotal, SORT_ASC, $dataList);
			//根據帳號排序
			foreach($dataList as $key=>$row){
				$rowTotal[$key]  = $row['u_id'];
			}
			@array_multisort($rowTotal, SORT_ASC, $dataList);
			
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Daily/report_all/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Daily/report_all/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/daily_all", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
		
	}
} 