<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pepay{
	var $CI;
	var $paymentURL='https://gate.pepay.com.tw/pepay/payselect_amt.php';
	var $SHOP_ID='PPS_165134';	//廠商代碼
	var $CURRENCY='TWD';	//幣別
	var $SYS_TRUST_CODE='hstJf0vuAj';		 //系統信任碼
	var $SHOP_TRUST_CODE='Bo33mWQtMk';	//廠商信任碼
	
	public function __construct(){		
		$this->CI =&get_instance();
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function getFormArray($order_no,$Amount,$Payment='ATM',$target="_self",$paymentButton=NULL){
		$data=array();
		$data['SHOP_ID']=$this->SHOP_ID;
		$data['ORDER_ID']=$order_no;
		$data['ORDER_ITEM']=urlencode('商城商品');
		$data['AMOUNT']=$Amount;
		$data['CURRENCY']=$this->CURRENCY;
		//$data['PAY_TYPE']='TY-ATM';
		if($Payment=='ATM'){
			$data['PROD_ID']='PD-ATM-CTCB';
		}else{
			switch($Payment){
				case 'FAMI':	//全家
					$data['PROD_ID']='PD-STORE-FAMI';
					break;
				case 'HILIFEET':	//萊爾富
					$data['PROD_ID']='PD-STORE-HILIFEET';
					break;
				case 'IBON':	//7-11
					$data['PROD_ID']='PD-STORE-IBON';
					break;
			}
			
		}
		$data['SHOP_PARA']='';
		$data['CHECK_CODE']=$this->getOrderCode($order_no,$Amount);
		$szHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$szHtml .= '<form id="__PepayForm" method="post" target="' . $target . '"  action="' . $this->paymentURL . '">';
		foreach ($data as $keys => $value) {
			$szHtml .="<input type='hidden' name='$keys' value='$value' />";
		}
		// 手動或自動送出表單。
		if (!isset($paymentButton)) {
			$szHtml .= '<script type="text/javascript">document.getElementById("__PepayForm").submit();</script>';
		} else {
			$szHtml .= '<input type="submit" id="__paymentButton" value="' . $paymentButton . '" />';
		}
		$szHtml .= '</form>';
        print $szHtml;
        exit();
        die();
        flush();
        return;
	}
	
	public function CheckR1Feedback(){	//檢查R1
		$nRes=0;
        $arFeedback = $_POST;
		$cTmp=$this->SYS_TRUST_CODE."#".$this->SHOP_ID."#".$arFeedback['ORDER_ID']."#".$arFeedback['AMOUNT']."#".$arFeedback['SESS_ID']."#".$arFeedback['PROD_ID']."#".$this->SHOP_TRUST_CODE;
		$cTrustCode=md5($cTmp);
		if($arFeedback['CHECK_CODE'] != $cTrustCode){
			$nRes=20002;
		}
		//核查訂單編號與訂單金額是否正確
		$parameter=array();
		$sqlStr="select mem_num from `orders` where `order_no`=? and `amount`=?";
		$parameter['order_no']=$arFeedback['ORDER_ID'];
		$parameter['amount']=$arFeedback['AMOUNT'];
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row==NULL){
			$nRes=20099;
		}
		$cOutput="&";
		$cOutput.="USER_ID=".$row["mem_num"]."&RES_CODE=".$nRes;
		echo $cOutput;
	}
	
	
	public function getOrderCode($order_no,$Amount){
		$CHECK_CODE=$this->SYS_TRUST_CODE.'#'.$this->SHOP_ID.'#'.$order_no.'#'.$Amount.'#'.$this->SHOP_TRUST_CODE;
		return md5($CHECK_CODE);
	}
	
	public function CheckR2Feedback(){	//檢查R2
		$nRes=0;
		$arFeedback = $_GET;
		$cTmp=$this->SYS_TRUST_CODE."#".$this->SHOP_ID."#".$arFeedback['ORDER_ID']."#".$arFeedback['AMOUNT']."#".$arFeedback['SESS_ID']."#".$arFeedback['PROD_ID']."#".$arFeedback['USER_ID']."#".$this->SHOP_TRUST_CODE;
		$cTrustCode=md5($cTmp);
		if($arFeedback['CHECK_CODE'] != $cTrustCode){
			$nRes=20002;
		}else{
			//DATA_CODE 說明  0:一般交易;1001:帳單重複儲值交易;9999:由後台介接測試所產生的測試交易
			if($arFeedback['TRADE_CODE']==0 &&  $arFeedback['DATA_CODE']==0){	
				//檢查訂單是否存在以及開單金額是否正確 非檢查實際繳款金額
				$sqlStr="select * from `orders` where `order_no`=? and amount=?";
				$parameter=array(':order_no'=>$arFeedback["ORDER_ID"],':amount'=>$arFeedback["SOURCE_AMOUNT"]);
				$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
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
						$this->CI->webdb->sqlExc($sqlStr,$parameter);
						
						//更新訂單狀態以及發送情形
						$upSql="UPDATE `orders` SET `is_received`=1,`keyin2`=1 where order_no='".$row["order_no"]."'";
						$this->CI->webdb->sqlExc($upSql);
					}else{	//已領過款
						$nRes=20290;
					}
				}else{	//訂單不存在 或者 與開單金額不相符
					$nRes=20299;
				}
			}else{
				$nRes=20299;
			}
		}
		$cOutput="RES_CODE=".$nRes;
		echo $cOutput;
	}
	
	
}

?>