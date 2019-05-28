<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Checkpay extends CI_Controller {
	function __construct(){
		parent::__construct(); 
		date_default_timezone_set("Asia/Taipei");
	}
	
	
	public function index() {
		try{
			
			$MerchantID=3030677;		
			$HashKey=tb_sql2('HashKey','allpay','MerchantID',$MerchantID);
			$HashIV=tb_sql2('HashIV','allpay','MerchantID',$MerchantID);
			$data=array('MerchantID'=>$MerchantID,'MerchantTradeNo'=>'AH150349966423114','TimeStamp'=>time());
						
			$parameter="&".http_build_query($data);//'&MerchantID='.$MerchantID.'&MerchantTradeNo=AH150349966423114&TimeStamp='.time();
			$szCheckMacValue=strtolower(urlencode('HashKey='.$HashKey.$parameter.'&HashIV='.$HashIV));
            // 取代為與 dotNet 相符的字元
            $szCheckMacValue = str_replace('%2d', '-', $szCheckMacValue);
            $szCheckMacValue = str_replace('%5f', '_', $szCheckMacValue);
            $szCheckMacValue = str_replace('%2e', '.', $szCheckMacValue);
            $szCheckMacValue = str_replace('%21', '!', $szCheckMacValue);
            $szCheckMacValue = str_replace('%2a', '*', $szCheckMacValue);
            $szCheckMacValue = str_replace('%28', '(', $szCheckMacValue);
            $szCheckMacValue = str_replace('%29', ')', $szCheckMacValue);
            // Customize for Magento
            $szCheckMacValue = str_replace('%3f___sid%3d' . session_id(), '', $szCheckMacValue);
            $szCheckMacValue = str_replace('%3f___sid%3du', '', $szCheckMacValue);
            $szCheckMacValue = str_replace('%3f___sid%3ds', '', $szCheckMacValue);
			
			$szCheckMacValue = strtoupper(hash('sha256', $szCheckMacValue));
			
			$post_data=array_merge($data,array('CheckMacValue'=>$szCheckMacValue));
			//print_r($post_data);
			//echo $szCheckMacValue;
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,'https://payment.ecpay.com.tw/Cashier/QueryTradeInfo/V5');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errno = curl_errno($ch);
			curl_close($ch);	
			parse_str($output,$output);	//轉回陣列
			print_r($output);
			
			$arErrors = array();
			$arParameters = array();
			$arFeedback = array();
			$szCheckMacValue = '';
			
			// 重新整理回傳參數。
			foreach ($output as $keys => $value) {
				if ($keys != 'CheckMacValue') {
					$arFeedback[$keys] = $value;
				} else {
					$szCheckMacValue = $value;
				}
			}
			// 回傳參數鍵值轉小寫。
			foreach ($output as $keys => $value) {
				$arParameters[strtolower($keys)] = $value;
			}
			unset($arParameters['checkmacvalue']);
			ksort($arParameters);
			if (sizeof($arFeedback) > 0) {
				$szConfirmMacValue = "HashKey=$HashKey";
				foreach ($arParameters as $key => $value) {
					$szConfirmMacValue .= "&$key=$value";
				}
				$szConfirmMacValue .= "&HashIV=$HashIV";
				$szConfirmMacValue = strtolower(urlencode($szConfirmMacValue));
				// 取代為與 dotNet 相符的字元
				$szConfirmMacValue = str_replace('%2d', '-', $szConfirmMacValue);
				$szConfirmMacValue = str_replace('%5f', '_', $szConfirmMacValue);
				$szConfirmMacValue = str_replace('%2e', '.', $szConfirmMacValue);
				$szConfirmMacValue = str_replace('%21', '!', $szConfirmMacValue);
				$szConfirmMacValue = str_replace('%2a', '*', $szConfirmMacValue);
				$szConfirmMacValue = str_replace('%28', '(', $szConfirmMacValue);
				$szConfirmMacValue = str_replace('%29', ')', $szConfirmMacValue);
				// CheckMacValue 壓碼
				$szConfirmMacValue = hash('sha256', $szConfirmMacValue);
				if ($szCheckMacValue != strtoupper($szConfirmMacValue)) {
					array_push($arErrors, 'CheckMacValue verify fail.');
				}
				if (sizeof($arErrors) > 0) {
					throw new Exception(json_encode('- ', $arErrors));
				}
				
			}
		}catch (Exception $e){
			echo '0|' . $e->getMessage();
		}
	}
	
	
	
	
	public function ajaxCheck(){
		
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('order_no')){
					$this->db->where('order_no',$this->input->post('order_no'),true);
					$query=$this->db->get('orders');
					$row=$query->row_array();
					if($row!=NULL){
						$MerchantID=tb_sql2('MerchantID','allpay_orders','order_no',$this->input->post('order_no',true));
						$HashKey=tb_sql2('HashKey','allpay','MerchantID',$MerchantID);
						$HashIV=tb_sql2('HashIV','allpay','MerchantID',$MerchantID);
						if($MerchantID!=NULL && $HashKey!=NULL && $HashIV!=NULL ){
							$data=array('MerchantID'=>$MerchantID,'MerchantTradeNo'=>$row['order_no'],'TimeStamp'=>time());
							$result=$this->server_post($data,$HashKey,$HashIV);
							if($this->checkData($result,$HashKey,$HashIV)){	//檢查數據封包
								if($result["TradeStatus"]==1){
									if($result["TradeAmt"]==$row["amount"]){
										if($row["is_received"]==0){
											//金流目前累積金額
											$parameter=array(':MerchantID'=>$result['MerchantID']);
											$sqlStr="select `total` from `allpay` where `MerchantID`=?";
											$row_pay=$this->webdb->sqlRow($sqlStr,$parameter);
											
											//更新此金流交易金額
											$parameter=array(':total'=>$result["TradeAmt"]+$row_pay["total"],':MerchantID'=>$result['MerchantID']);
											$upSql="UPDATE `allpay` SET `total`=?  where `MerchantID`=?";
											$this->webdb->sqlExc($upSql,$parameter);
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
											//更新繳費者資訊
											$parameter=array();
											$upSql="UPDATE `orders` SET `ATMAccBank`=?,`ATMAccNo`=?,`PayFrom`=? where order_no='".$row["order_no"]."'";
											$parameter[':ATMAccBank']=isset($result["ATMAccBank"]) ? $result["ATMAccBank"] : NULL;
											$parameter[':ATMAccNo']=isset($result["ATMAccNo"]) ? $result["ATMAccNo"] : NULL;
											$parameter[':PayFrom']=isset($result["PayFrom"]) ? $result["PayFrom"] : NULL;
											$this->webdb->sqlExc($upSql,$parameter);
											
											echo json_encode(array('RntCode'=>'Y','Msg'=>'點數已經補發到會員帳戶內！'));	
										}else{
											echo json_encode(array('RntCode'=>'N','Msg'=>'此單據已完成繳費，無須補點！'));	
										}
									}else{
										echo json_encode(array('RntCode'=>'N','Msg'=>'繳費金額與訂單金額不相同！'));
									}
								}else{
									echo json_encode(array('RntCode'=>'N','Msg'=>'此單據尚未完成繳費！'));		
								}
							}else{
								echo json_encode(array('RntCode'=>'N','Msg'=>'金流封包數據錯誤！'));
							}
						}else{
							echo json_encode(array('RntCode'=>'N','Msg'=>'金流參數讀取錯誤！'));	
						}
					}else{
						echo json_encode(array('RntCode'=>'N','Msg'=>'訂單資訊不存在'));	
					}
					
				}else{
					echo json_encode(array('RntCode'=>'N','Msg'=>'必要參數錯誤'));	
				}
			}else{
				echo json_encode(array('RntCode'=>'N','Msg'=>'不允許的存取方法'));
			}
		}else{
			echo json_encode(array('RntCode'=>'N','Msg'=>'禁止跨域存取'));
		}
	}
	
	private function server_post($data,$HashKey,$HashIV){
			$parameter="&".http_build_query($data);
			$szCheckMacValue=strtolower(urlencode('HashKey='.$HashKey.$parameter.'&HashIV='.$HashIV));
            // 取代為與 dotNet 相符的字元
            $szCheckMacValue = str_replace('%2d', '-', $szCheckMacValue);
            $szCheckMacValue = str_replace('%5f', '_', $szCheckMacValue);
            $szCheckMacValue = str_replace('%2e', '.', $szCheckMacValue);
            $szCheckMacValue = str_replace('%21', '!', $szCheckMacValue);
            $szCheckMacValue = str_replace('%2a', '*', $szCheckMacValue);
            $szCheckMacValue = str_replace('%28', '(', $szCheckMacValue);
            $szCheckMacValue = str_replace('%29', ')', $szCheckMacValue);
            // Customize for Magento
            $szCheckMacValue = str_replace('%3f___sid%3d' . session_id(), '', $szCheckMacValue);
            $szCheckMacValue = str_replace('%3f___sid%3du', '', $szCheckMacValue);
            $szCheckMacValue = str_replace('%3f___sid%3ds', '', $szCheckMacValue);
			$szCheckMacValue = strtoupper(hash('sha256', $szCheckMacValue));
			$post_data=array_merge($data,array('CheckMacValue'=>$szCheckMacValue));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,'https://payment.ecpay.com.tw/Cashier/QueryTradeInfo/V5');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);  //Post Fields
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errno = curl_errno($ch);
			curl_close($ch);	
			parse_str($output,$output);	//轉回陣列
			//print_r($output);
			return $output;
	}
	
	private function checkData($data,$HashKey,$HashIV){
		$arErrors = array();
		$arParameters = array();
		$arFeedback = array();
		$szCheckMacValue = '';
		// 重新整理回傳參數。
		foreach ($data as $keys => $value) {
			if ($keys != 'CheckMacValue') {
				$arFeedback[$keys] = $value;
			} else {
				$szCheckMacValue = $value;
			}
		}
		// 回傳參數鍵值轉小寫。
		foreach ($data as $keys => $value) {
			$arParameters[strtolower($keys)] = $value;
		}
		unset($arParameters['checkmacvalue']);
		ksort($arParameters);
		if (sizeof($arFeedback) > 0) {
			$szConfirmMacValue = "HashKey=$HashKey";
			foreach ($arParameters as $key => $value) {
				$szConfirmMacValue .= "&$key=$value";
			}
			$szConfirmMacValue .= "&HashIV=$HashIV";
			$szConfirmMacValue = strtolower(urlencode($szConfirmMacValue));
			// 取代為與 dotNet 相符的字元
			$szConfirmMacValue = str_replace('%2d', '-', $szConfirmMacValue);
			$szConfirmMacValue = str_replace('%5f', '_', $szConfirmMacValue);
			$szConfirmMacValue = str_replace('%2e', '.', $szConfirmMacValue);
			$szConfirmMacValue = str_replace('%21', '!', $szConfirmMacValue);
			$szConfirmMacValue = str_replace('%2a', '*', $szConfirmMacValue);
			$szConfirmMacValue = str_replace('%28', '(', $szConfirmMacValue);
			$szConfirmMacValue = str_replace('%29', ')', $szConfirmMacValue);
			// CheckMacValue 壓碼
			$szConfirmMacValue = hash('sha256', $szConfirmMacValue);
			if ($szCheckMacValue != strtoupper($szConfirmMacValue)) {
				return false;
			}else{
				return true;	
			}
		}else{
			return false;	
		}
	}
		
}
