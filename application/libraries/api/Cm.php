<?php
defined('BASEPATH') OR exit( 'No direct script access allowed' );

class Cm {
	var $CI;
	var $timeout = CM_timeout;    //curl允許等待秒數
	var $api_url = CM_API_URL;  // 基本上不異動
	var $key = CM_api_key;       // 正式環境
	var $agent = CM_Agent;
	var $lang = CM_lang;         // 基本上不異動

    const GAME_MAKER_NUM = 62;
    public function __construct ()
    {
        $this->CI =& get_instance();
        date_default_timezone_set("Asia/Taipei");
    }
    private function hash($data){
        $hash = '';
        $i = 0;
        $data['hash_key'] = $this->key;
        ksort($data);
        foreach($data as $key => $val){
            if($i != 0)$hash .= '&';
            $hash .= $key.'='.$val;
            $i++;
        }
        return md5($hash);
    }

    public function create_account ($u_id, $u_password, $mem_num, $gamemaker_num = self::GAME_MAKER_NUM)
    {    //創建遊戲帳號
        //先判定此會員是否已有帳號
        $parameter = [ ];
        $sqlStr = "select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
        $parameter[ ':u_id' ] = trim($u_id);
        $parameter[ ':mem_num' ] = $mem_num;
        $parameter[ ':gamemaker_num' ] = $gamemaker_num;
        $row = $this->CI->webdb->sqlRow($sqlStr, $parameter);
        if ($row == null) {    //無帳號才呼叫api
            $post_data = [
                'agent_id' => $this->agent,
                'account' => $u_id
            ];
            $post_data['api'] = 'create_member';
            $post_data['hash'] = $this->hash($post_data);
            $output = $this->curl($post_data);

            if ($output) {
                if ($output->code == 0) {
                    $colSql = "u_id,mem_num,gamemaker_num,u_password";
                    $upSql = "INSERT INTO `games_account` (" . sqlInsertString($colSql, 0) . ") VALUES (" . sqlInsertString($colSql, 1) . ")";
                    $parameter = [ ];
                    $parameter[ ':u_id' ] = trim($u_id);
                    $parameter[ ':mem_num' ] = $mem_num;
                    $parameter[ ':gamemaker_num' ] = $gamemaker_num;
                    $parameter[ ':u_password' ] = $this->CI->encryption->encrypt($u_password);
                    $this->CI->webdb->sqlExc($upSql, $parameter);

                    return null;
                } else {
                    return '創建失敗:' . $output->code;
                }
            } else {
                return '系統繁忙中，請稍後再試';
            }
        } else {
            return '會員已有此類型帳號';
        }
    }


    public function forward_game ($u_id)
    {    //登入遊戲
        $post_data = [
            'agent_id' => $this->agent,
            'account' => $u_id
        ];
        $post_data['api'] = 'login';
        $post_data['hash'] = $this->hash($post_data);
        $output = $this->curl($post_data);

        if ($output) {
            if ($output->code == 0) {
                return $output->data;
            }
        }
    }

    public function get_balance ($u_id)
    {    //餘額查詢
        $post_data = [
            'agent_id' => $this->agent,
            'account' => $u_id
        ];
        $post_data['api'] = 'check_wallet';
        $post_data['hash'] = $this->hash($post_data);
        $output = $this->curl($post_data);
        //print_r($output);exit;
        if ($output) {
            if ($output->code == 0) {
                return number_format( $output->data,2);
            } else {
                return '--';
            }
        } else {
            return '--';
        }
    }

    public function deposit ($u_id, $amount, $mem_num, $gamemaker_num = self::GAME_MAKER_NUM, $logID = null)
    {    //轉入點數到遊戲帳號內
        $tx_id = time() . mt_rand(1111, 9999);
        //將轉點編號寫入DB
        $upSql = "UPDATE `member_wallet_log` SET `TradeNo`='" . $tx_id . "' where num=" . $logID;
        $this->CI->webdb->sqlExc($upSql);

        $post_data = [
            'agent_id' => $this->agent,
            'account' => $u_id,
            'action' => 1,
            'total' => $amount
        ];
        $post_data['api'] = 'set_wallet';
        $post_data['hash'] = $this->hash($post_data);
        $output = $this->curl($post_data);
        $balance = $this->get_balance($u_id);

        if($balance == '--')$balance = 0;
        if ($output) {
            if ($output->code == 0) {
                $parameter = [ ];
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance - (float)$amount;//異動後點數
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter[ ":mem_num" ] = $mem_num;
                $parameter[ ":kind" ] = 3;    //轉入遊戲
                $parameter[ ":points" ] = "-" . $amount;
                $parameter[ ":makers_num" ] = $gamemaker_num;
                $parameter[ ":makers_balance" ] = str_replace(',','', $balance);
                $parameter[ ":admin_num" ] = tb_sql("admin_num", "member", $mem_num);
                $parameter[ ":buildtime" ] = now();
                $parameter[ ':before_balance' ] = $before_balance;
                $parameter[ ':after_balance' ] = $after_balance;
                $this->CI->webdb->sqlExc($sqlStr, $parameter);

                return null;
            } else {
                return '轉點失敗:' . $output->code;
            }
        } else {
            return '系統繁忙中，請稍後再試';
        }
    }

    public function withdrawal ($u_id, $amount, $mem_num, $gamemaker_num = self::GAME_MAKER_NUM, $logID = null)
    {    //遊戲點數轉出
        $tx_id = time() . mt_rand(1111, 9999);
        //將轉點編號寫入DB
        $upSql = "UPDATE `member_wallet_log` SET `TradeNo`='" . $tx_id . "' where num=" . $logID;
        $this->CI->webdb->sqlExc($upSql);

        $post_data = [
            'agent_id' => $this->agent,
            'account' => $u_id,
            'action' => 0,
            'total' => $amount
        ];
        $post_data['api'] = 'set_wallet';
        $post_data['hash'] = $this->hash($post_data);
        $output = $this->curl($post_data);
        $balance = $this->get_balance($u_id);

        if($balance == '--')$balance = 0;
        if ($output) {
            if ($output->code == 0) {
                $parameter = [ ];
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance + (float)$amount;//異動後點數
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter[ ":mem_num" ] = $mem_num;
                $parameter[ ":kind" ] = 4;    //遊戲轉出
                $parameter[ ":points" ] = $amount;
                $parameter[ ":makers_num" ] = $gamemaker_num;
                $parameter[ ":makers_balance" ] = str_replace(',','',$balance);
                $parameter[ ":admin_num" ] = tb_sql("admin_num", "member", $mem_num);
                $parameter[ ":buildtime" ] = now();
                $parameter[ ':before_balance' ] = $before_balance;
                $parameter[ ':after_balance' ] = $after_balance;
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                return null;
            } else {
                return '轉點失敗:' . $output->code;
            }
        } else {
            return '系統繁忙中，請稍後再試';
        }
    }
    public function reporter_all($startdate, $enddate){
        $post_data = [
            'agent_id' => $this->agent,
            'sdate' => strtotime($startdate),
            'edate' => strtotime($enddate)
        ];
        $post_data['api'] = 'get_report';
        $post_data['hash'] = $this->hash($post_data);
        $output = $this->curl($post_data);
        if ($output) {
            if ($output->code == 0) {
                return json_decode(json_encode($output->data));
            }
        }
    }
    public function curl ($post_data)
    {
        $headers = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        $curl_error = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 200 && !$curl_error) {
            return $output;
        }
    }

}

?>
