<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Demo extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		$this -> data["picDir"]=UPLOADS_PATH . '/demo/';	//上傳目錄	
	}
	
	public function index(){		
		
		$this->data["subtitle"]=$this->getsubtitle();	//取得當前頁面名稱
		
		$this -> data["body"] = $this -> load -> view("sysAdmin/demo_form/web_form", $this -> data,true);   
		$this -> load -> view("sysAdmin/main2", $this -> data);  
	}
	
	
	public function AjaxUpload(){
		if($_POST){
			
			$p1=array();
			$p2=array();
			//上傳檔案
			
			$images = $_FILES['upload'];
			$filenames = $images['name'];
			
			for ($i=0; $i < count($filenames); $i++){
				if ($images["size"][$i]>0){ //檢查檔案大小是否大於0
					//重新命名				
					
					$subName=explode('.',basename($filenames[$i]));					
					
					$newName = "pro_".date("Ymdhis")."_".$i.".".array_pop($subName);
					
					$key = $newName;
					$picUrl=UPLOADS_URL.'/demo/'.$newName;
					$deleteUrl=site_url("sysAdmin/Demo/AjaxDelete");
					$p1[$i] = "<img style='height:160px' src='".$picUrl."' class='file-preview-image'>"; 
					//$p2[$i] = array('caption'=>$key.$subName,'width'=>'120px','url'=>$url,'key'=>$key);
					$p2[$i]=array('caption'=>$newName,'width'=>'120px','url'=>$deleteUrl,'key'=>$i,'extra'=>array('num'=>'','xd'=>5));
					//傳檔
					copy($images["tmp_name"][$i],$this -> data["picDir"].$newName);   //存檔案
				}
			}
			echo json_encode(array('initialPreview'=>$p1,'initialPreviewConfig'=>$p2,'append'=>true));
		}
		
	}
	
	public function AjaxDelete(){
		if($_POST){
			unlink('/demo/'.$_REQUEST["key"]);
			echo '{}';
		}
		
	}
}
?>