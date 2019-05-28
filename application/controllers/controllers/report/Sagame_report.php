<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Sagame_report extends Core_controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	//使用這方式補帳
	public function index(){
		//$Date='2018-05-09';
		//此服务用于获取当前大厅当指定日子(中午12:00:00至上午11:59:59)的下注信息, 没有指定日期则 使用服务器当天日期.每5分钟可以调用5次,否则报错..
		//$eTime=date('Y-m-d 11:59:59');
		//$sTime=date('Y-m-d 12:00:00',strtotime($eTime."-1 day"));
		//echo $sTime."|".$eTime;
		$result=$this->sagamingapi->reporter_all("2018-12-09 12:00:00","2018-12-11 11:59:59");
		echo "<pre>";
		var_dump($result);
		//exit;
		$uid = "";
		$ResultAmount = 0;
		$BetID ="";
		if(count($result)){	//執行成功且有帳回來，先取出會員編號配u_id
			foreach($result->BetDetail as $row){
				$BetID .= $row->BetID."|";
				$ResultAmount += $row->ResultAmount;
				
				if( !strpos(" ".$uid, "'".$row->Username."'") ){
					$uid .= "'".$row->Username."',";			
				}
			}
		}
		$uid = substr($uid,0,strlen($uid)-1);		
		print_r($uid);	
		var_dump($ResultAmount);echo "<br>";
		//var_dump($BetID);
		//exit;
		if(count($result)){	//執行成功且有帳回來
		
			$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=9";	
			$rowAll=$this->webdb->sqlRowList($sqlStr);	
			$adminsql = "SELECT num,root,percent from admin";
			$AdminList = $this->webdb->sqlRowList($adminsql);			
		
			foreach($result->BetDetail as $row){
				
				//寫入DB
				//if($this->check_betnum($row->BetID)){	//資料庫內沒有紀錄才新增.
					/*
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->Username."' and gamemaker_num=9";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$u_power4_profit=round((float)$row->ResultAmount * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$row->ResultAmount * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$row->ResultAmount * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					*/
					//echo 'ResultAmount：'.$row->ResultAmount.'，u_power4_profit：'.$u_power4_profit.'<br>';
					//echo '被樹：'.$this->get_percent($u_power4,$u_power5).'<br>';
					
					//有效投注額, 基本上可以從返回的 ResultAmount 判斷. 如果 ResultAmount = 0, 即和或退回投注, 那有效投注就是0, 否則, 有效投注就是 BetAmount 了
					$ValidAmount=($row->ResultAmount==0 ? $row->ResultAmount : $row->BetAmount);	
						
					$u_power4 = 0;
					$u_power5 = 0;
					$u_power6 = 0;
					$u_power4_profit=0;
					$u_power5_profit=0;
					$u_power6_profit=0;	
					$mem_num=0;				
					
					//取出會員代理總代代理編號	
					for($i=0; $i<count($rowAll); $i++){	
						//$this->data[$k]=$v;
						if($rowAll[$i]["u_id"] == $row->Username){
							$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
							$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
							break;
						}
					}	
					
					//代理分潤、總代編號
					for($i=0; $i<count($AdminList); $i++){
						if($AdminList[$i]["num"] == $u_power6){
							$u_power6_profit = round( (float)$row->ResultAmount * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
							$u_power5 = $AdminList[$i]["root"];	
							break;
						}
					}
					
					//總代分潤、股東編號
					for($i=0; $i<count($AdminList); $i++){
						if($AdminList[$i]["num"] == $u_power5){
							$u_power5_profit = round( (float)$row->ResultAmount * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
							$u_power4 = $AdminList[$i]["root"];	
							break;
						}
					}
					
					//股東分潤
					for($i=0; $i<count($AdminList); $i++){
						if($AdminList[$i]["num"] == $u_power4){
							$u_power4_profit = round( (float)$row->ResultAmount * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
							break;
						}
					}					
					//var_dump($row->BetID);
					
					$parameter=array();
					$colSql="BetID,Username,BetTime,PayoutTime,HostID,Detail,GameID,Round,Set,BetAmount,ValidAmount";
					$colSql.=",ResultAmount,Balance,GameType,BetType,BetSource,State";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `sagame_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':BetID']=$row->BetID;
					$parameter[':Username']=$row->Username;
					$parameter[':BetTime']=date('Y-m-d H:i:s',strtotime($row->BetTime));
					$parameter[':PayoutTime']=date('Y-m-d H:i:s',strtotime($row->PayoutTime));
					$parameter[':HostID']=$row->HostID;
					$parameter[':Detail']=$row->Detail;
					$parameter[':GameID']=$row->GameID;
					$parameter[':Round']=$row->Round;
					$parameter[':Set']=$row->Set;
					$parameter[':BetAmount']=$row->BetAmount;
					$parameter[':ValidAmount']=$ValidAmount;
					$parameter[':ResultAmount']=$row->ResultAmount;
					$parameter[':Balance']=$row->Balance;
					$parameter[':GameType']=$row->GameType;
					$parameter[':BetType']=$row->BetType;
					$parameter[':BetSource']=$row->BetSource;
					$parameter[':State']=$row->State;
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
					
				//}
			}	
		}
	}

	
	
	public function get_report(){
		$eTime=date('Y-m-d H:i:s');
		$sTime=date('Y-m-d H:i:s',strtotime($eTime."-1 hour"));
		$result=$this->sagamingapi->reporter_all($sTime,$eTime);
		print_r($result);
		if(count($result)){	//執行成功且有帳回來
			foreach($result->BetDetail as $row){
				//寫入DB
				//if($this->check_betnum($row->BetID)){	//資料庫內沒有紀錄才新增
					
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->Username."' and gamemaker_num=9";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$u_power4_profit=round((float)$row->ResultAmount * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$row->ResultAmount * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$row->ResultAmount * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					
					//echo 'ResultAmount：'.$row->ResultAmount.'，u_power4_profit：'.$u_power4_profit.'<br>';
					//echo '被樹：'.$this->get_percent($u_power4,$u_power5).'<br>';
					
					//有效投注額, 基本上可以從返回的 ResultAmount 判斷. 如果 ResultAmount = 0, 即和或退回投注, 那有效投注就是0, 否則, 有效投注就是 BetAmount 了
					$ValidAmount=($row->ResultAmount==0 ? $row->ResultAmount : $row->BetAmount);	
					
					$parameter=array();
					$colSql="BetID,Username,BetTime,PayoutTime,HostID,Detail,GameID,Round,Set,BetAmount,ValidAmount";
					$colSql.=",ResultAmount,Balance,GameType,BetType,BetSource,State";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `sagame_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':BetID']=$row->BetID;
					$parameter[':Username']=$row->Username;
					$parameter[':BetTime']=date('Y-m-d H:i:s',strtotime($row->BetTime));
					$parameter[':PayoutTime']=date('Y-m-d H:i:s',strtotime($row->PayoutTime));
					$parameter[':HostID']=$row->HostID;
					$parameter[':Detail']=$row->Detail;
					$parameter[':GameID']=$row->GameID;
					$parameter[':Round']=$row->Round;
					$parameter[':Set']=$row->Set;
					$parameter[':BetAmount']=$row->BetAmount;
					$parameter[':ValidAmount']=$ValidAmount;
					$parameter[':ResultAmount']=$row->ResultAmount;
					$parameter[':Balance']=$row->Balance;
					$parameter[':GameType']=$row->GameType;
					$parameter[':BetType']=$row->BetType;
					$parameter[':BetSource']=$row->BetSource;
					$parameter[':State']=$row->State;
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				//}
			}	
		}
	}

	
	//取得分潤成數~~up_num 傳入上層,down_num傳入下成
	public function get_percent($up_num,$down_num){
		$up_percent=tb_sql('percent','admin',$up_num);
		$down_percent=tb_sql('percent','admin',$down_num);
		$profit=$up_percent-$down_percent;
		if($profit <=0){
			return 0;
		}else{
			return ($profit / 100);
		}
	}
	
	private function check_betnum($BetID){	//檢查此筆資料是否已經存在
		$sqlStr="select `BetID` from `sagame_report` where `BetID`=?";	
		$row=$this->webdb->sqlRow($sqlStr,array(':BetID'=>$BetID));
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}
}

?>