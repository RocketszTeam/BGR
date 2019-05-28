<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Ad extends Core_controller{
	private $picMax=1;  //圖片數量
	private $picDir;
	public function __construct(){
		parent::__construct();
		
		
		//載入商品設定
		$this -> data["picDir"]=UPLOADS_PATH . '/banner/';	//上傳目錄
		$this-> picDir=$this -> data["picDir"];
		
		if (!file_exists($this->picDir)) {
			mkdir($this->picDir, 0777, true);
		}
		
		
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `banner` where 1=1";
		//---分類-------------------------------
		if(@$_REQUEST["find2"]!=""){	
			$sqlStr.=" and (`kind`=? or `area`=?)";	
			$parameter[":kind"]=@$_REQUEST["find2"];
			$parameter[":area"]=@$_REQUEST["find2"];
		}
		//---主題-------------------------------
		if(@$_REQUEST["find3"]!=""){
			$sqlStr.=" and `subject` like ?";
			$parameter[":subject"]="%".@$_REQUEST["find3"]."%";
		}
		//---狀態-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `view`=?";
			$parameter[":view"]=@$_REQUEST["find4"];
		}
		//開放時間
		if(!empty($_REQUEST['find5'])){
			$sqlStr.=" and `selltime1` >=?";
			$parameter[":selltime1"]=@$_REQUEST["find5"];
		}
		if(!empty($_REQUEST['find6'])){			
			$sqlStr.=" and `selltime2` <=?";
			$parameter[":selltime2"]=@$_REQUEST["find6"];
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Ad/index?".$this->data["att"];//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=0;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();


		//撈出分類查詢下拉用
		$parameter=array();
		$sqlStr="select * from `banner_kind` where `root`=0 and `nation`=?";
		$parameter[":nation"]=$this->defaultNation();
		$sqlStr.=" order by `range`";
		$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this->data["editBTN"]=SYSTEM_URL."/Ad/edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Ad/delete/";
							
		$this -> data["body"] = $this -> load -> view("admin/ad/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
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
	

	
} 