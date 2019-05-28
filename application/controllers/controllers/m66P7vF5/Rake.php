<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Rake extends Core_controller{
	
	public function __construct(){
		parent::__construct();
		//error_reporting(0);
		$this->data["member_group"]=array('0_一般會員','1_黃金會員','2_白金會員','3_藍鑽會員');
		$this->data["rakeKeyin1"]=array('0_等待處理','1_處理完畢','2_拒絕發放');
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	public function rake_config($m_group=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		if($_POST){	//修改資料
			$parameter=array();
			$colSql="live_rake,sport_rake,egame_rake,lottery_rake,esport_rake";
			$sqlStr="UPDATE `member_group_rake` SET ".sqlUpdateString($colSql);
			$sqlStr.=" where m_group=?";
			$parameter[":live_rake"]=$this->input->post("live_rake",true);
			$parameter[":sport_rake"]=$this->input->post("sport_rake",true);
			$parameter[":egame_rake"]=$this->input->post("egame_rake",true);
			$parameter[":lottery_rake"]=$this->input->post("lottery_rake",true);
			$parameter[":esport_rake"]=$this->input->post("esport_rake",true);
			$parameter[":m_group"]=$this->input->post("m_group",true);
			$this->webdb->sqlExc($sqlStr,$parameter);
			$msg = array("type" => "success", 'title' => '設定完成！', 'content' => '');
			$this -> _setMsgAndRedirect($msg, current_url());
		}else{
			
			$sqlStr="select * from `member_group_rake` where `m_group`=".$m_group;
			$this->data["row"]=$this->webdb->sqlRow($sqlStr);
			
			$this->data["changeUrl"]=SYSTEM_URL."/Rake/rake_config/";
			
			$this->data["formAction"]=site_url(SYSTEM_URL."/Rake/rake_config/".$m_group);
			$this->data["subtitle"]=$this->getsubtitle();
			$this -> data["body"] = $this -> load -> view("admin/rake/rake_config", $this -> data,true); 
			$this -> load -> view("admin/main", $this -> data);	
		}
	}
	
	public function index($keyin1=0){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$this->data["keyin1"]=$keyin1;
		$parameter=array();
		$sqlStr="select * from `member_rake` where `keyin1`=?";
		$parameter[':keyin1']=$keyin1;
		//---會員帳號----------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `u_id` like ?";
			$parameter[":u_id"]="%".@$_REQUEST["find4"]."%";
		}
		//日期區間
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `buildtime` >=?";
			$parameter[":find7"]=date('Ymd',strtotime(@$_REQUEST["find7"]));
		}
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `buildtime` < ?";
			$parameter[":find8"]=date('Ymd',strtotime(@$_REQUEST["find8"]));
		}
		//----除了最高權限 能看到所有...否則只抓該使用者的分類-------*/		
		$ag_array=array(4,5,6);		
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Rake/index/".$keyin1."?".$this->data["att"];//site_url("admin/news/index");
		$this->data["s_action"]=$config['base_url'];
		$config['total_rows'] = $total;
		$limit=10;	//每頁比數
		$config['per_page'] = $limit;	
		//$config['uri_segment'] = 4;
		$config['num_links'] = 4;
		$config['page_query_string'] = TRUE;
		$maxpage = $total % $limit == 0 ? $total/$limit : floor($total/$limit)+1; //總頁數
		$nowpage=1;
		if (@$_GET["per_page"]!=""){$nowpage=@$_GET["per_page"];}
		if ($nowpage>$maxpage){$nowpage=$maxpage;}	
		$sqlStr.=" order by `num` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
							
		$this -> data["body"] = $this -> load -> view("admin/rake/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	//AJAX點數發放
	public function keyChange(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$order_no=trim($this->input->post("order_no",true));//table
				$value=trim($this->input->post("value",true));//table
				if($order_no!=''  && $value!=''){
					$parameter=array();
					$sqlStr="select * from `member_rake` where `num`=?";
					$parameter[':member_rake']=$order_no;
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						$upSql="UPDATE `member_rake` SET `keyin1`=? where `num`=".$row["num"];
						$this->webdb->sqlExc($upSql,array(':keyin1'=>$value));						
						//發放點數處理
						if($value==1 && $row["is_received"]==0){
							
							$real_points=round((float)$row["rake"] * (tb_sql("point_percent","company",1) / 100),2);
							$u_power6=tb_sql("admin_num","member",$row["mem_num"]);	//代理編號
							$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
							$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
							$u_power4_profit=round($real_points * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
							$u_power5_profit=round($real_points * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
							$u_power6_profit=round($real_points * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
							
							$WalletTotal=getWalletTotal($row["mem_num"]);	//會員餘額
							$before_balance=(float)$WalletTotal;//異動前點數
							$after_balance= (float)$before_balance+(float)$row["rake"];//異動後點數
							
							$parameter=array();
							$colSql="mem_num,kind,points,real_points,admin_num,admin_num1,admin_num2,update_num,word,buildtime";
							$colSql.=",before_balance,after_balance,u_power4_profit,u_power5_profit,u_power6_profit";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':mem_num']=$row["mem_num"];
							$parameter[':kind']=7;
							$parameter[':points']=$row["rake"];
							$parameter[':real_points']=$real_points;
							$parameter[":admin_num"]= $u_power6;
							$parameter[":admin_num1"]= $u_power5;
							$parameter[":admin_num2"]= $u_power4;
							$parameter[":update_num"]= $this->web_root_num;
							$parameter[':word']='會員返水';
							$parameter[":buildtime"]= now();
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$parameter[':u_power4_profit']=$u_power4_profit;
							$parameter[':u_power5_profit']=$u_power5_profit;
							$parameter[':u_power6_profit']=$u_power6_profit;
							$this->webdb->sqlExc($sqlStr,$parameter);
							//變更為已經補點
							$upSql="UPDATE `member_rake` SET `is_received`=1 where `num`=".$row["num"];
							$this->webdb->sqlExc($upSql);
						}
						
						//紀錄此次變更人員和時間
						$upSql="UPDATE `member_rake` SET `is_received`=1,`update_admin`=".$this->web_root_num.",`updatetime`='".now()."'";
						$upSql.=" where `num`=".$row["num"];
						$this->webdb->sqlExc($upSql);
					}					
				}
			}	
		}
	}
	
	//AJAX一鍵點數發放
	public function AllChange(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$value=trim($this->input->post("value",true));//table
				$sqlStr="select * from `member_rake` where `keyin1`=0 and `is_received`=0";
				$rowAll=$this->webdb->sqlRowList($sqlStr);
				if($rowAll!=NULL){
					foreach($rowAll as $row){
						$upSql="UPDATE `member_rake` SET `keyin1`=? where `num`=".$row["num"];
						$this->webdb->sqlExc($upSql,array(':keyin1'=>$value));
						//發放點數處理
						if($value==1 && $row["keyin1"]==0 && $row["is_received"]==0){
							$real_points=round((float)$row["rake"] * (tb_sql("point_percent","company",1) / 100),2);
							$u_power6=tb_sql("admin_num","member",$row["mem_num"]);	//代理編號
							$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
							$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
							$u_power4_profit=round($real_points * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
							$u_power5_profit=round($real_points * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
							$u_power6_profit=round($real_points * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
							
							$WalletTotal=getWalletTotal($row["mem_num"]);	//會員餘額
							$before_balance=(float)$WalletTotal;//異動前點數
							$after_balance= (float)$before_balance+(float)$row["rake"];//異動後點數
							
							$parameter=array();
							$colSql="mem_num,kind,points,real_points,admin_num,admin_num1,admin_num2,update_num,word,buildtime";
							$colSql.=",before_balance,after_balance,u_power4_profit,u_power5_profit,u_power6_profit";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':mem_num']=$row["mem_num"];
							$parameter[':kind']=7;
							$parameter[':points']=$row["rake"];
							$parameter[':real_points']=$real_points;
							$parameter[":admin_num"]= $u_power6;
							$parameter[":admin_num1"]= $u_power5;
							$parameter[":admin_num2"]= $u_power4;
							$parameter[":update_num"]= $this->web_root_num;
							$parameter[':word']='會員返水';
							$parameter[":buildtime"]= now();
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$parameter[':u_power4_profit']=$u_power4_profit;
							$parameter[':u_power5_profit']=$u_power5_profit;
							$parameter[':u_power6_profit']=$u_power6_profit;
							$this->webdb->sqlExc($sqlStr,$parameter);
							//變更為已經補點
							$upSql="UPDATE `member_rake` SET `is_received`=1 where `num`=".$row["num"];
							$this->webdb->sqlExc($upSql);
						}
						//紀錄此次變更人員和時間
						$upSql="UPDATE `member_rake` SET `is_received`=1,`update_admin`=".$this->web_root_num.",`updatetime`='".now()."'";
						$upSql.=" where `num`=".$row["num"];
						$this->webdb->sqlExc($upSql);
					}
				}					
			}
		}	
		
	}
	
} 