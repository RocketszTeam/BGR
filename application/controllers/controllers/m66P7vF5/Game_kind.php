<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Game_kind extends Core_controller{
	
	private $kindLevel=3;	//分類層數
	public function __construct(){
		parent::__construct();
		
		$this -> load -> model("sysAdmin/game_kind_model", "kind");
		$this -> load -> model("sysAdmin/game_makers_model", "game_makers");
		
		$this->data["kindLevel"]=$this->kindLevel;
		
		$this->data["makersTab"]=array();
		$sqlStr="select * from [myTable] order by `range`";
		$this->data["makersTab"]=$this->game_makers->sqlRowList($sqlStr);
	}
	
	public function index($makers_num=NULL,$root=0){		
		$this->isLogin();//檢查登入狀態

		$this->_append_js2("sort/jquery.ui.core.js");
		$this->_append_js2("sort/jquery-sortable.js");
		$this->_append_css2("sort/sort3.css");
		
		
		$this->data["nation"]=$this->defaultNation();
		$makers_num=($makers_num==NULL ? $this->data["makersTab"][0]["num"] : $makers_num);	
			
		$this->data["item"]=array('num'=>($root > 0 ? tb_sql("root","game_kind",$root) : $root),'makers_num'=>$makers_num,'root'=>$root);
		
		//print_r()
		
		
		$parameter=array();
		$sqlStr="select * from [myTable] where `makers_num`=? and `root`=? order by `range`";
		$parameter[':makers_num']=$makers_num;
		$parameter[':root']=$root;
		
		
		$this->data["result"]=$this->kind->sqlRowList($sqlStr,$parameter);
		$this -> data["body"] = $this -> load -> view("sysAdmin/game/kind", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);  
	}
	
	
	
	function create($makers_num,$root){	
		$this->isLogin();//檢查登入狀態
		if($_POST){	//新增資料
			//取得排序最後值
			$sqlStr="select `range` from [myTable] where `root`=? and `makers_num`=? and `nation`=?";
			$sqlStr.=" order by `range` desc limit 0,1";
			$parameter=array(':root' => $root,':makers_num'=>$makers_num,':nation'=>$this->defaultNation());
			$row=$this->kind->sqlRow($sqlStr,$parameter);
			if ($row!=NULL){
				$max_r=$row["range"]+1;
			}else{
				$max_r=0;	
			}	
			$parameter=array();
			$colSql="kind,makers_num,root,range,nation";
			$sqlStr="INSERT INTO [myTable] (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":kind"]=$this->input->post("kind",true);
			$parameter[":makers_num"]=$makers_num;
			$parameter[":root"]=$root;
			$parameter[":range"]=$max_r;
			$parameter[":nation"]=$this->defaultNation();
			if(!$this->kind->sqlExc($sqlStr,$parameter)){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Game_kind/index/".$makers_num."/".$root);
			}	
		}else{
			
			$this->data["upArray"]=array();				
			$this->data["upArray"]["hasUp"]=($root > 0 ? true : false);
			$this->data["upArray"]["UpKind"]=tb_sql("kind","game_kind",$root);
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Game_kind/create/".$makers_num."/".$root);	//表單送出連結
			$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Game_kind/index/".$makers_num."/".$root);	
			$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱	

			$this -> data["body"] = $this -> load -> view("sysAdmin/game/kind_form", $this -> data,true); 
			$this -> load -> view("sysAdmin/main", $this -> data);	
		}
		
	}
	
	function edit($makers_num,$root,$num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->kind->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Game_kind/index/".$makers_num."/".$root);
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
				$this -> _setMsgAndRedirect($msg, SYSTEM_URL."/Game_kind/index/".$makers_num."/".$root);
				
			}else{
				
				$this->data["upArray"]=array();				
				$this->data["upArray"]["hasUp"]=($row["root"] > 0 ? true : false);
				$this->data["upArray"]["UpKind"]=tb_sql("kind","game_kind",$row["root"]);
				
				$this->data["cancelBtn"]=site_url(SYSTEM_URL."/Game_kind/index/".$makers_num."/".$root);
							
				$this->data["formAction"]=site_url(SYSTEM_URL."/Game_kind/edit/".$makers_num."/".$root."/".$row["num"]);
				$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("sysAdmin/game/kind_form", $this -> data,true); 
				$this -> load -> view("sysAdmin/main", $this -> data);	
				
			}
			
		}		
	}

	function delete($makers_num,$root,$num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->kind->sqlRow($sqlStr,array(':num'=>$num));
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Game_kind/index/".$makers_num."/".$root);
		}else{
			$sqlStr="delete from [myTable] where num=?";
			$this->kind->sqlExc($sqlStr,array(':num'=>$num));
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,SYSTEM_URL."/Game_kind/index/".$makers_num."/".$root);
		}
		
	}

	
	//排序
	function AjaxRange(){
		if($this->input->is_ajax_request()){
			$num=explode(",",$this->input->post("sortNum",true));
			for ($i=0;$i<count($num);$i++){
				$sqlStr="UPDATE [myTable] SET `range`=".$i." where num=?";
				$parameter=array(':num' => $num[$i]);
				$this->kind->sqlExc($sqlStr,$parameter);	
			}
		}
	}
	
} 