<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vcode extends CI_Controller {
	
	function __construct(){
		parent::__construct(); 
		$this->load->library('securimage/securimage');
		
	}
	
	
	public function index($W=150,$H=54) {
		
		if($this->session->userdata('code_token') && $this->input->get('token')==$this->session->userdata('code_token')){	
			$img = new Securimage();
			if (!empty($_GET['form'])) $img->setNamespace($_GET['form']);			
			$img->charset = '0123456789';
			$img->code_length = 4;
			$img->image_bg_color = new Securimage_Color("#ffc107");	//背景顏色	
			$img->text_color = new Securimage_Color('#000');	//文字顏色
			$img->line_color = new Securimage_Color('#fff');	//線條顏色
			$img->noise_color = new Securimage_Color("#ffc107");	//點點的顏色
			$img->image_width = ($this->input->get('w') ? $this->input->get('w') : $W);
			$img->image_height = ($this->input->get('h') ? $this->input->get('h') : $H);	
			$img->num_lines = 0;	//線條數量
			$img->show();
		}
	}	
}
