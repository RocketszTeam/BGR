<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Rtg_report extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this->load->library('api/rtgapi');	//賓果
		//取得當前資料庫最新的WagerID
		//$vgreport_lastid_strSql = "select WagerID from vg_report ORDER BY WagerID desc limit 0,1";
		//$this->data['vgreport_lastid'] = $this->webdb->sqlRow($vgreport_lastid_strSql)['WagerID'];
		
		
		
	}
	
	
	//RTG用這方式捕帳
    public function index(){
		//$this->get_report($sTime="2019-02-15 00:00:00",$eTime="2019-02-15 23:59:59");
		for($i=1;$i<26;$i++){
			$this->get_report($sTime="2019-02-".str_pad($i,2,"0",STR_PAD_LEFT)." 00:00:00",$eTime="2019-02-".str_pad($i,2,"0",STR_PAD_LEFT)." 23:59:59");
			sleep(1);
		}
        //$result=$this->rtgapi->reporter_all($sTime,$eTime);
        //echo "<pre>";
        //var_dump($result);
    }
	
	//新的方式
	public function get_report($sTime=null,$eTime=null){
		if($sTime==NULL && $eTime==NULL){
			if($sTime==NULL && $eTime==NULL){
				$eTime=date('Y-m-d H:i:s');
				$sTime=date('Y-m-d H:i:s',strtotime($eTime."-50 min"));
			}
		}
		
		//var_dump($sTime,$eTime);exit
		echo "<pre>";
		$uid="";
		//$uid2="";
		var_dump($sTime."|".$eTime);
		$result=$this->rtgapi->reporter_all($sTime,$eTime);
		var_dump($result->items);
		
		if(isset($result)){
			foreach($result->items as $row){
					
				if(!strpos(" ".$uid,"'".$row->playerName."'")){
					$uid .= "'".$row->playerName."',";
				}
				
				
			}
			$uid2 = substr($uid,0,strlen($uid)-1);	
			var_dump($uid2);
			if($uid2 ==false){$uid2 = "'"."'";}
			var_dump($uid2);
			
			$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid2.") and gamemaker_num=51";	
			$rowAll=$this->webdb->sqlRowList($sqlStr);
			
			//var_dump($rowAll);
			$adminsql = "SELECT num,root,percent from admin";
			$AdminList = $this->webdb->sqlRowList($adminsql);
		
			foreach($result->items as $row){
				
				$u_power4 = 0;
				$u_power5 = 0;
				$u_power6 = 0;
				$u_power4_profit=0;
				$u_power5_profit=0;
				$u_power6_profit=0;	
				$mem_num=0;
				
				$winOrLose=0;	//預設輸贏金額
				$validAmount=0;	//預設有效投注量
				//if($row->gameresult==0 || $row->gameresult==1 ){	//當會員贏錢 或者 輸錢 才算出實際金額
					
					//if($row->gameresult ==0){ //若輸的話 輸贏值就是$row->money值
						//$winOrLoss = $row->money;
					//}elseif($row->gameresult ==1){
						$winOrLose = (float)$row->win-(float)$row->bet;
					//}
					
				//}
				//var_dump($win);
				//if($row->Result!='C'){
					//$validbetamount=$row->validbetamount;	//除了跟註銷單沒有洗碼之外都有
				//}
			
				//var_dump($rowAll);
				//var_dump($row->username);
				//取出會員代理總代代理編號	
				for($i=0; $i<count($rowAll); $i++){	
					echo "<pre>";
					//var_dump($rowAll[$i]["u_id"] ."==". $row->playerName."==".$rowAll[$i]["mem_num"]);
					if(strtoupper($rowAll[$i]["u_id"]) == strtoupper($row->playerName)){
						$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
						$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
						//echo "<pre>";
					
						//var_dump("0000".$mem_num,$u_power6);
						break;
					}
				}	
				//var_dump($mem_num,$u_power6);
				//var_dump($win);
				
				//代理分潤、總代編號
				for($i=0; $i<count($AdminList); $i++){
					if($AdminList[$i]["num"] == $u_power6){
						$u_power6_profit = round( (float)$winOrLose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//代理分潤
						$u_power5 = $AdminList[$i]["root"];	
						break;
					}
				}
				
				
				
				//總代分潤、股東編號
				for($i=0; $i<count($AdminList); $i++){
					if($AdminList[$i]["num"] == $u_power5){
						$u_power5_profit = round( (float)$winOrLose * ((float)$AdminList[$i]["percent"] / 100) ,2);	//總代分潤
						$u_power4 = $AdminList[$i]["root"];	
						break;
					}
				}
				
				//股東分潤
				for($i=0; $i<count($AdminList); $i++){
					if($AdminList[$i]["num"] == $u_power4){
						$u_power4_profit = round( (float)$winOrLose* ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
						break;
					}
				}
				//var_dump($mem_num,"u_power6:".$u_power6,"u_power6_profit:".$u_power6_profit,"u_power5:".$u_power5,"u_power5_profit:".$u_power5_profit,"u_power4:".$u_power4,"u_power4_profit:".$u_power4_profit);
				
			
				
				$parameter=array();
				$colSql="id,gameStartDate,gameDate,gameNumber,playerName,jpBet,jpWin,balanceStart,balanceEnd,bet,gameId,gameName,win,winOrLose,sideBet";
				$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
				$sqlStr="REPLACE INTO `rtg_report` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':id']=$row->id;
				$parameter[':gameStartDate']=date("Y-m-d H:i:s",strtotime($row->gameStartDate." +0 hour"));// 游戏完成日期UTC + 0
				$parameter[':gameDate']=date("Y-m-d H:i:s",strtotime($row->gameDate." +0 hour"));//游戏开始日期UTC + 0
				$parameter[':gameNumber']=$row->gameNumber;// 游戏回合编号
				$parameter[':playerName']=$row->playerName;// 玩家用户名
				$parameter[':jpBet']=$row->jpBet;//大奖彩池累积奖金
				$parameter[':jpWin']=$row->jpWin;// 大奖彩池累积盈利
				$parameter[':balanceStart']=$row->balanceStart;//游戏开始时玩家余额
				$parameter[':balanceEnd']=$row->balanceEnd;//游戏结束时玩家余额
				$parameter[':bet']=(float)$row->bet;//下注额
				$parameter[':gameId']=$row->gameId;//游戏的系统ID
				$parameter[':gameName']=$row->gameName;//已玩游戏的名称
				$parameter[':win']=(float)$row->win;// 赢额。包含任何盈利 (例如，大奖彩池与一般盈利)
				$parameter[':winOrLose'] = $winOrLose;
				$parameter[':sideBet']=$row->sideBet;//旁观下注
				//$parameter[':id']=$row->id;//游戏ID (gamedetail API中使用的BetId)
				//$parameter[':jpType']=$row->jackpotDetails['jpType']//大奖彩池类型 (例如一般(Random)，特殊(Major)，超级 (Minor))
				//$parameter[':ddcGameId']=$row->ddcGameId;//游戏ID
				//$parameter[':jpBet_g']=$row->jackpotDetails['jpBet_g'];//特定大奖彩池累积奖金
				
				
				$parameter[':mem_num']=$mem_num;
				$parameter[':u_power4']=$u_power4;
				$parameter[':u_power5']=$u_power5;
				$parameter[':u_power6']=$u_power6;
				$parameter[':u_power4_profit']=$u_power4_profit;
				$parameter[':u_power5_profit']=$u_power5_profit;
				$parameter[':u_power6_profit']=$u_power6_profit;					
				$this->webdb->sqlExc($sqlStr,$parameter);
				
				
			}
		
		}
		
		

	}
	
	//舊的方式
	public function get_report_2($sTime=NULL,$eTime=NULL,$Page=1){
		if($sTime==NULL && $eTime==NULL){
			$eTime=date('Y-m-d H:i:s');
			$sTime=date('Y-m-d H:i:s',strtotime($eTime."-12 hour"));
		}
		$result=$this->s9k168->reporter_all($sTime,$eTime,$Page);
		//print_r($result);
		if(isset($result)){	//執行成功且有帳回來
			if(count($result->BetList) > 0){	//有帳
				foreach($result->BetList as $row){
					//取出會員代理總代代理編號	
					$sqlStr="select mem_num from `games_account` where u_id='".$row->MemberAccount."' and gamemaker_num=26";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					$winOrLoss=0;	//預設輸贏金額
					$validAmount=0;	//預設有效投注量
					if($row->Result=='W' || $row->Result=='L'){	//當會員贏錢 或者 輸錢 才算出實際金額
						$winOrLoss=(float)$row->PayOff - (float)$row->TotalAmount;
					}
					if($row->Result!='C'){
						$validAmount=$row->BetAmount;	//除了跟註銷單沒有洗碼之外都有
					}
					
					
					$u_power4_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
					$parameter=array();
					$colSql="WagerID,WagerDate,GameDate,BossID,MemberAccount,TypeCode,BetAmount,validAmount,winOrLoss,PayOff,Result";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `9k168_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':WagerID']=$row->WagerID;
					$parameter[':WagerDate']=$row->WagerDate;
					$parameter[':GameDate']=$row->GameDate;
					$parameter[':BossID']=$row->BossID;
					$parameter[':MemberAccount']=$row->MemberAccount;
					$parameter[':MemberAccount']=$row->MemberAccount;
					$parameter[':TypeCode']=$row->TypeCode;
					//$parameter[':BetAmount']=$row->BetAmount;
					$parameter[':BetAmount']=$row->TotalAmount;
					$parameter[':validAmount']=$validAmount;
					$parameter[':winOrLoss']=$winOrLoss;
					$parameter[':PayOff']=$row->PayOff;
					$parameter[':Result']=$row->Result;
					
					$parameter[':mem_num']=$mem_num;
					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
				}
				
				if($Page < $result->PageInfo->TotalPage){
					$this->get_report($sTime,$eTime,($Page + 1));
				}
			}
		}
	}
	
	

	//手動補帳
	public function auto_report(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$report_count=$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true));
				echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！'));
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'網域不被允許'));
		}
	}
	
	
	
	
}

?>