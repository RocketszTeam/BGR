<?phpif ( ! defined('BASEPATH')) exit('No direct script access allowed');include_once (dirname(__FILE__)."/Core_controller.php");error_reporting(0);class tool extends Core_controller{	public function __construct(){ 		parent::__construct();		date_default_timezone_set("Asia/Taipei");		$this->load->library('api/allgameapi');	//載入遊戲api	}	public function index2($page){		$filte_ary = array("gamemaker_num" => "3");	$query = $this->db->get_where('games_account',$filte_ary);	$num_rows=$query->num_rows();	echo $num_rows.'<br>';	//$page=(int)$this->uri->segament(4);// 獲取頁碼	echo $page ;	//$config["uri_segment"] = 3;	$page=($page=='')?1:$page;	$config['base_url'] = '/report/tool/index2'; //url地址	$config['total_rows'] = $num_rows;	$config['per_page'] = 20; //每頁幾筆	$config['use_page_numbers'] = TRUE; //使用頁碼方式而非偏移量傳值	$offset = $page == false?1:($config['per_page'] * ($page - 1)); // 用頁碼計算偏移量  //設置分頁導航條樣式	echo '<table><tr><td>u_id</td><td>balance</td><td>transfer</td></tr>';	$query = $this->db->get_where('games_account', $filte_ary , $config['per_page'] , $offset);//		foreach ($query->result_array() as $row) {		//print_r($row);		//echo '<br>';		//echo '<br>U_ID:'.$row['u_id'].'<br>';		$balance=$this->allgameapi->get_balance($row['mem_num'],3);	//密碼解密		//echo 'U_PASSWORD:'.$row['u_password'];		//$balance=$this->allbetapi->get_balance($row['u_id'],$u_password);		//echo 'balance:'.$balance ;		if (($balance)>0){		echo '<tr><td>'.$row['u_id'].'</td><td>'.$balance.'</td><td>'.$this->allgameapi->withdrawal($balance,$row['mem_num'],3).'</td></tr>';		}else{		echo '<tr><td>'.$row['u_id'].'</td><td>無餘額</td><td>無可回存餘額</td></tr>';		}	}	echo '</table>';	$this->load->library('pagination');	$this->pagination->initialize($config);	echo $this->pagination->create_links();	  //echo $this->pagination->create_links();	}public function test($u_id,$u_password,$num){$u_id = 'PJokwap810';$u_password='f69c8edf591a6fcc3b694e03b3b5e3e34ef49fb81bad2f194967964c218c73cf407578c42e518db160f01c1c54c595b36baae0d7ffe8ec5fb111dc128ea88b85UURr';$num = 20 ;$logmsg = $this->allgameapi->forward_game($u_id,$u_password,$num);echo $logmsg ;}public function index2_do($amount,$u_num,$gamemaker_num=3){	echo $this->allgameapi->withdrawal($amount,$u_num,$gamemaker_num);}	public function getmemberinallbet($start,$len=30){  //載入'分頁類'  $this->load->library('pagination');  $this->load->helper('form');  $this->load->helper('url');  //根據組合條件，計算記錄總數，（當前組合條件為空）  $config['total_rows'] = $this->db->count_all_results('games_account');  //設置本頁路徑  $config['base_url'] = "本頁路徑";  //設置每頁顯示記錄數  $config['per_page'] = '15';  //設置分頁導航條樣式  $config['full_tag_open']   = '<div id = "page_nav">';  $config['full_tag_close']  = '</div>';  $config['first_link']      = '首頁';  $config['last_link']       = '末頁';  $config['next_link']       = '下一頁>';  $config['prev_link']       = '<上一頁';  //應用設置  $this->pagination->initialize($config);  //設置查詢條件  //排序順序  //limit(結果數，偏移量)  $this->db->limit($config['per_page'],$offset);  //查詢  $query = $this->db->get(' games_account where gamemaker=3 ');  //顯示結果列表  foreach ($query->result_array() as $row){    $this->table->add_row($row);  }  echo $this->table->generate();  //添加分頁導航條  echo $this->pagination->create_links();/*		$sqlStr = 'select * from games_account where gamemaker_num=3 limit '.$start.' , '.$len;		$query = $this->db->query($sqlStr);		$orderQuery = $query->result_array() ;		//$start = 31 ;		//$end = 50 ;		$i = 0 ;		foreach($orderQuery as $row){				$i++ ;		//	if (($i >= $start)and ($i<=$end)){				echo $row['u_id'].'  '.$this->allbetapi->get_balance($row['u_id'],$row['u_password']).'<br>';		//	}		//	if ($i==$end){		//		break ;		//	}		}*/	}		public function getallbetmember(){		$this->getmemberinallbet(100,10);	}	}?>