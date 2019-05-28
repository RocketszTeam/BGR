<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Test2 extends CI_Controller{
    function __construct(){
        parent::__construct(); // needed when adding a constructor to a controller
		//$this->load->library('rsa');
		//$CI =& get_instance();
		//$CI =& get_instance();

 		$this->load->library('api/allgameapi');

    }
	
	public function index(){
        //echo $this->slotteryapi->query_handicaps();
        //print_r($this->slotteryapi->create_account('EMrocketsz','a123456',9,20));
        echo $this->slotteryapi->forward_game('EMrocketsz','a123456');

        //print_r($this->egapi->reporter_all($sTime=NULL,$eTime=NULL));
		/*
		$u_id = 'D9filochan';
		$u_password='level4';
		$mem_num = 312 ;
		 echo'建立帳戶：';
		 //print_r($this->pk10->create_account($u_id,$mem_num,30));
		 echo '<pre>';	//創建補魚機

		$this->pk10->reporter_all('2018-05-16 09:00:00','2018-05-16 15:00:00');
		//echo '<br>儲值：'.$this->pk10->deposit($u_id,300,$mem_num,30);
		//echo '<br>取值：'.$this->pk10->withdrawal($u_id,100,$mem_num,30);
		//echo '<br>餘額：'.$this->pk10->get_balance($u_id);
		*/
		//echo (float)215.20-(float)215 ;
	}


	

}

?>