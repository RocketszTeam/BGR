<?php
defined('BASEPATH') OR exit('No direct script access allowed');
abstract class Core_controller extends CI_Controller {

	public function __construct() {
		parent::__construct();	
		//設定時區+8h
		date_default_timezone_set("Asia/Taipei");	
		
		//設定允許存取的白名單
		$allow_IP=array();
		array_push($allow_IP,'127.0.0.1');
		array_push($allow_IP,'114.35.87.221');
		array_push($allow_IP,'122.117.20.37');
		array_push($allow_IP,'139.162.82.172');
		array_push($allow_IP,'59.126.249.97');
		array_push($allow_IP,'220.132.30.112');
		array_push($allow_IP,'59.126.18.8');
		//取得UserIP
		$UserIP=$this->input->ip_address();
		if(!in_array($UserIP,$allow_IP) && !$this->input->is_ajax_request()){	//不在白名單內且非ajax存取時候 才判斷
			//echo '您無權限存取此頁面';exit;
		}
		
		$this->load->library('api/allbetapi');	//歐博
		//$this->load->library('api/royalapi');	//皇家
		$this->load->library('api/fishapi');	//捕魚機		
		$this->load->library('api/superapi');
		$this->load->library('api/sagamingapi');	//沙龍	
		$this->load->library('api/qtechapi');	//QT電子	
		$this->load->library('api/dreamgame');	//dg真人	
		$this->load->library('api/Wmapi');	//WM真人
		$this->load->library('api/slotteryapi');	//Super 彩球
		$this->load->library('api/ssbapi');	//贏家體育
		$this->load->library('api/s7pkapi');	//7PK
		$this->load->library('api/bingoapi');	//賓果
		$this->load->library('api/ebetapi');	//Ebet真人
		$this->load->library('api/waterapi');	//水立方
		$this->load->library('api/ameba');	//AMEBA
		
	}

	
	public function curl_post(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$post_data=$this->input->post();
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$post_data["path"]);		
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				$curl_errno = curl_errno($ch);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				//print_r($output);exit;
				if(!$curl_errno){
						echo $output;
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'系統發生錯誤，請通知網管人員'));
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'網域不被允許'));
		}
	}
	

}
