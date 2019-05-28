<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Report2 extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		$this->load->library('api/allbetapi');	//歐博API
		$this->load->library('api/sagamingapi');	//沙龍
		$this->load->library('api/superapi');	//Super
					
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
	
	
	
	//沙龍報表
	public function sagame($root=0){	
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
			$sqlStr="select SUM(BetAmount) as TotalbetAmount,SUM(ValidAmount) as TotalvalidAmount,SUM(ResultAmount) as TotalwinOrLoss,Count(*) as totals";
			if($root==0){	//列出所有股東
				$sqlStr.=",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
			}elseif(tb_sql('u_power','admin',$root)==4){	//股東身分列出總代
				$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
				$whereSql.=" and `u_power4`=?";	
				$parameter[':u_power4']=$root;
			}else{	//總代身分列出代理
				$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
				$whereSql.=" and `u_power5`=?";	
				$parameter[':u_power5']=$root;
			}
			$sqlStr.=" from `sagame_report`  where 1=1".$whereSql;
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `PayoutTime` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `PayoutTime` <= ?";
				$parameter[':find8']=$_REQUEST["find8"];
			}
			$sqlStr.=" group by agent_num";
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
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
					array_push($dataList,$data);
				}
			}
			
		}
		//代理點進來 改列出會員處理
		if(($root > 0 && (tb_sql("u_power","admin",$root)==6)) || @$_REQUEST["find9"]!=''){
			$dataList=array();
			$parameter=array();
			$sqlStr="select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(ValidAmount) as TotalvalidAmount,SUM(ResultAmount) as TotalwinOrLoss ,count(*) as totals";
			$sqlStr.=" from `sagame_report`  where 1=1";
			if( $root > 0 ){
				$sqlStr.=" and `u_power6`=?";
				$parameter[':u_power6']=$root;
			}
			//===起始日期=======================
			if(@$_REQUEST["find7"]!=""){
				$sqlStr.=" and `PayoutTime` >=?";
				$parameter[':find7']=@$_REQUEST["find7"];
			}
			//===終止日期=======================
			if(@$_REQUEST["find8"]!=""){
				$sqlStr.=" and `PayoutTime` <= ?";
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
		$this->data["baseURL"]=SYSTEM_URL."/Report2/sagame/";
		$this->data["memberURL"]=SYSTEM_URL."/Report2/sagame_details/";
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report2/sagame/".$this -> data["root"].($this->data["att"]!="" ? "?p=1".$this->data["att"] : ""));
		$this -> data["result"] = $dataList;
		$this -> data["body"] = $this -> load -> view("admin/report/sagame2", $this -> data,true);   
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
		$sqlStr="select * from `sagame_report`  where `u_power6`=?";
		$sqlSum="select SUM(BetAmount) as TotalbetAmount,SUM(ResultAmount) as TotalwinOrLoss,SUM(ValidAmount) as TotalvalidAmount from `sagame_report` where `u_power6`=?";
		$parameter[':u_power6']=$admin_num;
		if(@$_REQUEST["find1"]!=""){	//抓出會員
			$sqlStr.=" and `mem_num`=?";
			$sqlSum.=" and `mem_num`=?";
			$parameter[':find1']=@$_REQUEST["find1"];
		}

		//===起始日期=======================
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `PayoutTime` >=?";
			$sqlSum.=" and `PayoutTime` >=?";
			$parameter[':find7']=@$_REQUEST["find7"];
		}
		//===終止日期=======================
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `PayoutTime` < ?";
			$sqlSum.=" and `PayoutTime` < ?";
			$parameter[':find8']=$_REQUEST["find8"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = site_url(SYSTEM_URL."/Report2/sagame_details/".$admin_num."?p=1".$this->data["att"]);//site_url("admin/news/index");
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
		$this->data["backBTN"]=site_url(SYSTEM_URL."/Report2/sagame/".$this -> data["root"].($this->data["att"]!="" ? urlQuery('find1') : ""));
		$this -> data["body"] = $this -> load -> view("admin/report/sagame_details", $this -> data,true);
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	
} 