<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class S7pkapi{
    var $agent = s7pk_agent;
    var $api_url = s7pk_api_url;
    var $api_key = s7pk_api_key;
    var $login_url = s7pk_login_url;
    var $CI;
    var $timeout=s7pk_timeout;	//curl允許等待秒數
    public function __construct(){
        $this->CI =&get_instance();
        date_default_timezone_set("Asia/Taipei");
    }

    public function create_account($u_id,$mem_num,$gamemaker_num=22){	//創建遊戲帳號
        //先判定此會員是否已有帳號
        $parameter=array();
        $sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
        $parameter[':u_id']=trim($u_id);
        $parameter[':mem_num']=$mem_num;
        $parameter[':gamemaker_num']=$gamemaker_num;
        $row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
        if($row==NULL){	//無帳號才呼叫api
            //先判定此會員是否已有帳號
            $post_data=array('userid'=>trim($u_id),'username'=>trim($u_id),'memberid'=>$this->agent,'password'=>$this->getAPIKey(trim($u_id)));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$this->api_url."/CreateUser.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
            $output = json_decode(curl_exec($ch));
            $curl_errno = curl_errno($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            //print_r($output);exit;
            if(!$curl_errno){
                if($http_code===200 && ($output->ReturnCode=='0')){	//帳號創建成功
                    $colSql="u_id,mem_num,gamemaker_num";
                    $upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
                    $parameter=array();
                    $parameter[':u_id']=trim($u_id);
                    $parameter[':mem_num']=$mem_num;
                    $parameter[':gamemaker_num']=$gamemaker_num;
                    $this->CI->webdb->sqlExc($upSql,$parameter);
                    return NULL;
                }else{
                    return $output->Description;
                }
            }else{
                return '系統繁忙中，請稍後再試'	;
            }
        }else{
            return '會員已有此類型帳號';
        }

    }

    public function get_balance($u_id){	//餘額查詢
        $post_data=array('userid'=>trim($u_id),'memberid'=>$this->agent,'password'=>$this->getAPIKey(trim($u_id)));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url."/GetUserBalance.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        $output = json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //print_r($output);exit;
        if(!$curl_errno){
            if($http_code===200 && $output->ReturnCode=='0'){
                return $output->Balance;
            }else{
                return '--';
            }
        }else{
            return '--';
        }
    }

    public function deposit($u_id,$amount,$mem_num,$gamemaker_num=22){	//轉入點數到遊戲帳號內
        $post_data=array('userid'=>trim($u_id),'amount'=>trim($amount),'memberid'=>$this->agent,'password'=>$this->getAPIKey(trim($u_id)));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url."/FundTransfer.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        $output = json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //print_r($output);exit;
        if(!$curl_errno){
            if($http_code===200 && $output->ReturnCode=='0'){
                $WalletTotal=getWalletTotal($mem_num);	//會員餘額
                $before_balance=(float)$WalletTotal;//異動前點數
                $after_balance= (float)$before_balance - (float)$amount;//異動後點數
                $parameter=array();
                $colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
                $sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
                $parameter[":mem_num"]=$mem_num;
                $parameter[":kind"]=3;	//轉入遊戲
                $parameter[":points"]="-".$amount;
                $parameter[":makers_num"]=$gamemaker_num;
                $parameter[":makers_balance"]=$output->After_Amount;
                $parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
                $parameter[":buildtime"]=now();
                $parameter[':before_balance']=$before_balance;
                $parameter[':after_balance']=$after_balance;
                $this->CI->webdb->sqlExc($sqlStr,$parameter);
                return NULL;
            }elseif($http_code===200 && $output->ReturnCode=='-1'){
                return '會員仍在遊戲中，請稍後再試';
            }else{
                return "轉點錯誤";
            }
        }else{
            return '系統繁忙中，請稍候再試';
        }
    }

    public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=22){	//遊戲點數轉出
        $post_data=array('userid'=>trim($u_id),'amount'=>"-".trim($amount),'memberid'=>$this->agent,'password'=>$this->getAPIKey(trim($u_id)));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url."/FundTransfer.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        $output = json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //print_r($output);exit;
        if(!$curl_errno){
            if($http_code===200 && $output->ReturnCode=='0'){
                $WalletTotal=getWalletTotal($mem_num);	//會員餘額
                $before_balance=(float)$WalletTotal;//異動前點數
                $after_balance= (float)$before_balance + (float)$amount;//異動後點數
                $parameter=array();
                $colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
                $sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
                $sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
                $parameter[":mem_num"]=$mem_num;
                $parameter[":kind"]=4;	//遊戲轉出
                $parameter[":points"]=$amount;
                $parameter[":makers_num"]=$gamemaker_num;
                $parameter[":makers_balance"]=$output->After_Amount;
                $parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
                $parameter[":buildtime"]=now();
                $parameter[':before_balance']=$before_balance;
                $parameter[':after_balance']=$after_balance;
                $this->CI->webdb->sqlExc($sqlStr,$parameter);
                return NULL;
            }elseif($http_code===200 && $output->ReturnCode=='-1'){
                return '會員仍在遊戲中，請稍後再試';
            }else{
                return "轉點錯誤";
                //return $output->Description;
            }
        }else{
            return '系統繁忙中，請稍候再試';
        }
    }

    public function forward_game($u_id){	//登入遊戲
        $post_data=array('userid'=>trim($u_id),'memberid'=>$this->agent,'password'=>$this->getAPIKey(trim($u_id)));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url."/GetUserKey.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        $output = json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if(!$curl_errno){
            if($http_code===200 && $output->ReturnCode=='0'){
                //echo $this->login_url.trim($u_id)."&memberid=".$this->agent."&otp=".$output->Otp;
                return $this->login_url.trim($u_id)."&memberid=".$this->agent."&otp=".$output->Otp;
                //exit;
            }else{
                return $output->Description;
            }
        }else{
            //超時處理
        }
    }

    //報表
    public function reporter_all($sTime,$eTime){
        $post_data=array('startdate'=>trim($sTime),'enddate'=>trim($eTime),'memberid'=>$this->agent,'password'=>$this->getAPIKey(trim($sTime)));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url."/GetUserDetailReportAllLevel.php?".http_build_query($post_data));

        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_TIMEOUT,180);
        $output = json_decode(curl_exec($ch));
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //echo 'api url :'.$this->api_url."/GetUserDetailReportAllLevel.php?".http_build_query($post_data).'<br>';
        //echo 'http_code:'.$http_code;
        //echo '<br>';
        //echo 'curl_errno:'.$curl_errno;
        //echo '<br>';
        //echo '回應內容:';
        //print_r($output);

        //if(!$curl_errno){
        if($http_code===200 && $output->ReturnCode=='0'){
            return $output->Detail;
            //}
        }

    }

    //取得api key組合
    private function getAPIKey($text){
        return md5($this->agent.":".trim($text).":".$this->api_key);
    }

}
?>