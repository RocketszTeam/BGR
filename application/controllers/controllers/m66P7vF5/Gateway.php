<?php
/**
 * Created by PhpStorm.
 * User: Tiger
 * Date: 2018/8/7
 * Time: 上午 10:12
 */
include_once (dirname(__FILE__)."/Core_controller.php");
defined('BASEPATH') OR exit('No direct script access allowed');

class Gateway extends Core_controller{

    public function __construct(){
        parent::__construct();
        if(!$this->isLogin()){	//檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
            $this -> _setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
    }
    public function index(){ //金流平台設定
        $parameter=array();
        $sqlStr="select * from `payment_gateway`";
        $rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);
        $this->data["result"]=$rowAll;

        $this->data["editBTN"]=SYSTEM_URL.'/gateway/edit/';
        $this->data["delBTN"]=SYSTEM_URL.'/gateway/delete/';

        $this -> data["body"] = $this -> load -> view("admin/gateway/index", $this -> data,true);
        $this -> load -> view("admin/main", $this -> data);
    }
    function create(){	//新增選項
        if($_POST){	//新增資料
            $colSql="gatewayName,gatewayUrl,atmncvs,credit,libs";
            $sqlStr="INSERT INTO `payment_gateway` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
            $parameter = $this->getParam();
            if(!$this->webdb->sqlExc($sqlStr,$parameter)){
                $msg = array("type" => "danger", 'title' => '新增失敗！','content'=>'請聯絡系統管理員.');
                $this -> _setMsgAndRedirect($msg, current_url());
            }else{
                $msg = array("type" => "success", 'title' => '新增成功！', 'content' => '至列表查看新增之資料');
                $this -> _setMsgAndRedirect($msg, SYSTEM_URL."/gateway/index/");
            }
        }else{
            $this->data["subtitle"] = $this->getsubtitle();    //取得當前頁面名稱
            $this->data["formAction"] = site_url(SYSTEM_URL . "/gateway/create");
            $this->data["cancelBtn"] = site_url(SYSTEM_URL . "/gateway/index");
            $this->data["todo"] = "add";
            $this->data["body"] = $this->load->view("admin/gateway/form", $this->data, true);
            $this->load->view("admin/main", $this->data);
        }

    }
    function edit($uid){
        $sqlStr = "select * from `payment_gateway` where uid=?";
        $row = $this->webdb->sqlRow($sqlStr, array(':uid' => $uid), "payment_gateway");
        if ($row == NULL) {
            $msg = array("type" => "warning", 'title' => '查無資料！');
            $this->_setMsgAndRedirect($msg, SYSTEM_URL . "/gateway/index");
        } else {
            if ($_POST) {    //修改資料
                $colSql = "gatewayName,gatewayUrl,atmncvs,credit,libs";
                $sqlStr = "UPDATE `payment_gateway` SET " . sqlUpdateString($colSql);
                $sqlStr .= " where uid=?";
                $parameter = $this->getParam();
                $parameter[":uid"] = $uid;
                $this->webdb->sqlExc($sqlStr, $parameter);

                $msg = array("type" => "success", 'title' => '修改成功！');
                $this->_setMsgAndRedirect($msg, SYSTEM_URL . "/gateway/index");

            } else {

                $this->data["formAction"] = site_url(SYSTEM_URL . "/gateway/edit/".$uid);
                $this->data["cancelBtn"] = site_url(SYSTEM_URL . "/gateway/index");
                $this->data["subtitle"] = $this->getsubtitle();    //取得當前頁面名稱
                $this->data["todo"] = "edit";
                $this->data["row"] = $row;
                $this->data["body"] = $this->load->view("admin/gateway/form", $this->data, true);
                $this->load->view("admin/main", $this->data);

            }

        }
    }
    function delete($uid=NULL){
        $sqlStr="select * from `payment_gateway` where uid=?";
        $row=$this->webdb->sqlRow($sqlStr,array(':uid'=>$uid));
        if($row==NULL){
            $msg=array("type" => "warning", 'title' => '查無資料！');
            $this -> _setMsgAndRedirect($msg,SYSTEM_URL."/gateway/index");
        }else{
            if(in_array($row["uid"],$this->data["sysGroup"])){
                $msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...此為系統帳號無法刪除');
                $this -> _setMsgAndRedirect($msg,SYSTEM_URL."/gateway/index");
            }else{
                $sqlStr="delete from `payment_gateway` where uid=?";
                $this->webdb->sqlExc($sqlStr,array(':uid'=>$uid));
                $msg=array("type" => "success",'title' => '刪除成功！');
                $this -> _setMsgAndRedirect($msg,SYSTEM_URL."/gateway/index");
            }
        }

    }
    private function getParam(){
        $parameter = array();
        $parameter[":gatewayName"] = trim($this->input->post("gatewayName", true));
        $parameter[":gatewayUrl"] = trim($this->input->post("gatewayUrl", true));
        $sum = 0;
        $atmncvs = $this->input->post("atmncvs", true);
        foreach ($atmncvs as $k => $v)$sum += $v;
        $parameter[":atmncvs"] = $sum;


        $sum = 0;
        $credit = $this->input->post("credit", true);
        foreach ($credit as $k => $v)$sum += $v;
        $parameter[":credit"] = $sum;

        $parameter[":libs"] = trim($this->input->post("libs", true));
        return $parameter;
    }
}