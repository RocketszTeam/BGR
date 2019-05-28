<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(dirname(__FILE__))."/Core_controller.php");
class Aerresult extends Core_controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('aerclass');	
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->input->get();
			if (sizeof($arFeedback) > 0) {
				if($arFeedback["Status"]=='0000'){  //繳費成功
					//檢查訂單是否存在以及金額是否正確
					$sqlStr="select * from `orders` where `order_no`=? and amount=?";
					$parameter=array(':order_no'=>$arFeedback["Ordernum"],':amount'=>$arFeedback["Total"]);
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						//檢察回傳繳費編號 是否跟當初取號的編號一致
						$parameter=array();
						if(isset($arFeedback["ACTCode"])){	//ATM
							$sqlStr2="select order_no from `aer_orders` where `ACID`=? and `order_no`='".$row["order_no"]."'";
							$parameter[':ACID']=$arFeedback["ACTCode"];
						}else{	//CVS
							$sqlStr2="select order_no from `aer_orders` where `StoreCode`=? and `order_no`='".$row["order_no"]."'";
							$parameter[':StoreCode']=$arFeedback["StoreCode"];
						}
						$rowCheck=$this->webdb->sqlRow($sqlStr2,$parameter);
						if($rowCheck!=NULL){
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
							$upSql="UPDATE `orders` SET `PayFrom`=? where order_no='".$row["order_no"]."'";
							$parameter[':PayFrom']=isset($arFeedback["Store"]) ? $arFeedback["Store"] : NULL;
							$this->webdb->sqlExc($upSql,$parameter);
							print '1|OK';
						}else{
							print '0|payinfo not error';	
						}
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
