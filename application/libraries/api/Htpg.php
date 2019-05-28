<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Htpg
{
    var $CI;
    var $timeout = 15;    //curl允許等待秒數
    var $api_url = 'http://wj.pg.live';
    var $game_url = 'https://livefront.pg.live/#/main/liveList?token=';
    var $partner = 526172657;    //外接商户ID
    var $sign = 'LCpJjwGp3VjXhShuy71FnUIohpUs4u1e2QC';    //外接商户密钥
    var $parentLabel = 'penh';    //所屬代理識別
    //報表FTP設定
    var $report_url = '58.82.236.30';    //FTP位置
    var $report_id = 'taojin';    //FTP帳號
    var $report_pw = 'yv64sbucnqPN'; //FTP 密碼
    //定義排除目錄
    var $denyDir = array('worldcup', 'bushui', 'gift');    //排除禮物單、世界盃單據、歷史單據 就是單純撈彩球的
    const GAME_MAKER_NUM = 41;
    const EXCHANGE_RATE = 5;    //人民幣匯率

    public function __construct()
    {
        $this->CI =& get_instance();
        date_default_timezone_set("Asia/Taipei");
        $this->CI->load->library('ftp');
    }

    /**
     * @param string $u_id
     * @param string $u_password
     * @param int $mem_num
     * @param int $gamemaker_num
     * @return null|string
     */
    public function create_account($u_id, $u_password, $mem_num, $gamemaker_num = self::GAME_MAKER_NUM)
    {
        //先判定此會員是否已有帳號
        $sqlStr = "select * from `games_account` where  `mem_num`=? and `gamemaker_num`=?";
        $parameter = [
            ':mem_num'       => $mem_num,
            ':gamemaker_num' => $gamemaker_num
        ];
        $row = $this->CI->webdb->sqlRow($sqlStr, $parameter);
        if ($row == NULL) {    //無帳號才呼叫api
            $parameter = [
                'partner'     => $this->partner,
                'orderNumber' => time(),
                'username'    => trim($u_id),
                'password'    => trim($u_password),
                'sign'        => $this->sign
            ];
            $post_data = array_merge($parameter, array('parentLabel' => $this->parentLabel));    //其餘要傳入的參數但不列入Token規則
            $result = $this->createToken($parameter);//生成验证码及get参数
            $url = $this->api_url . '/register-user?' . http_build_query($post_data) . '&token=' . $result['token'];
            $output = $this->getRemoteData($url);
            if ($output) {
                if ($output->status == 1) {
                    $colSql = "u_id,u_password,mem_num,gamemaker_num";
                    $upSql = "INSERT INTO `games_account` (" . sqlInsertString($colSql, 0) . ") VALUES (" . sqlInsertString($colSql, 1) . ")";
                    $parameter = [
                        ':u_id'          => trim($u_id),
                        ':u_password'    => $this->CI->encryption->encrypt($u_password),    //密碼加密
                        ':mem_num'       => $mem_num,
                        ':gamemaker_num' => $gamemaker_num
                    ];
                    $this->CI->webdb->sqlExc($upSql, $parameter);
                    return NULL;
                } else {
                    return '創建失敗:' . $output->status . ':' . $output->error;
                }
            } else {
                return '系統繁忙中，請稍後再試';
            }
        } else {
            return '會員已有此類型帳號';
        }
    }

    /**
     * 查詢餘額
     * @param string $u_id
     * @return string
     */
    public function get_balance($u_id)
    {
        $parameter = [
            'partner'     => $this->partner,
            'orderNumber' => time(),
            'username'    => trim($u_id),
            'sign'        => $this->sign
        ];
        $post_data = array_merge($parameter, array('parentLabel' => $this->parentLabel));    //其餘要傳入的參數但不列入Token規則
        $result = $this->createToken($parameter);//生成验证码及get参数
        $url = $this->api_url . '/balance-user?' . http_build_query($post_data) . '&token=' . $result['token'];
        $output = $this->getRemoteData($url);
        if ($output) {
            if ($output->status == 1) {
                return bcmul($output->balance, 5, 2);
            } else {
                return '--';
            }
        } else {
            return '--';
        }

    }

    /**
     * 點數轉入(type 說明 in：從第三方轉入到盤古彩票，out：從盤古彩票轉出到第三方)
     * @param string $u_id
     * @param int $amount
     * @param int $mem_num
     * @param int $gamemaker_num
     * @param null $logID
     * @return null|string
     */
    public function deposit($u_id, $amount, $mem_num, $gamemaker_num = self::GAME_MAKER_NUM, $logID = NULL)
    {    //轉入點數到遊戲帳號內
        $transationNo = time() . mt_rand(1111, 9999);
        $parameter = [
            'partner'      => $this->partner,
            'orderNumber'  => time(),
            'username'     => trim($u_id),
            'type'         => 'in',
            'amount'       => trim($amount),
            'transationNo' => $transationNo,
            'sign'         => $this->sign
        ];//產生Token用的
        $post_data = array_merge($parameter, array());    //其餘要傳入的參數但不列入Token規則
        $result = $this->createToken($parameter);//生成验证码及get参数
        $url = $this->api_url . '/transfer-user?' . http_build_query($post_data) . '&currency=TWD&token=' . $result['token'];
        $output = $this->getRemoteData($url);
        if ($output) {
            if ($output->status == 1) {
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance - (float)$amount;//異動後點數
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter = [
                    ":mem_num"        => $mem_num,
                    ":kind"           => 3,//轉入遊戲
                    ":points"         => "-" . $amount,
                    ":makers_num"     => $gamemaker_num,
                    ":makers_balance" => $output->balance,
                    ":admin_num"      => tb_sql("admin_num", "member", $mem_num),
                    ":buildtime"      => now(),
                    ":before_balance" => $before_balance,
                    ":after_balance"  => $after_balance
                ];
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                //將轉點編號寫入DB
                $upSql = "UPDATE `member_wallet_log` SET `TradeNo`='" . $transationNo . "' where num=" . $logID;
                $this->CI->webdb->sqlExc($upSql);
                return NULL;
            } else {
                return '轉點失敗:' . $output->status . ':' . $output->error;
            }
        } else {
            return $this->point_checking($transactionNo, $post_data["type"], $amount, $mem_num, $gamemaker_num);
        }

    }

    /**
     * 點數轉出(type 說明 in：從第三方轉入到盤古彩票，out：從盤古彩票轉出到第三方)
     * @param string $u_id
     * @param int $amount
     * @param int $mem_num
     * @param int $gamemaker_num
     * @param null $logID
     * @return null|string
     */
    public function withdrawal($u_id, $amount, $mem_num, $gamemaker_num = self::GAME_MAKER_NUM, $logID = NULL)
    {
        $transationNo = time() . mt_rand(1111, 9999);
        $parameter = [
            'partner'      => $this->partner,
            'orderNumber'  => time(),
            'username'     => trim($u_id),
            'type'         => 'out',
            'amount'       => trim($amount),
            'transationNo' => $transationNo,
            'sign'         => $this->sign
        ];
        $post_data = array_merge($parameter, array());    //其餘要傳入的參數但不列入Token規則
        $result = $this->createToken($parameter);//生成验证码及get参数
        $url = $this->api_url . '/transfer-user?' . http_build_query($post_data) . '&currency=TWD&token=' . $result['token'];
        $output = $this->getRemoteData($url);
        if ($output) {
            if ($output->status == 1) {
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance + (float)$amount;//異動後點數
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter = [
                    ":mem_num"        => $mem_num,
                    ":kind"           => 4,
                    ":points"         => $amount,
                    ":makers_num"     => $gamemaker_num,
                    ":makers_balance" => $output->balance,
                    ":admin_num"      => tb_sql("admin_num", "member", $mem_num),
                    ":buildtime"      => now(),
                    ":before_balance" => $before_balance,
                    ":after_balance"  => $after_balance
                ];
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                //將轉點編號寫入DB
                $upSql = "UPDATE `member_wallet_log` SET `TradeNo`='" . $transationNo . "' where num=" . $logID;
                $this->CI->webdb->sqlExc($upSql);
                return NULL;
            } else {
                return '轉點失敗:' . $output->status . ':' . $output->error;
            }
        } else {
            return $this->point_checking($transationNo, $post_data["type"], $amount, $mem_num, $gamemaker_num);
        }

    }

    /**
     * 查詢轉帳狀況
     * @param string $transationNo
     * @param $type
     * @param $amount
     * @param $mem_num
     * @param $gamemaker_num
     * @return null|string
     */

    public function point_checking($transationNo, $type, $amount, $mem_num, $gamemaker_num = self::GAME_MAKER_NUM)
    {
        $parameter = [
            'partner'      => $this->partner,
            'orderNumber'  => time(),
            'transationNo' => $transationNo,
            'sign'         => $this->sign
        ];
        $post_data = array_merge($parameter, array());    //其餘要傳入的參數但不列入Token規則
        $result = $this->createToken($parameter);//生成验证码及get参数
        $url = $this->api_url . '/transfer-status?' . http_build_query($post_data) . '&token=' . $result['token'];
        $output = $this->getRemoteData($url);
        if ($output) {
            if ($output->status == 1) {
                $WalletTotal = getWalletTotal($mem_num);    //錢包餘額
                $makers_balance = ($this->get_balance($u_id) != '--' ? $this->get_balance($u_id) : 0);
                if ($type == 'in') {    //轉入遊戲
                    $before_balance = (float)$WalletTotal;    //異動前點數
                    $after_balance = (float)$before_balance - (float)$amount;//異動後點數
                    $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                    $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                    $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                    $parameter = [
                        ":mem_num"        => $mem_num,
                        ":kind"           => 3,
                        ":points"         => "-" . $amount,
                        ":makers_num"     => $gamemaker_num,
                        ":makers_balance" => $makers_balance,
                        ":admin_num"      => tb_sql("admin_num", "member", $mem_num),
                        ":buildtime"      => now(),
                        ":before_balance" => $before_balance,
                        ":after_balance"  => $after_balance
                    ];
                    $this->CI->webdb->sqlExc($sqlStr, $parameter);
                    return NULL;
                } else {
                    $before_balance = (float)$WalletTotal;//異動前點數
                    $after_balance = (float)$before_balance + (float)$amount;//異動後點數
                    $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                    $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                    $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                    $parameter = [
                        ":mem_num"        => $mem_num,
                        ":kind"           => 4,
                        ":points"         => $amount,
                        ":makers_num"     => $gamemaker_num,
                        ":makers_balance" => $makers_balance,
                        ":admin_num"      => tb_sql("admin_num", "member", $mem_num),
                        ":buildtime"      => now(),
                        ":before_balance" => $before_balance,
                        ":after_balance"  => $after_balance
                    ];
                    $this->CI->webdb->sqlExc($sqlStr, $parameter);
                    return NULL;
                }
            } else {
                return '查詢轉點失敗:' . $output->status . ':' . $output->error;
            }
        } else {
            return '系統繁忙中，請稍後再試';
        }
    }

    /**
     * 登入遊戲
     * @param string $u_id
     * @param string $u_password
     * @return string
     */
    public function forward_game($u_id, $u_password)
    {
        $parameter = [
            'partner'     => $this->partner,
            'orderNumber' => time(),
            'username'    => trim($u_id),
            'password'    => trim($u_password),
            'sign'        => $this->sign
        ];
        //登陆方式：1电脑版，2手机网页版，3手机APP
        $login_side = (!$this->CI->agent->is_mobile() ? 1 : 2);
        $post_data = array_merge($parameter, array('login_side' => $login_side, 'ip' => $this->CI->input->ip_address()));    //其餘要傳入的參數但不列入Token規則
        $result = $this->createToken($parameter);//生成验证码及get参数
        $url = $this->api_url . '/login-user?' . http_build_query($post_data) . '&token=' . $result['token'];
        $output = $this->getRemoteData($url);
        if ($output) {
            if ($output->status == 1) {
                return $this->game_url . $output->token;
            }
        }
    }

    /**
     * 撈取報表(已結與未結)
     * @param string $YMD 日期字串(YYYYmmdd)
     * @return array
     */
    public function reporter_all($YMD)
    {
        $this->config_ftp();
        $list = $this->CI->ftp->list_files(date('Ym/d', strtotime($YMD)));
        $files_list = array();
        if (count($list) > 0) {
            $host_root = '/var/www/html/report_file/' . $YMD;
            if (!is_dir($host_root)) {
                mkdir($host_root, 0777);
            }
            $ok_path = '/var/www/html/report_file/' . $YMD . '/ok_list.txt';
            $ok_list = [];
            if (file_exists($ok_path) && filesize($ok_path) > 0) {
                $fp = fopen($ok_path, 'r');
                $ok_list = json_decode(fread($fp, filesize($ok_path)), true);
                fclose($fp);
            }
            foreach ($list as $row) {
                $path_parts = pathinfo($row);
                $host_path = $host_root . '/' . $path_parts['basename'];
                if (!in_array(basename($host_path), $ok_list)) {
                    if ($this->CI->ftp->download($row, $host_path)) {
                        array_push($files_list, $host_path);
                    }
                }
            }
        }
        $this->CI->ftp->close();
        return $files_list;
    }

    /**
     * 抓取FTP報表檔案(排除未結單據,補資料用),強制重置ok_list.txt
     * @param string $YMD 日期字串(YYYYmmdd)
     * @return array
     */
    public function reporter_all2($YMD)
    {
        $this->config_ftp();
        $list = $this->CI->ftp->list_files(date('Ym/d', strtotime($YMD)));
        $files_list = array();
        if (count($list) > 0) {
            $host_root = '/var/www/html/report_file/' . $YMD;
            $this->clearFolder($host_root);

            if (!is_dir($host_root)) {
                mkdir($host_root, 0777);
            }
            foreach ($list as $row) {
                $path_parts = pathinfo($row);
                $host_path = $host_root . '/' . $path_parts['basename'];
                if (substr($path_parts['basename'], 0, 1) != 'b') {    //排除未結單據
                    if ($this->CI->ftp->download($row, $host_path)) {
                        array_push($files_list, $host_path);
                    }
                }
            }
        }
        $this->CI->ftp->close();
        return $files_list;
    }

    /**
     * 新增代理帳號
     * @param string $u_id 代理帳號
     * @param string $u_password 密碼
     * @return string
     */
    public function register_agent($u_id, $u_password)
    {
        $parameter = [
            'partner'     => $this->partner,
            'orderNumber' => time(),
            'username'    => trim($u_id),
            'password'    => trim($u_password),
            'label'       => trim($u_id),
            'sign'        => $this->sign
        ];
        $result = $this->createToken($parameter);//生成验证码及get参数
        $url = $this->api_url . '/register-agent?' . http_build_query($parameter) . '&fandian=0&bet=0&token=' . $result['token'];
        $output = $this->getRemoteData($url);
        if ($output) {
            if ($output->status == 1) {
                return json_encode(['code' => 1, 'message' => '新增代理成功']);
            } else {
                return json_encode(['code' => 0, 'message' => $output->error]);
            }
        }
    }

    /**
     * 初始化FTP相關設定
     */
    private function config_ftp()
    {
        $config['hostname'] = $this->report_url;
        $config['username'] = $this->report_id;
        $config['password'] = $this->report_pw;
        $this->CI->ftp->connect($config);
    }

    /**
     * curl程式
     * @param string $url
     * @return mixed
     */
    private function getRemoteData($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        $curl_error = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code == 200 && !$curl_error) {
            return $output;
        }
    }

    /**
     * 生成md5驗證字串
     * @param array $params
     * @return array
     */
    private function createToken($params)
    {
        $str = '';
        foreach ($params as $k => $v) {
            if ($k != 'sign') {
                $str .= '&' . $k . '=' . $v;
            }
        }
        $request_str = trim($str, '&');
        $token = md5($request_str . '&sign=' . $params['sign']);
        $result = [
            'request' => $request_str,
            'token'   => $token
        ];
        return $result;

    }

    /**
     * 清除資料夾內容
     * @param string $path
     */
    private function clearFolder($path)
    {
        $clear_file = scandir($path . '/');
        foreach ($clear_file as $file_name) {
            if (!in_array($file_name, array(".", ".."))) {
                unlink($path . '/' . $file_name);
            }
        }
    }

}


?>