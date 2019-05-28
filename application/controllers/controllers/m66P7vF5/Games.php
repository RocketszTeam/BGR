<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Games extends Core_controller{
	private $picMax=1;  //圖片數量
	private $picDir;
	public function __construct(){
		parent::__construct();
		$this -> data["picMax"]=$this->picMax;
		$this -> data["picDir"]=UPLOADS_PATH . '/games/';	//上傳目錄
		$this->picDir=$this -> data["picDir"];
		
		if (!file_exists($this->picDir)) {
			mkdir($this->picDir, 0777, true);
		}
		
		$this->data["openFind"]="N";//是否啟用搜尋
		
		//定義支援裝置
		$this->data["deviceArr"]=array('1_通用','2_電腦版','3_手機板');
		
	}
	
	public function index(){	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `games` where 1=1";

		//Search 
		$parameter=array();
		$sqlStr="select * from `games` where 1=1";
		//---分類-------------------------------


		if(@$_REQUEST["find2"]!=""){	
			$sqlStr.=" and (`makers_num`=? )";	
			$parameter[":makers_num"]=@$_REQUEST["find2"];

		}


		//---主題-------------------------------
		if(@$_REQUEST["find3"]!=""){
			$sqlStr.=" and `game_name` like ?";
			$parameter[":game_name"]="%".@$_REQUEST["find3"]."%";
		}
	
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["result"] = $rowAll;
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Games/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `range`  LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		if (isset($_REQUEST['find2'])){
		$this -> data["find2"] = $_REQUEST['find2'];}
		if (isset($_REQUEST['find3'])){
		$this -> data["find3"] = $_REQUEST['find3'];}
		if (isset($_REQUEST['per_page'])){
		$this -> data["per_page"] = $_REQUEST['per_page'];}


		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();

		$this->data["editBTN"]=SYSTEM_URL."/Games/edit/";
		$this->data["delBTN"]=SYSTEM_URL."/Games/delete/";
		

		
		//列出遊戲廠商
		$parameter=array();
		$sqlStr="select DISTINCT * from `game_makers`,`games` where `games`.`makers_num`=`game_makers`.`num` group by game_makers.num
";
		$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this->data["openFind"]="Y";//是否啟用搜尋


							
		$this -> data["body"] = $this -> load -> view("admin/game/index", $this -> data,true);   
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
			for ($i=0;$i<$this->picMax;$i++){
				if (@$_FILES["upload"]["size"][$i]>0){ //檢查檔案大小是否大於0	
					//重新命名
					$subName=explode('.',$_FILES["upload"]["name"][$i]);
					$subName=".".end($subName);		
					$newName = "games_".date("Ymdhis")."_".$i.$subName;
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
		
			$sort_sql="UPDATE `games` SET `range`=`range`+1";
			$this->webdb->sqlExc($sort_sql);
		
			$parameter=array();
			$colSql="kind,game_name,device,makers_num,game_code,active,range".$picSql;;
			$sqlStr="INSERT INTO `games` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":kind"]=$this->input->post("kind",true);
			$parameter[":game_name"]=$this->input->post("game_name",true);
			$parameter[":device"]=$this->input->post("device",true);
			$parameter[":makers_num"]=trim($this->input->post("makers_num",true));
			$parameter[":game_code"]=trim($this->input->post("game_code"));
			$parameter[":active"]=$this->input->post("active",true);
			$parameter[":range"]=1;
			for ($i=0;$i<$this->picMax;$i++){
				$parameter[":pic".($i+1)]=$pic[$i];
			}			
			
			if(!$this->webdb->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Games/index");
			}
		}else{
			//撈出遊戲廠商
			$parameter=array();
			$sqlStr="select * from `game_makers`  order by `range`";
			$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
			$this->data["formAction"]=site_url(SYSTEM_URL."/Games/create");
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Games/index");
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("admin/game/form", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}




		$sqlStr="select * from `games` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Games/index");
		}else{		
			if($_POST){	//修改資料
			
				//上傳檔案
				for ($i=0;$i<$this->picMax;$i++){
					if (@$_FILES["upload"]["size"][$i]>0){ //檢查檔案大小是否大於0	
						//重新命名
						$subName=explode('.',$_FILES["upload"]["name"][$i]);
						$subName=".".end($subName);		
						$newName = "games_".date("Ymdhis")."_".$i.$subName;
						//傳檔
						copy($_FILES["upload"]["tmp_name"][$i],$this->picDir.$newName);   //存檔案
						$pic[$i]=$newName;	
					}else{
						$pic[$i]="";
					}										
				}
				
				$parameter=array();
				$colSql="kind,game_name,device,makers_num,game_code,active";
				$sqlStr="UPDATE `games` SET ".sqlUpdateString($colSql);
				$parameter[":kind"]=$this->input->post("kind",true);
				$parameter[":game_name"]=$this->input->post("game_name",true);
				$parameter[":device"]=$this->input->post("device",true);
				$parameter[":makers_num"]=trim($this->input->post("makers_num",true));
				$parameter[":game_code"]=trim($this->input->post("game_code"));
				$parameter[":active"]=$this->input->post("active",true);
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
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Games/index"."?find2=".$_REQUEST['find2']."&find3=". $_REQUEST['find3']."&per_page=". $_REQUEST['per_page']);
				
			}else{
				//撈出遊戲廠商
				$parameter=array();
				$sqlStr="select * from `game_makers` order by `range`";
				$this->data["row_group"]=$this->webdb->sqlRowList($sqlStr,$parameter);
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/Games/edit/".$row["num"]);
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Games/index");
				$this->data["todo"]="edit";
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("admin/game/form", $this -> data,true); 
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
		$sqlStr="select * from `games` where num=?";
		$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Games/index");
		}else{
			//刪除圖片		
			for($i=1;$i<=$this->picMax;$i++){
				if($row["pic".$i]!=""){
					@unlink($this->picDir.$row["pic".$i]);	
				}
			}		
			
			$sqlStr="delete from `games` where num=?";
			$this->webdb->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Games/index");
		}
		
	}
	
	function ajaxKind(){
		if($this->input->is_ajax_request()){
			echo '<option value="">請選擇</option>';
			if($this->input->post("makers_num")){
				$parameter=array();
				$sqlStr="select * from `game_kind` where `makers_num`=? and `root`=0 order by `range`";
				$parameter[':makers_num']=$this->input->post("makers_num",true);
				$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
				if($rowAll!=NULL){
					foreach($rowAll as $row){
						echo '<option value="'.$row["num"].'"'.($this->input->post("kindVal")==$row["num"] ? ' selected' : '').'>'.$row["kind"].'</option>';	
					}
				}
			}
		}
	}
	
	function keyChange(){
		if($this->input->is_ajax_request()){
			$num=trim($this->input->post("num"));//table
			$value=trim($this->input->post("value"));//table
			if($num!='' && $value!=''){
				$parameter=array();
				$sqlStr="UPDATE `games` SET `active`=? where num=?";
				$parameter[":active"]=$value;
				$parameter[":num"]=$num;
				$this->webdb->sqlExc($sqlStr,$parameter);
			}
		}
	}

	

	
} 