<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Company extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->data["openFind"]="N";//是否啟用搜尋
		$this->data["member_group"]=array('0_一般會員','1_黃金會員','2_白金會員','3_藍鑽會員');
		//放點與扣點設定
		$point_sqlStr = "select * from wallet_kind where root=0 and num not in ('3','4','5','9','10','11','12')";
		$point_sqlStr_row = $this->webdb->sqlRowList($point_sqlStr);
		$this->data['point_sqlStr_row'] = $point_sqlStr_row;
	}
	
	public function index(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		//網站設定
		$parameter=array();
		$sqlStr="select * from `company` where num=1";
		$row=$this->webdb->sqlRow($sqlStr,$parameter);
		
		//金流設定
		$sqlStr="select * from `member_pay_mode`";
		$this->data['rowPay']=$this->webdb->sqlRowList($sqlStr);
		
		if($row==NULL){	
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());				
		}else{
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="com_name,com_title,com_keywords,com_description,point_percent,rake_mode,rake_winLimit";
				$sqlStr="UPDATE `company` SET ".sqlUpdateString($colSql);	
				$parameter[":com_name"]=$this->input->post("com_name",true);
				$parameter[":com_title"]=$this->input->post("com_title",true);
				$parameter[":com_keywords"]=$this->input->post("com_keywords",true);
				$parameter[":com_description"]=$this->input->post("com_description",true);
				$parameter[":point_percent"]=$this->input->post("point_percent") ? $this->input->post("point_percent",true) : 50;	//上繳最少50%
				$parameter[":rake_mode"]=$this->input->post("rake_mode");
				$parameter[":rake_winLimit"]=$this->input->post("rake_winLimit",true);	
				$sqlStr.=" where num=1";
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Company/index");
				
			}else{
				$this->data["formAction"]=site_url(SYSTEM_URL."/Company/index");
				$this->data["payAction"]=site_url(SYSTEM_URL."/Company/setPayMode");
				$this->data["wallet_kind_point"]=site_url(SYSTEM_URL."/Company/wallet_kind_point");
				$this->data["subtitle"]=$this->getsubtitle();
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/company/index", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
		}		
	}

	public function setPayMode(){	//金流設定
		if($_POST){	//修改資料
			foreach($this->data["member_group"] as $keys=>$value){
				/*
				if($this->input->post("pay_mode".$keys)==3){
					$msg = array("type" => "danger", 'title' => '修改失敗！','content'=>'紅陽目前處於測試階段不開放');
					$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Company/index");
					exit;
				}
				*/
				$parameter=array();	
				$colSql="pay_mode,update_admin,update_time";
				$sqlStr="UPDATE `member_pay_mode` SET ".sqlUpdateString($colSql);
				$parameter[":pay_mode"]=$this->input->post("pay_mode".$keys,true);
				$parameter[":update_admin"]=$this->web_root_num;
				$parameter[":update_time"]=now();
				$sqlStr.=" where m_group=".$keys;
				$this->webdb->sqlExc($sqlStr,$parameter);
			}
			$msg = array("type" => "success", 'title' => '修改成功！');
			$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Company/index");
		}
		
	}
	
	
	//放扣點設定
	public function put_and_back(){
		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		};
		
		$this->data["wallet_kind_point"]=site_url(SYSTEM_URL."/Company/wallet_kind_point");
		$this -> data["body"] = $this -> load -> view("admin/company/put_and_back", $this -> data,true); 
		$this -> load -> view("admin/main", $this -> data);	

		
	}

	
	//放點扣點名稱類別新增
	public function put_and_back_create(){
		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		};
		
		if($_POST){
			//var_dump($_POST);exit;
			$parameter=array();
			$colSql="kind,type,active,point";
			$sqlStr="INSERT INTO `wallet_kind` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":kind"]=$this->input->post("kind",true);
			$parameter[":type"]=$this->input->post("type",true);
			$parameter[":active"]=$this->input->post("active",true);
			$parameter[":point"]=$this->input->post("point",true);
			$this->webdb->sqlExc($sqlStr,$parameter);
			$msg = array("type" => "success", 'title' => '新增成功！');
			$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Company/put_and_back");
		}else{
		
			$this->data["formAction"]=site_url(SYSTEM_URL."/company/put_and_back_create/");
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/company/put_and_back");
			$this -> data["body"] = $this -> load -> view("admin/company/put_and_back_create", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
			
		}
		
	}
	
	
	
	
	//放點扣點修改
	public function wallet_kind_point(){ 
		
		foreach($this->data['point_sqlStr_row'] as $key=>$value){
			if($this->input->post("wallet_kind".$key,true) == "2"){ //若是關閉的話則 point 為0，表示無限制
				$parameter=array();	
				$sqlStr="UPDATE `wallet_kind` SET active='".$this->input->post("wallet_kind".$key,true)."',point=0 where num='".$this->input->post("num".$key,true)."'";
				$this->webdb->sqlExc($sqlStr);
			}else{
				$parameter=array();	//若是啟動的話 
				$sqlStr="UPDATE `wallet_kind` SET active='".$this->input->post("wallet_kind".$key,true)."',point='".$this->input->post("wallet_kind_point".$key,true)."' where num='".$this->input->post("num".$key,true)."'";
				$this->webdb->sqlExc($sqlStr);
			}
		}
		$msg = array("type" => "success", 'title' => '修改成功！');
		$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Company/put_and_back");
		
	}
	


	//賓果限額設定
	public function SetLimit(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->load->library('api/bingoapi');	//賓果
		//$this->data["SetLimit"];
		$this->data["SetLimit"]=$this->bingoapi->getLimit();
		if($_POST){	//修改資料
			$logMsg=$this->bingoapi->setLimit($this->input->post());
			if($logMsg==NULL){
				$msg = array("type" => "success", 'title' => '設定完成！', 'content' => '');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "danger", 'title' => '修改失敗！','content'=>$logMsg);
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			$this->data["formAction"]=site_url(SYSTEM_URL."/Company/SetLimit");
			$this->data["subtitle"]=$this->getsubtitle();
			//$this->data["row"]=$row;	
			$this -> data["body"] = $this -> load -> view("admin/company/SetLimit", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	//賓果賠率設定
	public function SetOdds(){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->load->library('api/bingoapi');	//賓果
		$this->data["SetOdds"]=$this->bingoapi->getOdds();
		if($_POST){	//修改資料
			$logMsg=$this->bingoapi->setOdds($this->input->post());
			if($logMsg==NULL){
				$msg = array("type" => "success", 'title' => '設定完成！', 'content' => '');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "danger", 'title' => '修改失敗！','content'=>$logMsg);
				$this -> _setMsgAndRedirect($msg, current_url());
			}
		}else{
			$this->data["fiveArray"]=array(1=>'金',2=>'木',3=>'水',4=>'火',5=>'土');
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Company/SetOdds");
			$this->data["subtitle"]=$this->getsubtitle();
			//$this->data["row"]=$row;	
			$this -> data["body"] = $this -> load -> view("admin/company/SetOdds", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
	}

	
} 