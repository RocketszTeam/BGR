<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Htpg_report extends CI_Controller
{
    const GAME_MAKER_NUM = 41;  //遊戲代碼
    const DECIMAL_POINT = 2;    //小數位數
    const EXCHANGE_RATE = 5;    //人民幣匯率
    const AGENT_LABEL = 'penh';   //代理帳號請至彩播後台查詢

    public function __construct()
    {
        parent::__construct();
        $this->load->library('api/htpg');
        date_default_timezone_set("Asia/Taipei");
    }

    /**
     * 透過下注時間去補前日的單據
     */
    public function getDayreport()
    {
        $sTime = date('Y-m-d', strtotime("-1 day")) . ' 00:00:00';
        $eTime = date('Y-m-d', strtotime("-1 day")) . ' 23:59:59';
        $this->get_report($sTime, $eTime, 100, 1, $type = 'CreateAt');
    }

    /**
     * 撈取彩播報表(已結與未結)
     * @param string YMD 日期
     */
    public function index($YMD)
    {
        $this->get_report($YMD);
    }

    /**
     * 撈取今日彩播報表(已結與未結)
     * @param null|string $YMD
     */
    public function get_report($YMD = NULL)
    {
        if ($YMD == NULL) {
            //六合彩開獎日拖的比較長所以先設定五日前資料
            for ($i = 5; $i >= 0; $i--) {
                $YMD = date('Ymd', strtotime("-" . $i . " day"));
                $this->buildReportAll($YMD);
            }
        } else {
            $YMD = date('Ymd', strtotime($YMD));
            $this->buildReportAll($YMD);
        }
    }


    /**
     * 撈取指定時間彩播報表(已結)
     * @param null $YMD
     */
    public function update_report($YMD = NULL)
    {
        //更新今日
        if ($YMD == NULL) {
            $now = date('Ymd');
            $YMD = date('Ymd', strtotime($now));
        } else {
            $YMD = date('Ymd', strtotime($YMD));
        }
        $result = $this->htpg->reporter_all2($YMD);
        if (count($result) > 0) {
            $okPath = '/var/www/html/report_file/' . $YMD . '/ok_list.txt';
            $okList = [];
            foreach ($result as $list) {
                try {
                    $file = fopen($list, "r");
                    $jsonList = json_decode(fread($file, filesize($list)));
                    fclose($file);
                    $this->intoDB2($jsonList);
                    array_push($okList, basename($list));
                } catch (Exception $e) {
                    unlink($list);
                    echo $list . ' 寫入失敗 ; ' . $e->getMessage() . '<br>';
                }
            }
            $fp = fopen($okPath, 'w');
            fwrite($fp, json_encode($okList));
            fclose($fp);
        }

    }

    /**
     * 寫入DB 只寫入新單和更新未開獎單據
     * @param $result
     * @throws Exception
     */
    private function intoDB($result)
    {
        $uidList = [];
        if (count($result) > 0) {
            foreach ($result as $row) {
                $uidList[$row->username] = '';
            }
            $uidList = array_keys($uidList);
            $sqlStr = "select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in ('" . implode("','", $uidList) . "') and gamemaker_num=" . self::GAME_MAKER_NUM;
            $rowAll = $this->webdb->sqlRowList($sqlStr);
            $adminSql = "SELECT num,root,percent from admin";
            $AdminList = $this->webdb->sqlRowList($adminSql);
            if ($rowAll != NULL) {
                foreach ($result as $row) {
                    if ($row->agent_label != self::AGENT_LABEL) {
                        continue;
                    }
                    $u_power4 = 0;
                    $u_power5 = 0;
                    $u_power6 = 0;
                    $u_power4_profit = 0;
                    $u_power5_profit = 0;
                    $u_power6_profit = 0;
                    $mem_num = 0;

                    $winOrLossCn = $this->doBcSub($row->amount_win, $row->amount);
                    $winOrLoss = $this->doBcMul($winOrLossCn, self::EXCHANGE_RATE);

                    //取出會員代理總代代理編號
                    for ($i = 0; $i < count($rowAll); $i++) {
                        if (strcasecmp($rowAll[$i]["u_id"], $row->username) == 0) {    //因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
                            $mem_num = $rowAll[$i]["mem_num"];    //取出會員編號
                            $u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
                            break;
                        }
                    }
                    //階層分潤計算
                    for ($i = 0; $i < count($AdminList); $i++) {
                        if ($AdminList[$i]["num"] == $u_power6) {
                            $u_power6_profit = $this->doBcMul($winOrLoss, $this->doBcDiv($AdminList[$i]["percent"], 100));
                            $u_power5 = $AdminList[$i]["root"];
                            break;
                        }
                    }
                    for ($i = 0; $i < count($AdminList); $i++) {
                        if ($AdminList[$i]["num"] == $u_power5) {
                            $u_power5_profit = $this->doBcMul($winOrLoss, $this->doBcDiv($AdminList[$i]["percent"], 100));
                            $u_power4 = $AdminList[$i]["root"];
                            break;
                        }
                    }
                    for ($i = 0; $i < count($AdminList); $i++) {
                        if ($AdminList[$i]["num"] == $u_power4) {
                            $u_power4_profit = $this->doBcMul($winOrLoss, $this->doBcDiv($AdminList[$i]["percent"], 100));
                            break;
                        }
                    }

                    //比對注單 在db內 屬於未開獎的 才更新
                    $sqlStr = "select `status` from `htpg_report` where `order_number`='" . $row->order_number . "'";
                    $rowCheck = $this->webdb->sqlRow($sqlStr);
                    if ($rowCheck == NULL || $rowCheck["status"] < 2) {
                        //寫入DB
                        $parameter = [
                            'order_number'    => $row->order_number,
                            'label'           => $row->label,
                            'username'        => $row->username,
                            'lottery_name'    => $row->lottery_name,
                            'moshi'           => $row->moshi,
                            'status'          => $row->status,
                            'created_at'      => $row->created_at,
                            'updated_at'      => (isset($row->updated_at) ? $row->updated_at : NULL),
                            'remark'          => $row->remark,
                            'expect'          => $row->expect,
                            'total'           => $row->total,
                            'amount'          => $this->doBcMul($row->amount, self::EXCHANGE_RATE),
                            'amount_cn'       => $row->amount,
                            'valid_amount'    => ($row->status == 4) ? 0 : $this->doBcMul($row->amount, self::EXCHANGE_RATE),
                            'valid_amount_cn' => ($row->status == 4) ? 0 : $row->amount,
                            'amount_win'      => $this->doBcMul($row->amount_win, self::EXCHANGE_RATE),
                            'amount_win_cn'   => $row->amount_win,
                            'winOrLoss'       => $winOrLoss,
                            'winOrLossCn'     => $winOrLossCn,
                            'game_result'     => $row->game_result,
                            'number'          => $row->number,
                            'device_type'     => $row->device_type,
                            'jiangjin_bili'   => $row->jiangjin_bili,
                            'unit_price'      => $row->unit_price,
                            'amount_fandian'  => $row->amount_fandian,
                            'total_win'       => $row->total_win,
                            'beishu'          => $row->beishu,
                            'istrue'          => $row->istrue,
                            'mem_num'         => $mem_num,
                            'u_power4'        => $u_power4,
                            'u_power5'        => $u_power5,
                            'u_power6'        => $u_power6,
                            'u_power4_profit' => $u_power4_profit,
                            'u_power5_profit' => $u_power5_profit,
                            'u_power6_profit' => $u_power6_profit
                        ];
                        $this->webdb->sqlReplace('htpg_report', $parameter);
                    }
                }
            }
        }
    }

    /**
     * 更新所有已結單
     * @param $result
     */
    private function intoDB2($result)
    {
        $uidList = [];
        if (count($result) > 0) {
            foreach ($result as $row) {
                $uidList[$row->username] = '';
            }
            $uidList = array_keys($uidList);
            $sqlStr = "select mem_num,games_account.u_id,member.admin_num  from `games_account` LEFT JOIN member on games_account.mem_num = member.num where games_account.u_id in ('" . implode("','", $uidList) . "') and gamemaker_num=" . self::GAME_MAKER_NUM;
            $rowAll = $this->webdb->sqlRowList($sqlStr);
            $adminSql = "SELECT num,root,percent from admin";
            $AdminList = $this->webdb->sqlRowList($adminSql);
            if ($rowAll != NULL) {
                foreach ($result as $row) {
                    if ($row->agent_label != self::AGENT_LABEL) {
                        continue;
                    }
                    $u_power4 = 0;
                    $u_power5 = 0;
                    $u_power6 = 0;
                    $u_power4_profit = 0;
                    $u_power5_profit = 0;
                    $u_power6_profit = 0;
                    $mem_num = 0;

                    $winOrLossCn = $this->doBcSub($row->amount_win, $row->amount);
                    $winOrLoss = $this->doBcMul($winOrLossCn, self::EXCHANGE_RATE);

                    //取出會員代理總代代理編號
                    for ($i = 0; $i < count($rowAll); $i++) {
                        //$this->data[$k]=$v;
                        if (strcasecmp($rowAll[$i]["u_id"], $row->username) == 0) {    //因為回傳帳號 是全小寫 所以用不區分大小寫方式比對
                            $mem_num = $rowAll[$i]["mem_num"];    //取出會員編號
                            $u_power6 = $rowAll[$i]["admin_num"];//取出代理編號
                            break;
                        }
                    }
                    //代理分潤、總代編號
                    for ($i = 0; $i < count($AdminList); $i++) {
                        if ($AdminList[$i]["num"] == $u_power6) {
                            $u_power6_profit = $this->doBcMul($winOrLoss, $this->doBcDiv($AdminList[$i]["percent"], 100));
                            $u_power5 = $AdminList[$i]["root"];
                            break;
                        }
                    }
                    for ($i = 0; $i < count($AdminList); $i++) {
                        if ($AdminList[$i]["num"] == $u_power5) {
                            $u_power5_profit = $this->doBcMul($winOrLoss, $this->doBcDiv($AdminList[$i]["percent"], 100));
                            $u_power4 = $AdminList[$i]["root"];
                            break;
                        }
                    }
                    for ($i = 0; $i < count($AdminList); $i++) {
                        if ($AdminList[$i]["num"] == $u_power4) {
                            $u_power4_profit = $this->doBcMul($winOrLoss, $this->doBcDiv($AdminList[$i]["percent"], 100));
                            break;
                        }
                    }
                    //寫入DB
                    $parameter = [
                        'order_number'    => $row->order_number,
                        'label'           => $row->label,
                        'username'        => $row->username,
                        'lottery_name'    => $row->lottery_name,
                        'moshi'           => $row->moshi,
                        'status'          => $row->status,
                        'created_at'      => $row->created_at,
                        'updated_at'      => (isset($row->updated_at) ? $row->updated_at : NULL),
                        'remark'          => $row->remark,
                        'expect'          => $row->expect,
                        'total'           => $row->total,
                        'amount'          => $this->doBcMul($row->amount, self::EXCHANGE_RATE),
                        'amount_cn'       => $row->amount,
                        'valid_amount'    => ($row->status == 4) ? 0 : $this->doBcMul($row->amount, self::EXCHANGE_RATE),
                        'valid_amount_cn' => ($row->status == 4) ? 0 : $row->amount,
                        'amount_win'      => $this->doBcMul($row->amount_win, self::EXCHANGE_RATE),
                        'amount_win_cn'   => $row->amount_win,
                        'winOrLoss'       => $winOrLoss,
                        'winOrLossCn'     => $winOrLossCn,
                        'game_result'     => $row->game_result,
                        'number'          => $row->number,
                        'device_type'     => $row->device_type,
                        'jiangjin_bili'   => $row->jiangjin_bili,
                        'unit_price'      => $row->unit_price,
                        'amount_fandian'  => $row->amount_fandian,
                        'total_win'       => $row->total_win,
                        'beishu'          => $row->beishu,
                        'istrue'          => $row->istrue,
                        'mem_num'         => $mem_num,
                        'u_power4'        => $u_power4,
                        'u_power5'        => $u_power5,
                        'u_power6'        => $u_power6,
                        'u_power4_profit' => $u_power4_profit,
                        'u_power5_profit' => $u_power5_profit,
                        'u_power6_profit' => $u_power6_profit
                    ];
                    $this->webdb->sqlReplace('htpg_report', $parameter);
                }
            }
        }
    }

    /**
     * 撈取單日報表(未結單與已結單)
     * @param string $YMD 日期(YYYYmmdd)
     */
    private function buildReportAll($YMD)
    {

        $result = $this->htpg->reporter_all($YMD);
        if (count($result) > 0) {
            $okPath = '/var/www/html/report_file/' . $YMD . '/ok_list.txt';
            $okList = [];
            if (file_exists($okPath) && filesize($okPath) > 0) {
                $fp = fopen($okPath, 'r');
                $okList = json_decode(fread($fp, filesize($okPath)), true);
                fclose($fp);
            }
            foreach ($result as $list) {
                try {
                    if (in_array(basename($list), $okList)) {
                        continue;
                    }
                    $file = fopen($list, "r");
                    $jsonList = json_decode(fread($file, filesize($list)));
                    fclose($file);
                    $this->intoDB($jsonList);
                    array_push($okList, basename($list));
                } catch (Exception $e) {
                    unlink($list);
                    echo $list . ' 寫入失敗 ; ' . $e->getMessage() . '<br>';
                }
            }
            $fp = fopen($okPath, 'w');
            fwrite($fp, json_encode($okList));
            fclose($fp);
        }
    }

    /**
     * 手動補帳
     */
    public function auto_report()
    {
        if (!$this->agent->is_referral()) {
            if ($this->input->is_ajax_request()) {
                if ($this->input->post('sTime') != '' && $this->input->post('eTime') != '') {
                    date_default_timezone_set("Asia/Taipei");
                    $report_count = $this->get_report($this->input->post('sTime', true), $this->input->post('eTime', true), 100, 1, 'CreateAt');
                    echo json_encode(array('RntCode' => 'Y', 'Msg' => '補帳完畢！'));
                } else {
                    echo json_encode(array('RntCode' => 'N', 'Msg' => '請設定補帳日期'));
                }
            } else {
                echo json_encode(array('RntCode' => 'N', 'Msg' => '不允許的方法'));
            }
        } else {
            echo json_encode(array('RntCode' => 'N', 'Msg' => '網域不被允許'));
        }
    }

    /**
     * Add two arbitrary precision numbers
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
     * @return string
     */
    private function doBcAdd($leftOperand, $rightOperand, $scale = self::DECIMAL_POINT)
    {
        return bcadd($leftOperand, $rightOperand, $scale);
    }

    /**
     * Subtract one arbitrary precision number from another
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
     * @return string
     */
    private function doBcSub($leftOperand, $rightOperand, $scale = self::DECIMAL_POINT)
    {
        return bcsub($leftOperand, $rightOperand, $scale);
    }

    /**
     * Multiply two arbitrary precision numbers
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
     * @return string
     */
    private function doBcMul($leftOperand, $rightOperand, $scale = self::DECIMAL_POINT)
    {
        return bcmul($leftOperand, $rightOperand, $scale);
    }

    /**
     * Divide two arbitrary precision numbers
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale 小數位數
     * @return string
     */
    private function doBcDiv($leftOperand, $rightOperand, $scale = self::DECIMAL_POINT)
    {
        return bcdiv($leftOperand, $rightOperand, $scale);
    }

}

?>