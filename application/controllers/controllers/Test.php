<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Test extends CI_Controller{
    function __construct(){
        parent::__construct(); // needed when adding a constructor to a controller
		
		date_default_timezone_set("Asia/Taipei");
    }
	public function split(){
        $str = '取號為 : 96565819049767';
        echo
        $code =  str_replace('取號為 : ', '', $str);;
    }
	
	//皇朝
	public function hces(){
		$out_trade_no=15288106674744;
		$this->load->library('api/hces888');
		$u_id='testAccount';
		$ga = "LU".$u_id;
		//$this->hces888->agentToken();
		//$this->hces888->create_account($u_id,1);
		//$this->hces888->get_balance($u_id);
		//$this->hces888->deposit($u_id,1000,1);
		//$this->hces888->withdrawal($u_id,200,1);
		//$this->hces888->point_checking($out_trade_no,2,$u_id,1,1);
		$this->hces888->forward_game($u_id);
		$sTime='2018-06-22 00:00:00';
		$eTime='2018-06-22 23:00:00';
		$result=$this->hces888->reporter_all($sTime,$eTime,$type=0);
		echo '<pre>';
		print_r($result);
		
		
		//$uname=explode('@',$result->data[0]->user_name);
		//echo reset($uname);
		
		//if(count($result->data)==$result->page_size && isset($result->cursor)){
			//$this->hces888->reporter_all($sTime,$eTime,$type=0,$result->cursor);
		//}
		
	}
	
	
	public function index(){
		/*
		$this->load->library('api/allbetapi');	//訂單函式庫
		$this->allbetapi->query_handicaps();
		
		$this->load->library('api/rtgapi');
		$this->rtgapi->index_agent();
		
		$this->load->library('api/sagamingapi');
		print_r( $this->sagamingapi->QueryBetLimit() );
		
		$this->load->library('api/slotteryapi');
		$this->slotteryapi->deposit_agent();
		//$this->slotteryapi->create_agent();
		//$this->slotteryapi->create_exampleaccount();
		
		*/
		


	}
	
	//RTG取得api token
	public function rtgapi(){
			
		var_dump($this->rtgapi->index_token());
		
	}
	//RTG取得代理資訊
	public function rtgapi_start(){
		var_dump($this->rtgapi->index_agent());
		//var_dump($this->rtgapi->index_agent()->agentId);
		//var_dump($this->rtgapi->index_3()->casinos[0]->id);
		
	}
	//RTG建立遊戲帳號
	public function rtgapi_create_account(){
		echo "<pre>";
		var_dump($this->rtgapi->create_account($u_id="GRrockvgt1",$u_password="a123456",$mem_num=20669,$gamemaker_num=51));
	}
	//RTG deposit
	public function rtgaoi_deposit(){
		echo "<pre>";
		var_dump($this->rtgapi->deposit($u_id="GRrockvgt1",$amount=50,$mem_num=20669,$gamemaker_num=51,$logID=NULL));
	}
	//RTG get_balance
	public function rtgaoi_get_balance(){
		echo "<pre>";
		var_dump($this->rtgapi->get_balance($u_id="GRrockvgt1"));
	}
	//RTG withdrawal.
	public function rtgapi_withdrawal(){
		echo "<pre>";
		var_dump($this->rtgapi->withdrawal($u_id="GRrockvgt1",$amount=50,$mem_num=20669,$gamemaker_num=51,$logID=NULL));
	}
	//RTG gamestrings
	public function rtgapi_gamestrings(){
		echo "<pre>";
		var_dump($this->rtgapi->gamestrings());
	} 
	//RTG forward_game
	public function rtgapi_forward_game(){
		echo "<pre>";
		var_dump($this->rtgapi->forward_game("GRrockvgt1",$gameId="1179826"));
	}
	//RTG fun_game
	public function rtgapi_fun_game(){
		echo "<pre>";
		var_dump($this->rtgapi->fun_game("D9rocketsz",1179826));
	}
	//RTG reporter_all
	public function rtgapi_reporter_all(){
		echo "<pre>";
		var_dump($this->rtgapi->reporter_all());
	}


    public function cvs(){
        $this->load->library('orderclass');	//訂單函式庫
        $this->load->library('memberclass');

        $order_no=$this->orderclass->order_no();
        $amount = 100;
        $parameter=array();
        $colSql="order_no,mem_num,admin_num,amount,payment,pay_mode,buildtime";
        $sqlStr="INSERT INTO `orders` (".sqlInsertString($colSql,0).")";
        $sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
        $parameter[':order_no']=$order_no;
        $parameter[':mem_num']=3;
        $parameter[':admin_num']=5;
        $parameter[':amount']=$amount;
        $parameter[':payment']='CVS';
        $parameter[':pay_mode']=99;
        $parameter[':buildtime']=now();
        $this->webdb->sqlExc($sqlStr,$parameter);
        $this->load->library('payment/ufopay');
        $phone=tb_sql('phone','member',$this->memberclass->num());
        $log=$this->ufopay->cvsPay($order_no,$amount);
    }
	public function QueryBetLimit(){
		$this->load->library('api/sagamingapi');	//載入會員函式庫
		$BetLimitList=$this->sagamingapi->QueryBetLimit();
		foreach($BetLimitList->BetLimit as $v){
			print_r($v);
			echo '<hr>';	
		}
	}

	public function points_record(){
		$this->load->library('api/slotteryapi');	//載入會員函式庫
		
		$u_id='V60915393533';
		$Date=date('Y/m/d');
		$Date="2018/03/19";
		$result=$this->slotteryapi->points_record($u_id,$Date);	
		if(isset($result)){
			echo '<table border=1>';
			echo '<tr>';
			echo '<th>帳號</th>';
			echo '<th>類型</th>';
			echo '<th>點數</th>';
			echo '<th>異動後點數</th>';
			echo '<th>操作日期時間</th>';
			echo '<th>操作IP</th>';
			echo '</tr>';
			foreach($result as $row){
				echo '<tr>';
				echo '<td>'.$u_id.'</td>';
				echo '<td>'.($row[0]==1 ? '轉出遊戲' : '轉入遊戲').'</td>';
				echo '<td>'.$row[1].'</td>';
				echo '<td>'.$row[2].'</td>';
				echo '<td>'.$row[3].'</td>';
				echo '<td>'.$row[4].'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}
	

}

?>