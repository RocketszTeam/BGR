<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Cm_report extends Core_controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set("Asia/Taipei");
        $this->load->library('api/cm');    // 新濠彩票
    }
    public function index(){
        $this->get_report(date('2019-01-17 00:00:00'), date('2019-01-17 23:59:59'));
    }
    public function get_report($startdate = NULL, $enddate = NULL){
        if(is_null($enddate)){
            $enddate = date('Y-m-d H:i:s');
        }
        if(is_null($startdate)){
            $startdate = date('Y-m-d H:i:s', strtotime($enddate."-60 minutes"));
        }
        $result = $this->cm->reporter_all($startdate, $enddate);
		echo"<pre>";var_dump($result);
        if(isset($result)){
            if(count($result) > 0){
                foreach($result as $row){
                    //取出會員代理總代代理編號
                    $sqlStr="select mem_num from `games_account` where u_id='".$row->account."' and gamemaker_num=62";
                    $row_mem=$this->webdb->sqlRow($sqlStr);
                    $mem_num=$row_mem["mem_num"];	//取出會員編號
                    $u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
                    $u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
                    $u_power4=tb_sql("root",'admin',$u_power5);	//股東編號

                    $u_power4_profit=round((float)$row->win_amount * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
                    $u_power5_profit=round((float)$row->win_amount * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
                    $u_power6_profit=round((float)$row->win_amount * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤

                    $colSql="report_uid,bet_date,set_date,bet_amount,set_amount,win_amount,type_id,account,game,status_id,content,mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";

                    $sqlStr="REPLACE INTO `cm_report` (".sqlInsertString($colSql,0).")";
                    $sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
                    $parameter[':report_uid']=$row->report_uid;
                    $parameter[':bet_date']=date('Y-m-d H:i:s', $row->bet_date);
                    $parameter[':set_date']=date('Y-m-d H:i:s', $row->set_date);
                    $parameter[':bet_amount']=$row->bet_amount;
                    $parameter[':set_amount']=$row->set_amount;
                    $parameter[':win_amount']=$row->win_amount;
                    $parameter[':type_id']=$row->type_id;
                    $parameter[':account']=$row->account;
                    $parameter[':game']=$row->game;
                    $parameter[':status_id']=$row->status_id;
                    $parameter[':content']=$row->html;

                    $parameter[':mem_num']=$mem_num;
                    $parameter[':u_power4']=$u_power4;
                    $parameter[':u_power5']=$u_power5;
                    $parameter[':u_power6']=$u_power6;
                    $parameter[':u_power4_profit']=$u_power4_profit;
                    $parameter[':u_power5_profit']=$u_power5_profit;
                    $parameter[':u_power6_profit']=$u_power6_profit;
                    $this->webdb->sqlExc($sqlStr,$parameter);
                }
            }
        }
    }
    //手動補帳
    public function auto_report(){
        if(!$this->agent->is_referral()){
            if($this->input->is_ajax_request()){
                $report_count=$this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true));
                echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！'));
            }else{
                echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
            }
        }else{
            echo json_encode(array('RntCode'=>'N','Msg'=>'網域不被允許'));
        }
    }

}