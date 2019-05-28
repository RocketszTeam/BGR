<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Bingo{
	var $CI;
	var $timeout=Bingo_timeout;	//curl允許等待秒數
    const API_URL = Bingo_API_URL;
	const API_KEY = Bingo_API_KEY;
	public function __construct(){	
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function create_account($u_id,$u_password,$mem_num,$gamemaker_num=28){
		//先判定此會員是否已有帳號	
		$parameter=array();
		$sqlStr="select * from `games_account` where  `mem_num`=? and `gamemaker_num`=?";
		$parameter[':mem_num']=$mem_num;
		$parameter[':gamemaker_num']=$gamemaker_num;
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){	//無帳號才呼叫api
			$api_url='/api/players';
			$post_data=array('account'=>trim($u_id),'name'=>trim($u_id),'password'=>trim($u_password),'password_confirmation'=>trim($u_password));
			$output=json_decode($this->curl($api_url,'POST',$post_data));
			//print_r($output);exit;
			if($output->error_code==0){
				if($output->http_code==201){
					$colSql="u_id,u_password,mem_num,gamemaker_num";
					$upSql="INSERT INTO `games_account` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter=array();
					$parameter[':u_id']=trim($u_id);
					$parameter[':u_password']=$this->CI->encryption->encrypt($u_password);	//密碼加密	
					$parameter[':mem_num']=$mem_num;
					$parameter[':gamemaker_num']=$gamemaker_num;
					$this->CI->webdb->sqlExc($upSql,$parameter);
					return NULL;
				}else{
					return '創建失敗：'.$output->response->errors->account[0];
				}
			}else{
				return '系統繁忙中，請稍候再試';	
			}
		}else{
			return '會員已有此類型帳號';
		}
	}
	
	public function get_balance($u_id){	//餘額查詢
		$api_url='/api/players';
		$post_data=array('identify'=>trim($u_id),'only_balance'=>1);
		$output=json_decode($this->curl($api_url,'GET',$post_data));
		//print_r($output);;
		if($output->error_code==0){
			if($output->http_code==200){
				return  $output->response->data[0]->balance;
			}else{
				return	'--';
			}
		}else{
			return	'--';	
		}
	}
	
	public function deposit($u_id,$amount,$mem_num,$gamemaker_num=28){	//轉入點數到遊戲帳號內
		$api_url='/api/points/deposit';
		$post_data=array('player_identifies'=>array(trim($u_id)),'volume'=>trim($amount));
		$output=json_decode($this->curl($api_url,'POST',$post_data));
		//print_r($output);;
		if($output->error_code==0){
			if($output->http_code==201){
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
				$parameter[":makers_balance"]= $output->response[0]->after_balance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
				
				//$output->response[0]->after_balance; 加點後的餘額欄位
			}else{
				return '轉點錯誤：'.$output->response->errors;
			}
		}else{
			//return $this->point_checking($sn,$mem_num,$gamemaker_num);
			return	'系統繁忙中，請稍候再試';	
		}
	}
	
	public function withdrawal($u_id,$amount,$mem_num,$gamemaker_num=28){	//遊戲點數轉出
		$api_url='/api/points/withdraw';
		$post_data=array('player_identifies'=>array(trim($u_id)),'volume'=>trim($amount));
		$output=json_decode($this->curl($api_url,'POST',$post_data));
		//print_r($output);
		if($output->error_code==0){
			if($output->http_code==201){
				$WalletTotal=getWalletTotal($mem_num);	//會員餘額
				$before_balance=(float)$WalletTotal;//異動前點數
				$after_balance= (float)$before_balance - (float)$amount;//異動後點數
				$parameter=array();
				$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime,before_balance,after_balance";
				$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[":mem_num"]=$mem_num;
				$parameter[":kind"]=4;	//遊戲轉出
				$parameter[":points"]=$amount;
				$parameter[":makers_num"]=$gamemaker_num;
				$parameter[":makers_balance"]= $output->response[0]->after_balance;
				$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
				$parameter[":buildtime"]=now();
				$parameter[':before_balance']=$before_balance;
				$parameter[':after_balance']=$after_balance;
				$this->CI->webdb->sqlExc($sqlStr,$parameter);
				return NULL;
				//$output->response[0]->after_balance; 加點後的餘額欄位
			}else{
				return '轉點錯誤：'.$output->response->errors;
			}
		}else{
			//return $this->point_checking($sn,$mem_num,$gamemaker_num);
			return	'系統繁忙中，請稍候再試';	
		}
	}
	
	//點數確認~~
	public function point_checking($u_id,$amount,$mem_num,$action,$sTime=NULL,$gamemaker_num=28){
		$api_url='/api/points';
		$eTime=date('Y-m-d H:i:s',strtotime($sTime."+10 sec"));
		$post_data=array('player_identify'=>trim($u_id),'action'=>$action,'created_at_begin'=>$sTime,'created_at_end'=>$eTime);
		$output=json_decode($this->curl($api_url,'GET',$post_data));
		print_r($output);;
		if($output->error_code==0){
			if($output->http_code==200){
				if($output->response->total > 0){
					$WalletTotal=getWalletTotal($mem_num);	//檢查錢包點數
					if($action=='deposit'){	//存款需要檢查錢包點數
						if((int)$WalletTotal >=(int)$amount){
							foreach($output->response->data as $row){
								if($row->amount==$amount){
									$parameter=array();
									$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime";
									$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
									$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
									$parameter[":mem_num"]=$mem_num;
									$parameter[":kind"]=3;	//轉入遊戲
									$parameter[":points"]="-".$row->amount;
									$parameter[":makers_num"]=$gamemaker_num;
									$parameter[":makers_balance"]=$row->after_balance;
									$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
									$parameter[":buildtime"]=now();
									$this->CI->webdb->sqlExc($sqlStr,$parameter);
									return NULL;
									break;
								}
							}
						}else{
							return '錢包點數不足'; 	
						}
					}else{	//取款
						foreach($output->response->data as $row){
							if($row->amount==$amount){
								$parameter=array();
								$colSql="mem_num,kind,points,makers_num,makers_balance,admin_num,buildtime";
								$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
								$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
								$parameter[":mem_num"]=$mem_num;
								$parameter[":kind"]=4;	//遊戲轉出
								$parameter[":points"]=abs($output->amount);
								$parameter[":makers_num"]=$gamemaker_num;
								$parameter[":makers_balance"]=$row->after_balance;
								$parameter[":admin_num"]=tb_sql("admin_num","member",$mem_num);
								$parameter[":buildtime"]=now();
								$this->CI->webdb->sqlExc($sqlStr,$parameter);
								return NULL;
								break;
							}
						}
					}
				}else{
					return '轉點失敗或無此紀錄';
				}
			}else{
				return '轉點錯誤：'.$output->http_code;
			}
		}else{
			return	'系統繁忙中，請稍候再試';
		}
	}
	
	
	public function forward_game($u_id){	//登入遊戲
		$api_url="/api/players/$u_id/play-url";
		$output=json_decode($this->curl($api_url,'POST'));
		//print_r($output);exit;
		if($output->error_code==0){
			if($output->http_code==201){
				if(!$this->CI->agent->is_mobile()){		//電腦版
					return  $output->response->play_url;
				}else{	//手機板
					return $output->response->mobile_url;
				}
			}
		}
	}
	
	
	
	public function reporter_all($sTime,$eTime,$page=1,$pageSize=100){	//群撈報表
		$api_url='/api/tickets';
		$post_data=array('created_at_begin'=>$sTime,'created_at_end'=>$eTime,'page'=>$page,'page_size'=>$pageSize);
		$output=json_decode($this->curl($api_url,'GET',$post_data));
		//print_r($output);
		if($output->error_code==0){
			if($output->http_code==200){
				if($output->response->total > 0){
					return $output->response;
				}
			}
		}
	}
	
	
	public function getBetType(){
		for($i=1;$i<=10;$i++){
			$betType["star_".$i]="押星號:".$i."星";	
		}
		$betType["super_odd"]="超級玩法(特別號)： 單";
		$betType["super_even"]="超級玩法(特別號)： 雙";
		$betType["super_guess"]="超級玩法(特別號)： 獨猜";
		$betType["super_big"]="超級玩法(特別號)： 大";
		$betType["super_small"]="超級玩法(特別號)： 小";
		$betType["super_tie"]="超級玩法(特別號)： 合";
		$betType["normal_odd"]="一般玩法： 單";
		$betType["normal_even"]="一般玩法： 雙";
		$betType["normal_draw"]="一般玩法： 平";
		$betType["normal_big"]="一般玩法： 大";
		$betType["normal_small"]="一般玩法： 小";
		$betType["normal_tie"]="一般玩法： 合";
		
		$betType["element_metal"]="五行： 金";
		$betType["element_wood"]="五行： 木";
		$betType["element_water"]="五行： 水";
		$betType["element_fire"]="五行： 火";
		$betType["element_earth"]="五行： 土";
		
		$betType["season_spring"]="四季： 春";
		$betType["season_summer"]="四季： 夏";
		$betType["season_autumn"]="四季： 秋";
		$betType["season_winter"]="四季： 冬";
		$betType["other_fanbodan"]="其他： 反波膽";
		return $betType;
	}
	
    public function curl($api, $method = 'POST', $parameters = [], $options = []) {
        $curl = curl_init();

        $curlSetting = [
            CURLOPT_URL => self::API_URL . $api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT=> 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Safari/604.1.38',
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        // 加入自訂分頁器
        $curlSetting[CURLOPT_POSTFIELDS] = json_encode((array_key_exists('pagination', $options)) ?
            array_merge($parameters, $options['pagination']) :
            $parameters
        );

        // 合併自訂請求表頭
        $headers = [
            'X-Requested-With: XMLHttpRequest',
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::API_KEY,
            'Content-Length: ' . strlen(json_encode($parameters))
        ];

        if (array_key_exists('headers', $options)) {
            $headers = array_merge($options['headers'], $headers);
        }
        $curlSetting[CURLOPT_HTTPHEADER] = $headers;

        curl_setopt_array($curl, $curlSetting);

        $response = curl_exec($curl);
        $err = curl_error($curl);
		$curl_errno = curl_errno($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
		//print_r($response);
		//echo '<hr>';
		if(!$curl_errno){
			return json_encode(array('error_code'=>$curl_errno,'http_code'=>$http_code,'response'=>json_decode($response)));
		}else{
			return json_encode(array('error_code'=>$curl_errno,'http_code'=>$http_code,'Error'=>$err));
		}
        //return ($err) ? 'Error: ' . $err : ['response' => $response, 'http_code' => $http_code];
    }
	
	
	
}

?>