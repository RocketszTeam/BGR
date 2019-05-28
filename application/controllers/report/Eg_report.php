<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
define("account_prefix", "BGR");    //遊戲帳號前綴
class Eg_report extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->library('api/egapi');
        date_default_timezone_set("Asia/Taipei");
    }

    public function index(){
        $sTime = "2019-05-03 19:00:00";
        $eTime = "2019-05-03 20:00:00";
        $this->get_report($sTime,$eTime);

//        $starttime = "2019-04-30 00:00:00";
//        for($i = 16; $i <= 23; $i++) {
//            echo date("Y-m-d H:i:s", strtotime($starttime . " +" . $i . " hour")) . "~" . date("Y-m-d H:i:s", strtotime($starttime . " +" . ($i + 1) . " hour"));
//            //$result=$this->allbetapi->reporter_all("2019-04-03 03:00:00","2019-04-03 04:00:00");
//            $this->get_report(date("Y-m-d H:i:s", strtotime($starttime . " +" . $i . " hour")), date("Y-m-d H:i:s", strtotime($starttime . " +" . ($i + 1) . " hour")));
//        }
    }

    //透過下注時間補單據
    public function get_report($sTime=NULL,$eTime=NULL){
        //更新今日
        if($sTime==NULL && $eTime==NULL){
            $sTime=date('Y-m-d H:i:s',strtotime($eTime."-10 min"));
            $eTime=date('Y-m-d H:i:s');
        }
        $result = $this->egapi->reporter_all($sTime,$eTime);
        //$result = json_decode($result['response']);
//        echo $sTime.'---'.$eTime;
//        echo "<pre>";
//        print_r($result);
//        echo "<hr>";
        //$this->intoDB($result);
        $uid = "";
        if(isset($result)){
            if(count($result->result->data) > 0){
                foreach($result->result->data as $row){
                    //回傳帳號需要去掉尾墜
                    $user_name=explode('@',$row->userId);
                    $user_name=reset($user_name);
                    if( !strpos(" ".$uid, "'".$user_name."'") and substr($user_name, 0, 2) == account_prefix ){ //需判斷遊戲前綴不然全部站會員下注都會抓到
                        $uid .= "'".$user_name."',";
                    }
                }
                $uid = substr($uid,0,strlen($uid)-1);

                if(count($result->result->data) && strlen($uid) > 1){
                    $sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=64";

                    $rowAll=$this->webdb->sqlRowList($sqlStr);
                    $adminsql = "SELECT num,root,percent from admin";
                    $AdminList = $this->webdb->sqlRowList($adminsql);

                    if($rowAll!=NULL){
                        foreach($result->result->data as $row){
                            $u_power4 = 0;
                            $u_power5 = 0;
                            $u_power6 = 0;
                            $u_power4_profit=0;
                            $u_power5_profit=0;
                            $u_power6_profit=0;
                            $mem_num=0;

                            //回傳帳號需要去掉尾墜
                            $user_name=explode('@',$row->userId);
                            $user_name=reset($user_name);
                            if(substr($user_name, 0, 2) == account_prefix) {
                                $winOrLoss = 0;    //預設輸贏=0
//							if(isset($row->returnamount)){	//無派彩金額 代表未結單 有派彩金額才算出輸贏金額
//								$winOrLoss=(float)$row->returnamount - (float)$row->betamount;
//							}
                                if (!empty($row->winLoss)) {
                                    $winOrLoss = (float)$row->winLoss * 1;
                                }

                                //取出會員代理總代代理編號
                                for ($i = 0; $i < count($rowAll); $i++) {
                                    //$this->data[$k]=$v;
                                    if (strcasecmp($rowAll[$i]["u_id"], $user_name) == 0) {    //因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
                                        $mem_num = $rowAll[$i]["mem_num"];    //取出會員編號
                                        $u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
                                        break;
                                    }
                                }
                                //代理分潤、總代編號
                                for ($i = 0; $i < count($AdminList); $i++) {
                                    if ($AdminList[$i]["num"] == $u_power6) {
                                        $u_power6_profit = round((float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100), 2);    //股東分潤
                                        $u_power5 = $AdminList[$i]["root"];
                                        break;
                                    }
                                }
                                //總代分潤、股東編號
                                for ($i = 0; $i < count($AdminList); $i++) {
                                    if ($AdminList[$i]["num"] == $u_power5) {
                                        $u_power5_profit = round((float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100), 2);    //股東分潤
                                        $u_power4 = $AdminList[$i]["root"];
                                        break;
                                    }
                                }
                                //股東分潤
                                for ($i = 0; $i < count($AdminList); $i++) {
                                    if ($AdminList[$i]["num"] == $u_power4) {
                                        $u_power4_profit = round((float)$winOrLoss * ((float)$AdminList[$i]["percent"] / 100), 2);    //股東分潤
                                        break;
                                    }
                                }
                                //寫入DB
                                $parameter['id'] = $row->id;
                                $parameter['userId'] = $row->userId;
                                $parameter['betAmount'] = $row->betAmount * 1;
                                $parameter['validBetAmount'] = $row->validBetAmount * 1;
                                $parameter['win'] = $row->win;
                                $parameter['winLoss'] = $winOrLoss;
                                $parameter['betTime'] = $row->betTime;
                                $parameter['hitFish'] = $row->hitFish;
                                $parameter['catchFish'] = $row->catchFish;
                                $parameter['type'] = $row->type;
                                $parameter['mem_num'] = $mem_num;
                                $parameter['u_power4'] = $u_power4;
                                $parameter['u_power5'] = $u_power5;
                                $parameter['u_power6'] = $u_power6;
                                $parameter['u_power4_profit'] = $u_power4_profit;
                                $parameter['u_power5_profit'] = $u_power5_profit;
                                $parameter['u_power6_profit'] = $u_power6_profit;

//							if($type==0){	//已結算注單撈取複寫原本的
//								$this->webdb->sqlReplace('eg_report',$parameter);
//							}else{	//未結算單據則不複寫
                                $this->webdb->sqlReplace('eg_report', $parameter);
//							}
                            }
                        }
                    }
                }
                //繼續往下撈分頁
//				if(count($result->data) > $result->page_size && isset($result->cursor)){
//					$this->get_report($sTime,$eTime,$type,$result->cursor);
//				}
            }
        }

    }

    //透過結算時間去補單據
    public function update_report($sTime=NULL,$eTime=NULL){
        //更新今日
        if($sTime==NULL && $eTime==NULL){
            $sTime=date('Y-m-d H:i:s',strtotime("-10 min"));
            $eTime=date('Y-m-d H:i:s');
        }
        $result=$this->get_report($sTime,$eTime);
    }

    //手動補帳
    public function auto_report(){
        if(!$this->agent->is_referral()){
            if($this->input->is_ajax_request()){
                if($this->input->post('sTime')!='' && $this->input->post('eTime')!=''){
                    if((strtotime($this->input->post('eTime')) - strtotime($this->input->post('sTime'))) <= 3600){
                        date_default_timezone_set("Asia/Taipei");
                        $this->get_report($this->input->post('sTime',true),$this->input->post('eTime',true));	//先撈未結單
                        $this->update_report($this->input->post('sTime',true),$this->input->post('eTime',true));	//在撈已結單
                        echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！'));
                    }else{
                        echo json_encode(array('RntCode'=>'Y','Msg'=>'該遊戲補單時間須小於等於 1小時！'));
                    }
                }else{
                    echo json_encode(array('RntCode'=>'N','Msg'=>'請設定補帳日期'));
                }
            }else{
                echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
            }
        }else{
            echo json_encode(array('RntCode'=>'N','Msg'=>'網域不被允許'));
        }
    }

}

?>