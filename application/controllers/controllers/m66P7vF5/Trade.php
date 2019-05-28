<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Trade extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		$this->data["openFind"]="N";//是否啟用搜尋
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
	}
	
	/*
	public function index(){	
		
		$sDate=date('Y-m-'."01 00:00:00");	//預設起始日期
		$eDate=date('Y-m-d H:i:s',strtotime($sDate."+1 month -1 second"));	//預設結束日期
		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$ag_array=array(4,5,6);
		$sqlStr="select num,u_id,u_power,u_name from `admin` where `u_power`=6";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':num']=	$this->web_root_num;
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);	
		
		$this->data["ATMSUM"]=0;	//總ATM儲值金額
		$this->data["MMKSUM"]=0;	//總超商儲值金額
		$this->data["BANKSUM"]=0;	//總銀行匯款金額
		$this->data["OrdersSUM"]=0;	//儲值小計總額
		$this->data["SELLSUM"]=0;	//拋售總額
		$this->data["FEESUM"]=0;	//拋售手續費總額
		$this->data["POINTSUM"]=0;	//贈點統計
		$this->data["ALLSUM"]=0;	//小計總和
		
		$this->data["sDate"]=$sDate;
		$this->data["eDate"]=$eDate;
		
		$dataList=array();
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				
				//===起始日期=======================
				if(@$_REQUEST["find7"]!=""){
					$sDate=@$_REQUEST["find7"];
				}
				//===終止日期=======================
				if(@$_REQUEST["find8"]!=""){
					//$eDate=date('Y-m-d',strtotime($_REQUEST["find8"]."+1 day"));
					$eDate=@$_REQUEST["find8"];
				}
				
				//抓出ATM收款總結
				$parameter2=array();
				$newSql="select SUM(amount) as ATM_TOTAL,payment from `orders` where `payment`='ATM'  and `keyin2`=1 and `admin_num`=".$row["num"];
				$newSql.=" and `buildtime` >=?";
				$newSql.=" and `buildtime` <= ?";
				$parameter2[':sDate']=$sDate;
				$parameter2[':eDate']=$eDate;
				$ATM_row=$this->webdb->sqlRow($newSql,$parameter2);
				
				//抓出超商收款總結
				$parameter2=array();
				$newSql="select SUM(amount) as MMK_TOTAL,payment from `orders` where `payment`='CVS'  and `keyin2`=1 and `admin_num`=".$row["num"];
				$newSql.=" and `buildtime` >=?";
				$newSql.=" and `buildtime` <= ?";
				$parameter2[':sDate']=$sDate;
				$parameter2[':eDate']=$eDate;
				$MMK_row=$this->webdb->sqlRow($newSql,$parameter2);			
				//抓出銀行匯款總結
				$parameter2=array();
				$newSql="select SUM(amount) as BANK_TOTAL from `member_bank_transfer` where `keyin2`=1 and `admin_num`=".$row["num"];
				$newSql.=" and `buildtime` >=?";
				$newSql.=" and `buildtime` <= ?";
				$parameter2[':sDate']=$sDate;
				$parameter2[':eDate']=$eDate;
				$BANK_row=$this->webdb->sqlRow($newSql,$parameter2);
				
				//抓出寶物出售
				$parameter2=array();
				$newSql="select SUM(amount) as SELL_TOTAL,SUM(fee) as FEE_TOTAL from `member_sell` where  `keyin1`=1 and `admin_num`=".$row["num"];
				$newSql.=" and `buildtime` >=?";
				$newSql.=" and `buildtime` <= ?";
				$parameter2[':sDate']=$sDate;
				$parameter2[':eDate']=$eDate;
				$SELL_row=$this->webdb->sqlRow($newSql,$parameter2);
				
				//紅利贈點
				$parameter2=array();
				$newSql="select SUM(points) as POINT_TOTAL from `member_wallet`  where admin_num=".$row["num"];
				$newSql.=" and kind in (1,6,7,8,13)";
				$newSql.=" and buildtime >=?";
				$newSql.=" and buildtime <= ?";
				$parameter2[':sDate']=$sDate;
				$parameter2[':eDate']=$eDate;
				$POINT_row=$this->webdb->sqlRow($newSql,$parameter2);
				
				
				$data=array();
				$data["num"]=$row["num"];
				$data["u_id"]=$row["u_id"];
				$data["u_name"]=$row["u_name"];		
				$data["u_power"]=$row["u_power"];
				//儲值資料
				$data["ATM_TOTAL"]=($ATM_row["ATM_TOTAL"]!="" ? $ATM_row["ATM_TOTAL"] : 0);
				$data["MMK_TOTAL"]=($MMK_row["MMK_TOTAL"]!="" ? $MMK_row["MMK_TOTAL"] : 0);
				$data["BANK_TOTAL"]=($BANK_row["BANK_TOTAL"]!="" ? $BANK_row["BANK_TOTAL"] : 0);
				$data["Orders_TOTAL"]=$data["ATM_TOTAL"] + $data["MMK_TOTAL"] + $data["BANK_TOTAL"];	//儲值小計
				$this->data["OrdersSUM"]+=$data["Orders_TOTAL"];	//計算儲值小計加總
				$this->data["ATMSUM"]+=$data["ATM_TOTAL"];	//計算 ATM總額
				$this->data["MMKSUM"]+=$data["MMK_TOTAL"];	//計算 超商總額
				$this->data["BANKSUM"]+=$data["BANK_TOTAL"];//計算 銀行匯款總額
				//拋售資料
				$data["SELL_TOTAL"]=($SELL_row["SELL_TOTAL"]!="" ? $SELL_row["SELL_TOTAL"] : 0);
				$data["FEE_TOTAL"]=$data["SELL_TOTAL"]-($SELL_row["FEE_TOTAL"]!="" ? $SELL_row["FEE_TOTAL"] : 0);	//實際出款
				$this->data["SELLSUM"]+=$data["SELL_TOTAL"];	//計算拋售總額
				$this->data["FEESUM"]+=$data["FEE_TOTAL"];	//實際出款金額總計
				//紅利贈點
				$data["POINT_TOTAL"]=($POINT_row["POINT_TOTAL"]!="" ? $POINT_row["POINT_TOTAL"] : 0);
				$this->data["POINTSUM"]+=$data["POINT_TOTAL"];	//計算贈點總額
				
				//計算最後小計
				//$data["ALL_TOTAL"]=$data["Orders_TOTAL"] - $data["FEE_TOTAL"] - $data["POINT_TOTAL"];
				$data["ALL_TOTAL"]=$data["Orders_TOTAL"] - $data["FEE_TOTAL"];	//不算紅利
				$this->data["ALLSUM"]+=$data["ALL_TOTAL"];	//計算小計總和
				array_push($dataList,$data);
			}
		}
		
		
		//根據金額排序
		foreach($dataList as $key=>$row){
			$rowTotal[$key]  = $row['ALL_TOTAL'];
		}
		@array_multisort($rowTotal, SORT_DESC, $dataList);
		
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/trade/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	*/
	
	public function index(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sDate=date('Y-m-'."01 00:00:00");	//預設起始日期
		$eDate=date('Y-m-d H:i:s',strtotime($sDate."+1 month -1 second"));	//預設結束日期
		$ag_array=array(4,5,6);
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sDate=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			//$eDate=date('Y-m-d',strtotime($_REQUEST["find8"]."+1 day"));
			$eDate=@$_REQUEST["find8"];
		}
		$this->data["ATMSUM"]=0;	//總ATM儲值金額
		$this->data["MMKSUM"]=0;	//總超商儲值金額
		$this->data["BANKSUM"]=0;	//總銀行匯款金額
		$this->data["OrdersSUM"]=0;	//儲值小計總額
		$this->data["SELLSUM"]=0;	//拋售總額
		$this->data["FEESUM"]=0;	//拋售手續費總額
		$this->data["POINTSUM"]=0;	//贈點統計
		$this->data["ALLSUM"]=0;	//小計總和
		$this->data["sDate"]=$sDate;
		$this->data["eDate"]=$eDate;
		
		$dataList=array();
		
		//抓出ATM收款總結
		$parameter=array();
		$sqlStr="select SUM(amount) as ATM_TOTAL,admin_num from `orders` where `payment`='ATM'  and `keyin2`=1";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY admin_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$data=array();
				$data["num"]=$row["admin_num"];
				$data["u_id"]=tb_sql('u_id','admin',$row["admin_num"]);
				$data["u_name"]=tb_sql('u_name','admin',$row["admin_num"]);	
				$data["u_power"]=tb_sql('u_power','admin',$row["admin_num"]);
				$data["ATM_TOTAL"]=($row["ATM_TOTAL"]!="" ? $row["ATM_TOTAL"] : 0);
				$data["Orders_TOTAL"]=0;	//儲值小計預設值
				$data["Orders_TOTAL"]+=$data["ATM_TOTAL"];	//加總儲值小計
				$data["ALL_TOTAL"]=0;	//最後小計預設值
				$data["ALL_TOTAL"]+=$data["ATM_TOTAL"];	//最後小計計算
				$dataList[$row["admin_num"]]=$data;
			}
		}
		//抓出超商收款總結
		$parameter=array();
		$sqlStr="select SUM(amount) as MMK_TOTAL,admin_num from `orders` where (`payment`='IBON' || `payment`='FAMI' || `payment`='CVS')  and `keyin2`=1";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY admin_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["admin_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["admin_num"]]["MMK_TOTAL"]=($row["MMK_TOTAL"]!="" ? $row["MMK_TOTAL"] : 0);
					$dataList[$row["admin_num"]]["Orders_TOTAL"]+=$dataList[$row["admin_num"]]["MMK_TOTAL"];	//加總儲值小計
					$dataList[$row["admin_num"]]["ALL_TOTAL"]+=$dataList[$row["admin_num"]]["MMK_TOTAL"];	//最後小計 計算
				}else{
					$data=array();
					$data["num"]=$row["admin_num"];
					$data["u_id"]=tb_sql('u_id','admin',$row["admin_num"]);
					$data["u_name"]=tb_sql('u_name','admin',$row["admin_num"]);	
					$data["u_power"]=tb_sql('u_power','admin',$row["admin_num"]);
					$data["MMK_TOTAL"]=($row["MMK_TOTAL"]!="" ? $row["MMK_TOTAL"] : 0);
					$data["Orders_TOTAL"]=0;	//儲值小計預設值
					$data["Orders_TOTAL"]+=$data["MMK_TOTAL"];	//加總儲值小計
					$data["ALL_TOTAL"]=0;	//最後小計預設值
					$data["ALL_TOTAL"]+=$data["MMK_TOTAL"];	//最後小計計算
					$dataList[$row["admin_num"]]=$data;
				}
			}
		}
		
		//抓出銀行匯款總結
		$parameter=array();
		$sqlStr="select SUM(amount) as BANK_TOTAL,admin_num from `member_bank_transfer` where  `keyin2`=1";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY admin_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["admin_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["admin_num"]]["BANK_TOTAL"]=($row["BANK_TOTAL"]!="" ? $row["BANK_TOTAL"] : 0);
					$dataList[$row["admin_num"]]["Orders_TOTAL"]+=$dataList[$row["admin_num"]]["BANK_TOTAL"];	//加總儲值小計
					$dataList[$row["admin_num"]]["ALL_TOTAL"]+=$dataList[$row["admin_num"]]["BANK_TOTAL"];	//最後小計 計算
				}else{
					$data=array();
					$data["num"]=$row["admin_num"];
					$data["u_id"]=tb_sql('u_id','admin',$row["admin_num"]);
					$data["u_name"]=tb_sql('u_name','admin',$row["admin_num"]);	
					$data["u_power"]=tb_sql('u_power','admin',$row["admin_num"]);
					$data["BANK_TOTAL"]=($row["BANK_TOTAL"]!="" ? $row["BANK_TOTAL"] : 0);
					$data["Orders_TOTAL"]=0;	//儲值小計預設值
					$data["Orders_TOTAL"]+=$data["BANK_TOTAL"];	//加總儲值小計
					$data["ALL_TOTAL"]=0;	//最後小計預設值
					$data["ALL_TOTAL"]+=$data["BANK_TOTAL"];	//最後小計計算
					$dataList[$row["admin_num"]]=$data;
				}
			}
		}
	
		//抓出寶物拋售
		$parameter=array();
		$sqlStr="select SUM(amount) as SELL_TOTAL,SUM(fee) as FEE_TOTAL,admin_num from `member_sell` where  `keyin1`=1";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY admin_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["admin_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["admin_num"]]["SELL_TOTAL"]=($row["SELL_TOTAL"]!="" ? $row["SELL_TOTAL"] : 0);
					$dataList[$row["admin_num"]]["FEE_TOTAL"]=($row["FEE_TOTAL"]!="" ? $row["FEE_TOTAL"] : 0);
					$dataList[$row["admin_num"]]["FEE_TOTAL"]=$dataList[$row["admin_num"]]["SELL_TOTAL"]-$dataList[$row["admin_num"]]["FEE_TOTAL"];
					$dataList[$row["admin_num"]]["ALL_TOTAL"]-=$dataList[$row["admin_num"]]["FEE_TOTAL"];	//最後小計 計算
				}else{
					$data=array();
					$data["num"]=$row["admin_num"];
					$data["u_id"]=tb_sql('u_id','admin',$row["admin_num"]);
					$data["u_name"]=tb_sql('u_name','admin',$row["admin_num"]);	
					$data["u_power"]=tb_sql('u_power','admin',$row["admin_num"]);
					$data["SELL_TOTAL"]=($row["SELL_TOTAL"]!="" ? $row["SELL_TOTAL"] : 0);
					$data["FEE_TOTAL"]=($row["FEE_TOTAL"]!="" ? $row["FEE_TOTAL"] : 0);
					$data["FEE_TOTAL"]=$data["SELL_TOTAL"]-$data["FEE_TOTAL"];
					$data["ALL_TOTAL"]=0;	//最後小計預設值
					$data["ALL_TOTAL"]-=$data["FEE_TOTAL"];	//最後小計計算
					$dataList[$row["admin_num"]]=$data;
				}
			}
		}
	
		//紅利贈點
		$parameter=array();
		$sqlStr="select SUM(points) as POINT_TOTAL,admin_num from `member_wallet`  where kind in (6,7,8,13)";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY admin_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["admin_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["admin_num"]]["POINT_TOTAL"]=($row["POINT_TOTAL"]!="" ? $row["POINT_TOTAL"] : 0);
				}else{
					$data=array();
					$data["num"]=$row["admin_num"];
					$data["u_id"]=tb_sql('u_id','admin',$row["admin_num"]);
					$data["u_name"]=tb_sql('u_name','admin',$row["admin_num"]);	
					$data["u_power"]=tb_sql('u_power','admin',$row["admin_num"]);
					$data["POINT_TOTAL"]=($row["POINT_TOTAL"]!="" ? $row["POINT_TOTAL"] : 0);
					$dataList[$row["admin_num"]]=$data;
				}
			}
		}
	
		
		if(count($dataList) > 0){
			foreach($dataList as $row){
				$this->data["ATMSUM"]+=@$row["ATM_TOTAL"];	//總ATM儲值金額
				$this->data["MMKSUM"]+=@$row["MMK_TOTAL"];	//總超商儲值金額
				$this->data["BANKSUM"]+=@$row["BANK_TOTAL"];	//總銀行匯款金額
				$this->data["OrdersSUM"]+=@$row["Orders_TOTAL"];	//儲值小計總額
				$this->data["SELLSUM"]+=@$row["SELL_TOTAL"];	//拋售總額
				$this->data["FEESUM"]+=@$row["FEE_TOTAL"];	//拋售手續費總額
				$this->data["POINTSUM"]+=@$row["POINT_TOTAL"];	//贈點統計
				$this->data["ALLSUM"]+=@$row["ALL_TOTAL"];	//小計總和
			}
				
		}
		
		
		
		//根據金額排序
		foreach($dataList as $key=>$row){
			$rowTotal[$key]  = @$row['ALL_TOTAL'];
		}
		@array_multisort($rowTotal, SORT_DESC, $dataList);

		
		
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/trade/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}
	
	public function show_member($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($admin_num==''){
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...該代理不存在');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
			
		}
		$this->data["agent_num"]=$admin_num;
		$sDate=date('Y-m-'."01 00:00:00");	//預設起始日期
		$eDate=date('Y-m-d H:i:s',strtotime($sDate."+1 month -1 second"));	//預設結束日期
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sDate=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			//$eDate=date('Y-m-d',strtotime($_REQUEST["find8"]."+1 day"));
			$eDate=@$_REQUEST["find8"];
		}
		$this->data["ATMSUM"]=0;	//總ATM儲值金額
		$this->data["MMKSUM"]=0;	//總超商儲值金額
		$this->data["BANKSUM"]=0;	//總銀行匯款金額
		$this->data["OrdersSUM"]=0;	//儲值小計總額
		$this->data["SELLSUM"]=0;	//拋售總額
		$this->data["FEESUM"]=0;	//拋售手續費總額
		$this->data["POINTSUM"]=0;	//贈點統計
		$this->data["ALLSUM"]=0;	//小計總和
		$this->data["sDate"]=$sDate;
		$this->data["eDate"]=$eDate;
		
		$dataList=array();
		
		//抓出ATM收款總結
		$parameter=array();
		$sqlStr="select SUM(amount) as ATM_TOTAL,mem_num from `orders` where `payment`='ATM'  and `keyin2`=1";
		$sqlStr.=" and `admin_num` = ?";
		$parameter[':admin_num']=$admin_num;
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
				$data["u_name"]=tb_sql('u_name','member',$row["mem_num"]);	
				$data["ATM_TOTAL"]=($row["ATM_TOTAL"]!="" ? $row["ATM_TOTAL"] : 0);
				$data["Orders_TOTAL"]=0;	//儲值小計預設值
				$data["Orders_TOTAL"]+=$data["ATM_TOTAL"];	//加總儲值小計
				$data["ALL_TOTAL"]=0;	//最後小計預設值
				$data["ALL_TOTAL"]+=$data["ATM_TOTAL"];	//最後小計計算
				$dataList[$row["mem_num"]]=$data;
			}
		}
		//抓出超商收款總結
		$parameter=array();
		$sqlStr="select SUM(amount) as MMK_TOTAL,mem_num from `orders` where (`payment`='IBON' || `payment`='FAMI')  and `keyin2`=1";
		$sqlStr.=" and `admin_num` = ?";
		$parameter[':admin_num']=$admin_num;
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["mem_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["mem_num"]]["MMK_TOTAL"]=($row["MMK_TOTAL"]!="" ? $row["MMK_TOTAL"] : 0);
					$dataList[$row["mem_num"]]["Orders_TOTAL"]+=$dataList[$row["mem_num"]]["MMK_TOTAL"];	//加總儲值小計
					$dataList[$row["mem_num"]]["ALL_TOTAL"]+=$dataList[$row["mem_num"]]["MMK_TOTAL"];	//最後小計 計算
				}else{
					$data=array();
					$data["num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_name"]=tb_sql('u_name','member',$row["mem_num"]);	
					$data["MMK_TOTAL"]=($row["MMK_TOTAL"]!="" ? $row["MMK_TOTAL"] : 0);
					$data["Orders_TOTAL"]=0;	//儲值小計預設值
					$data["Orders_TOTAL"]+=$data["MMK_TOTAL"];	//加總儲值小計
					$data["ALL_TOTAL"]=0;	//最後小計預設值
					$data["ALL_TOTAL"]+=$data["MMK_TOTAL"];	//最後小計計算
					$dataList[$row["mem_num"]]=$data;
				}
			}
		}
		
		//抓出銀行匯款總結
		$parameter=array();
		$sqlStr="select SUM(amount) as BANK_TOTAL,mem_num from `member_bank_transfer` where  `keyin2`=1";
		$sqlStr.=" and `admin_num` = ?";
		$parameter[':admin_num']=$admin_num;
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["mem_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["mem_num"]]["BANK_TOTAL"]=($row["BANK_TOTAL"]!="" ? $row["BANK_TOTAL"] : 0);
					$dataList[$row["mem_num"]]["Orders_TOTAL"]+=$dataList[$row["mem_num"]]["BANK_TOTAL"];	//加總儲值小計
					$dataList[$row["mem_num"]]["ALL_TOTAL"]+=$dataList[$row["mem_num"]]["BANK_TOTAL"];	//最後小計 計算
				}else{
					$data=array();
					$data["num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_name"]=tb_sql('u_name','member',$row["mem_num"]);	
					$data["BANK_TOTAL"]=($row["BANK_TOTAL"]!="" ? $row["BANK_TOTAL"] : 0);
					$data["Orders_TOTAL"]=0;	//儲值小計預設值
					$data["Orders_TOTAL"]+=$data["BANK_TOTAL"];	//加總儲值小計
					$data["ALL_TOTAL"]=0;	//最後小計預設值
					$data["ALL_TOTAL"]+=$data["BANK_TOTAL"];	//最後小計計算
					$dataList[$row["mem_num"]]=$data;
				}
			}
		}
	
		//抓出寶物拋售
		$parameter=array();
		$sqlStr="select SUM(amount) as SELL_TOTAL,SUM(fee) as FEE_TOTAL,mem_num from `member_sell` where  `keyin1`=1";
		$sqlStr.=" and `admin_num` = ?";
		$parameter[':admin_num']=$admin_num;
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["mem_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["mem_num"]]["SELL_TOTAL"]=($row["SELL_TOTAL"]!="" ? $row["SELL_TOTAL"] : 0);
					$dataList[$row["mem_num"]]["FEE_TOTAL"]=($row["FEE_TOTAL"]!="" ? $row["FEE_TOTAL"] : 0);
					$dataList[$row["mem_num"]]["FEE_TOTAL"]=$dataList[$row["mem_num"]]["SELL_TOTAL"]-$dataList[$row["mem_num"]]["FEE_TOTAL"];
					$dataList[$row["mem_num"]]["ALL_TOTAL"]-=$dataList[$row["mem_num"]]["FEE_TOTAL"];	//最後小計 計算
				}else{
					$data=array();
					$data["num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_name"]=tb_sql('u_name','member',$row["mem_num"]);	
					$data["SELL_TOTAL"]=($row["SELL_TOTAL"]!="" ? $row["SELL_TOTAL"] : 0);
					$data["FEE_TOTAL"]=($row["FEE_TOTAL"]!="" ? $row["FEE_TOTAL"] : 0);
					$data["FEE_TOTAL"]=$data["SELL_TOTAL"]-$data["FEE_TOTAL"];
					$data["ALL_TOTAL"]=0;	//最後小計預設值
					$data["ALL_TOTAL"]-=$data["FEE_TOTAL"];	//最後小計計算
					$dataList[$row["mem_num"]]=$data;
				}
			}
		}
	
		//紅利贈點
		$parameter=array();
		$sqlStr="select SUM(points) as POINT_TOTAL,mem_num from `member_wallet`  where kind in (6,7,8,13)";
		$sqlStr.=" and `admin_num` = ?";
		$parameter[':admin_num']=$admin_num;
		$sqlStr.=" and `buildtime` >=?";
		$sqlStr.=" and `buildtime` <= ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["mem_num"],$dataList)){	//若該筆資料已存在則加入金額
					$dataList[$row["mem_num"]]["POINT_TOTAL"]=($row["POINT_TOTAL"]!="" ? $row["POINT_TOTAL"] : 0);
				}else{
					$data=array();
					$data["num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_name"]=tb_sql('u_name','member',$row["mem_num"]);	
					$data["POINT_TOTAL"]=($row["POINT_TOTAL"]!="" ? $row["POINT_TOTAL"] : 0);
					$dataList[$row["mem_num"]]=$data;
				}
			}
		}
	
		
		if(count($dataList) > 0){
			foreach($dataList as $row){
				$this->data["ATMSUM"]+=@$row["ATM_TOTAL"];	//總ATM儲值金額
				$this->data["MMKSUM"]+=@$row["MMK_TOTAL"];	//總超商儲值金額
				$this->data["BANKSUM"]+=@$row["BANK_TOTAL"];	//總銀行匯款金額
				$this->data["OrdersSUM"]+=@$row["Orders_TOTAL"];	//儲值小計總額
				$this->data["SELLSUM"]+=@$row["SELL_TOTAL"];	//拋售總額
				$this->data["FEESUM"]+=@$row["FEE_TOTAL"];	//拋售手續費總額
				$this->data["POINTSUM"]+=@$row["POINT_TOTAL"];	//贈點統計
				$this->data["ALLSUM"]+=@$row["ALL_TOTAL"];	//小計總和
			}
				
		}
		
		
		
		//根據金額排序
		foreach($dataList as $key=>$row){
			$rowTotal[$key]  = @$row['ALL_TOTAL'];
		}
		@array_multisort($rowTotal, SORT_DESC, $dataList);
		
		
		//根據金額排序
		foreach($dataList as $key=>$row){
			$rowTotal[$key]  = @$row['ALL_TOTAL'];
		}
		@array_multisort($rowTotal, SORT_DESC, $dataList);
		
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/trade/show_member", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}

	
} 