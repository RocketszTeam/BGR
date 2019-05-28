<?php
include_once (dirname(__FILE__)."/Core_model.php");

class Daily_report_model extends Core_model {
	protected $table ;
	
	public function __construct(){ 
        parent::__construct();
    }
	
	public function report_all($root,$d1,$d2,$makers_num){
		$whereSql='';
		$sqlStr="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `daily_report`  where makers_num='".$makers_num."'".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':find8']=$d2;
		}
		$sqlStr.=" group by agent_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["agent_num"];
				$data["u_id"]=tb_sql('u_id','admin',$row["agent_num"]);	
				$data["u_power"]=tb_sql('u_power','admin',$row["agent_num"]);
				$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
				$data["Profit"]=($row["TotalProfit"]!="" ? $row["TotalProfit"] : 0);
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	public function buildData($dataList,$rowAll,$prefix){
		if($rowAll!=NULL){
			foreach($rowAll as $keys=>$row){
				if(array_key_exists($keys,$dataList)){	//若該筆資料已存在 則加入資料
					$dataList[$keys][$prefix."_betAmount"]=(isset($row["betAmount"]) ? $row["betAmount"] : 0);
					$dataList[$keys][$prefix."_validAmount"]=(isset($row["validAmount"]) ? $row["validAmount"] : 0);
					$dataList[$keys][$prefix."_winOrLoss"]=(isset($row["winOrLoss"]) ? $row["winOrLoss"] : 0);
					$dataList[$keys][$prefix."_Profit"]=(isset($row["Profit"]) ? $row["Profit"] : 0);
					$dataList[$keys][$prefix."_Totals"]=(isset($row["totals"]) ? $row["totals"] : 0);
				}else{
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=($row["u_power"]!=6 ? $row["u_power"] : NULL);
					$data[$prefix."_betAmount"]=(isset($row["betAmount"]) ? $row["betAmount"] : 0);
					$data[$prefix."_validAmount"]=(isset($row["validAmount"]) ? $row["validAmount"] : 0);
					$data[$prefix."_winOrLoss"]=(isset($row["winOrLoss"]) ? $row["winOrLoss"] : 0);
					$data[$prefix."_Profit"]=(isset($row["Profit"]) ? $row["Profit"] : 0);
					$data[$prefix."_Totals"]=(isset($row["totals"]) ? $row["totals"] : 0);
					$dataList[$keys]=$data;
				}
			}
		}
		return $dataList;
	}



	//紅利計算
	public function member_points($num,$u_power,$d1,$d2){
		$parameter=array();
		$sqlStr="select SUM(real_points) as TotalRealPoints";
		switch ($u_power){
			case 4:	//股東
				$sqlStr.=",SUM(u_power4_profit) as PointsProfit";
				$whereql=" and `admin_num2`=".$num;
				break;
			case 5:	//總代
				$sqlStr.=",SUM(u_power5_profit) as PointsProfit";
				$whereql=" and `admin_num1`=".$num;
				break;
			case 6:	//代理
				$sqlStr.=",SUM(u_power6_profit) as PointsProfit";
				$whereql=" and `admin_num`=".$num;
				break;
		}
		$sqlStr.=" from `member_wallet` where kind in(6,7,8,13)".$whereql;
		//===開始日期=======================
		if($d1!=""){
			$sqlStr.=" and `buildtime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `buildtime` < ?";
			$parameter[':d2']=date('Y-m-d',strtotime($d2."+1 day"));
		}
				
		return $this->sqlRow($sqlStr,$parameter);
	}
	
}
