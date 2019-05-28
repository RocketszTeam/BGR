<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Payresult extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('esafe');	
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->esafe->CheckOutFeedback();
			if (sizeof($arFeedback) > 0) {
				if($arFeedback["errcode"]=='00'){  //
					//檢查訂單是否存在以及金額是否正確
					$sqlStr="select * from `orders` where `order_no`=? and amount=?";
					$parameter=array(':order_no'=>$arFeedback["Td"],':amount'=>$arFeedback["MN"]);
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
						$upSql="UPDATE `orders` SET `PayFrom`=? where order_no='".$row["order_no"]."'";
						$parameter[':PayFrom']=isset($arFeedback["PayType"]) ? $arFeedback["PayType"] : NULL;
						$this->webdb->sqlExc($upSql,$parameter);
						
						print '0000';
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
