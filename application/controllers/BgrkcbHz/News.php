<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class News extends Core_controller{
	private $picMax=0;  //圖片數量
	private $picDir;
	public function __construct(){
		parent::__construct();
		
		
		$this -> data["picMax"]=$this->picMax;
		$this -> data["picDir"]=UPLOADS_PATH . '/news/';	//上傳目錄
		$this->picDir=$this -> data["picDir"];
		
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
		$sqlStr="select * from `news` where 1=1";
		//---分類-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `kind`=?";	
			$parameter[":kind"]=@$_REQUEST["find1"];
		}
		//---主題-------------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `subject` like ?";
			$parameter[":subject"]="%".@$_REQUEST["find2"]."%";
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/News/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr="select * from `news_kind` where `root`=0 and `nation`=?";
		$parameter[":nation"]=$this->defaultNation();
		$sqlStr.=" order by `range`";
		$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		
		$this->data["editBTN"]=SYSTEM_URL."/News/edit/";
		$this->data["delBTN"]=SYSTEM_URL."/News/delete/";
							
		$this -> data["body"] = $this -> load -> view("admin/news/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	function create(){	//新增選項
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		//文字編輯器====================================
		$this->_append_js('ckeditor/ckeditor.js');
		//============================================
		if($_POST){	//新增資料
		
			//上傳檔案
			for ($i=0;$i<$this->picMax;$i++){
				if (@$_FILES["upload"]["size"][$i]>0){ //檢查檔案大小是否大於0	
					//重新命名
					$subName=explode('.',$_FILES["upload"]["name"][$i]);
					$subName=".".end($subName);		
					$newName = "news_".date("Ymdhis")."_".$i.$subName;
					//傳檔
					copy($_FILES["upload"]["tmp_name"][$i],$this->picDir.$newName);   //存檔案
					$pic[$i]=$newName;	
				}else{
					$pic[$i]="";
				}
			}
		
		
			$picSql="";
			for ($i=0;$i<$this->picMax;$i++){
				$picSql.=",pic".($i+1);
			}
		
		
			$parameter=array();
			$colSql="kind,subject,word,selltime1,selltime2,buildtime,nation,reg_time".$picSql;;
			$sqlStr="INSERT INTO `news` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":kind"]=$this->input->post("kind",true);
			$parameter[":subject"]=trim($this->input->post("subject",true));
			$parameter[":word"]=trim($this->input->post("word",false));
			$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
			$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);
			$parameter[":buildtime"]=$this->input->post("buildtime",true);
			$parameter[":nation"]=$this->defaultNation();
			$parameter[":reg_time"]=now();
			for ($i=0;$i<$this->picMax;$i++){
				$parameter[":pic".($i+1)]=$pic[$i];
			}			
			
			if(!$this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/News/index");
			}
		}else{
			//撈出分類查詢下拉用
			$parameter=array();
			$sqlStr="select * from `news_kind` where `root`=0 and `nation`=?";
			$parameter[":nation"]=$this->defaultNation();
			$sqlStr.=" order by `range`";
			$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
			$this->data["formAction"]=site_url(SYSTEM_URL."/News/create");
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/News/index");
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/news/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		//文字編輯器====================================
		$this->_append_js('ckeditor/ckeditor.js');
		//============================================
		$sqlStr="select * from `news` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/News/index");
		}else{		
			if($_POST){	//修改資料
			
				//上傳檔案
				for ($i=0;$i<$this->picMax;$i++){
					if (@$_FILES["upload"]["size"][$i]>0){ //檢查檔案大小是否大於0	
						//重新命名
						$subName=explode('.',$_FILES["upload"]["name"][$i]);
						$subName=".".end($subName);		
						$newName = "news_".date("Ymdhis")."_".$i.$subName;
						//傳檔
						copy($_FILES["upload"]["tmp_name"][$i],$this->picDir.$newName);   //存檔案
						$pic[$i]=$newName;	
					}else{
						$pic[$i]="";
					}										
				}
				
				$parameter=array();
				$colSql="kind,subject,word,selltime1,selltime2,buildtime,nation,reg_time";
				$sqlStr="UPDATE `news` SET ".sqlUpdateString($colSql);
				
				$parameter[":kind"]=$this->input->post("kind",true);
				$parameter[":subject"]=trim($this->input->post("subject",true));
				$parameter[":word"]=trim($this->input->post("word",false));
				$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
				$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);
				$parameter[":buildtime"]=$this->input->post("buildtime",true);
				$parameter[":nation"]=$this->input->post("nation",true);
				$parameter[":reg_time"]=now();
				for ($i=0;$i<$this->picMax;$i++){
					//有傳圖片或刪除圖片
					if ($pic[$i]!="" || @$_POST["delpic".$i]=="Y"){  
						$sqlStr.=",`pic".($i+1)."`=?";
						$parameter[":pic".($i+1)]=$pic[$i];
						if ($row["pic".($i+1)]!=""){
							@unlink($this->picDir.$row["pic".($i+1)]); //執行刪除	
						}				
					}		
				}					
				$sqlStr.=" where num=?";
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/News/index");
				
			}else{
				//撈出分類查詢下拉用
				$parameter=array();
				$sqlStr="select * from `news_kind` where `root`=0 and `nation`=?";
				$parameter[":nation"]=$this->defaultNation();
				$sqlStr.=" order by `range`";
				$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/News/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/News/index");
				$this->data["todo"]="edit";
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/news/form", $this -> data,true); 
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
		$sqlStr="select * from `news` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/News/index");
		}else{
			//刪除圖片		
			for($i=1;$i<=$this->picMax;$i++){
				if($row["pic".$i]!=""){
					@unlink($this->picDir.$row["pic".$i]);	
				}
			}		
			$sqlStr="delete from `news` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/News/index");
		}
		
	}
	

	

	
} 