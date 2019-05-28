<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Maya {
	var $CI;
	var $timeout=MAYA_timeout;	//curl允許等待秒數
	var $Checkkey=MAYA_Checkkey;	//自行設定
	var $api_url=MAYA_api_url;	//API URL
	var $game_url=MAYA_game_url;	//遊戲URL
	var	$VenderNo=MAYA_VenderNo;	//代理編碼(廠商給的)
	var $SiteNo=MAYA_SiteNo;	//分站編號自行設定
	var $LayerNo=MAYA_LayerNo;	//層級編碼(自行定義)
	var	$MD5KEY=MAYA_MD5KEY;	//数据签名MD5密码
	var	$DESKEY=MAYA_DESKEY;	//页面DES密码
	var $Token=MAYA_Token;	//自定義
	var $LanguageNo=MAYA_LanguageNo;	//語系
	var $GameConfigID=MAYA_GameConfigID;	//限紅編號
	var $CurrencyNo=MAYA_CurrencyNo;	//貨幣代號
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function getToken(){
		return $this->Token;
	}
	
	public function getGameID(){
		$GameID['Baccarat']='百家樂';
		$GameID['Roulette']='輪盤';
		$GameID['LongHu']='龍虎';
		$GameID['BIDBaccarat']='競咪百家樂';
		$GameID['VipBaccarat']='百家樂包桌';
		$GameID['Dice']='骰子';
		$GameID['INSBaccarat']='保險百家樂';
		return $GameID;	
	}
	
	public function create_account($u_id,$mem_num,$gamemaker_num=11){	//創建遊戲帳號
		$parameter=array();
		$sqlStr="select * from `games_account` where `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api
			$post_data=array(
							'VenderNo'=>$this->VenderNo,
							'SiteNo'=>$this->SiteNo,
							'LayerNo'=>$this->LayerNo,
							'VenderMemberID'=>trim($u_id),
							'MemberName'=>trim($u_id),
							'TestState'=>0,
							'CurrencyNo'=>$this->CurrencyNo,
							'NickName'=>trim($u_id),
							'NowDateTime'=>date('YmdHis'));
			$MD5DATA=$this->md5Data($post_data);
			$url=$this->api_url."/CreateMember?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
			$ch = curl_init();		
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
			$output = json_decode(curl_exec($ch));
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errno = curl_errno($ch);

			
			if(!$curl_errno){
				if($http_code===200 && $output->ErrorCode==0){
					$colSql="u_id,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);	
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return '會員創建失敗：'.$output->ErrorCode.@$output->ErrorMsg;	
				}
			}else{
				return '系統繁忙中，請稍後再試'	;
			}
		}else{
			return '會員已有此類型帳號';
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$GameMemberID=$this->getMemberID($u_id);
		$post_data=array('VenderNo'=>$this->VenderNo,'GameMemberIDs'=>trim($GameMemberID),'NowDateTime'=>date('YmdHis'));
		$MD5DATA=$this->md5Data($post_data);
		$url=$this->api_url."/GetBalance?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->ErrorCode==0){
				return $output->MemberBalanceList[0]->Balance;	
			}else{
				return '--';	
			}
		}else{
			return '--';
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=11,$logID=NULL){	//轉入點數到遊戲帳號內
		$GameMemberID=$this->getMemberID($u_id);
		$VenderTransactionID=time().mt_rand();
		$post_data=array(
						'VenderNo'=>$this->VenderNo,
						'GameMemberID'=>trim($GameMemberID),
						'VenderTransactionID'=>$VenderTransactionID,
						'Amount'=>trim($amount),
						'Direction'=>'in',
						'NowDateTime'=>date('YmdHis'));
		$MD5DATA=$this->md5Data($post_data);				
		$url=$this->api_url."/FundTransfer?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->ErrorCode==0){
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				$before_balance=(float)$WalletTotal;//異動前點數
				$after_balance= (float)$before_balance - (float)round($amount,0);//異動後點數
				$parameter=array();
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=3;	//轉入遊戲
				$parameter[":points"]="-".$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]=$output->AfterBalance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				//將轉點編號寫入DB
				$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$VenderTransactionID."' where num=".$logID;
				$this->CI->webdb->sqlExc($upSql);
				return NULL;
			}else{
				return '轉點錯誤：'.$output->ErrorCode.@$output->ErrorMsg;	
			}
		}else{
			return CheckFundTransfer($VenderTransactionID,$post_data['Direction'],$amount,$mem_num,$gamemaker_num);
		}
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=11,$logID=NULL){	//遊戲點數轉出
		$GameMemberID=$this->getMemberID($u_id);
		$VenderTransactionID=time().mt_rand();
		$post_data=array(
						'VenderNo'=>$this->VenderNo,
						'GameMemberID'=>trim($GameMemberID),
						'VenderTransactionID'=>$VenderTransactionID,
						'Amount'=>trim($amount),
						'Direction'=>'out',
						'NowDateTime'=>date('YmdHis'));
		$MD5DATA=$this->md5Data($post_data);				
		$url=$this->api_url."/FundTransfer?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->ErrorCode==0){
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
				$parameter[":makers_balance"]=$output->AfterBalance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				//將轉點編號寫入DB
				$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$VenderTransactionID."' where num=".$logID;
				$this->CI->webdb->sqlExc($upSql);
				return NULL;
			}else{
				return '轉點錯誤：'.$output->ErrorCode.@$output->ErrorMsg;	
			}
		}else{
			return CheckFundTransfer($VenderTransactionID,$post_data['Direction'],$amount,$mem_num,$gamemaker_num);
		}
	}
	
	private function CheckFundTransfer($VenderTransactionID,$type,$amount,$mem_num,$gamemaker_num,$logID=NULL){	//檢查轉帳
		$post_data=array(
						'VenderNo'=>$this->VenderNo,
						'VenderTransactionID'=>$VenderTransactionID,
						'NowDateTime'=>date('YmdHis'));
		$MD5DATA=$this->md5Data($post_data);				
		$url=$this->api_url."/CheckFundTransfer?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200 && $output->ErrorCode==0){
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				if($type=='in'){	//轉入點數
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
					$parameter[":makers_balance"]=$output->AfterBalance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}else{	//轉出點數
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
					$parameter[":makers_balance"]=$output->AfterBalance;
					$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
					$parameter[":buildtime"]=now();
					$parameter[':before_balance']=$before_balance;
					$parameter[':after_balance']=$after_balance;
					$this->CI->webdb->sqlExc($sqlStr,$parameter);
					return NULL;
				}
				//將轉點編號寫入DB
				$upSql="UPDATE `member_wallet_log` SET `TradeNo`='".$VenderTransactionID."' where num=".$logID;
				$this->CI->webdb->sqlExc($upSql);				
			}else{
				return '轉點錯誤：'.$output->ErrorCode.@$output->ErrorMsg;	
			}
		}else{
			return '系統繁忙中，請稍後再試'	;
		}
	
	
	}
	
	//透過遊戲帳號取得會員ID
	public function getMemberID($u_id){
		$post_data=array('VenderNo'=>$this->VenderNo,'VenderMemberID'=>trim($u_id),'SiteNo'=>$this->SiteNo,'NowDateTime'=>date('YmdHis'));
		$MD5DATA=$this->md5Data($post_data);
		$url=$this->api_url."/GetGameMemberID?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->ErrorCode==0){
				return $output->GameMemberID;	
			}
		}
	}
	
	
	//取得遊戲token
	public function forward_game($u_id){
		$GameMemberID=$this->getMemberID($u_id);	
		if($GameMemberID){	
			$params["VenderNo"]=$this->VenderNo;
			$params["SiteNo"]=$this->SiteNo;
			$params['GameMemberID']=$GameMemberID;
			$params["MemberName"]=$u_id;
			$params["GameConfigID"]=$this->GameConfigID;
			$params["LanguageNo"]=$this->LanguageNo;
			$params["ShowRecharge"]=0;
			$params['Token']=$this->Token;
			$params['OpenURL']='';
			$params['OpenBackURL']='';
			$params['EntryType']=(!$this->CI->agent->is_mobile() ? 0 : 1);	//0 :PC Flash 進入 (默認值) 1:移動端 app HTML5 進入
			$params["NowDateTime"]=date('YmdHis');
			$DESDATA=$this->desData($params);
			$url=$this->game_url."?VenderNo=".$params["VenderNo"].'&DESDATA='.$DESDATA;
			//echo $url;
			//exit();
			return $url;
		}else{
			return '--';	
		}
	}
	
	
	//試玩
	public function fun_game($GameCode='OGS-1can2can'){		//試玩mode=demo;真錢=real
		$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$HostUrl.=$_SERVER['HTTP_HOST'];
		$token=$this->getToken();
		$data=array('currency'=>'TWD','country'=>'TW','lang'=>'zh_TW','mode'=>'demo',
					'device'=>($this->CI->agent->is_mobile() ? 'mobile' : 'desktop'),
					'returnUrl'=> ($this->CI->agent->is_mobile() ? $HostUrl : ''));
		$post_data=json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api_url."/v1/games/".$GameCode."/launch-url");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$headers=array('Content-Type: application/json','Content-Length:'.strlen($post_data),'Authorization:Bearer '.$token);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		curl_close($ch);	
		//echo $curl_errno;
		//print_r($output);exit;
		if(!$curl_errno){
			if($http_code===200){
				return $output->url;
			}else{
				return '--'	;	
			}
		}else{
			return '--'	;	
		}
	}
	
	//報表
	public function reporter_all($sTime,$eTime,$GameID,$Page=1,$Row=100){
		$post_data=array(
						'VenderNo'=>$this->VenderNo,
						'SiteNo'=>$this->SiteNo,
						'GameID'=>$GameID,
						'StartDateTime'=>trim($sTime),
						'EndDateTime'=>trim($eTime),
						'PageSize'=>$Row,
						'CurrentPage'=>$Page,
						'LanguageNo'=>$this->LanguageNo,
						'NowDateTime'=>date('YmdHis'));
		$MD5DATA=$this->md5Data($post_data);
		$url=$this->api_url."/GetGameDetailForMember?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->ErrorCode==0){
				return $output;	
			}
		}
	}
	
	//根據遊戲起始流水號
	public function reporter_all2($StartGameSequenceID=0){
		$post_data=array(
						'VenderNo'=>$this->VenderNo,
						'SiteNo'=>$this->SiteNo,
						'StartGameSequenceID'=>$StartGameSequenceID,
						'BetCodeLanguage'=>$this->LanguageNo,
						'NowDateTime'=>date('YmdHis'));
		$MD5DATA=$this->md5Data($post_data);
		$url=$this->api_url."/GetGameDetailForSequence?".http_build_query($post_data).'&MD5DATA='.$MD5DATA;
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
		$output = json_decode(curl_exec($ch));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($ch);
		//print_r($output);
		if(!$curl_errno){
			if($http_code===200 && $output->ErrorCode==0){
				return $output->GameDetailList;	
			}
		}
	}
	
	
	
	public function getBetType(){
		//打賞
		$betType['DaShang']='[打賞單]';
		//百家樂
		$betType['Zhuang']='莊';
		$betType['ZhuangFree']='免傭莊下注';
		$betType['Xian']='閒';
		$betType['Da']='大';
		$betType['Xiao']='小';
		$betType['ZDD']='莊對';
		$betType['XDD']='閒對';
		$betType['ZhuangINS4']='莊保險（未補過牌保險下注）';
		$betType['ZhuangINS5']='莊保險（補過一張牌後保險下注）';
		$betType['XianINS4']='閒保險（未補過牌保險下注）';
		$betType['XianINS5']='閒保險（（補過一張牌後保險下注）';
		$betType['He']='和';
		//龍虎
		$betType['Long']='龍';
		$betType['Hu']='虎';
		$betType['LongDan']='龍單';
		$betType['HuDan']='虎單';
		$betType['LongShuang']='龍雙';
		$betType['HuShuang']='虎雙';
		//骰子
		$betType['E01']='大';
		$betType['E02']='小';
		$betType['E03']='單';
		$betType['E04']='雙';
		for($i=1;$i<=6;$i++) $betType['A0'.$i]='押注'.$i.'點';
		for($i=1;$i<=6;$i++) $betType['D0'.$i]='圍骰'.$i.'點';
		$betType['F01']='全圍';
		for($i=1;$i<=6;$i++) $betType['P'.$i.$i]='對子'.$i.'點';
		for($i=2;$i<=6;$i++) $betType['B1'.$i]='組合1'.$i;
		for($i=3;$i<=6;$i++) $betType['B2'.$i]='組合2'.$i;
		for($i=4;$i<=6;$i++) $betType['B3'.$i]='組合3'.$i;
		for($i=5;$i<=6;$i++) $betType['B4'.$i]='組合4'.$i;
		$betType['B56']='組合56';
		for($i=1;$i<=17;$i++) $betType['C'.$this->zero($i,2)]='和值'.$i.'點';
		
		//牛牛
		for($i=1;$i<=3;$i++){
			$betType['XianEqual'.$i]='閒'.$i.'平倍';
			$betType['XianDouble'.$i]='閒'.$i.'翻倍';
		}
		
		//輪盤
		$betType['LZ,01,04,07,10,13,16,19,22,25,28,31,34,']='第一列';
		$betType['LZ,02,05,08,11,14,17,20,23,26,29,32,35,']='第二列';
		$betType['LZ,03,06,09,12,15,18,21,24,27,30,33,36,']='第三列';
		$betType['DZ,01,02,03,04,05,06,07,08,09,10,11,12,']='第一打';
		$betType['DZ,13,14,15,16,17,18,19,20,21,22,23,24,']='第二打';
		$betType['DZ,25,26,27,28,29,30,31,32,33,34,35,36,']='第三打';
		
		$betType['DS,01,03,05,07,09,11,13,15,17,19,21,23,25,27,29,31,33,35,']='單';
		$betType['DS,02,04,06,08,10,12,14,16,18,20,22,24,26,28,30,32,34,36,']='雙';
		$betType['HH,01,03,05,07,09,12,14,16,18,19,21,23,25,27,30,32,34,36,']='紅';
		$betType['HH,02,04,06,08,10,11,13,15,17,20,22,24,26,28,29,31,33,35,']='黑';
		$betType['DX,01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,']='小';
		$betType['DX,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,']='大';
		
		return $betType;
		
	}
	
	//補零
	private function zero($str,$len){
		if($str!='' && $len!=''){
			return str_pad($str,$len,'0',STR_PAD_LEFT);	
		}
	}
	
	/**
	 * 数据API
	 * 获取数据API MD5DATA数据签名值
	 * 该方法在访问普通API时使用，例如： CreateMember.do  GetGameMemberID.do 等...
	 * param key 提供的密码
	 * param params 访问接口时需要带入的参数
	 * return 返回MD5DATA数据签名值
	 */
	public function md5Data($params){
		$arrayKeys = array_keys($params);
		array_push($arrayKeys, 'pwd');
		natcasesort($arrayKeys);
		$str = '';
		foreach ($arrayKeys as $value) {
			$v = @$params[$value];
			if (is_null($v)){
				$str .= $this->MD5KEY;
			}else{
				$str .= $v;
			}
		}
		return md5($str);
	}
	
	
	private function desData($params){
		$params['MD5DATA'] = $this->md5Data($params);
		unset($params["VenderNo"]);
		$arrayKeys = array_keys($params);
		$str = '';
		foreach ($arrayKeys as $value) {
			$str .= $value . '=' . $params[$value] . '&';
		}
		$str=substr($str,0,strlen($str)-1);
		// DES/CBC/PKCS5Padding
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
		$str = $this->pkcs5Pad($str, $size);
		$td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
		$iv = $this->DESKEY;
		mcrypt_generic_init($td, $this->DESKEY, $iv);
		$data = mcrypt_generic($td, $str);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return bin2hex($data);
	}
	
	function pkcs5Pad($str, $blockSize){
		$pad = $blockSize - (strlen($str) % $blockSize);
		return $str . str_repeat(chr($pad), $pad);
	}
	
	
	
}

?>