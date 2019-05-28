<?php
defined('BASEPATH') OR exit('No direct script access allowed');
abstract class Core_controller extends CI_Controller {
	protected $data;
	protected $nation;
	public function __construct() {
		parent::__construct();
			
		//設定時區+8h
		date_default_timezone_set("Asia/Taipei");	
		$this->data["att"]="";
		$this -> data["js"] = array();
		$this -> data["css"] = array();
		//載入公司資訊
		$sqlStr="select * from company where num=1";
		$web_info=$this->webdb->sqlRow($sqlStr);
		if($web_info!=NULL){
			foreach($web_info as $k=>$v){	
				$this->data[$k]=$v;
			}
		}
		
		/*
		$a=strtotime('2017-07-17 10:30:00');
		$b=strtotime(now());
		$c=array('122.117.20.37','59.126.18.8','172.104.88.144');
		$u_ip=$this->input->ip_address();
		
		if(!in_array($u_ip,$c)){
			if($b >=$a){die('系統維護中');}	
		}
		*/
		
		//設定系統訊息
		$this->data["alertMsg"]=$this->_getMsg();
		
		//加入會員成功訊息
		$this->data["joinMsg"]=$this -> session -> flashdata("joinMsg");
		
		
		//區別站別
		$this->data["Web_CustNum"]= 5;	//預設為主站
		$Web_Host=$this->input->server('HTTP_HOST',true);	//取得網域名稱
		$HostArray=explode('.',$Web_Host);
		if(count($HostArray) < 3){	//無法正確解析的網址 一律是別為主站
			$this->data["Web_CustNum"]=	5;
		}elseif($HostArray[0]=='www'){	//如果網址帶有www為主站
			$this->data["Web_CustNum"]=	5;
		}else{
			$sqlStr="select num from `admin` where `u_power`=6 and `root` > 0 and `u_id`=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':u_id'=>$HostArray[0]));
			if($row!=NULL){
				$this->data["Web_CustNum"]=$row["num"];
			}
		}

		
		//針對網域名稱判斷
		$parameter=array();
		$sqlStr="select num from `admin` where `u_power`=6 and `root` > 0 and CONCAT(',',`cust_url`,',') like ?";
		$parameter[':cust_url']="%,".$Web_Host.",%";
		$row=$this->webdb->sqlRow($sqlStr,$parameter);
		if($row!=NULL){
			$this->data["Web_CustNum"]=$row["num"];
		}else{
			$this->data["Web_CustNum"]=	5;	//找不到一律 為主站
		}
	
		
		$this->load->library('memberclass');	//載入會員函式庫
		//判斷會員登入
		if($this->memberclass->getMemLogin()){
			//清除登入用驗證碼session~~需要用的時候 在重新宣告
			$this->session->unset_userdata('code_token');
			
			$this->data["isLogin"]=true;
			
			//取出會員基本資料
			$user_info=array();
			$user_info['num']=$this->memberclass->num();	//會員編號
			$user_info['u_id']=$this->memberclass->u_id();	//會員帳號
			$user_info['u_name']=$this->memberclass->u_name();	//會員姓名
			$user_info['agent']=tb_sql('admin_num','member',$this->memberclass->num());	//會員上層代理
			$user_info['m_group']=tb_sql('m_group','member',$this->memberclass->num());	//會員群組
			$user_info['WalletTotal']=$this->memberclass->getWalletTotal($user_info['num']);	//會員錢包點數

            //會員付款設定
            $this->data[ "pay_mode" ] = 99;
            $sqlStr="select DISTINCT `paymentType` from `payment_config` where `m_group` =".$user_info['m_group']." AND `enable` =1";
            $this->data["pay_config"]=$this->webdb->sqlRowList($sqlStr);

			//會員銀行帳戶
			$sqlStr="select * from `member_bank` where `mem_num`=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
			if($row!=NULL){
				$user_info['bank_num']=$row["bank_num"];	//銀行系統代碼
				$user_info['bank_name']=tb_sql("bank_code","bank_list",$row["bank_num"]).tb_sql("bank_name","bank_list",$row["bank_num"]);	//銀行名稱
				$user_info['bank_branch']=$row['bank_branch'];	//分行名稱
				$user_info['bank_account']=$row["bank_account"];	//銀行帳號
				$user_info['account_name']=$row["account_name"];	//銀行戶名
			}
			$this->data["user_info"]=$user_info;
			
			
		}else{
			$this->data["isLogin"]=false;
			//登入用驗證碼
			$this->data["token"] = md5(uniqid(rand(), true));
			$this->session->set_userdata('code_token', $this->data["token"]);
			
		}

		// $this->CI = &get_instance();
		// $controller = $this->CI->router->fetch_class();
		// $action = $this->CI->router->fetch_method();
		// $uri = "{$controller}/{$action}";
		// if (
		// 		$this->CI->agent->is_mobile() && // is mobile?
		// 		!$this->data["isLogin"] && // is login?
		// 		!in_array($uri, [ // 不擋的功能
		// 				'Login/index', //登入頁面
		// 				'Index/ajax_login', // 登入ajax
		// 				'Manger/register', // 註冊會員頁面
		// 				'Manger/register_do', // 處理註冊會員 ajax
		// 				'Forget/index', // 忘記密碼頁面
		// 		        'Forget/forget_do', // 忘記密碼ajax
		// 				'Manger/ajax_chkid',//帳號檢查
		// 				'Manger/ajax_phonecode',//驗證碼發送
		// 				'Vcode2/index',//驗證碼
		// 				'Index/index'//首頁
		// 		])

		// ) {
		// 	redirect('/Login');
		// }

		
		$this->data["notIndex"]='Y';

		//英雄榜
		$sqlStr="select * from `heroes` where 1=1".time_sql()." order by `range`,num DESC";
		$this->data["HeroeList"]=$this->webdb->sqlRowList($sqlStr);

		//撈出最新消息
		$sqlStr="select * from `news` where  1=1".time_sql()." order by buildtime DESC,num DESC";
		$this->data["newsList"]=$this->webdb->sqlRowList($sqlStr);
		
		
		//輪播
		$this->load->library('banner');
		$this->data["bannerList"]=$this->banner->banner_show(1);

		// get remember id/pwd
		$this->data['cookie_id'] = $this->input->cookie('login_u_id');
		$this->data['cookie_pwd'] = $this->input->cookie('login_u_password');
		$this->data['is_login_remember'] = $this->input->cookie('login_remember');
	}


	function _append_js($path) {	//前台載入JS用
		$uri = ASSETS_URL . "/www/js/" . $path;
		$path = ASSETS_PATH . "/www/js/" . $path;
		if (file_exists($path) && !in_array($uri, $this -> data["js"])) {
			array_push($this -> data["js"], $uri);
		}
	}

	function _append_js2($path) {
		$uri = ASSETS_URL . "/www/" . $path;
		$path = ASSETS_PATH . "/www/" . $path;
		if (file_exists($path) && !in_array($uri, $this -> data["js"])) {
			array_push($this -> data["js"], $uri);
		}
	}


	function _append_css($path) {
		$uri = ASSETS_URL . "/www/css/" . $path;
		$path = ASSETS_PATH . "/www/css/" . $path;
		if (file_exists($path) && !in_array($uri, $this -> data["css"])) {
			array_push($this -> data["css"], $uri);
		}
	}
	
	function _append_css2($path) {
		$uri = ASSETS_URL . "/www/" . $path;
		$path = ASSETS_PATH . "/www/" . $path;
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

	function _setMsgAndRedirect($msg, $uri = "sysAdmin/index") {
		$this->_setMsg($msg);
		redirect($uri);
	}

	function _getMsg() {
		return $this -> session -> flashdata("alertMsg");
	}
	
}
