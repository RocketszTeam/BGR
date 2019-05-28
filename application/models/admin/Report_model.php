<?php
include_once (dirname(__FILE__)."/Core_model.php");

class Report_model extends Core_model {
	protected $table ;
	
	public function __construct(){ 
        parent::__construct();
    }
	
	//歐博報表資料
	public function allbet($root,$d1,$d2,$select=false,$showself){
		$whereSql='';		
		$sqlStr="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		
		
		
		$sqlStr.=" from `allbet_report`  where 1=1".$whereSql;
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
		//===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
        }
		$sqlStr.=" group by agent_num";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
//		print_r($sqlStr);print_r($parameter);exit;
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	
	
	//歐博會員帳
	public function allbet_member($num,$d1,$d2){
		$sqlStr="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
		$sqlStr.=",SUM(winOrLoss) as TotalwinOrLoss";
		$sqlStr.=" from `allbet_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//歐博會員洗碼
	public function allbet_rake($d1,$d2){
		$sqlStr="select SUM(validAmount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `allbet_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	
	//歐博電子
	public function allbet_egame($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";

		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}

		$sqlStr.=" from `allbet_egame_report`  where 1=1".$whereSql;
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
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
	
	//歐博電子會員帳
	public function allbet_egame_member($num,$d1,$d2){
		$sqlStr="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
		$sqlStr.=",SUM(winOrLoss) as TotalwinOrLoss";
		$sqlStr.=" from `allbet_egame_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//歐博電子會員洗碼
	public function allbet_egame_rake($d1,$d2){
		$sqlStr="select SUM(validAmount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `allbet_egame_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	//沙龍報表資料
	public function sagame($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(ValidAmount) as TotalvalidAmount,SUM(ResultAmount) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
		
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `sagame_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `PayoutTime` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `PayoutTime` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//沙龍會員帳
	public function sagame_member($num,$d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(ValidAmount) as TotalvalidAmount";
		$sqlStr.=",SUM(ResultAmount) as TotalwinOrLoss";
		$sqlStr.=" from `sagame_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `PayoutTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `PayoutTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//沙龍會員洗碼
	public function sagame_rake($d1,$d2){
		$sqlStr="select SUM(ValidAmount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `sagame_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `PayoutTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `PayoutTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//Super 報表資料
	public function super($root,$d1,$d2,$select,$showself=""){
		$whereSql='';
		$sqlStr="select SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount,SUM(result_gold) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
		
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `super_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `m_date` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `m_date` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	//Super會員帳
	public function super_member($num,$d1,$d2){
		$sqlStr="select SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount";
		$sqlStr.=",SUM(result_gold) as TotalwinOrLoss";
		$sqlStr.=" from `super_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `m_date` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `m_date` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//Super會員洗碼
	public function super_rake($d1,$d2){
		$sqlStr="select SUM(bet_gold) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `super_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `count_date` >=?";
			$parameter[':d1']=date('Y-m-d',strtotime($d1));
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `count_date` <= ?";
			$parameter[':d2']=date('Y-m-d',strtotime($d2));
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//捕魚機報表資料
	public function fish($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(profit) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `fish_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `bettimeStr` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `bettimeStr` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//捕魚機會員帳
	public function fish_member($num,$d1,$d2){
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(profit) as TotalwinOrLoss";
		$sqlStr.=" from `fish_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `bettimeStr` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `bettimeStr` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//捕魚機會員洗碼
	public function fish_rake($d1,$d2){
		$sqlStr="select SUM(bet) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `fish_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `bettimeStr` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `bettimeStr` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//PG電子報表資料(緯來)
	public function pg($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(winlose) as TotalwinOrLoss,Count(*) as totals";
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}	
		$sqlStr.=" from `grand_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `bdate` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `bdate` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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

	//PG電子會員帳
	public function pg_member($num,$d1,$d2){
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(bet) as TotalvalidAmount";
		$sqlStr.=",SUM(winlose) as TotalwinOrLoss";
		$sqlStr.=" from `grand_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `bdate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `bdate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	
	//PG電子會員洗碼
	public function pg_rake($d1,$d2){
		$sqlStr="select SUM(bet) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `grand_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `bdate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `bdate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//QT電子報表資料
	public function qt($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(totalBet) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `qtech_report`  where `status`='COMPLETED'".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `initiated` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `initiated` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	//QT會員帳
	public function qt_member($num,$d1,$d2){
		$sqlStr="select SUM(totalBet) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss";
		$sqlStr.=" from `qtech_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `initiated` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `initiated` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//qt會員洗碼
	public function qt_rake($d1,$d2){
		$sqlStr="select SUM(totalBet) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `qtech_report` where `status`='COMPLETED'";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `initiated` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `initiated` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//dg真人報表資料
	public function dg($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(betPoints) as TotalbetAmount,SUM(availableBet) as TotalvalidAmount,SUM(totalWinlose) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `dreamgame_report`  where 1=1".$whereSql;
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
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	//DG會員帳
	public function dg_member($num,$d1,$d2){
		$sqlStr="select SUM(betPoints) as TotalbetAmount,SUM(availableBet) as TotalvalidAmount";
		$sqlStr.=",SUM(totalWinlose) as TotalwinOrLoss";
		$sqlStr.=" from `dreamgame_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//dg會員洗碼
	public function dg_rake($d1,$d2){
		$sqlStr="select SUM(availableBet) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `dreamgame_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	//贏家 報表資料
	public function ssb($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount,SUM(meresult) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `ssb_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `orderdate` >=?";
			$parameter[':d1']=date('Ymd',strtotime($d1));
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `orderdate` <= ?";
			$parameter[':d2']=date('Ymd',strtotime($d2));
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);					
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	//贏家會員帳
	public function ssb_member($num,$d1,$d2){
		$sqlStr="select SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount";
		$sqlStr.=",SUM(meresult) as TotalwinOrLoss";
		$sqlStr.=" from `ssb_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `added_date` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `added_date` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//贏家會員洗碼
	public function ssb_rake($d1,$d2){
		$sqlStr="select SUM(gold_c) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `ssb_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `orderdate` >=?";
			$parameter[':d1']=date('Ymd',strtotime($d1));
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `orderdate` <= ?";
			$parameter[':d2']=date('Ymd',strtotime($d2));
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	//7pk
	public function s7pk($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";

		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `7pk_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `WagersDate` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `WagersDate` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
	//7pk會員帳
	public function s7pk_member($num,$d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss";
		$sqlStr.=" from `7pk_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `WagersDate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `WagersDate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//7PK會員洗碼
	public function s7pk_rake($d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `7pk_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `WagersDate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `WagersDate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	//Bingo Start 賓果
	public function bingo($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(real_bet) as TotalvalidAmount,SUM(win_lose) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `bingo_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `created_at` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `created_at` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}


	//Bingo Start 賓果會員
	public function bingo_member($num,$d1,$d2){
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(real_bet) as TotalvalidAmount";
		$sqlStr.=",SUM(win_lose) as TotalwinOrLoss";
		$sqlStr.=" from `bingo_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `created_at` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `created_at` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//賓果賓果會員洗碼
	public function bingo_rake($d1,$d2){
		$sqlStr="select SUM(real_bet) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `bingo_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `created_at` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `created_at` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	//Ebet報表資料
	public function ebet($root,$d1,$d2){
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
		$sqlStr.=" from `ebet_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `payoutTime` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `payoutTime` <= ?";
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
	
	//Ebet會員帳
	public function ebet_member($num,$d1,$d2){
		$sqlStr="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
		$sqlStr.=",SUM(winOrLoss) as TotalwinOrLoss";
		$sqlStr.=" from `ebet_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `payoutTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `payoutTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//水立方報表資料
	public function water($root,$d1,$d2){
		$whereSql='';
		$sqlStr="select SUM(BetGold) as TotalbetAmount,SUM(RealBetPoint) as TotalvalidAmount,SUM(WinGold) as TotalwinOrLoss,Count(*) as totals";
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
		$sqlStr.=" from `water_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `BetDate` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `BetDate` <= ?";
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
	
	//水立方會員帳
	public function water_member($num,$d1,$d2){
		$sqlStr="select SUM(BetGold) as TotalbetAmount,SUM(RealBetPoint) as TotalvalidAmount";
		$sqlStr.=",SUM(WinGold) as TotalwinOrLoss";
		$sqlStr.=" from `water_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `BetDate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `BetDate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//北京賽車報表
	public function s9k168($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `9k168_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `WagerDate` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `WagerDate` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}


	//北京賽車會員障
	public function s9k168_member($num,$d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
		$sqlStr.=",SUM(winOrLoss) as TotalwinOrLoss";
		$sqlStr.=" from `9k168_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `WagerDate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `WagerDate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//北京賽車會員洗碼
	public function s9k168_rake($d1,$d2){
		$sqlStr="select SUM(validAmount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `9k168_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `WagerDate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `WagerDate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	//新北京賽車報表
	public function pk10($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(amount) as TotalbetAmount,SUM(amount) as TotalvalidAmount,SUM(WinorLoss) as TotalwinOrLoss,Count(*) as totals";
		$u_power = tb_sql('u_power','admin',$root);
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `pk10_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$d1!=""){
			$sqlStr.=" and `actionTime` >=?";
			$parameter[':d1']=strtotime($d1);
		}
		//===終止日期=======================
		if(@$d2!=""){
			$sqlStr.=" and `actionTime` <= ?";
			$parameter[':d2']=strtotime($d2);			
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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

	//北京賽車會員障
	public function pk10_member($num,$d1,$d2){
		$sqlStr="select SUM(amount) as TotalbetAmount,SUM(amount) as TotalvalidAmount";
		$sqlStr.=",SUM(WinorLoss) as TotalwinOrLoss";
		$sqlStr.=" from `pk10_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		//===起始日期=======================
		if(@$d1!=""){
			$sqlStr.=" and `actionTime` >=?";
			$parameter[':d1']=strtotime($d1);
		}
		//===終止日期=======================
		if(@$d2!=""){
			$sqlStr.=" and `actionTime` <= ?";
			$parameter[':d2']=strtotime($d2);			
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//新北京賽車會員洗碼
	public function pk10_rake($d1,$d2){
		$sqlStr="select SUM(amount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `pk10_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `actionTime` >=?";
			$parameter[':d1']=strtotime($d1);
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `actionTime` <= ?";
			$parameter[':d2']=strtotime($d2);
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	

	//AMEBA報表資料
	public function ameba($root,$d1,$d2,$select="",$showself){
		$whereSql='';
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals, SUM(u_power4_profit) as TotalProfit4,SUM(u_power5_profit) as TotalProfit5,SUM(u_power6_profit) as TotalProfit6";
		$u_power = tb_sql('u_power','admin',$root);
				
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif($u_power==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif($u_power==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `ameba_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `WagerDate` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `WagerDate` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				//$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
				$data["Profit"]=($row["TotalProfit"]!="" ? $row["TotalProfit"] : 0);
				$data["Profit4"]=($row["TotalProfit4"]!="" ? $row["TotalProfit4"] : 0);
				$data["Profit5"]=($row["TotalProfit5"]!="" ? $row["TotalProfit5"] : 0);
				$data["Profit6"]=($row["TotalProfit6"]!="" ? $row["TotalProfit6"] : 0);		
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	//AMEBA會員帳
	public function ameba_member($num,$d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss";
		$sqlStr.=" from `ameba_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `WagerDate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `WagerDate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	//AMEBA會員洗碼
	public function ameba_rake($d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `ameba_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `WagerDate` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `WagerDate` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}

	//泛亞電競
	public function avia($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(Money) as TotalwinOrLoss,Count(*) as totals";
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";

		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `avia_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `CreateAt` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `CreateAt` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
				//$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
				$data["Profit"]=($row["TotalProfit"]!="" ? $row["TotalProfit"] : 0);
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	//泛亞電競會員帳
	public function avia_member($num,$d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(Money) as TotalwinOrLoss";
		$sqlStr.=" from `avia_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `CreateAt` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `CreateAt` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}
	
	
	//泛亞電競會員洗碼
	public function avia_rake($d1,$d2){
		$sqlStr="select SUM(BetAmount) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `avia_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `CreateAt` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `CreateAt` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	
	
	
	//皇朝電競
	public function hces888($root,$d1,$d2,$select,$selectmem,$showself){
		$whereSql='';
		$sqlStr="select SUM(betamount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";

		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			}
			$whereSql.=" and `u_power4`=?";
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			if($showself){
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
			}
			$whereSql.=" and `u_power5`=?";
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
			$whereSql.=" and `u_power6`=?";
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `hces888_report`  where 1=1".$whereSql;
		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `bettime` >=?";
			$parameter[':find7']=$d1;
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `bettime` <= ?";
			$parameter[':find8']=$d2;
		}
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
        }
		//===搜尋會員帳號====================
        if($selectmem){
            $sqlStr.=" and `mem_num` in (?)";
            $parameter[':find10']=implode(',',$selectmem);
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
				//$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
				$data["Profit"]=($row["TotalProfit"]!="" ? $row["TotalProfit"] : 0);
				$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
				$dataList[$row["agent_num"]]=$data;
			}
		}
		return $dataList;
	}
	
	//皇朝電競會員帳
	public function hces888_member($num,$d1,$d2){
		$sqlStr="select SUM(betamount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss";
		$sqlStr.=" from `hces888_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `bettime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `bettime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);
	}
	
	
	
	
	//皇朝電競會員洗碼
    public function hces888_rake($d1, $d2)
    {
        $sqlStr = "select SUM(betamount) as TotalvalidAmount,mem_num";
        $sqlStr .= " from `hces888_report` where 1=1";
        $parameter = array();
        if ($d1 != "") {
            $sqlStr .= " and `bettime` >=?";
            $parameter[':d1'] = $d1;
        }
        //===終止日期=======================
        if ($d2 != "") {
            $sqlStr .= " and `bettime` <= ?";
            $parameter[':d2'] = $d2;
        }
        $sqlStr .= " GROUP BY mem_num";
        $rowAll = $this->sqlRowList($sqlStr, $parameter);
        $dataList = array();
        if ($rowAll != NULL) {
            foreach ($rowAll as $row) {
                $data = array();
                $data["num"] = $row["mem_num"];
                $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                $dataList[$row["mem_num"]] = $data;
            }
        }
        return $dataList;
    }
	
	
	
	//VG
    public function vg($root, $d1, $d2, $select, $showself)
    {
        $whereSql = '';
        $sqlStr = "select SUM(betamount) as TotalbetAmount,SUM(validbetamount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
        if ($root == 0) {    //列出所有股東
            $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
        } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
            if ($showself) {
                // add for showself
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } else {
                $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            }
            $whereSql .= " and `u_power4`=?";
            $parameter[':u_power4'] = $root;
        } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
            if ($showself) {
                $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            } else {
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            }
            $whereSql .= " and `u_power5`=?";
            $parameter[':u_power5'] = $root;
        } else {    //代理列出自己
            $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            $whereSql .= " and `u_power6`=?";
            $parameter[':u_power6'] = $root;
        }
        $sqlStr .= " from `vg_report`  where 1=1" . $whereSql;
        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `createtime` >=?";
            $parameter[':find7'] = $d1;
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `createtime` <= ?";
            $parameter[':find8'] = $d2;
        }
        //===搜尋代理帳號====================
        if ($select) {
            $sqlStr .= " and `u_power6` in (?)";
            $parameter[':find9'] = implode(',', $select);
        }
        $sqlStr .= " group by agent_num";
        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $dataList = array();
        if ($rowAll != NULL) {
            foreach ($rowAll as $row) {
                $data = array();
                $data["num"] = $row["agent_num"];
                $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                $dataList[$row["agent_num"]] = $data;
            }
        }
        return $dataList;
    }

    //VG會員帳
    public function vg_member($num, $d1, $d2)
    {
        $sqlStr = "select SUM(betamount) as TotalbetAmount,SUM(validbetamount) as TotalvalidAmount, SUM(winOrLoss) as TotalwinOrLoss";
        $sqlStr .= " from `vg_report`  where mem_num=?";
        $parameter = array();
        $parameter[":mem_num"] = $num;
        if ($d1 != "") {
            $sqlStr .= " and `createtime` >=?";
            $parameter[':d1'] = $d1;
        }
        //===終止日期=======================
        if ($d2 != "") {
            $sqlStr .= " and `createtime` <= ?";
            $parameter[':d2'] = $d2;
        }
        $sqlStr .= " GROUP BY mem_num";
        return $this->sqlRow($sqlStr, $parameter);
    }
	
	
	
	//VG會員洗碼.
    public function vg_rake($d1, $d2)
    {
        $sqlStr = "select SUM(validbetamount) as TotalvalidAmount,mem_num";
        $sqlStr .= " from `vg_report` where 1=1";
        $parameter = array();
        if ($d1 != "") {
            $sqlStr .= " and `createtime` >=?";
            $parameter[':d1'] = $d1;
        }
        //===終止日期=======================
        if ($d2 != "") {
            $sqlStr .= " and `createtime` <= ?";
            $parameter[':d2'] = $d2;
        }
        $sqlStr .= " GROUP BY mem_num";
        $rowAll = $this->sqlRowList($sqlStr, $parameter);
        $dataList = array();
        if ($rowAll != NULL) {
            foreach ($rowAll as $row) {
                $data = array();
                $data["num"] = $row["mem_num"];
                $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                $dataList[$row["mem_num"]] = $data;
            }
        }
        return $dataList;
    }
	
	

	//WM報表
	public function wm($root,$d1,$d2,$select,$showself){
		$whereSql='';
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(validbet) as TotalvalidAmount,SUM(winLoss) as TotalwinOrLoss,Count(*) as totals";
		if($root==0){	//列出所有股東
			$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
		}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
			if($showself){
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";				
			}
			$whereSql.=" and `u_power4`=?";	
			$parameter[':u_power4']=$root;
		}elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
			if($showself){		
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
			} else {
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			}
			$whereSql.=" and `u_power5`=?";	
			$parameter[':u_power5']=$root;
		}else{	//代理列出自己
			$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";				
			$whereSql.=" and `u_power6`=?";	
			$parameter[':u_power6']=$root;
		}
		$sqlStr.=" from `wm_report`  where 1=1".$whereSql;
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
		//===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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
	//WM會員明細
	public function wm_member($num,$d1,$d2){	
		$sqlStr="select SUM(bet) as TotalbetAmount,SUM(winLoss) as TotalwinOrLoss,SUM(validbet) as TotalvalidAmount";
		$sqlStr.=" from `wm_report`  where mem_num=?";
		$parameter=array();
		$parameter[":mem_num"]=$num;
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		return $this->sqlRow($sqlStr,$parameter);	
	}	

	//WM會員洗碼
	public function wm_rake($d1,$d2){
		$sqlStr="select SUM(validbet) as TotalvalidAmount,mem_num";
		$sqlStr.=" from `wm_report` where 1=1";
		$parameter=array();
		if($d1!=""){
			$sqlStr.=" and `betTime` >=?";
			$parameter[':d1']=$d1;
		}
		//===終止日期=======================
		if($d2!=""){
			$sqlStr.=" and `betTime` <= ?";
			$parameter[':d2']=$d2;
		}
		$sqlStr.=" GROUP BY mem_num";
		$rowAll=$this->sqlRowList($sqlStr,$parameter);
		$dataList=array();
		if($rowAll!=NULL){					
			foreach($rowAll as $row){					
				$data=array();
				$data["num"]=$row["mem_num"];
				$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
				$dataList[$row["mem_num"]]=$data;
			}
		}
		return $dataList;
	}

    /**
     * 彩播報表(game_maker = 41)
     * @param $root
     * @param $d1
     * @param $d2
     * @param $select
     * @param $showself
     * @return array
     */
    public function htpg($root,$d1,$d2,$select,$showself){
        $report_table = 'htpg_report';
        $whereSql='';
        $sqlStr="select SUM(amount) as TotalbetAmount,SUM(valid_amount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";

        $u_power = tb_sql('u_power','admin',$root);
        if($root==0){	//列出所有股東
            $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
        }elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
            if($showself){
                $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            }
            $whereSql.=" and `u_power4`=?";
            $parameter[':u_power4']=$root;
        }elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
            if($showself){
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            }
            $whereSql.=" and `u_power5`=?";
            $parameter[':u_power5']=$root;
        }else{	//代理列出自己
            $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            $whereSql.=" and `u_power6`=?";
            $parameter[':u_power6']=$root;
        }
        $sqlStr.=" from `".$report_table."`  where 1=1 and status != 4".$whereSql;
        //===起始日期=======================
        if(@$d1!=""){
            $sqlStr.=" and `created_at` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if(@$d2!=""){
            $sqlStr.=" and `created_at` <= ?";
            $parameter[':d2']=$d2;
        }
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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

    /**
     * 彩播會員帳(game_maker = 41)
     * @param $num
     * @param $d1
     * @param $d2
     * @return null
     */
    public function htpg_member($num,$d1,$d2){
        $report_table = 'htpg_report';
        $sqlStr="select SUM(amount) as TotalbetAmount,SUM(valid_amount) as TotalvalidAmount";
        $sqlStr.=",SUM(winOrLoss) as TotalwinOrLoss";
        $sqlStr.=" from `".$report_table."`  where status != 4 and mem_num=?";
        $parameter=array();
        $parameter[":mem_num"]=$num;
        //===起始日期=======================
        if(@$d1!=""){
            $sqlStr.=" and `created_at` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if(@$d2!=""){
            $sqlStr.=" and `created_at` <= ?";
            $parameter[':d2']=$d2;
        }
        $sqlStr.=" GROUP BY mem_num";
        return $this->sqlRow($sqlStr,$parameter);
    }

    /**
     * 彩播洗碼(game_maker = 41)
     * @param $d1
     * @param $d2
     * @return array
     */
    public function htpg_rake($d1,$d2){
        $report_table = 'htpg_report';
        $sqlStr="select SUM(valid_amount) as TotalvalidAmount,mem_num";
        $sqlStr.=" from `".$report_table."` where 1=1 and status != 4";
        $parameter=array();
        if($d1!=""){
            $sqlStr.=" and `created_at` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if($d2!=""){
            $sqlStr.=" and `created_at` <= ?";
            $parameter[':d2']=$d2;
        }
        $sqlStr.=" GROUP BY mem_num";
        $rowAll=$this->sqlRowList($sqlStr,$parameter);
        $dataList=array();
        if($rowAll!=NULL){
            foreach($rowAll as $row){
                $data=array();
                $data["num"]=$row["mem_num"];
                $data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                $dataList[$row["mem_num"]]=$data;
            }
        }
        return $dataList;
    }

	//RTG報表
    public function rtg($root,$d1,$d2,$select,$showself){
        $whereSql='';
        //echo 'root:'.$root ;
        //echo 'u_Power:'.tb_sql('u_power','admin',$root) ;
        $sqlStr="select SUM(bet) as TotalbetAmount,SUM(bet) as TotalvalidAmount,SUM(winOrLose) as TotalwinOrLoss,Count(*) as totals";
        if($root==0){	//列出所有股東
            $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
        }elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
            if($showself){
                $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            }
            $whereSql.=" and `u_power4`=?";
            $parameter[':u_power4']=$root;
        }elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
            if($showself){
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            }
            $whereSql.=" and `u_power5`=?";
            $parameter[':u_power5']=$root;
        }else{	//代理列出自己
            $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            $whereSql.=" and `u_power6`=?";
            $parameter[':u_power6']=$root;
        }
        $sqlStr.=" from `rtg_report`  where 1=1".$whereSql;
        //echo '<p style="margin-top:100px;"></p>' ;
        //echo 'sqlStr:'.$sqlStr .'<br> parameter:';
        //print_r($parameter);
        //===起始日期=======================
        if(@$_REQUEST["find7"]!=""){
            $sqlStr.=" and `gameStartDate` >=?";
            $parameter[':find7']=$d1;
        }
        //===終止日期=======================
        if(@$_REQUEST["find8"]!=""){
            $sqlStr.=" and `gameStartDate` <= ?";
            $parameter[':find8']=$d2;
        }
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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

	//rtg會員帳
    public function rtg_member($num,$d1,$d2){
        $sqlStr="select SUM(bet) as TotalbetAmount,SUM(bet) as TotalvalidAmount";
        $sqlStr.=",SUM(winOrLose) as TotalwinOrLoss";
        $sqlStr.=" from `rtg_report`  where mem_num=?";
        $parameter=array();
        $parameter[":mem_num"]=$num;
        if($d1!=""){
            $sqlStr.=" and `gameStartDate` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if($d2!=""){
            $sqlStr.=" and `gameStartDate` <= ?";
            $parameter[':d2']=$d2;
        }
        $sqlStr.=" GROUP BY mem_num";
        return $this->sqlRow($sqlStr,$parameter);
    }

	//rtg會員洗碼
    public function rtg_rake($d1,$d2){
        $sqlStr="select SUM(bet) as TotalvalidAmount,mem_num";
        $sqlStr.=" from `rtg_report` where 1=1";
        $parameter=array();
        if($d1!=""){
            $sqlStr.=" and `gameStartDate` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if($d2!=""){
            $sqlStr.=" and `gameStartDate` <= ?";
            $parameter[':d2']=$d2;
        }
        $sqlStr.=" GROUP BY mem_num";
        $rowAll=$this->sqlRowList($sqlStr,$parameter);
        $dataList=array();
        if($rowAll!=NULL){
            foreach($rowAll as $row){
                $data=array();
                $data["num"]=$row["mem_num"];
                $data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                $dataList[$row["mem_num"]]=$data;
            }
        }
        return $dataList;
		
    }

    //eg
    public function eg($root,$d1,$d2,$select,$showself){
        $whereSql='';
        $sqlStr = "select SUM(betAmount) as TotalbetAmount,SUM(validBetAmount) as TotalvalidAmount,SUM(winLoss) as TotalwinOrLoss,Count(*) as totals";
        if($root==0){	//列出所有股東
            $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";

        }elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
            if($showself){
                $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            }
            $whereSql.=" and `u_power4`=?";
            $parameter[':u_power4']=$root;
        }elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
            if($showself){
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            }
            $whereSql.=" and `u_power5`=?";
            $parameter[':u_power5']=$root;
        }else{	//代理列出自己
            $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            $whereSql.=" and `u_power6`=?";
            $parameter[':u_power6']=$root;
        }
        $sqlStr.=" from `eg_report`  where 1=1".$whereSql;
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
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
		}
		/*
        //===搜尋會員帳號====================
        if($selectmem > 0){
            $sqlStr.=" and `mem_num` in (?)";
            $parameter[':find10']=implode(',',$selectmem);
		}
		*/
		$sqlStr.=" group by agent_num";
		//echo $sqlStr;
		//exit();
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

    //EG會員帳
    public function eg_member($num,$d1,$d2){
        $sqlStr = "select SUM(betAmount) as TotalbetAmount,SUM(validBetAmount) as TotalvalidAmount,SUM(winLoss) as TotalwinOrLoss,Count(*) as totals";
        $sqlStr.=" from `eg_report`  where mem_num=?";
        $parameter=array();
        $parameter[":mem_num"]=$num;
        if($d1!=""){
            $sqlStr.=" and `betTime` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if($d2!=""){
            $sqlStr.=" and `betTime` <= ?";
            $parameter[':d2']=$d2;
        }
        $sqlStr.=" GROUP BY mem_num";
        return $this->sqlRow($sqlStr,$parameter);
    }

    //EG會員洗碼
    public function eg_rake($d1, $d2)
    {
        $sqlStr = "select SUM(validBetAmount) as TotalvalidAmount,mem_num";
        $sqlStr .= " from `eg_report` where 1=1";
        $parameter = array();
        if ($d1 != "") {
            $sqlStr .= " and `betTime` >=?";
            $parameter[':d1'] = $d1;
        }
        //===終止日期=======================
        if ($d2 != "") {
            $sqlStr .= " and `betTime` <= ?";
            $parameter[':d2'] = $d2;
        }
        $sqlStr .= " GROUP BY mem_num";
        $rowAll = $this->sqlRowList($sqlStr, $parameter);
        $dataList = array();
        if ($rowAll != NULL) {
            foreach ($rowAll as $row) {
                $data = array();
                $data["num"] = $row["mem_num"];
                $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                $dataList[$row["mem_num"]] = $data;
            }
        }
        return $dataList;
    }

    //新濠報表
    public function cm($root,$d1,$d2,$select,$showself){
        $whereSql='';
        $sqlStr="select SUM(bet_amount) as TotalbetAmount,SUM(bet_amount) as TotalvalidAmount,SUM(win_amount) as TotalwinOrLoss,Count(*) as totals";
        if($root==0){	//列出所有股東
            $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
        }elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
            if($showself){
                $sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            }
            $whereSql.=" and `u_power4`=?";
            $parameter[':u_power4']=$root;
        }elseif(tb_sql('u_power','admin',$root)==5){	//總代身分列出代理
            if($showself){
                $sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
            } else {
                $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            }
            $whereSql.=" and `u_power5`=?";
            $parameter[':u_power5']=$root;
        }else{	//代理列出自己
            $sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
            $whereSql.=" and `u_power6`=?";
            $parameter[':u_power6']=$root;
        }
        $sqlStr.=" from `cm_report`  where 1=1".$whereSql;
        //echo '<p style="margin-top:100px;"></p>' ;
        //echo 'sqlStr:'.$sqlStr .'<br> parameter:';
        //print_r($parameter);
        //===起始日期=======================
        if(@$_REQUEST["find7"]!=""){
            $sqlStr.=" and `bet_date` >=?";
            $parameter[':find7']=$d1;
        }
        //===終止日期=======================
        if(@$_REQUEST["find8"]!=""){
            $sqlStr.=" and `bet_date` <= ?";
            $parameter[':find8']=$d2;
        }
        //===搜尋代理帳號====================
        if($select){
            $sqlStr.=" and `u_power6` in (?)";
            $parameter[':find9']=implode(',',$select);
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

    //新濠會員
    public function cm_member($num,$d1,$d2){
        $sqlStr="select SUM(bet_amount) as TotalbetAmount,SUM(bet_amount) as TotalvalidAmount";
        $sqlStr.=",SUM(win_amount) as TotalwinOrLoss";
        $sqlStr.=" from `cm_report`  where mem_num=?";
        $parameter=array();
        $parameter[":mem_num"]=$num;
        if($d1!=""){
            $sqlStr.=" and `bet_date` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if($d2!=""){
            $sqlStr.=" and `bet_date` <= ?";
            $parameter[':d2']=$d2;
        }
        $sqlStr.=" GROUP BY mem_num";
        return $this->sqlRow($sqlStr,$parameter);
    }

    //新濠洗碼
    public function cm_rake($d1,$d2){
        $sqlStr="select SUM(bet_amount) as TotalvalidAmount,mem_num";
        $sqlStr.=" from `cm_report` where 1=1";
        $parameter=array();
        if($d1!=""){
            $sqlStr.=" and `bet_date` >=?";
            $parameter[':d1']=$d1;
        }
        //===終止日期=======================
        if($d2!=""){
            $sqlStr.=" and `bet_date` <= ?";
            $parameter[':d2']=$d2;
        }
        $sqlStr.=" GROUP BY mem_num";
        $rowAll=$this->sqlRowList($sqlStr,$parameter);
        $dataList=array();
        if($rowAll!=NULL){
            foreach($rowAll as $row){
                $data=array();
                $data["num"]=$row["mem_num"];
                $data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                $dataList[$row["mem_num"]]=$data;
            }
        }
        return $dataList;
    }
	
	public function buildData($dataList,$rowAll,$prefix,$game_maker=null){
		if($rowAll!=NULL){
			foreach($rowAll as $keys=>$row){
				if(array_key_exists($keys,$dataList)){	//若該筆資料已存在 則加入資料
					$dataList[$keys][$prefix."_betAmount"]=(isset($row["betAmount"]) ? $row["betAmount"] : 0);
					if($game_maker == 35 || $game_maker ==36){//若是皇朝電競或泛亞電競 則投注額等於洗碼量
						$dataList[$keys][$prefix."_validAmount"]=(isset($row["betAmount"]) ? $row["betAmount"] : 0);
					}else{
						$dataList[$keys][$prefix."_validAmount"]=(isset($row["validAmount"]) ? $row["validAmount"] : 0);
					}
					$dataList[$keys][$prefix."_winOrLoss"]=(isset($row["winOrLoss"]) ? $row["winOrLoss"] : 0);
					$dataList[$keys][$prefix."_Profit"]=(isset($row["Profit"]) ? $row["Profit"] : 0);
					$dataList[$keys][$prefix."_Totals"]=(isset($row["totals"]) ? $row["totals"] : 0);
				}else{
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$data[$prefix."_betAmount"]=(isset($row["betAmount"]) ? $row["betAmount"] : 0);
					if($game_maker == 35 || $game_maker ==36){//若是皇朝電競或泛亞電競 則投注額等於洗碼量
						$data[$prefix."_validAmount"]=(isset($row["betAmount"]) ? $row["betAmount"] : 0);
					}else{
						$data[$prefix."_validAmount"]=(isset($row["validAmount"]) ? $row["validAmount"] : 0);
					}
					$data[$prefix."_winOrLoss"]=(isset($row["winOrLoss"]) ? $row["winOrLoss"] : 0);
					$data[$prefix."_Profit"]=(isset($row["Profit"]) ? $row["Profit"] : 0);
					$data[$prefix."_Totals"]=(isset($row["totals"]) ? $row["totals"] : 0);
					$dataList[$keys]=$data;
				}
			}
		}
		return $dataList;
	}


	public function buildMemberData($data,$row,$prefix,$game_maker=null){
		if($game_maker == 36 || $game_maker==35){ //皇朝電競與泛亞電競沒有洗碼量欄位，投注額就是洗碼量
			$data[$prefix."_betAmount"]=(isset($row["TotalbetAmount"]) ? $row["TotalbetAmount"] : 0);
			$data[$prefix."_validAmount"]=(isset($row["TotalbetAmount"]) ? $row["TotalbetAmount"] : 0);
			$data[$prefix."_winOrLoss"]=(isset($row["TotalwinOrLoss"]) ? $row["TotalwinOrLoss"] : 0);
		}else{
			$data[$prefix."_betAmount"]=(isset($row["TotalbetAmount"]) ? $row["TotalbetAmount"] : 0);
			$data[$prefix."_validAmount"]=(isset($row["TotalvalidAmount"]) ? $row["TotalvalidAmount"] : 0);
			$data[$prefix."_winOrLoss"]=(isset($row["TotalwinOrLoss"]) ? $row["TotalwinOrLoss"] : 0);
		}
		return $data;
	}

	public function buildRake($dataList,$rowAll,$prefix){
		if($rowAll!=NULL){
			foreach($rowAll as $keys=>$row){
				if(array_key_exists($keys,$dataList)){	//若該筆資料已存在 則加入資料
					$dataList[$keys][$prefix."_validAmount"]=(isset($row["validAmount"]) ? $row["validAmount"] : 0);
				}else{
					$data=array();
					$data["num"]=$row["num"];
					$data[$prefix."_validAmount"]=(isset($row["validAmount"]) ? $row["validAmount"] : 0);
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
			$sqlStr.=" and `buildtime` <= ?";
			$parameter[':d2']=$d2;
		}
				
		return $this->sqlRow($sqlStr,$parameter);
	}
}
