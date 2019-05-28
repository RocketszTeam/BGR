<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Tools extends Core_controller{
	private $picMax=0;  //圖片數量
	private $picDir;
	public function __construct(){
		parent::__construct();
		$this->CI =&get_instance();
		
		$this->data["openFind"]="N";//是否啟用搜尋
	}
	
	public function index(){	
		
		//撈出分類查詢下拉用
		$this->load->library('pagination');

		$parameter=array();
		$sqlStr="select * from `game_makers` ";
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數


    	$this->data['links'] =  $this->pagination->create_links();		
    							
		$this->data["game_makers"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		$this->data["s_action"]=site_url(SYSTEM_URL."/Tools/get_balances");
							
		$this -> data["body"] = $this -> load -> view("admin/tools", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	public function get_balances(){	//抓遊戲餘額
		$this->load->library('api/allgameapi');	//載入遊戲api
		$this->load->library('pagination');

        $sqlStr = '' ;
        $parameter = array() ;

        $sqlStr="select * from `game_makers` ";
        $this->data["game_makers"]=$this->webdb->sqlRowList($sqlStr,$parameter);
        /*
        if(@$_REQUEST["find2"]!=""){
            $sqlStr.=" and `gamemaker_num` = ?";
            $parameter[":gamemaker_num"]="'".@$_REQUEST["find2"]."'";
        }
        */
        //取得使用者資料
        $sqlStr="select * from `games_account` where 1=1 ";
        if(@$_REQUEST["find2"]!=""){
            $sqlStr .= " and `gamemaker_num` =".@$_REQUEST["find2"];
            $parameter[":gamemaker_num"]="'".@$_REQUEST["find2"]."'";
        }
        if(@$_REQUEST["find1"]!=""){
            $sqlStr .= " and `u_id` like '%".@$_REQUEST["find1"]."%'";
            $parameter[":u_id"]="'%".@$_REQUEST["find1"]."%'";
        }

        //echo $sqlStr ;
        //print_r($parameter);
		
		$total=$this->webdb->sqlRowCount($sqlStr,$parameter); //總筆數
		$config['base_url'] = base_url().SYSTEM_URL."/Tools/get_balances?".$this->data["att"];//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=30;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 3;
		$config['page_query_string'] = TRUE;
		
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `num` DESC LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);	

    	$this->pagination->initialize($config); 

    	$this->data['links'] =  $this->pagination->create_links();



		//$row=$this->webdb->sqlRowList($sqlStr,$parameter);
		//print_r($row);
		//取得遊戲廠商資料
		//$sqlStr="select * from `game_makers` where num=".@$_REQUEST["find2"];
		//$this->data["game_makers"]=$this->webdb->sqlRowList($sqlStr,$parameter);
		//$rows=$this->webdb->sqlRowList($sqlStr,$parameter);
		//$i = 0 ;
		if($rowAll!=NULL){
			//$this->data['member'] = $row ;
			//抓遊戲餘額
			$i = 0 ;
			foreach($rowAll as $row_data){
				//取餘額
				//echo 'mem_num:'.$row_data['mem_num'];
				//echo '<br>gamemaker_num:'.$row_data['gamemaker_num'].'<br>';
				$balance = '00';
				$balance=@$this->allgameapi->get_balance($row_data['mem_num'],$row_data['gamemaker_num']);
				//echo 'balance:'.$balance .'<br>';
				$rowAll[$i]['balance']=$balance ;
				$i++ ;
			}
			//print_r($row);
			$this->data["member"] = $rowAll ;
			$this->data["s_action"]=site_url(SYSTEM_URL."/Tools/get_balances");
			$this->data["s_balance"]=site_url(SYSTEM_URL."/Tools/withdraw/");
			//$this->data["all_balance"]=site_url(SYSTEM_URL."/Tools/withdrawall/");


			$this -> data["body"] = $this -> load -> view("admin/tools", $this -> data,true);   
			$this -> load -> view("admin/main", $this -> data); 			
		}


	}
	public function withdraw(){
		$this->load->library('api/allgameapi');	//載入遊戲api
		$mem_num = $_POST['mem_num'];
		$gamemaker_num = $_POST['gamemaker_num'];
		$balance=$this->allgameapi->get_balance($mem_num,$gamemaker_num);
		$balance=$this->allgameapi->withdrawal($balance,$mem_num,$gamemaker_num);
		
		$balance=$this->allgameapi->get_balance($mem_num,$gamemaker_num);
		//$balance = 100 ;
		echo json_decode($balance);

	}
	

	

	
} 