<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Payresult extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('spclass');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->spclass->CheckOutFeedback();
			$TradeInfo = $this->spclass->create_aes_decrypt($arFeedback['TradeInfo']);	//資料解密後轉陣列
			parse_str($TradeInfo, $arFeedback);
			if (sizeof($arFeedback) > 0) {
				if($arFeedback['Status']=='SUCCESS'){  //
					//檢查訂單是否存在以及金額是否正確
					$sqlStr="select * from `orders` where `order_no`=? and amount=?";
					$parameter=array(':order_no'=>$arFeedback["MerchantOrderNo"],':amount'=>$arFeedback["Amt"]);
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						if($row["is_received"]==0){	//尚未撥款才發放
							$WalletTotal=getWalletTotal($row["mem_num"]);	//會員餘額
							$before_balance=(float)$WalletTotal;//異動前點數
							$after_balance= (float)$before_balance + (float)$row["amount"];//異動後點數
						
						
							//發放點數
							$parameter=array();
							$colSql="mem_num,kind,points,order_no,admin_num,buildtime,before_balance,after_balance";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[":mem_num"]=$row["mem_num"];
							$parameter[":kind"]=5;	//會員儲值
							$parameter[":points"]=$row["amount"];
							$parameter[":order_no"]=$row["order_no"];
							$parameter[':admin_num']=tb_sql("admin_num","member",$row["mem_num"]);
							$parameter[":buildtime"]=now();
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$this->webdb->sqlExc($sqlStr,$parameter);
							
							//更新訂單狀態以及發送情形
							$upSql="UPDATE `orders` SET `is_received`=1,`keyin2`=1 where order_no='".$row["order_no"]."'";
							$this->webdb->sqlExc($upSql);
						}
						
						//更新繳費者資訊
						
						$parameter=array();
						$upSql="UPDATE `orders` SET `ATMAccBank`=?,`ATMAccNo`=?,`PayFrom`=? where order_no='".$row["order_no"]."'";
						$parameter[':ATMAccBank']=isset($arFeedback["PayBankCode"]) ? $arFeedback["PayBankCode"] : NULL;
						$parameter[':ATMAccNo']=isset($arFeedback["PayerAccount5Code"]) ? $arFeedback["PayerAccount5Code"] : NULL;
						$parameter[':PayFrom']=isset($arFeedback["StoreType"]) ? $arFeedback["StoreType"] : NULL;
						$this->webdb->sqlExc($upSql,$parameter);
						
						print '1|OK';
					}else{
						print '0|orders not found';	
					}
				}
			}else{
				print '0|Fail';		
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
	
		
}
