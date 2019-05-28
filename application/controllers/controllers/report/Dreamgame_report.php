<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
//error_reporting(0);
class Dreamgame_report extends Core_controller{
	public function __construct(){ 
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this -> load -> model("admin/Daily_model", "daily", true);
	}
	

	//補撈報表(這只是用來漏單補單，注意:日期年月日要一樣)
	public function makeup_reporter_all($beginTime='2018-12-26 00:00:00',$endTime='2018-12-26 23:59:59'){
		
		$result=$this->dreamgame->makeup_reporter_all($beginTime,$endTime);
		echo "<pre>";
		var_dump($result);
		if(isset($result) && count($result) > 0){	//有帳.
			$idlist = array();
			$orderdateArray=array();//紀錄帳務日用的
			foreach($result as $row){
				
				array_push($idlist, $row->id);
				
				
				if($row->gameType!='2'){	//排除紅包小費
					array_push($orderdateArray, date('Y-m-d H:i:s',strtotime($row->betTime)));	//紀錄帳務日
					
					
					$sqlStr="select mem_num from `games_account` where u_id='".$row->userName."' and gamemaker_num=12";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					
					
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					//取其代理、總代、股東編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
				
					$u_power4_percent = tb_sql('percent','admin',$u_power4);
					$u_power5_percent = tb_sql('percent','admin',$u_power5);
					$u_power6_percent = tb_sql('percent','admin',$u_power6);
					//計算輸贏結果
					$winOrLoss=isset($row->winOrLoss) ? $row->winOrLoss : 0;
					$totalWinlose=(float)$winOrLoss - (float)$row->betPoints;
				
					
					//計算分潤結果
					$u_power4_profit=round((float)$totalWinlose * ($u_power4_percent / 100),2);	//股東分潤
					$u_power5_profit=round((float)$totalWinlose * ($u_power5_percent / 100),2);	//股東分潤
					$u_power6_profit=round((float)$totalWinlose * ($u_power6_percent / 100),2);	//代理分潤
				
					
					$parameter=array();
					$colSql="id,tableId,shoeId,playId,lobbyId,gameType,gameId,memberId,parentId,betTime";
					$colSql.=",calTime,winOrLoss,totalWinlose,balanceBefore,betPoints,betPointsz,availableBet";
					$colSql.=",userName,result,betDetail,ip,ext,isRevocation,currencyId,deviceType";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit,profit";
					$sqlStr="REPLACE INTO `dreamgame_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':id']=$row->id;//$row->id;
					$parameter[':tableId']=(isset($row->tableId) ? $row->tableId : NULL);
					$parameter[':shoeId']=(isset($row->shoeId) ? $row->shoeId : NULL);
					$parameter[':playId']=(isset($row->playId) ? $row->playId : NULL);
					$parameter[':lobbyId']=(isset($row->lobbyId) ? $row->lobbyId : NULL);
					$parameter[':gameType']=$row->gameType;
					$parameter[':gameId']=$row->gameId;
					$parameter[':memberId']=$row->memberId;
					$parameter[':parentId']=(isset($row->parentId) ? $row->parentId : NULL);

					$parameter[':betTime']=date('Y-m-d H:i:s',strtotime($row->betTime));
					$parameter[':calTime']=date('Y-m-d H:i:s',strtotime($row->calTime));
					$parameter[':winOrLoss']=$winOrLoss;
					$parameter[':totalWinlose']=$totalWinlose;
					$parameter[':balanceBefore']=(isset($row->balanceBefore) ? $row->balanceBefore : NULL);
					$parameter[':betPoints']=$row->betPoints;
					$parameter[':betPointsz']=$row->betPointsz;
					$parameter[':availableBet']=$row->availableBet;
					$parameter[':userName']=$row->userName;
					$parameter[':result']=(string)$row->result;
					$parameter[':betDetail']=$row->betDetail;
					$parameter[':ip']=$row->ip;
					$parameter[':ext']=$row->ext;
					$parameter[':isRevocation']=$row->isRevocation;
					$parameter[':currencyId']=$row->currencyId;
					$parameter[':deviceType']=$row->deviceType;
					
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;
					$parameter[':profit']=$u_power4_percent.'=='.$u_power5_percent.'=='.$u_power6_percent;					
					$this->webdb->sqlExc($sqlStr,$parameter);
					
					
				}
				
				
				
			}
		}
		
	}
	
	
	
	public function get_report(){
		$result=$this->dreamgame->reporter_all();
		if(isset($result) && count($result) > 0){	//有帳
			$idlist = array();
			$orderdateArray=array();//紀錄帳務日用的
			foreach($result as $row){
				array_push($idlist, $row->id);
				if($row->gameType!='2'){	//排除紅包小費
					array_push($orderdateArray, date('Y-m-d H:i:s',strtotime($row->betTime)));	//紀錄帳務日
					//取出會員編號
					$sqlStr="select mem_num from `games_account` where u_id='".$row->userName."' and gamemaker_num=12";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					//取其代理、總代、股東編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
	
					$u_power4_percent = tb_sql('percent','admin',$u_power4);
					$u_power5_percent = tb_sql('percent','admin',$u_power5);
					$u_power6_percent = tb_sql('percent','admin',$u_power6);
					//計算輸贏結果
					$winOrLoss=isset($row->winOrLoss) ? $row->winOrLoss : 0;
					$totalWinlose=(float)$winOrLoss - (float)$row->betPoints;
					//計算分潤結果
					$u_power4_profit=round((float)$totalWinlose * ($u_power4_percent / 100),2);	//股東分潤
					$u_power5_profit=round((float)$totalWinlose * ($u_power5_percent / 100),2);	//股東分潤
					$u_power6_profit=round((float)$totalWinlose * ($u_power6_percent / 100),2);	//代理分潤
					
					$parameter=array();
					$colSql="id,tableId,shoeId,playId,lobbyId,gameType,gameId,memberId,parentId,betTime";
					$colSql.=",calTime,winOrLoss,totalWinlose,balanceBefore,betPoints,betPointsz,availableBet";
					$colSql.=",userName,result,betDetail,ip,ext,isRevocation,currencyId,deviceType";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit,profit";
					$sqlStr="REPLACE INTO `dreamgame_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':id']=$row->id;
					$parameter[':tableId']=(isset($row->tableId) ? $row->tableId : NULL);
					$parameter[':shoeId']=(isset($row->shoeId) ? $row->shoeId : NULL);
					$parameter[':playId']=(isset($row->playId) ? $row->playId : NULL);
					$parameter[':lobbyId']=(isset($row->lobbyId) ? $row->lobbyId : NULL);
					$parameter[':gameType']=$row->gameType;
					$parameter[':gameId']=$row->gameId;
					$parameter[':memberId']=$row->memberId;
					$parameter[':parentId']=(isset($row->parentId) ? $row->parentId : NULL);

					$parameter[':betTime']=date('Y-m-d H:i:s',strtotime($row->betTime));
					$parameter[':calTime']=date('Y-m-d H:i:s',strtotime($row->calTime));
					$parameter[':winOrLoss']=$winOrLoss;
					$parameter[':totalWinlose']=$totalWinlose;
					$parameter[':balanceBefore']=$row->balanceBefore;
					$parameter[':betPoints']=$row->betPoints;
					$parameter[':betPointsz']=$row->betPointsz;
					$parameter[':availableBet']=$row->availableBet;
					$parameter[':userName']=$row->userName;
					$parameter[':result']=(string)$row->result;
					$parameter[':betDetail']=$row->betDetail;
					$parameter[':ip']=$row->ip;
					$parameter[':ext']=$row->ext;
					$parameter[':isRevocation']=$row->isRevocation;
					$parameter[':currencyId']=$row->currencyId;
					$parameter[':deviceType']=$row->deviceType;
					
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;
					$parameter[':profit']=$u_power4_percent.'=='.$u_power5_percent.'=='.$u_power6_percent;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
				//標記注單
				$this->dreamgame->markReport($idlist);
				//將帳務日由小到大排序
				sort($orderdateArray);
				$sTime=$orderdateArray[0];	//帳務起始日期
				$eTime=$orderdateArray[count($orderdateArray)-1];	//帳務終止日期
				//$this->daily->dg($sTime,$eTime);  //贏家歸帳	
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
		$sqlStr="select `ID` from `dreamgame_report` where `ID`=?";	
		$row=$this->webdb->sqlRow($sqlStr,array(':ID'=>$BetID));
		if($row==NULL){
			return true;	
		}else{
			return false;
		}
	}
}

?>