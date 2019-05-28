<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Maya_report extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->library('api/maya');	//QT電子
        date_default_timezone_set("Asia/Taipei");
    }

    public function index(){

        $sTime='2018-03-21 00:00:00';
        $eTime='2018-03-21 23:59:59';
        //$sTime=date('Y-m-d\TH:i:s',strtotime($sTime));
        //$eTime=date('Y-m-d\TH:i:s',strtotime($eTime));
        $GameIDList=$this->maya->getGameID();
        $GameIDList=array_keys($GameIDList);
        foreach($GameIDList as $GameID){
            $this->get_report($sTime,$eTime,$GameID,1,100);
        }
    }

    public function get_report($StartGameSequenceID=0){
        if($StartGameSequenceID==0){
            $sqlStr="select `GameSequenceID` from `maya_report` order by `GameSequenceID` DESC Limit 1";
            $row=$this->webdb->sqlRow($sqlStr);
            if($row!=NULL){
                $StartGameSequenceID=$row["GameSequenceID"];
            }
        }

        //echo $StartGameSequenceID;

        $result=$this->maya->reporter_all2($StartGameSequenceID);
        //echo '<pre>';
        //print_r($result);
        return $this->intoDB($result);

    }


    private function intoDB($result){
        $uid = "";
        if(isset($result)){
            if(count($result) > 0){

                foreach($result as $row){
                    if( !strpos(" ".$uid, "'".$row->VenderMemberID."'") ){
                        $uid .= "'".$row->VenderMemberID."',";
                    }
                }
                $uid = substr($uid,0,strlen($uid)-1);
                if(count($result) && strlen($uid) > 1){
                    $sqlStr="select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in (".$uid.") and gamemaker_num=33";

                    $rowAll=$this->webdb->sqlRowList($sqlStr);
                    $adminsql = "SELECT num,root,percent from admin";
                    $AdminList = $this->webdb->sqlRowList($adminsql);
                    if($rowAll!=NULL){
                        foreach($result as $row){
                            $u_power4 = 0;
                            $u_power5 = 0;
                            $u_power6 = 0;
                            $u_power4_profit=0;
                            $u_power5_profit=0;
                            $u_power6_profit=0;
                            $mem_num=0;


                            //取出會員代理總代代理編號
                            for($i=0; $i<count($rowAll); $i++){
                                //$this->data[$k]=$v;
                                if(strcasecmp($rowAll[$i]["u_id"],$row->VenderMemberID) == 0){	//因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
                                    $mem_num=$rowAll[$i]["mem_num"];	//取出會員編號
                                    $u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
                                    break;
                                }
                            }
                            //代理分潤、總代編號
                            for($i=0; $i<count($AdminList); $i++){
                                if($AdminList[$i]["num"] == $u_power6){
                                    $u_power6_profit = round( (float)$row->WinLoseMoney * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
                                    $u_power5 = $AdminList[$i]["root"];
                                    break;
                                }
                            }
                            //總代分潤、股東編號
                            for($i=0; $i<count($AdminList); $i++){
                                if($AdminList[$i]["num"] == $u_power5){
                                    $u_power5_profit = round( (float)$row->WinLoseMoney * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
                                    $u_power4 = $AdminList[$i]["root"];
                                    break;
                                }
                            }
                            //股東分潤
                            for($i=0; $i<count($AdminList); $i++){
                                if($AdminList[$i]["num"] == $u_power4){
                                    $u_power4_profit = round( (float)$row->WinLoseMoney * ((float)$AdminList[$i]["percent"] / 100) ,2);	//股東分潤
                                    break;
                                }
                            }
                            //寫入DB
                            $parameter['BetNo']=$row->BetNo;
                            $parameter['GameSequenceID']=$row->GameSequenceID;
                            $parameter['VenderMemberID']=(isset($row->VenderMemberID) ? $row->VenderMemberID : NULL);
                            $parameter['GameMemberID']=$row->GameMemberID;
                            $parameter['BetDateTime']=$row->BetDateTime;
                            $parameter['CountDateTime']=$row->CountDateTime;
                            $parameter['AccountDateTime']=$row->AccountDateTime;
                            $parameter['GameID']=$row->GameID;
                            $parameter['GameNo']=$row->GameNo;
                            $parameter['TableCode']=$row->TableCode;
                            $parameter['BetMoney']=$row->BetMoney;
                            $parameter['ValidBetMoney']=$row->ValidBetMoney;
                            $parameter['WinLoseMoney']=$row->WinLoseMoney;
                            $parameter['Handsel']=$row->Handsel;
                            $parameter['BetDetail']=$row->BetDetail;
                            $parameter['State']=$row->State;
                            $parameter['BetType']=$row->BetType;
                            $parameter['Terminal']=$row->Terminal;
                            $parameter['IsChange']=$row->IsChange;
                            $parameter['GameResult']=(isset($row->GameResult) ? serialize($row->GameResult) : NULL);

                            $parameter['mem_num']=$mem_num;
                            $parameter['u_power4']=$u_power4;
                            $parameter['u_power5']=$u_power5;
                            $parameter['u_power6']=$u_power6;
                            $parameter['u_power4_profit']=$u_power4_profit;
                            $parameter['u_power5_profit']=$u_power5_profit;
                            $parameter['u_power6_profit']=$u_power6_profit;
                            $this->webdb->sqlReplace('maya_report',$parameter);


                        }
                    }
                }

            }
        }
        return count($result);
    }


    //手動補帳
    public function auto_report(){
        if(!$this->agent->is_referral()){
            if($this->input->is_ajax_request()){
                if($this->input->post('sTime')!='' && $this->input->post('eTime')!=''){
                    date_default_timezone_set("Asia/Taipei");
                    $report_count=$this->get_report();
                    //echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！'));
                    echo json_encode(array('RntCode'=>'Y','Msg'=>'補帳完畢！共更新了'. $report_count .'筆帳目','data_count'=> $report_count));
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