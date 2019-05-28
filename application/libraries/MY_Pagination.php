<?php
class MY_Pagination extends CI_Pagination {
	
	private $_config = array(
			"full_tag_open"=>"<ul class=\"pagination pagination-centered\">",
			"full_tag_close"=>"</ul>",
			
			"first_tag_open" => '<li>',
			"first_tag_close" => '</li>',
			"first_link" => '<i class="ace-icon fa fa-angle-double-left"></i><i class="ace-icon fa fa-angle-double-left"></i>',
			
			"last_tag_open" => "<li>",
			"last_tag_close" => "</li>",
			"last_link" => '<i class="ace-icon fa fa-angle-double-right"></i><i class="ace-icon fa fa-angle-double-right"></i>',
			
			"next_tag_open" => "<li>",
			"next_tag_close" => "</li>",
			"next_link" => '<i class="ace-icon fa fa-angle-double-right"></i>',
			
			"prev_tag_open" => "<li>",
			"prev_tag_close" => "</li>",
			"prev_link" => '<i class="ace-icon fa fa-angle-double-left"></i>',
			
			"cur_tag_open" => "<li class=\"active\"><a>",
			"cur_tag_close" => "</a></li>",
			
			"num_tag_open" => "<li>",
			"num_tag_close" => "</li>",
			
			);

	private $_config2 = array(
			"full_tag_open"=>"<ul class=\"pagination\">",
			"full_tag_close"=>"</ul>",

			"first_tag_open" => '<li>',
			"first_tag_close" => '</li>',
			"first_link" => "&laquo; 第一頁",

			"last_tag_open" => "<li>",
			"last_tag_close" => "</li>",
			"last_link" => "最末頁 &raquo;",

			"next_tag_open" => "<li>",
			"next_tag_close" => "</li>",
			"next_link" => "»",

			"prev_tag_open" => "<li>",
			"prev_tag_close" => "</li>",
			"prev_link" => "«",

			"cur_tag_open" => "<li class=\"active\"><a>",
			"cur_tag_close" => "</a></li>",

			"num_tag_open" => "<li>",
			"num_tag_close" => "</li>",

	);
	
	//前台電腦版
	private $PC = array(
			"full_tag_open"=>"<div class=\"pagination\">",
			"full_tag_close"=>"</div>",
			
			//"first_tag_open" => '<span>',
			//"first_tag_close" => '</span>',
			//"first_link" => "&laquo;&laquo; 第一頁",
			
			//"last_tag_open" => "<span>",
			//"last_tag_close" => "</span>",
			//"last_link" => "最末頁 &raquo;&raquo;",
			
			"next_tag_open" => "<span>",
			"next_tag_close" => "</span>",
			"next_link" => "下一頁 &raquo;",
			
			"prev_tag_open" => "<span>",
			"prev_tag_close" => "</span>",
			"prev_link" => "&laquo; 上一頁",
			
			"cur_tag_open" => "<span class=\"current\">",
			"cur_tag_close" => "</span>",
			
			"num_tag_open" => "<span>",
			"num_tag_close" => "</span>",
			
			);
	
	//前台手機板		
	//前台手機板		
	private $Mobile = array(
			"full_tag_open"=>"<ul class=\"pagination pagination-sm\">",
			"full_tag_close"=>"</ul>",
			
			//"first_tag_open" => '<li>',
			//"first_tag_close" => '</li>',
			//"first_link" => "&laquo; 第一頁",
			
			//"last_tag_open" => "<li>",
			//"last_tag_close" => "</li>",
			//"last_link" => "最後一頁 &raquo;",
			
			"next_tag_open" => "<li class=\"page-item\">",
			"next_tag_close" => "</li>",
			"next_link" => "下一頁 &rsaquo;",
			
			"prev_tag_open" => "<li class=\"page-item\">",
			"prev_tag_close" => "</li>",
			"prev_link" => "&lsaquo; 上一頁 ",
			
			"cur_tag_open" => "<li class=\"active page-item\"><a class=\"page-link\">",
			"cur_tag_close" => "</a></li>",
			
			"num_tag_open" => "<li class=\"page-item\">",
			"num_tag_close" => "</li>",
			
			);

	public function __construct(){
		parent::__construct();
	}
	
	public function doConfig($config){
		$config = array_merge($config , $this->_config);		
		parent::initialize($config);
		
	}

	public function doConfig2($config){
		$config = array_merge($config , $this->_config2);
		parent::initialize($config);

	}
	
	//前台電腦版
	public function doConfigPC($config){
		$config = array_merge($config , $this->PC);		
		parent::initialize($config);
		
	}
	//前台手機板
	public function doConfigMobile($config){
		$config = array_merge($config , $this->Mobile);		
		parent::initialize($config);
		
	}
	public function initialize(array $config = array()){
		
		$config = array_merge($config , $this->_config);		
		parent::initialize($config);
		
	}
	
}
