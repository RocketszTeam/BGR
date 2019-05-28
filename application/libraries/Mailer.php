<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Mailer {
	var $mail;	
	public function __construct(){			
		require_once('PHPMailer-master/class.phpmailer.php');
		$CI = get_instance();
		$CI->load->model('web_config_model');
		$result=$CI->web_config_model->get_web_info();	//載入網站設定
		$this->pp=$result;
		$this->mail = new PHPMailer(true);
		$this->mail->IsSMTP(); // 設定使用SMTP方式寄信
		$this->mail->SMTPAuth = ($result->smtp_id!=""?true:false);	//設定SMTP需要驗證
		//$this->mail->SMTPDebug  = 1		;
		if ($result->use_gmail=="Y"){
			//Gmail需主機確認開放openSSL才能正常
			$this->mail->SMTPSecure = "ssl";       // Gmail的SMTP主機需要使用SSL連線
			$this->mail->Host = "smtp.gmail.com";  //Gamil的SMTP主機
			$this->mail->Port = 465;               //Gamil的SMTP主機的SMTP埠位為465埠。
		}else{
			$this->mail->Host = $result->smtp;  		  //SMTP主機
			$this->mail->Port = $result->smtp_port;     //SMTP埠位
		}	
		$this->mail->CharSet = "utf-8";         //設定郵件編碼  
		if ($result->smtp_id!=""){
			$this->mail->Username = $result->smtp_id;  //帳號
			$this->mail->Password = $result->smtp_pw;  //密碼    
		}
			
		$this->mail->From =  $result->com_mail; //設定寄件者信箱
		$this->mail->FromName = $result->com_name;           //設定寄件者姓名
		$this->mail->IsHTML(true);                     //設定郵件內容為HTML				
		
	}
	
	
	public function mailSend($subject,$message,$to,$cc=NULL,$bcc=NULL){
		$this->mail->Subject = $subject;
		$this->mail->Body = $message;
		
		if(is_array($to)){
			foreach($to as $user){
				$this->mail->AddAddress($user); //設定收件者郵件及名稱
			}
		}else{
			$this->mail->AddAddress($to);
		}

		if($cc!=NULL){	//副本
			if(is_array($cc)){
				foreach($cc as $user){
					$this->mail->AddCC($user); 
				}
			}else{
				$this->mail->AddCC($cc);
			}
		}

		if($bcc!=NULL){	//秘密副本
			if(is_array($bcc)){
				foreach($bcc as $user){
					$this->mail->AddBCC($user); 
				}
			}else{
				$this->mail->AddBCC($bcc);
			}
		}

		
		if(!$this->mail->Send()) {
		   return "Mailer Error: " . $this->mail->ErrorInfo;
		}
		else {
		   return NULL;
		}
			 
	}
	
}
?>