<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once(dirname(__FILE__) . "/Core_controller.php");

class Report extends Core_controller
{

    public function __construct()
    {
        parent::__construct();
        error_reporting(0);
        date_default_timezone_set("Asia/Taipei");
        $this->load->library('api/allbetapi');    //歐博API
        $this->load->library('api/sagamingapi');    //沙龍
        $this->load->library('api/superapi');    //Super
        $this->load->library('api/dreamgame');    //Dg
        $this->load->library('api/wmapi');    //wm
        $this->load->library('api/pk10');    //PK10

        //本週
        $toWeek = reportDate('tw');
        $this->data["toWeek"] = array('d1' => $toWeek['d1'], 'd2' => $toWeek['d2']);
        //上周
        $yeWeek = reportDate('yw');
        $this->data["yeWeek"] = array('d1' => $yeWeek['d1'], 'd2' => $yeWeek['d2']);
        //本月
        $toMonth = reportDate('m');
        $this->data["toMonth"] = array('d1' => $toMonth['d1'], 'd2' => $toMonth['d2']);
        //上月
        $ymMonth = reportDate('ym');
        $this->data["ymMonth"] = array('d1' => $ymMonth['d1'], 'd2' => $ymMonth['d2']);
    }

    //歐博報表
    public function index($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `allbet_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `betTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `betTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }

        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `allbet_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `betTime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `betTime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`client` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/index/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/allbet_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/index/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/index", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //歐博會員明細
    public function allbet_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `allbet_report` where 1=1";
        $sqlSum = "select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss from `allbet_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `betTime` >=?";
            $sqlSum .= " and `betTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `betTime` <= ?";
            $sqlSum .= " and `betTime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/allbet_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `betTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameTypeArray"] = array(
            '101' => '普通百家樂', '102' => 'VIP百家樂',
            '103' => '急速百家樂', '104' => '競咪百家樂',
            '201' => '骰寶', '301' => '龍虎', '401' => '輪盤'
        );

        $this->data["betTypeArray"] = $this->allbetapi->get_betType();
        $this->data["gameResultArray"] = $this->allbetapi->get_gameResult();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/index/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/allbet_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //歐博電子報表
    public function allbet_egame($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself


            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `allbet_egame_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `betTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `betTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `allbet_egame_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `betTime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `betTime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`client` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }
        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself
        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/allbet_egame/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/allbet_egame_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/allbet_egame/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/allbet_egame", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //歐博電子會員明細
    public function allbet_egame_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `allbet_egame_report` where 1=1";
        $sqlSum = "select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss,SUM(jackpotBetAmount) as TotaljackpotBetAmount";
        $sqlSum .= ",SUM(jackpotValidAmount) as TotaljackpotValidAmount,SUM(jackpotWinOrLoss) as TotaljackpotWinOrLoss";
        $sqlSum .= " from `allbet_egame_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `betTime` >=?";
            $sqlSum .= " and `betTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `betTime` <= ?";
            $sqlSum .= " and `betTime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/allbet_egame_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `betTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型

        $this->data["gameTypeArray"] = array(
            '501' => '桌面遊戲', '502' => '老虎機遊戲',
            '503' => 'P2P對戰遊戲', '504' => '迷你老虎機遊戲',
            '505' => '迷你棋牌遊戲', '702' => '桌面遊戲', '703' => '老虎機遊戲',
            '704' => '街機遊戲', '801' => '對戰遊戲', '802' => '老虎機遊戲',
            '803' => 'Casino遊戲', '1000' => '魚樂無窮', '1100' => '空戰世紀'
        );


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/allbet_egame/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/allbet_egame_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //DG報表
    public function dreamgame($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(betPoints) as TotalbetAmount,SUM(availableBet) as TotalvalidAmount,SUM(totalWinlose) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `dreamgame_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `betTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `betTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(betPoints) as TotalbetAmount,SUM(availableBet) as TotalvalidAmount,SUM(totalWinlose) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `dreamgame_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `betTime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `betTime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`userName` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }
        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/dreamgame/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/dreamgame_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/dreamgame/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;

        $this->data["body"] = $this->load->view("admin/report/dreamgame", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //dg會員明細
    public function dreamgame_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `dreamgame_report` where 1=1";
        $sqlSum = "select SUM(betPoints) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss,SUM(availableBet) as TotalvalidAmount from `dreamgame_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }

        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `betTime` >=?";
            $sqlSum .= " and `betTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `betTime` <= ?";
            $sqlSum .= " and `betTime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關


        $config['base_url'] = site_url(SYSTEM_URL . "/Report/dreamgame_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");


        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `betTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);


        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);

        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameTypeArrays"] = array(
            '1' => '百家樂', '3' => '龍虎',
            '5' => '骰寶',
            '7' => '牛牛', '4' => '輪盤',
            '8' => '競咪百家樂'
        );


        $this->data["betTypeArray"] = $this->dreamgame->getBetType();


        $this->data["root"] = $admin_num;


        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/dreamgame/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/dreamgame_details", $this->data, true);


        $this->load->view("admin/main", $this->data);
        //$this -> load -> view("www/View",$this->data);
    }

    //WM報表
    public function WM($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        /* add to showself */
        $showself = false;
        $whereSql = '';

        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);

        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }

        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(bet) as TotalbetAmount,SUM(winLoss) as TotalwinOrLoss,SUM(validbet) as TotalvalidAmount,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }

            $sqlStr .= " from `wm_report`  where 1=1" . $whereSql;
            $parameter2 = array();

            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `betTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `betTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }

            $sqlStr .= " group by agent_num";
            //echo "<!--".$sqlStr."-->";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["AgProfit"] = (@$row["TotalProfit"] != "" ? $row["AgTotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

            /*
			$data=array();
			$data["num"]=$row["num"];
			$data["u_id"]=$row["u_id"];
			$data["u_power"]=$row["u_power"];
			$report_row=$this->webdb->sqlRow($sqlStr,$parameter2);

			$data["betAmount"]=($report_row["TotalbetAmount"]!="" ? $report_row["TotalbetAmount"] : 0);
			$data["validAmount"]=($report_row["TotalvalidAmount"]!="" ? $report_row["TotalvalidAmount"] : 0);
			$data["winOrLoss"]=($report_row["TotalwinOrLoss"]!="" ? $report_row["TotalwinOrLoss"] : 0);
			$data["Profit"]=($report_row["TotalProfit"]!="" ? $report_row["TotalProfit"] : 0);
			array_push($dataList,$data);
			*/

        }

        //代理點進來 改列出會員處理

        if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
            $dataList = array();
            $parameter = array();
            $sqlStr = "select mem_num,SUM(bet) as TotalbetAmount,SUM(winLoss) as TotalwinOrLoss,SUM(validbet) as TotalvalidAmount,Count(*) as totals";
            $sqlStr .= " from `wm_report`  where 1=1";
            if ($root > 0) {
                $sqlStr .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
            }
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `betTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `betTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            //===會員帳號 or 遊戲帳號=======================
            if (@$_REQUEST["find9"] != "") {
                $sqlStr .= " and (`user` like ? or mem_num in(select num from member where u_id like ?))";
                $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
            }

            $sqlStr .= " group by mem_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $root;
                    $data["mem_num"] = $row["mem_num"];
                    $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                    $data["u_power"] = NULL;
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }
        }

        $this->data["root"] = tb_sql("root", "admin", $root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/wm/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/wm_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/wm/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;

        $this->data["body"] = $this->load->view("admin/report/wm", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //WM會員明細
    public function WM_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `wm_report` where 1=1";
        $sqlSum = "select SUM(bet) as TotalbetAmount,SUM(winLoss) as TotalwinOrLoss,SUM(validbet) as TotalvalidAmount from `wm_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }

        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `betTime` >=?";
            $sqlSum .= " and `betTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `betTime` <= ?";
            $sqlSum .= " and `betTime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/wm_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `BetTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameTypeArrays"] = array(
            '1' => '百家樂', '3' => '龍虎',
            '5' => '骰寶',
            '7' => '牛牛', '4' => '輪盤',
            '8' => '競咪百家樂'
        );


        //$this->data["betTypeArray"]=$this->dreamgame->getBetType();


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/wm/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));
        $this->data["body"] = $this->load->view("admin/report/wm_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //沙龍報表
    public function sagame($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(BetAmount) as TotalbetAmount,SUM(ValidAmount) as TotalvalidAmount,SUM(ResultAmount) as TotalwinOrLoss,Count(BetID) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `sagame_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `PayoutTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `PayoutTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }

        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(ResultAmount) as TotalwinOrLoss,SUM(ValidAmount)  as TotalvalidAmount ,count(BetID) as totals";
                $sqlStr .= " from `sagame_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `PayoutTime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `PayoutTime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`Username` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/sagame/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/sagame_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/sagame/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/sagame", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //沙龍會員明細
    public function sagame_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `sagame_report` where 1=1";
        $sqlSum = "select SUM(BetAmount) as TotalbetAmount,SUM(ResultAmount) as TotalwinOrLoss,SUM(ValidAmount) as TotalvalidAmount,count(*) as totals from `sagame_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `PayoutTime` >=?";
            $sqlSum .= " and `PayoutTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `PayoutTime` <= ?";
            $sqlSum .= " and `PayoutTime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/sagame_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `BetTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameTypeArray"] = array(
            'bac'     => '百家樂', 'dtx' => '龍虎',
            'sicbo'   => '骰寶', 'ftan' => '番攤',
            'slot'    => '電子遊戲', 'rot' => '輪盤',
            'lottery' => '48彩', 'minigame' => '小遊戲'
        );

        $this->data["betTypeArray"] = $this->sagamingapi->getBetType();


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/sagame/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));
        $this->data["body"] = $this->load->view("admin/report/sagame_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //super 體育報表
    public function super($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount,SUM(result_gold) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                //$whereSql.=" and `u_power4`=?";
                //$parameter[':u_power4']=$root;
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                //$whereSql.=" and `u_power5`=?";
                //$parameter[':u_power5']=$root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `super_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `m_date` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `m_date` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount,SUM(result_gold) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `super_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `m_date` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `m_date` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`m_id` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }


        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself
        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/super/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/super_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/super/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/super", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //Super體育會員明細
    public function super_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `super_report` where 1=1";
        $sqlSum = "select SUM(gold) as TotalbetAmount,SUM(bet_gold) as TotalvalidAmount";
        $sqlSum .= ",SUM(result_gold) as TotalwinOrLoss from `super_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `m_date` >=?";
            $sqlSum .= " and `m_date` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `m_date` <= ?";
            $sqlSum .= " and `m_date` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/super_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `m_date` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();


        $this->data["gType"] = $this->superapi->gTypeArr();
        $this->data["fashionArr"] = $this->superapi->fashionArr();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/super/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/super_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //捕魚機報表
    public function fish($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(bet) as TotalbetAmount,SUM(profit) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                //$whereSql.=" and `u_power4`=?";
                //$parameter[':u_power4']=$root;
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                //$whereSql.=" and `u_power5`=?";
                //$parameter[':u_power5']=$root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `fish_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `bettimeStr` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `bettimeStr` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(bet) as TotalbetAmount,SUM(profit) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `fish_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `bettimeStr` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `bettimeStr` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`accountno` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }
        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/fish/";
        //$this->data["memberURL"]=SYSTEM_URL."/Report/super_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/fish/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/fish", $this->data, true);
        $this->load->view("admin/main", $this->data);

    }

    //Qt電子報表
    public function qt($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(totalBet) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                //$whereSql.=" and `u_power4`=?";
                //$parameter[':u_power4']=$root;
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                //$whereSql.=" and `u_power5`=?";
                //$parameter[':u_power5']=$root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `qtech_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `initiated` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `initiated` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(totalBet) as TotalbetAmount,SUM(totalWinlose) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `qtech_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `initiated` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `initiated` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`playerId` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself
        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/qt/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/qt_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/qt/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/qt", $this->data, true);
        $this->load->view("admin/main", $this->data);

    }

    //QT電子會員明細
    public function qt_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `qtech_report` where 1=1";

        $sqlSum = "select SUM(totalBet) as TotalbetAmount,SUM(totalPayout) as TotalvalidAmount";
        $sqlSum .= ",SUM(totalWinlose) as TotalwinOrLoss from `qtech_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `initiated` >=?";
            $sqlSum .= " and `initiated` >=?";
            $parameter[':find7'] = $_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `initiated` <= ?";
            $sqlSum .= " and `initiated` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/qt_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `initiated` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);

        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/qt/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/qt_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //贏家體育
    public function ssb($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount,SUM(meresult) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `ssb_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `orderdate` >=?";
                $parameter[':find7'] = date('Ymd', strtotime(@$_REQUEST["find7"]));
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `orderdate` <= ?";
                $parameter[':find8'] = date('Ymd', strtotime(@$_REQUEST["find8"]));
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount,SUM(meresult) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `ssb_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `orderdate` >=?";
                    $parameter[':find7'] = date('Ymd', strtotime(@$_REQUEST["find7"]));
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `orderdate` <= ?";
                    $parameter[':find8'] = date('Ymd', strtotime(@$_REQUEST["find8"]));
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`meusername1` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself
        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/ssb/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/ssb_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/ssb/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/ssb", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //贏家會員明細
    public function ssb_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `ssb_report` where 1=1";

        $sqlSum = "select SUM(gold) as TotalbetAmount,SUM(gold_c) as TotalvalidAmount";
        $sqlSum .= ",SUM(meresult) as TotalwinOrLoss from `ssb_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `orderdate` >=?";
            $sqlSum .= " and `orderdate` >=?";
            $parameter[':find7'] = date('Ymd', strtotime($_REQUEST["find7"]));
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `orderdate` <= ?";
            $sqlSum .= " and `orderdate` <= ?";
            $parameter[':find8'] = date('Ymd', strtotime($_REQUEST["find8"]));
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/ssb_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `orderdate` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);

        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/ssb/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/ssb_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //7PK
    public function s7pk($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                //$whereSql.=" and `u_power4`=?";
                //$parameter[':u_power4']=$root;
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                //$whereSql.=" and `u_power5`=?";
                //$parameter[':u_power5']=$root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `7pk_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `WagersDate` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `WagersDate` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `7pk_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `WagersDate` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `WagersDate` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`Account` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/s7pk/";
        //$this->data["memberURL"]=SYSTEM_URL."/Report/sagame_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/s7pk/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/s7pk", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //太陽神bingo報表
    public function sun_bingo($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(money) as TotalbetAmount,SUM(valid) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
            } else {    //總代身分列出代理
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
            }
            $sqlStr .= " from `bingo_report_copy`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `bet_time` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `bet_time` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
            $dataList = array();
            $parameter = array();
            $sqlStr = "select mem_num,SUM(money) as TotalbetAmount,SUM(valid) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
            $sqlStr .= " from `bingo_report_copy`  where 1=1";
            if ($root > 0) {
                $sqlStr .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
            }
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `bet_time` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `bet_time` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            //===會員帳號 or 遊戲帳號=======================
            if (@$_REQUEST["find9"] != "") {
                $sqlStr .= " and (`client` like ? or mem_num in(select num from member where u_id like ?))";
                $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
            }

            $sqlStr .= " group by mem_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $root;
                    $data["mem_num"] = $row["mem_num"];
                    $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                    $data["u_power"] = NULL;
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }
        }
        $this->data["root"] = tb_sql("root", "admin", $root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/sun_bingo/";
        //$this->data["memberURL"]=SYSTEM_URL."/Report/bingo_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/sun_bingo/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/sun_bingo", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //bingo報表
    public function bingo($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(bet) as TotalbetAmount,SUM(real_bet) as TotalvalidAmount,SUM(win_lose) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `bingo_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `created_at` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `created_at` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(bet) as TotalbetAmount,SUM(real_bet) as TotalvalidAmount,SUM(win_lose) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `bingo_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `created_at` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `created_at` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`account` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/bingo/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/bingo_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/bingo/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/bingo", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //賓果會員明細
    public function bingo_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `bingo_report` where 1=1";
        $sqlSum = "select SUM(bet) as TotalbetAmount,SUM(real_bet) as TotalvalidAmount";
        $sqlSum .= ",SUM(win_lose) as TotalwinOrLoss from `bingo_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `created_at` >=?";
            $sqlSum .= " and `created_at` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `created_at` <= ?";
            $sqlSum .= " and `created_at` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/bingo_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `created_at` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //取得押注類型
        $this->load->library('api/bingo');
        $this->data["betType"] = $this->bingo->getBetType();


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/bingo/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/bingo_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //Super六合彩
    public function slottery($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $whereSql = '';
        $dataList = array();
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(cmount) as TotalbetAmount,SUM(bmount) as TotalvalidAmount,SUM(m_result) as TotalwinOrLoss,(SUM(up_no1_rake) - SUM(up_no1_rake)) as TotalRake,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
            } else {    //總代身分列出代理
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
            }
            $sqlStr .= " from `slottery_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `Bet_date` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `Bet_date` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            //echo $sqlStr;
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    $data["Rake"] = ($row["TotalRake"] != "" ? $row["TotalRake"] : 0);    //代理總退水
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
            $dataList = array();
            $parameter = array();
            $sqlStr = "select mem_num,SUM(cmount) as TotalbetAmount,SUM(bmount) as TotalvalidAmount,SUM(m_result) as TotalwinOrLoss ,count(*) as totals";
            $sqlStr .= " from `slottery_report`  where 1=1";
            if ($root > 0) {
                $sqlStr .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
            }
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `Bet_date` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `Bet_date` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            //===會員帳號 or 遊戲帳號=======================
            if (@$_REQUEST["find9"] != "") {
                $sqlStr .= " and (`account` like ? or mem_num in(select num from member where u_id like ?))";
                $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
            }

            $sqlStr .= " group by mem_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $root;
                    $data["mem_num"] = $row["mem_num"];
                    $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                    //$data["u_power"]=NULL;
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }
        }

        $this->data["root"] = tb_sql("root", "admin", $root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/slottery/";
        //$this->data["memberURL"]=SYSTEM_URL."/Report/bingo_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/slottery/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/slottery", $this->data, true);
        $this->load->view("admin/main", $this->data);

    }

    //Ebet真人
    public function ebet($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
            } else {    //總代身分列出代理
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
            }
            $sqlStr .= " from `ebet_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `payoutTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `payoutTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
            $dataList = array();
            $parameter = array();
            $sqlStr = "select mem_num,SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
            $sqlStr .= " from `ebet_report`  where 1=1";
            if ($root > 0) {
                $sqlStr .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
            }
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `payoutTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `payoutTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            //===會員帳號 or 遊戲帳號=======================
            if (@$_REQUEST["find9"] != "") {
                $sqlStr .= " and (`username` like ? or mem_num in(select num from member where u_id like ?))";
                $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
            }

            $sqlStr .= " group by mem_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $root;
                    $data["mem_num"] = $row["mem_num"];
                    $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                    $data["u_power"] = NULL;
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }
        }
        $this->data["root"] = tb_sql("root", "admin", $root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/ebet/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/ebet_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/ebet/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/ebet", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //Ebet真人會員明細
    public function ebet_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `ebet_report` where 1=1";
        $sqlSum = "select SUM(betAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss from `ebet_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `payoutTime` >=?";
            $sqlSum .= " and `payoutTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `payoutTime` <= ?";
            $sqlSum .= " and `payoutTime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/ebet_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `payoutTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameType"] = array(
            '1' => '百家樂', '2' => '龍虎',
            '3' => '骰寶', '4' => '輪盤',
            '5' => '水果機'
        );

        $this->load->library('api/ebetapi');    //賓果
        $this->data["betType"] = $this->ebetapi->getBetType();


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/ebet/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/ebet_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //水立方真人
    public function water($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(BetGold) as TotalbetAmount,SUM(RealBetPoint) as TotalvalidAmount,SUM(WinGold) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
            } else {    //總代身分列出代理
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
            }
            $sqlStr .= " from `water_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `BetDate` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `BetDate` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
            $dataList = array();
            $parameter = array();
            $sqlStr = "select mem_num,SUM(BetGold) as TotalbetAmount,SUM(RealBetPoint) as TotalvalidAmount,SUM(WinGold) as TotalwinOrLoss ,count(*) as totals";
            $sqlStr .= " from `water_report`  where 1=1";
            if ($root > 0) {
                $sqlStr .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
            }
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `BetDate` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `BetDate` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            //===會員帳號 or 遊戲帳號=======================
            if (@$_REQUEST["find9"] != "") {
                $sqlStr .= " and (`Cid` like ? or mem_num in(select num from member where u_id like ?))";
                $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
            }

            $sqlStr .= " group by mem_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $root;
                    $data["mem_num"] = $row["mem_num"];
                    $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                    $data["u_power"] = NULL;
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }
        }
        $this->data["root"] = tb_sql("root", "admin", $root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/water/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/water_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/water/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/water", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //水立方真人會員明細
    public function water_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `water_report` where 1=1";
        $sqlSum = "select SUM(BetGold) as TotalbetAmount,SUM(RealBetPoint) as TotalvalidAmount";
        $sqlSum .= ",SUM(WinGold) as TotalwinOrLoss from `water_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `BetDate` >=?";
            $sqlSum .= " and `BetDate` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `BetDate` <= ?";
            $sqlSum .= " and `BetDate` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/water_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `BetDate` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameType"] = array('1' => '百家樂', '22' => '免佣百家樂');
        $this->data["GMidType"] = array('1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5', 'E');


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/water/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/water_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //北京賽車
    public function s9k168($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        //echo '<p style="margin-top:100px;"></p>';
        //echo 'root in function1:'.$root .'<br>';
        //echo 'web_root_num in function1:'.$this->web_root_num .'<br>';

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */

        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }

            //echo 'root:'.$root .'  showself:'.$showself.' tb_sql(u_power,admin,$root)'.tb_sql('u_power','admin',$root);
            $sqlStr = "select SUM(BetAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                //$whereSql.=" and `u_power4`=?";
                //$parameter[':u_power4']=$root;
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                //$whereSql.=" and `u_power5`=?";
                //$parameter[':u_power5']=$root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `9k168_report`  where 1=1" . $whereSql;
            //echo '<br>sqlStr:'.$sqlStr ;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `WagerDate` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `WagerDate` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `9k168_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `WagerDate` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `WagerDate` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`MemberAccount` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //echo 'showself:'.$showself.'<br>';
        //echo ' root : '.$root.'<br>';
        //echo ' web_root_num : '.$this->web_root_num.'<br>';
        //echo ' web_root_u_name : '.$this->web_root_u_name.'<br>';
        //echo ' web_root_u_power : '.$this->web_root_u_power.'<br>';
        //echo '$admin_num:'.$admin_num .'<br>';
        //echo 'tb_sql("root","admin",$root):'.tb_sql("root","admin",$root).'('.tb_sql("u_power","admin",$root).')'.'<br>';
        //echo '<br>web_root_num:'.$this->web_root_num.'  num:'.tb_sql('root','admin',$root) ;


        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself


        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/s9k168/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/s9k168_details/";
        $this->data["showself"] = $showself;

        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/s9k168/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));

        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/s9k168", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //新北京賽車
    public function pk10($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }

        /*end of showself */

        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }

            $sqlStr = "select SUM(amount) as TotalbetAmount,SUM(amount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `pk10_report`  where 1=1" . $whereSql;
            //echo '<br>sqlStr:'.$sqlStr ;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `kjTime` >=?";
                $parameter[':find7'] = strtotime(@$_REQUEST["find7"]);
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `kjTime` <= ?";
                $parameter[':find8'] = strtotime(@$_REQUEST["find8"]);
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);

            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(amount) as TotalbetAmount,SUM(amount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `pk10_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `kjTime` >=?";
                    $parameter[':find7'] = strtotime(@$_REQUEST["find7"]);
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `kjTime` <= ?";
                    $parameter[':find8'] = strtotime($_REQUEST["find8"]);
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`username` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }


        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself
        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/pk10/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/pk10_details/";
        $this->data["showself"] = $showself;

        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/pk10/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));

        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/pk10", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //新北京賽車會員明細
    public function Pk10_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `pk10_report` where 1=1";
        $sqlSum = "select SUM(amount) as TotalbetAmount,SUM(amount) as TotalvalidAmount";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss from `pk10_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `kjTime` >=?";
            $sqlSum .= " and `kjTime` >=?";
            $parameter[':find7'] = strtotime(@$_REQUEST["find7"]);
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `kjTime` <= ?";
            $sqlSum .= " and `kjTime` <= ?";
            $parameter[':find8'] = strtotime(@$_REQUEST["find8"]);
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/pk10_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `kjTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameType"] = array('XYFT' => '幸運飛艇', 'BJPK10' => '北京賽車', 'BingoBingo' => '賓果賓果');


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/pk10/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/pk10_detail", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //北京賽車會員明細
    public function s9k168_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `9k168_report` where 1=1";
        $sqlSum = "select SUM(BetAmount) as TotalbetAmount,SUM(validAmount) as TotalvalidAmount";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss from `9k168_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `WagerDate` >=?";
            $sqlSum .= " and `WagerDate` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `WagerDate` <= ?";
            $sqlSum .= " and `WagerDate` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/s9k168_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `WagerDate` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameType"] = array('XYFT' => '幸運飛艇', 'BJPK10' => '北京賽車', 'BingoBingo' => '賓果賓果');


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/s9k168/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/s9k168_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //AMEBA報表
    public function ameba($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `ameba_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `WagerDate` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `WagerDate` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }

        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `ameba_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `WagerDate` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `WagerDate` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`MemberAccount` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }
        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/ameba/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/ameba_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/ameba/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/ameba", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //AMEBA電子會員明細
    public function ameba_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `ameba_report` where 1=1 ";

        $sqlSum = "select SUM(BetAmount) as TotalbetAmount,SUM(PayOff) as TotalvalidAmount";
        $sqlSum .= ",SUM(WinOrLoss) as TotalwinOrLoss from `ameba_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `WagerDate` >=?";
            $sqlSum .= " and `WagerDate` >=?";
            $parameter[':find7'] = $_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `WagerDate` <= ?";
            $sqlSum .= " and `WagerDate` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/ameba_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `WagerDate` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);

        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/ameba/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/ameba_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //PG電子(緯來)報表
    public function grand($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';

        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(bet) as TotalbetAmount,SUM(winlose) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";

            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }


            $sqlStr .= " from `grand_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `bdate` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `bdate` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }


        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(bet) as TotalbetAmount,SUM(winlose) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `grand_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `bdate` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `bdate` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`account` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }


        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/grand/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/grand_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/grand/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/grand", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //GD緯來會員明細
    public function grand_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `grand_report` where 1=1";

        $sqlSum = "select SUM(bet) as TotalbetAmount,SUM(jppoints) as TotaljpPoints";
        $sqlSum .= ",SUM(winlose) as TotalwinOrLoss from `grand_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }

        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `bdate` >=?";
            $sqlSum .= " and `bdate` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `bdate` < ?";
            $sqlSum .= " and `bdate` < ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/grand_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `bdate` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["gameID"] = array(
            1002 => '迎財神',
            2002 => '多人骰寶',
            3001 => '動物星球',
            4001 => '魚王爭霸',
            5001 => '猴子爬樹',
            1003 => '福祿壽喜',
            3002 => '西遊爭霸',
            4003 => '全民捕魚',
            3003 => '皇冠列車',
            3005 => '賓士寶馬',
            3004 => '小瑪莉'
        );


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/grand/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/grand_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //泛亞電競報表
    public function avia($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }

        $select_ag_id = [];
        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }
        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(BetAmount) as TotalbetAmount,SUM(Money) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `avia_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `CreateAt` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `CreateAt` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(BetAmount) as TotalbetAmount,SUM(Money) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `avia_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `CreateAt` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `CreateAt` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`UserName` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }
                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num) {
            $uproot = "0";
        } else {
            $uproot = tb_sql("root", "admin", $root);
        }
        $this->data["root"] = $uproot;
        //end of showself


        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/avia/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/avia_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/avia/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/avia", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //泛亞電競會員明細
    public function avia_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `avia_report` where 1=1 ";

        $sqlSum = "select SUM(BetAmount) as TotalbetAmount";
        $sqlSum .= ",SUM(Money) as TotalwinOrLoss from `avia_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `CreateAt` >=?";
            $sqlSum .= " and `CreateAt` >=?";
            $parameter[':find7'] = $_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `CreateAt` <= ?";
            $sqlSum .= " and `CreateAt` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/avia_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `CreateAt` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);

        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/avia/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/avia_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

	
	//皇朝電競報表
    public function hces888($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }

        $select_ag_id = [];
        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }
        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(betamount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `hces888_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `bettime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `bettime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            //var_dump($sqlStr);
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(betamount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `hces888_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `bettime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `bettime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`user_name` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }
                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num) {
            $uproot = "0";
        } else {
            $uproot = tb_sql("root", "admin", $root);
        }
        $this->data["root"] = $uproot;
        //end of showself


        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/hces888/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/hces888_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/hces888/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/hces888", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //皇朝電競會員明細
    public function hces888_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `hces888_report` where 1=1 ";

        $sqlSum = "select SUM(betamount) as TotalbetAmount";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss from `hces888_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `reckondate` >=?";
            $sqlSum .= " and `reckondate` >=?";
            $parameter[':find7'] = $_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `reckondate` <= ?";
            $sqlSum .= " and `reckondate` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/hces888_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `reckondate` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);

        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/hces888/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/hces888_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
		
    }
	
	
	
	//VG
    public function vg($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        //echo '<p style="margin-top:100px;"></p>';
        //echo 'root in function1:'.$root .'<br>';
        //echo 'web_root_num in function1:'.$this->web_root_num .'<br>';

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;
			
        }
        //var_dump($u_power,$this->web_root_u_power);
		//var_dump($this->web_root_num);
        //exit;
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }

        //var_dump($this->web_root_num."|".$this->web_root_u_power."|".$showself);

        /*end of showself */

        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }

            //echo 'root:'.$root .'  showself:'.$showself.' tb_sql(u_power,admin,$root)'.tb_sql('u_power','admin',$root);
            $sqlStr = "select SUM(betamount) as TotalbetAmount,SUM(validbetamount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                //$whereSql.=" and `u_power4`=?";
                //$parameter[':u_power4']=$root;
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself

                //$sqlStr.=",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                //$whereSql.=" and `u_power5`=?";
                //$parameter[':u_power5']=$root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `vg_report`  where 1=1" . $whereSql;
            //echo '<br>sqlStr:'.$sqlStr ;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `createtime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `createtime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            //var_dump($sqlStr,$root);
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(betamount) as TotalbetAmount,SUM(validbetamount) as TotalvalidAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `vg_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `createtime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `createtime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`username` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                //var_dump($sqlStr);
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //echo 'showself:'.$showself.'<br>';
        //echo ' root : '.$root.'<br>';
        //echo ' web_root_num : '.$this->web_root_num.'<br>';
        //echo ' web_root_u_name : '.$this->web_root_u_name.'<br>';
        //echo ' web_root_u_power : '.$this->web_root_u_power.'<br>';
        //echo '$admin_num:'.$admin_num .'<br>';
        //echo 'tb_sql("root","admin",$root):'.tb_sql("root","admin",$root).'('.tb_sql("u_power","admin",$root).')'.'<br>';
        //echo '<br>web_root_num:'.$this->web_root_num.'  num:'.tb_sql('root','admin',$root) ;


        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself


        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/vg/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/vg_details/";
        $this->data["showself"] = $showself;

        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/vg/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));

        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/vg", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //VG會員明細
    public function vg_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `vg_report` where 1=1";
        $sqlSum = "select SUM(betamount) as TotalbetAmount,SUM(validbetamount) as TotalvalidAmount";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss from `vg_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }


        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `createtime` >=?";
            $sqlSum .= " and `createtime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `createtime` <= ?";
            $sqlSum .= " and `createtime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/vg_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `createtime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        //var_dump($rowAll);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //定義遊戲類型
        $this->data["gameType"] = array('XYFT' => '幸運飛艇', 'BJPK10' => '北京賽車', 'BingoBingo' => '賓果賓果');


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/vg/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/vg_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
		
		
    }
	
	
	
	
	
    /**
     * 彩播報表(game_maker = 41)
     * @param int $root
     */
    public function htpg($root = 0)
    {
        $this->chkMemberLoginStatus();
        $game_name = 'htpg';
        $report_table = 'htpg_report';    //報表資料表名稱
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
                $showself = ($root == 0 ? true : false);
            }
            $sqlStr = "select SUM(amount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `" . $report_table . "`  where 1=1 and status != 4" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `created_at` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `created_at` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    //$data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(amount) as TotalbetAmount,SUM(winOrLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `" . $report_table . "`  where 1=1 and status != 4";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `created_at` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `created_at` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`UserName` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }
                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num) {
            $uproot = "0";
        } else {
            $uproot = tb_sql("root", "admin", $root);
        }
        $this->data["root"] = $uproot;
        //end of showself

        $this->data["baseURL"] = SYSTEM_URL . "/Report/" . $game_name . "/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/" . $game_name . "_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/" . $game_name . "/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/" . $game_name, $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    /**
     * 彩播報表明細(game_maker = 41)
     * @param int $admin_num
     */
    public function htpg_details($admin_num)
    {
        $game_name = 'htpg';
        $report_table = 'htpg_report';
        $this->chkMemberLoginStatus();
        $parameter = array();
        $sqlStr = "select * from `" . $report_table . "` where 1=1 ";

        $sqlSum = "select SUM(amount) as TotalbetAmount,SUM(valid_amount) as validAmountTotal";
        $sqlSum .= ",SUM(winOrLoss) as TotalwinOrLoss from `" . $report_table . "` where 1=1 and status != 4";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `created_at` >=?";
            $sqlSum .= " and `created_at` >=?";
            $parameter[':find7'] = $_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `created_at` <= ?";
            $sqlSum .= " and `created_at` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/" . $game_name . "_details/" . $admin_num . "?p=1" . $this->data["att"]);
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `created_at` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);

        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/" . $game_name . "/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));
        $this->data["body"] = $this->load->view("admin/report/" . $game_name . "_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }
	
	
	
	/**
    * RTG報表(game_maker = 51)
     @param int $root
    */
    public function rtg($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $game_name = 'rtg';
        $report_table = 'rtg_report';    //報表資料表名稱

        $bet = 'bet';
        $winOrLoss = 'winOrLose';
        $valid = 'bet';
        $buildDate = 'gameStartDate';

		//var_dump(tb_sql('u_power', 'admin', $root));
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
                $showself = ($root == 0 ? true : false);
            }
            $sqlStr = "select SUM(".$bet.") as TotalbetAmount,SUM(".$winOrLoss.") as TotalwinOrLoss,SUM(".$valid.") as TotalvalidAmount,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;
                //end of showself
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;

            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `" . $report_table . "`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `".$buildDate."` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `".$buildDate."` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"]=($row["TotalvalidAmount"]!="" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }
        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(".$bet.") as TotalbetAmount,SUM(".$winOrLoss.") as TotalwinOrLoss,SUM(".$valid.") as TotalvalidAmount,count(*) as totals";
                $sqlStr .= " from `" . $report_table . "`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `".$buildDate."` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `".$buildDate."` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`playerName` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }
                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        $data["u_power"] = NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num) {
            $uproot = "0";
        } else {
            $uproot = tb_sql("root", "admin", $root);
        }
        $this->data["root"] = $uproot;
        //end of showself

        $this->data["baseURL"] = SYSTEM_URL . "/Report/" . $game_name . "/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/" . $game_name . "_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/" . $game_name . "/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/" . $game_name, $this->data, true);
        $this->load->view("admin/main", $this->data);
    }
	
	
	
	/**
     * RTG報表明細(game_maker = 51)
     * @param int $admin_num
     */
    public function rtg_details($admin_num)
    {
        $game_name = 'rtg';
        $report_table = 'rtg_report';    //報表資料表名稱
        $bet = 'bet';
        $winOrLoss = 'winOrLose';
        $valid = 'bet';
        $buildDate = 'gameStartDate';


        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `".$report_table."` where 1=1";

        $sqlSum = "select SUM(".$bet.") as TotalbetAmount";
        $sqlSum .= ",SUM(".$winOrLoss.") as TotalwinOrLoss, SUM(".$valid.") as TotalvalidAmount from `".$report_table."` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }

        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `".$buildDate."` >=?";
            $sqlSum .= " and `".$buildDate."` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `".$buildDate."` < ?";
            $sqlSum .= " and `".$buildDate."` < ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/".$game_name."_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `".$buildDate."` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        //Sid+Gid
        $this->data["gameID"] = gameCode('ps');


        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/".$game_name."/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/".$game_name."_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
		
    }
	
	
	
    //eg 報表
    public function eg($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';
        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);
        if (tb_sql('u_power', 'admin', $root) < $this->web_root_u_power && $this->web_root_u_power != 7) {
            $root = 0;

        }


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(betAmount) as TotalbetAmount,SUM(validBetAmount) as TotalvalidAmount,SUM(winLoss) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;

            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理

                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;

            } else {    //代理列出自己

                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;

            }
            $sqlStr .= " from `eg_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `betTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `betTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }
        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(betAmount) as TotalbetAmount,SUM(validBetAmount) as TotalvalidAmount,SUM(winLoss) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `eg_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `betTime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `betTime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`userId` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }


        //判斷上層是不是自已 showself
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself
        //$this -> data["root"]=tb_sql("root","admin",$root);
        $this->data["baseURL"] = SYSTEM_URL . "/Report/eg/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/eg_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/eg/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/eg", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }


    //eg會員明細
    public function eg_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `eg_report` where 1=1";
        $sqlSum = "select SUM(betAmount) as TotalbetAmount,SUM(validBetAmount) as TotalvalidAmount";
        $sqlSum .= ",SUM(winLoss) as TotalwinOrLoss from `eg_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }
        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `betTime` >=?";
            $sqlSum .= " and `betTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `betTime` <= ?";
            $sqlSum .= " and `betTime` <= ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/eg_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `betTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        /*
        //定義遊戲類型
        $this->data["gameTypeArray"] = array(
            '1' => '小黄鱼', '2' => '孔雀鱼', '3' => '宝莲灯', '4' => '蝴蝶鱼', '5' => '天使鱼', '6' => '小丑鱼', '7' => '海马', '8' => '狐狸鱼', '9' => '金鱼', '10' => '鲷鱼'
            , '11' => '水母', '12' => '龙虾', '13' => '灯笼鱼', '14' => '锦鲤', '15' => '螃蟹', '16' => '狮子鱼', '17' => '河豚', '18' => '海龟', '19' => '章鱼', '20' => '银龙鱼'
            , '21' => '蝙蝠', '22' => '未知22', '23' => '剑鱼', '24' => '鲨鱼', '25' => '虎鲸', '26' => '金海马', '27' => '金河豚', '28' => '金海龟', '29' => '金章鱼', '30' => '金龙鱼'
            , '31' => '金蝙蝠鱼', '32' => '金剑鱼', '33' => '金海豚', '34' => '金鲨鱼', '35' => '真假巨钳龙虾', '36' => '未知36', '37' => '巨翡蟹', '38' => '金钱鳄', '39' => '巨钳龙虾', '40' => '亚特兰人鱼'
            , '41' => '金龙龟', '42' => '独角白鲸', '43' => '未知43', '44' => '电小鳗', '45' => '未知45', '46' => '炸弹蟹', '47' => '海豚'
        );
        */
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/eg/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));

        $this->data["body"] = $this->load->view("admin/report/eg_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //瑪雅真人
    public function maya($root = 0)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }

        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $dataList = array();

        /* add to showself */
        $showself = false;
        $whereSql = '';

        //身份層級檢核
        $u_power = tb_sql('u_power', 'admin', $root);


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
        }


        //echo 'root in function2:'.$root .'<br>';
        $select_ag_id = [];

        if (@$_REQUEST["find9"]) {
            $select_ag_account = $_REQUEST["find9"];
            $this->db->select('num');
            $this->db->from('admin');
            $this->db->like('u_id', $select_ag_account);
            $this->db->where('u_power', 6);
            $ag_id = $this->db->get();
            $ag_id = $ag_id->result_array();
            foreach ($ag_id as $val) {
                $select_ag_id[] = $val['num'];
            }
        }


        /*end of showself */
        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                $root = ($root > 0 ? $root : $this->web_root_num);
            }
            $sqlStr = "select SUM(BetMoney) as TotalbetAmount,SUM(ValidBetMoney) as TotalvalidAmount,SUM(WinLoseMoney) as TotalwinOrLoss,Count(*) as totals";
            if ($root == 0) {    //列出所有股東
                $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
            } elseif (tb_sql('u_power', 'admin', $root) == 4) {    //股東身分列出總代
                if ($showself) {
                    // add for showself
                    $sqlStr .= ",SUM(u_power4_profit) as TotalProfit,u_power4 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                }
                $whereSql .= " and `u_power4`=?";
                $parameter[':u_power4'] = $root;

                //$sqlStr.=",SUM(u_power5_profit) as TotalProfit,SUM(u_power4_profit - u_power5_profit) as AgTotalProfit,u_power5 as agent_num";
                //$whereSql.=" and `u_power4`=?";
                //$parameter[':u_power4']=$root;
            } elseif (tb_sql('u_power', 'admin', $root) == 5) {    //總代身分列出代理
                //showself
                if ($showself) {
                    $sqlStr .= ",SUM(u_power5_profit) as TotalProfit,u_power5 as agent_num";
                } else {
                    $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                }
                $whereSql .= " and `u_power5`=?";
                $parameter[':u_power5'] = $root;
                //end of showself
                //$sqlStr.=",SUM(u_power6_profit) as TotalProfit,SUM(u_power5_profit - u_power6_profit) as AgTotalProfit,u_power6 as agent_num";
                //$whereSql.=" and `u_power5`=?";
                //$parameter[':u_power5']=$root;
            } else {    //代理列出自己
                //showself
                $sqlStr .= ",SUM(u_power6_profit) as TotalProfit,u_power6 as agent_num";
                $whereSql .= " and `u_power6`=?";
                $parameter[':u_power6'] = $root;
                // end of showself
            }
            $sqlStr .= " from `maya_report`  where 1=1" . $whereSql;
            //===起始日期=======================
            if (@$_REQUEST["find7"] != "") {
                $sqlStr .= " and `AccountDateTime` >=?";
                $parameter[':find7'] = @$_REQUEST["find7"];
            }
            //===終止日期=======================
            if (@$_REQUEST["find8"] != "") {
                $sqlStr .= " and `AccountDateTime` <= ?";
                $parameter[':find8'] = $_REQUEST["find8"];
            }
            $sqlStr .= " group by agent_num";
            $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
            if ($rowAll != NULL) {
                foreach ($rowAll as $row) {
                    $data = array();
                    $data["num"] = $row["agent_num"];
                    $data["u_id"] = tb_sql('u_id', 'admin', $row["agent_num"]);
                    $data["u_power"] = tb_sql('u_power', 'admin', $row["agent_num"]);
                    $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                    $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                    $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                    $data["Profit"] = ($row["TotalProfit"] != "" ? $row["TotalProfit"] : 0);
                    $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                    array_push($dataList, $data);
                }
            }

        }

        //代理點進來 改列出會員處理
        if (!$showself) {
            if (($root > 0 && (tb_sql("u_power", "admin", $root) == 6)) || @$_REQUEST["find9"] != '') {
                $dataList = array();
                $parameter = array();
                $sqlStr = "select mem_num,SUM(BetMoney) as TotalbetAmount,SUM(ValidBetMoney) as TotalvalidAmount,SUM(WinLoseMoney) as TotalwinOrLoss ,count(*) as totals";
                $sqlStr .= " from `maya_report`  where 1=1";
                if ($root > 0) {
                    $sqlStr .= " and `u_power6`=?";
                    $parameter[':u_power6'] = $root;
                }
                //===起始日期=======================
                if (@$_REQUEST["find7"] != "") {
                    $sqlStr .= " and `AccountDateTime` >=?";
                    $parameter[':find7'] = @$_REQUEST["find7"];
                }
                //===終止日期=======================
                if (@$_REQUEST["find8"] != "") {
                    $sqlStr .= " and `AccountDateTime` <= ?";
                    $parameter[':find8'] = $_REQUEST["find8"];
                }
                //===會員帳號 or 遊戲帳號=======================
                if (@$_REQUEST["find9"] != "") {
                    $sqlStr .= " and (`VenderMemberID` like ? or mem_num in(select num from member where u_id like ?))";
                    $parameter[':find9-1'] = "%" . trim($_REQUEST["find9"]) . "%";
                    $parameter[':find9-2'] = "%" . trim($_REQUEST["find9"]) . "%";
                }

                $sqlStr .= " group by mem_num";
                $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
                if ($rowAll != NULL) {
                    foreach ($rowAll as $row) {
                        $data = array();
                        $data["num"] = $root;
                        $data["mem_num"] = $row["mem_num"];
                        $data["u_id"] = tb_sql('u_id', 'member', $row["mem_num"]);
                        //$data["u_power"]=NULL;
                        $data["betAmount"] = ($row["TotalbetAmount"] != "" ? $row["TotalbetAmount"] : 0);
                        $data["validAmount"] = ($row["TotalvalidAmount"] != "" ? $row["TotalvalidAmount"] : 0);
                        $data["winOrLoss"] = ($row["TotalwinOrLoss"] != "" ? $row["TotalwinOrLoss"] : 0);
                        $data["totals"] = ($row["totals"] != "" ? $row["totals"] : 0);
                        array_push($dataList, $data);
                    }
                }
            }
        }

        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;
        //end of showself

        $this->data["baseURL"] = SYSTEM_URL . "/Report/maya/";
        $this->data["memberURL"] = SYSTEM_URL . "/Report/maya_details/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/maya/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;


        $this->data["body"] = $this->load->view("admin/report/maya", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //瑪雅會員明細
    public function maya_details($admin_num)
    {
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $parameter = array();
        $sqlStr = "select * from `maya_report` where 1=1";
        $sqlSum = "select SUM(BetMoney) as TotalbetAmount,SUM(WinLoseMoney) as TotalwinOrLoss,SUM(ValidBetMoney) as TotalvalidAmount from `maya_report` where 1=1";
        if ($admin_num > 0) {
            $sqlStr .= " and `u_power6`=?";
            $sqlSum .= " and `u_power6`=?";
            $parameter[':u_power6'] = $admin_num;
        }

        if (@$_REQUEST["find1"] != "") {    //抓出會員
            $sqlStr .= " and `mem_num`=?";
            $sqlSum .= " and `mem_num`=?";
            $parameter[':find1'] = @$_REQUEST["find1"];
        }

        //===起始日期=======================
        if (@$_REQUEST["find7"] != "") {
            $sqlStr .= " and `AccountDateTime` >=?";
            $sqlSum .= " and `AccountDateTime` >=?";
            $parameter[':find7'] = @$_REQUEST["find7"];
        }
        //===終止日期=======================
        if (@$_REQUEST["find8"] != "") {
            $sqlStr .= " and `AccountDateTime` < ?";
            $sqlSum .= " and `AccountDateTime` < ?";
            $parameter[':find8'] = $_REQUEST["find8"];
        }

        $total = $this->webdb->sqlRowCount($sqlStr, (count($parameter) > 0 ? $parameter : NULL)); //總筆數
        //分頁相關
        $config['base_url'] = site_url(SYSTEM_URL . "/Report/maya_details/" . $admin_num . "?p=1" . $this->data["att"]);//site_url("admin/news/index");
        $this->data["s_action"] = $config['base_url'];
        $config['total_rows'] = $total;
        $limit = 10;    //每頁比數
        $config['per_page'] = $limit;
        //$config['uri_segment'] = 4;
        $config['num_links'] = 4;
        $config['page_query_string'] = TRUE;
        $maxpage = $total % $limit == 0 ? $total / $limit : floor($total / $limit) + 1; //總頁數
        $nowpage = 1;
        if (@$_GET["per_page"] != "") {
            $nowpage = @$_GET["per_page"];
        }
        if ($nowpage > $maxpage) {
            $nowpage = $maxpage;
        }
        $sqlStr .= " order by `CountDateTime` desc LIMIT " . ((($nowpage > 0 ? $nowpage : 1) - 1) * $limit) . "," . $limit;
        //$sqlStr.=" order by `betTime` DESC";

        $rowAll = $this->webdb->sqlRowList($sqlStr, $parameter);
        $this->data["result"] = $rowAll;
        $this->data["rowSum"] = $this->webdb->sqlRow($sqlSum, $parameter);
        //產生分頁連結
        $this->load->library("pagination");
        $this->pagination->doConfig($config);
        $this->data["pagination"] = $this->pagination->create_links();

        $this->load->library('api/maya');


        $this->data["gameTypeArrays"] = $this->maya->getGameID();
        $this->data["BetType"] = $this->maya->getBetType();

        //$this->data["betTypeArray"]=$this->dreamgame->getBetType();
        $this->data["root"] = $admin_num;
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/maya/" . $this->data["root"] . ($this->data["att"] != "" ? urlQuery('find1') : ""));
        $this->data["body"] = $this->load->view("admin/report/maya_details", $this->data, true);
        $this->load->view("admin/main", $this->data);
    }

    //整合報表(新~~較快)
    public function report_all($root = 0)
    {
        error_reporting(1);
        if (!$this->isLogin()) {    //檢查登入狀態
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
        $this->load->model('admin/Report_model', 'report');


        $parameter = array();
        $Cust_array = array(4, 5, 6);    //股東 總代 代理
        $whereSql = '';
        $dataList = array();
        $showself = false;


        if (@$_REQUEST["find7"] != "" || @$_REQUEST["find8"] != "") {
            //---身份判定---------------------------
            if (in_array($this->web_root_u_power, $Cust_array)) {    //登入身分為股東或者總代
                if ($root == 0) {
                    $root = $this->web_root_num;
                    $showself = true;
                }
            }
            //echo $root."|".$showself;
            $select_ag_id = [];

            if (@$_REQUEST["find9"]) {
                $select_ag_account = $_REQUEST["find9"];
                $this->db->select('num');
                $this->db->from('admin');
                $this->db->like('u_id', $select_ag_account);
                $this->db->where('u_power', 6);
                $ag_id = $this->db->get();
                $ag_id = $ag_id->result_array();
                foreach ($ag_id as $val) {
                    $select_ag_id[] = $val['num'];
                }
            }
            $this->data["prefix"] = array();
            if (strstr($_REQUEST["find10"], ","))
                $_REQUEST["find10"] = explode(",", $_REQUEST["find10"]);


            $gameList = gameList();
            foreach ($gameList as $gameCode => $gameInfo) {
                $rowAll = $this->report->{$gameInfo['gameModel']}($root, $_REQUEST["find7"], $_REQUEST["find8"], '', '', $showself);
                $dataList = $this->report->buildData($dataList, $rowAll, $gameCode, $gameInfo[0]);
                array_push($this->data["prefix"], array($gameCode, $gameInfo['gameName']));
            }
            
            //紅利計算
            if (count($dataList) > 0) {

                //Old

                foreach ($dataList as $keys => $row) {
                    $row = $this->report->member_points($row["num"], $row["u_power"], $_REQUEST["find7"], $_REQUEST["find8"]);
                    $dataList[$keys]["PointsProfit"] = (@$row["PointsProfit"] != "" ? $row["PointsProfit"] : 0);    //紅利分潤
                    $dataList[$keys]["TotalRealPoints"] = (@$row["TotalRealPoints"] != "" ? $row["TotalRealPoints"] : 0);
                }

            }

            //根據帳號排序
            foreach ($dataList as $key => $row) {
                $rowTotal[$key] = $row['u_id'];
            }
            @array_multisort($rowTotal, SORT_ASC, $dataList);

        }

        /*
        //上排子選單
        $msql = "select * from `item` where `root`=? and isShow='Y' order by `range` asc";
        $parameter=array();
        $parameter[':root']=156;
        $rowAll=$this->webdb->sqlRowList($msql,$parameter);
        if($rowAll!=NULL){
            $this -> data["item"] = $rowAll;
        }
        //上排選單cookies
        */

        //判斷上層是不是自已
        $this->data["showself"] = $showself;
        if ($root == $this->web_root_num)
            $uproot = "0";
        else
            $uproot = tb_sql("root", "admin", $root);

        $this->data["root"] = $uproot;

        $this->data["u_power"] = $this->web_root_u_power;
        $this->data["baseURL"] = SYSTEM_URL . "/Report/report_all/";
        $this->data["backBTN"] = site_url(SYSTEM_URL . "/Report/report_all/" . $this->data["root"] . ($this->data["att"] != "" ? "?p=1" . $this->data["att"] : ""));
        $this->data["result"] = $dataList;
        $this->data["body"] = $this->load->view("admin/report/report_all", $this->data, true);

        $this->load->view("admin/main", $this->data);

    }


    /**
     * 檢查是否有登入
     */
    private function chkMemberLoginStatus()
    {
        if (!$this->isLogin()) {
            $msg = array("type" => "danger", 'title' => '權限錯誤！', 'content' => '很抱歉...您無權檢示頁面');
            $this->_setMsgAndRedirect($msg, $this->agent->referrer());
            exit;
        }
    }
} 