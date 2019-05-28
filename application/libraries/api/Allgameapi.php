<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define("account_prefix", "BGR");    //遊戲帳號前綴
class Allgameapi
{
    public function __construct()
    {
        $this->CI =& get_instance();


        $this->CI->load->library('api/allbetapi');    //歐博
        //$this->CI->load->library('api/royalapi');	  //皇家
        //$this->CI->load->library('api/fishapi');    //捕魚機
        //$this->CI->load->library('api/grandapi');   //緯來(PG電子)
        $this->CI->load->library('api/superapi');     //Super
        $this->CI->load->library('api/sagamingapi');  //沙龍
        //$this->CI->load->library('api/qtechapi');   //QT電子
        $this->CI->load->library('api/dreamgame');    //dg真人
        $this->CI->load->library('api/wmapi');        //wm真人
        //$this->CI->load->library('api/slotteryapi');  //Super彩球
        //$this->CI->load->library('api/ssbapi');     //贏家體育
        $this->CI->load->library('api/s7pkapi');      //7PK
        //$this->CI->load->library('api/bingoapi');	  //賓果
        //$this->CI->load->library('api/bingo');      //bingo start
        //$this->CI->load->library('api/ebetapi');	  //Ebet真人
        //$this->CI->load->library('api/waterapi');	  //水立方真人
        //$this->CI->load->library('api/s9k168');     //北京賽車
        //$this->CI->load->library('api/ameba');      //Ameba
        //$this->CI->load->library('api/pk10');       //pk10北京賽車
        $this->CI->load->library('api/avia');         //泛亞電競
		//$this->CI->load->library('api/hces888');    //皇朝電競
		//$this->CI->load->library('api/vgapi');      //Vg
        //$this->CI->load->library('api/htpg');       //彩播
		$this->CI->load->library('api/rtgapi');       //RTG
        //$this->CI->load->library('api/maya');       //瑪亞
        $this->CI->load->library('api/egapi');        //EG捕魚
        $this->CI->load->library('api/cm');           //新濠
        date_default_timezone_set("Asia/Taipei");

    }

    public function create_account($u_id, $u_password, $mem_num)
    {    //創建遊戲帳號	//登入會員用
        //$this->CI->combetsportapi->create_account(account_prefix.$u_id,$mem_num,1);	//創建Combet球版帳號
        //$this->CI->royalapi->create_account(account_prefix.$u_id,$u_password,$mem_num,2);	//創建皇家
        //$this->CI->allbetapi->create_account(account_prefix.$u_id,$u_password,$mem_num,3);	//創建歐博遊戲帳號
        //$this->CI->fishapi->create_account(account_prefix.$u_id,$u_password,$mem_num,4);	//創建補魚機
        //$this->CI->uc8api->create_account(account_prefix.$u_id,$mem_num,7);	//創建UC8
        //$this->CI->superapi->create_account(account_prefix.$u_id,$u_password,$mem_num,8);	//Super
        //$this->CI->sagamingapi->create_account(account_prefix.$u_id,$mem_num,9);	//沙龍
        //$this->CI->qtechapi->create_account(account_prefix.$u_id,$mem_num,11);	//QT電子
        //$this->CI->dreamgame->create_account(account_prefix.$u_id,$u_password,$mem_num,12);	//dg真人
        //$this->CI->wmapi->create_account(account_prefix.$u_id,$u_password,$mem_num,13);	//WM真人
        //$this->CI->slotteryapi->create_account(account_prefix.$u_id,$u_password,$mem_num,20);	//Super彩球
        //$this->CI->ssbapi->create_account(account_prefix.$u_id,$u_password,$mem_num,21);//贏家體育
        //$this->CI->s7pkapi->create_account(account_prefix.$u_id,$mem_num,22);//7PK
        //$this->CI->bingoapi->create_account(account_prefix.$u_id,$mem_num,23);//賓果賓果
        //$this->CI->bingo->create_account(account_prefix.$u_id,$u_password,$mem_num,28);//bingo start
        //$this->CI->ebetapi->create_account(account_prefix.$u_id,$mem_num,24);	//EB真人
        //$this->CI->waterapi->create_account(account_prefix.$u_id,$u_password,$mem_num,25);	//水立方
        //$this->CI->s9k168->create_account(account_prefix.$u_id,$u_password,$mem_num,26);	//北京賽車
        //$this->CI->ameba->create_account(account_prefix.$u_id,$mem_num,27);//AMEBA
    }

    public function create_account2($u_id, $u_password, $mem_num, $gamemaker_num)
    {    //後台個別創立會員用
        //return '此功能尚未開放，請洽火箭科技';exit;
        $logMsg = NULL;
        switch ($gamemaker_num) {
            /*
            case "1":	//Combet
                $logMsg=$this->CI->combetsportapi->create_account(account_prefix.$u_id,$mem_num,$gamemaker_num);
                break;
            case "2":	//皇家
                $logMsg=$this->CI->royalapi->create_account(account_prefix.$u_id,$u_password,$mem_num,$gamemaker_num);
                break;
                */
            case "3":    //歐博
                $logMsg = $this->CI->allbetapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;

            case "4":    //捕魚機
                $logMsg = $this->CI->fishapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "6":    //緯來
                $logMsg = $this->CI->grandapi->create_account(account_prefix . $u_id, $mem_num, $gamemaker_num);
                break;
            case "7":    //UC8
                $logMsg = $this->CI->uc8api->create_account(account_prefix . $u_id, $mem_num, $gamemaker_num);
                break;
            case "8":    //Super
                $logMsg = $this->CI->superapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "9": //沙龍
                $logMsg = $this->CI->sagamingapi->create_account(account_prefix . $u_id, $mem_num, $gamemaker_num);
                break;
            case "11": //QT電子
                //$logMsg='遊戲尚未開放';
                $logMsg = $this->CI->qtechapi->create_account(account_prefix . $u_id, $mem_num, $gamemaker_num);
                break;
            case "12": //dg真人
                $logMsg = $this->CI->dreamgame->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "13"://wm真人
                $logMsg = $this->CI->wmapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "20": //Super彩球
                $logMsg = $this->CI->slotteryapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "21": //贏家體育
                $logMsg = $this->CI->ssbapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "22": //7PK
                $logMsg = $this->CI->s7pkapi->create_account(account_prefix . $u_id, $mem_num, $gamemaker_num);
                break;
            /*
            case "23": //賓果賓果
                $logMsg=$this->CI->bingoapi->create_account(account_prefix.$u_id,$mem_num,$gamemaker_num);
                break;

            case "24": //Ebet真人
                $logMsg='遊戲尚未上線';
                //$logMsg=$this->CI->ebetapi->create_account(account_prefix.$u_id,$mem_num,$gamemaker_num);
                break;

            case "25": //水立方真人
                $logMsg=$this->CI->waterapi->create_account(account_prefix.$u_id,$u_password,$mem_num,$gamemaker_num);
                break;
            */
            case "26":    //北京賽車	account_prefix2
                $logMsg = $this->CI->s9k168->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "27":    //Ameba
                $logMsg = $this->CI->ameba->create_account(account_prefix . $u_id, $mem_num, $gamemaker_num);
                break;
            case "28": //Bingo Start
                $logMsg = $this->CI->bingo->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "31": //pk10
                $logMsg = $this->CI->pk10->create_account(account_prefix . '' . $u_id, $mem_num, $gamemaker_num);
                break;
            case "33": //maya
                $logMsg = $this->CI->maya->create_account(account_prefix . '' . $u_id, $mem_num, $gamemaker_num);
                break;
            case "35": //泛亞電競
                //$logMsg='遊戲尚未開放';
                $logMsg = $this->CI->avia->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "36": //皇朝電競
                $logMsg = $this->CI->hces888->create_account(account_prefix . $u_id, $mem_num, $gamemaker_num);
                break;
			case "38": //VG
                $logMsg = $this->CI->vgapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;	
			case "41": //彩播
                $logMsg = $this->CI->htpg->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
			case "51": //RTG
			    //$logMsg='遊戲尚未開放';
                $logMsg = $this->CI->rtgapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "62": //CM
                //$logMsg='遊戲尚未開放';
                $logMsg = $this->CI->cm->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;
            case "64": //EG
			    //$logMsg='遊戲尚未開放';
                $logMsg = $this->CI->egapi->create_account(account_prefix . $u_id, $u_password, $mem_num, $gamemaker_num);
                break;	
            default:
                $logMsg = "該遊戲廠商不存在";
                break;
        }
        return $logMsg;
    }

    public function get_balance($mem_num, $gamemaker_num)
    {    //抓遊戲餘額
        //抓出該會員對應遊戲廠商帳號
        $parameter = array();
        $sqlStr = "select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
        $parameter[':mem_num'] = $mem_num;
        $parameter[':gamemaker_num'] = $gamemaker_num;
        $row = $this->CI->webdb->sqlRow($sqlStr, $parameter);
        if ($row != NULL) {
            $u_id = $row["u_id"];
            $u_password = $this->CI->encryption->decrypt($row["u_password"]);    //密碼解密
            switch ($gamemaker_num) {
                /*
                case "1":	//Combet
                    return $this->CI->combetsportapi->get_balance($u_id);
                    break;
                case "2":	//皇家
                    return $this->CI->royalapi->get_balance($u_id);
                    break;
                */
                case "3":    //歐博
                    return $this->CI->allbetapi->get_balance($u_id, $u_password);
                    break;

                case "4":    //捕魚機
                    return $this->CI->fishapi->get_balance($u_id, $u_password);
                    break;
                case "6": //緯來
                    return $this->CI->grandapi->get_balance($u_id);
                    break;
                case "7":    //UC8
                    return $this->CI->uc8api->get_balance($u_id);
                    break;
                case "8":    //Super
                    return $this->CI->superapi->get_balance($u_id);
                    break;
                case "9": //沙龍
                    return $this->CI->sagamingapi->get_balance($u_id);
                    break;
                case "11": //QT電子
                    return $this->CI->qtechapi->get_balance($u_id);
                    break;
                case "12": //dg真人
                    return $this->CI->dreamgame->get_balance($u_id);
                    break;
                case "13"://WM真人
                    return $this->CI->wmapi->get_balance($u_id);
                    break;
                case "20": //Super彩球
                    return $this->CI->slotteryapi->get_balance($u_id, $u_password);
                    break;
                case "21": //贏家體育
                    return $this->CI->ssbapi->get_balance($u_id);
                    break;
                case "22": //7PK
                    return $this->CI->s7pkapi->get_balance($u_id);
                    break;
                /*
                case "23": //賓果賓果
                    return 	$this->CI->bingoapi->get_balance($u_id);
                    break;
                case "24": //EBet真人
                    return 	$this->CI->ebetapi->get_balance($u_id);
                    break;
                case "25": //水立方真人
                    return 	$this->CI->waterapi->get_balance($u_id,$u_password);
                    break;
                */
                case "26":    //北京賽車
                    return $this->CI->s9k168->get_balance($u_id);
                    break;
                case "27":    //Ameba
                    return $this->CI->ameba->get_balance($u_id);
                    break;
                case "28":    //Bingo Start
                    return $this->CI->bingo->get_balance($u_id);
                    break;
                case "31": //pk10
                    return $this->CI->pk10->get_balance($u_id);
                    break;
                case "33": //maya
                    return $this->CI->maya->get_balance($u_id);
                    break;
                case "35": //泛亞電競
                    return $this->CI->avia->get_balance($u_id);
                    break;
				case "36": //皇朝電競
                    return $this->CI->hces888->get_balance($u_id);
                    break;
				case "38": //VG
                    return $this->CI->vgapi->get_balance($u_id);
                    break;
                case "41": //彩播
                    return $this->CI->htpg->get_balance($u_id);
                    break;
				case "51": //RTG
                    return $this->CI->rtgapi->get_balance($u_id);
                    break;
                case "62": //cm
                    return $this->CI->cm->get_balance($u_id);
                    break;
                case "64": //EG
                    return $this->CI->egapi->get_balance($u_id);
                    break;	
                default:
                    return '--';
                    break;
            }
        } else {
            return '--';
        }
    }

    public function deposit($amount, $mem_num, $gamemaker_num, $logID = NULL)
    {    //轉入遊戲
        //抓出該會員對應遊戲廠商帳號
        $parameter = array();
        $sqlStr = "select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
        $parameter[':mem_num'] = $mem_num;
        $parameter[':gamemaker_num'] = $gamemaker_num;
        $row = $this->CI->webdb->sqlRow($sqlStr, $parameter);

        if ($row == NULL) {//如果沒遊戲帳號，自動建立帳號
            $sqlStr = "select * from `member` where num=?";
            $rowMember = $this->CI->webdb->sqlRow($sqlStr, array(':num' => $mem_num));

            if ($rowMember != NULL) {
                $this->data["link"] = $this->create_account2($rowMember["u_id"], $this->CI->encryption->decrypt($rowMember["u_password"]), $rowMember["num"], $gamemaker_num);
            }

            //再讀取一次資料
            $sqlStr = "select * from `games_account` where `gamemaker_num`=" . $gamemaker_num . " and `mem_num`=?";
            $row = $this->CI->webdb->sqlRow($sqlStr, array(':mem_num' => $mem_num));
        }

        if ($row != NULL) {
            $u_id = $row["u_id"];
            $u_password = $this->CI->encryption->decrypt($row["u_password"]);    //密碼解密
            switch ($gamemaker_num) {
                /*
                case "1":	//Combet
                    $logMsg=$this->CI->combetsportapi->deposit($u_id,$amount,$mem_num);
                    break;
                case "2":	//皇家
                    $logMsg=$this->CI->royalapi->deposit($u_id,$amount,$mem_num);
                    break;
                    */
                case "3":    //歐博
                    $logMsg = $this->CI->allbetapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;

                case "4":    //捕魚機
                    $logMsg = $this->CI->fishapi->deposit($u_id, $u_password, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "6": //緯來
                    $logMsg = $this->CI->grandapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "7":    //UC8
                    $logMsg = $this->CI->uc8api->deposit($u_id, $amount, $mem_num);
                    break;
                case "8": //super
                    $logMsg = $this->CI->superapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "9": //沙龍
                    $logMsg = $this->CI->sagamingapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "11": //QT電子
                    $logMsg = $this->CI->qtechapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "12": //DG
                    $logMsg = $this->CI->dreamgame->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "13": //WM真人
                    $logMsg = $this->CI->wmapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "20": //Super彩球
                    $logMsg = $this->CI->slotteryapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "21": //贏家體育
                    $logMsg = $this->CI->ssbapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "22": //7PK
                    $logMsg = $this->CI->s7pkapi->deposit($u_id, $amount, $mem_num);
                    break;
                /*
                case "23": //賓果賓果
                    $logMsg=$this->CI->bingoapi->deposit($u_id,$amount,$mem_num);
                    break;
                case "24": //Ebet真人
                    $logMsg=$this->CI->ebetapi->deposit($u_id,$amount,$mem_num,$gamemaker_num,$logID);
                    break;
                case "25": //水立方真人
                    $logMsg=$this->CI->waterapi->deposit($u_id,$u_password,$amount,$mem_num,$gamemaker_num,$logID);
                    break;
                */
                case "26": //北京賽車
                    $logMsg = $this->CI->s9k168->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "27": //Ameba
                    $logMsg = $this->CI->ameba->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "28": //Bingo Start
                    $logMsg = $this->CI->bingo->deposit($u_id, $amount, $mem_num, $gamemaker_num);
                    break;
                case "31": //pk10
                    $logMsg = $this->CI->pk10->deposit($u_id, $amount, $mem_num, $gamemaker_num);
                    break;
                case "33": //maya
                    $logMsg = $this->CI->maya->deposit($u_id, $amount, $mem_num, $gamemaker_num);
                    break;
                case "35": //泛亞電競
                    $logMsg = $this->CI->avia->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
				case "36": //皇朝電競
                    $logMsg = $this->CI->hces888->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
				case "38": //VG (轉入遊戲後 遊戲幣 = 實際幣值 *100)
                    $logMsg = $this->CI->vgapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "41": //彩播
                    $logMsg = $this->CI->htpg->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
				case "51": //RTG
                    $logMsg = $this->CI->rtgapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "62": //cm
                    $logMsg = $this->CI->cm->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "64": //EG
                    $logMsg = $this->CI->egapi->deposit($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;	
                default:
                    $logMsg = "該遊戲廠商不存在";
                    break;
            }
        } else {
            $logMsg = "您尚未擁有此款遊戲帳號";
        }
        return $logMsg;
    }

    public function withdrawal($amount, $mem_num, $gamemaker_num, $logID = NULL)
    {    //遊戲轉出
        //抓出該會員對應遊戲廠商帳號
        $parameter = array();
        $sqlStr = "select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
        $parameter[':mem_num'] = $mem_num;
        $parameter[':gamemaker_num'] = $gamemaker_num;
        $row = $this->CI->webdb->sqlRow($sqlStr, $parameter);
        if ($row != NULL) {
            $u_id = $row["u_id"];
            $u_password = $this->CI->encryption->decrypt($row["u_password"]);    //密碼解密
            switch ($gamemaker_num) {
                /*
                case "1":	//Combet
                    $logMsg=$this->CI->combetsportapi->withdrawal($u_id,$amount,$mem_num);
                    break;
                case "2":	//皇家
                    $logMsg=$this->CI->royalapi->withdrawal($u_id,$amount,$mem_num);
                    break;
                */
                case "3":    //歐博
                    $logMsg = $this->CI->allbetapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;

                case "4":    //捕魚機
                    $logMsg = $this->CI->fishapi->withdrawal($u_id, $u_password, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "6": //緯來
                    $logMsg = $this->CI->grandapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "7": //UC8
                    $logMsg = $this->CI->uc8api->withdrawal($u_id, $amount, $mem_num);
                    break;
                case "8":    //super
                    $logMsg = $this->CI->superapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "9":    //沙龍
                    $logMsg = $this->CI->sagamingapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "11":    //QT電子
                    $logMsg = $this->CI->qtechapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "12":    //DG
                    $logMsg = $this->CI->dreamgame->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "13": //WM真人
                    $logMsg = $this->CI->wmapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "20":    //Super彩球
                    $logMsg = $this->CI->slotteryapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "21":    //贏家體育
                    $logMsg = $this->CI->ssbapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "22":    //7PK
                    $logMsg = $this->CI->s7pkapi->withdrawal($u_id, $amount, $mem_num);
                    break;
                /*
                case "23":	//賓果賓果
                    $logMsg=$this->CI->bingoapi->withdrawal($u_id,$amount,$mem_num);
                    break;
                case "24":	//Ebet真人
                    $logMsg=$this->CI->ebetapi->withdrawal($u_id,$amount,$mem_num,$gamemaker_num,$logID);
                    break;
                case "25":	//水立方真人
                    $logMsg=$this->CI->waterapi->withdrawal($u_id,$u_password,$amount,$mem_num,$gamemaker_num,$logID);
                    break;
                */
                case "26":    //北京賽車
                    $logMsg = $this->CI->s9k168->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $gamemaker_num, $logID);
                    break;
                case "27":    //Ameba
                    $logMsg = $this->CI->ameba->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "28":    //Bingo Start
                    $logMsg = $this->CI->bingo->withdrawal($u_id, $amount, $mem_num, $gamemaker_num);
                    break;
                case "31": //pk10
                    $logMsg = $this->CI->pk10->withdrawal($u_id, $amount, $mem_num, $gamemaker_num);
                    break;
                case "33": //maya
                    $logMsg = $this->CI->maya->withdrawal($u_id, $amount, $mem_num, $gamemaker_num);
                    break;
                case "35": //泛亞電競
                    $logMsg = $this->CI->avia->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
				case "36": //皇朝電競
                    $logMsg = $this->CI->hces888->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
				case "38":    //VG
                    $logMsg = $this->CI->vgapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num);
                    break;
                case "41": //彩播
                    $logMsg = $this->CI->htpg->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
				case "51": //RTG
                    $logMsg = $this->CI->rtgapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "62": //cm
                    $logMsg = $this->CI->cm->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;
                case "64": //EG
                    $logMsg = $this->CI->egapi->withdrawal($u_id, $amount, $mem_num, $gamemaker_num, $logID);
                    break;	
                default:
                    $logMsg = "該遊戲廠商不存在";
                    break;
            }
        } else {
            $logMsg = "您尚未擁有此款遊戲帳號";
        }
        return $logMsg;
    }


    public function forward_game($u_id, $u_password, $gamemaker_num, $GameCode = NULL)
    {    //取得遊戲連結
        $logMsg = NULL;
        switch ($gamemaker_num) {
            /*
            case "1":	//Combet
                $logMsg=$this->CI->combetsportapi->forward_game($u_id);
                break;
            case "2":	//皇家
                $logMsg=$this->CI->royalapi->forward_game($u_id,$u_password);
                break;
            */
            case "3":    //歐博
                $logMsg = $this->CI->allbetapi->forward_game($u_id, $u_password, $GameCode);
                break;

            case "4":    //捕魚機
                $logMsg = $this->CI->fishapi->forward_game($u_id, $u_password);
                break;
            case "6":    //緯來
                $logMsg = $this->CI->grandapi->forward_game($u_id, $GameCode);
                break;
            case "8":    //Super
                $logMsg = $this->CI->superapi->forward_game($u_id, $u_password);
                break;
            case "11":    //Qt電子
                $logMsg = $this->CI->qtechapi->forward_game($u_id, $GameCode);
                break;
            case "12":    //dg
                $logMsg = $this->CI->dreamgame->forward_game($u_id, $u_password);
                break;
            case "13": //WM真人
                $logMsg = $this->CI->wmapi->forward_game($u_id, $u_password);
                break;
            case "20":    //Super彩球
                $logMsg = $this->CI->slotteryapi->forward_game($u_id, $u_password);
                break;
            case "21":    //贏家體育
                $logMsg = $this->CI->ssbapi->forward_game($u_id, $u_password);
                break;
            case "22":    //7PK
                $logMsg = $this->CI->s7pkapi->forward_game($u_id);
                break;
            /*
            case "23":	//賓果賓果
                $logMsg=$this->CI->bingoapi->forward_game($u_id);
                break;
            case "24":	//Ebet真人
                $logMsg=$this->CI->ebetapi->forward_game($u_id);
                break;
            case "25":	//水立方真人
                $logMsg=$this->CI->waterapi->forward_game($u_id,$u_password);
                break;
            */
            case "26":    //北京賽車
                $logMsg = $this->CI->s9k168->forward_game($u_id, $u_password);
                break;
            case "27":    //Ameba
                $logMsg = $this->CI->ameba->forward_game($u_id, $GameCode);
                break;
            case "28":    //Bingo Start
                $logMsg = $this->CI->bingo->forward_game($u_id);
                break;
            case "31":    //新北京賽車
                $logMsg = $this->CI->pk10->forward_game($u_id);
                break;
            case "33":    //maya
                $logMsg = $this->CI->maya->forward_game($u_id);
                break;
            case "35":    //泛亞電競
                $logMsg = $this->CI->avia->forward_game($u_id);
                break;
			case "36":    //皇朝電競
                $logMsg = $this->CI->hces888->forward_game($u_id);
                break;	
            case "41":    //彩播
                $logMsg = $this->CI->htpg->forward_game($u_id, $u_password);
                break;
			case "51":    //RTG
                $logMsg = $this->CI->rtgapi->forward_game($u_id, $GameCode);
                break;
            case "62":    //cm
                $logMsg = $this->CI->cm->forward_game($u_id, $GameCode);
                break;
            case "64":    //EG
                $logMsg = $this->CI->egapi->forward_game($u_id, $GameCode);
                break;	
            default:
                $logMsg = "該遊戲廠商不存在";
                break;
        }
        return $logMsg;
    }

    //Fungame
    public function fun_game($gamemaker_num, $GameCode = NULL)
    {    //遊戲試玩
        $logMsg = NULL;
        switch ($gamemaker_num) {
            case "6":    //PG電子
                $logMsg = $this->CI->grandapi->fun_game($GameCode);
                break;
            case "11":    //Qt電子
                $logMsg = $this->CI->qtechapi->fun_game($GameCode);
                break;
            case "27":    //Ameba
                $logMsg = $this->CI->ameba->fun_game($GameCode);
                break;
			case "38":    //VG
                $logMsg = $this->CI->vgapi->fun_game($GameCode);
                break;
			case "51":    //RTG
                $logMsg = $this->CI->rtgapi->fun_game($u_id,$GameCode);
                break;	
            default:
                $logMsg = "該遊戲廠商不存在";
                break;
        }
        return $logMsg;
    }

    //補零
    private function add_zero($str, $len)
    {
        if ($str != '' && $len != '') {
            return str_pad($str, $len, '0', STR_PAD_LEFT);
        }
    }

    //自動生成帳號 需要資料表`auto_account`搭配
    private function auto_account()
    {
        $SN = NULL;
        $this->CI->db->trans_begin();
        $query = $this->CI->db->query('select `AccNo` from `auto_account` order by `AccNo` DESC Limit 1');
        if ($query->num_rows() == 0) {    //沒資料
            $SN = account_prefix . chr(65) . $this->add_zero(1, 10);
        } else {
            $row = $query->row_array();
            $s_asc = ord(substr($row["AccNo"], 2, 1)); //取得第二碼的ASCII代碼
            $s_num = substr($row["AccNo"], 3, 10); //後10碼流水號
            if (strlen((int)$s_num + 1) == 11) { //如果流水號用光了
                $SN = account_prefix . chr($s_asc + 1) . $this->add_zero(1, 10);
            } else {
                $SN = account_prefix . chr($s_asc) . $this->add_zero(((int)$s_num + 1), 10);
            }
        }
        $this->CI->db->query("INSERT INTO `auto_account` (`AccNo`) VALUES ('" . $SN . "')");
        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
        } else {
            $this->CI->db->trans_commit();
        }
        return $SN;
    }

}

?>