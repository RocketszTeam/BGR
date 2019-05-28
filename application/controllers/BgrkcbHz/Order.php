<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Order extends Core_controller{

	public function __construct(){
		parent::__construct();
		
		$this->load->library('orderclass');	//載入訂單函式庫
		
		$this->data["orderKeyin1"]=$this->orderclass->orderKeyin1();//處理情形
		$this->data["orderKeyin2"]=$this->orderclass->orderKeyin2();//收款情形
		$this->data["paymentType"]=array('ATM_ATM','CVS_超商代碼','FAMI_全家','HILIFEET_萊爾富','IBON_7-11','Credit_信用卡');
		//拋售處理情況
		$this->data["sellKeyin1"]=$this->orderclass->sellKeyin1();//處理情形
		
		//轉移處理情況
		$this->data["transferKeyin1"]=$this->orderclass->transferKeyin1();
		
		//銀行匯款收款情況
		$this->data["bankKeyin2"]=$this->orderclass->bankKeyin2();

		$this->data["PayFrom"]=array('family'=>'全家',
									 'FAMIPORT'=>'全家',
									 'hilife'=>'萊爾富',
									 'LIFE_ET'=>'萊爾富',
									 'okmart'=>'OK 超商',
									 'OKGO'=>'OK 超商',
									 'ibon'=>'7-11',
									 'IBON'=>'7-11',
									 '1' =>'7-11',
									 '2' => '全家',
									 '3' =>'OK 超商',
									 '4' =>'萊爾富'
									);
		
		$this->data["openFind"]="Y";//是否啟用搜尋
	}
	
	public function index(){		
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$parameter=array();
		$sqlStr="select * from `orders` where 1=1";
		//---訂單編號----------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `order_no` like ?";	
			$parameter[":order_no"]="%".@$_REQUEST["find1"]."%";
		}
		//---付款情況----------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `keyin2`=?";
			$parameter[":keyin2"]=@$_REQUEST["find2"];
		}
		//---付款方式----------------------------
		if(@$_REQUEST["find3"]!=""){
			$sqlStr.=" and `payment`=?";
			$parameter[":find3"]=@$_REQUEST["find3"];
		}		
		
		//---會員帳號----------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `mem_num` in (select num from `member` where `u_id` like ?)";
			$parameter[":u_id"]="%".@$_REQUEST["find4"]."%";
		}
		//日期區間
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and buildtime >= ?";
			$parameter[":find7"]=@$_REQUEST["find7"];
		}
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and buildtime <=  ?";
			$parameter[":find8"]=@$_REQUEST["find8"];
		}
		
		//---繳費帳號----------------------------
		if(@$_REQUEST["find9"]!=""){
			//$sqlStr.=" and `order_no` in (select order_no from `allpay_orders` where `PaymentNo` like ?)";
			//$parameter[":PaymentNo"]="%".@$_REQUEST["find9"]."%";
		}


		//----除了最高權限 能看到所有...否則只抓該使用者的分類-------*/
		$ag_array=array(4,5,6);		
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		
		//統計指定日期區間已收款總額
		if(@$_REQUEST["find2"]==1 && @$_REQUEST["find7"]!="" && @$_REQUEST["find8"]!=""){
			$params=array();
			$sqlSum="select SUM(amount) as TotalAmount from `orders` where `keyin2`=1";
			$sqlSum.=" and buildtime >= ?";
			$params[":find7"]=@$_REQUEST["find7"];
			$sqlSum.=" and buildtime <=  ?";
			$params[":find8"]=@$_REQUEST["find8"];
			if(in_array($this->web_root_u_power,$ag_array)){
				$sqlSum.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
				$params[':admin_num']=$this->web_root_num;
			}
			$this->data["sumTotal"]=$this->webdb->sqlRow($sqlSum,$params);
		}
		
		
		
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/order/index?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
							
		$this -> data["body"] = $this -> load -> view("admin/order/index", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	
	//寶物拋售
	public function sell($keyin1=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		
		$this->data["keyin1"]=$keyin1;
		$parameter=array();
		$sqlStr="select * from `member_sell` where `keyin1`=?";
		$parameter[':keyin1']=$keyin1;
		//---訂單編號----------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `order_no` like ?";	
			$parameter[":order_no"]="%".@$_REQUEST["find1"]."%";
		}
		//---處理情況----------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `keyin1`=?";
			$parameter[":find2"]=@$_REQUEST["find2"];
		}
		//---會員帳號----------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `mem_num` in (select num from `member` where `u_id` like ?)";
			$parameter[":u_id"]="%".@$_REQUEST["find4"]."%";
		}
		//日期區間
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `buildtime` >=?";
			$parameter[":find7"]=@$_REQUEST["find7"];
		}
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `buildtime` < ?";
			$parameter[":find8"]=date('Y-m-d',strtotime($_REQUEST["find8"]."+1 day"));
		}
		
		//----除了最高權限 能看到所有...否則只抓該使用者的分類-------*/		
		$ag_array=array(4,5,6);		
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Order/sell/".$keyin1."?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
							
		$this -> data["body"] = $this -> load -> view("admin/order/sell", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
	}
	
	//AJAX修改手續費
	public function ajaxFee(){
		if(!$this->agent->is_referral()){	//判斷使用者是否別網站來的
			if($this->input->is_ajax_request()){	//檢查token是否正確
				$order_no=$this->input->post('order_no',true);
				$fee=$this->input->post('fee',true);
				$sqlStr="select * from `member_sell` where `order_no`=?";
				$row=$this->webdb->sqlRow($sqlStr,array('order_no'=>$order_no));
				if($row!=NULL){
					if($row["keyin1"] == 0){
						if((int)$row["amount"] > (int)$fee){
							$upSql="UPDATE `member_sell` SET `fee`= ? where `order_no`='".$order_no."'";
							$this->webdb->sqlExc($upSql,array('fee'=>$fee));
							echo json_encode(array('RntCode'=>1,'title'=>'處理成功','Msg'=>'請關閉視窗更新單據資料！'));
						}else{
							echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'手續費不得大於單據金額！'));
						}
					}else{
						echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'該單據已處理完畢無法變更手續費！'));
					}
				}else{
					echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'該單據並不存在！'));
				}
			}else{
				echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'不允許的存許方法！'));
			}
		}else{
			echo json_encode(array('RntCode'=>0,'title'=>'請求失敗','Msg'=>'不允許跨網域存取！'));
		}
	}
	
	
	
	//列出會員拋售單據當月或傳入時間的總帳
	public function sellReport(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				if($this->input->post('orderDate')){
					$eDate=$this->input->post('orderDate',true);
					$sDate=date('Y-m-d H:i:s',strtotime($eDate." -3 day"));
				}else{
					$sDate=$this->input->post('sTime',true);
					$eDate=$this->input->post('eTime',true);
				}
				$mem_num=$this->input->post('mem_num',true);
				$this->load->model('admin/Report_model','report');
				$this->session->set_userdata('mytest','aa');

				$prefix=array();
				$data=array();
				$data["mem_num"]=$mem_num;
				$data["u_id"]=tb_sql("u_id","member",$mem_num);
				$data["u_name"]=tb_sql("u_name","member",$mem_num);
				$data["sDate"]=$sDate;
				$data["eDate"]=$eDate;

                $gameList = rakegameList();
                foreach ($gameList as $gameCode => $gameInfo) {
                    $row = $this->report->{$gameInfo['gameModel'].'_member'}($mem_num, $sDate, $eDate);
                    if(isset($gameInfo[2]))$data = $this->report->buildMemberData($data, $row, $gameCode, $gameInfo[2]);
                    else $data = $this->report->buildMemberData($data, $row, $gameCode);
                    array_push($prefix, array($gameCode, $gameInfo['gameName']));
                }
				
				$data["total_betAmount"]=0;
				$data["total_validAmount"]=0;
				$data["total_winOrLoss"]=0;
				foreach($prefix as $value){
					$data["total_betAmount"]+=$data[$value[0]."_betAmount"];
					$data["total_validAmount"]+=$data[$value[0]."_validAmount"];
					$data["total_winOrLoss"]+=$data[$value[0]."_winOrLoss"];
				}
				
				$dataList=array('prefix'=>$prefix,'data'=>$data);
				echo json_encode($dataList);
			}
		}
	}
	
	
	//銀行匯款
	public function bank($keyin1=0){
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}
		$this->data["keyin1"]=$keyin1;
		$parameter=array();
		$sqlStr="select * from `member_bank_transfer` where `keyin2`=?";
		$parameter[':keyin2']=$keyin1;
		//---訂單編號----------------------------
		if(@$_REQUEST["find1"]!=""){	
			$sqlStr.=" and `order_no` like ?";	
			$parameter[":order_no"]="%".@$_REQUEST["find1"]."%";
		}
		//---收款情況----------------------------
		if(@$_REQUEST["find2"]!=""){
			$sqlStr.=" and `keyin2`=?";
			$parameter[":find2"]=@$_REQUEST["find2"];
		}
		//---會員帳號----------------------------
		if(@$_REQUEST["find4"]!=""){
			$sqlStr.=" and `mem_num` in (select num from `member` where `u_id` like ?)";
			$parameter[":u_id"]="%".@$_REQUEST["find4"]."%";
		}
		//日期區間
		if(@$_REQUEST["find7"]!=""){
			$sqlStr.=" and `buildtime`>=?";
			$parameter[":find7"]=@$_REQUEST["find7"];
		}
		if(@$_REQUEST["find8"]!=""){
			$sqlStr.=" and `buildtime`< ?";
			$parameter[":find8"]=date('Y-m-d',strtotime($_REQUEST["find8"]."+1 day"));
		}
		
		//----除了最高權限 能看到所有...否則只抓該使用者的分類-------*/		
		$ag_array=array(4,5,6);		
		if(in_array($this->web_root_u_power,$ag_array)){
			$sqlStr.=" and `admin_num` in (?".kind_sql($this->web_root_num,'admin').")";
			$parameter[':admin_num']=	$this->web_root_num;
		}
		
		$total=$this->webdb->sqlRowCount($sqlStr,(count($parameter)>0?$parameter:NULL)); //總筆數
		
		//分頁相關
		$config['base_url'] = base_url().SYSTEM_URL."/Order/bank/".$keyin1."?".$this->data["att"];//site_url("admin/news/index");
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
		$sqlStr.=" order by `buildtime` desc LIMIT ".((($nowpage>0?$nowpage:1)-1)*$limit).",".$limit;
		$rowAll=$this->webdb->sqlRowList($sqlStr,$parameter);		
		$this -> data["nowpage"]=$nowpage;
		$this -> data["total_rows"] = $config["total_rows"];
		$this -> data["result"] = $rowAll;
						
		//產生分頁連結
		$this -> load -> library("pagination");
		$this -> pagination -> doConfig($config);
		$this -> data["pagination"] = $this -> pagination -> create_links();
							
		$this -> data["body"] = $this -> load -> view("admin/order/bank", $this -> data,true);   
		$this -> load -> view("admin/main", $this -> data);  
		
		
	}
	
	//銀行匯款收款情況變更
	public function bank_keyChange(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$order_no=trim($this->input->post("order_no",true));//table
				$value=trim($this->input->post("value",true));//table
				if($order_no!=''  && $value!=''){
					$parameter=array();
					$sqlStr="select * from `member_bank_transfer` where `order_no`=?";
					$parameter[':order_no']=$order_no;
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						$upSql="UPDATE `member_bank_transfer` SET `keyin2`=? where `order_no`='".$row["order_no"]."'";
						$this->webdb->sqlExc($upSql,array(':keyin1'=>$value));												
						//確認已收款
						if($value==1 && $row["is_received"]==0){
							
							$WalletTotal=getWalletTotal($row["mem_num"]);	//會員餘額
							//異動前點數
							$before_balance=(float)$WalletTotal;
							//異動後點數
							$after_balance= (float)$before_balance+(float)$row["amount"];
							
							$parameter=array();
							$colSql="mem_num,kind,points,word,order_no,admin_num,buildtime,before_balance,after_balance";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':mem_num']=$row["mem_num"];
							$parameter[':kind']=12;	//放棄轉移
							$parameter[':points']=$row["amount"];
							$parameter[':word']='銀行匯款';
							$parameter[':order_no']=$row["order_no"];
							$parameter[':admin_num']=tb_sql("admin_num","member",$row["mem_num"]);
							$parameter[':buildtime']=now();	
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$this->webdb->sqlExc($sqlStr,$parameter);
							
							//變更為已經補點
							$upSql="UPDATE `member_bank_transfer` SET `is_received`=1 where `order_no`='".$row["order_no"]."'";
							$this->webdb->sqlExc($upSql);
						}
						//紀錄此次變更人員和時間
						$upSql="UPDATE `member_bank_transfer` SET `is_received`=1,`update_admin`=".$this->web_root_num.",`updatetime`='".now()."'";
						$upSql.=" where `order_no`='".$row["order_no"]."'";
						$this->webdb->sqlExc($upSql);
					}					
				}
			}	
		}
	}
	
	//AJAX拋售處理情況變更
	public function keyChange(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$order_no=trim($this->input->post("order_no",true));//table
				$value=trim($this->input->post("value",true));//table
				if($order_no!=''  && $value!=''){
					$parameter=array();
					$sqlStr="select * from `member_sell` where `order_no`=?";
					$parameter[':order_no']=$order_no;
					$row=$this->webdb->sqlRow($sqlStr,$parameter);
					if($row!=NULL){
						$upSql="UPDATE `member_sell` SET `keyin1`=? where `order_no`='".$row["order_no"]."'";
						$this->webdb->sqlExc($upSql,array(':keyin1'=>$value));						
						//放棄訂購補點處理
						if($value==2 && $row["is_received"]==0){
							
							$WalletTotal=getWalletTotal($row["mem_num"]);	//會員餘額
							//異動前點數
							$before_balance=(float)$WalletTotal;
							//異動後點數
							$after_balance= (float)$before_balance+(float)$row["amount"];
							
							$parameter=array();
							$colSql="mem_num,kind,points,word,admin_num,buildtime,before_balance,after_balance";
							$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).")";
							$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
							$parameter[':mem_num']=$row["mem_num"];
							$parameter[':kind']=10;	//放棄提領
							$parameter[':points']=$row["amount"];
							$parameter[':word']='放棄提領';
							$parameter[':admin_num']=tb_sql("admin_num","member",$row["mem_num"]);
							$parameter[':buildtime']=now();	
							$parameter[':before_balance']=$before_balance;
							$parameter[':after_balance']=$after_balance;
							$this->webdb->sqlExc($sqlStr,$parameter);
							
							//變更為已經補點
							$upSql="UPDATE `member_sell` SET `is_received`=1 where `order_no`='".$row["order_no"]."'";
							$this->webdb->sqlExc($upSql);
						}
						
						//紀錄此次變更人員和時間
						$upSql="UPDATE `member_sell` SET `is_received`=1,`update_admin`=".$this->web_root_num.",`updatetime`='".now()."'";
						$upSql.=" where `order_no`='".$row["order_no"]."'";
						$this->webdb->sqlExc($upSql);
					}					
				}
			}	
		}
	}
	
	//ajax靜音
	public function ajaxCloseSound(){
		if(!$this->agent->is_referral()){
			if($this->input->is_ajax_request()){
				$upSql="UPDATE `".$this->input->post("tb",true)."` SET `no_sound`=1 where `".$this->input->post("column",true)."`=?";
				$this->webdb->sqlExc($upSql,array(':id'=>$this->input->post("value",true)));
			}
		}
	}
	


	
} 