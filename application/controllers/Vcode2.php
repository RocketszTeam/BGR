<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vcode2 extends CI_Controller {
	
	function __construct(){
		parent::__construct(); 
		//$this->load->library('securimage/securimage');
		
	}
	function index() {
		if($this->session->userdata('code_token') && $this->input->get('token')==$this->session->userdata('code_token')){
			header("Content-type:image/png");
			header("Content-Disposition:filename=image_code.png");
			//定義 header 的文件格式為 png，第二個定義其實沒什麼用		
			// 設定亂數種子
			mt_srand((double)microtime()*1000000);
			// 驗證碼變數
			$verification__session = '';
			// 定義顯示在圖片上的文字，可以再加上大寫字母
			//$str = 'abcdefghjkmnpqrstuvwxy3456789';
			$str = '0123456789';
			$l = strlen($str); //取得字串長度
			//隨機取出 4 個字
			for($i=0; $i<4; $i++){
			   $num=rand(0,$l-1);
			   $verification__session.= $str[$num];
			}
			// 將驗證碼記錄在 session 中
			//@$_SESSION["string"] = $verification__session;
			$s_name=$this->input->get('s_name') ?  $this->input->get('s_name',true) : 'checknum';
			
			$this->session->set_userdata($s_name,$verification__session);
			// 圖片的寬度與高度
			$imageWidth = 90; $imageHeight = 25;
			// 建立圖片物件
			$im = @imagecreatetruecolor($imageWidth, $imageHeight) or die("無法建立圖片！");
			//主要色彩設定
			// 圖片底色
			$bgColor = imagecolorallocate($im, 255,255,255);
			// 文字顏色
			$Color = imagecolorallocate($im, rand(0,50),rand(0,50),rand(0,255));
			// 干擾線條顏色
			$gray1 = imagecolorallocate($im, rand(100,200),rand(100,200),rand(100,200));
			// 干擾像素顏色
			$gray2 = imagecolorallocate($im, rand(100,200),rand(100,200),rand(100,200));
			//設定圖片底色
			imagefill($im,0,0,$bgColor);
			//底色干擾線條
			for($i=0; $i<10; $i++){
			   imageline($im,rand(0,$imageWidth),rand(0,$imageHeight),
			   rand($imageHeight,$imageWidth),rand(0,$imageHeight),$gray1);
			}
			
			//利用true type字型來產生圖片
			imagettftext($im,18,0,5,20, $Color,$_SERVER['DOCUMENT_ROOT']."/assets/admin/fonts/arial.ttf",strtoupper($verification__session));
			
			// 干擾像素
			for($i=0;$i<90;$i++){
			   imagesetpixel($im, rand()%$imageWidth ,
			   rand()%$imageHeight , $gray2);
			}
			
			imagepng($im);
			imagedestroy($im);
		}
	}
}
