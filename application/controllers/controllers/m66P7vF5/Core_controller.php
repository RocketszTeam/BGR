<?php
defined('BASEPATH') OR exit('No direct script access allowed');
abstract class Core_controller extends CI_Controller {
	protected $data;
	protected $nation;
	protected $mydata;
	protected $breadcrumb = array();
	protected $designIP = array();
	
	/****** 登入資訊用  ******/
	protected $web_root_login_status=false;
	protected $web_root_num;
	protected $web_root_u_id;
	protected $web_root_u_name;
	protected $web_root_time;
	protected $web_root_u_power;
	protected $web_root_power_name;
	protected $web_root_power_list;

	public function __construct() {
		parent::__construct();
			
		$now_url=explode('/',$this -> uri -> uri_string());//取得當前頁面URL
		//設定時區+8h
		date_default_timezone_set("Asia/Taipei");	
		$this->data["att"]="";
		$this->data["satt"]="";
		
		$this -> data["js"] = array();
		$this -> data["css"] = array();
		
		//語系相關
		$this->data["isMultiLanguage"]=$this->isMultiLanguage();
		$this->data["lang"]=$this->languageSet();	//載入語系設定	
		$this->nation=(@$_COOKIE['nation']!="" ? @$_COOKIE['nation'] : $this->defaultNation());	//取得預設語系
		$this->data["nation"]=$this->nation;
		
		
		
		//載入公司資訊
		$sqlStr="select * from `company` where num=1";
		$rowAll=$this->webdb->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $k=>$v){	
				$this->data[$k]=$v;
			}
		}
		
		
		
		if(in_array(SYSTEM_URL,$now_url) || in_array('module',$now_url)){	//判斷那些只在後台執行	
			//設定允許顯示設計師模式的IP位置
			//array_push($this->designIP,'127.0.0.1');
			//array_push($this->designIP,'59.126.18.8');
			//array_push($this->designIP,'103.227.173.15');
			//array_push($this->designIP,'103.227.173.20');
			//array_push($this->designIP,'139.162.127.155');
			//array_push($this->designIP,'49.156.46.6');

			$this->data["nation"]=($this->isMultiLanguage()?'':$this->defaultNation()); //如果無多語系則取得預設語系
			
			
			$this -> data["title"] = CMS_NAME;
			$this -> data["alert"] = $this -> _getMsg();
			//延續搜尋條件
			$this->data["att"]=$this->getRequests('find');
			$this->data["satt"]=$this->getRequests('sfind');
			$this->data["find_msg"]="";

			
			if($this->input->cookie('login_root',true)!=""){//後台登入資訊
				$tmpVal=$this->encryption->decrypt($this->input->cookie('login_root',true));//解密
				$cookie_root_tmp=explode("|",$tmpVal);
				if (count($cookie_root_tmp)==8){
					$this->web_root_num=$cookie_root_tmp[0];	//記錄流水號
					$this->web_root_u_id=$cookie_root_tmp[1];	//記錄帳號
					$this->web_root_u_name=urldecode($cookie_root_tmp[2]);	//記錄使用者
					$this->web_root_time=$cookie_root_tmp[3];	//記錄登入時間
					$this->web_root_u_power=$cookie_root_tmp[4];	//記錄權限代碼
					$this->web_root_power_name=urldecode($cookie_root_tmp[5]);	//記錄權限名稱
					$this->web_root_power_list=$cookie_root_tmp[6];	//記錄權限列表
					$this->web_root_login_code=$cookie_root_tmp[7];	//登入識別碼			
					$this->web_root_login_status=true;
					$this -> data["menu"] = $this->_getMenu();	//載入主選單	
					
					$this->data["web_root_num"]=$this->web_root_num;
					$this->data["web_root_u_id"]=$this->web_root_u_id;
					$this->data["web_root_u_power"]=$this->web_root_u_power;
					$url=$this -> uri -> uri_string();
					@$this->data["breadcrumb"]=$this->_getBreadcrumb($url); //載入麵包穴
					//print_r($this->_getBreadcrumb($url));
				}else{
					$cookie=array('name'=>'login_root',
								  'value'=>'',
								  'expire'	=>time() - 86400,
								  'path'   => '/'
								 );
					$this->input->set_cookie($cookie);			 
					scriptMsg("後端登入錯誤，強制退出",SYSTEM_URL."/Login");
					exit;	
				}
			}
			
		}
	}



	//檢查登入
	function isLogin(){
		$logMsg=NULL;
		if ($this->web_root_login_status){
			if(!$this->chkDesignMode()){
				$sqlStr="select `active`,`adwidth`,`login_code` from `admin` where u_id=? and num=?";
				$parameter=array(':u_id' => $this->web_root_u_id, ':num' => $this->web_root_num);
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if ($row!=NULL){
					if ($row["active"]!="Y"){
						$logMsg='您的帳號已停權，請洽管理者';
						//return false;
					}
					if($this->web_root_login_code!=$row["login_code"]){
						$logMsg='帳號已重其他地方登入';
						//return false;
					}
					if ($row["adwidth"]!=""){
						$this->data["com_adwidth"]=$row["adwidth"];
					}			
					
				}else{
					$logMsg='您的帳號已不存在';
					//return false;
				}
			}
		}else{
			$logMsg='已超過登入時間，請重新登入';
			//return false;
		}
		
		//頁面權限檢查
		if ($this->web_root_u_id!="" && $this->web_root_u_power!="1"){	//排除掉未登入後台和最大管理者
			if ($this->web_root_power_list!=""){
				$sqlStr="select `num` from `item` where `num` in (".$this->web_root_power_list.")";
				$myurl=explode('/',$this -> uri -> uri_string());
				$myurl=$myurl[1].'/'.$myurl[2];//只取3層
				$sqlStr.=" and (`url` like '%".$myurl."%' or `other_url` like '%".$myurl."%')";	 	
				$row=$this->webdb->sqlRow($sqlStr,NULL);
				if($row==NULL){
					$logMsg='很抱歉...您無權檢示頁面';
					//return false;
				}
			}else{
				$logMsg='很抱歉...您無權檢示頁面';
				//return false;
			}
		}elseif ($this->web_root_u_id==""){  //未登入
			$logMsg='您尚未登入或閒置時間太久，請重新登入!';
			//return false;
		}
		if($this->agent->referrer()==NULL && $logMsg!=NULL){	//當無上一頁 可以返回 且權限錯誤(例如直接輸入網址)
			scriptMsg($logMsg,SYSTEM_URL."/Login");
			exit;
		}elseif($logMsg!=NULL){
			return false;
		}else{
			return true;
		}
	}

	function chk_auth($str){	//檢查權限
		$r = false;
		if($this->web_root_u_power == "1"){	//最大管理者
			$r = true;
		} else if ($this->web_root_u_id!=""){	//有登入後台
			if ($this->web_root_power_list!=""){
				$sqlStr="select `num` from `item` where `num` in (".$this->web_root_power_list.") and `url` = '".$str."'"; 	
	  				
				$row=$this->webdb->sqlRow($sqlStr,NULL);
				if($row!=NULL){
					$r = true;
				}
			}
		} 		
		return $r;
	}

	function _getMenu(){	//取得選單
		$menuArr=array();
		if($this->web_root_u_power==1){	//最高權限
			$sqlStr="select * from `item` where `root`=0 and isShow='Y' order by `range` asc";
		}else{//其它權限管理者
			$sqlStr="select * from `admin_group` where u_power=?";
			$sqlStr.=" and power_list is not null and power_list<>''";
			$parameter=array(':u_power' => $this->web_root_u_power);
			$row=$this->webdb->sqlRow($sqlStr,$parameter);		
			if ($row["power_list"]==""){
				$sqlStr="select * from `item` where root=-1";			
			}else{
				$power_list=$row["power_list"];
				$sqlStr="select * from `item` where root=0 and isShow='Y'";
				$sqlStr.=" and num in (".$power_list.") order by `range` asc";	
					
			}		
		}
		$i=0;
		$rowAll=$this->webdb->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$rootMenu=array();
				$rootMenu["num"]=$row["num"];
				$rootMenu["title"]=$row["title"];
				$rootMenu["icon"]=$row["icon"];
				$rootMenu["url"]=$row["url"];
				$rootMenu["target"]=$row["target"];				
				$rootMenu["Child"]=$this->_getChildMenu($row["num"],@$power_list);//子選單
				$menuArr[$i]=$rootMenu;
				$i++;
			}
		}
		return $menuArr;
	}

	function _getChildMenu($root,$power_list){	//目錄子選單
		$menuArr=array();
		if($this->web_root_u_power==1){	//最高權限
			$sqlStr="select * from `item` where `root`=".$root;
			$sqlStr.=" order by `range` asc";
		}else{
			  $sqlStr="select * from `item` where root=".$root;
			  $sqlStr.=" and num in (".$power_list.") order by `range` asc";	
		}
		$i=0;
		$rowAll=$this->webdb->sqlRowList($sqlStr);
		if($rowAll!=NULL){
			foreach($rowAll as $row){				
				$rootMenu=array();
				$rootMenu["num"]=$row["num"];
				$rootMenu["title"]=$row["title"];
				$rootMenu["url"]=$row["url"];
				$rootMenu["isShow"]=$row["isShow"];
				$rootMenu["target"]=$row["target"];
				$rootMenu["Child"]=$this->_getChildMenu($row["num"],@$power_list);
				$menuArr[$i]=$rootMenu;				
				$i++;
			}
		}
		return $menuArr;
	}

	function _getBreadcrumb($url){
		$url=explode('/',$url);
		$newUrl=$url[1].'/'.$url[2];
		$BreadcrumbArr=array();
		$sqlStr="select * from `item` where `url` like '".$newUrl."%' or CONCAT(',',other_url,',') like '%,".$newUrl.",%'";
		$row=$this->webdb->sqlRow($sqlStr);
		if($row!=NULL){
			$numList=$this->_getRootKind($row["num"]);
			$sqlStr2="select * from `item` where num in (".$numList.")";
			$sqlStr2.=" order by `root`";
			$rowAll2=$this->webdb->sqlRowList($sqlStr2);
			if($rowAll2!=NULL){
				$i=0;
				foreach($rowAll2 as $row2){
					$itemArr=array();
					$itemArr["url"]=$row2["url"];
					$itemArr["title"]=$row2["title"];
					$BreadcrumbArr[$i]=$itemArr;
					$i++;
				}
			}
		}
		return $BreadcrumbArr;
	}
	
	
	//取得當前所在頁面名稱
	function getsubtitle(){
		$myurl=explode('/',$this -> uri -> uri_string());
		$myurl=$myurl[1].'/'.$myurl[2];//只取3層
		$sqlStr="select title from `item` where `url` like '%".$myurl."%'";
		$row=$this->webdb->sqlRow($sqlStr);
		if($row!=NULL){
			return $row["title"];	
		}else{
			return NULL;
		}
	}
	
	function _getRootKind($num){
		$sqlStr="select * from `item` where `root` >0 and num=".$num;
		$row=$this->webdb->sqlRow($sqlStr);
		$itemArr=array();
		if($row!=NULL){		
			return $this->_getRootKind($row["root"]).",".$num;
		}else{
			return $num;;
		}
	}

	function _append_js($path) {
		$uri = ASSETS_URL . "/admin/js/" . $path;
		$path = ASSETS_PATH . "/admin/js/" . $path;
		if (file_exists($path) && !in_array($uri, $this -> data["js"])) {
			array_push($this -> data["js"], $uri);
		}
	}


	function _append_js2($path) {
		$uri = ASSETS_URL . "/admin/" . $path;
		$path = ASSETS_PATH . "/admin/" . $path;
		if (file_exists($path) && !in_array($uri, $this -> data["js"])) {
			array_push($this -> data["js"], $uri);
		}
	}


	function _append_css($path) {
		$uri = ASSETS_URL . "/admin/css/" . $path;
		$path = ASSETS_PATH . "/admin/css/" . $path;
		if (file_exists($path) && !in_array($uri, $this -> data["css"])) {
			array_push($this -> data["css"], $uri);
		}
	}

	function _append_css2($path) {
		$uri = ASSETS_URL . "/admin/" . $path;
		$path = ASSETS_PATH . "/admin/" . $path;
		if (file_exists($path) && !in_array($uri, $this -> data["css"])) {
			array_push($this -> data["css"], $uri);
		}
	}


	function _setMsg($msg) {
		$this -> load -> helper('array');
		$msg_header = array('type', 'title', 'content');
		$alert_type = array("success", "info", "warning", "danger");

		if (count($msg) > 0 && $msg != '') {
			$msg = elements($msg_header, $msg, null);

			if (!in_array($msg["type"], $alert_type, true)) {
				$msg["type"] = "info";
			}
			if ($msg["title"] == null) {
				$msg["title"] = "系統提示：";
			}
			if ($msg["content"] == null) {
				$msg["content"] = "";
			}
			$this -> session -> set_flashdata("alert", $msg);
		}	
	}

	function _setMsgAndRedirect($msg, $uri) {
		$this->_setMsg($msg);
		redirect($uri);
		exit;
	}

	function _getMsg() {
		return $this -> session -> flashdata("alert");
	}
	
	//語系設定===============================
	public function languageSet(){
		$nationSet=array();
		$nationSet[0]="繁體中文|TW";  //如為單語系，請至少保留一個，勿全刪
		//$nationSet[1]="简体中文|CN";
		//$nationSet[2]="English|US";
		//$nationSet[3]="日文版|JP";
		return $nationSet;
	}
	
	//是否為多語系
	public function isMultiLanguage(){
		$lang=$this->languageSet();
		if (count($lang)>1){return true;}else{return false;}	
	}

	//建立語系下拉
	public function buildLanguage($nation=NULL){
		$lang=$this->languageSet();
		for ($i=0;$i<count($lang);$i++){
			$nations=explode("|",$lang[$i]);
			echo '<option value="'.$nations[1].'" '.($nations[1]==$nation?'selected="selected"':'').'>'.$nations[0].'</option>';
		}
	}


	//取得預設語系代號
	public function defaultNation(){
		$lang=$this->languageSet();
		$nations=explode("|",$lang[0]);
		return $nations[1];
	}
	
	//取得對應的語系名稱
	public function languageText($value){
		$lang=$this->languageSet();
		for ($i=0;$i<count($lang);$i++){
			$nations=explode("|",$lang[$i]);
			if ($value==$nations[1]){
				return $nations[0];
			}
		}
		return "";	
		
	}

	//延續搜尋條件=========================================================
	function getRequests($key){
		$att="";
		$AryKey=array();
		$a=array();
		$i=0;
		$Ary = $_REQUEST;	
		foreach($Ary as $AryKey[$i]=>$a[$i]){
			  if (strtolower(substr($AryKey[$i],0,strlen($key)))==$key){
				  if ($a[$i]!=""){
					  $att.="&".$AryKey[$i]."=".urlencode($a[$i]);
				  }
			  }
			  $i++;
		}	
		return $att;
	}
	
	//設計師模式===============================
	function chkDesignIP(){  //核對ip
		$ip=$this->input->ip_address();	
		$chk=false;
		if(in_array($ip,$this->designIP)){
			$chk=true;
		}
		return $chk;
	}
	
	function chkDesignMode(){  //檢查狀態是否為設計師模式
		$chk=false;
		if ($this->chkDesignIP()){
			$chk=true;
			if ($this->web_root_login_status){		
				if ($this->web_root_num!="0"){
					$chk=false;
				}
			}
		}
		return $chk;	
	}	
}
