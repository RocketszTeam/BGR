<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Report extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		error_reporting(0);
		date_default_timezone_set("Asia/Taipei");
		$this->load->library('api/allbetapi');	//歐博API
		$this->load->library('api/sagamingapi');	//沙龍
		$this->load->library('api/superapi');	//Super
		$this->load->library('api/dreamgame');	//Dg
		$this->load->library('api/wmapi');	//wm
					
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
	
	//歐博報表
	public function index($root=0){	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
					$newSql.=",SUM(winOrLoss) as TotalwinOrLoss";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `allbet_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `betTime` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `betTime` <= ?";
						$parameter2[':find8']=@$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
			$sqlStr.=",SUM(winOrLoss) as TotalwinOrLoss from `allbet_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `betTime` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `betTime` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`client` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/index/";
		$this->data["memberURL"]=SYSTEM_URL."/Report/allbet_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/index/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	//歐博會員明細
	public function allbet_details($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `allbet_report` where 1=1";
		
		$sqlSum="select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
		$sqlSum.=",SUM(winOrLoss) as TotalwinOrLoss from `allbet_report` where 1=1";
		
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `betTime` >=?";
			$sqlSum.=" and `betTime` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `betTime` <= ?";
			$sqlSum.=" and `betTime` <= ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report/allbet_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `betTime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		//$sqlStr.=" order by `betTime` DESC";
		
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this -> data["result"] = $rowAll;
		$this -> data["rowSum"]=$this->webdb->sqlRow($sqlSum,$parameter);
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		//定義遊戲類型
		$this->data["gameTypeArray"]=array(
						'101'=>'普通百家樂','102'=>'VIP百家樂',
						'103'=>'急速百家樂','104'=>'競咪百家樂',
						'201'=>'骰寶','301'=>'龍虎','401'=>'輪盤');
		
		$this->data["betTypeArray"]=$this->allbetapi->get_betType();
		$this->data["gameResultArray"]=$this->allbetapi->get_gameResult();
		
		$this -> data["root"]=$admin_num;
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/index/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
			
		$this -> data["body"] = $this -> load -> view("admin/report/allbet_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}
	//DG報表
	public function dreamgame($root=0){	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(betPoints) as TotalbetAmount,SUM(availableBet) as TotalvalidAmount";
					$newSql.=",SUM(totalWinlose) as TotalwinOrLoss , count(*) as totalNum ";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `dreamgame_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `BetTime` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `BetTime` <= ?";
						$parameter2[':find8']=@$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					$data["totalNum"]=$report_row["totalNum"];
					$data["newSql"]=$newSql;
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(betPoints) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss,SUM(availableBet) as TotalvalidAmount , count(*) as totalNum ";
			$sqlStr.=" from `dreamgame_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `BetTime` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `BetTime` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`userName` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					$data["totalNum"]=$row["totalNum"];
					$data["sqlStr"]=$sqlStr;
					$data["parameter"]= $parameter ;
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/dreamgame/";
		$this->data["memberURL"]=SYSTEM_URL."/Report/dreamgame_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/dreamgame/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;

		$this -> data["body"] = $this -> load -> view("admin/report/dreamgame", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	//dg會員明細
	public function dreamgame_details($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `dreamgame_report` where 1=1";
		$sqlSum="select SUM(betPoints) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss,SUM(availableBet) as TotalvalidAmount from `dreamgame_report` where 1=1";

		
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `BetTime` >=?";
			$sqlSum.=" and `BetTime` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `BetTime` <= ?";
			$sqlSum.=" and `BetTime` <= ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report/dreamgame_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `BetTime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		//$sqlStr.=" order by `betTime` DESC";
		
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this -> data["result"] = $rowAll;
		$this -> data["rowSum"]=$this->webdb->sqlRow($sqlSum,$parameter);
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		//定義遊戲類型
		$this->data["gameTypeArrays"]=array(
						'1'=>'百家樂','3'=>'龍虎',
						'5'=>'骰寶',
						'7'=>'牛牛','4'=>'輪盤',
						'8'=>'競咪百家樂');
		
		
		
		//$this->data["betTypeArray"]=$this->dreamgame->getBetType();
		
		
		$this -> data["root"]=$admin_num;	
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/dreamgame/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
		$this -> data["body"] = $this -> load -> view("admin/report/dreamgame_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}
	//WM報表
	public function WM($root=0){	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}

		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(bet) as TotalbetAmount,SUM(winLoss) as TotalwinOrLoss,SUM(validbet) as TotalvalidAmount";
					$newSql.="";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `WM_report`  where 1=1".$whereql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `BetTime` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `BetTime` <= ?";
						$parameter2[':find8']=$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);

					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				//}
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(bet) as TotalbetAmount,SUM(winLoss) as TotalwinOrLoss,SUM(validbet) as TotalvalidAmount";
			$sqlStr.=" from `WM_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `BetTime` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `BetTime` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`user` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/wm/";
		$this->data["memberURL"]=SYSTEM_URL."/Report/wm_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/wm/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;

		$this -> data["body"] = $this -> load -> view("admin/report/wm", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	//WM會員明細
	public function WM_details($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `WM_report` where 1=1";
		$sqlSum="select SUM(bet) as TotalbetAmount,SUM(winLoss) as TotalwinOrLoss,SUM(validbet) as TotalvalidAmount from `WM_report` where 1=1";

		
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `BetTime` >=?";
			$sqlSum.=" and `BetTime` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `BetTime` <= ?";
			$sqlSum.=" and `BetTime` <= ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report/wm_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `BetTime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		//$sqlStr.=" order by `betTime` DESC";
		
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this -> data["result"] = $rowAll;
		$this -> data["rowSum"]=$this->webdb->sqlRow($sqlSum,$parameter);
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		//定義遊戲類型
		$this->data["gameTypeArrays"]=array(
						'1'=>'百家樂','3'=>'龍虎',
						'5'=>'骰寶',
						'7'=>'牛牛','4'=>'輪盤',
						'8'=>'競咪百家樂');
		
		
		
		//$this->data["betTypeArray"]=$this->dreamgame->getBetType();
		
		
		$this -> data["root"]=$admin_num;	
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/wm/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
		$this -> data["body"] = $this -> load -> view("admin/report/wm_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}	
	//沙龍報表
	public function sagame($root=0){	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(BetAmount) as TotalbetAmount,SUM(ResultAmount) as TotalwinOrLoss,SUM(ValidAmount) as TotalvalidAmount,count(*) as totals";
					$newSql.="";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `sagame_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `BetTime` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `BetTime` <= ?";
						$parameter2[':find8']=$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					//print_r($report_row);
					//echo '<hr>';
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					$data["totals"]=($report_row["totals"] != "" ? $report_row["totals"]:0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(ResultAmount) as TotalwinOrLoss,SUM(ValidAmount)  as TotalvalidAmount ,count(*) as totals";
			$sqlStr.=" from `sagame_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `BetTime` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `BetTime` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`Username` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					//$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					$data["totals"]=($row["totals"]!="" ? $row["totals"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/sagame/";
		$this->data["memberURL"]=SYSTEM_URL."/Report/sagame_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/sagame/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/sagame", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	//沙龍會員明細
	public function sagame_details($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `sagame_report` where 1=1";
		$sqlSum="select SUM(BetAmount) as TotalbetAmount,SUM(ResultAmount) as TotalwinOrLoss,SUM(ValidAmount) as TotalvalidAmount,count(*) as totals from `sagame_report` where 1=1";
		
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `BetTime` >=?";
			$sqlSum.=" and `BetTime` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `BetTime` <= ?";
			$sqlSum.=" and `BetTime` <= ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report/sagame_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `BetTime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		//$sqlStr.=" order by `betTime` DESC";
		
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this -> data["result"] = $rowAll;
		$this -> data["rowSum"]=$this->webdb->sqlRow($sqlSum,$parameter);
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		//定義遊戲類型
		$this->data["gameTypeArray"]=array(
						'bac'=>'百家樂','dtx'=>'龍虎',
						'sicbo'=>'骰寶','ftan'=>'番攤',
						'slot'=>'電子遊戲','rot'=>'輪盤',
						'lottery'=>'48彩','minigame'=>'小遊戲');
		
		
		
		$this->data["betTypeArray"]=$this->sagamingapi->getBetType();
		
		
		$this -> data["root"]=$admin_num;	
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/sagame/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
		$this -> data["body"] = $this -> load -> view("admin/report/sagame_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	//super 體育報表
	public function super($root=0){	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount";
					$newSql.=",SUM(result_gold) as TotalwinOrLoss";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `super_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `m_date` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `m_date` <= ?";
						$parameter2[':find8']=@$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount";
			$sqlStr.=",SUM(result_gold) as TotalwinOrLoss from `super_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `m_date` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `m_date` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`m_id` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/super/";
		$this->data["memberURL"]=SYSTEM_URL."/Report/super_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/super/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/super", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	//Super體育會員明細
	public function super_details($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `super_report` where 1=1";
		
		$sqlSum="select SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount";
		$sqlSum.=",SUM(result_gold) as TotalwinOrLoss from `super_report` where 1=1";
		
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `m_date` >=?";
			$sqlSum.=" and `m_date` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `m_date` <= ?";
			$sqlSum.=" and `m_date` <= ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report/super_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `m_date` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		//$sqlStr.=" order by `betTime` DESC";
		
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this -> data["result"] = $rowAll;
		$this -> data["rowSum"]=$this->webdb->sqlRow($sqlSum,$parameter);
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		
		$this->data["gType"]=$this->superapi->gTypeArr();
		$this->data["fashionArr"]=$this->superapi->fashionArr();
		
		$this -> data["root"]=$admin_num;
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/super/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
			
		$this -> data["body"] = $this -> load -> view("admin/report/super_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	//捕魚機報表
	public function fish($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(bet) as TotalbetAmount,SUM(profit) as TotalwinOrLoss";
					$newSql.="";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `fish_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `bettimeStr` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `bettimeStr` <= ?";
						$parameter2[':find8']=$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					//print_r($report_row);
					//echo '<hr>';
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					//$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(bet) as TotalbetAmount,SUM(profit) as TotalwinOrLoss";
			$sqlStr.=" from `fish_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `bettimeStr` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `bettimeStr` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`accountno` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					//$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/fish/";
		//$this->data["memberURL"]=SYSTEM_URL."/Report/sagame_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/fish/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/fish", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}
	
	//Qt電子報表
	public function qt($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(totalBet) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss";
					$newSql.="";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `qtech_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `initiated` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `initiated` <= ?";
						$parameter2[':find8']=$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					//print_r($report_row);
					//echo '<hr>';
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					//$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(totalBet) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss";
			$sqlStr.=" from `qtech_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `initiated` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `initiated` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`playerId` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					//$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/qt/";
		//$this->data["memberURL"]=SYSTEM_URL."/Report/sagame_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/qt/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/qt", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}
	
	//贏家體育
	public function ssb($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount";
					$newSql.=",SUM(meresult) as TotalwinOrLoss";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `ssb_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `added_date` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `added_date` <= ?";
						$parameter2[':find8']=@$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount";
			$sqlStr.=",SUM(meresult) as TotalwinOrLoss from `ssb_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `added_date` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `added_date` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`meusername1` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/ssb/";
		$this->data["memberURL"]=SYSTEM_URL."/Report/ssb_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/ssb/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/ssb", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	//贏家會員明細
	public function ssb_details($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `ssb_report` where 1=1";
		
		$sqlSum="select SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount";
		$sqlSum.=",SUM(meresult) as TotalwinOrLoss from `ssb_report` where 1=1";
		
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `added_date` >=?";
			$sqlSum.=" and `added_date` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `added_date` <= ?";
			$sqlSum.=" and `added_date` <= ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report/ssb_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `added_date` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		//$sqlStr.=" order by `betTime` DESC";
		
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this -> data["result"] = $rowAll;
		$this -> data["rowSum"]=$this->webdb->sqlRow($sqlSum,$parameter);
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		$this -> data["root"]=$admin_num;
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/ssb/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
			
		$this -> data["body"] = $this -> load -> view("admin/report/ssb_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	//7PK
	public function s7pk($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss";
					$newSql.="";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `7pk_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `WagersDate` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `WagersDate` <= ?";
						$parameter2[':find8']=$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss";
			$sqlStr.=" from `7pk_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `WagersDate` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `WagersDate` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`Account` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					//$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/s7pk/";
		//$this->data["memberURL"]=SYSTEM_URL."/Report/sagame_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/s7pk/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/s7pk", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	//賓果報表
	public function bingo($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(money) as TotalbetAmount,SUM(valid) as TotalvalidAmount";
					$newSql.=",SUM(winOrLoss) as TotalwinOrLoss";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `bingo_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `bet_time` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `bet_time` <= ?";
						$parameter2[':find8']=@$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(money) as TotalbetAmount,SUM(valid) as TotalvalidAmount";
			$sqlStr.=",SUM(winOrLoss) as TotalwinOrLoss from `bingo_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `bet_time` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `bet_time` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`client` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/bingo/";
		$this->data["memberURL"]=SYSTEM_URL."/Report/bingo_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/bingo/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/bingo", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}
	
	//賓果明細
	public function bingo_details($admin_num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `bingo_report` where 1=1";
		
		$sqlSum="select SUM(money) as TotalbetAmount,SUM(valid) as TotalvalidAmount";
		$sqlSum.=",SUM(winOrLoss) as TotalwinOrLoss from `bingo_report` where 1=1";
		
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `bet_time` >=?";
			$sqlSum.=" and `bet_time` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `bet_time` <= ?";
			$sqlSum.=" and `bet_time` <= ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report/bingo_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `bet_time` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		//$sqlStr.=" order by `betTime` DESC";
		
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this -> data["result"] = $rowAll;
		$this -> data["rowSum"]=$this->webdb->sqlRow($sqlSum,$parameter);
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
		
		
		$this->data["gameType"]=array(1=>'1~5星',2=>'猜大小',3=>'超級單雙',4=>'超級大小',5=>'五行總合');
		$this->data["fiveArray"]=array(1=>'金',2=>'木',3=>'水',4=>'火',5=>'土');
		
		$this -> data["root"]=$admin_num;
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/bingo/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
			
		$this -> data["body"] = $this -> load -> view("admin/report/bingo_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	//Super六合彩
	public function slottery($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);			
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$newSql="select SUM(cmount) as TotalbetAmount,SUM(bmount) as TotalvalidAmount";
					$newSql.=",SUM(m_result) as TotalwinOrLoss";
					switch ($row["u_power"]){
						case 4:	//股東
							$newSql.=",SUM(u_power4_profit) as TotalProfit";
							$whereql=" and `u_power4`=".$row["num"];
							break;
						case 5:	//總代
							$newSql.=",SUM(u_power5_profit) as TotalProfit";
							$whereql=" and `u_power5`=".$row["num"];
							break;
						case 6:	//代理
							$newSql.=",SUM(u_power6_profit) as TotalProfit";
							$whereql=" and `u_power6`=".$row["num"];
							break;
					}
					$newSql.=" from `slottery_report`  where 1=1".$whereql;
					
					//echo $newSql;
					$parameter2=array();
					
					//===起始日期=======================
					if(@$_REQUEST["find7"]!=""){
						$newSql.=" and `Bet_date` >=?";
						$parameter2[':find7']=@$_REQUEST["find7"];
					}
					//===終止日期=======================
					if(@$_REQUEST["find8"]!=""){
						$newSql.=" and `Bet_date` <= ?";
						$parameter2[':find8']=@$_REQUEST["find8"];
					}
					
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=$row["u_power"];
					$report_row=$this->webdb->sqlRow($newSql,$parameter2);
					$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
					$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
					$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		//代理點進來 改列出會員處理
		
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(cmount) as TotalbetAmount,SUM(bmount) as TotalvalidAmount";
			$sqlStr.=",SUM(m_result) as TotalwinOrLoss from `slottery_report`  where 1=1";
			if( $root>0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `Bet_date` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `Bet_date` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			//===會員帳號 or 遊戲帳號=======================
			if(@$_REQUEST["find9"]!=""){
				$sqlStr.=" and (`account` like ? or mem_num in(select num from member where u_id like ?))";
				$parameter[':find9-1']="%".trim($_REQUEST["find9"])."%";
				$parameter[':find9-2']="%".trim($_REQUEST["find9"])."%";
			}
			
			$sqlStr.=" group by mem_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$root;
					$data["mem_num"]=$row["mem_num"];
					$data["u_id"]=tb_sql('u_id','member',$row["mem_num"]);
					$data["u_power"]=NULL;
					$data["betAmount"]=($row["TotalbetAmount"]!="" ? $row["TotalbetAmount"] : 0);
					$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
					$data["winOrLoss"]=($row["TotalwinOrLoss"]!="" ? $row["TotalwinOrLoss"] : 0);
					array_push($dataList,$data);
				}
			}
		}
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/slottery/";
		//$this->data["memberURL"]=SYSTEM_URL."/Report/bingo_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/slottery/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/slottery", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}
	
	
	//整合報表
	public function report_all($root=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->load->model('admin/Report_model','report');
		
		$parameter=array();
		$sqlStr="select num,u_id,u_power from `admin` where  1=1";
		$Cust_array=array(4,5);	//股東 總代 代理				
		//---身份判定---------------------------
		if(in_array($this->web_root_u_power,$Cust_array)){	//登入身分為股東或者總代 
			$sqlStr.=" and `root`= ?";
			$parameter[':root']= ($root > 0 ? $root : $this->web_root_num);
		}elseif($this->web_root_u_power==6){	//代理登入就抓自己
			$sqlStr.=" and `num`= ?";
			$parameter[':num']=	$this->web_root_num;
		}else{	//否則就是管理者
			if($root==0){
				$sqlStr.=" and `u_power`=4";	//管理者身分直接列出股東
			}else{
				$sqlStr.=" and `root`= ?";
				$parameter[':root']= $root;	
			}
		}
					
		
		$sqlStr.=" order by `u_id`";
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);	
		
		$dataList=array();
		if(@$_REQUEST["find7"]!="" || @$_REQUEST["find8"]!=""){
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$data=array();
					$data["num"]=$row["num"];
					$data["u_id"]=$row["u_id"];	
					$data["u_power"]=($row["u_power"]!=6 ? $row["u_power"] : NULL);
					
					//取得歐博
					$allbet_row=$this->report->allbet($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["Allbet_betAmount"]=($allbet_row["TotalbetAmount"]!="" ? $allbet_row["TotalbetAmount"] : 0);
					$data["Allbet_validAmount"]=($allbet_row["TotalvalidAmount"]!="" ? $allbet_row["TotalvalidAmount"] : 0);
					$data["Allbet_winOrLoss"]=($allbet_row["TotalwinOrLoss"]!="" ? $allbet_row["TotalwinOrLoss"] : 0);
					$data["Allbet_Profit"]=($allbet_row["TotalProfit"]!="" ? $allbet_row["TotalProfit"] : 0);
					
					//沙龍
					$sagame_row=$this->report->sagame($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["Sagame_betAmount"]=($sagame_row["TotalbetAmount"]!="" ? $sagame_row["TotalbetAmount"] : 0);
					$data["Sagame_validAmount"]=($sagame_row["TotalvalidAmount"]!="" ? $sagame_row["TotalvalidAmount"] : 0);
					$data["Sagame_winOrLoss"]=($sagame_row["TotalwinOrLoss"]!="" ? $sagame_row["TotalwinOrLoss"] : 0);
					$data["Sagame_Profit"]=($sagame_row["TotalProfit"]!="" ? $sagame_row["TotalProfit"] : 0);
					
					//Super
					$super_row=$this->report->super($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["Super_betAmount"]=($super_row["TotalbetAmount"]!="" ? $super_row["TotalbetAmount"] : 0);
					$data["Super_validAmount"]=($super_row["TotalvalidAmount"]!="" ? $super_row["TotalvalidAmount"] : 0);
					$data["Super_winOrLoss"]=($super_row["TotalwinOrLoss"]!="" ? $super_row["TotalwinOrLoss"] : 0);
					$data["Super_Profit"]=($super_row["TotalProfit"]!="" ? $super_row["TotalProfit"] : 0);
					
					//捕魚機(遊聯天下)
					$fish_row=$this->report->fish($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["Fish_betAmount"]=($fish_row["TotalbetAmount"]!="" ? $fish_row["TotalbetAmount"] : 0);
					$data["Fish_validAmount"]=0;
					$data["Fish_winOrLoss"]=($fish_row["TotalwinOrLoss"]!="" ? $fish_row["TotalwinOrLoss"] : 0);
					$data["Fish_Profit"]=($fish_row["TotalProfit"]!="" ? $fish_row["TotalProfit"] : 0);
					
					//QT電子
					$qt_row=$this->report->qt($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["QT_betAmount"]=($qt_row["TotalbetAmount"]!="" ? $qt_row["TotalbetAmount"] : 0);
					$data["QT_validAmount"]=0;
					$data["QT_winOrLoss"]=($qt_row["TotalwinOrLoss"]!="" ? $qt_row["TotalwinOrLoss"] : 0);
					$data["QT_Profit"]=($qt_row["TotalProfit"]!="" ? $qt_row["TotalProfit"] : 0);
					
					//DG真人
//					echo '$row[num]:'.$row["num"].'<br>';
//					echo '$row[u_power]:'.$row["u_power"].'<br>';

					$dg_row=$this->report->dg($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["DG_betAmount"]=($dg_row["TotalbetAmount"]!="" ? $dg_row["TotalbetAmount"] : 0);
					$data["DG_validAmount"]=($dg_row["TotalvalidAmount"]!="" ? $dg_row["TotalvalidAmount"] : 0);
					$data["DG_winOrLoss"]=($dg_row["TotalwinOrLoss"]!="" ? $dg_row["TotalwinOrLoss"] : 0);
					$data["DG_Profit"]=($dg_row["TotalProfit"]!="" ? $dg_row["TotalProfit"] : 0);
					
					//WM
					$wm_row=$this->report->wm($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["WM_betAmount"]=($wm_row["TotalbetAmount"]!="" ? $wm_row["TotalbetAmount"] : 0);
					$data["WM_validAmount"]=($wm_row["TotalvalidAmount"]!="" ? $wm_row["TotalvalidAmount"] : 0);
					$data["WM_winOrLoss"]=($wm_row["TotalwinOrLoss"]!="" ? $wm_row["TotalwinOrLoss"] : 0);
					$data["WM_Profit"]=($wm_row["TotalProfit"]!="" ? $wm_row["TotalProfit"] : 0);
					
					//贏家體育
					$ssb_row=$this->report->ssb($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["SSB_betAmount"]=($ssb_row["TotalbetAmount"]!="" ? $ssb_row["TotalbetAmount"] : 0);
					$data["SSB_validAmount"]=($ssb_row["TotalvalidAmount"]!="" ? $ssb_row["TotalvalidAmount"] : 0);
					$data["SSB_winOrLoss"]=($ssb_row["TotalwinOrLoss"]!="" ? $ssb_row["TotalwinOrLoss"] : 0);
					$data["SSB_Profit"]=($ssb_row["TotalProfit"]!="" ? $ssb_row["TotalProfit"] : 0);
					
					//7PK
					$s7pk_row=$this->report->s7pk($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["S7PK_betAmount"]=($s7pk_row["TotalbetAmount"]!="" ? $s7pk_row["TotalbetAmount"] : 0);
					$data["S7PK_validAmount"]=0;
					$data["S7PK_winOrLoss"]=($s7pk_row["TotalwinOrLoss"]!="" ? $s7pk_row["TotalwinOrLoss"] : 0);
					$data["S7PK_Profit"]=($s7pk_row["TotalProfit"]!="" ? $s7pk_row["TotalProfit"] : 0);
					
					//賓果賓果
					$bingo_row=$this->report->bingo($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["Bingo_betAmount"]=($bingo_row["TotalbetAmount"]!="" ? $bingo_row["TotalbetAmount"] : 0);
					$data["Bingo_validAmount"]=($bingo_row["TotalvalidAmount"]!="" ? $bingo_row["TotalvalidAmount"] : 0);
					$data["Bingo_winOrLoss"]=($bingo_row["TotalwinOrLoss"]!="" ? $bingo_row["TotalwinOrLoss"] : 0);
					$data["Bingo_Profit"]=($bingo_row["TotalProfit"]!="" ? $bingo_row["TotalProfit"] : 0);
					
					//紅利計算
					$point_row=$this->report->member_points($row["num"],$row["u_power"],$_REQUEST["find7"],$_REQUEST["find8"]);
					$data["PointsProfit"]=($point_row["PointsProfit"]!="" ? $point_row["PointsProfit"] : 0);	//紅利分潤
					$data["TotalRealPoints"]=($point_row["TotalRealPoints"]!="" ? $point_row["TotalRealPoints"] : 0);
					
					array_push($dataList,$data);
				}
			}
		}
		
		
		$this -> data["root"]=tb_sql("root","admin",$root);	
		$this->data["baseURL"]=SYSTEM_URL."/Report/report_all/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report/report_all/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/report_all", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}
	
	
	
	
} 