<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Egapi {
    var $CI;
    var $timeout = EG_timeout;	//curl允許等待秒數

	public function __construct(){
		$this->CI = &get_instance();
	}

	public function agentToken(){
        $post_data = array('clientId' => EG_ClientId, 'clientKey' => EG_ClientKey);
        $post_data = json_encode($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, EG_API_URL . "/client/auth");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this -> timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this -> timeout);
        $output = json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
//        print_r($output->result->code);
//        exit;
        if(!$curl_errno){
            if($http_code == 200 && $output -> code == "0000"){
                return $output -> result -> code;
            }
        }
	}

    public function EGConnect($post_data, $path){
        $post_data = json_encode($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, EG_API_URL . $path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  //Post Fields
        //print_r($post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array('Content-Type: application/json', 'code:'.$this->agentToken(), 'Content-Length: ' . strlen($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //print_r($headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this -> timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this -> timeout);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        return array('httpCode' => $httpCode, 'response' => $response, 'curl_errno' => $curl_errno);
    }
	
	public function create_account($u_id, $u_password, $mem_num, $gamemaker_num = 64){
		$parameter = array();
		$sqlStr = "select * from `games_account` where `mem_num` = ? and `gamemaker_num` = ?";
		$parameter[':mem_num'] = $mem_num;
		$parameter[':gamemaker_num'] = $gamemaker_num;
		$row = $this -> CI -> webdb -> sqlRow($sqlStr, $parameter);
		if($row == NULL){	//該館遊戲帳號不存在則呼叫API

            $path = '/player/create';
            $post_data = array(
                "userId" => $u_id,
                "parentId" => EG_ParentId
            );
            $output = $this -> EGConnect($post_data, $path);
            $outputJson = json_decode($output['response']);

			if(!$output['curl_errno']){
				if($output['httpCode'] == 200 && $outputJson -> code == "0000"){
					$colSql = "u_id,mem_num,gamemaker_num";
					$upSql = "INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter = array();
					$parameter[':u_id'] = trim($u_id);
					$parameter[':mem_num'] = $mem_num;
					$parameter[':gamemaker_num'] = $gamemaker_num;
					$this -> CI -> webdb -> sqlExc($upSql, $parameter);
					return NULL;
				}else{
					return '創建失敗：'.$outputJson -> code.':'.$outputJson -> msg;
				}
			}else{
				return '系統繁忙中，請稍後再試！';
			}
		}else{
			return '會員已有此類型帳號';	
		}
	}

	public function get_balance($u_id){	//餘額查詢
        $path = '/player/getBalance';
        $post_data = array(
            "userId" => $u_id
        );
        $output = $this -> EGConnect($post_data, $path);
        $outputJson = json_decode($output['response']);
        if(!$output['curl_errno']){
            if($output['httpCode'] == 200 && $outputJson -> code == "0000"){
				return $outputJson -> result -> result -> balance;
			}else{
                return '查詢餘額失敗：'.$outputJson -> code.':'.$outputJson -> msg;
			}
		}else{
            return '系統繁忙中，請稍後再試！';
		}
	}
	
	//轉入遊戲 type=1;轉出遊戲 type=2
	public function deposit($u_id, $amount, $mem_num, $gamemaker_num=64, $logID = NULL){	//轉入點數到遊戲帳號內
		$out_trade_no = EG_StationID.time().mt_rand(1111, 9999);
        $timestamp = date("Y-m-d H:i:s");
		//將轉點編號寫入DB
		$upSql = "UPDATE `member_wallet_log` SET `TradeNo` = '".$out_trade_no."' where num = ".$logID;
		$this -> CI -> webdb -> sqlExc($upSql);

        $path = '/player/credit';
        $post_data = array(
            "userId" => trim($u_id),
            "amount" => trim($amount),
            "ptxid" => $out_trade_no,
            "timestamp" => $timestamp
        );
        $output = $this -> EGConnect($post_data, $path);
        $outputJson = json_decode($output['response']);
        //print_r($outputJson);

        /*
         stdClass Object
        (
            [result] => stdClass Object
            (
                [result] => stdClass Object
                (
                    [amount] => 220
                    [txid] => 0fe227ac-e245-47f0-bd46-70d6a7c5c570
                    [beforeBalance] => 170
                    [afterBalance] => 220
                    [userId] => U88Test123
                    [ptxid] => RST_U8815555761802707
                )
                [code] => 0000
            )
            [code] => 0000
        )
        */
        if(!$output['curl_errno']) {
            if($output['httpCode'] == 200 && $outputJson -> result -> code == "0000") {
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;    //異動前點數
                $after_balance = (float)$before_balance - (float)$amount;//異動後點數
                $parameter = array();
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter[":mem_num"] = $mem_num;
                $parameter[":kind"] = 3;    //轉入遊戲
                $parameter[":points"] = "-" . $amount;
                $parameter[":makers_num"] = $gamemaker_num;
                $parameter[":makers_balance"] = $outputJson->result->result->afterBalance;
                $parameter[":admin_num"] = tb_sql("admin_num", "member", $mem_num);
                $parameter[":buildtime"] = now();
                $parameter[':before_balance'] = $before_balance;
                $parameter[':after_balance'] = $after_balance;
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                return NULL;
			}else{
                return '轉點錯誤：' . $outputJson->result->code . ':' . $outputJson->result->errMsg;
			}
		}else{
			return $this -> point_checking($out_trade_no,1, $u_id, $mem_num, $gamemaker_num);
			//return '系統繁忙中，請稍候再試';
		}
	}

	//轉入遊戲 type=1;轉出遊戲 type=2
	public function withdrawal($u_id, $amount, $mem_num, $gamemaker_num = 64, $logID = NULL){	//轉出遊戲點數到錢包內
		$out_trade_no = EG_StationID.time().mt_rand(1111,9999);
        $timestamp = date("Y-m-d H:i:s");
		//將轉點編號寫入DB
		$upSql = "UPDATE `member_wallet_log` SET `TradeNo`='".$out_trade_no."' where num=".$logID;
		$this -> CI -> webdb -> sqlExc($upSql);

        $path = '/player/debit';
        $post_data = array(
            "userId" => $u_id,
            "amount" => $amount,
            "ptxid" => $out_trade_no,
            "timestamp" => $timestamp
        );
        $output = $this -> EGConnect($post_data, $path);
        $outputJson = json_decode($output['response']);
        //print_r($outputJson);
/*成功
        stdClass Object
        (
            [result] => stdClass Object
            (
                [result] => stdClass Object
                (
                    [amount] => 670
                    [txid] => 4760ea61-fd0a-45c4-935f-37abbe15fe5d
                    [beforeBalance] => 720
                    [afterBalance] => 670
                    [userId] => U88Test123
                    [ptxid] => RST_U8815555754838297
                )
                [code] => 0000
            )
            [code] => 0000
        )
 */

        if(!$output['curl_errno']){
            if($output['httpCode'] == 200 && $outputJson->result->code == "0000") {
                $WalletTotal = getWalletTotal($mem_num);    //會員餘額
                $before_balance = (float)$WalletTotal;//異動前點數
                $after_balance = (float)$before_balance + (float)$amount;//異動後點數
                $parameter = array();
                $colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr = "INSERT INTO `member_wallet` (" . sqlInsertString($colSql, 0) . ")";
                $sqlStr .= " VALUES (" . sqlInsertString($colSql, 1) . ")";
                $parameter[":mem_num"] = $mem_num;
                $parameter[":kind"] = 4;    //遊戲轉出
                $parameter[":points"] = $amount;
                $parameter[":makers_num"] = $gamemaker_num;
                $parameter[":makers_balance"] = $outputJson->result->result->afterBalance;
                $parameter[":admin_num"] = tb_sql("admin_num", "member", $mem_num);
                $parameter[":buildtime"] = now();
                $parameter[':before_balance'] = $before_balance;
                $parameter[':after_balance'] = $after_balance;
                $this->CI->webdb->sqlExc($sqlStr, $parameter);
                return NULL;
			}else{
                return '轉點錯誤：' . $outputJson->result->code . ':' . $outputJson->result->errMsg;
			}
		}else{
			return $this -> point_checking($out_trade_no,2, $u_id, $amount, $mem_num, $gamemaker_num);
			//return '系統繁忙中，請稍候再試';	
		}
	}
	
	public function point_checking($out_trade_no, $type, $u_id, $amount, $mem_num, $gamemaker_num){	//點查轉帳情況
        $startTime = date("Y-m-d H:i:s",strtotime("-1 hours"));
        $endTime = date("Y-m-d H:i:s");
        $path = '/report/getTransferHistory';
        $post_data = array(
            "startTime" => $startTime, //查詢區間不得超過一天的時間
            "endTime" => $endTime,
            //  "userId" => $u_id,    //(選)
            //  "type" => $type,        //(選) 1 存 2 提
            "ptxid" => $out_trade_no       //(選) 交易代碼(營運商)
        );
        $output = $this -> EGConnect($post_data, $path);
        $outputJson = json_decode($output['response']);

        if(!$output['curl_errno']){
            if($output['httpCode'] == 200 && $outputJson -> code == "0000"){
				$WalletTotal = getWalletTotal($mem_num);	//錢包餘額
				if($type == 1){	//轉入遊戲
                    $parameter = array();
					$before_balance = (float)$WalletTotal;	//異動前點數
					$after_balance = (float)$before_balance - (float)$amount;//異動後點數
					$colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr = "INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr .= " VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"] = $mem_num;
					$parameter[":kind"] = 3;	//轉入遊戲
					$parameter[":points"] = "-".$amount;
					$parameter[":makers_num"] = $gamemaker_num;
					$parameter[":makers_balance"] = $outputJson -> result -> result -> afterBalance;
					$parameter[":admin_num"] = tb_sql("admin_num","member", $mem_num);
					$parameter[":buildtime"] = now();
					$parameter[':before_balance'] = $before_balance;
					$parameter[':after_balance'] = $after_balance;
					$this -> CI -> webdb -> sqlExc($sqlStr, $parameter);
					return NULL;
				} else {	//轉出遊戲
					$parameter = array();
					$before_balance = (float)$WalletTotal;//異動前點數
					$after_balance = (float)$before_balance + (float)$amount;//異動後點數
					$colSql = "mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
					$sqlStr = "INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
					$sqlStr .= " VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":mem_num"] = $mem_num;
					$parameter[":kind"] = 4;	//遊戲轉出
					$parameter[":points"] = $amount;
					$parameter[":makers_num"] = $gamemaker_num;
					$parameter[":makers_balance"] = $outputJson -> result -> result -> afterBalance;
					$parameter[":admin_num"] = tb_sql("admin_num","member", $mem_num);
					$parameter[":buildtime"] = now();
					$parameter[':before_balance'] = $before_balance;
					$parameter[':after_balance'] = $after_balance;
					$this -> CI -> webdb -> sqlExc($sqlStr, $parameter);
					return NULL;
				}
			} else {
				return '查詢轉點錯誤：'.$outputJson -> code.':'.$outputJson -> msg;
			}
		} else {
			return '系統繁忙中，請稍候再試';
		}
	}

	public function login($u_id){ //玩家登入
        $path = '/player/login';
        $post_data = array(
            "userId" => $u_id
        );
        return $this -> EGConnect($post_data, $path);
    }

	public function forward_game($u_id){	//登入遊戲
        $login = $this -> login($u_id);
        $loginJson = json_decode($login['response']);
        $path = '/player/enterGame';
        $post_data = array(
            "token" => $loginJson->result->token,
            "gameId" => EG_GameID,
            "returnUrl" => EG_ReturnUrl
        );
        $output = $this -> EGConnect($post_data, $path);
        $outputJson = json_decode($output['response']);

        if(!$output['curl_errno']){
            if($output['httpCode'] == 200 && $outputJson->code == "0000"){
				if(!$this -> CI -> agent -> is_mobile()){	//電腦版
					return $outputJson -> result -> gameUrl;
				}else{
					return $outputJson -> result -> gameUrl;
				}
			}else{
				return '登入遊戲錯誤：'.$outputJson -> code.':'.$outputJson -> msg;
			}
		}else{
			return '系統繁忙中，請稍候再試';	
		}
	}

    public function reporter_all($sTime,$eTime){  //報表
        $path = '/report/getGameHistory';
        $post_data = array(
            "startTime" => $sTime, //查詢區間不得超過一小時
            "endTime" => $eTime,
            //"userId" => $userId,    //(選)
            //"parentId" => parentId  //(選)
        );
        $output = $this -> EGConnect($post_data, $path);
        $outputJson = json_decode($output['response']);

        if(!$output['curl_errno']){
            if($output['httpCode'] == 200 && $outputJson -> code == "0000"){
                return $outputJson;
            }
        }
    }

    public function TransferHistory($u_id,$sTime,$eTime){  //轉帳紀錄
        $path = '/report/getTransferHistory';
        $post_data = array(
            "startTime" => $sTime, //查詢區間不得超過一小時
            "endTime" => $eTime,
            "userId" => $u_id,    //(選)
            //"parentId" => parentId  //(選)
        );
        $output = $this -> EGConnect($post_data, $path);
        $outputJson = json_decode($output['response']);
        if(!$output['curl_errno']){
            if($output['httpCode'] == 200 && $outputJson -> code == "0000"){
                return $outputJson;
            }
        }
    }
	
}
