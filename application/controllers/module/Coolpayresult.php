<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(dirname(__FILE__))."/Core_controller.php");
class Coolpayresult extends Core_controller {
	function __construct(){
		parent::__construct(); 
		
		$this->load->library('coolpaypal');	
		
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			$arFeedback = $this->coolpaypal->CheckOutFeedback();
			if (sizeof($arFeedback) > 0) {
				if($arFeedback["RtnCode"]==1){  //成功
				
					//檢查訂單是否存在以及金額是否正確
					$sqlStr="select * from `orders` where `order_no`=? and amount=?";
					$parameter=array(':order_no'=>$arFeedback["MerTradeID"],':amount'=>$arFeedback["Amount"]);
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						$fee=0;
						$Amount=$row["amount"];
						if($row["payment"]=='Credit'){	//信用卡金額需要扣掉一層手續費
							$Amount=$row["amount"] - round($row["amount"] * 0.1);
							$fee=round($row["amount"] * 0.1,0);
						}
						
						if($row["is_received"]==0){	//尚未撥款才發放
							$WalletTotal=getWalletTotal($row["mem_num"]);	//會員餘額
							$before_balance=(float)$WalletTotal;//異動前點數
							$after_balance= (float)$before_balance + (float)$Amount;//異動後點數
							//發放點數
							$parameter=array();
							$colSql="mem_num,kind,points,order_no,admin_num,buildtime,before_balance,after_balance";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[":mem_num"]=$row["mem_num"];
							$parameter[":kind"]=5;	//會員儲值
							$parameter[":points"]=$Amount;
							$parameter[":order_no"]=$row["order_no"];
							$parameter[':admin_num']=tb_sql("admin_num","member",$row["mem_num"]);
							$parameter[":buildtime"]=now();
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$this->webdb->sqlExc($sqlStr,$parameter);
							
							//更新訂單狀態以及發送情形
							$upSql="UPDATE `orders` SET `is_received`=1,`keyin2`=1,fee=".$fee;
							if($row["payment"]=='Credit'){	//變更訂單金額為 實際取得點數的金額
								//$upSql.=",amount=".	$Amount;
							}
							$upSql.=" where order_no='".$row["order_no"]."'";
							$this->webdb->sqlExc($upSql);
						}
						print '1|OK';
					}else{
						print '0|orders not found';	
					}
				}
			}else{
				print '0|Not Post Params';		
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
	
		
}
