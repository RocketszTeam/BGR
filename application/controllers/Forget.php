<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Forget extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
	}
	
	public function index(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg==NULL){header("Location:".base_url());exit;}	//如果已經登入會員跳轉
		//產生驗證碼token
		$this->data["token"] = md5(uniqid(rand(), true));		
		$this->data["forget_sms_token"] = md5(uniqid(rand(), true));	//手機簡訊token		
		$this->session->set_userdata('code_token', $this->data["token"]);		
		$this->session->set_userdata('forget_sms_token', $this->data["forget_sms_token"]);		
			
		
		$this -> load -> view("www/forget.php", $this -> data);
	}
	
	public function forget_do(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if(strtolower($this->input->post('forget_checknum',true))==strtolower($this->session->userdata('forget_checknum'))){	//驗證碼判斷
					if($this->session->userdata('forget_sms_token') && $this->input->post('forget_sms_token',true)==$this->session->userdata('forget_sms_token')){
						$parameter=array();
						$sqlStr="select num,u_id,u_name,u_password from member where u_id=? and phone=?";
						$parameter[':u_id']=$this->input->post('u_id',true);
						$parameter[':phone']=$this->input->post('phone',true);
						$row=$this->webdb->sqlRow($sqlStr,$parameter);
						if($row!=NULL){
							$this->load->library('smsclass');
							$limit_time=30;	//重複發送間隔
							$forget_last_send_time=$this->session->userdata('forget_last_send_time');
							if(!$this->session->userdata('forget_last_send_time')){
								$this->session->set_userdata('forget_last_send_time',strtotime("-40 second"));
							}
							$send_time=time();	//此次發送時間
							$subject=$this->data["com_name"]."會員忘記密碼";
							$content =$this->data["com_name"]."。忘記密碼，你的密碼為：【".$this->encryption->decrypt($row["u_password"])."】";
							if($send_time-$this->session->userdata('forget_last_send_time') > $limit_time){
								if($this->smsclass->sendSMS($content, $this->input->post('phone',true),$subject)){
									$this->session->set_userdata('forget_last_send_time',$send_time);	//記錄此次發送時間
									$this -> session -> set_flashdata("alertMsg",'您的密碼已經發送簡訊到您的手機上了');
									echo json_encode(array('RntCode'=>'Y','Msg'=>'您的密碼已經發送簡訊到您的手機上了'));
								}else{
									echo json_encode(array('RntCode'=>'N','Msg'=>'簡訊發送失敗，請聯絡網管人員'));
								}
							}else{
								$time_wait=$send_time-$this->session->userdata('forget_last_send_time');
								echo json_encode(array('RntCode'=>'W','last_send_time'=>$time_wait,'Msg'=>'發送次數太頻繁~請稍候在發送'));
							}
						}else{
							echo json_encode(array('RntCode'=>'N','Msg'=>'此會員資料並不存在！'));
						}
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'Access Token Error'));
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'驗證碼錯誤,請重新輸入!!'));
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'您的網域不被允許'));
		}
	}
		
} 