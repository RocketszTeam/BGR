<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Vg_report extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this->load->library('api/vgapi');	//賓果
		//取得當前資料庫最新的WagerID
		//$vgreport_lastid_strSql = "select WagerID from vg_report ORDER BY WagerID desc limit 0,1";
		//$this->data['vgreport_lastid'] = $this->webdb->sqlRow($vgreport_lastid_strSql)['WagerID'];
		
		
		
	}
	
	public function index($id){
		$this->get_report($id);  //1526037217   2018-08-30 14:11:02
		//echo $this->data['vgreport_lastid'];.
		
	}
	
	
	
	//新的方式
	public function get_report($accounting=null){
		if($accounting == null){
			//取得當前資料庫最新的WagerID
			$vgreport_lastid_strSql = "select WagerID from vg_report ORDER BY WagerID desc limit 0,1";
			if($this->webdb->sqlRow($vgreport_lastid_strSql) == null){
				$last_count_array_id = 0;
			}else{
				$last_count_array_id = $this->webdb->sqlRow($vgreport_lastid_strSql)['WagerID'];
			}
		}else{
			$last_count_array_id = $accounting;
		}
		
		$count_array=100;
		$vd="";
		$uid="";
		
		while($count_array ==100){	
			$tty = $this->vgapi->reporter_all($last_count_array_id);
			
			foreach($tty->value as $row){
				
				if(!strpos(" ".$uid,"'".$row->username."'")){
					$uid .= "'".$row->username."',";
				}
				
				
			}
			$uid2 = substr($uid,0,strlen($uid)-1);	
			
			$count_array = count($tty->value); //取得共有幾條
			$last_count_array = $count_array-1;
			$last_count_array_id = $tty->value[$last_count_array]->id; //取道最後一條id
			
			
			if($count_array && strlen($uid2) > 1){	
				$sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid2.") and gamemaker_num=38";	
				$rowAll=$this->webdb->sqlRowList($sqlStr);	
				$adminsql = "SELECT num,root,percent from admin";
				$AdminList = $this->webdb->sqlRowList($adminsql);
				
			
				//if($rowAll!=NULL){    //執行成功且有資料回來.
					
					foreach($tty->value as $row){
						
						$u_power4 = 0;
						$u_power5 = 0;
						$u_power6 = 0;
						$u_power4_profit=0;
						$u_power5_profit=0;
						$u_power6_profit=0;	
						$mem_num=0;
						
						$winOrLoss=0;	//預設輸贏金額
						$validAmount=0;	//預設有效投注量
						//if($row->gameresult==0 || $row->gameresult==1 ){	//當會員贏錢 或者 輸錢 才算出實際金額
							
							//if($row->gameresult ==0){ //若輸的話 輸贏值就是$row->money值
								//$winOrLoss = $row->money;
							//}elseif($row->gameresult ==1){
								$winOrLoss = $row->money + $row->servicemoney; //若贏的話 要減掉抽水 才是輸贏值($row->servicemoney都是負值 所以用加的)
							//}
							
						//}
						
						//if($row->Result!='C'){
							//$validbetamount=$row->validbetamount;	//除了跟註銷單沒有洗碼之外都有
						//}
					
						//var_dump($rowAll);
						//var_dump($row->username);
						//取出會員代理總代代理編號	
						for($i=0; $i<count($rowAll); $i++){	
							echo "<pre>";
							var_dump($rowAll[$i]["u_id"] ."==". $row->username."==".$rowAll[$i]["mem_num"]);
							if(strtoupper($rowAll[$i]["u_id"]) == strtoupper($row->username)){
								$mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
								$u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
								//echo "<pre>";
							
								//var_dump("0000".$mem_num,$u_power6);
								break;
							}
						}	
						//var_dump($mem_num,$u_power6);
						
						
						//代理分潤、總代編號
						for($i=0; $i<count($AdminList); $i++){
							if($AdminList[$i]["num"] == $u_power6){
								$u_power6_profit = round( (float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//代理分潤
								$u_power5 = $AdminList[$i]["root"];	
								break;
							}
						}
						
						
						
						//總代分潤、股東編號
						for($i=0; $i<count($AdminList); $i++){
							if($AdminList[$i]["num"] == $u_power5){
								$u_power5_profit = round( (float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100) ,2);	//總代分潤
								$u_power4 = $AdminList[$i]["root"];	
								break;
							}
						}
						
						//股東分潤
						for($i=0; $i<count($AdminList); $i++){
							if($AdminList[$i]["num"] == $u_power4){
								$u_power4_profit = round( (float)$winOrLoss* ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
								break;
							}
						}
						
						$parameter=array();
						$colSql="WagerID,createtime,begintime,channel,username,roomid,tableid,roundid,gametype,betamount,gameinfo,validbetamount,winOrLoss,isbanker,gameresult,beforebalance,money,servicemoney";
						$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
						$sqlStr="REPLACE INTO `vg_report` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[':WagerID']=$row->id;
						$parameter[':createtime']=$row->createtime;//資料建立時間==遊戲結束時間
						$parameter[':begintime']=$row->begintime;//遊戲開始時間
						$parameter[':channel']=$row->channel;
						$parameter[':username']=$row->username;
						$parameter[':roomid']=$row->roomid;
						$parameter[':tableid']=$row->tableid;
						$parameter[':roundid']=$row->roundid;
						$parameter[':gametype']=$row->gametype;
						$parameter[':betamount']=$row->betamount;
						$parameter[':gameinfo']=$row->gameinfo;
						$parameter[':validbetamount']=$row->validbetamount;
						$parameter[':winOrLoss']=$winOrLoss;
						$parameter[':isbanker']=$row->isbanker;//庄闲 1表示庄 0表示闲
						$parameter[':gameresult']=$row->gameresult;
						$parameter[':beforebalance']=$row->beforebalance * 100 ;//開局時用戶餘額 實際金額*100=遊戲幣
						$parameter[':money']=$row->money;
						$parameter[':servicemoney']=$row->servicemoney;
						
						
						$parameter[':mem_num']=$mem_num;
						$parameter[':u_power4']=$u_power4;
						$parameter[':u_power5']=$u_power5;
						$parameter[':u_power6']=$u_power6;
						$parameter[':u_power4_profit']=$u_power4_profit;
						$parameter[':u_power5_profit']=$u_power5_profit;
						$parameter[':u_power6_profit']=$u_power6_profit;					
						$this->webdb->sqlExc($sqlStr,$parameter);
						
					}
					
					
					
				
					
				//}	
			
			}
			
			sleep(5);
			
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