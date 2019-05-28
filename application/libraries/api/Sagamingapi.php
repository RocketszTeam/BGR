<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Sagamingapi {
    var $CI;
    var $timeout=SA_timeout;	        //curl允許等待秒數
    var $Checkkey=SA_Checkkey;	    //自行設定
    var $api_url=SA_api_url;	        //API URL
    var $rpt_api_url=SA_rpt_api_url;	//report URL
    var	$game_url=SA_game_url;	    //遊戲URL
    var	$Lobbycode=SA_Lobbycode;	//大廳名稱
    var	$SecretKey=SA_SecretKey;	    //私密金鑰
    var	$Md5Key=SA_Md5Key;	        //MD5金鑰
    var	$EncryptKey=SA_EncryptKey;	//DES 金鑰

    public function __construct(){
        $this->CI =&get_instance();
        //測試ID：171228
    }

    public function create_account($u_id,$mem_num,$gamemaker_num=9){	//創建遊戲帳號
        $parameter=array();
        $sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
        $parameter[':u_id']=trim($u_id);
        $parameter[':mem_num']=$mem_num;
        $parameter[':gamemaker_num']=$gamemaker_num;
        $row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
        if($row==NULL){	//無帳號才呼叫api
            $Time=date('YmdHis');
            $data=array('method'=>'RegUserInfo','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),'Checkkey'=>$this->Checkkey,'CurrencyType'=>'TWD');
            $QS=http_build_query($data);
            $q=urlencode($this->encrypt($QS));
            $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
            $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
            $output = simplexml_load_string(curl_exec($ch));
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_errno = curl_errno($ch);
            curl_close($ch);
            //print_r($output);
            if(!$curl_errno){
                if($http_code===200 && ($output->ErrorMsgId=='0' || $output->ErrorMsgId=='113')){	//帳號創建成功
                    $colSql="u_id,mem_num,gamemaker_num";
                    $upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
                    $parameter=array();
                    $parameter[':u_id']=trim($u_id);
                    $parameter[':mem_num']=$mem_num;
                    $parameter[':gamemaker_num']=$gamemaker_num;
                    $this->CI->webdb->sqlExc($upSql,$parameter);

                    //最大贏額
                    $this->SetUserMaxWin($u_id);

                    return NULL;
                }else{
                    $ErrorMsg=(array)$output->ErrorMsg;
                    return 	$ErrorMsg[0];
                }
            }else{
                return '系統繁忙中，請稍後再試'	;
            }
        }else{
            return '會員已有此類型帳號';
        }
    }

    public function get_balance($u_id){	//餘額查詢
        $Time=date('YmdHis');
        $data=array('method'=>'GetUserStatusDV','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id));
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0' && $output->IsSuccess){	//帳號創建成功
                return (float)$output->Balance;
            }else{
                return 	'--';
            }
        }else{
            return '--'	;
        }
    }

    public function deposit($u_id,$amount,$mem_num,$gamemaker_num=9,$logID=NULL){	//轉入點數到遊戲帳號內
        $OrderId='IN'.date('YmdHis').trim($u_id);
        //將轉點編號寫入DB
        $upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$OrderId."' where num=".$logID;
        $this->CI->webdb->sqlExc($upSql);
        $Time=date('YmdHis');
        $data=array('method'=>'CreditBalanceDV','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),
            'Checkkey'=>$this->Checkkey,'OrderId'=>$OrderId,'CreditAmount'=>$amount);
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);exit;
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){	//帳號創建成功
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
                $parameter[":makers_balance"]=(float)$output->Balance;
                $parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
                $parameter[":buildtime"]=now();
                $parameter[':before_balance']=$before_balance;
                $parameter[':after_balance']=$after_balance;
                $this->CI->webdb->sqlExc($sqlStr,$parameter);
                return NULL;
            }else{
                return "轉點錯誤：".$output->ErrorMsgId;
            }
        }else{
            return $this->point_checking($OrderId,1,$u_id,$amount,$mem_num,$gamemaker_num);
            //return '系統繁忙中，請稍後再試'	;
        }
    }

    public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=9,$logID=NULL){	//遊戲點數轉出
        $OrderId='OUT'.date('YmdHis').trim($u_id);
        //將轉點編號寫入DB
        $upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$OrderId."' where num=".$logID;
        $this->CI->webdb->sqlExc($upSql);
        $Time=date('YmdHis');
        $data=array('method'=>'DebitBalanceDV','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),
            'Checkkey'=>$this->Checkkey,'OrderId'=>$OrderId,'DebitAmount'=>$amount);
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){	//帳號創建成功
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
                $parameter[":makers_balance"]=(float)$output->Balance;
                $parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
                $parameter[":buildtime"]=now();
                $parameter[':before_balance']=$before_balance;
                $parameter[':after_balance']=$after_balance;
                $this->CI->webdb->sqlExc($sqlStr,$parameter);
                return NULL;
            }else{
                return "轉點錯誤：".$output->ErrorMsgId;
            }
        }else{
            return $this->point_checking($OrderId,2,$u_id,$amount,$mem_num,$gamemaker_num);
            //return '系統繁忙中，請稍後再試'	;
        }
    }

    public function point_checking($OrderId,$type,$u_id,$amount,$mem_num,$gamemaker_num){	//點查轉帳情況
        $Time=date('YmdHis');
        $data=array('method'=>'CheckOrderId','Key'=>$this->SecretKey,'Time'=>$Time,'Checkkey'=>$this->Checkkey,'OrderId'=>$OrderId);
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){
                if($output->isExist=='true'){
                    $makers_balance=$this->get_balance($u_id);
                    if($type==1){
                        $WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
                        if((int)$WalletTotal >=(int)$amount){
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
                            $parameter[":makers_balance"]=$makers_balance;
                            $parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
                            $parameter[":buildtime"]=now();
                            $parameter[':before_balance']=$before_balance;
                            $parameter[':after_balance']=$after_balance;
                            $this->CI->webdb->sqlExc($sqlStr,$parameter);
                            return NULL;
                        }else{
                            return '錢包點數不足';
                        }
                    }else{
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
                        $parameter[":makers_balance"]=$makers_balance;
                        $parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
                        $parameter[":buildtime"]=now();
                        $parameter[':before_balance']=$before_balance;
                        $parameter[':after_balance']=$after_balance;
                        $this->CI->webdb->sqlExc($sqlStr,$parameter);
                        return NULL;
                    }
                }else{
                    return '無此記錄';
                }
            }else{
                return "查詢轉點錯誤：".$output->ErrorMsgId;
            }
        }else{
            return '系統繁忙中，請稍後再試';
        }
    }

    //查詢限紅
    public function QueryBetLimit(){
        $Time=date('YmdHis');
        $data=array('method'=>'QueryBetLimit','Key'=>$this->SecretKey,'Time'=>$Time,'Currency'=>'TWD');
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);exit;
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){	//帳號創建成功
                return $output->BetLimitList;
            }else{
                return 	"--";
            }
        }else{
            return '--'	;
        }

    }

    //設定限紅
    public function SetBetLimit($u_id){
        $Time=date('YmdHis');
        $data=array('method'=>'SetBetLimit','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),'Currency'=>'TWD','Set1'=>2048,'Set2'=>8192);
        //$data=array('method'=>'SetBetLimit','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),'Currency'=>'TWD','Set1'=>32,'Set2'=>8192,'Set3'=>524288);
        //$data=array('method'=>'SetBetLimit','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),'Currency'=>'TWD','Set1'=>4,'Set2'=>4096,'Set3'=>65536);
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);exit;
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){	//帳號創建成功
                return NULL;
            }else{
                return 	"--";
            }
        }else{
            return '--'	;
        }
    }

    //設定最高贏取金額
    //API3.0.4  2018/09/19 更新SetUserMaxWin 改为 SetUserMaxBalance
    public function SetUserMaxWin($u_id){
        $Time=date('YmdHis');
        $data=array('method'=>'SetUserMaxBalance','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),'MaxWinning'=>500000);
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);exit;
        if(!$curl_errno){
            if($http_code===200 && @$output->ErrorMsgId=='0'){	//帳號創建成功
                return NULL;
            }else{
                return 	"--";
            }
        }else{
            return '--'	;
        }
    }

    //取得遊戲token
    public function forward_game($u_id){
        //$this->SetBetLimit($u_id);    //限紅設定

        $Time=date('YmdHis');
        $data=array('method'=>'LoginRequest','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),'Checkkey'=>$this->Checkkey,'CurrencyType'=>'TWD');
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){	//帳號創建成功
                return $output->Token;
            }else{
                return 	"--";
            }
        }else{
            return '--'	;
        }
    }

    //用於老虎機登入用
    public function slot_forward_game($u_id,$GameCode){
        $Time=date('YmdHis');
        //$data=array('method'=>'LoginRequest','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),'Checkkey'=>$this->Checkkey,'CurrencyType'=>'TWD');
        $data=array('method'=>'SlotLoginRequest','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),
            'Checkkey'=>$this->Checkkey,'CurrencyType'=>'TWD','GameCode'=>trim($GameCode));
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){
                $game_url=$output->GameURL.'?token='.$output->Token.'&name='.$output->DisplayName.'&language=2';
                if($this->CI->agent->is_mobile()) $game_url.='&mobile=true';	//手機板
                return $game_url;
            }else{
                return 	"--";
            }
        }else{
            return '--'	;
        }
    }

    //用於捕魚登入用
    public function fish_forward_game($u_id,$GameCode){
        $Time=date('YmdHis');
        if($this->CI->agent->is_mobile()){
            $m = 1;
        } else{
            $m = 0;
        }
        $data=array('method'=>'AnimatedGameLoginRequest','Key'=>$this->SecretKey,'Time'=>$Time,'Username'=>trim($u_id),
            'Checkkey'=>$this->Checkkey,'CurrencyType'=>'TWD','Language'=>'zh_TW','GameCode'=>'Fishermen Gold','Mobile'=>trim( $m ));
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        //print_r($output);
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){
                $game_url=$output->GameURL.'?token='.$output->Token.'&name='.$output->DisplayName.'&language=2';
                //if($this->CI->agent->is_mobile()) $game_url.='&mobile=true';	//手機板
                return $game_url;
            }else{
                return 	"--";
            }
        }else{
            return '--'	;
        }
    }

    //報表
    public function reporter_all($FromTime,$ToTime){
        //$ToTime=date('Y-m-d H:i:s');
        //$FromTime=date('Y-m-d H:i:s',strtotime($ToTime."-1 hour"));
        $Time=date('YmdHis');
        $data=array('method'=>'GetAllBetDetailsForTimeIntervalDV','Key'=>$this->SecretKey,'Time'=>$Time,'Checkkey'=>$this->Checkkey,'FromTime'=>$FromTime,'ToTime'=>$ToTime);
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->rpt_api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,180);

        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        print_r($output);
        if(!$curl_errno){
            if($http_code===200 && $output->ErrorMsgId=='0'){
                return $output->BetDetailList;
            }else{

            }
        }else{

        }
    }

    //補帳用 .每5分钟可以调用5次,否则报错. 實際測試 時好時壞
    public function reporter_all2($Date){
        //$ToTime=date('Y-m-d H:i:s');
        //$FromTime=date('Y-m-d H:i:s',strtotime($ToTime."-1 hour"));
        $Time=date('YmdHis');
        //$Date = '2017-08-03';
        $data=array('method'=>'GetAllBetDetailsDV','Key'=>$this->SecretKey,'Time'=>$Time,'Checkkey'=>$this->Checkkey,'Date'=>$Date);
        $QS=http_build_query($data);
        $q=urlencode($this->encrypt($QS));
        $signature=md5($QS.$this->Md5Key.$Time.$this->SecretKey);
        $ch = curl_init($this->rpt_api_url."?q=".$q.'&s='.$signature);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = simplexml_load_string(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        print_r($output);
        //if(!$curl_errno){
        if($http_code===200 && $output->ErrorMsgId=='0'){
            return $output->BetDetailList;
        }else{

        }
        //}else{

        //}
    }

    public function getLoginUrl(){
        return $this->game_url;
    }

    public function getCheckkey(){
        return 	$this->Checkkey;
    }

    public function getLobbycode(){
        return $this->Lobbycode;
    }

    //定義投住類型
    public function getBetType(){
        //定義百家樂投注類型
        $betType["bac"][0]='合';
        $betType["bac"][1]='閒';
        $betType["bac"][2]='莊';
        $betType["bac"][3]='閒對';
        $betType["bac"][4]='莊對';
        $betType["bac"][5]='閒單點';
        $betType["bac"][6]='莊單點';
        $betType["bac"][7]='總單點';
        $betType["bac"][8]='閒雙點';
        $betType["bac"][9]='莊雙點';
        $betType["bac"][10]='總雙點';
        $betType["bac"][11]='閒點小';
        $betType["bac"][12]='莊點小';
        $betType["bac"][13]='總點小';
        $betType["bac"][14]='閒點大';
        $betType["bac"][15]='莊點大';
        $betType["bac"][16]='總點大';
        $betType["bac"][17]='閒牌小';
        $betType["bac"][18]='莊牌小';
        $betType["bac"][19]='總牌小';
        $betType["bac"][20]='閒牌大';
        $betType["bac"][21]='莊牌大';
        $betType["bac"][22]='總牌大';
        $betType["bac"][25]='超級六和';
        $betType["bac"][26]='超級六閒贏';
        $betType["bac"][27]='超級六莊贏';
        $betType["bac"][28]='超級六閒對';
        $betType["bac"][29]='超級六莊對';
        $betType["bac"][30]='超級六';
        $betType["bac"][31]='超級百家樂和';
        $betType["bac"][32]='超級百家樂閒贏';
        $betType["bac"][33]='超級百家樂莊贏';
        $betType["bac"][34]='超級百家樂閒對';
        $betType["bac"][35]='超級百家樂莊對';
        $betType["bac"][36]='閒例牌';
        $betType["bac"][37]='莊例牌';
        $betType["bac"][38]='超級百家樂閒例牌';
        $betType["bac"][39]='超級百家樂莊例牌';
        $betType["bac"][40]='超級六閒例牌';
        $betType["bac"][41]='超級六莊例牌';
        $betType["bac"][42]='牛牛百家樂閒贏';
        $betType["bac"][43]='牛牛百家樂莊贏';

        //骰寶
        $betType["sicbo"][0]='小';
        $betType["sicbo"][1]='大';
        $betType["sicbo"][2]='單';
        $betType["sicbo"][3]='雙';
        $betType["sicbo"][4]='三軍1';
        $betType["sicbo"][5]='三軍2';
        $betType["sicbo"][6]='三軍3';
        $betType["sicbo"][7]='三軍4';
        $betType["sicbo"][8]='三軍5';
        $betType["sicbo"][9]='三軍6';
        $betType["sicbo"][10]='圍一';
        $betType["sicbo"][11]='圍二';
        $betType["sicbo"][12]='圍三';
        $betType["sicbo"][13]='圍四';
        $betType["sicbo"][14]='圍五';
        $betType["sicbo"][15]='圍六';
        $betType["sicbo"][16]='全圍';
        $betType["sicbo"][17]='4點';
        $betType["sicbo"][18]='5點';
        $betType["sicbo"][19]='6點';
        $betType["sicbo"][20]='7點';
        $betType["sicbo"][21]='8點';
        $betType["sicbo"][22]='9點';
        $betType["sicbo"][23]='10點';
        $betType["sicbo"][24]='11點';
        $betType["sicbo"][25]='12點';
        $betType["sicbo"][26]='13點';
        $betType["sicbo"][27]='14點';
        $betType["sicbo"][28]='15點';
        $betType["sicbo"][29]='16點';
        $betType["sicbo"][30]='17點';
        $betType["sicbo"][31]='長牌12';
        $betType["sicbo"][32]='長牌13';
        $betType["sicbo"][33]='長牌14';
        $betType["sicbo"][34]='長牌15';
        $betType["sicbo"][35]='長牌16';
        $betType["sicbo"][36]='長牌23';
        $betType["sicbo"][37]='長牌24';
        $betType["sicbo"][38]='長牌25';
        $betType["sicbo"][39]='長牌26';
        $betType["sicbo"][40]='長牌34';
        $betType["sicbo"][41]='長牌35';
        $betType["sicbo"][42]='長牌36';
        $betType["sicbo"][43]='長牌45';
        $betType["sicbo"][44]='長牌46';
        $betType["sicbo"][45]='長牌56';
        $betType["sicbo"][46]='短牌1';
        $betType["sicbo"][47]='短牌2';
        $betType["sicbo"][48]='短牌3';
        $betType["sicbo"][49]='短牌4';
        $betType["sicbo"][50]='短牌5';
        $betType["sicbo"][51]='短牌6';
        $betType["sicbo"][52]='三全單';
        $betType["sicbo"][53]='兩單一雙';
        $betType["sicbo"][54]='兩雙一單';
        $betType["sicbo"][55]='三全雙';
        $betType["sicbo"][56]='1,2,3,4';
        $betType["sicbo"][57]='2,3,4,5';
        $betType["sicbo"][58]='2,3,5,6';
        $betType["sicbo"][59]='3,4,5,6';
        $betType["sicbo"][60]='112';
        $betType["sicbo"][61]='113';
        $betType["sicbo"][62]='114';
        $betType["sicbo"][63]='115';
        $betType["sicbo"][64]='116';
        $betType["sicbo"][65]='221';
        $betType["sicbo"][66]='223';
        $betType["sicbo"][67]='224';
        $betType["sicbo"][68]='225';
        $betType["sicbo"][69]='226';
        $betType["sicbo"][70]='331';
        $betType["sicbo"][71]='332';
        $betType["sicbo"][72]='334';
        $betType["sicbo"][73]='335';
        $betType["sicbo"][74]='336';
        $betType["sicbo"][75]='441';
        $betType["sicbo"][76]='442';
        $betType["sicbo"][77]='443';
        $betType["sicbo"][78]='445';
        $betType["sicbo"][79]='446';
        $betType["sicbo"][80]='551';
        $betType["sicbo"][81]='552';
        $betType["sicbo"][82]='553';
        $betType["sicbo"][83]='554';
        $betType["sicbo"][84]='556';
        $betType["sicbo"][85]='661';
        $betType["sicbo"][86]='662';
        $betType["sicbo"][87]='663';
        $betType["sicbo"][88]='664';
        $betType["sicbo"][89]='665';
        $betType["sicbo"][90]='126';
        $betType["sicbo"][91]='135';
        $betType["sicbo"][92]='234';
        $betType["sicbo"][93]='256';
        $betType["sicbo"][94]='346';
        $betType["sicbo"][95]='123';
        $betType["sicbo"][96]='136';
        $betType["sicbo"][97]='145';
        $betType["sicbo"][98]='235';
        $betType["sicbo"][99]='356';
        $betType["sicbo"][100]='124';
        $betType["sicbo"][101]='146';
        $betType["sicbo"][102]='236';
        $betType["sicbo"][103]='245';
        $betType["sicbo"][104]='456';
        $betType["sicbo"][105]='125';
        $betType["sicbo"][106]='134';
        $betType["sicbo"][107]='156';
        $betType["sicbo"][108]='246';
        $betType["sicbo"][109]='345';

        //番推
        $betType["ftan"][0]='單';
        $betType["ftan"][1]='雙';
        $betType["ftan"][2]='1正';
        $betType["ftan"][3]='2正';
        $betType["ftan"][4]='3正';
        $betType["ftan"][5]='4正';
        $betType["ftan"][6]='1番';
        $betType["ftan"][7]='2番';
        $betType["ftan"][8]='3番';
        $betType["ftan"][9]='4番';
        $betType["ftan"][10]='1念2';
        $betType["ftan"][11]='1念3';
        $betType["ftan"][12]='1念4';
        $betType["ftan"][13]='2念1';
        $betType["ftan"][14]='2念3';
        $betType["ftan"][15]='2念4';
        $betType["ftan"][16]='3念1';
        $betType["ftan"][17]='3念2';
        $betType["ftan"][18]='3念4';
        $betType["ftan"][19]='4念1';
        $betType["ftan"][20]='4念2';
        $betType["ftan"][21]='4念3';
        $betType["ftan"][22]='12角';
        $betType["ftan"][23]='14角';
        $betType["ftan"][24]='23角';
        $betType["ftan"][25]='34角';
        $betType["ftan"][26]='23一通';
        $betType["ftan"][27]='24一通';
        $betType["ftan"][28]='34一通';
        $betType["ftan"][29]='13二通';
        $betType["ftan"][30]='14二通';
        $betType["ftan"][31]='34二通';
        $betType["ftan"][32]='12三通';
        $betType["ftan"][33]='14三通';
        $betType["ftan"][34]='24三通';
        $betType["ftan"][35]='12四通';
        $betType["ftan"][36]='13四通';
        $betType["ftan"][37]='23四通';
        $betType["ftan"][38]='123中';
        $betType["ftan"][39]='124中';
        $betType["ftan"][40]='134中';
        $betType["ftan"][41]='234中';

        //輪盤
        for($i=0;$i<=36;$i++){
            $betType["rot"][$i]=$i;
        }
        $betType["rot"][37]='0,1';
        $betType["rot"][38]='0,2';
        $betType["rot"][39]='0,3';
        $betType["rot"][40]='1,2';
        $betType["rot"][41]='1,4';
        $betType["rot"][42]='2,3';
        $betType["rot"][43]='2,5';
        $betType["rot"][44]='3,6';
        $betType["rot"][45]='4,5';
        $betType["rot"][46]='4,7';
        $betType["rot"][47]='5,6';
        $betType["rot"][48]='5,8';
        $betType["rot"][49]='6,9';
        $betType["rot"][50]='7,8';
        $betType["rot"][51]='7,10';
        $betType["rot"][52]='8,9';
        $betType["rot"][53]='8,11';
        $betType["rot"][54]='9,12';
        $betType["rot"][55]='10,11';
        $betType["rot"][56]='10,13';
        $betType["rot"][57]='11,12';
        $betType["rot"][58]='11,14';
        $betType["rot"][59]='12,15';
        $betType["rot"][60]='13,14';
        $betType["rot"][61]='13,16';
        $betType["rot"][62]='14,15';
        $betType["rot"][63]='14,17';
        $betType["rot"][64]='15,18';
        $betType["rot"][65]='16,17';
        $betType["rot"][66]='16,19';
        $betType["rot"][67]='17,18';
        $betType["rot"][68]='17,20';
        $betType["rot"][69]='18,21';
        $betType["rot"][70]='19,20';
        $betType["rot"][71]='19,22';
        $betType["rot"][72]='20,21';
        $betType["rot"][73]='20,23';
        $betType["rot"][74]='21,24';
        $betType["rot"][75]='22,23';
        $betType["rot"][76]='22,25';
        $betType["rot"][77]='23,24';
        $betType["rot"][78]='23,26';
        $betType["rot"][79]='24,27';
        $betType["rot"][80]='25,26';
        $betType["rot"][81]='25,28';
        $betType["rot"][82]='26,27';
        $betType["rot"][83]='26,29';
        $betType["rot"][84]='27,30';
        $betType["rot"][85]='28,29';
        $betType["rot"][86]='28,31';
        $betType["rot"][87]='29,30';
        $betType["rot"][88]='29,32';
        $betType["rot"][89]='30,33';
        $betType["rot"][90]='31,32';
        $betType["rot"][91]='31,34';
        $betType["rot"][92]='32,33';
        $betType["rot"][93]='32,35';
        $betType["rot"][94]='33,36';
        $betType["rot"][95]='34,35';
        $betType["rot"][96]='35,36';
        $betType["rot"][97]='0,1,2';
        $betType["rot"][98]='0,2,3';
        $betType["rot"][99]='1,2,3';
        $betType["rot"][100]='4,5,6';
        $betType["rot"][101]='7,8,9';
        $betType["rot"][102]='10,11,12';
        $betType["rot"][103]='13,14,15';
        $betType["rot"][104]='16,17,18';
        $betType["rot"][105]='19,20,21';
        $betType["rot"][106]='22,23,24';
        $betType["rot"][107]='25,26,27';
        $betType["rot"][108]='28,29,30';
        $betType["rot"][109]='31,32,33';
        $betType["rot"][110]='34,35,36';
        $betType["rot"][111]='1,2,4,5';
        $betType["rot"][112]='2,3,5,6';
        $betType["rot"][113]='4,5,7,8';
        $betType["rot"][114]='5,6,8,9';
        $betType["rot"][115]='7,8,10,11';
        $betType["rot"][116]='8,9,11,12';
        $betType["rot"][117]='10,11,13,14';
        $betType["rot"][118]='11,12,14,15';
        $betType["rot"][119]='13,14,16,17';
        $betType["rot"][120]='14,15,17,18';
        $betType["rot"][121]='16,17,19,20';
        $betType["rot"][122]='17,18,20,21';
        $betType["rot"][123]='19,20,22,23';
        $betType["rot"][124]='20,21,23,24';
        $betType["rot"][125]='22,23,25,26';
        $betType["rot"][126]='23,24,26,27';
        $betType["rot"][127]='25,26,28,29';
        $betType["rot"][128]='26,27,29,30';
        $betType["rot"][129]='28,29,31,32';
        $betType["rot"][130]='29,30,32,33';
        $betType["rot"][131]='31,32,34,35';
        $betType["rot"][132]='32,33,35,36';
        $betType["rot"][133]='1,2,3,4,5,6';
        $betType["rot"][134]='4,5,6,7,8,9';
        $betType["rot"][135]='7,8,9,10,11,12';
        $betType["rot"][136]='10,11,12,13,14,15';
        $betType["rot"][137]='13,14,15,16,17,18';
        $betType["rot"][138]='16,17,18,19,20,21';
        $betType["rot"][139]='19,20,21,22,23,24';
        $betType["rot"][140]='22,23,24,25,26,27';
        $betType["rot"][141]='25,26,27,28,29,30';
        $betType["rot"][142]='28,29,30,31,32,33';
        $betType["rot"][143]='31,32,33,34,35,36';
        $betType["rot"][144]='第一列​ ​ (1~12)';
        $betType["rot"][145]='第二列​ ​ (13~24)';
        $betType["rot"][146]='第三列​ ​ (25~36)';
        $betType["rot"][147]='第一行​ ​ (1~34)';
        $betType["rot"][148]='第二行​ ​ (2~35)';
        $betType["rot"][149]='第三行​ ​ (3~36)';
        $betType["rot"][150]='1~18​ ​ ( 小)';
        $betType["rot"][151]='19~36​ ​ ( 大)';
        $betType["rot"][152]='單';
        $betType["rot"][153]='雙';
        $betType["rot"][154]='紅';
        $betType["rot"][155]='黑';
        $betType["rot"][156]='0,1,2,3';

        //龍虎
        $betType["dtx"][0]='合';
        $betType["dtx"][1]='龍';
        $betType["dtx"][2]='虎';

        return $betType;
    }

    private function DES( $key, $iv=0 ) {
        $this->key = $key;
        if( $iv == 0 ) {
            $this->iv = $key;
        } else {
            $this->iv = $iv;
        }
    }

    private function encrypt($str) {
        $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad ( $str, $size );
        return base64_encode( mcrypt_encrypt(MCRYPT_DES, 'g9G16nTs', $str, MCRYPT_MODE_CBC, 'g9G16nTs' ) );
    }

    private function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

}

?>