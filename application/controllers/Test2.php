<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Test2 extends CI_Controller{
    function __construct(){
        parent::__construct(); // needed when adding a constructor to a controller
        $this->load->library('api/Allgameapi');
    }
    public function index(){
        //echo $this->slotteryapi->create_account('U88Test123','a123456',1756,20);
        //echo $this->dreamgame->query_handicaps();

        //print_r($this->wmapi->deposit('U88Test123',100,1756,13));

        //echo $this->sagamingapi->get_balance('U88Test123');
        //print_r($this->rtgapi->withdrawal('U88Test123',200,1756,51));
        //echo $this->dw->forward_game('BGRTest123');



        //AB流程
        //1 查限紅組
        print_r($this->allbetapi->query_handicaps());
        
        //SUP 彩球 流程
        //1 建立代理
        //print_r($this->slotteryapi->create_agent());

        //2 建立複製會員
        //print_r($this->slotteryapi->create_exampleaccount());

        //3 給代理點數
        //print_r($this->slotteryapi->deposit_agent());

        //4 測試建立遊戲會員
        //print_r($this->slotteryapi->create_account('U88Test123','a123456',1756,20));
    }
}

?>