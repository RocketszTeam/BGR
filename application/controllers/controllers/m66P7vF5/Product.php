<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Product extends Core_controller{

	private $prokind2Options=array("A_一般商品","B_暢銷商品","C_最新商品","D_特價商品");   //類別
	private $proSellOptions=array("B_銷售","S_展示","D_下架");   //狀態
	private $picMax=3;  //圖片數量
	private $picDir;
	private $kindLevel=3;	//分類層數
	public function __construct(){
		parent::__construct();
		
		$this -> load -> model("sysAdmin/product_model", "product");
		
		//載入商品設定
		$this -> data["prokind2Options"]=$this->prokind2Options;		
		$this -> data["proSellOptions"]=$this->proSellOptions;
		$this -> data["picMax"]=$this->picMax;
		$this -> data["picDir"]=UPLOADS_PATH . '/product/';	//上傳目錄
		$this->picDir=$this -> data["picDir"];
		
		if (!file_exists($this->picDir)) {
			mkdir($this->picDir, 0777, true);
		}
		
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	public function index(){		
		$this->isLogin();//檢查登入狀態
		$parameter=array();
		$sqlStr="select * from [myTable] where 1=1";
		//---語系-------------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `nation`=?";	
			$parameter[":nation"]=@$_REQUEST["find1"];
		}
		//---分類-------------------------------
		if(@$_REQUEST["find2"]!=""){
			//die($this->my_config->kind_sql(@$_REQUEST["find2"],"pro_kind"));	
			$sqlStr.=" and `pro_kind` in (?".$this->my_config->kind_sql(@$_REQUEST["find2"],"pro_kind").")";	
			$parameter[":kind"]=@$_REQUEST["find2"];
		}
		//---商品型號-------------------------------
		if(@$_REQUEST["find3"]!=""){
			$sqlStr.=" and `pro_num` like ?";
			$parameter[":pro_num"]="%".@$_REQUEST["find3"]."%";
		}
		//---商品名稱-------------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `pro_name` like ?";
			$parameter[":pro_name"]="%".@$_REQUEST["find4"]."%";
		}
		//---商品類別-------------------------------
		if(@$_REQUEST["find5"]!=""){
			$sqlStr.=" and `pro_kind2`=?";
			$parameter[":pro_kind2"]=@$_REQUEST["find5"];
		}
		//---商品狀態-------------------------------
		if(@$_REQUEST["find6"]!=""){
			$sqlStr.=" and `pro_sell`=?";
			$parameter[":pro_sell"]=@$_REQUEST["find6"];
		}	
		//日期區間
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `buildtime`>=?";
			$parameter[":buildtime"]=@$_REQUEST["find7"];
		}
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `buildtime`<=?";
			$parameter[":buildtime"]=@$_REQUEST["find8"];
		}
		//除了最高權限 能看到所有...否則只抓該使用者的分類
		if($this->web_root_u_power > 1){	
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		
		$total=$this->product->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL),"product"); //總筆數
		//分頁相關
		$config['base_url'] = base_url()."index.php/sysAdmin/product/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `range` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->admin->sqlRowList($sqlStr,$parameter,"product");		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();


		//撈出分類查詢下拉用
		$parameter=array();
		$sqlStr="select * from [myTable] where `root`=0 and `nation`=?";
		$parameter[":nation"]=$this->defaultNation();
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$sqlStr.=" order by `range`";
		$this->data["row_group"]=$this->product->sqlRowList($sqlStr,$parameter,"pro_kind");
		
							
		$this -> data["body"] = $this -> load -> view("sysAdmin/product/index", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
	
	
	function create(){	//新增選項
		$this->isLogin();//檢查登入狀態
		//文字編輯器====================================
		$this->_append_js('ckeditor/ckeditor.js');
		//============================================
		if($_POST){	//新增資料
		
			//上傳檔案
			for ($i=0;$i<$this->picMax;$i++){
				if (@$_FILES["upload"]["size"][$i]>0){ //檢查檔案大小是否大於0	
					//重新命名
					$subName=".".end(explode('.',$_FILES["upload"]["name"][$i]));		
					$newName = "pro_".date("Ymdhis")."_".$i.$subName;
					//傳檔
					copy($_FILES["upload"]["tmp_name"][$i],$this->picDir.$newName);   //存檔案
					$pic[$i]=$newName;	
				}else{
					$pic[$i]="";
				}
			}
		
			//更新排序
			sqlSortAdd("product",$this->input->post("nation",true));
		
			$picSql="";
			for ($i=0;$i<$this->picMax;$i++){
				$picSql.=",pic".($i+1);
			}
		
			$parameter=array();
			$colSql="nation,pro_kind,pro_num,pro_name,price1,price2,price3";
			$colSql.=",pro_kind2,demo,selltime1,selltime2,pro_sell,word";
			$colSql.=",pro_other,buy_kind1,buy_kind2,range,reg_time,admin_num".$picSql;
			$sqlStr="INSERT INTO [myTable] (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":nation"]=$this->input->post("nation",true);
			$parameter[":pro_kind"]=trim($this->input->post("pro_kind",true));
			$parameter[":pro_num"]=trim($this->input->post("pro_num",true));
			$parameter[":pro_name"]=trim($this->input->post("pro_name",true));
			$parameter[":price1"]=($this -> input -> post("price1") ? trim($this -> input -> post("price1")) : NULL);
			$parameter[":price2"]=($this -> input -> post("price2") ? trim($this -> input -> post("price2")) : NULL);
			$parameter[":price3"]=($this -> input -> post("price3") ? trim($this -> input -> post("price3")) : NULL);
			$parameter[":pro_kind2"]=trim($this->input->post("pro_kind2",true));
			$parameter[":demo"]=trim($this->input->post("demo",true));			
			$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
			$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);			
			$parameter[":pro_sell"]=trim($this->input->post("pro_sell",true));
			$parameter[":word"]=trim($this->input->post("word"));
			$parameter[":pro_other"]=trim($this->input->post("pro_other",true));
			$parameter[":buy_kind1"]=trim($this->input->post("buy_kind1",true));
			$parameter[":buy_kind2"]=trim($this->input->post("buy_kind2",true));
			$parameter[":range"]=1;		
			$parameter[":reg_time"]=now();
			$parameter[":admin_num"]=($this->web_root_u_power > 1 ? $this->web_root_num : 0);
			for ($i=0;$i<$this->picMax;$i++){
				$parameter[":pic".($i+1)]=$pic[$i];
			}			
			
			if(!$this->product->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, "sysAdmin/product/index");
			}
		}else{
			//撈出分類查詢下拉用
			
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
			
			$this->data["formAction"]=site_url("sysAdmin/product/create");
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("sysAdmin/product/form", $this -> data,true); 
			$this -> load -> view("sysAdmin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num){
		$this->isLogin();//檢查登入狀態
		//文字編輯器====================================
		$this->_append_js('ckeditor/ckeditor.js');
		//============================================
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->product->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/product/index");
		}else{		
			if($_POST){	//修改資料
			
				//上傳檔案
				for ($i=0;$i<$this->picMax;$i++){
					if (@$_FILES["upload"]["size"][$i]>0){ //檢查檔案大小是否大於0	
						//重新命名
						$subName=".".end(explode('.',$_FILES["upload"]["name"][$i]));		
						$newName = "pro_".date("Ymdhis")."_".$i.$subName;
						//傳檔
						copy($_FILES["upload"]["tmp_name"][$i],$this->picDir.$newName);   //存檔案
						$pic[$i]=$newName;	
					}else{
						$pic[$i]="";
					}										
				}
			
				
				$parameter=array();
				$colSql="nation,pro_kind,pro_num,pro_name,price1,price2,price3";
				$colSql.=",pro_kind2,demo,selltime1,selltime2,pro_sell";
				$colSql.=",word,pro_other,buy_kind1,buy_kind2";
				$sqlStr="UPDATE [myTable] SET ".sqlUpdateString($colSql);				
				$parameter[":nation"]=$this->input->post("nation",true);
				$parameter[":pro_kind"]=trim($this->input->post("pro_kind",true));
				$parameter[":pro_num"]=trim($this->input->post("pro_num",true));
				$parameter[":pro_name"]=trim($this->input->post("pro_name",true));
				$parameter[":price1"]=($this -> input -> post("price1") ? trim($this -> input -> post("price1")) : NULL);
				$parameter[":price2"]=($this -> input -> post("price2") ? trim($this -> input -> post("price2")) : NULL);
				$parameter[":price3"]=($this -> input -> post("price3") ? trim($this -> input -> post("price3")) : NULL);
				$parameter[":pro_kind2"]=trim($this->input->post("pro_kind2",true));
				$parameter[":demo"]=trim($this->input->post("demo",true));			
				$parameter[":selltime1"]=($this -> input -> post("selltime1") ? trim($this -> input -> post("selltime1")) : NULL);
				$parameter[":selltime2"]=($this -> input -> post("selltime2") ? trim($this -> input -> post("selltime2")) : NULL);			
				$parameter[":pro_sell"]=trim($this->input->post("pro_sell",true));
				$parameter[":word"]=trim($this->input->post("word"));
				$parameter[":pro_other"]=trim($this->input->post("pro_other",true));
				$parameter[":buy_kind1"]=trim($this->input->post("buy_kind1",true));
				$parameter[":buy_kind2"]=trim($this->input->post("buy_kind2",true));				
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
				
				
				//die($sqlStr);
				
				$this->product->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, "sysAdmin/product/index");
				
			}else{
				//撈出分類查詢下拉用
				$parameter=array();
				$sqlStr="select * from [myTable] where `root`=0 and `nation`=?";
				$parameter[":nation"]=$this->defaultNation();
				if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
					$sqlStr.=" and `admin_num`=?";
					$parameter[":admin_num"]=$this->web_root_num;
				}
				$sqlStr.=" order by `range`";
				$this->data["row_group"]=$this->product->sqlRowList($sqlStr,$parameter,"pro_kind");
							
				$this->data["formAction"]=site_url("sysAdmin/product/edit/".$row["num"]);
				$this->data["todo"]="edit";
				//$this->data["subtitle"] = '商品資料修改';
				$this->data["subtitle"]=$this->getsubtitle();
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("sysAdmin/product/form", $this -> data,true); 
				$this -> load -> view("sysAdmin/main", $this -> data);	
				
			}
			
		}		
	}
	
	
	
	function delete($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->product->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/product/index");
		}else{
			//刪除圖片		
			for($i=1;$i<=$this->picMax;$i++){
				if($row["pic".$i]!=""){
					@unlink($this->picDir.$row["pic".$i]);	
				}
			}		
			$sqlStr="delete from [myTable] where num=?";
			$this->product->sqlExc($sqlStr,array(':num'=>$num),"product");
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/product/index");
		}
		
	}
	
	//AJAX改變訂單狀態
	function keyChange(){
		if($this->input->is_ajax_request()){
			$num=trim($this->input->post("num"));//table
			$value=trim($this->input->post("value"));//table
			if($num!='' && $value!=''){
				$parameter=array();
				$sqlStr="UPDATE [myTable] SET `pro_sell`=? where num=?";
				$parameter[":pro_sell"]=$value;
				$parameter[":num"]=$num;
				$this->admin->sqlExc($sqlStr,$parameter,"product");
			}
		}
	}
	


	//相關商品
	function other(){
		if($this->input->is_ajax_request()){
			if($this -> input -> post('pro_kind')){
				$pro_kind = $this -> input -> post('pro_kind');
				$pro_other=$this -> input -> post('pro_other');
				$self=$this -> input -> post('self');
				$customValue=$this -> input -> post('customValue');
				$divID=$this -> input -> post('divID');
				$hiddenID=$this -> input -> post('hiddenID');
				$num=array();
				if (@$pro_other!=""){
					$num=explode(",",@$pro_other);
				}
				if ($customValue==1 || $customValue==2){	//根據要撈出的語法而定
					//抓取資料
					$kinds=explode(',',$pro_kind.$this->my_config->kind_sql($pro_kind,"pro_kind"));
					$parameter=array();
					$sqlStr="select * from [myTable] where `num`!=? and `pro_kind` in ?";
					if($customValue==2){
						$sqlStr.=" and `price3` >=0";
					}
					$parameter[":num"]=$self;
					$parameter[":pro_kind"]=$kinds;
					if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
						$sqlStr.=" and `admin_num`=?";
						$parameter[":admin_num"]=$this->web_root_num;
					}
					$rowAll=$this->product->sqlRowList($sqlStr,$parameter,"product");
				}
				if($rowAll!=NULL){
					echo '<div style="height:150px;overflow:auto;margin-top:5px;padding: 4px 12px;" class="form-control">';
					$i=0;
					foreach($rowAll as $row){
						$showCheck=true;
						if(in_array($row["num"],$num)){
							$showCheck=false;
						}
						
						if ($showCheck){
							$i++;
							$chkID=$this -> input -> post('hiddenID')."_chkPro".$i;
							echo '<div id="'.$chkID.'Div" onMouseOver="this.style.color=\'blue\'" onMouseOut="this.style.color=\'#21536a\'">';
							echo '<input id="'.$chkID.'" type="checkbox" onChange="isCheckOther(this.value,\''.$chkID.'Div\',\''.$divID.'\',\''.$hiddenID.'\','.$customValue.');" value="'.$row["num"].'"><label for="'.$chkID.'">'.$row["pro_name"].'</label></div>';		
						}
						
					}
					echo '</div>';
				}
			}
		}
	}

	function other_sel(){
		if ($this -> input -> is_ajax_request()) {
			$customValue=$this -> input -> post('customValue');
			
			if($this -> input -> post('nums')){
				if ($customValue==1){	//根據要撈出的語法而定
					$rowAll=$this->my_config->other_sel($this -> input -> post('nums'));
				}
				if ($customValue==2){	//根據要撈出的語法而定
					$rowAll=$this->my_config->other_sel($this -> input -> post('nums'));
				}
				if($rowAll!=NULL){
					$i=0;
					foreach($rowAll as $row){
						$selID=$_POST["hiddenID"]."_selPro".$i;
						echo '<div id="'.$selID.'Div" class="selDiv" onMouseOver="this.style.color=\'blue\'" onMouseOut="this.style.color=\'#21536a\'">';
						echo '<input id="'.$selID.'" type="checkbox" onChange="unCheckOther(this.value,\''.$selID.'Div\',\''.$_POST["divID"].'\',\''.$_POST["hiddenID"].'\','.$_POST["customValue"].');" checked="checked" value="'.$row["num"].'"><label for="'.$selID.'">'.$row["pro_name"].'</label></div>';	
						$i++;
					}
				}
			}
		}
	}
	
	function hasChild($root){
		$sqlStr="select * from [myTable] where `root`=? ";
		$parameter[":root"]=$root;
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$sqlStr.=" order by `range`";
		$row=$this->product->sqlRow($sqlStr,$parameter,'pro_kind');
		if($row!=NULL){
			return true;
		}else{
			return false;
		}
	}
	//排序
	function myRange($root=0,$nation=NULL){		
		$this->_append_js2("sort/jquery.ui.core.js");
		$this->_append_js2("sort/jquery-sortable.js");
		$this->_append_css2("sort/sort.css");
		
		$this->isLogin();//檢查登入狀態
		$this->data["nation"]=($nation!=NULL ? $nation : $this->defaultNation());
		$parameter=array();
		$sqlStr="select * from [myTable] where `pro_kind` in (?".$this->my_config->kind_sql($root,"pro_kind").") and `nation`=?";
		$parameter[":pro_kind"]=$root;
		$parameter[":nation"]=$this->data["nation"];
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$sqlStr.=" order by `range`";
		$rowAll=$this->product->sqlRowList($sqlStr,$parameter,'product');
		
		$this->data["rowAll"]=$rowAll;	
			
		$this->data["maintitle"]=$this->getsubtitle();
		
		$this -> data["body"] = $this -> load -> view("sysAdmin/product/range", $this -> data,true); 
		$this -> load -> view("sysAdmin/main", $this -> data);	
	}
	
	//排序
	function load_view2(){
		if ($this -> input -> is_ajax_request()) {
			$level=1;
			$data=array();
			$parameter=array();
			$sqlStr="select * from [myTable] where `root`=0 and `nation`=?";
			$parameter[":nation"]=$this->input->post('nation',true);
			if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
				$sqlStr.=" and `admin_num`=?";
				$parameter[":admin_num"]=$this->web_root_num;
			}
			$sqlStr.=" order by `range`";
			$rowAll=$this->product->sqlRowList($sqlStr,$parameter,'pro_kind');
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$options=array();
					$options["text"]=$row["kind"];
					$options["href"]=site_url("sysAdmin/product/myRange/".$row["num"]."/".(!$this->input->post('nation') ? "" : $this->input->post('nation',true)));
					if($this->hasChild($row["num"]) && $level < $this->kindLevel){	//子節點
						$options["nodes"]=$this->load_child_view2($row["num"],($level+1));
					}
										
					array_push($data,$options);
				}
			}
			echo json_encode($data);
		}
		
	}
	
	
	function load_child_view2($root,$level=2){
		$data=array();
		$parameter=array();
		$sqlStr="select * from [myTable] where `root`=?";
		$parameter[":root"]=$root;
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$sqlStr.=" order by `range`";
		$rowAll=$this->product->sqlRowList($sqlStr,$parameter,'pro_kind');
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$options=array();
				$options["text"]=$row["kind"];
				$options["href"]=site_url("sysAdmin/product/myRange/".$row["num"]."/".(!$this->input->post('nation') ? "" : $this->input->post('nation',true)));
				if($this->hasChild($row["num"]) && $level < $this->kindLevel){	//子節點
					$options["nodes"]=$this->load_child_view2($row["num"],($level+1));
				}
													
				array_push($data,$options);
			}
		}
		return $data;
	}
	
	//排序
	function AjaxRange(){
		if($this->input->is_ajax_request()){
			$num=explode(",",$this->input->post("sortNum",true));
			for ($i=0;$i<count($num);$i++){
				$sqlStr="UPDATE [myTable] SET `range`=".$i." where num=?";
				$parameter=array(':num' => $num[$i]);
				$this->product->sqlExc($sqlStr,$parameter,'product');	
			}
		}
	}

	
} 