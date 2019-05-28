<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Receivegame extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
		$this->load->library('api/allgameapi');	//載入遊戲api
		
		//定義現在時間
		$nowTime=date('wHis');
		//定義各館維修時間
		$serviceIng=array();
		//星期一維修
		$serviceIng[9]=array('sTime'=>1103000,'eTime'=>1140000);	//沙龍維修10:30:00-14:00:00
		$serviceIng[8]=array('sTime'=>1120000,'eTime'=>1160000);	//super體育跟彩球維修12:00:00-16:00:00
		$serviceIng[25]=array('sTime'=>1113000,'eTime'=>1130000);	//水立方維修11:30:00-13:00:00
		
		//星期三維修
		$serviceIng[3]=array('sTime'=>3080000,'eTime'=>3120000);	//歐伯維修08:00:00~12:00:00
		$serviceIng[12]=array('sTime'=>3070000,'eTime'=>3090000);	//DG維修07:00:00~09:00:00
		
		//定義各館維修狀態
		$this->data["abService"]=false;	//歐伯
		if($nowTime >= $serviceIng[3]['sTime'] &&  $nowTime <= $serviceIng[3]['eTime']){	//歐伯維修
			$this->data["abService"]=true;
		}
		$this->data["dgService"]=false;	//DG
		if($nowTime >= $serviceIng[12]['sTime'] &&  $nowTime <= $serviceIng[12]['eTime']){	//DG維修
			$this->data["dgService"]=true;
		}
		$this->data["saService"]=false;	//沙龍
		if($nowTime >= $serviceIng[9]['sTime'] &&  $nowTime <= $serviceIng[9]['eTime']){	//沙龍維修
			$this->data["saService"]=true;
		}
		$this->data["superService"]=false;	//super體育跟彩球
		if($nowTime >= $serviceIng[8]['sTime'] &&  $nowTime <= $serviceIng[8]['eTime']){	//super體育跟彩球維修
			$this->data["superService"]=true;
		}
		$this->data["waterService"]=false;	//水立方
		if($nowTime >= $serviceIng[25]['sTime'] &&  $nowTime <= $serviceIng[25]['eTime']){	//水立方維修
			$this->data["waterService"]=true;
		}
		
	
	}
	
	public function index(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			
			$this->data["gameAccount"]=array();
			$sqlStr="select `gamemaker_num` from `games_account` where mem_num=?";
			$parameter[':mem_num']=$this->memberclass->num();
			$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					array_push($this->data["gameAccount"],$row["gamemaker_num"]);	
				}
			}
			
			$this -> load -> view("www/receivegame.php", $this -> data);
		}
	}
	
	//ajax領取遊戲帳號
	public function ajaxCreate(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$Loginlog=$this->memberclass->isLogin();
				if($Loginlog==NULL){
					if($this->input->post('makers_num')){
						$sqlStr="select `num`,`u_id`,`u_password` from `member` where `num`=?";
						$parameter[':num']=$this->memberclass->num();
						$row=$this->webdb->sqlRow($sqlStr,$parameter);
						if($row!=NULL){
							$makers_num=$this->input->post('makers_num',true);
							$u_id=$row["u_id"];
							$u_password=$this->encryption->decrypt($row["u_password"]);	//密碼解密
							$logMsg=$this->allgameapi->create_account2(trim($u_id),$u_password,$row["num"],$makers_num);
							//print_r($logMsg);
							if($logMsg==NULL){
								$this -> session -> set_flashdata("alertMsg",'遊戲帳號領取成功！');
								echo json_encode(array('RntCode'=>'Y','Msg'=>'遊戲帳號領取成功！'));
							}else{
								echo json_encode(array('RntCode'=>'N','Msg'=>'遊戲回傳訊息：'.$logMsg));
							}
						}else{
							echo json_encode(array('RntCode'=>'W','Msg'=>'會員資料不存在'));
						}
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'遊戲編號空白'));
					}
				}else{
					echo json_encode(array('RntCode'=>'W','Msg'=>'請先登入會員'));
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'您的網域不被允許'));
		}
		
	}
		
} 