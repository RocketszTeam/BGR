<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define("WebID", "bwfun188");
define("VendorId","comebet");
define("ROYAL_API_URL", "http://Api.RoyalLiveGame.com:8951/DGC.asmx");
define("ROYAL_GAME_URL","http://Web.RoyalLiveGame.com:9690/entry_g.aspx");

class Royalapi {
	var $CI;
	var $roy_timeout=4;	//curl允許等待秒數
	public function __construct(){	
		$this->CI =&get_instance();
		$this->CI->load -> model("sysAdmin/game_account_model", "game_account", true);
		$this->CI->load -> model("sysAdmin/wallet_model", "wallet", true);	
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=2){	//創建遊戲帳號	
		$post_data=array('WebID'=>WebID,'UserId'=>$u_id,
						 'Password'=>$u_password,
						 'UserName'=>$u_id.mt_rand(111,999),
						 'Currency'=>'NT',
						 'TingYong_XinYong'=>-1,
						 'OpenGameId'=>'ALL',
						 'LimitLevel'=>1,
						 'isTestAccount'=>0,'Fkey'=>'');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,ROYAL_API_URL."/CreateUser");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->roy_timeout);
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->Status==1){
				$parameter=array();
				$sqlStr="select * from `games_account` where `u_id`=?  and `mem_num`=? and `gamemaker_num`=?";
				$parameter[':u_id']=trim($u_id);
				$parameter[':mem_num']=$mem_num;
				$parameter[':gamemaker_num']=$gamemaker_num;
				$row=$this->CI->game_account->sqlRow($sqlStr,$parameter);
				if($row==NULL){	//如果帳號不存在則把帳號寫入資料庫
					$colSql="u_id,u_password,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);
					$parameter[':u_password']=$this->CI->encryption->encrypt($u_password);	//密碼加密
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->game_account->sqlExc($upSql,$parameter);
				}
				return NULL;
			}else{
				return $output->Description;
			}
		}else{
			//超時處理
			return "系統繁忙中請稍候再試";
		}
	}

	public function get_balance($u_id){	//餘額查詢
		$post_data=array('WebID'=>WebID,'UserId'=>$u_id);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,ROYAL_API_URL."/CheckUserBalance");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->roy_timeout);
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->Status==1){
				return (int)$output->Balance;
			}else{
				return '--';
			}
		}else{
			//超時處理
			return '--';	
		}
	}

	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=2){	//轉入點數到遊戲帳號內
		$post_data=array('WebID'=>WebID,'UserId'=>$u_id,'Amount'=>$amount);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,ROYAL_API_URL."/FundTransfer");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->roy_timeout);
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->Status==1){
				$parameter=array();
				$colSql="mem_num,kind,less_point,makers_num,buildtime";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=3;	//轉入遊戲
				$parameter[":less_point"]=$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":buildtime"]=now();
				$this->CI->wallet->sqlExc($sqlStr,$parameter);
				return NULL;
			}else{
				return "轉點錯誤Error:".$output->Status;
			}
		}else{
			//超時處理
			return "系統繁忙中請稍候再試";
		}
	}

	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=2){	//遊戲點數轉出
		$post_data=array('WebID'=>WebID,'UserId'=>$u_id,'Amount'=>"-".$amount);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,ROYAL_API_URL."/FundTransfer");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->roy_timeout);
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->Status==1){
				$parameter=array();
				$colSql="mem_num,kind,add_point,makers_num,buildtime";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=4;	//遊戲轉出
				$parameter[":add_point"]=$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":buildtime"]=now();
				$this->CI->wallet->sqlExc($sqlStr,$parameter);
				return NULL;
			}elseif($output->Status==-4){
				return "帳號餘額不足";
			}else{
				return "轉點錯誤Error:".$output->Status;
			}
		}else{
			//超時處理
			return "系統繁忙中請稍候再試";
		}
	}

	public function forward_game($u_id){	//登入遊戲
		//$this->KickUser($u_id);
		$post_data=array('WebID'=>WebID,'UserId'=>$u_id);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,ROYAL_API_URL."/GetSessionKey");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->roy_timeout);
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->Status==1){
				return ROYAL_GAME_URL.'?lang=zh-tw&guid='.$output->SessionKey;
			}else{
				return $output->Status;
			}
		}else{
			//超時處理
		}
	}


	//剔除使用者
	public function KickUser($u_id){
		$post_data=array('WebID'=>WebID,'UserId'=>$u_id);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,ROYAL_API_URL."/KickUser");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->roy_timeout);
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		print_r($output);exit;
		curl_close($ch);
	}
	

	public function reporter_all(){
		$MaxId=1;
		$sqlStr="select `Id` from `royal_report` order by `Id` DESC Limit 1";
		$row=$this->CI->wallet->sqlRow($sqlStr);
		if($row!=NULL){
			$MaxId	= $row['Id'];
		}	
		$post_data=array('WebID'=>WebID,'VendorId'=>VendorId,
						 'MaxId'=> $MaxId);						
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,ROYAL_API_URL."/GetStakeDetail2");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->roy_timeout);		
		$output = simplexml_load_string(curl_exec($ch));
		$curl_errno = curl_errno($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!$curl_errno){
			if($http_code===200 && $output->Status==1){
				return $output->Data->row;
			}else{
				return $output->Status;	
			}
		}else{
			//超時處理	
		}
	}

	//投注對照表(MaHao)
	public function getMaHao(){
		//百家樂
		$MaHao['Bacc']['Xian']='閒';
		$MaHao['Bacc']['Zhuang']='莊';	
		$MaHao['Bacc']['He']='和';
		$MaHao['Bacc']['ZDD']='莊對';
		$MaHao['Bacc']['XDD']='閒對';
		//翻攤
		for($i=1;$i<=4;$i++){
			$MaHao['FanTan']['0'.$i.',']='0'.$i.',';
		}
		for($i=2;$i<=4;$i++){
			$MaHao['FanTan']['01'.',0'.$i.',']='01'.',0'.$i.',';
		}
		$MaHao['FanTan']['02,03,']='02,03,';
		$MaHao['FanTan']['02,04,']='02,04,';
		$MaHao['FanTan']['03,04,']='03,04,';
		$MaHao['FanTan']['01,02,03']='01,02,03';
		$MaHao['FanTan']['01,02,04']='01,02,04';
		$MaHao['FanTan']['01,03,04']='01,03,04';
		$MaHao['FanTan']['02,03,04']='02,03,04';
		//輪盤
		$MaHao['LunPan']['A0,']='A0,';
		$MaHao['LunPan']['A1,']='A1,';
		$MaHao['LunPan']['A0,A1,']='A0,A1,';
		$MaHao['LunPan']['A0,03,']='A0,03,';
		$MaHao['LunPan']['A0,02,']='A0,02,';
		$MaHao['LunPan']['A0,01,']='A0,01,';
		$MaHao['LunPan']['A1,02,']='A1,02,';
		$MaHao['LunPan']['A1,01,']='A1,01,';
		$MaHao['LunPan']['A0,02,03,']='A0,02,03,';
		$MaHao['LunPan']['A0,01,02,']='A0,01,02,';
		$MaHao['LunPan']['A0,A1,02,']='A0,A1,02,';
		$MaHao['LunPan']['A1,01,02,']='A1,01,02,';
		$MaHao['LunPan']['A0,01,02,03,']='A0,01,02,03,';
		$MaHao['LunPan']['A0,A1,01,02,03,']='A0,A1,01,02,03,';
		for($i=1;$i<=11;$i++){
			$MaHao['LunPan'][str_pad($i,2,'0',STR_PAD_LEFT)]=str_pad($i,2,'0',STR_PAD_LEFT).',';	
		}
		//骰子
		for($i=1;$i<=6;$i++){
			$MaHao['ShaiZi']['A0'.$i]=$i.'點';
		}
		for($i=1;$i<=6;$i++){
			for($j=$i;$j<=6;$j++){
				$MaHao['ShaiZi']['B'.$i.$j]=$i.'+'.$j;	
			}
		}
		for($i=4;$i<=17;$i++){
			$MaHao['ShaiZi']['C'.str_pad($i,2,'0',STR_PAD_LEFT)]='和值：'.$i;	
		}
		for($i=1;$i<=6;$i++){
			$MaHao['ShaiZi']['D0'.$i]='圍骰'.$i;	
		}
		$MaHao['ShaiZi']['E01']='小';
		$MaHao['ShaiZi']['E02']='大';
		$MaHao['ShaiZi']['F01']='全圍(豹子)';
		//魚蝦蟹
		$MaHao['YuXiaXie']['A01']='魚';
		$MaHao['YuXiaXie']['A02']='蝦';
		$MaHao['YuXiaXie']['A03']='葫蘆';
		$MaHao['YuXiaXie']['A04']='老虎';
		$MaHao['YuXiaXie']['A05']='螃蟹';
		$MaHao['YuXiaXie']['A06']='雞';
		for($i=4;$i<=17;$i++){
			$MaHao['YuXiaXie']['C'.str_pad($i,2,'0',STR_PAD_LEFT)]=$i.'點';	
		}

		for($i=1;$i<=6;$i++){
			$MaHao['YuXiaXie']['D0'.$i]='圍骰'.$i;	
		}
		$MaHao['YuXiaXie']['E01']='小';
		$MaHao['YuXiaXie']['E02']='大';
		$MaHao['YuXiaXie']['F01']='全圍(豹子)';
		for($i=1;$i<=3;$i++){
			$MaHao['YuXiaXie']['G'.$i.'1']='指定'.$i.'色(紅)';
			$MaHao['YuXiaXie']['G'.$i.'2']='指定'.$i.'色(綠)';
			$MaHao['YuXiaXie']['G'.$i.'3']='指定'.$i.'色(藍)';
		}
		$MaHao['YuXiaXie']['G30']='任意3色';
		//龍虎
		$MaHao['LongHu']['Long']='龍';
		$MaHao['LongHu']['LongDan']='龍單';
		$MaHao['LongHu']['LongHei']='龍黑';
		$MaHao['LongHu']['HuHong']='虎紅';
		$MaHao['LongHu']['Hu']='虎';
		$MaHao['LongHu']['LongShuang']='龍雙';
		$MaHao['LongHu']['HuDan']='虎單';
		$MaHao['LongHu']['HuHei']='虎黑';
		$MaHao['LongHu']['He']='和';
		$MaHao['LongHu']['LongHong']='龍紅';
		$MaHao['LongHu']['HuShuang']='虎雙';
		//保險百家樂
		$MaHao['InsuBacc']['Xian']='閒';
		$MaHao['InsuBacc']['Zhuang']='莊';	
		$MaHao['InsuBacc']['He']='和';
		$MaHao['InsuBacc']['ZDD']='莊對';
		$MaHao['InsuBacc']['XDD']='閒對';
		$MaHao['InsuBacc']['XBX']='閒保險';
		$MaHao['InsuBacc']['ZBX']='莊保險';
		//print_r($MaHao);
		return $MaHao;
	}


}

?>