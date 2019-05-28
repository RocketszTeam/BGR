<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Site_analytic extends Core_controller{
	//private $picMax=1;  //圖片數量
	//private $picDir;
	public function __construct(){
		parent::__construct();
		
		
		//載入商品設定
		//$this -> data["picDir"]=UPLOADS_PATH . '/banner/';	//上傳目錄
		//$this-> picDir=$this -> data["picDir"];
		
		//if (!file_exists($this->picDir)) {
		//	mkdir($this->picDir, 0777, true);
		//}
		//$this->load->helper('web_config_helper');
		$this->load->model('admin/Site_analytic_model','siteA');
		
		//本週
		$toWeek=reportDate('tw');
		$this->data["toWeek"]=array('d1'=>$toWeek['d1'],'d2'=>$toWeek['d2']);
		//上周
		$yeWeek=reportDate('yw');
		$this->data["yeWeek"]=array('d1'=>$yeWeek['d1'],'d2'=>$yeWeek['d2']);
		//本月
		$toMonth=reportDate('m');
		$this->data["toMonth"]=array('d1'=>$toMonth['d1'],'d2'=>$toMonth['d2']);
		//上月
		$ymMonth=reportDate('ym');
		$this->data["ymMonth"]=array('d1'=>$ymMonth['d1'],'d2'=>$ymMonth['d2']);
		
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	public function index(){
		echo "<!--";
		var_dump($this->web_root_u_id."|".$this->web_root_num."|".$this->web_root_u_power);
		echo "-->";
		//var_dump($this->web_root_num);
		$Cust_array=array(4,5,6);	//股東 總代 代理		
		$kind_web_root_num = kind_sql($this->web_root_num,'admin');
		//var_dump($kind_web_root_num);
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$ptoWeek=reportDate('tw');
		$find7 = (@$_REQUEST["find7"] !="")? @$_REQUEST["find7"] : $ptoWeek['d1']." 00:00:00";
		$find8 = (@$_REQUEST["find8"] !="")? @$_REQUEST["find8"] : $ptoWeek['d2']." 23:59:59";
		//var_dump($find7."|".$find8);
		
	
			$rowOrders = $this->siteA->order_max($find7,$find8,$this->web_root_num,$kind_web_root_num);
			$rowOrders_2 = $this->siteA->member_sell_max($find7,$find8,$this->web_root_num,$kind_web_root_num);
			$rowOrders_3 = $this->siteA->order_sell($find7,$find8,$this->web_root_num,$kind_web_root_num);
			$rowOrders_4 = $this->siteA->game_hit($find7,$find8);//遊戲點擊次數
			$rowOrders_5 = $this->siteA->k168_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//北京賽車
			$rowOrders_5_player =$this->siteA->k168_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//北京賽車玩家人數
			$rowOrders_6 = $this->siteA->super_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//super體育
			$rowOrders_6_player = $this->siteA->super_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//super體育玩家人數
			$rowOrders_ssb = $this->siteA->ssb_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//贏家體育
			$rowOrders_ssb_player = $this->siteA->ssb_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//贏家體育玩家人數
			$rowOrders_7 = $this->siteA->sa_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//沙龍
			$rowOrders_7_player = $this->siteA->sa_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//沙龍玩家人數
			$rowOrders_8 = $this->siteA->dg_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//DG真人
			$rowOrders_8_player = $this->siteA->dg_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//DG真人玩家人數
			$rowOrders_9 = $this->siteA->ab_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//歐博真人
			$rowOrders_9_player = $this->siteA->ab_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//歐博真人玩家人數
			$rowOrders_ameba = $this->siteA->ameba_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//ameba真人
			$rowOrders_ameba_player = $this->siteA->ameba_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//ameba真人玩家人數
			$rowOrders_7pk = $this->siteA->pk_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//7pk真人
			$rowOrders_7pk_player = $this->siteA->pk_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//7pk真人玩家人數
			$rowOrders_qt = $this->siteA->qt_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//qt
			$rowOrders_qt_player = $this->siteA->qt_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//qt玩家人數
			$rowOrders_slottery = $this->siteA->slottery_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//super彩球 slottery
			$rowOrders_slottery_player = $this->siteA->slottery_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//super彩球 slottery玩家人數
			$rowOrders_fish = $this->siteA->fish_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//fish捕魚
			$rowOrders_fish_player = $this->siteA->fish_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//fish捕魚玩家人數
			$rowOrders_bingo = $this->siteA->bingo_win($find7,$find8,$this->web_root_num,$this->web_root_u_power);//bingo
			$rowOrders_bingo_player = $this->siteA->bingo_player_count($find7,$find8,$this->web_root_num,$this->web_root_u_power)['user'];//bingi玩家人數

			
			
			
			//var_dump($rowOrders_qt);
			if(!empty($rowOrders_3)){
				foreach($rowOrders_3 as $key=>$row){
					//$num[$key] = $row['num'];
					//$u_name[$key] = $row['u_name'];
					$sell_percent_all_ok[$key] = $row['sell_percent_all_ok'];//將sell_percent_all_ok集合陣列
				}
				array_multisort($sell_percent_all_ok,SORT_DESC,$rowOrders_3);//sell_percent_all_ok降冪排序，以計算好的值做大小排序，$rowOrders_3就會以$sell_percent_all_ok來做降冪排序
			}
			
			if(!empty($rowOrders_4)){
				foreach($rowOrders_4 as $key=>$row){
					//$num[$key] = $row['num'];
					//$u_name[$key] = $row['u_name'];
					$gameTimes[$key] = $row['gameTimes'];//將sell_percent_all_ok集合陣列
				}
				array_multisort($gameTimes,SORT_DESC,$rowOrders_4);//sell_percent_all_ok降冪排序，以計算好的值做大小排序，$rowOrders_3就會以$sell_percent_all_ok來做降冪排序
			}
			
		
			$gameTimes=array();
			foreach($rowOrders_4 as $row){
				array_push($gameTimes,$row['gameTimes']);
				//$gameTimes .= $row['gameTimes'].",";
			}
		
			$k168_wOrL = array();
			foreach($rowOrders_5 as $row){
				array_push($k168_wOrL,$row['wOrL']);
			}
			
			$k168_win_rate = array();
			foreach($rowOrders_5 as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($k168_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$super_wOrL=array();
			foreach($rowOrders_6 as $row){
				array_push($super_wOrL,$row['wOrL']);
			}
			
			$super_win_rate = array();
			foreach($rowOrders_6 as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($super_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$ssb_wOrL=array();
			foreach($rowOrders_ssb as $row){
				array_push($ssb_wOrL,$row['wOrL']);
			}
			
			$ssb_win_rate = array();
			foreach($rowOrders_ssb as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($ssb_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$sa_wOrL=array();
			foreach($rowOrders_7 as $row){
				array_push($sa_wOrL,$row['wOrL']);
			}
			
			$sa_win_rate = array();
			foreach($rowOrders_7 as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($sa_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$dg_wOrL=array();
			foreach($rowOrders_8 as $row){
				array_push($dg_wOrL,$row['wOrL']);
			}
			
			$dg_win_rate = array();
			foreach($rowOrders_8 as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($dg_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$ab_wOrL=array();
			foreach($rowOrders_9 as $row){
				array_push($ab_wOrL,$row['wOrL']);
			}
	
			$ab_win_rate = array();
			foreach($rowOrders_9 as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($ab_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$ameba_wOrL=array();
			foreach($rowOrders_ameba as $row){
				array_push($ameba_wOrL,$row['wOrL']);
			}
	
			$ameba_win_rate = array();
			foreach($rowOrders_ameba as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($ameba_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			
			$pk_wOrL=array();
			foreach($rowOrders_7pk as $row){
				array_push($pk_wOrL,$row['wOrL']);
			}
	
			$pk_win_rate = array();
			foreach($rowOrders_7pk as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($pk_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			
			$qt_wOrL=array();
			foreach($rowOrders_qt as $row){
				array_push($qt_wOrL,$row['wOrL']);
			}
	
			$qt_win_rate = array();
			foreach($rowOrders_qt as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($qt_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			
			$slottery_wOrL=array();
			foreach($rowOrders_slottery as $row){
				array_push($slottery_wOrL,$row['wOrL']);
			}
	
			$slottery_win_rate = array();
			foreach($rowOrders_slottery as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($slottery_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			
			$fish_wOrL=array();
			foreach($rowOrders_fish as $row){
				array_push($fish_wOrL,$row['wOrL']);
			}
	
			$fish_win_rate = array();
			foreach($rowOrders_fish as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($fish_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$bingo_wOrL=array();
			foreach($rowOrders_bingo as $row){
				array_push($bingo_wOrL,$row['wOrL']);
			}
	
			$bingo_win_rate = array();
			foreach($rowOrders_bingo as $row){
				//$ppp = number_format($row['w_times']/$row['m_count']*100,1)."(".$row['w_times']."/".$row['m_count'].")";
				//var_dump($ppp);
				array_push($bingo_win_rate,number_format($row['w_times']/$row['m_count']*100,0));
			}
			
			$this->data['web_root_num']= $this->web_root_num;
			$this->data['web_root_u_power'] = $this->web_root_u_power;
			$this->data['rowOrders'] = $rowOrders;
			$this->data['rowOrders_2'] = $rowOrders_2;
			$this->data['rowOrders_3'] = $rowOrders_3;
			$this->data['rowOrders_4'] = $rowOrders_4;
			$this->data['rowOrders_5'] = $rowOrders_5;//北京賽車
			$this->data['rowOrders_5_player'] = $rowOrders_5_player;//北京賽車玩家人數
			$this->data['rowOrders_6'] = $rowOrders_6;//super體育
			$this->data['rowOrders_6_player'] = $rowOrders_6_player;//super體育玩家人數
			$this->data['rowOrders_ssb'] = $rowOrders_ssb;//super體育
			$this->data['rowOrders_ssb_player'] = $rowOrders_ssb_player;//super體育玩家人數
			$this->data['rowOrders_7'] = $rowOrders_7;//沙龍
			$this->data['rowOrders_7_player'] = $rowOrders_7_player;//沙龍玩家人數 
			$this->data['rowOrders_8'] = $rowOrders_8;//DG真人
			$this->data['rowOrders_8_player'] = $rowOrders_8_player;//DG真人玩家人數
			$this->data['rowOrders_9'] = $rowOrders_9;//歐博
			$this->data['rowOrders_9_player'] = $rowOrders_9_player;//歐博玩家人數
			$this->data['rowOrders_ameba'] = $rowOrders_ameba;//歐博
			$this->data['rowOrders_ameba_player'] = $rowOrders_ameba_player;//歐博玩家人數
			$this->data['rowOrders_7pk'] = $rowOrders_7pk;//7pk
			$this->data['rowOrders_7pk_player'] = $rowOrders_7pk_player;//7pk
			$this->data['rowOrders_qt'] = $rowOrders_qt;//QT
			$this->data['rowOrders_qt_player'] = $rowOrders_qt_player;//QT玩家人數
			$this->data['rowOrders_slottery'] = $rowOrders_slottery;//slottery
			$this->data['rowOrders_slottery_player'] = $rowOrders_slottery_player;//slottery玩家人數
			$this->data['rowOrders_fish'] = $rowOrders_fish;//捕魚fish
			$this->data['rowOrders_fish_player'] = $rowOrders_fish_player;//捕魚fish玩家人數
			$this->data['rowOrders_bingo'] = $rowOrders_bingo;//bingo
			$this->data['rowOrders_bingo_player'] = $rowOrders_bingo_player;//bingo玩家人數
			$this->data['gameTimes'] = implode(",",$gameTimes);
			$this->data['k168_wOrL'] = implode(',',$k168_wOrL);
			$this->data['k168_win_rate'] = implode(',',$k168_win_rate);
			$this->data['super_wOrL'] = implode(',',$super_wOrL);
			$this->data['super_win_rate'] = implode(',',$super_win_rate);
			$this->data['ssb_wOrL'] = implode(',',$ssb_wOrL);
			$this->data['ssb_win_rate'] = implode(',',$ssb_win_rate);
			$this->data['sa_wOrL'] = implode(',',$sa_wOrL);
			$this->data['sa_win_rate'] = implode(',',$sa_win_rate);
			$this->data['dg_wOrL'] = implode(',',$dg_wOrL);
			$this->data['dg_win_rate'] = implode(',',$dg_win_rate);
			$this->data['ab_wOrL'] = implode(',',$ab_wOrL);
			$this->data['ab_win_rate'] = implode(',',$ab_win_rate);
			$this->data['ameba_wOrL'] = implode(',',$ameba_wOrL);
			$this->data['ameba_win_rate'] = implode(',',$ameba_win_rate);
			$this->data['pk_wOrL'] = implode(',',$pk_wOrL);
			$this->data['pk_win_rate'] = implode(',',$pk_win_rate);
			$this->data['qt_wOrL'] = implode(',',$qt_wOrL);
			$this->data['qt_win_rate'] = implode(',',$qt_win_rate);
			$this->data['slottery_wOrL'] = implode(',',$slottery_wOrL);
			$this->data['slottery_win_rate'] = implode(',',$slottery_win_rate);
			$this->data['fish_wOrL'] = implode(',',$fish_wOrL);
			$this->data['fish_win_rate'] = implode(',',$fish_win_rate);
			$this->data['bingo_wOrL'] = implode(',',$bingo_wOrL);
			$this->data['bingo_win_rate'] = implode(',',$bingo_win_rate);
			
			
		
		
		$this -> data["body"] = $this -> load -> view("admin/site_analytic/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
	}
	

	public function index2(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$this -> data["body"] = $this -> load -> view("admin/site_analytic/index2", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	/*
	function create(){	//新增選項
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($_POST){	//新增資料
			//上傳檔案
			if (@$_FILES["upload"]["size"]>0){ //檢查檔案大小是否大於0	
				//重新命名
				$subName=explode('.',$_FILES["upload"]["name"]);
				$subName=".".end($subName);		
				$newName = "banner_".date("Ymdhis")."_".$subName;
				//傳檔
				copy($_FILES["upload"]["tmp_name"],$this->picDir.$newName);   //存檔案
				$pic=$newName;	
			}else{
				$pic="";
			}
			$parameter=array();
			$colSql="nation,kind,subject,selltime1,selltime2,view,buildtime";
			$colSql.=",url,demo,pic";
			$sqlStr="INSERT INTO `banner` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":nation"]=$this->defaultNation();
			$parameter[":kind"]=$this->input->post("kind");
			$parameter[":subject"]=trim($this->input->post("subject"));
			$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
			$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);
			$parameter[":view"]=($this->input->post("view")=='Y'?'Y':'N');
			$parameter[":buildtime"]=now();
			$parameter[":url"]=trim($this->input->post("url"));
			$parameter[":demo"]=quotes_to_entities(trim($this -> input -> post("demo")));
			$parameter[":pic"]=trim($pic);
			if(!$this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Ad/index");
			}
		}else{
			//撈出區域查詢下拉用
			$parameter=array();
			$sqlStr="select * from `banner_kind` where `root`=0 and `nation`=?";
			$parameter[":nation"]=$this->defaultNation();
			$sqlStr.=" order by `range`";
			$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
			$this->data["formAction"]=site_url(SYSTEM_URL."/Ad/create");
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Ad/index");
			$this->data["todo"]="add";
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
			$this -> data["body"] = $this -> load -> view("admin/ad/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `banner` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Ad/index");
		}else{		
			if($_POST){	//修改資料	
				//上傳檔案
				if (@$_FILES["upload"]["size"]>0){ //檢查檔案大小是否大於0	
					//重新命名
					$subName=explode('.',$_FILES["upload"]["name"]);
					$subName=".".end($subName);		
					$newName = "banner_".date("Ymdhis")."_".$subName;
					//傳檔
					copy($_FILES["upload"]["tmp_name"],$this->picDir.$newName);   //存檔案
					$pic=$newName;	
				}else{
					$pic="";
				}
				
				$parameter=array();
				$colSql="kind,subject,selltime1,selltime2,view";
				$colSql.=",url,demo";
				$sqlStr="UPDATE `banner` SET ".sqlUpdateString($colSql);
				
				$parameter[":kind"]=$this->input->post("kind");
				$parameter[":subject"]=trim($this->input->post("subject"));
				$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
				$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);
				$parameter[":view"]=($this->input->post("view")=='Y'?'Y':'N');
				$parameter[":url"]=trim($this->input->post("url"));
				$parameter[":demo"]=quotes_to_entities(trim($this -> input -> post("demo")));
				
				//有傳圖片或刪除圖片
				if ($pic!="" || @$_POST["delpic"]=="Y"){  
					$sqlStr.=",`pic`=?";
					$parameter[":pic"]=$pic;
					if ($row["pic"]!=""){
						@unlink($this->picDir.$row["pic"]); //執行刪除	
					}				
				}		
									
			
				$sqlStr.=" where num=?";
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Ad/index");
				
			}else{
				//撈出區域查詢下拉用
				$parameter=array();
				$sqlStr="select * from `banner_kind` where `root`=0 and `nation`=?";
				$parameter[":nation"]=$this->defaultNation();
				$sqlStr.=" order by `range`";
				$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/Ad/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Ad/index");
				$this->data["todo"]="edit";
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/ad/form", $this -> data,true); 
				$this -> load -> view("admin/main", $this -> data);	
				
			}
			
		}		
	}
	
	

	
	function delete($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$sqlStr="select * from `banner` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Ad/index");
		}else{
			//刪除圖片		
			@unlink($this->picDir.$row["pic"]);	
			$sqlStr="delete from `banner` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Ad/index");
		}
		
	}
	

	
	//AJAX改變訂單狀態
	function keyChange(){
		if($this->input->is_ajax_request()){
			$num=trim($this->input->post("num"));//table
			$value=trim($this->input->post("value"));//table
			if($num!='' && $value!=''){
				$parameter=array();
				$sqlStr="UPDATE `banner` SET `view`=? where num=?";
				$parameter[":view"]=$value;
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
			}
		}
	}
	*/

	
} 