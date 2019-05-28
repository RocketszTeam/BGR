<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Order_payment extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		
		$this -> load -> model("sysAdmin/order_model", "order");
		
		
		
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
		//---付款方式-------------------------------
		if(@$_REQUEST["find2"]!=""){	
			$sqlStr.=" and `kind`=?";	
			$parameter[":kind"]=@$_REQUEST["find2"];
		}
		//---狀態-------------------------------
		if(@$_REQUEST["find3"]!=""){
			$sqlStr.=" and `active`=?";
			$parameter[":active"]=@$_REQUEST["find3"];
		}
		
		/*//----防止最高權限帳號被搜尋-------
		if($this->web_root_u_power!=1){
			$sqlStr.=" and u_power <>1 and u_power > ?";
			$parameter[":root_power"]=$this->web_root_u_power;	
		}	*/	
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$total=$this->order->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL),"pro_payment"); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url()."index.php/sysAdmin/order_payment/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `reg_time` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->admin->sqlRowList($sqlStr,$parameter,"pro_payment");		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
							
		$this -> data["body"] = $this -> load -> view("sysAdmin/order/payment", $this -> data,true);   
		$this -> load -> view("sysAdmin/main", $this -> data);   
	}
	
	
	function create($root=0,$nation=NULL){	//新增選項
		$this->isLogin();//檢查登入狀態
		if($_POST){	//新增資料
			$nation=$this->input->post("nation",true);
			$parameter=array();
			$colSql="kind,bank_id,bank_name,bank_user,bank_num,words,nation,admin_num,active,reg_time";
			$sqlStr="INSERT INTO [myTable] (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
			$parameter[":kind"]=trim($this->input->post("kind",true));
			$parameter[":bank_id"]=trim($this->input->post("bank_id",true));
			$parameter[":bank_name"]=trim($this->input->post("bank_name",true));
			$parameter[":bank_user"]=trim($this->input->post("bank_user",true));
			$parameter[":bank_num"]=trim($this->input->post("bank_num",true));
			$parameter[":words"]=trim($this->input->post("words",true));
			$parameter[":nation"]=$nation;
			$parameter[":admin_num"]=($this->web_root_u_power > 1 ? $this->web_root_num : 0);
			$parameter[":active"]=$this->input->post("active",true);
			$parameter[":reg_time"]=now();
			$num=$this->admin->sqlExc($sqlStr,$parameter,'pro_payment');
			if(!$num){
				$msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
				$this -> _setMsgAndRedirect($msg, current_url());
			}else{
				$msg = array("type" => "success", 'title' => '新增成功！', 'content' => '請查看新增之資料');
				$this -> _setMsgAndRedirect($msg, "sysAdmin/order_payment/index");
			}
		}else{
			$this->data["subtitle"] = '付款方式登錄';
			//撈出付款方式下拉用
			$this->data["nation"]=($nation!=NULL ? $nation : $this->defaultNation());
			$parameter=array();
			$sqlStr="select * from [myTable] where `root`=0 and `nation`=?";
			$parameter[":nation"]=$this->data["nation"];
			$sqlStr.=" order by `range`";
			$this->data["row_group"]=$this->order->sqlRowList($sqlStr,$parameter,"pro_payment_kind");
			$this->data["formAction"]=site_url("sysAdmin/order_payment/create");
			$this->data["todo"]="add";
			$this -> data["body"] = $this -> load -> view("sysAdmin/order/payment_form", $this -> data,true); 
			$this -> load -> view("sysAdmin/main", $this -> data);	
		}
	}
	
	
	function edit($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->admin->sqlRow($sqlStr,array(':num'=>$num),"pro_payment");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/order/payment");
		}else{	
			if($_POST){	//修改資料	
				$nation=$this->input->post("nation",true);//語系
				$parameter=array();
				$colSql="bank_id,bank_name,bank_user,bank_num,words,active,reg_time";
				$sqlStr="UPDATE [myTable] SET ".sqlUpdateString($colSql);
				$sqlStr.=" where num=?";
				$parameter[":bank_id"]=trim($this->input->post("bank_id",true));
				$parameter[":bank_name"]=trim($this->input->post("bank_name",true));
				$parameter[":bank_user"]=trim($this->input->post("bank_user",true));
				$parameter[":bank_num"]=trim($this->input->post("bank_num",true));
				$parameter[":words"]=trim($this->input->post("words",true));
				$parameter[":active"]=$this->input->post("active",true);
				$parameter[":reg_time"]=now();
				$parameter[":num"]=$num;
				$this->admin->sqlExc($sqlStr,$parameter,'pro_payment');
				$msg = array("type" => "success", 'title' => '修改成功！');
				$this -> _setMsgAndRedirect($msg, "sysAdmin/order_payment/index");
				
			}else{
				$this->data["subtitle"] = '付款方式修改';
				$this->data["formAction"]=site_url("sysAdmin/order_payment/edit/".$row["num"]);
				$this->data["todo"]="edit";
				$this->data["row"]=$row;	
				$this -> data["body"] = $this -> load -> view("sysAdmin/order/payment_form", $this -> data,true); 
				$this -> load -> view("sysAdmin/main", $this -> data);	
			}
		}		
	}
	
	function delete($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select * from [myTable] where num=?";
		$row=$this->admin->sqlRow($sqlStr,array(':num'=>$num),"pro_payment");
		if($row==NULL){
			$msg=array("type" => "warning", 'title' => '查無資料！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/order_payment/index");
		}else{
			$sqlStr="delete from [myTable] where num=?";
			$this->admin->sqlExc($sqlStr,array(':num'=>$num),"pro_payment");
			$msg=array("type" => "success",'title' => '刪除成功！');
			$this -> _setMsgAndRedirect($msg,"sysAdmin/order_payment/index");
		}
	}
	
	function enable($num){
		$this->isLogin();//檢查登入狀態
		$parameter=array();
		$sqlStr="select num from [myTable] where `num`=? ";
		$parameter[":num"]=$num;
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$row=$this->order->sqlRow($sqlStr,$parameter,'pro_payment');	
		if($row==NULL){
			$this -> _setMsgAndRedirect(array("type" => "warning", 
											  'title' => '查無資料！'),"sysAdmin/order_payment/index");
		}else{
			$sqlStr="Update [myTable] set `active`='Y' where num=?";
			$this->order->sqlExc($sqlStr,array(':num'=>$num),'pro_payment');	
			$info=array("type" => "success",'title' => '編輯成功！');
			$path="sysAdmin/order_payment/index";
			$this -> _setMsgAndRedirect($info,$path);						
		}
	}
	
	function disable($num){
		$this->isLogin();//檢查登入狀態
		$sqlStr="select num from [myTable] where `num`=? ";
		$parameter[":num"]=$num;
		if($this->web_root_u_power > 1){	//除了最高權限 能看到所有...否則只抓該使用者的分類
			$sqlStr.=" and `admin_num`=?";
			$parameter[":admin_num"]=$this->web_root_num;
		}
		$row=$this->order->sqlRow($sqlStr,$parameter,'pro_payment');	
		if($row==NULL){
			$this -> _setMsgAndRedirect(array("type" => "warning", 
											  'title' => '查無資料！'),"sysAdmin/order_payment/index");
		}else{
			$sqlStr="Update [myTable] set `active`='N' where num=?";
			$this->order->sqlExc($sqlStr,array(':num'=>$num),'pro_payment');	
			$info=array("type" => "success",'title' => '編輯成功！');
			$path="sysAdmin/order_payment/index/";
			$this -> _setMsgAndRedirect($info,$path);						
		}
		
	}
	
	//語系類別連動
	function ajax_kind(){
		if($this->input->is_ajax_request()){
			$table=$this->input->post("table");//table
			$ojbID=$this->input->post("ojbID");//ID
			$nation=$this->input->post("nation");//語系
			$defValue=$this->input->post("defValue");//預設值
			$pleaseSelect=$this -> input -> post('pleaseSelect');//是否有請選擇
			$required=$this -> input -> post('required');//是否需要判斷必輸入
			$admin_num=($this->web_root_u_power > 1? $this->web_root_num : 0);	//大於0傳入當前使用者num，否則傳0
			echo '<select class="form-control" name="'.$ojbID.'" id="'.$ojbID.'" data-live-search="true" '.($required =='true'? 'required' : '').'>';
			echo ($pleaseSelect ? '<option value="">請選擇</option>' : "");
			if($nation!=""){
				echo getKindOption($table,$defValue,$nation,$admin_num);
			}
			echo '</select>';
		}
	}
} 