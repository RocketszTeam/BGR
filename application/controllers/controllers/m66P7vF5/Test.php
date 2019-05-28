<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Test extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		$this->load->library('api/allbetapi');	//歐博API
		//$this->load->library('api/sagamingapi');	//沙龍
		//$this->load->library('api/superapi');	//Super
		
		//$this->load->library('api/Wmapi');	//Wm	
		$this->load->library('api/s9k168');//9k
		$this->load->library('api/ameba');	//ameba
		$this->load->library("api/rtgapi");//RTG
	}
	
	
	
	//RTG取得api token
	public function rtgapi(){
		
		var_dump($this->rtgapi->index_token());
		
	}
	
	//RTG取得代理資訊
	public function rtgapi_start(){
		var_dump($this->rtgapi->index_agent());
		//var_dump($this->rtgapi->index_agent()->agentId);
		//var_dump($this->rtgapi->index_3()->casinos[0]->id);
		
	}
	//RTG建立遊戲帳號
	public function rtgapi_create_account(){
		echo "<pre>";
		var_dump($this->rtgapi->create_account($u_id="GRrockvgt1",$u_password="a123456",$mem_num=20669,$gamemaker_num=51));
	}
	//RTG deposit
	public function rtgaoi_deposit(){
		echo "<pre>";
		var_dump($this->rtgapi->deposit($u_id="GRrockvgt1",$amount=50,$mem_num=20669,$gamemaker_num=51,$logID=NULL));
	}
	//RTG get_balance
	public function rtgaoi_get_balance(){
		echo "<pre>";
		var_dump($this->rtgapi->get_balance($u_id="GRrockvgt1"));
	}
	//RTG withdrawal.
	public function rtgapi_withdrawal(){
		echo "<pre>";
		var_dump($this->rtgapi->withdrawal($u_id="GRrockvgt1",$amount=50,$mem_num=20669,$gamemaker_num=51,$logID=NULL));
	}
	//RTG gamestrings
	public function rtgapi_gamestrings(){
		echo "<pre>";
		var_dump($this->rtgapi->gamestrings());
	} 
	//RTG forward_game
	public function rtgapi_forward_game(){
		echo "<pre>";
		var_dump($this->rtgapi->forward_game("WErocketsz",$gameId="1179826"));
	}
	//RTG fun_game
	public function rtgapi_fun_game(){
		echo "<pre>";
		var_dump($this->rtgapi->fun_game("D9rocketsz",1179826));
	}
	//RTG reporter_all
	public function rtgapi_reporter_all(){
		echo "<pre>";
		var_dump($this->rtgapi->reporter_all());
	}
	
	
	//ameba進入遊戲測試範例
	public function ameba_TEST_index($u_id="PFrocketsz",$game_id=39){	
		$post_data=array('action'=>'register_token','site_id'=>$this->ameba->site_id,'account_name'=>trim($u_id),'game_id'=>$game_id,'lang'=>$this->ameba->lang);
		$output=$this->ameba->curl($post_data);
		print_r($output);
	}
	
	private function encryptText($plain_text) {
	    $padded = $this->pkcs5Pad($plain_text, mcrypt_get_block_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_CBC));
		return mcrypt_encrypt(MCRYPT_TRIPLEDES, base64_decode(ALLBET_DES_KEY), $padded, MCRYPT_MODE_CBC, base64_decode("AAAAAAAAAAA="));
	}
	
	private function pkcs5Pad($text, $blocksize) {
	    $pad = $blocksize - (strlen($text) % $blocksize);
	    return $text . str_repeat(chr($pad), $pad);
	}
	
	private function getSignCode($data){
		$to_sign = $data.ALLBET_MD5_KEY;
		return base64_encode(md5($to_sign, TRUE));	
	}
	
	public function rrfvv(){
		$post_data=array('MemberAccount'=>trim("PFya88213"),'MemberPassword'=>"+ihQlImfjDao0f8kkqS/yZCy2RQ7LwFzWo=");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->s9k168->api_url.$this->s9k168->ApiToken.'/UserLogin');	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->s9k168->timeout); 
		$output = json_decode(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $http_code;exit;
		print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->success==0){
				var_dump($output->data->UserLogin->GameUrl);
			}else{
			 	var_dump($output->success.':'.$output->msg);
			}
		}else{
			var_dump('系統繁忙中，請稍候再試');
		}
	}
	
	public function ppt(){
		$this->load->model('admin/Report_model', 'report');
		$rowAll = $this->report->hces888("0", "2018-08-01 00:00:00", "2018-08-12 00:00:00", '', '', false);
		$dataList = $this->report->buildData($dataList, $rowAll, 'hces888');
		 $pointnum = array();
                $i = 0;
                foreach ($dataList as $keys => $row) {
                    $pointnum[$i] = $row["num"];
                    $u_power = $row["u_power"];

                    $i++;
                }
		echo "<pre>";		
		var_dump($dataList);
		var_dump($pointnum);
		var_dump($u_power);
		
	}
	
	public function index($root=0){	
		
		
		
		////////////////////////////////////////////////////////////////////////////////////////
		//歐博報表
		/*
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}

		$data = $dreamgame_report->get_report() ;
		
		//代理點進來 改列出會員處理
		
		
		$this -> load -> view("admin/test", $this -> data);  
		*/

		//WEabc123,WEabc1234

		//echo $this->wmapi->Hello();
		/*
		echo $this->wmapi->create_account("WEabc1235","a123456",10,13);
		

		
		echo $this->wmapi->deposit("WEabc1235",1000,10,13);
		echo "<!--";
		echo $this->wmapi->forward_game("WEabc1235","a123456");
		echo "-->";	
		*/	
		/*
		$r = $this->wmapi->reporter_all("20180611","20180612");
		print_r($r);
		*/
		//$log = $this->s9k168->create_account("WEbigear42","a123456",10,26);
		
		//$log = $this->s9k168->deposit("WEbigear42",100,10,26);
		
		//print_r($log);		
	}
	
	
	
	public function create_account(){
		$aa123 = $this->vgapi->create_account("j123","a123456",10483,38);
		var_dump($aa123);
	}
	
	public function get_balance(){
		$aa456 = $this->vgapi->get_balance("WErocketsz");
		var_dump($aa456);
	}
	
	
	public function deposit(){
		$aaaty = $this->vgapi->deposit("WErocketsz",100,5,38);
		var_dump($aaaty);
	}
	
	public function withdrawal(){
		$aaatu = $this->vgapi->withdrawal("a123",500,1,38);
		var_dump($aaatu);
	}
	
	
	public function forward_game(){
		//$aaatu = $this->vgapi->forward_game("WErocketsz",7);
		//var_dump($aaatu);
		
		$u_id = "WErockvgt1";
		$gameType=8;
		$gameversion=1;
		$string = strtoupper($u_id)."loginWithChannel"."RSG".$gameType.$gameversion."12E1o#46Ue*hjiI90";
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://223.27.38.244:7000/webapi/interface.aspx?username=".strtoupper($u_id)."&action=loginWithChannel&channel=RSG"."&gameType=".$gameType."&gameversion=".$gameversion."&verifyCode=".$md5encrypt_upper);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,15); 
		$output = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//var_dump($output);
		
		if(!$curl_errno){
			//為避免simplexml_load_string對於"&"有錯誤訊息，所以先置換"&"為"@"
			
			if($gameType ==1 || $gameType ==2 || $gameType ==3 || $gameType ==4){ //若遊戲不為楚漢德州 則 回復回來的 result有& ，要置換
				
				//為避免simplexml_load_string對於"&"有錯誤訊息，所以先置換"&"為"@"
				$aaa = strpos($output,"&"); //在xml字串若有"&"
				if($aaa){
					$aaab = str_replace("&","@",$output); //xml字串若有"&"，先置換為"@"
					$aaac = simplexml_load_string($aaab);
					$aaad = str_replace("@","&",$aaac->result);//變成物件後 再把"@"置換回"&"
				}
			
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					var_dump($aaad);
				}else{
					var_dump($aaac->errcode.':'.$aaac->errtext);
				}
			}elseif($gameType ==7 || $gameType==8){  //若遊戲為楚漢德州 則回復回來的 result 沒有& ，所以不用置換
				$aaac = simplexml_load_string($output);
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					
					var_dump($aaac->result);
				}else{
					var_dump($aaac->errcode.':'.$aaac->errtext);
				}
			}
			
		}else{
			return '系統繁忙中，請稍候再試';
		}
		
	}
	
	public function reporter_all(){
		//$tty = $this->vgapi->reporter_all($id=0);
		//$tty->value=array();
		$last_count_array_id=0;
		$count_array=50;
		$vd="";
		$uid="";
		while($count_array ==50){	
			$tty = $this->vgapi->reporter_all($last_count_array_id);
			echo "<pre>";
			var_dump($tty->value);
			foreach($tty->value as $row){
				//var_dump($row->username);
				if(!strpos(" ".$uid,"'".$row->username."'")){
					$uid .= "'".$row->username."',";
				}
			}
			$count_array = count($tty->value); //取得共有幾條
			$last_count_array_id = $tty->value[$count_array-1]->id; //取道最後一條id
			
			sleep(5);
			$vd += $count_array;
		}
		//$uid = substr($uid,0,strlen($uid)-1);	
		var_dump($vd,$uid);
		//var_dump($count_array);
	
		
		
	}
	
	public function fun_game(){
		//$aav = $this->vgapi->fun_game(7);
		//var_dump($aav);
		$gametype = 7;
		$gameversion = 1;
		$string = "RSG".$gametype.$gameversion."12E1o#46Ue*hjiI90";
		$md5encrypt_upper = strtoupper(md5($string));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://223.27.38.244:7000/webapi/trygame.aspx?channel=RSG&gametype=".$gametype."&gameversion=".$gameversion."&verifyCode=".$md5encrypt_upper);	
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,15); 
		$output = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		var_dump($output);
		
		if($gametype !==7){ 
				//為避免simplexml_load_string對於"&"有錯誤訊息，所以先置換"&"為"@"
				$aaa = strpos($output,"&"); //在xml字串若有"&"
				if($aaa){
					$aaab = str_replace("&","@",$output); //xml字串若有"&"，先置換為"@"
					$aaac = simplexml_load_string($aaab);
					$aaad = str_replace("@","&",$aaac->result);//變成物件後 再把"@"置換回"&"
				}
			
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					var_dump($aaad);
				}else{
					var_dump($aaac->errcode.':'.$aaac->errtext);
				}
			}elseif($gametype ==7){  //若遊戲不為楚漢德州 則 回復回來的 result 沒有& ，所以不用置換	
				$aaac = simplexml_load_string($output);
				if($http_code===200 && $aaac->errcode=="0" && $aaac->errtext="success"){	
					
					var_dump($aaac->result);
				}else{
					var_dump($aaac->errcode.':'.$aaac->errtext);
				}
			}
		
		
		
		
	}
	
	
	
	
	
} 