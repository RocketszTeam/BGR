<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");

class Test2 extends Core_controller{
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function index2(){//密碼有空白時使用
		$sqlStr="select `u_id`,`phone` from `member` where `u_id` like '91D09%'";
		$row=$this->webdb->sqlRowList($sqlStr);	
		$usql = "";
		foreach($row as $rs){
			$usql = "update `member` set `u_password` = '".$this->encryption->encrypt($rs["phone"])."' where u_id = '".$rs["u_id"]."' and phone='".$rs["phone"]."'";
			$this->webdb->sqlExc($usql);
		}

		
	}
	
	//產生帳號、給200、簡訊
	public function index($root=0){	
		if(!$this->isLogin()){	//檢查登入狀態
			$msg = array("type" => "danger", 'title' => '權限錯誤！','content'=>'很抱歉...您無權檢示頁面');
			$this -> _setMsgAndRedirect($msg, $this->agent->referrer());
			exit;
		}

		$handle = fopen("/var/www/html/application/controllers/91dsysTem/0906.txt", "r");
		if (FALSE === $handle) {
			exit("Failed to open stream to URL");
		}
		
		$contents = '';
		
		/*
		while (!feof($handle)) {
			$contents = fread($handle, 10);
			echo $contents."<Br>";
		}
		*/
		$i = 0;
		
		while (($buffer = fgets($handle, 4096)) !== false) {
			if(strlen($buffer) > 7){		
				$buffer = trim($buffer);
				//帳號
				$acc = "91D".$buffer;
				//密碼
				$pwd = $buffer;				
				/*
				//===================建立帳號區塊=============================				
				//檢查電話號碼有沒有重覆
				$sqlStr="select `num` from `member` where `phone`='".$buffer."' or `u_id`='".$acc."'";
				$row=$this->webdb->sqlRow($sqlStr);
				if($row==NULL){
					
					
					//echo "Account:".$acc."|Password:".$pwd."<Br>";
					//創建帳號
					$parameter=array();
					$colSql="nation,u_id,u_password,u_name,phone,line,email,m_group";
					$colSql.=",active,is_vaid,demo,admin_num,reg_time";
					$sqlStr="INSERT INTO `member` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter[":nation"]=trim("TW");
					$parameter[":u_id"]=trim($acc);
					$parameter[":u_password"]=$this->encryption->encrypt($pwd);
					$parameter[":u_name"]=trim($acc);
					$parameter[":phone"]=trim($buffer);
					$parameter[":line"]="";
					$parameter[":email"]="";
					$parameter[":m_group"]=0;//一般會員
					$parameter[":active"]="Y";
					$parameter[":is_vaid"]="0";//驗證
					$parameter[":demo"]="";//備註
					$parameter[":admin_num"]=188;	//TEXT
					$parameter[":reg_time"]=now();
					$mem_num=$this->webdb->sqlExc($sqlStr,$parameter);
				
					//給點
					$points = 200;
					$real_points=200;
					$u_power6=tb_sql("admin_num","member",$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					$u_power4_profit=round($real_points * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round($real_points * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round($real_points * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤			
					$parameter=array();
					$colSql="mem_num,kind,points,real_points,admin_num,admin_num1,admin_num2,update_num,word,buildtime";
					$colSql.=",before_balance,after_balance,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="INSERT INTO `member_wallet` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':mem_num']=$mem_num;
					$parameter[':kind']=1;
					$parameter[':points']=$points;
					$parameter[':real_points']=$real_points;
					$parameter[":admin_num"]= $u_power6;
					$parameter[":admin_num1"]= $u_power5;
					$parameter[":admin_num2"]= $u_power4;
					$parameter[":update_num"]= 0;
					$parameter[':word']="幸運會員獲得免費【200點】";
					$parameter[":buildtime"]= now();
					$parameter[':before_balance']=0;
					$parameter[':after_balance']=200;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;
					$this->webdb->sqlExc($sqlStr,$parameter);	
					

				} else {
					echo $buffer." 電話號碼重覆";	
				}
				//===================建立帳號區塊=============================
				*/
				/*
				//===================簡訊區塊=============================
				//發簡訊			
				$subject="恭喜您成為「玖天娛樂」的幸運會員";
				$msg = "恭喜您成為「玖天娛樂」的幸運會員，免費獲得【200點】，點數巳撥入帳號內。請依照帳號".$acc." 密碼".$pwd." 登入，密碼登入後可更改「立即使用」↓  http://text.91d.biz/ ";
				//echo $msg."<hr>";					
				$this->load->library('smsclass2');
				$phone=$buffer;
				
				if(!$a = $this->smsclass2->sendSMS($msg, $phone,$subject) ){
					echo $phone.'簡訊發送失敗<br>';
				} else {
					echo $phone."<Br>";	
				}
				
				sleep(0.02);
				//===================簡訊區塊=============================
				*/					
				$i++;
				
			}
			
		}		
		fclose($handle);
		
		
		
	}
	
	
	public function gp(){

		$amount = 50;
		$payment = "CVS";		
		$this->load->library('orderclass');	//訂單函式庫
		$this->load->library('gpclass');	//訂單函式庫
		
		$order_no=$this->orderclass->order_no();
		
		$parameter=array();
		$colSql="order_no,mem_num,admin_num,amount,payment,pay_mode,buildtime";
		$sqlStr="INSERT INTO `orders` (".sqlInsertString($colSql,0).")";
		$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
		$parameter[':order_no']=$order_no;
		$parameter[':mem_num']=24;
		$parameter[':admin_num']=5;
		$parameter[':amount']=$amount;
		$parameter[':payment']=$payment;
		$parameter[':pay_mode']=2;
		$parameter[':buildtime']=now();
		$this->webdb->sqlExc($sqlStr,$parameter);		
		
		$log=$this->gpclass->getFormArray($order_no,$amount,$payment);	
		if($log==NULL){
			echo "Manger/deposit_result?order_no=".$order_no;
		} else {
			echo $order_no."錯誤";
		}
		
	}
	
	public function pc(){

		$amount = 50;
		$payment = "ATM";		
		$this->load->library('orderclass');	//訂單函式庫
		$this->load->library('pchomeclass');	//訂單函式庫
		
		$order_no=$this->orderclass->order_no();
		
		$parameter=array();
		$colSql="order_no,mem_num,admin_num,amount,payment,pay_mode,buildtime";
		$sqlStr="INSERT INTO `orders` (".sqlInsertString($colSql,0).")";
		$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
		$parameter[':order_no']=$order_no;
		$parameter[':mem_num']=24;
		$parameter[':admin_num']=5;
		$parameter[':amount']=$amount;
		$parameter[':payment']=$payment;
		$parameter[':pay_mode']=2;
		$parameter[':buildtime']=now();
		$this->webdb->sqlExc($sqlStr,$parameter);		
		
		$log=$this->pchomeclass->getFormArray($order_no,$amount,$payment);	
		print_r($log);
		/*
		if($log==NULL){
			echo "Manger/deposit_result?order_no=".$order_no;
		} else {
			echo $order_no."錯誤";
		}
		*/
	}	
	
	
	
} 