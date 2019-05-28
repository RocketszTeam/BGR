<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Opengame extends Core_controller{
	private $url_match='/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i';	//遊戲url驗證
	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
		$this->load->library('api/allgameapi');	//載入遊戲api
		$this->load->library('api/allbetapi');	//歐博
		$this->load->library('api/sagamingapi');	//沙龍
		$this->load->library('api/dreamgame');	//dg		
		$this->load->library('api/Wmapi');	//Wm		
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;	
		}
	}
	
	public function index(){
        if(strlen($this->input->get('GameCode')) > 35){
            scriptMsg('',"Index");
            exit;
        }
        if((int)$this->input->get('gm') > 100){
            scriptMsg('',"Index");
            exit;
        }
		if($this->input->get('gm')){
			$sqlStr="select * from `game_makers` where `online`=1 and num=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$this->input->get('gm',true)));
			if($row!=NULL){
				
				//取得點擊次數 寫入game_hit
				$u_power6=tb_sql("admin_num",'member',$this->memberclass->num());	//代理編號
				$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
				$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
				//var_dump($u_power6."|".$u_power5."|".$u_power4);
				
				$data = array(
					"makers_num"=>$this->input->get('gm'),
					"makers_name"=>$row['makers_name'],
					"mem_num"=>$this->memberclass->num(),
					"u_power4"=>$u_power4,
					"u_power5"=>$u_power5,
					"u_power6"=>$u_power6,
					"buildtime"=>date("Y-m-d H:i:s"),
					"datee"=>date("Y-m-d"),
					"last_ip"=>$this->input->ip_address(),
					
				);
				$this->db->insert('game_hit',$data);
				
				
				
				//維修判斷
				$nowTime=date('wHis');	//現在時間
				if(($nowTime >= $row['selltime1'] &&  $nowTime <= $row['selltime2']) || $row["active"]!='Y'){	//遊戲被關閉或者處於維護狀態 跳轉到維修頁面
					header("Location:".site_url("Service"));
					exit;
				}
				
				$sqlStr="select * from `games_account` where `gamemaker_num`=".$row["num"]." and `mem_num`=?";
				$row2=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
				
				if($row2==NULL){//如果沒遊戲帳號，自動建立帳號
					$sqlStr="select * from `member` where num=?";
					$rowMember=$this->webdb->sqlRow($sqlStr,array(':num'=>$this->memberclass->num()));

					if($rowMember!=NULL){				
						$this->data["link"]=$this->allgameapi->create_account2($this->memberclass->u_id(),$this->encryption->decrypt($rowMember["u_password"]),$rowMember["num"],(int)$this->input->get('gm'));
					}

					//再讀取一次資料					
					$sqlStr="select * from `games_account` where `gamemaker_num`=".$row["num"]." and `mem_num`=?";
					$row2=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
				
				}
				
				
				if($row2!=NULL){
					$u_id=$row2["u_id"];
					if($this->input->get('gm')=='3' && $this->agent->is_mobile()){	//真對歐博手機板
						//$row2["u_id"]=$u_id.$this->allbetapi->getIdenCode();


					}
					$u_password=$this->encryption->decrypt($row2["u_password"]);
					$row2["u_password"]=$u_password;	//針對黃金
					$this->data["accountInfo"]=$row2;
					if($this->input->get('gm')=='20'){	//Super彩球
						$this->data["link"]=site_url("Opengame/slottery?u_id=".urlencode($row2["u_id"])."&u_password=".urlencode($row2["u_password"]));
						$this->load->library('api/slotteryapi');	//Super彩球
						$this->slotteryapi->forward_game($row2["u_id"],$row2["u_password"]);
						
						
					}elseif($this->input->get('gm')=='9'){	//針對沙龍
						$this->sagamingapi->SetUserMaxWin($u_id);	//最大贏額設定
						if(!$this->input->get('GameCode')){	//真人
							$token=$this->sagamingapi->forward_game($u_id);
							if($token=='--'){
								scriptCloseMsg('取得遊戲連結錯誤！');	
								exit;
							}
							if($this->agent->is_mobile()){
								echo '<form id="form1" method="post" action="'.$this->sagamingapi->getLoginUrl().'">';
								echo '<input type="hidden" name="username" value="'.$u_id.'" />';
								echo '<input type="hidden" name="token" value="'.$token.'" />';
								echo '<input type="hidden" name="lobby" value="'.$this->sagamingapi->getLobbycode().'" />';
								echo '<input type="hidden" name="lang" value="zh_TW" />';
								echo '<input type="hidden" name="mobile" value="true"  />';
								echo '</form>';
								echo '<script type="application/javascript">document.getElementById("form1").submit();</script>';
								exit;
							}
							
							$this->data["link"]=site_url("Opengame/sagameing?u_id=".$u_id."&token=".$token);
						}else{	
							if($this->input->get('GameCode',true) != "FishermenGold"){
								//老虎機
								$this->data["link"]=$this->sagamingapi->slot_forward_game($u_id,$this->input->get('GameCode',true));
								if(!preg_match($this->url_match,$this->data["link"])){
									scriptCloseMsg('取得遊戲連結錯誤！');	
									exit;
								}
							} else {
								//捕魚機
								$this->data["link"]=$this->sagamingapi->fish_forward_game($u_id,$this->input->get('GameCode',true));
								if(!preg_match($this->url_match,$this->data["link"])){
									scriptCloseMsg('取得遊戲連結錯誤！');	
									exit;
								}								
							}
						}
					}elseif($this->input->get('gm')=='38'){
						
						//查詢目前有那些gamecode
						$parameter=array();
						$gamecodeList= array();
						$sqlStr="select game_code from `games` where `active`='Y' and makers_num=38";
						$gamecodeList = $this->webdb->sqlRowList($sqlStr,$parameter);
						$ttQw = "";//將查殉道的gamecode變成字串
						
						foreach($gamecodeList as $key=>$row){
							
							if($ttQw =="" && $key==0){
								$ttQw .= $row['game_code'];
							}elseif($ttQw !=="" && $key!==0){
								$ttQw .= ",".$row['game_code'];
							}elseif($ttQw !==""){
								$ttQw .= $row['game_code'].",";
							}
						}
						
						//將要加入的遊戲號碼加入allow_gamecode;
						//$allow_gamecode = array(1,3,4,7,8,9,10,11,12,15);//1,3,4,7,8,9,10,11,12,15
						//if( in_array($this->input->get('GameCode'),$allow_gamecode)){//正確進到遊戲
						
						$ffgh= strpos($ttQw,$this->input->get('GameCode'));//判斷$this->input->get('GameCode')是否有在$ttQw字串中
						if($ffgh !== false){ //如果$this->input->get('GameCode') 有在$ttQw字串中則打開個別遊戲
						  if(!$this->agent->is_mobile()){
							$this->data["link"]=$this->vgapi->forward_game($u_id,$this->input->get('GameCode'));
						  }else{
							$this->data["link"]=$this->vgapi->mobile_forward_game($u_id,$this->input->get('GameCode'));
						  }
						} else {
						  //大廰
						  if(!$this->agent->is_mobile()){
							$this->data["link"]=$this->vgapi->forward_game($u_id,1000);
						  }else{
							$this->data["link"]=$this->vgapi->mobile_forward_game($u_id,1000);
						  }

						}
						
					}else{
						$this->data["link"]=$this->allgameapi->forward_game($u_id,$u_password,$row["num"],$this->input->get('GameCode',true));
						if($this->agent->is_mobile() && $this->input->get('gm')==12){	//DG手機板網址過不了驗證
							header("Location:".$this->data["link"]);
							exit;	
						}
						if(!preg_match($this->url_match,$this->data["link"])){	//針對網址格式作驗證 格式錯誤代表api掛了
							scriptCloseMsg('取得遊戲連結錯誤！!!!');
							echo "<!--";
							print_r($this->data);
							echo "-->";								
							exit;
						}
						if($this->agent->is_mobile()){	//Combet不支援Iframe
							header("Location:".$this->data["link"]);
							exit;	
						}
					}
					$this -> load -> view("www/opengame", $this -> data);
				}else{
					scriptCloseMsg('您尚未擁有此類型遊戲帳號！');	
				}
			}else{
				scriptCloseMsg('該遊戲廠商不存在！');
			}
		}else{
			scriptCloseMsg('參數錯誤！');
		}
	}
	
	//歐博手機版 要載的頁面
	public function allbet_dl(){
		
		//歐博用
		if($this->input->get('gm')){	
			$sqlStr="select * from `game_makers` where num=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$this->input->get('gm',true)));
			if($row!=NULL){
				$sqlStr="select * from `games_account` where `gamemaker_num`=".$row["num"]." and `mem_num`=?";
				$row2=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
				if($row2!=NULL){
					$u_id=$row2["u_id"];
					$row2["u_id"]=$u_id.$this->allbetapi->getIdenCode();
					$u_password=$this->encryption->decrypt($row2["u_password"]);
					$row2["u_password"]=$u_password;	
					$this->data["accountInfo"]=$row2;
				}
			}
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,'https://www.abgapp.net/config.json');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,4); 
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		
		$this->data["downLoad"]=array();
		foreach($output as $keys=>$row){
			switch ($row->os){
				case "iphone":
					$this->data["downLoad"]["iphone_link"]=$row->link;
					break;
				case "ipad":
					$this->data["downLoad"]["ipad_link"]=$row->link;
					break;
				default:
					$this->data["downLoad"]["android_link"]=$row->link;
					break;	
			}
		}
		//print_r($this->data["downLoad"]);exit;
		


		$this -> load -> view("www/allbet_dl.php", $this -> data);
	}
	
	public function dg_mob(){
		//dg 真人用
		$sqlStr="select * from `game_makers` where num=12";
		$row=$this->webdb->sqlRow($sqlStr);
		if($row!=NULL){
			$sqlStr="select * from `games_account` where `gamemaker_num`=12 and `mem_num`=?";
			$row2=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
			if($row2!=NULL){
				$u_id=$row2["u_id"];
				$U_password = $row2['u_password'];
				//$row2["u_id"]=$u_id.$this->allbetapi->getIdenCode();
				//$u_password=$this->encryption->decrypt($row2["u_password"]);
				//$row2["u_password"]=$u_password;	
				//$this->data["accountInfo"]=$row2;
				$this->data["dgaccountInfo"]["u_id"]=$u_id.'@CDY';
				//$this->data["dgaccountInfo"]["u_password"]=$U_password;	
			}
		}
		$output = $this->dreamgame->getapp($u_id,$u_password);
		$this->data["dgdownLoad"]['app']=$output;
	}
	
	
	public function sagameing(){	//沙龍
		$this->data["lobby"]=$this->sagamingapi->getLobbycode();
		$this->data["formAction"]=$this->sagamingapi->getLoginUrl();
		$this -> load -> view("www/sagameing", $this -> data);
	}
	
	public function super(){
		$this->load->library('api/superapi');	//載入遊戲api
		//$this->superapi->logout($this->input->get('u_id',true));	//先執行登出
		$this->data["account"]=$this->superapi->getAesEncrypt($this->input->get('u_id',true));
		$this->data["passwd"]=$this->superapi->getAesEncrypt($this->input->get('u_password',true));
		$this->data["formAction"]=$this->superapi->getLoginUrl();
		
		$this -> load -> view("www/super", $this -> data);
		
	}
	
	//super彩球
	public function slottery(){
		$this->load->library('api/slotteryapi');	//Super彩球
		$u_id=$this->input->get('u_id',true);
		$u_password=$this->input->get('u_password',true);
		$this->slotteryapi->forward_game($u_id,$u_password);
		
		//$this -> load -> view("www/slottery", $this -> data);
	}
	
} 