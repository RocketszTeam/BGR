<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Manger extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->library('memberclass');	//載入會員函式庫
		$this->load->library('api/allgameapi');	//載入遊戲api
	}
	
	
	public function register(){	//註冊會員
		$logMsg=$this->memberclass->isLogin();
		if($logMsg==NULL){header("Location:".base_url());exit;}	//如果已經登入會員跳轉
		
		//產生驗證碼token
		$this->data["token"] = md5(uniqid(rand(), true));		
		$this->data["sms_token"] = md5(uniqid(rand(), true));	//手機簡訊token		
		$this->session->set_userdata('code_token', $this->data["token"]);		
		$this->session->set_userdata('sms_token', $this->data["sms_token"]);		
		
		$this -> load -> view("www/register.php", $this -> data);
		
	}
	
	public function register_do(){	//處理註冊會員
		if(!$this->input->is_ajax_request()){
			echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			exit;
		}
		if(!$this->agent->is_referral()){
			//密碼原則規則判斷
			if(!preg_match('/^(?!([^a-zA-Z]+|\D+)$)[a-zA-Z0-9]{6,13}$/',$this->input->post('u_password',true))){
				//scriptMsg("密碼請輸入6~25碼英文數字混合！", "Member/register");
				echo json_encode(array('RntCode'=>'N','Msg'=>'密碼請輸入6~13碼英文數字混合！'));
				exit;
			}else{
				if($this->input->post('u_password')!=$this->input->post('u_password2')){
					//scriptMsg("會員密碼與確認密碼不相同！", "Member/register");
					echo json_encode(array('RntCode'=>'N','Msg'=>'會員密碼與確認密碼不相同！'));
					exit;
				}
			}
			
			//判斷發送驗證碼手機 和 送出手機是否相同
			
			if(!$this->session->userdata('sms_phone') || ($this->session->userdata('sms_phone')!=$this->input->post('phone',true))){
				//scriptMsg("驗證手機與輸入手機不符", "Member/register");
				echo json_encode(array('RntCode'=>'N','Msg'=>'驗證手機與輸入手機不符！'));
				exit;
			}
			
			
			if(strtolower($this->input->post('checknum',true))==strtolower($this->session->userdata('reg_checknum'))){	//驗證碼判斷
				if($this->input->post('sms_code',true) && $this->session->userdata('sms_code')==$this->input->post('sms_code',true)){	//簡訊驗證碼
					$accountLog=json_decode($this->chkid($this->input->post('u_id',true)));
					if($accountLog->RntCode=='Y'){	//檢查帳號是否重複
						$phoneLog=json_decode($this->chkphone($this->input->post('phone',true)));
						if($phoneLog->RntCode=='Y'){	//檢查手機是否已被註冊
							$parameter=array();
							$colSql="nation,u_id,u_password,u_name,phone,line,wechat";
							$colSql.=",active,admin_num,reg_time";
							$sqlStr="INSERT INTO `member` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':nation']='TW';
							$parameter[':u_id']=$this->input->post('u_id',true);
							$parameter[":u_password"]=$this->encryption->encrypt($this->input->post("u_password",true));
							$parameter[':u_name']=$this->input->post('u_name',true);
							$parameter[':phone']=$this->input->post('phone',true);
							$parameter[':line']=$this->input->post('line',true);
							$parameter[':wechat']=$this->input->post('wechat',true);
							$parameter[':active']='Y';
							$parameter[':admin_num']=$this->data["Web_CustNum"];
							$parameter[':reg_time']=now();
							$mem_num=$this->webdb->sqlExc($sqlStr,$parameter);
							if($mem_num){
								$u_id=trim($this->input->post('u_id',true));
								$u_password=$this->input->post('u_password',true);
								//執行登入	
								$logMsg=$this->memberclass->login($u_id,$u_password,$this->data["Web_CustNum"]);
								
								
								echo json_encode(array('RntCode'=>'Y','Msg'=>''));
								exit;
							}else{
								echo json_encode(array('RntCode'=>'N','Msg'=>'加入會員失敗，請重新填寫！'));
								exit;
							}
						}else{
							echo json_encode(array('RntCode'=>'N','Msg'=>$phoneLog->Msg));
							exit;
						}
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>$accountLog->Msg));
						exit;
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'簡訊認證碼錯誤,請重新輸入!!'));
					exit;
				}
			}else{
				
				echo json_encode(array('RntCode'=>'N','Msg'=>'驗證碼錯誤,請重新輸入!!'));
				exit;
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'您的網域不被允許!!'));
			exit;
		}
	}
	
	//修改會員資料
	public function account(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			
			//銀行下拉
			$sqlStr="select * from bank_list";
			$this->data["bankList"]=$this->webdb->sqlRowList($sqlStr);
			
			
			$sqlStr="select * from `member` where num=?";
			$this->data['rowMember']=$this->webdb->sqlRow($sqlStr,array(':num'=>$this->data["user_info"]["num"]));
			$this -> load -> view("www/account.php", $this -> data);
		}
		
	}
	
	public function account_do(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			if(!$this->agent->is_referral()){
				$sqlStr="select * from `member` where num=?";
				$row=$this->webdb->sqlRow($sqlStr,array(':num'=>$this->data["user_info"]["num"]));
				if($row!=NULL){
					if($this->input->post('u_password',true)==$this->encryption->decrypt($row["u_password"])){
						//更新會員資料
						/*
						$parameter=array();
						$colSql="u_name,line,wechat";
						$sqlStr="UPDATE `member` SET ".sqlUpdateString($colSql);
						$sqlStr.=" where num=".$row["num"];
						$parameter[':u_name']=$this->input->post('u_name',true);
						$parameter[':line']=$this->input->post('line',true);
						$parameter[':wechat']=$this->input->post('wechat',true);
						$this->webdb->sqlExc($sqlStr,$parameter);
						$Msg='會員資料修改完成！';
						*/
						//處理修改密碼
						if($this->input->post('new_password',true)==$this->input->post('new_password2',true) && $this->input->post('new_password')!=NULL){
							$parameter=array();
							$sqlStr="UPDATE `member` SET `u_password`=? where num=".$row["num"];
							$parameter[':u_password']=$this->encryption->encrypt($this->input->post('new_password',true));	//密碼加密
							$this->webdb->sqlExc($sqlStr,$parameter);
							$Msg='會員密碼修改完成！';
						}
						$this -> session -> set_flashdata("alertMsg",$Msg);
						scriptMsg('',"Manger/account");
						exit;
					}else{
						$this -> session -> set_flashdata("alertMsg",'會員密碼錯誤！');
						scriptMsg("","Manger/account");
						exit;
					}
				}else{
					$this -> session -> set_flashdata("alertMsg",'會員資料不存在！');
					scriptMsg("","Manger/account");
					exit;
				}
			}else{
				$this -> session -> set_flashdata("alertMsg",'您的網域不被允許');
				scriptMsg("","Manger/account");
				exit;
			}
		}
	}
	public function bank_do(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			if(!$this->agent->is_referral()){
				$parameter=array();
				$colSql="mem_num,bank_num,bank_branch,bank_account,account_name";
				$sqlStr="INSERT INTO `member_bank` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter[':mem_num']=$this->memberclass->num();
				$parameter[':bank_num']=$this->input->post('bank_num',true);
				$parameter[':bank_branch']=$this->input->post('bank_branch',true);
				$parameter[':bank_account']=$this->input->post('bank_account',true);
				$parameter[':account_name']=$this->input->post('account_name',true);
				$this->webdb->sqlExc($sqlStr,$parameter);
				$this -> session -> set_flashdata("alertMsg",'銀行帳戶設定完成');
				scriptMsg("","Manger/account");
				
			}else{
				$this -> session -> set_flashdata("alertMsg",'您的網域不被允許');
				scriptMsg("","Manger/account");
				exit;
			}
		}
	}
	
	
	
	//提領
	public function withdrawal(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			
			//檢查會員銀行帳戶
			$sqlStr="select * from `member_bank` where `mem_num`=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
			if($row==NULL){
				$this -> session -> set_flashdata("alertMsg",'請先設定銀行帳戶');
				scriptMsg('',"Manger/account");
				exit;
			}

			$parameter=array();
			//幣商廣告			
			$sqlStr="select * from `coinman` where 1=1 ".time_sql();
			$sqlStr.=" order by buildtime DESC,num DESC";
			$this->data["BitCoin"]=$this->webdb->sqlRowList($sqlStr,$parameter);
			
			$this->load->library('orderclass');	//訂單函式庫
			$this -> load -> view("www/withdrawal.php", $this -> data);
		}
		
	}
	
	public function withdrawal_do(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			if(!$this->agent->is_referral()){
				if(preg_match('/^[0-9]*[1-9][0-9]*$/',$this->input->post('amount'))){
					if($this->input->post('amount') <= (int)$this->data["user_info"]["WalletTotal"]){
						//檢查會員銀行帳戶
						$sqlStr="select * from `member_bank` where `mem_num`=?";
						$row=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
						if($row==NULL){
							$this -> session -> set_flashdata("alertMsg",'請先設定銀行帳戶');
							scriptMsg('',"Manger/account");
							exit;
						}
						
						
						$this->load->library('orderclass');	//訂單函式庫
						$order_no=$this->orderclass->order_no();
						if($order_no!=NULL){
							//建立訂單語法
							$parameter=array();
							$colSql="order_no,mem_num,admin_num,amount,fee,bank_name,bank_branch,bank_account,account_name,buildtime";
							$sqlStr="INSERT INTO `member_sell` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':order_no']=$order_no;
							$parameter[':mem_num']=$this->memberclass->num();
							$parameter[':admin_num']=$this->data["user_info"]['agent'];
							$parameter[':amount']=$this->input->post('amount',true);
							//手續費運算
							$parameter[':fee']=(int)$this->input->post('amount',true) * 0.02;

							$parameter[':bank_name']=$this->data["user_info"]["bank_name"];
							$parameter[':bank_branch']=$this->data["user_info"]["bank_branch"];
							$parameter[':bank_account']=$this->data["user_info"]["bank_account"];
							$parameter[':account_name']=$this->data["user_info"]["account_name"];
							
							$parameter[':buildtime']=now();
							$this->webdb->sqlExc($sqlStr,$parameter);
							
							$WalletTotal=getWalletTotal($this->memberclass->num());	//會員餘額
							//異動前點數
							$before_balance=(float)$WalletTotal;
							//異動後點數
							$after_balance= (float)$before_balance - (float)$this->input->post('amount',true);
							
							
							//錢包扣點處理
							$parameter=array();
							$colSql="mem_num,kind,points,word,admin_num,buildtime,before_balance,after_balance";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':mem_num']=$this->memberclass->num();
							$parameter[':kind']=9;	//會員提領
							$parameter[':points']="-".$this->input->post('amount',true);
							$parameter[':word']='會員提領';
							$parameter[':admin_num']=tb_sql("admin_num","member",$this->memberclass->num());
							$parameter[':buildtime']=now();	
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$this->webdb->sqlExc($sqlStr,$parameter);
							
							$this -> session -> set_flashdata("alertMsg",'轉點單已經送出，我們將盡快為您處理。');
							scriptMsg('',"Manger/withdrawal_order");
							exit;
						}else{
							$this -> session -> set_flashdata("alertMsg",'訂單建立失敗‧‧請重新嘗試');
							scriptMsg('',"Manger/withdrawal");
							exit;
						}
					}else{
						$this -> session -> set_flashdata("alertMsg",'錢包點數不足');
						scriptMsg("","Manger/withdrawal");
						exit;
					}
				}else{
					$this -> session -> set_flashdata("alertMsg",'提領金額請輸入數字');
					scriptMsg("","Manger/withdrawal");
					exit;
				}
			}else{
				$this -> session -> set_flashdata("alertMsg",'您的網域不被允許');
				scriptMsg("","Manger/withdrawal");
				exit;
			}
		}
	}
	
	//提領結果頁
	public function withdrawal_order(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			$this->load->library('orderclass');	//訂單函式庫
			$this -> load -> view("www/withdrawal_order.php", $this -> data);
		}
		
	}
	
	//點數轉移
	public function transfer(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			//遊戲廠商下拉
			$sqlStr="select * from `game_makers` where num in(3,4,8,9,11,12,20,21,22,26,27,28) order by `range`";	//排除黃金俱樂部 and 捕魚機
			$this->data["makers_data"]=$this->webdb->sqlRowList($sqlStr);
			$this -> load -> view("www/transfer.php", $this -> data);
		}
	}
	
	
	//新版面點數轉移AJAX
	public function transfer_do(){
	
		$logMsg=$this->memberclass->isLogin();	
		if($logMsg!=NULL){
			echo json_encode(array('RntCode'=>'W','Msg'=>$logMsg));	
		}else{
			if(!$this->agent->is_referral()){
				if($this->input->is_ajax_request()){
					if($this->input->post('amount') && $this->input->post('makers_num_B')!="" && $this->input->post('makers_num_A')!=""){
						if($this->input->post('makers_num_B')!=$this->input->post('makers_num_A')){
							$amount=$this->input->post('amount',true);
							$mem_num=$this->data["user_info"]["num"];
							$makers_num_A=$this->input->post('makers_num_A',true);
							$makers_num_B=$this->input->post('makers_num_B',true);
							if(preg_match('/^[0-9]*[1-9][0-9]*$/',$amount)){
								//建立Log檔
								$colSql="mem_num,type,source,target,points,buildtime";
								$sqlStr="INSERT INTO `member_wallet_log` (".sqlInsertString($colSql,0).")";
								$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
								$parameter[":mem_num"]=$mem_num;
								$parameter[":type"]=($makers_num_A=='0' ? 1 : 2);	//來源
								$parameter[":source"]=$makers_num_A;	//來源
								$parameter[":target"]=$makers_num_B;	//目的	
								$parameter[":points"]=$amount;	//金額
								$parameter[":buildtime"]=now();
								$logID=$this->webdb->sqlExc($sqlStr,$parameter);
								if($makers_num_A=='0'){	//錢包轉遊戲
									if((int)$amount <= (int)$this->data["user_info"]["WalletTotal"]){
										$logMsg=$this->allgameapi->deposit($amount,$mem_num,$makers_num_B,$logID);
										if($logMsg==NULL){
											//變更狀態為轉移成功
											$upSql="UPDATE `member_wallet_log` SET `status`=1,`upTime`='".now()."' where num=".$logID;
											$this->webdb->sqlExc($upSql);
											echo json_encode(array('RntCode'=>'Y','Msg'=>'點數轉移成功!!!'));
											$this -> session -> set_flashdata("alertMsg",'點數轉移成功');
										}else{
											//紀錄失敗原因
											$upSql="UPDATE `member_wallet_log` SET `word`='".$logMsg."',`upTime`='".now()."' where num=".$logID;
											$this->webdb->sqlExc($upSql);
											echo json_encode(array('RntCode'=>'N','Msg'=>$logMsg));
										}
									}else{
										echo json_encode(array('RntCode'=>'N','Msg'=>'錢包點數不足'));
									}
								}elseif($makers_num_B=='0'){	//遊戲轉回錢包
									$logMsg=$this->allgameapi->withdrawal($amount,$mem_num,$makers_num_A,$logID);
									if($logMsg==NULL){
										//變更狀態為轉移成功
										$upSql="UPDATE `member_wallet_log` SET `status`=1,`upTime`='".now()."' where num=".$logID;
										$this->webdb->sqlExc($upSql);
										echo json_encode(array('RntCode'=>'Y','Msg'=>'點數轉移成功!!'));
										$this -> session -> set_flashdata("alertMsg",'點數轉移成功');
									}else{
										//紀錄失敗原因
										$upSql="UPDATE `member_wallet_log` SET `word`='".$logMsg."',`upTime`='".now()."' where num=".$logID;
										$this->webdb->sqlExc($upSql);
										echo json_encode(array('RntCode'=>'N','Msg'=>$logMsg));
									}
								}else{	//遊戲互轉
									//先將遊戲轉回錢包
									$delSql="DELETE FROM `member_wallet_log` where num=".$logID;
									$this->webdb->sqlExc($delSql);	//刪除互轉單據紀錄
									
									echo json_encode(array('RntCode'=>'N','Msg'=>'目前各館別遊戲互轉維護中...請統一轉入電子錢包'));
									$this -> session -> set_flashdata("alertMsg",'目前各館別遊戲互轉維護中...請統一轉入電子錢包');
									exit;
									/*
									$logMsg1=$this->allgameapi->withdrawal($amount,$mem_num,$makers_num_A);
									if($logMsg1==NULL){
										if((int)$amount <= (int)$this->memberclass->getWalletTotal($this->data["user_info"]['num'])){	//先檢查錢包點數
											//在從錢包轉入遊戲
											$logMsg2=$this->allgameapi->deposit($amount,$mem_num,$makers_num_B);
											if($logMsg2==NULL){
												echo json_encode(array('RntCode'=>'Y','Msg'=>'點數轉移成功!'));
												$this -> session -> set_flashdata("alertMsg",'點數轉移成功');
											}else{
												echo json_encode(array('RntCode'=>'N','Msg'=>'點數轉移失敗'));
											}
										}else{
											echo json_encode(array('RntCode'=>'N','Msg'=>'點數轉移失敗'));
										}
									}else{
										echo json_encode(array('RntCode'=>'N','Msg'=>'點數轉移失敗'));
									}
									*/
								}
							}else{
								echo json_encode(array('RntCode'=>'N','Msg'=>'轉移金額請輸入數字'));
							}
						}else{
							echo json_encode(array('RntCode'=>'N','Msg'=>'來源與目的不得相同'));
						}
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'請檢查轉移來源目的與金額'));
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'不允許存取的方法'));
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'您的網域不被允許'));	
			}
		}
		exit;
	}
		
	
	//儲值
	public function deposit(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			//檢查會員銀行帳戶
			$sqlStr="select * from `member_bank` where `mem_num`=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
			if($row==NULL){
				$this -> session -> set_flashdata("alertMsg",'請先設定銀行帳戶');
				scriptMsg('',"Manger/account");
				exit;
			}
			
			
			$this -> load -> view("www/deposit.php", $this -> data);
			
		}
	}
	
	//儲值表單送出
	public function deposit_transfer(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			//檢查會員銀行帳戶
			$sqlStr="select * from `member_bank` where `mem_num`=?";
			$row=$this->webdb->sqlRow($sqlStr,array(':mem_num'=>$this->memberclass->num()));
			if($row==NULL){
				$this -> session -> set_flashdata("alertMsg",'請先設定銀行帳戶');
				scriptMsg('',"Manger/account");
				exit;
			}
			if(!$this->agent->is_referral()){
				if($this->input->post('amount') && $this->input->post('payment')){
					$amount=$this->input->post('amount',true);
					$mem_num=$this->data["user_info"]["num"];
					$payment=$this->input->post('payment',true);
					if(preg_match('/^[0-9]*[1-9][0-9]*$/',$amount)){
						if((int)$amount < 100){	//儲值金額限制
							$this -> session -> set_flashdata("alertMsg",'儲值金額不得小於100元');
							scriptMsg('',"Manger/deposit");
							exit;
						}
						if((int)$amount > 20000 && $payment=='CVS'){
							$this -> session -> set_flashdata("alertMsg",'超商繳費上限金額不得超過2萬');
							scriptMsg('',"Manger/deposit");
							exit;
						}
						if($this->data["pay_mode"]=='1'){	//綠界
							$this->load->library('payclass');	//載入金流
							$log=$this->payclass->load_config($this->data["user_info"]["m_group"]);	//根據會員等級設置會員所屬金流
							if($log!=NULL){	//無設定金流則返回頁面
								$this -> session -> set_flashdata("alertMsg",$log);
								scriptMsg('',"Manger/deposit");
								exit;
							}
						}elseif($this->data["pay_mode"]=='2'){//中華網通
							$this->load->library('pepay');	//載入金流
						}
						
						$this->load->library('orderclass');	//訂單函式庫
						$order_no=$this->orderclass->order_no();
						if($order_no!=NULL){
							$parameter=array();
							$colSql="order_no,mem_num,admin_num,amount,payment,pay_mode,buildtime";
							$sqlStr="INSERT INTO `orders` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':order_no']=$order_no;
							$parameter[':mem_num']=$mem_num;
							$parameter[':admin_num']=$this->data["user_info"]['agent'];
							$parameter[':amount']=$amount;
							$parameter[':payment']=$payment;
							$parameter[':pay_mode']=$this->data["pay_mode"];
							$parameter[':buildtime']=now();
							$this->webdb->sqlExc($sqlStr,$parameter);
							//取號
							if($this->data["pay_mode"]=='1'){	//綠界
								$log=$this->payclass->getFormArray($order_no,$amount,$payment);
							}elseif($this->data["pay_mode"]=='2'){//中華網通
								$log=$this->pepay->getFormArray($order_no,$amount,$payment);
							}
							if($log==NULL){
								scriptMsg("","Manger/deposit_result?order_no=".$order_no);
								exit;
							}else{
								$this -> session -> set_flashdata("alertMsg",$log);
								scriptMsg('',"Manger/deposit");
								exit;
							}
						}else{
							$this -> session -> set_flashdata("alertMsg",'訂單建立失敗‧‧請重新嘗試');
							scriptMsg('',"Manger/deposit");
							exit;
						}
					}else{
						$this -> session -> set_flashdata("alertMsg",'儲值金額請輸入數字');
						scriptMsg('',"Manger/deposit");
						exit;
					}
				}else{
					$this -> session -> set_flashdata("alertMsg",'必要參數錯誤');
					scriptMsg('',"Manger/deposit");
					exit;
				}
			}else{
				$this -> session -> set_flashdata("alertMsg",'您的網域不被允許');
				scriptMsg('',"Manger/deposit");
				exit;	
			}
		}
	}


	//儲值表單送出-銀行匯款
	public function bonus_transfer2(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			if(!$this->agent->is_referral()){
				if($this->input->post('amount') && $this->input->post('payment')){
					$amount=$this->input->post('amount',true);
					$mem_num=$this->data["user_info"]["num"];
					$payment=$this->input->post('payment',true);
					if(preg_match('/^\d+$/',$amount)){
						if((int)$amount < 100){	//儲值金額限制
							$this -> session -> set_flashdata("alertMsg",'儲值金額不得小於100元');
							scriptMsg('',"Manger/deposit");
							exit;
						}
						
						//檢查銀行帳戶
						$sqlStr="select * from `company_bank` where `active`='Y' order by rand() Limit 1";
						$rowBank=$this->webdb->sqlRow($sqlStr);
						if($rowBank==NULL){
							$this -> session -> set_flashdata("alertMsg",'尚未建立匯款帳戶，無法使用');
							scriptMsg('',"Manger/deposit");
							exit;
						}
						
						
						$this->load->library('orderclass');	//訂單函式庫
						$order_no=$this->orderclass->order_no();
						if($order_no!=NULL){
							$parameter=array();
							$colSql="order_no,mem_num,admin_num,amount,bank_name,bank_account,account_name,buildtime";
							$sqlStr="INSERT INTO `member_bank_transfer` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':order_no']=$order_no;
							$parameter[':mem_num']=$mem_num;
							$parameter[':admin_num']=$this->data["user_info"]['agent'];
							$parameter[':amount']=$amount;
							$parameter[':bank_name']=tb_sql('bank_code','bank_list',$rowBank['bank_num']).tb_sql('bank_name','bank_list',$rowBank['bank_num']);
							$parameter[':bank_account']=$rowBank['bank_account'];
							$parameter[':account_name']=$rowBank['account_name'];
							$parameter[':buildtime']=now();
							$this->webdb->sqlExc($sqlStr,$parameter);
							scriptMsg("","Manger/bonus_order2?order_no=".$order_no);
							exit;
						}else{
							$this -> session -> set_flashdata("alertMsg",'訂單建立失敗‧‧請重新嘗試');
							scriptMsg('',"Manger/deposit");
							exit;
						}
					}else{
						$this -> session -> set_flashdata("alertMsg",'儲值金額請輸入數字');
						scriptMsg('',"Manger/deposit");
						exit;
					}
				}else{
					$this -> session -> set_flashdata("alertMsg",'必要參數錯誤');
					scriptMsg('',"Manger/deposit");
					exit;
				}
			}else{
				$this -> session -> set_flashdata("alertMsg",'您的網域不被允許');
				scriptMsg('',"Index");
				exit;	
			}
		}
	}

	
	//取號結果頁面
	public function deposit_result(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			if($this->input->get('order_no')){
				$parameter=array(':order_no'=>$this->input->get('order_no',true));
				$sqlStr="select * from `orders` where `order_no`=?";
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if($row!=NULL){
					//取出繳費資訊
					if($row["pay_mode"]==3){	//紅陽
						$sqlStr2="select * from `esafe_orders` where `order_no`='".$row["order_no"]."'";
					}elseif($row["pay_mode"]==2){	//金恆通
						$sqlStr2="select * from `aer_orders` where `order_no`='".$row["order_no"]."'";	
					}else{	//綠界
						$sqlStr2="select * from `allpay_orders` where `order_no`='".$row["order_no"]."'";
					}
					$row2=$this->webdb->sqlRow($sqlStr2);
					if($row2!=NULL){
						$this->data["payInfo"]=$row2;
						$this->data["orderInfo"]=$row;
						$this -> load -> view("www/deposit_result.php", $this -> data);
						
					}else{
						$this -> session -> set_flashdata("alertMsg",'繳費資訊不存在，請重新下單');
						scriptMsg('',"Manger/deposit");
						exit;
					}
				}else{
					$this -> session -> set_flashdata("alertMsg",'此筆訂單不存在，請重新下單');
					scriptMsg('',"Manger/deposit");
					exit;
				}
			}else{
				$this -> session -> set_flashdata("alertMsg",'訂單編號空白');
				scriptMsg('',"Manger/deposit");
				exit;
			}
		}
		
	}
	//銀行匯款結果夜
	public function bonus_order2(){
		$logMsg=$this->memberclass->isLogin();
		if($logMsg!=NULL){
			$this -> session -> set_flashdata("alertMsg",$logMsg);
			scriptMsg('',"Index?rtn=".urlencode(current_url()));
			exit;
		}else{
			if($this->input->get('order_no')){
				$parameter=array(':order_no'=>$this->input->get('order_no',true));
				$sqlStr="select * from `member_bank_transfer` where `order_no`=?";
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if($row!=NULL){
					//取出繳費資訊
					$this->data["orderInfo"]=$row;
					$this -> load -> view("www/bonus_order2", $this -> data);
				}else{
					$this -> session -> set_flashdata("alertMsg",'此筆訂單不存在，請重新下單');
					scriptMsg('',"Manger/deposit");
					exit;
				}
			}else{
				$this -> session -> set_flashdata("alertMsg",'訂單編號空白');
				scriptMsg('',"Manger/deposit");
				exit;
			}
		}
		
	}

	
	
	public function ajax_balance(){	//AJAX取得遊戲廠商餘額
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$this->load->library('api/allgameapi');	//載入遊戲api
				$balance=$this->allgameapi->get_balance($this->data["user_info"]['num'],$this->input->post('makersnum',true));
				echo json_encode(array('makersnum'=>$this->input->post('makersnum',true),'balance'=>$balance));
			}
		}
	}
	
	public function refresh_token(){	//刷新驗證碼token
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$token = md5(uniqid(rand(), true));
				$this->session->set_userdata('code_token', $token);
				echo json_encode(array('token'=>$token));
			}
		}
	}
	
	//檢查推薦人帳號是否存在
	public function chkup_account($up_account){	
		if(preg_match('/^[a-zA-Z0-9]{4,12}$/',$up_account)){
			$sqlStr="select `num` from `member` where `u_id`=?";
			$parameter=array(':u_id'=>$up_account);
			$row=$this->webdb->sqlRow($sqlStr,$parameter);
			if($row!=NULL){
				return json_encode(array('RntCode'=>'Y','Msg'=>''));
			}else{
				return json_encode(array('RntCode'=>'N','Msg'=>'推薦人不存在'));
			}
		}else{
			return json_encode(array('RntCode'=>'N','Msg'=>'推薦人帳號必須是4~12碼英文或數字！'));
		}
	}
	//AJAX檢查推薦人是否存在
	public function ajax_chkupaccount(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if(preg_match('/^[a-zA-Z0-9]{4,12}$/',$this->input->post('up_account',true))){
					$sqlStr="select `num` from `member` where `u_id`=?";
					$parameter=array(':u_id'=>$this->input->post('up_account',true));
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						echo json_encode(array('RntCode'=>'Y','Msg'=>''));
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'推薦人不存在'));
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'推薦人帳號必須是4~12碼英文或數字！'));
				}
			}
		}
	}
	
	
	//檢查帳號是否可用非AJAX方法
	public function chkid($u_id){	
		if(preg_match('/^[a-zA-Z0-9]{4,10}$/',$u_id)){
			$sqlStr="select `num` from `member` where `u_id`=?";
			$parameter=array(':u_id'=>$u_id);
			$row=$this->webdb->sqlRow($sqlStr,$parameter);
			if($row==NULL){
				return json_encode(array('RntCode'=>'Y','Msg'=>''));
			}else{
				return json_encode(array('RntCode'=>'N','Msg'=>'會員帳號已存在'));
			}
		}else{
			return json_encode(array('RntCode'=>'N','Msg'=>'會員帳號必須是4~10碼英文或數字！'));
		}
	}
	
	//AJAX檢查帳號是否存在
	public function ajax_chkid(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if(preg_match('/^[a-zA-Z0-9]{4,10}$/',$this->input->post('u_id',true))){
					$sqlStr="select `num` from `member` where `u_id`=?";
					$parameter=array(':u_id'=>$this->input->post('u_id',true));
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row==NULL){
						echo json_encode(array('RntCode'=>'Y','Msg'=>''));
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'會員帳號已存在'));
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'會員帳號必須是4~10碼英文或數字！'));
				}
			}
		}
	}

	//AJAX檢查手機是否存在
	public function ajax_chkphone(){
		/*
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if(preg_match('/^09[0-9]{8}$/',$this->input->post('phone',true))){
					$sqlStr="select `num` from `member` where `phone`=?";
					$parameter=array(':phone'=>$this->input->post('phone',true));
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row==NULL){
						echo json_encode(array('RntCode'=>'Y','Msg'=>''));
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'手機號碼已被註冊'));
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'手機格式不正確'));
				}
			}
		}
		*/
		echo json_encode(array('RntCode'=>'N','Msg'=>'請清除瀏覽器暫存，再重新登入。'));
	}
	
	
	
	//檢查手機是否可用非AJAX方法
	public function chkphone($phone){
		if(preg_match('/^09[0-9]{8}$/',$phone)){
			$sqlStr="select `num` from `member` where `phone`=?";
			$parameter=array(':phone'=>$phone);
			$row=$this->webdb->sqlRow($sqlStr,$parameter);
			if($row==NULL){
				return json_encode(array('RntCode'=>'Y','Msg'=>''));
			}else{
				return json_encode(array('RntCode'=>'N','Msg'=>'手機號碼已被註冊'));
			}
		}else{
			return json_encode(array('RntCode'=>'N','Msg'=>'手機格式不正確'));
		}
	}

	//ajax發送手機驗證碼
	public function ajax_phonecode(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->session->userdata('sms_token') && $this->input->post('sms_token',true)==$this->session->userdata('sms_token')){
					if(preg_match('/^09[0-9]{8}$/',$this->input->post('phone',true))){

						$phoneLog=json_decode($this->chkphone($this->input->post('phone',true)));
						if($phoneLog->RntCode=='Y'){	//檢查手機是否已被註
	
							$this->load->library('smsclass');
							$limit_time=30;	//重複發送間隔
							$last_send_time=$this->session->userdata('last_send_time');
							if(!$this->session->userdata('last_send_time')){
								//$_SESSION['last_send_time']=strtotime("-40 second");
								$this->session->set_userdata('last_send_time',strtotime("-40 second"));
							}
							$send_time=time();	//此次發送時間
							$SMSCODE=mt_rand(1111,9999);	//驗證碼
							$subject=$this->data["com_name"]."會員認證";
							$content =$this->data["com_name"]."註冊會員驗證碼：【".$SMSCODE."】";
							if($send_time-$this->session->userdata('last_send_time') > $limit_time){
								if($this->smsclass->sendSMS($content, $this->input->post('phone',true),$subject)){							
									$this->session->set_userdata('last_send_time',$send_time);	//記錄此次發送時間
									$this->session->set_userdata('sms_code',$SMSCODE);	//記錄發送的驗證碼
									$this->session->set_userdata('sms_phone',$this->input->post('phone',true));	//發送的手機
									echo json_encode(array('RntCode'=>'Y','Msg'=>''));
								}else{
									echo json_encode(array('RntCode'=>'N','Msg'=>'簡訊發送失敗，請聯絡網管人員'));
								}
							}else{
								$time_wait=$send_time-$this->session->userdata('last_send_time');
								echo json_encode(array('RntCode'=>'W','last_send_time'=>$time_wait,'Msg'=>'發送次數太頻繁~請稍候在發送'));
							}
						} else {//phoneLog =="N"//檢查手機是否已被註
							echo json_encode(array('RntCode'=>'N','Msg'=>'手機號碼已被註冊'));	
						}
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'手機格式不正確'));
					}
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'Access Token Error'));
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'您的網域不被允許'));
		}
	}
		
} 