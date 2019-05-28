<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Char extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->data["toWeek"]=reportDate('tw');	
		$this->data["yeWeek"]=reportDate('yw');
		//本月
		$toMonth=reportDate('m');
		$this->data["toMonth"]=array('d1'=>$toMonth['d1'],'d2'=>$toMonth['d2']);
		//上月
		$ymMonth=reportDate('ym');
		$this->data["ymMonth"]=array('d1'=>$ymMonth['d1'],'d2'=>$ymMonth['d2']);	
		error_reporting(0);
	}
	
	public function index(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$ag_array=array(4,5,6);
		$kind_web_root_num = kind_sql($this->web_root_num,'admin');
		$this_web_root_num = $this->web_root_num.$kind_web_root_num;
		
		$find7 = $this->data["toWeek"]['d1'];
		$find8 = $this->data["toWeek"]['d2'];
		if(@$_REQUEST["find7"])
			$find7 = $_REQUEST["find7"];
		if(@$_REQUEST["find8"])
			$find8 = $_REQUEST["find8"];		
			
		$this->data["find7"] = $find7;
		$this->data["find8"] = $find8;		
		
		$sdiff = strtotime($find8) - strtotime($find7);
		$totalday = floor($sdiff/3600/24);
		$this->data["totalday"] = $totalday;		
		
		for($i=0;$i<$totalday;$i++){		
			/*
			$sDate=date("Y-m-d",strtotime($this->data["toWeek"]['d1']."+ $i day"));	//預設起始日期
			$eDate=date('Y-m-d',strtotime($sDate." +1 day"));	//預設結束日期
			*/
			if($find7)
				$sDate=date("Y-m-d",strtotime($find7."+ $i day"));	//預設起始日期
			else
				$sDate=date("Y-m-d",strtotime($this->data["toWeek"]['d1']."+ $i day"));	//預設起始日期
				
			$eDate=date('Y-m-d',strtotime($sDate." +1 day"));	//預設結束日期			


			$sql = "SELECT COUNT( DISTINCT (mem_num)) AS memberlogin FROM  `member_ip` WHERE buildtime >=  '".$sDate." 00:00:00' and buildTime <= '".$sDate." 23:59:59'";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sql.=" and `mem_num` in ( select num from member where admin_num in(".$this_web_root_num.") )";	
			}			
			
			$memberlogin = $this->webdb ->sqlRow($sql);
			$this->data['memberLogin'][$sDate]= $memberlogin['memberlogin'];

			$sql = "SELECT COUNT(*) AS memberreg FROM  `member` WHERE reg_time >=  '".$sDate." 00:00:00' and reg_time <= '".$sDate." 23:59:59'";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sql.=" and `admin_num` in(".$this_web_root_num.")";	
			}				
			
			$memberreg = $this->webdb ->sqlRow($sql);
			$this->data['memberreg'][$sDate]= $memberreg['memberreg'];
			
			//儲值人次
			$sqlStr="select COUNT(DISTINCT (mem_num)) as mysave from `orders` where `keyin2`=1 and buildtime >=  '".$sDate." 00:00:00' and buildTime <= '".$sDate." 23:59:59'";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sqlStr.=" and `admin_num` in(".$this_web_root_num.")";	
			}
			$rowSave = $this->webdb ->sqlRow($sqlStr);
			$this->data['memberSave'][$sDate]= $rowSave['mysave'];
			
			//匯款人次
			$sqlStr="select COUNT(DISTINCT (mem_num)) as mysave from `member_bank_transfer` where `keyin2`=1 and buildtime >=  '".$sDate." 00:00:00' and buildTime <= '".$sDate." 23:59:59'";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sqlStr.=" and `admin_num` in(".$this_web_root_num.")";	
			}
			$rowBank = $this->webdb ->sqlRow($sqlStr);
			$this->data['memberBank'][$sDate]= $rowBank['mysave'];
			

			//抓出儲值總結
			$parameter=array();
			$sqlStr="select SUM(amount) as ATM_TOTAL from `orders` where `keyin2`=1";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sqlStr.=" and `admin_num` in (?".$kind_web_root_num.")";
				$parameter[':admin_num']=	$this->web_root_num;
			}
			$sqlStr.=" and `buildtime` >=?";
			$sqlStr.=" and `buildtime` <?";
			$parameter[':sDate']=$sDate;
			$parameter[':eDate']=$eDate;
			$rowATM=$this->webdb->sqlRow($sqlStr,$parameter);
			$rowATM["ATM_TOTAL"]=($rowATM["ATM_TOTAL"]!="" ? $rowATM["ATM_TOTAL"] : 0);
			$this->data["ATM"][$i-1]=$rowATM["ATM_TOTAL"];
			
			//抓出銀行匯款總結
			$parameter=array();
			$sqlStr="select SUM(amount) as BANK_TOTAL from `member_bank_transfer` where `keyin2`=1";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sqlStr.=" and `admin_num` in (?".$kind_web_root_num.")";
				$parameter[':admin_num']=	$this->web_root_num;
			}
			$sqlStr.=" and `buildtime` >=?";
			$sqlStr.=" and `buildtime` < ?";
			$parameter[':sDate']=$sDate;
			$parameter[':eDate']=$eDate;
			$rowBank=$this->webdb->sqlRow($sqlStr,$parameter);
			$rowBank["BANK_TOTAL"]=($rowBank["BANK_TOTAL"]!="" ? $rowBank["BANK_TOTAL"] : 0);
			$this->data["Bank"][$i-1]=$rowBank["BANK_TOTAL"];
			
			//抓出寶物出售
			$parameter=array();
			$sqlStr="select SUM(amount) as SELL_TOTAL,SUM(fee) as FEE_TOTAL from `member_sell` where  `keyin1`=1 ";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sqlStr.=" and `admin_num` in (?".$kind_web_root_num.")";
				$parameter[':admin_num']=	$this->web_root_num;
			}
			$sqlStr.=" and `buildtime` >=?";
			$sqlStr.=" and `buildtime` < ?";
			$parameter[':sDate']=$sDate;
			$parameter[':eDate']=$eDate;
			$rowSell=$this->webdb->sqlRow($sqlStr,$parameter);
			$rowSell["SELL_TOTAL"]=($rowSell["SELL_TOTAL"]!="" ? $rowSell["SELL_TOTAL"] : 0);
			$this->data["Sell"][$i-1]=$rowSell["SELL_TOTAL"];


			//紅利贈點
			$parameter=array();
			$sqlStr="select SUM(points) as POINT_TOTAL  from `member_wallet`  where 1=1";
			if(in_array($this->web_root_u_power,$ag_array)){
				$sqlStr.=" and admin_num in (?".$kind_web_root_num.")";
				$parameter[':admin_num']=	$this->web_root_num;
			}
			$sqlStr.=" and kind in (6,7,8,13)";
			$sqlStr.=" and buildtime >=?";
			$sqlStr.=" and buildtime < ?";
			$parameter[':sDate']=$sDate;
			$parameter[':eDate']=$eDate;
			$rowPoint=$this->webdb->sqlRow($sqlStr,$parameter);
			$rowPoint["POINT_TOTAL"]=($rowPoint["POINT_TOTAL"]!="" ? $rowPoint["POINT_TOTAL"] : 0);
			$this->data["Point"][$i-1]=$rowPoint["POINT_TOTAL"];
		}
		
		$this->data["ATMTOTAL"]=array_sum($this->data["ATM"]) + array_sum($this->data["Bank"]);
		$this->data["SELLTOTAL"]=array_sum($this->data["Sell"]);
		$this->data["POINTTOTAL"]=array_sum($this->data["Point"]);
		
		//儲值排行榜
		/*
		$sDate=$this->data["toWeek"]['d1'];	//預設起始日期
		$eDate=$this->data["toWeek"]['d2'];	//預設起始日期
		$eDate=date('Y-m-d',strtotime($eDate." +1 day"));	//預設結束日期
		*/
		$sDate = $find7;
		$eDate = $find8;
		$eDate=date('Y-m-d',strtotime($eDate." +1 day"));	//預設結束日期		
		
		$this->data["rowOrders"]=array();
		//金流
		$parameter=array();
		$sqlStr="select SUM(amount) as amount,mem_num from `orders` where keyin2=1";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".$kind_web_root_num.")";
			$parameter[':admin_num']=$this->web_root_num;
		}
		$sqlStr.=" and `buildtime` >= ? and `buildtime` < ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY mem_num ORDER BY amount DESC Limit 7";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$data=array();
				$data['mem_num']=$row["mem_num"];
				$data['order_total']=$row["amount"];
				$this->data["rowOrders"][$row["mem_num"]]=$data;
			}
		}
		//銀行匯款
		$parameter=array();
		$sqlStr="select SUM(amount) as amount,mem_num from `member_bank_transfer` where keyin2=1";
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".$kind_web_root_num.")";
			$parameter[':admin_num']=$this->web_root_num;
		}
		$sqlStr.=" and `buildtime` >= ? and `buildtime` < ?";
		$parameter[':sDate']=$sDate;
		$parameter[':eDate']=$eDate;
		$sqlStr.=" GROUP BY mem_num ORDER BY amount DESC Limit 7";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				if(array_key_exists($row["mem_num"],$this->data["rowOrders"])){	//若該會員已存在則累加金額
					$this->data["rowOrders"][$row["mem_num"]]["order_total"]+=(int)$row["amount"];
				}else{
					$data=array();
					$data['mem_num']=$row["mem_num"];
					$data['order_total']=$row["amount"];
					$this->data["rowOrders"][$row["mem_num"]]=$data;
					
				}
			}
		}
		//根據金額排序
		foreach($this->data["rowOrders"] as $key=>$row){
			$rowTotal[$key]  = $row['order_total'];
		}
		@array_multisort($rowTotal, SORT_DESC, $this->data["rowOrders"]);
		$this->data["rowOrders"]=array_slice($this->data["rowOrders"],0,7);

		$this -> data["body"] = $this -> load -> view("admin/char/index", $this -> data,true);   
		$this -> load -> view("admin/main.php", $this -> data);  
	}
	

	
} 