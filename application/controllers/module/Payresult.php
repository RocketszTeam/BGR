<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(dirname(__FILE__))."/Core_controller.php");
class Payresult extends Core_controller {
	function __construct(){
		parent::__construct(); 
		
		$this->load->library('payclass');	
		$this->load->library('payclass2');
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->payclass->CheckOutFeedback();
			if (sizeof($arFeedback) > 0) {
				if($arFeedback["RtnCode"]==1 && $arFeedback["SimulatePaid"]==0){  //
				
					//金流金額
					$parameter=array(':MerchantID'=>$arFeedback['MerchantID']);
					$sqlStr="select * from `allpay` where `MerchantID`=?";
					$row_pay=$this->webdb->sqlRow($sqlStr,$parameter);
				
					//檢查訂單是否存在以及金額是否正確
					$sqlStr="select * from `orders` where `order_no`=? and amount=?";
					$parameter=array(':order_no'=>$arFeedback["MerchantTradeNo"],':amount'=>$arFeedback["TradeAmt"]);
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						if($row["is_received"]==0){	//尚未撥款才發放
						
							//更新此金流交易金額
							$parameter=array(':total'=>$arFeedback["TradeAmt"]+$row_pay["total"]);
							$upSql="UPDATE `allpay` SET `total`=?  where `MerchantID`=".$arFeedback['MerchantID'];
							$this->webdb->sqlExc($upSql,$parameter);
							
							$WalletTotal=getWalletTotal($row["mem_num"]);	//會員餘額
							//異動前點數
							$before_balance=(float)$WalletTotal;
							//異動後點數
							$after_balance= (float)$before_balance + (float)$row["amount"];
						
						
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
						$parameter[':ATMAccBank']=isset($arFeedback["ATMAccBank"]) ? $arFeedback["ATMAccBank"] : NULL;
						$parameter[':ATMAccNo']=isset($arFeedback["ATMAccNo"]) ? $arFeedback["ATMAccNo"] : NULL;
						$parameter[':PayFrom']=isset($arFeedback["PayFrom"]) ? $arFeedback["PayFrom"] : NULL;
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
	
	public function ezpay_result(){
		try{ 
			$arFeedback = $this->payclass2->CheckOutFeedback();
			if (sizeof($arFeedback) > 0) {
				//檢查訂單是否存在以及金額是否正確
				$sqlStr="select * from `orders` where `order_no`=? and amount=?";
				$parameter=array(':order_no'=>$arFeedback["ordernumber"],':amount'=>$arFeedback["amount"]);
				$row=$this->webdb->sqlRow($sqlStr,$parameter);
				if($row!=NULL){
					if($row["is_received"]==0){	//尚未撥款才發放
						//發放點數
						$parameter=array();
						$colSql="mem_num,kind,add_point,order_no,payment,buildtime";
						$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
						$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
						$parameter[":mem_num"]=$row["mem_num"];
						$parameter[":kind"]=5;	//會員儲值
						$parameter[":add_point"]=$row["amount"];
						$parameter[":order_no"]=$row["order_no"];
						$parameter[":payment"]=$row["payment"];
						$parameter[":buildtime"]=now();
						$this->webdb->sqlExc($sqlStr,$parameter);
						
						//更新訂單狀態以及發送情形
						$upSql="UPDATE `orders` SET `is_received`=1,`keyin2`=1 where order_no='".$row["order_no"]."'";
						$this->webdb->sqlExc($upSql);

					}
					print '1|OK';
				}else{
					print '0|orders not found';	
				}
			}else{
				print '0|Fail';	
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
		
	}
		
}
