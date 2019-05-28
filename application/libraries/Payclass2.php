<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Payclass2 {
	var $CI;
	//var $onLine=1;	//1=正式環境;0=測試環境
	var $merchantnumber;	//商店編號
	var $code;	//交易密碼
	var $ServiceURL='https://aquarius.ezpay.com.tw/CashSystemFrontEnd/Payment';
	public function __construct(){		
		$this->CI=&get_instance();
		//$this->CI->load -> model('sysAdmin/webdb_model',"webdb",true);
	}
	
	public function load_config($m_group){
		$sqlStr="select * from `neweb` where `m_group`=? and `active`='Y' order by rand() Limit 1";
		$parameter=array(':m_group'=>$m_group);
		$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
		if($row!=NULL){
			$this->merchantnumber=$row["merchantnumber"];
			$this->code=$row["code"];
		}else{
			return '付款方式尚未設定';	
		}
	}
	
	public function getFormArray($order_no,$Amount,$Paymenttype='ATM',$target='_self'){
		date_default_timezone_set("Asia/Taipei");
		$HostUrl=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$HostUrl.=$_SERVER['HTTP_HOST'];
		
		$duedate=date('Ymd',strtotime("+1 day"));//繳費期限 不帶入 設定2天
		
		$form=array();
		$form['merchantnumber']=$this->merchantnumber;	//商店代碼
		$form['ordernumber']=$order_no;	//訂單編號
		$form['amount']=$Amount;	//訂單金額
		$form['Paymenttype']=$Paymenttype;	//付款方式
		$form['paytitle']=urlencode('點數購買');	//付款說明
		$form['Paymemo']=urlencode('點數購買');	//備註
		$form['bankid']=($Paymenttype=='ATM' ? '007' : "");	//銀行代碼 當付款方式為ATM帶入
		$form['duedate']=$duedate;	//繳費期限 不帶入 設定2天
		$form['payname']='';	//繳款人姓名
		$form['payphone']='';	//繳款人電話
		$form['returnvalue']=1;	//若指定為”1”時，會將該支付工具必要的資訊直接回
		$form['mobile']=0;	//若指定為”1”時，交易畫面會手機網頁呈現；
		$form['nexturl']=$HostUrl.'/Manger/deposit_result?order_no='.$order_no	;//回商店網頁的網址 
		$form['hash']=md5($this->merchantnumber.$this->code.$Amount.$order_no);
		
		//$ch = curl_init();
		$ch = curl_init($this->ServiceURL);
		//curl_setopt($ch, CURLOPT_URL,$this->ServiceURL);		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($form));  //Post Fields
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		$output = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($http_code===200 && $output!=''){
			$resultData=array();
			$returnData=explode('&',trim($output));
			foreach($returnData as $value){
				$dataArr=explode('=',$value);
				$resultData[$dataArr[0]]=$dataArr[1];
			}
			if($resultData['rc']==0){
				if($Paymenttype=='ATM'){
					$hash='rc='.$resultData['rc'].'&bankid='.$resultData['bankid'].'&virtualaccount='.$resultData['virtualaccount'];
					$hash.='&amount='.$resultData['amount'].'&merchantnumber='.$resultData['merchantnumber'].'&ordernumber='.$resultData['ordernumber'];
					$hash.='&code='.$this->code;	
				}else{
					$hash='rc='.$resultData['rc'].'&amount='.$resultData['amount'].'&merchantnumber='.$resultData['merchantnumber'];
					$hash.='&ordernumber='.$resultData['ordernumber'].'&paycode='.$resultData['paycode'];
					$hash.='&code='.$this->code;	
				}
				$checksum=md5($hash);
				if($checksum==$resultData['checksum']){
					//將取號資訊寫入DB
					$parameter=array();
					$colSql="order_no,rc,payment,merchantnumber,amount,paycode";
					$colSql.=",bankid,virtualaccount,duedate,checksum";
					$sqlStr="INSERT INTO `neweb_orders` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':order_no']=($resultData['ordernumber']!="" ? $resultData['ordernumber'] : NULL);
					$parameter[':rc']=($resultData['rc']!="" ? $resultData['rc'] : NULL);
					$parameter[':Paymenttype']=$Paymenttype;
					$parameter[':merchantnumber']=($resultData['merchantnumber']!="" ? $resultData['merchantnumber'] : NULL);
					$parameter[':amount']=($resultData['amount']!="" ? $resultData['amount'] : NULL);
					$parameter[':paycode']=(@$resultData['paycode']!="" ? @$resultData['paycode'] : NULL);
					$parameter[':bankid']=(@$resultData['bankid']!="" ? @$resultData['bankid'] : NULL);
					$parameter[':virtualaccount']=(@$resultData['virtualaccount']!="" ? @$resultData['virtualaccount'] : NULL);
					$parameter[':duedate']=$duedate;
					$parameter[':checksum']=($resultData['checksum']!="" ? $resultData['checksum'] : NULL);
					$this->CI->webdb->sqlExc($sqlStr,$parameter);			
					return NULL;
				}else{
					return '取號錯誤，請重新取號';	
				}
			}else{
				return '取號失敗，錯誤代碼：'.$resultData['rc'];
			}
		}else{
			return '系統異常，請稍候嘗試';	
		}
		
		/*
		$szHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$szHtml .= '<div style="text-align:center;" ><form id="__allpayForm" method="post" target="' . $target . '" action="' . $this->ServiceURL . '">';
		foreach ($form as $keys => $value) {
			$szHtml .="<input type='hidden' name='$keys' value='$value' />";
		}
		$szHtml .= '<script type="text/javascript">document.getElementById("__allpayForm").submit();</script>';
		$szHtml .= '</form></div>';
		print $szHtml;
		*/
	}
	
	public function CheckOutFeedback(){
		$arErrors = array();
		$arFeedback=array();		
		foreach ($_POST as $keys => $value) {
			$arFeedback[$keys]=$value;	
		}
		if (sizeof($arFeedback) > 0) {
			$parameter=array(':merchantnumber'=>$arFeedback['merchantnumber']);
			$sqlStr="select * from `neweb` where `merchantnumber`=? and `active`='Y'";
			$row=$this->CI->webdb->sqlRow($sqlStr,$parameter);
			if($row!=NULL){
				$this->code=$row["code"];
				$hash='merchantnumber='.$arFeedback['merchantnumber'].'&ordernumber='.$arFeedback['ordernumber'].'&serialnumber='.$arFeedback['serialnumber'];
				$hash.='&writeoffnumber='.$arFeedback['writeoffnumber'].'&timepaid='.$arFeedback['timepaid'].'&paymenttype='.$arFeedback['paymenttype'];
				$hash.='&amount='.$arFeedback['amount'].'&tel='.$arFeedback['tel'];	
				$CheckMacValue=md5($hash.$this->code);			
				if(strtoupper($CheckMacValue)!=strtoupper($arFeedback['hash'])){
					 array_push($arErrors, 'hash verify fail.');
				}else{
					$upSql="UPDATE `neweb` SET `total`=? where num=".$row["num"];
					$this->CI->webdb->sqlExc($upSql,array(':total'=>($row["total"] + $arFeedback['amount'])));
				}
			}else{
				 array_push($arErrors, 'Pay type Error');
			}
		}
		if (sizeof($arErrors) > 0) {
			throw new Exception(join('- ', $arErrors));
		}
			
        return $arFeedback;		
	}
	
}
?>