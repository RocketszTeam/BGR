<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Payresult extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		$this->load->library('pchomeclass');	
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			//$myfile = fopen("/var/www/html/application/controllers/module/pchome/".time().".txt", "w") or die("Unable to open file!");
			$i = 0;
			//$arFeedback = array();
			$arF = array();
			foreach ($_POST as $keys => $value) {
				$arFeedback[$i]=$value;	
				$txt = "'".$keys."':".$value."\n";
				if(trim($keys) == "notify_message"){
					$arF = json_decode($value);		
				}				
				if(trim($keys) == "notify_type" && trim($value) == "order_confirm"){
					$i = 1;	
				}

				//print_r($_POST);
				//fwrite($myfile, $txt);

			}		

			//print_r($arF);
			//fclose($myfile);
			
			if( !empty($arF) ) {
				//echo $i."|".$arF->status;
				if( $i == 1 && trim($arF->status) == "S"){  //訂單確認
					//檢查訂單是否存在以及金額是否正確
					$sqlStr="select * from `orders` where `order_no`=?";
					$parameter=array(':order_no'=>trim($arF->order_id));
					
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
						
						//$parameter=array();
						//$upSql="UPDATE `orders` SET `ATMAccBank`=?,`ATMAccNo`=?,`PayFrom`=? where order_no='".$row["order_no"]."'";
						//$parameter[':ATMAccBank']=isset($arFeedback["PayBankCode"]) ? $arFeedback["PayBankCode"] : NULL;
						//$parameter[':ATMAccNo']=isset($arFeedback["PayerAccount5Code"]) ? $arFeedback["PayerAccount5Code"] : NULL;
						//$parameter[':PayFrom']=isset($arFeedback["StoreType"]) ? $arFeedback["StoreType"] : NULL;
						//$this->webdb->sqlExc($upSql,$parameter);
						
						
						//$parameter=array();
						//$upSql="UPDATE `gp_orders` SET `Status`=?,`PayAmount`=?,`PayDate`=? where order_no='".$row["order_no"]."'";
						//$parameter[':Status']=isset($arFeedback["Status"]) ? $arFeedback["Status"] : NULL;
						//$parameter[':PayAmount']=isset($arFeedback["PayAmount"]) ? $arFeedback["PayAmount"] : NULL;
						//$parameter[':PayDate']=isset($arFeedback["PayDate"]) ? $arFeedback["PayDate"] : NULL;
						//$this->webdb->sqlExc($upSql,$parameter);						
						
						
						print '1|OK';//'';
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
