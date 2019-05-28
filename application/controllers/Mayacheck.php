<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Mayacheck extends CI_Controller{
    function __construct(){
        parent::__construct(); // needed when adding a constructor to a controller
		date_default_timezone_set("Asia/Taipei");
		$this->load->library('api/maya');
    }
	
	
	public function index(){
		if (isset($_GET['url'])) {
			$url = base64_decode($_GET['url']);
			header("Content-Security-Policy: upgrade-insecure-requests");
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			echo $data;

		}
	}
	

	public function CheckLogin(){
		if($this->input->post('MemberName') && $this->input->post('IpAddress') && $this->input->post('Token')){
			$Token=$this->maya->getToken();
			if($this->input->post('Token')==$Token){
				echo json_encode(array('ErrorCode'=>0,'ErrorDes'=>''));
			}else{
				echo json_encode(array('ErrorCode'=>1,'ErrorDes'=>'Token錯誤'));
			}
		}else{
			echo json_encode(array('ErrorCode'=>-1,'ErrorDes'=>'參數錯誤'));
		}
	}
	
	public function GetMemberLimitInfo(){	
		if( $this->input->get('GameOpenMemberID',TRUE) || $this->input->get_request_header('GameOpenMemberID',TRUE) ){
			$sqlStr="select `num` from `games_account` where gamemaker_num = 33 and `u_id`=?";
			$gomid = $this->input->get_request_header('GameOpenMemberID',TRUE);
			if( $this->input->get('GameOpenMemberID',TRUE) ){
				$gomid = $this->input->get('GameOpenMemberID',TRUE);	
			}
			$parameter=array(':u_id'=>$gomid);
			
			$row=$this->webdb->sqlRow($sqlStr,$parameter);
			if($row==NULL){//會員不存在
				echo json_encode(array('ErrorCode'=>1,'GameLimitID'=>$this->maya->GameConfigID));
			}else{//成功
				echo json_encode(array('ErrorCode'=>0,'GameLimitID'=>$this->maya->GameConfigID));
			}			
		}else if( $this->input->post('GameOpenMemberID',TRUE) ){
			$sqlStr="select `num` from `games_account` where gamemaker_num = 33 and `u_id`=?";
			$gomid = $this->input->post('GameOpenMemberID',TRUE);

			$parameter=array(':u_id'=>$gomid);
			
			$row=$this->webdb->sqlRow($sqlStr,$parameter);
			if($row==NULL){//會員不存在
				echo json_encode(array('ErrorCode'=>1,'GameLimitID'=>$this->maya->GameConfigID));
			}else{//成功
				echo json_encode(array('ErrorCode'=>0,'GameLimitID'=>$this->maya->GameConfigID));
			}				
		}else{//找不到
			echo json_encode(array('ErrorCode'=>1,'GameLimitID'=>$this->maya->GameConfigID));			
		}
		
	}
	

}

?>