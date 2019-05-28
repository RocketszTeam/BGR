<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Pro_kind extends Core_controller{
	
	private $kindLevel=3;	//分類層數
	public function __construct(){
		parent::__construct();
		
		$this -> load -> model("sysAdmin/pro_kind_model", "kind");
		
		$this->data["kindLevel"]=$this->kindLevel;
		
	}
	
	public function index($root = 0,$nation=NULL){		
		$this->isLogin();//檢查登入狀態
		$this->data["nation"]=($nation!=NULL ? $nation : $this->defaultNation());
		
		$this->data["show_form"]=false;
		$this->data["action"] = site_url("sysAdmin/kind/create/".(empty($root) ? "" : $root)."/".(empty($nation) ? "" : $nation));
		$this->data["subtitle"] = '新增商品主分類';
		$this->data["maintitle"]=$this->getsubtitle();	//取得當前頁面名稱			
		$this -> data["body"] = $this -> load -> view("sysAdmin/product/kind", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
	
	
	function create($root=0,$nation=NULL){	//新增選項	
		$this->isLogin();//檢查登入狀態
		if($_POST){	//新增資料
			//取得排序最後值
			$sqlStr="select `range` from [myTable] where `root`=? and `nation`=?";
			$sqlStr.=" order by `range` desc limit 0,1";
			$parameter=array(':root' => $root,':nation'=>$nation);
			$row=$this->kind->sqlRow($sqlStr,$parameter);
			if ($row!=NULL){
				$max_r=$row["range"]+1;
			}else{
				$max_r=0;	
			}			
			$parameter=array();
			$colSql="nation,kind,admin_num,root,range";
			$sqlStr="INSERT INTO [myTable] (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":nation"]=$this->input->post("nation",true);
			$parameter[":kind"]=$this->input->post("kind",true);
			$parameter[":admin_num"]=($this->web_root_u_power > 1 ? $this->web_root_num : 0);
			$parameter[":root"]=$root;
			$parameter[":range"]=$max_r;
			if(!$this->kind->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, "sysAdmin/pro_kind/index/".$root."/".$nation);
			}	
		}else{
			
			$this->data["show_form"]=true;
			$this->data["action"] = site_url("sysAdmin/pro_kind/create/".$root."/".$nation);
			$this->data["subtitle"] = $root==0  ? '新增商品主分類' : '【'.$this->kind->tb_sql("kind",$root).'】-新增子分類';
			
			$this->data["maintitle"]=$this->getsubtitle();	//取得當前頁面名稱
			
			$this -> data["body"] = $this -> load -> view("sysAdmin/product/kind", $this -> data,true); 
			$this -> load -> view("sysAdmin/main", $this -> data);	
		}
		
	}
	
	
	function edit($num,$nation){
		$this->isLogin();//檢查登入狀態
		
		$this->data["todo"]='edit';
		
		$this->data["nation"]=$nation;
		
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->kind->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/pro_kind/index");
		}else{		
			if($_POST){	//修改資料
				$parameter=array();
				$colSql="kind";
				$sqlStr="UPDATE [myTable] SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":kind"]=$this->input->post("kind",true);
				$parameter[":num"]=$num;
				$this->kind->sqlExc($sqlStr,$parameter);
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg,"sysAdmin/pro_kind/index/".$row["root"]."/".$row["nation"]);
				
			}else{
				
				
				$this->data["show_form"]=true;
				$this->data["action"]=site_url("sysAdmin/pro_kind/edit/".$row["num"]."/".$nation);
				$this->data["maintitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("sysAdmin/product/kind", $this -> data,true); 
				$this -> load -> view("sysAdmin/main", $this -> data);	
				
			}
			
		}
		
		
	}
	
	
	function delete($num){
		
		$this->isLogin();//檢查登入狀態
		
		
		
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->kind->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/pro_kind/index");
		}else{
			$sqlStr="delete from [myTable] where num=?";
			$this->kind->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/pro_kind/index/".$row["root"]."/".$row["nation"]);
		}
		
	}
	
	
	
	
	
	function load_view(){
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
			$rowAll=$this->kind->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$options=array();
					$options["text"]=$row["kind"];
					$options["tags"]=array('<a href="'.site_url("sysAdmin/pro_kind/edit/".$row["num"]."/".$row["nation"]).'" class="btn btn-primary btn-xs" title="編輯"><span class="glyphicon glyphicon-pencil"></span></a>');
					
					if($level < $this->kindLevel){
						array_push($options['tags'],'<a href="'.site_url("sysAdmin/pro_kind/create/".$row["num"]."/".$row["nation"]).'" class="btn btn-primary btn-xs" title="新增子分類"><span class="glyphicon glyphicon-plus"></span></a>');	
					}
					$options["href"]='javascript:void(0)';
					if($this->hasChild($row["num"]) && $level < $this->kindLevel){	//子節點
						$options["nodes"]=$this->load_child_view($row["num"],($level+1));
					}
										
					array_push($data,$options);
				}
			}
			echo json_encode($data);
		}
		
	}
	
	
	function load_child_view($root,$level=2){
		$data=array();
		$parameter=array();
		$sqlStr="select * from [myTable] where `root`=?";
		$parameter[":root"]=$root;
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$sqlStr.=" order by `range`";
		$rowAll=$this->kind->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$options=array();
				$options["text"]=$row["kind"];
				$options["tags"]=array(
									'<a href="'.site_url("sysAdmin/pro_kind/edit/".$row["num"]."/".$row["nation"]).'" class="btn btn-primary btn-xs" title="編輯"><span class="glyphicon glyphicon-pencil"></span></a>'
									);	
					if($level < $this->kindLevel){
						array_push($options['tags'],'<a href="'.site_url("sysAdmin/pro_kind/create/".$row["num"]."/".$row["nation"]).'" class="btn btn-primary btn-xs" title="新增子分類"><span class="glyphicon glyphicon-plus"></span></a>');	
					}
					$options["href"]='javascript:void(0)';
					if($this->hasChild($row["num"]) && $level < $this->kindLevel){	//子節點
						$options["nodes"]=$this->load_child_view($row["num"],($level+1));
					}
													
				array_push($data,$options);
			}
		}
		return $data;
	}
	
	
	function hasChild($root){
		$sqlStr="select * from [myTable] where `root`=? ";
		$parameter[":root"]=$root;
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$sqlStr.=" order by `range`";
		$row=$this->kind->sqlRow($sqlStr,$parameter);
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
		$sqlStr="select * from [myTable] where `root`=? and `nation`=?";
		$parameter[":root"]=$root;
		$parameter[":nation"]=$this->data["nation"];
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$sqlStr.=" order by `range`";
		$rowAll=$this->kind->sqlRowList($sqlStr,$parameter,'pro_kind');
		$this->data["maintitle"]=$this->getsubtitle();	//取得當前頁面名
		
		$this->data["rowAll"]=$rowAll;		
		
		$this -> data["body"] = $this -> load -> view("sysAdmin/product/range2", $this -> data,true); 
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
			$rowAll=$this->kind->sqlRowList($sqlStr,$parameter);
			if($rowAll!=NULL){
				foreach($rowAll as $row){
					$options=array();
					$options["text"]=$row["kind"];
					$options["href"]=site_url("sysAdmin/pro_kind/myRange/".$row["num"]."/".(!$this->input->post('nation') ? "" : $this->input->post('nation',true)));
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
		$rowAll=$this->kind->sqlRowList($sqlStr,$parameter);
		if($rowAll!=NULL){
			foreach($rowAll as $row){
				$options=array();
				$options["text"]=$row["kind"];
				$options["href"]=site_url("sysAdmin/pro_kind/myRange/".$row["num"]."/".(!$this->input->post('nation') ? "" : $this->input->post('nation',true)));
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
				$this->kind->sqlExc($sqlStr,$parameter,'pro_kind');	
			}
		}
	}
	
} 