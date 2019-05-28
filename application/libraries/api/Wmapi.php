<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wmapi
{

    var $timeout = 15;    //curl允許等待秒數
    //var $url='http://api.kkwebs.co/api/public/Gateway.php';//測試端.
    var $url = 'https://rswb-039.wmapi88.com/api/public/Gateway.php';
    var $agentAccount = 'em66twapi';
    var $signature = '84cb99ed118e68df38fc9a342b13e76f';
    var $lang = 0;//0或空值 为简体中文
    public $errCode = array();

    public function __construct()
    {

        $this->CI =& get_instance();
        date_default_timezone_set("Asia/Taipei");
        //$this->token=md5($this->agentName.$this->key);
    }

    public function index()
    {
        $u_id = 'wellcity888api';
        $u_password = '~GR_V[z!Sfc!F3h9';

        $curl_err[1] = 'CURLE_UNSUPPORTED_PROTOCOL';
        $curl_err[2] = 'CURLE_FAILED_INIT';
        $curl_err[3] = 'CURLE_URL_MALFORMAT';
        $curl_err[5] = 'CURLE_COULDNT_RESOLVE_PROXY';
        $curl_err[6] = 'CURLE_COULDNT_RESOLVE_HOST';
        $curl_err[7] = 'CURLE_COULDNT_CONNECT';
        $curl_err[8] = 'CURLE_FTP_WEIRD_SERVER_REPLY';
        $curl_err[9] = 'CURLE_REMOTE_ACCESS_DENIED';
        $curl_err[11] = 'CURLE_FTP_WEIRD_PASS_REPLY';
        $curl_err[13] = 'CURLE_FTP_WEIRD_PASV_REPLY';

        $curl_err[14] = 'CURLE_FTP_WEIRD_227_FORMAT';
        $curl_err[15] = 'CURLE_FTP_CANT_GET_HOST';
        $curl_err[17] = 'CURLE_FTP_COULDNT_SET_TYPE';
        $curl_err[18] = 'CURLE_PARTIAL_FILE';
        $curl_err[19] = 'CURLE_FTP_COULDNT_RETR_FILE';
        $curl_err[21] = 'CURLE_QUOTE_ERROR';
        $curl_err[22] = 'CURLE_HTTP_RETURNED_ERROR';
        $curl_err[23] = 'CURLE_WRITE_ERROR';
        $curl_err[25] = 'CURLE_UPLOAD_FAILED';
        $curl_err[26] = 'CURLE_READ_ERROR';

        $curl_err[27] = 'CURLE_OUT_OF_MEMORY';
        $curl_err[28] = 'CURLE_OPERATION_TIMEDOUT';
        $curl_err[30] = 'CURLE_FTP_PORT_FAILED';
        $curl_err[31] = 'CURLE_FTP_COULDNT_USE_REST';
        $curl_err[33] = 'CURLE_RANGE_ERROR';
        $curl_err[34] = 'CURLE_HTTP_POST_ERROR';
        $curl_err[35] = 'CURLE_SSL_CONNECT_ERROR';
        $curl_err[36] = 'CURLE_FTP_BAD_DOWNLOAD_RESUME';
        $curl_err[37] = 'CURLE_FILE_COULDNT_READ_FILE';
        $curl_err[38] = 'CURLE_LDAP_CANNOT_BIND';


        $curl_err[39] = 'CURLE_LDAP_SEARCH_FAILED';
        $curl_err[41] = 'CURLE_FUNCTION_NOT_FOUND';
        $curl_err[42] = 'CURLE_ABORTED_BY_CALLBACK';
        $curl_err[43] = 'CURLE_BAD_FUNCTION_ARGUMENT';
        $curl_err[45] = 'CURLE_INTERFACE_FAILED';
        $curl_err[47] = 'CURLE_TOO_MANY_REDIRECTS';
        $curl_err[48] = 'CURLE_UNKNOWN_TELNET_OPTION';
        $curl_err[49] = 'CURLE_TELNET_OPTION_SYNTAX';
        $curl_err[51] = 'CURLE_PEER_FAILED_VERIFICATION';
        $curl_err[52] = 'CURLE_GOT_NOTHING';

        $curl_err[53] = 'CURLE_SSL_ENGINE_NOTFOUND';
        $curl_err[54] = 'CURLE_SSL_ENGINE_SETFAILED';
        $curl_err[55] = 'CURLE_SEND_ERROR';
        $curl_err[56] = 'CURLE_RECV_ERROR';
        $curl_err[58] = 'CURLE_SSL_CERTPROBLEM';
        $curl_err[59] = 'CURLE_SSL_CIPHER';
        $curl_err[60] = 'CURLE_SSL_CACERT';
        $curl_err[61] = 'CURLE_BAD_CONTENT_ENCODING';
        $curl_err[62] = 'CURLE_LDAP_INVALID_URL';
        $curl_err[63] = 'CURLE_FILESIZE_EXCEEDED';

        $curl_err[64] = 'CURLE_USE_SSL_FAILED';
        $curl_err[65] = 'CURLE_SEND_FAIL_REWIND';
        $curl_err[66] = 'CURLE_SSL_ENGINE_INITFAILED';
        $curl_err[67] = 'CURLE_LOGIN_DENIED';
        $curl_err[68] = 'CURLE_TFTP_NOTFOUND';
        $curl_err[69] = 'CURLE_TFTP_PERM';
        $curl_err[70] = 'CURLE_REMOTE_DISK_FULL';
        $curl_err[71] = 'CURLE_TFTP_ILLEGAL';
        $curl_err[72] = 'CURLE_TFTP_UNKNOWNID';
        $curl_err[73] = 'CURLE_REMOTE_FILE_EXISTS';


        $curl_err[74] = 'CURLE_TFTP_NOSUCHUSER';
        $curl_err[75] = 'CURLE_CONV_FAILED';
        $curl_err[76] = 'CURLE_CONV_REQD';
        $curl_err[77] = 'CURLE_SSL_CACERT_BADFILE';
        $curl_err[78] = 'CURLE_REMOTE_FILE_NOT_FOUND';
        $curl_err[79] = 'CURLE_SSH';
        $curl_err[80] = 'CURLE_SSL_SHUTDOWN_FAILED';

        $errCode[0] = "操作成功";//	该会员操作成功
        $errCode[103] = "参数错误, 代理商ID与识别码格式错误";
        $errCode[10301] = "参数错误, 代理商ID为空,请检查(vendorId)";
        $errCode[10302] = "参数错误, 有此代理商ID,但代理商代码(signature)错误";
        $errCode[10303] = "参数错误, 有此代理商ID,但代理商代码(signature)错误";
        $errCode[10304] = "参数错误, 代理商代码(signature)为空";
        $errCode[900] = "参数错误, 查无此函数";
        $errCode[10501] = "参数错误, 查无此帐号,请检查";
        $errCode[10502] = "参数错误, 帐号名不得为空";
        $errCode[10505] = "参数错误, 此帐号已被停用";
        $errCode[10507] = "参数错误, 此账号非此代理下线,不能使用此功能";
        $errCode[10201] = "参数错误, 此功能仅能查询一天内的报表，您已超过上限";
        $errCode[10801] = "参数错误	加扣点不得为零";
        $errCode[10802] = "参数错误	加扣点为空,或未设置(money)参数";
        $errCode[10803] = "参数错误	加扣点不得为汉字";
        $errCode[10804] = "操作失败	不得重复转帐";
        $errCode[10805] = "操作失败	转账失败，该账号余额不足";

        //$this->create_account($u_id,$u_password,999);
        //$this->get_balance($u_id);
        //$this->deposit($u_id,10000,999);
        //$this->withdrawal($u_id,200,999);//
        //$this->forward_game($u_id,$u_password);
        //$this->updateLimit($u_id);
        //$this->reporter_all();
    }

    /*

FORMAT 格式
Method Post
Request Array
Response Json

Request 代入参数 (贵司所需传送过来的值)
Field Type Decription
cmd (String) Hello
vendorld (String) 代理商(aid)
signature (String) 代理商标识符

Response 回传值
Field Type Decription
errorCode (Integer) 错误代码(0 :表示成功,其他为失败)
errorMessage (String) 错误讯息
result (String) 回传:HELLO


    */


    public function Hello()
    {
        $request = array(
            'cmd'       => "Hello",
            'vendorId'  => $this->agentAccount,
            'signature' => $this->signature,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  //Post Fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Content-Length: ' . strlen($request));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        //print_r($output->result);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //print_r($output);
        //		return $output->result;

        if (!$curl_errno) {
            if ($http_code === 200 && $output->errorCode == 0) {
                return $output->result;
            } else {
                return '連接錯誤：' . $output->errorCode;
            }
        } else {
            return '系統繁忙中，請稍候再試';
        }
    }


    /*

    第二步骤 :注册

    FORMAT 格式
    Method Post
    Request Array
    Response Json

    Request 代入参数
    Field Type Decription
    cmd String MemberRegister
    vendorld String 代理商(aid)
    signature String 代理商标识符
    user String 账号 <登入账号>
    password String 密码
    username String 姓名

    Response 回传值
    Field Type Decription
    errorCode Integer 错误代码(0:表示成功,
    其他为失败)
    errorMessage String 错误讯息
    result String “0”,”新增完成”


    */

    public function create_account($u_id, $u_password, $mem_num, $gamemaker_num = 13)
    {

        $parameter = array();
        $sqlStr = "select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
        $parameter[':u_id'] = trim($u_id);
        $parameter[':mem_num'] = $mem_num;
        $parameter[':gamemaker_num'] = $gamemaker_num;
        $row = $this->CI->webdb->sqlRow($sqlStr, $parameter);
        if ($row == NULL) {    //如果帳號不存在則把帳號寫入資料庫

            //echo 'in Hello1'.$this->agentAccount;
            $request = array(
                'cmd'       => "MemberRegister",
                'vendorId'  => $this->agentAccount,
                'signature' => $this->signature,
                'user'      => $u_id,
                'password'  => $u_password,
                'username'  => $u_id,
                'profile'   => 12,
                'maxwin'    => 200000    //最大贏額
            );
            //echo '<pre>';
            //print_r($request);
            //echo '</pre>';
            $response = $this->DoMethod($request);
            $errorCode = $this->GetValue($response, 'errorCode');
            $errorMessage = $this->GetValue($response, 'errorMessage');
            $MsgResult = $this->GetValue($response, 'result');
            $result = array(
                'status'       => $errorCode ? true : false,
                'errorMessage' => $errorMessage,
                'MsgResult'    => $MsgResult,
            );
            //					scriptMsg($MsgResult,"");
            //echo '<pre>';
            //print_r($result);
            //echo '</pre>';
            //exit ;
            //return $result;
            if ($errorCode == 0) {

                $colSql = "u_id,u_password,mem_num,gamemaker_num";
                $upSql = "INSERT INTO `games_account` (" . sqlInsertString($colSql, 0) . ") VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter = array();
                $parameter[':u_id'] = trim($u_id);
                $parameter[':u_password'] = $this->CI->encryption->encrypt($u_password);    //密碼加密
                $parameter[':mem_num'] = $mem_num;
                $parameter[':gamemaker_num'] = $gamemaker_num;
                $this->CI->webdb->sqlExc($upSql, $parameter);
                return NULL;

            } else {
                return $errorCode;
            }

        } else {
            return '會員已有此類型帳號';
        }

    }


    /*
FORMAT 格式
Method Post
Request Array
Response Json
Request 代入参数
Field Type Decription
cmd String GetBalance
vendorld String 代理商(aid)
signature String 代理商标识符
user String 账号
Response 回传值
Field Type Decription
errorCode Integer 错误代码(0 :表示成功,其他
为失败)
errorMessage String 错误讯息
result array
{
money:(Float ),
}



    */    //餘額查詢
    public function get_balance($u_id)
    {
        //要傳遞的参數內容
        //echo 'balance';

        $request = array(
            'cmd'       => "GetBalance",
            'vendorId'  => $this->agentAccount,
            'signature' => $this->signature,
            'user'      => $u_id,
        );
        //echo '<pre>';
        //print_r($request);
        //echo '</pre>';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  //Post Fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Content-Length: ');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        //print_r($output);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //		return $output->result;
        //echo $curl_errno.'  <br>';
        if (!$curl_errno) {
            if (isset($output)) {
                if ($http_code === 200 && $output->errorCode == 0) {
                    return ($output->result != NULL ? str_replace(',', '', $output->result) : 0);
                } else {
                    return '--';
                }
            } else {
                return '--';
            }
        } else {
            return '--';
        }


        /*
                $response = $this->DoMethod($request);
                $errorCode = $this->GetValue($response, 'errorCode');
                $errorMessage = $this->GetValue($response, 'errorMessage');
                $MsgResult = $this->GetValue($response, 'result');

                //echo '<pre>';
                //print_r($response);
                //echo '</pre>';

                $results = array(
                    'status' => $errorCode ? true : false,
                    'errorMessage' => $errorMessage,
                    'MsgResult' => $MsgResult,
                );
                //echo $MsgResult;
                return $results['MsgResult'] ;
        */
    }

    /*
    第五步骤加扣点
    FORMAT 格式
    Request 代入参数
    Method

    Post
    Request
    Array
    Response
    Json

    Field

    Type

    Decription
    cmd 	ChangeBalance
    vendorld	代理商(aid)
    signature	代理商标识符
    user 	账号
    money 	float

    点数
     Response 回传值


    Field
    Type
    Decription
    errorCode 	错误代码(0 :表示成功,其他为失败)
    errorMessage 	错误讯息
    result 	String 	用户 XXX 加点(??)成功 用户 XXX 扣点(??)成功 操作失败,会员余额不足

      操作失败,代理余额不足


    */

    public function deposit($u_id, $amount, $mem_num, $gamemaker_num = 13, $log_id = null)
    {    //轉入點數到遊戲帳號內
        $transaction_no = $this->generateTransactionNo();
        if (is_null($transaction_no)) {
            return '產生流水單號失敗';
        }
        $upSql = "UPDATE `member_wallet_log` SET `TradeNo`='" . $transaction_no . "' where num=" . $log_id;
        $this->CI->webdb->sqlExc($upSql);

        $request = array(
            'cmd'       => "ChangeBalance",
            'vendorId'  => $this->agentAccount,
            'signature' => $this->signature,
            'user'      => $u_id,
            'money'     => $amount,
            'order'     => $transaction_no
        );
        //echo '<pre>';
        //print_r($request);
        //echo '</pre>';
        /*
        $response = $this->DoMethod($request);
        print_r($response);
        $errorCode = $this->GetValue($response, 'errorCode');
        $errorMessage = $this->GetValue($response, 'errorMessage');
        $MsgResult = $this->GetValue($response, 'result');
        $result = array(
            'status' => $errorCode ? true : false,
            'errorMessage' => $errorMessage,
            'MsgResult' => $MsgResult,
        );
		*/


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  //Post Fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Content-Length: ');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        //print_r($output->result);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //print_r($output);
        //		return $output->result;

        if (!$curl_errno) {
            if ($http_code === 200 && $output->errorCode == 0) {
                $makers_balance = $this->get_balance($u_id);
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance - (float)$amount;//異動後點數
                $parameter = array();
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter[":mem_num"] = $mem_num;
                $parameter[":kind"] = 3;    //轉入遊戲
                $parameter[":points"] = "-" . $amount;
                $parameter[":makers_num"] = $gamemaker_num;
                $parameter[":makers_balance"] = $makers_balance;
                $parameter[":admin_num"] = tb_sql("admin_num", "member", $mem_num);
                $parameter[":buildtime"] = now();
                $parameter[':before_balance'] = $before_balance;
                $parameter[':after_balance'] = $after_balance;
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                //檢查轉帳狀態
                $res = $this->get_member_trade_report($u_id, $transaction_no);
                if ($res->errorCode == 0) {
                    return NULL;
                } else {
                    return $res->errorCode;
                }

                //return $output->result;
            } else {
                return $output->errorCode . $errCode[$output->errorCode];
            }
        } else {
            return '系統繁忙中，請稍候再試';
        }

        //return NULL;

    }

    /**
     * 將遊戲點數轉出
     * @param $u_id
     * @param $amount
     * @param $mem_num
     * @param int $gamemaker_num
     * @param null $log_id
     * @return null|string
     */
    public function withdrawal($u_id, $amount, $mem_num, $gamemaker_num = 13, $log_id = null)
    {   //遊戲轉出
        $transaction_no = $this->generateTransactionNo();
        if (is_null($transaction_no)) {
            return '產生流水單號失敗';
        }
        $upSql = "UPDATE `member_wallet_log` SET `TradeNo`='" . $transaction_no . "' where num=" . $log_id;
        $this->CI->webdb->sqlExc($upSql);

        $request = array(
            'cmd'       => "ChangeBalance",
            'vendorId'  => $this->agentAccount,
            'signature' => $this->signature,
            'user'      => $u_id,
            'money'     => '-' . $amount,
            'order'     => $transaction_no
        );


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  //Post Fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Content-Length: ');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        //print_r($output->result);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if (!$curl_errno) {
            if ($http_code === 200 && $output->errorCode == 0) {
                $makers_balance = $this->get_balance($u_id);
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance + (float)$amount;//異動後點數
                $parameter = array();
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter[":mem_num"] = $mem_num;
                $parameter[":kind"] = 4;    //轉入錢包
                $parameter[":points"] = $amount;
                $parameter[":makers_num"] = $gamemaker_num;
                $parameter[":makers_balance"] = $makers_balance;
                $parameter[":admin_num"] = tb_sql("admin_num", "member", $mem_num);
                $parameter[":buildtime"] = now();
                $parameter[':before_balance'] = $before_balance;
                $parameter[':after_balance'] = $after_balance;
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                //檢查轉帳狀態
                $res = $this->get_member_trade_report($u_id, $transaction_no);
                if ($res->errorCode == 0) {
                    return NULL;
                } else {
                    return $res->errorCode;
                }

                //return $output->result;
            } else {
                return $output->errorCode;
            }
        } else {
            return '系統繁忙中，請稍候再試';
        }

        /*





            $response = $this->DoMethod($request);
            $errorCode = $this->GetValue($response, 'errorCode');
            $errorMessage = $this->GetValue($response, 'errorMessage');
            $MsgResult = $this->GetValue($response, 'result');
            //echo '<pre>';
            //print_r($response);
            //echo '</pre>';

            if ($errorCode == 0){
                    //echo $amount ;
                    $parameter=array();
                    $colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime";
                    $sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
                    $sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
                    $parameter[":mem_num"]=$mem_num;
                    $parameter[":kind"]=4;	//轉入錢包
                    $parameter[":points"]=$amount;
                    $parameter[":makers_num"]=$gamemaker_num;
                    $parameter[":makers_balance"]=$makers_balance;
                    $parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
                    $parameter[":buildtime"]=now();
                    $this->CI->webdb->sqlExc($sqlStr,$parameter);
                    return NULL;
                //return $money ;
            }else{
                return $errorMessage ;
            }		//print_r($output);
            //return NULL;
            */
    }

    /*

第六步骤开游戏
FORMAT 格式
Request 代入参数
Method
 
Post
Request
Array
Response
Json
 
Field
 
Type
 
Decription
cmd 	LoginGame
vendorld 	代理商(aid)
signature	代理商标识符
user 账号
 Response 回传值
Field
 
Type
 
Decription
errorCode
Integer
错误代码(0 表示成功, 其他为失败)
errorMessage
String
错误讯息
result
 
 
String
传回登入游戏网址
*/

    public function forward_game($u_id, $u_password)
    {    //登入遊戲


        //echo 'in Hello1'.$this->agentAccount;
        $request = array(
            'cmd'       => "LoginGame",
            'vendorId'  => $this->agentAccount,
            'signature' => $this->signature,
            'user'      => $u_id,
            'lang'      => $this->lang
        );
        //echo '<pre>';
        //print_r($request);
        //echo '</pre>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  //Post Fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Content-Length: ');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        //print_r($output->result);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$curl_errno) {
            if ($http_code === 200 && $output->errorCode == 0) {


                return $output->result;
            } else {
                return $output->errorCode;
            }
        } else {
            return '系統繁忙中，請稍候再試';
        }

    }
    /*









            $response = $this->DoMethod($request);
            $errorCode = $this->GetValue($response, 'errorCode');
            $errorMessage = $this->GetValue($response, 'errorMessage');
            $MsgResult = $this->GetValue($response, 'result');
            $result = array(
                'status' => $errorCode ? true : false,
                'errorMessage' => $errorMessage,
                'MsgResult' => $MsgResult,
            );
            echo '<pre>';
            print_r($result);
            echo '</pre>';
            if ($errorCode == 0){
                return $MsgResult ;
            }else{
                return $errorMessage ;
            }
    */

    /*
    第七步骤会员报表
    FORMAT 格式
    Method

    Post
    Request
    Array
    Response
    Json
     Request 代入参数
    Field

    Type

    Decription
    cmd
    String
    GetMemberReport
    vendorld
    String
    代理商(aid)
    signature
    String
    代理商标识符
    startTime
    interger
    ex:20110424
    endTime
    interger
    ex:20160724
    user
    String

    账号
     如欲查询全部用户就不用输入 user
    14
    Response 回传值
    Field
    Type

    Decription
    errorCode
    Integer
    错误代码(0 :表示成功,其他为失败)
    errorMessage
    String
    错误讯息
    result

    array


    “user” : “username”(“string”) "betid":"1340", (interger)
    "betTime":"2016-02-23 17:33:01",(datetime)
    "bet":"500.0000", (float) "validbet":"0.0000",(float) "water":"2.5000", (float) "result":"-497.5000", (float) "betResult":"Banker", (String) "waterbet":"500", (float) "winLoss":"-500.0000",(float) "gid":"101",(interger) "event":"1855265",(interger) " eventChild":"2",(interger) "tableId":"1"(interger) “gameResult”:”"庄:♦3♦3 闲:♥9♣10"”(“string”) “gname”: “百家乐”(“string”)
    */

    public function reporter_all($sTime, $eTime)
    {

        //echo 'in Hello1'.$this->agentAccount;.
        //GetMemberReport
        $request = array(
            'cmd'       => "GetDateTimeReport",
            'vendorId'  => $this->agentAccount,
            'signature' => $this->signature,
            'user'      => "",
            'startTime' => $sTime,
            'endTime'   => $eTime,
        );


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  //Post Fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Content-Length: ');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        //print_r($request);
        //print_r($output->result);

        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$curl_errno) {
            if ($http_code === 200 && $output->errorCode == 0) {
                return $output->result;
            }
        }

    }

    /**
     * 轉點交易記錄狀態查詢
     * @param string $u_id
     * @param string $transaction_no
     * @return mixed
     */
    public function get_member_trade_report($u_id, $transaction_no = null)
    {
        $request = [
            'cmd'       => 'GetMemberTradeReport',
            'vendorId'  => $this->agentAccount,
            'signature' => $this->signature,
            'user'      => $u_id
        ];
        if (!is_null($transaction_no)) {
            $request['order'] = $transaction_no;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  //Post Fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        $output = json_decode(curl_exec($ch));
        $curl_err_no = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$curl_err_no) {
            if ($http_code === 200 && $output->errorCode == 0) {
                return $output;
            }
            return $output;
        }
        return $output;
    }

    public function DoMethod($request)
    {
        //echo 'DoMethod';
        $url = $this->url;
        $params = http_build_query($request);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //echo '<pre>DoMethod:';
        //print_r($output);
        //echo '</pre>';

        return json_decode($output);
    }

    public function GetValue($object, $key)
    {
        $value = null;
        if (is_object($object)) {
            $value = property_exists($object, $key) ? $object->$key : false;
        } else if (is_array($object)) {
            $value = !empty($object[$key]) ? $object[$key] : false;
        }
        return $value;
    }

    /**
     * 產生交易流水號
     * @return null|string
     */
    public function generateTransactionNo()
    {
        $sql = "SELECT UUID_SHORT() as transaction_no";
        $query = $this->CI->webdb->sqlRow($sql);
        if (is_null($query)) {
            return null;
        }
        return substr($query['transaction_no'], -10, 10);
    }

}
