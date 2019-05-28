<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Qt extends Core_controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('api/slotteryapi');	//Super彩球
		$this->load->library('api/ssbapi');	//贏家體育
		$this->load->library('api/s7pkapi');	//7PK
		$this->load->library('api/bingoapi');	//賓果
		$this->load->library('api/allbetapi');	//賓果
		$this->load->library('api/ebetapi');	//賓果
		$this->load->library('api/waterapi');	//賓果
	}
	
	public function test(){
		$sTime='2017-09-27 17:00:00';
		$eTime='2017-09-27 22:00:00';
		$result=$this->waterapi->reporter_all($sTime,$eTime);
		
	}
	
	
	public function index(){
		
		
		$u_id='F619';
		$u_password='dl1onxl1o';

		//$this->slotteryapi->create_account($u_id,$u_password,11);
		//$this->slotteryapi->get_balance($u_id);
		//$this->slotteryapi->deposit($u_id,1500000,1);
		//$this->slotteryapi->withdrawal($u_id,10,1);
		//$this->slotteryapi->forward_game($u_id,$u_password);
	}
	
	public function water(){
		$cid=45855;
		$u_id='test55557a7';
		$u_password='2iduiggr2';
		
		$api_url='http://111.oy33.net/api/system/';
		$apikey='1a2e2e5a95384327b4f8c40abfb1601b';
		
		$post_data=array('act'=>'adduser','apikey'=>$apikey,'user'=>trim($u_id),'pwd'=>trim($u_password),
						 'lang'=>'zh-tw','ip'=>$this->input->ip_address(),'nick'=>trim($u_id));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$api_url);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->code==802){	//帳號創建成功
			
			}
		}
	}
	
	public function login(){
		$u_id='mytestplayer';
		$u_password='mytestpass';
		
		$api_url='http://111.oy33.net/api/system/';
		$apikey='1a2e2e5a95384327b4f8c40abfb1601b';
		
		$post_data=array('act'=>'login','apikey'=>$apikey,'user'=>trim($u_id),'pwd'=>trim($u_password),
						 'lang'=>'zh-tw','ip'=>$this->input->ip_address());
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$api_url);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;
		print_r($output);
		echo '<hr>';
		$this->opengame($output->data->uid);
		
	}
	
	public function opengame($uid){
		$api_url='http://111.oy33.net/api/system/';
		$apikey='26c8a97aa79244e0a52a0cf98af53cea';
		
		$post_data=array('act'=>'openlobby','apikey'=>$apikey,'uid'=>trim($uid));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$api_url);		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;
		//header("Location:".$output->data);
		//exit;
		print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->code==200){	
				
				echo '<hr>';
				echo '<a href="'.$output->data.'" target="_blank">'.$output->data.'</a>';
				echo '<hr>';
				echo '<iframe id="gameFrame" src="'.$output->data.'" frameborder="0" width="100%" height="500"  scrolling="auto" marginheight="0" marginwidth="0"></iframe>';
				
			}
		}
	}
		
} 