<?php
/**
 * Created by PhpStorm.
 * User: Tiger
 * Date: 2018/8/7
 * Time: 上午 10:12
 */
include_once (dirname(__FILE__)."/Core_controller.php");
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends Core_controller{

    public function __construct(){
        parent::__construct();
    }

    public function index($groupID=0){
        if(!$this->isLogin()){	//檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
            $this -> _setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $this->data['groupID']= $groupID;
        $this->data["editBTN"]=SYSTEM_URL."/payment/edit/";
        $this->data["delBTN"]=SYSTEM_URL."/payment/delete/";


        //金流設定
        $sqlStr="select * from `payment_config` where `m_group`=?";
        $this->data["row"]=$this->webdb->sqlRowList($sqlStr, array(':m_group'=>$groupID));
        $this -> data["body"] = $this -> load -> view("admin/payment/index", $this -> data,true);
        $this -> load -> view("admin/main", $this -> data);
    }
    function create($groupID=0, $paymentType='atmncvs'){	//新增選項
        if(!$this->isLogin()){	//檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
            $this -> _setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $this->data['groupID']= $groupID;
        $this->data['paymentType']= $paymentType;
        $this->data['patchFlag'] = $this->input->get('patchFlag') | $this->input->post('patchFlag');
        if($_POST){	//新增資料
            $colSql="enable,libs,gatewayName,merchant,HashKey,HashIV,validate,paymentType,m_group,amount,buildDate";
            $sqlStr="INSERT INTO `payment_config` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
            $parameter = $this->getParam();
            $parameter[":buildDate"] = date("Y-m-d H:i:s");



            if(!empty($this->data['patchFlag'])){
                $b = FALSE;
                $m_group = $parameter[":m_group"];
                foreach(memberGroup() as $key => $val){
                    $parameter[":m_group"] = $key;
                    if(!$this->webdb->sqlExc($sqlStr,$parameter)){
                        $b = TRUE;
                        break;
                    }
                }
                if($b){
                    $msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
                    $this -> _setMsgAndRedirect($msg, current_url());
                }else{
                    $msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
                    $this -> _setMsgAndRedirect($msg, SYSTEM_URL."/payment/index/".$m_group);
                }
            }else{
                if(!$this->webdb->sqlExc($sqlStr,$parameter)){
                    $msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
                    $this -> _setMsgAndRedirect($msg, current_url());
                }else{
                    $colSql="admin_num,word,buildtime";
                    $sqlStr="INSERT INTO `payment_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
                    $para[":admin_num"] = $this->web_root_num;
                    $para[":word"] = '新增金流：'.$parameter[":gatewayName"];
                    $para[":buildtime"] = date("Y-m-d H:i:s");
                    $this->webdb->sqlExc($sqlStr,$para);

                    $msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
                    $this -> _setMsgAndRedirect($msg, SYSTEM_URL."/payment/index/".$parameter[":m_group"]);
                }
            }
        }else{
            $sqlStr="select * from `payment_gateway` where ".$paymentType." and ?";
            $this->data["getewayList"]=$this->webdb->sqlRowList($sqlStr, array(':'.$paymentType.''=>pow(2, $groupID)));

            $this->data["subtitle"] = $this->getsubtitle();    //取得當前頁面名稱
            $this->data["formAction"] = site_url(SYSTEM_URL . "/payment/create");
            $this->data["cancelBtn"] = site_url(SYSTEM_URL . "/payment/index/".$groupID);
            $this->data["todo"] = "add";
            $this->data["body"] = $this->load->view("admin/payment/form", $this->data, true);
            $this->load->view("admin/main", $this->data);
        }
    }
    public function edit($uid = NULL){
        if(!$this->isLogin()){	//檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
            $this -> _setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $sqlStr = "select * from `payment_config` where uid=?";
        $row = $this->webdb->sqlRow($sqlStr, array(':uid' => $uid), "payment_config");
        if ($row == NULL) {
            $msg = array("type" => "warning", 'title' => '查無資料！');
            $this->_setMsgAndRedirect($msg, SYSTEM_URL . "/payment/index");
        } else {
            if ($_POST) {    //修改資料
                $colSql = "enable,libs,gatewayName,merchant,HashKey,HashIV,validate,paymentType,m_group,amount";
                $sqlStr = "UPDATE `payment_config` SET " . sqlUpdateString($colSql);
                $sqlStr .= " where uid=?";
                $parameter = $this->getParam();
                $parameter[":uid"] = $uid;
                $this->webdb->sqlExc($sqlStr, $parameter);


                $colSql="admin_num,word,buildtime";
                $sqlStr="INSERT INTO `payment_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
                $para[":admin_num"] = $this->web_root_num;
                $para[":word"] = '修改金流：'.$parameter[":gatewayName"];
                $para[":buildtime"] = date("Y-m-d H:i:s");
                $this->webdb->sqlExc($sqlStr,$para);

                $msg = array("type" => "success", 'title' => '修改成功！');
                $this->_setMsgAndRedirect($msg, SYSTEM_URL . "/payment/index/".$parameter[":m_group"]);

            } else {

                $this->data['groupID']= $groupID= $row['m_group'];
                $this->data['paymentType']= $paymentType= $row['paymentType'];
                $sqlStr="select * from `payment_gateway` where ".$paymentType." and ?";
                $this->data["getewayList"]=$this->webdb->sqlRowList($sqlStr, array(':'.$paymentType.''=>pow(2, $groupID)));

                $this->data["formAction"] = site_url(SYSTEM_URL . "/payment/edit/".$uid);
                $this->data["cancelBtn"] = site_url(SYSTEM_URL . "/payment/index/".$groupID);
                $this->data["subtitle"] = $this->getsubtitle();    //取得當前頁面名稱
                $this->data["todo"] = "edit";
                $this->data["row"] = $row;
                $this->data["body"] = $this->load->view("admin/payment/form", $this->data, true);
                $this->load->view("admin/main", $this->data);
            }

        }
    }
    function delete($uid=NULL){
        if(!$this->isLogin()){	//檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
            $this -> _setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $sqlStr="select * from `payment_config` where uid=?";
        $row=$this->webdb->sqlRow($sqlStr,array(':uid'=>$uid));
        if($row==NULL){
            $msg=array("type" => "warning", 'title' => '查無資料！');
            $this -> _setMsgAndRedirect($msg,SYSTEM_URL."/payment/index");
        }else{
            if(in_array($row["uid"],$this->data["sysGroup"])){
                $msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
                $this -> _setMsgAndRedirect($msg,SYSTEM_URL."/payment/index");
            }else{
                $sqlStr = "select * from `payment_config` where uid=?";
                $row = $this->webdb->sqlRow($sqlStr, array(':uid' => $uid), "payment_config");

                $sqlStr="delete from `payment_config` where uid=?";
                $this->webdb->sqlExc($sqlStr,array(':uid'=>$uid));

                $colSql="admin_num,word,buildtime";
                $sqlStr="INSERT INTO `payment_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
                $para[":admin_num"] = $this->web_root_num;
                $para[":word"] = '刪除金流：'.$row["gatewayName"];
                $para[":buildtime"] = date("Y-m-d H:i:s");
                $this->webdb->sqlExc($sqlStr,$para);

                $msg=array("type" => "success",'title' => '刪除成功！');
                $this -> _setMsgAndRedirect($msg,SYSTEM_URL."/payment/index/".$row['m_group']);
            }
        }

    }
    private function getParam(){
        $parameter = array();
        $parameter[":enable"] = (int)$this->input->post("enable", true);
        $parameter[":libs"] = trim($this->input->post("libs", true));
        $sqlStr="select * from `payment_gateway` where `libs`=?";
        $row=$this->webdb->sqlRow($sqlStr,array(':libs'=>$parameter[":libs"]));
        $parameter[":gatewayName"] = $row["gatewayName"];

        $parameter[":merchant"] = trim($this->input->post("merchant", true));
        $parameter[":HashKey"] = trim($this->input->post("HashKey", true));
        $parameter[":HashIV"] = trim($this->input->post("HashIV", true));
        $parameter[":validate"] = trim($this->input->post("validate", true));
        $parameter[":paymentType"] = trim($this->input->post("paymentType", true));;
        $parameter[":m_group"] = (int)$this->input->post("m_group", true);
        $parameter[":amount"] = (int)$this->input->post("amount", true);
        return $parameter;
    }
    public function enableChange(){
        if($this->input->is_ajax_request()){
            $parameter=array();
            $sqlStr="UPDATE `payment_config` SET `enable`=? where uid=?";
            $parameter[":enable"]=$this->input->post("enable",true);
            $parameter[":uid"]=$this->input->post("uid",true);
            $this->webdb->sqlExc($sqlStr,$parameter);


            $sqlStr = "select * from `payment_config` where uid=?";
            $row = $this->webdb->sqlRow($sqlStr, array(':uid' => $parameter[":uid"]), "payment_config");

            $colSql="admin_num,word,buildtime";
            $sqlStr="INSERT INTO `payment_log` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
            $para[":admin_num"] = $this->web_root_num;
            $para[":word"] = (($parameter[":enable"] == 1) ? '開啟': '關閉').'金流：'.$row["gatewayName"];
            $para[":buildtime"] = date("Y-m-d H:i:s");
            $this->webdb->sqlExc($sqlStr,$para);
        }
    }
}