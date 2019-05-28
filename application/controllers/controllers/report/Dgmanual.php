<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once (dirname(__FILE__)."/Core_controller.php");
class Dgmanual extends Core_controller{
	public function __construct(){ 
		parent::__construct();
		error_reporting(0);
		date_default_timezone_set("Asia/Taipei");
	}
	
	public function initTicketsbydata($data){
		$result = $this->dreamgame->initTicketsbydata($data);//yyyy-MM-dd HH:mm:ss

		echo $result;

	}


	public function initTickets(){
		$result = $this->dreamgame->initTickets('2017-06-07','2017-06-07');//yyyy-MM-dd HH:mm:ss

		echo $result;

	}


	public function get_balance (){
		$result = $this->dreamgame->get_balance('ad817');//yyyy-MM-dd HH:mm:ss
		//$result=$this->dreamgame->forward_game('PJtw6644311','tw6644311');

		echo $result;
	}

	public function get_report(){
		
		$result=$this->dreamgame->reporter_all();
		
		$listarray = (array) $result->list;
		echo 'listarray:<br>';
		//print_r($listarray);
		
		//exit;
		if (is_array($listarray)){
			//echo '<pre>';
			//print_r($listarray);
			//echo '</pre>';

		}
		if ($result != null){
		if(count($listarray)){	//執行成功且有帳回來
			$idlist = array();

			foreach($listarray as $row){
				//寫入DB
				//if($this->check_betnum($row->BetID)){	//資料庫內沒有紀錄才新增
					//echo 'db:'.$row->id ;
					//取出會員代理總代代理編號	
					//echo '<pre>';
					//print_r($row);
					//echo '</pre>';
					$sqlStr="select mem_num from `games_account` where u_id='".$row->userName."' and gamemaker_num=12";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					
					$totalWinlose=(float)$row->winOrLoss - (float)$row->betPoints;
					
					$u_power4_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
										//echo 'ResultAmount：'.$row->ResultAmount.'，u_power4_profit：'.$u_power4_profit.'<br>';
					//echo '被樹：'.$this->get_percent($u_power4,$u_power5).'<br>';
					
					//有效投注額, 基本上可以從返回的 ResultAmount 判斷. 如果 ResultAmount = 0, 即和或退回投注, 那有效投注就是0, 否則, 有效投注就是 BetAmount 了
					$ValidAmount=($row->availableBet==0 ? $row->availableBet : $row->availableBet);	
					

					$sqlStr="select `id` from `dreamgame_report` where `id`=?";	
					$rowrecord=$this->webdb->sqlRow($sqlStr,array(':id'=>$row->id));
					if ($rowrecord == null)	{

					$parameter=array();

					$colSql="id,tableId,shoeId,playId,lobbyId,gameType,gameId,memberId,parentId,betTime";
					$colSql.=",calTime,winOrLoss,totalWinlose,balanceBefore,betPoints,betPointsz,availableBet";
					$colSql.=",userName,result,betDetail,ip,ext,isRevocation,currencyId,deviceType";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `dreamgame_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':id']=$row->id;
					$parameter[':tableId']=$row->tableId;
					echo "id:".$row->id.'<br>';
					$parameter[':shoeId']=$row->shoeId;
					$parameter[':playId']=$row->playId;
					$parameter[':tableId']=$row->tableId;
					$parameter[':lobbyId']=$row->lobbyId;
					$parameter[':gameType']=$row->gameType;
					$parameter[':gameId']=$row->gameId;
					$parameter[':memberId']=$row->memberId;
					$parameter[':parentId']=$row->parentId;

					$parameter[':betTime']=date('Y-m-d H:i:s',strtotime($row->betTime));
					$parameter[':calTime']=date('Y-m-d H:i:s',strtotime($row->calTime));
					$parameter[':winOrLoss']=$row->winOrLoss;
					$parameter[':totalWinlose']=$totalWinlose;
					$parameter[':balanceBefore']=$row->balanceBefore;
					$parameter[':betPoints']=$row->betPoints;
					$parameter[':betPointsz']=$row->betPointsz;
					$parameter[':availableBet']=$row->availableBet;


					$parameter[':userName']=$row->userName;
					$parameter[':result']=(string)$row->result;
					//echo 'result:'.$row->$result ;
					$parameter[':betDetail']=$row->betDetail;
					$parameter[':ip']=$row->ip;
					$parameter[':ext']=$row->ext;
					$parameter[':isRevocation']=$row->isRevocation;
					$parameter[':currencyId']=$row->currencyId;
					$parameter[':deviceType']=$row->deviceType;
					
					$parameter[':mem_num']=$mem_num;


					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
					//echo $sqlStr ;
					array_push($idlist, $row->id);

				}else{ echo '已有紀錄';}
			} // 每筆注單處理	
			//標注註單
			echo 'remark';
			$markreport=$this->dreamgame->markReport($idlist);
			echo '標注注單結果：'.$markreport;
			print_r($idlist);


			}else{
				echo '回傳list為空';
			}
			
		}


	}
	public function correct_page2 (){
		$cor_date = $this->input->post("cor_date");
		$sqlStr = 'select distinct userName from dreamgame_report where betTime >= "'.$cor_date.' 00:00:00" and betTime <= "'.$cor_date.' 23:59:59" and u_power5=0 order by userName';
		echo $sqlStr ;

		$output = $this->db->query ($sqlStr);
		$userName = $output->result_array();
		echo '<pre>';
		print_r($userName);

		foreach ($userName as $NameRow){

					$sqlStr="select mem_num from `games_account` where u_id='".$NameRow['userName']."' and gamemaker_num=12";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					echo '<br>mem_num:'.$mem_num.'<br>';
					echo 'u_power6:'.$u_power6.'<br>';
					echo 'u_power5:'.$u_power5.'<br>';
					echo 'u_power4:'.$u_power4.'<br>';

					//取出該員注單資料
					$sqlStr = 'select * from dreamgame_report where betTime >= "'.$cor_date.' 00:00:00" and betTime <= "'.$cor_date.' 23:59:59"  and userName="'.$NameRow['userName'].'" order by userName';
					echo 'sqlStr:'.$sqlStr ;


					$output = $this->db->query ($sqlStr);
					$Betorder = $output->result_array();

					$u4_percent = (tb_sql('percent','admin',$u_power4) / 100);
					$u5_percent = (tb_sql('percent','admin',$u_power5) / 100);
					$u6_percent = (tb_sql('percent','admin',$u_power6) / 100);
					echo '<br>u4:'.$u4_percent;
					echo '<br>u5:'.$u5_percent;
					echo '<br>u6:'.$u6_percent;



					foreach($Betorder as $row){
						echo '-------------------------------------';
						$isupdate = false;
						$updatestr = '';

						if ($row['mem_num']<>$mem_num){
							$isupdate = true ;
							$updatestr = 'wrong mem_num ';
						}

					$totalWinlose=(float)$row['winOrLoss'] - (float)$row['betPoints'];


					$u_power4_profit=round((float)$totalWinlose * $u4_percent,2);	//股東分潤
					$u_power5_profit=round((float)$totalWinlose * $u5_percent,2);	//股東分潤
					$u_power6_profit=round((float)$totalWinlose * $u6_percent,2);	//代理分潤


					if ($u_power4_profit<>$row['u_power4_profit']){
							$isupdate = true ;
							$updatestr .= ' wrong u_power4_profit';
					}
					if ($u_power5_profit_<>$row['u_power5_profit']){
							$isupdate = true ;
							$updatestr .= ' wrong u_power5_profit';
					}
					if ($u_power6_profit<>$row['u_power6_profit']){
							$isupdate = true ;
							$updatestr .= ' wrong u_power6_profit';
					}
					if ($u_power4<>$row['u_power4']){
							$isupdate = true ;
							$updatestr .= ' wrong u4';
					}
					if ($u_power5<>$row['u_power5']){
							$isupdate = true ;
							$updatestr .= ' wrong u5';
					}
					if ($u_power6<>$row['u_power6']){
							$isupdate = true ;
							$updatestr .= ' wrong u6';
					}
					//需要更新
					if ($isupdate){

						$parameter=array();
						$colSql="u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit,mod";
						$sqlStr="UPDATE `dreamgame_report` SET ".sqlUpdateString($colSql);
						$sqlStr.=" where id=?";
						//$parameter[":mem_num"]=$mem_num.'  '.$row['mem_num'];
						$parameter[":u_power4"]=$u_power4;
						$parameter[":u_power5"]=$u_power5;
						$parameter[":u_power6"]=$u_power6;
						$parameter[":u_power4_profit"]=$u_power4_profit;
						$parameter[":u_power5_profit"]=$u_power5_profit;
						$parameter[":u_power6_profit"]=$u_power6_profit;
						$parameter[":mod"]=1;
						$parameter[":id"]=$row['id'];
						echo '<br>'.$sqlStr ;
						echo '<br>'.$updatestr ;
						echo '<br>totalWinlose:'.$totalWinlose.'<br>' ;
						print_r($parameter);

						$this->webdb->sqlExc($sqlStr,$parameter);

					}



					}




		}



		
	}


	public function correct_page (){
		$cor_date = $this->input->post("cor_date");
		$sqlStr = 'select distinct userName from dreamgame_report where betTime >= "'.$cor_date.' 00:00:00" and betTime <= "'.$cor_date.' 23:59:59" and `mod` = 0 order by userName';
		echo $sqlStr ;

		$output = $this->db->query ($sqlStr);
		$userName = $output->result_array();
		echo '<pre>';
		print_r($userName);

		foreach ($userName as $NameRow){

					$sqlStr="select mem_num from `games_account` where u_id='".$NameRow['userName']."' and gamemaker_num=12";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					echo '<br>mem_num:'.$mem_num.'<br>';
					echo 'u_power6:'.$u_power6.'<br>';
					echo 'u_power5:'.$u_power5.'<br>';
					echo 'u_power4:'.$u_power4.'<br>';

					//取出該員注單資料
					$sqlStr = 'select * from dreamgame_report where betTime >= "'.$cor_date.' 00:00:00" and betTime <= "'.$cor_date.' 23:59:59" and `mod` = 0 and userName="'.$NameRow['userName'].'" order by userName';
					echo 'sqlStr:'.$sqlStr ;


					$output = $this->db->query ($sqlStr);
					$Betorder = $output->result_array();

					$u4_percent = (tb_sql('percent','admin',$u_power4) / 100);
					$u5_percent = (tb_sql('percent','admin',$u_power5) / 100);
					$u6_percent = (tb_sql('percent','admin',$u_power6) / 100);
					echo '<br>u4:'.$u4_percent;
					echo '<br>u5:'.$u5_percent;
					echo '<br>u6:'.$u6_percent;



					foreach($Betorder as $row){
						echo '-------------------------------------';
						$isupdate = false;
						$updatestr = '';

						if ($row['mem_num']<>$mem_num){
							$isupdate = true ;
							$updatestr = 'wrong mem_num ';
						}

					$totalWinlose=(float)$row['winOrLoss'] - (float)$row['betPoints'];


					$u_power4_profit=round((float)$totalWinlose * $u4_percent,2);	//股東分潤
					$u_power5_profit=round((float)$totalWinlose * $u5_percent,2);	//股東分潤
					$u_power6_profit=round((float)$totalWinlose * $u6_percent,2);	//代理分潤


					if ($u_power4_profit<>$row['u_power4_profit']){
							$isupdate = true ;
							$updatestr .= ' wrong u4';
					}
					if ($u_power5_profit<>$row['u_power5_profit']){
							$isupdate = true ;
							$updatestr .= ' wrong u5';
					}
					if ($u_power6_profit<>$row['u_power6_profit']){
							$isupdate = true ;
							$updatestr .= ' wrong u6';
					}
					//需要更新
					if ($isupdate){

						$parameter=array();
						$colSql="mem_num,u_power4_profit,u_power5_profit,u_power6_profit,mod";
						$sqlStr="UPDATE `dreamgame_report` SET ".sqlUpdateString($colSql);
						$sqlStr.=" where id=?";
						$parameter[":mem_num"]=$mem_num.'  '.$row['mem_num'];
						$parameter[":u_power4_profit"]=$u_power4_profit.'  '.$row['u_power4_profit'];
						$parameter[":u_power5_profit"]=$u_power5_profit.'  '.$row['u_power5_profit'];
						$parameter[":u_power6_profit"]=$u_power6_profit.'  '.$row['u_power6_profit'];
						$parameter[":mod"]=1;
						$parameter[":id"]=$row['id'];
						echo '<br>'.$sqlStr ;
						echo '<br>'.$updatestr ;
						echo '<br>totalWinlose:'.$totalWinlose.'<br>' ;
						print_r($parameter);

						$this->webdb->sqlExc($sqlStr,$parameter);

					}
					}

		}


		
	}
	public function correct (){
		$cor_date = $this->input->post("cor_date");
		$this->data["formAction"]=site_url("/report/dgmanual/correct_page2");

		$sqlStr = 'select * from dreamgame_report where betTime >= "'.$cor_date.' 00:00:00" anb betTime <= "'.$cor_date.' 23:59:59" order by userName';
		$this->data['sql']= $sqlStr ;

		//$this -> load -> view("www/dgmanual2.php", $this -> data);
		$this -> load -> view("www/dgmanual2.php", $this -> data);


	}

	public function create(){
		$str = $jsonstring;
		$str = $this->input->post("demo");
		//$str = '';
		//$str = '{"action": "create","record": {"type": "n$product","fields": {"n$name": "Bread","n$price": 2.11},"namespaces": { "my.demo": "n" }}}';

		echo $str ;
			$array = json_decode($str, true);
			//var_dump($array);
			//echo "<pre>";
			//echo '<br>arr:'.$array ;
			//print_r($array);
			//echo "</pre>";
			$i = 0 ;
			foreach($array as $row){
				echo '<pre>';
				print_r($row).'<br>';
				echo '</pre>';
//7pk

				$sqlStr="select mem_num from `games_account` where u_id='".$row[Account]."' and gamemaker_num=22";	
				$row_mem=$this->webdb->sqlRow($sqlStr);
				$mem_num=$row_mem["mem_num"];	//取出會員編號
				$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
				$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
				$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
				
				
				$winOrLoss=(float)$row[Payoff]-(float)$row[BetAmount];
				
				$u_power4_profit=round($winOrLoss * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
				$u_power5_profit=round($winOrLoss * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
				$u_power6_profit=round($winOrLoss * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
				
				
				$parameter=array();
				$colSql="WagersID,ID,Account,WagersDate,BetAmount,Payoff,winOrLoss";
				$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
				$sqlStr="Replace INTO `7pk_report` (".sqlInsertString($colSql,0).")";
				$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
				$parameter['WagersID']=$row[WagersID];
				$parameter['ID']=$row[ID];
				$parameter['Account']=$row[Account];
				$parameter['WagersDate']=$row[WagersDate];
				$parameter['BetAmount']=$row[BetAmount];
				$parameter['Payoff']=$row[Payoff];
				$parameter['winOrLoss']=$winOrLoss;
				$parameter['mem_num']=$mem_num;
				$parameter['u_power4']=$u_power4;
				$parameter['u_power5']=$u_power5;
				$parameter['u_power6']=$u_power6;
				$parameter['u_power4_profit']=$u_power4_profit;
				$parameter['u_power5_profit']=$u_power5_profit;
				$parameter['u_power6_profit']=$u_power6_profit;	
				
				
				//$this->webdb->sqlReplace('7pk_report',$parameter);		
				echo $row[WagersID].'<--<br><pre>' ;
				//print_r($parameter).'</pre><br>' ;

				$i ++ ;
				echo $i;		
				$this->webdb->sqlExc($sqlStr,$parameter);


/* dg
					//print_r($row);
					$sqlStr="select `id` from `dreamgame_report` where `id`=?";	
					$rowrecord=$this->webdb->sqlRow($sqlStr,array(':id'=>$row['id']));
					echo $rowrecord['id'] ;
					if ($rowrecord == null)	{
						echo '$i:'.$i ;
					$sqlStr="select mem_num from `games_account` where u_id='".$row->userName."' and gamemaker_num=12";	
					$row_mem=$this->webdb->sqlRow($sqlStr);
					$mem_num=$row_mem["mem_num"];	//取出會員編號
					$u_power6=tb_sql("admin_num",'member',$mem_num);	//代理編號
					$u_power5=tb_sql("root",'admin',$u_power6);	//總代編號
					$u_power4=tb_sql("root",'admin',$u_power5);	//股東編號
					
					
					$totalWinlose=(float)$row['winOrLoss'] - (float)$row['betPoints'];
					$u_power4_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power4) / 100),2);	//股東分潤
					$u_power5_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power5) / 100),2);	//股東分潤
					$u_power6_profit=round((float)$totalWinlose * (tb_sql('percent','admin',$u_power6) / 100),2);	//代理分潤
										//echo 'ResultAmount：'.$row->ResultAmount.'，u_power4_profit：'.$u_power4_profit.'<br>';
					//echo '被樹：'.$this->get_percent($u_power4,$u_power5).'<br>';
					
					//有效投注額, 基本上可以從返回的 ResultAmount 判斷. 如果 ResultAmount = 0, 即和或退回投注, 那有效投注就是0, 否則, 有效投注就是 BetAmount 了
					$ValidAmount=($row->availableBet==0 ? $row->availableBet : $row->availableBet);	
					




					$parameter=array();

					$colSql="id,tableId,shoeId,playId,lobbyId,gameType,gameId,memberId,parentId,betTime";
					$colSql.=",calTime,winOrLoss,totalWinlose,balanceBefore,betPoints,betPointsz,availableBet";
					$colSql.=",userName,result,betDetail,ip,ext,isRevocation,currencyId,deviceType";
					$colSql.=",mem_num,u_power4,u_power5,u_power6,u_power4_profit,u_power5_profit,u_power6_profit";
					$sqlStr="REPLACE INTO `dreamgame_report` (".sqlInsertString($colSql,0).")";
					$sqlStr.=" VALUES (".sqlInsertString($colSql,1).")";
					$parameter[':id']=$row['id'];
					$parameter[':tableId']=$row['tableId'];
					echo "<br>id:".$row['id'].'('.$row['betTime'].')<br>';
					$parameter[':shoeId']=$row['shoeId'];
					$parameter[':playId']=$row['playId'];
					$parameter[':tableId']=$row['tableId'];
					$parameter[':lobbyId']=$row['lobbyId'];
					$parameter[':gameType']=$row['gameType'];
					$parameter[':gameId']=$row['gameId'];
					$parameter[':memberId']=$row['memberId'];
					$parameter[':parentId']=$row['parentId'];

					$parameter[':betTime']=date('Y-m-d H:i:s',strtotime($row['betTime']));
					$parameter[':calTime']=date('Y-m-d H:i:s',strtotime($row['calTime']));
					$parameter[':winOrLoss']=$row['winOrLoss'];
					$parameter[':totalWinlose']=$totalWinlose;
					$parameter[':balanceBefore']=$row['balanceBefore'];
					$parameter[':betPoints']=$row['betPoints'];
					$parameter[':betPointsz']=$row['betPointsz'];
					$parameter[':availableBet']=$row['availableBet'];


					$parameter[':userName']=$row['userName'];
					$parameter[':result']=(string)$row['result'];
					//echo 'result:'.$row['$result ;
					$parameter[':betDetail']=$row['betDetail'];
					$parameter[':ip']=$row['ip'];
					$parameter[':ext']=$row['ext'];
					$parameter[':isRevocation']=$row['isRevocation'];
					$parameter[':currencyId']=$row['currencyId'];
					$parameter[':deviceType']=$row['deviceType'];
					
					$parameter[':mem_num']=$mem_num;


					$parameter[':u_power4']=$u_power4;
					$parameter[':u_power5']=$u_power5;
					$parameter[':u_power6']=$u_power6;
					$parameter[':u_power4_profit']=$u_power4_profit;
					$parameter[':u_power5_profit']=$u_power5_profit;
					$parameter[':u_power6_profit']=$u_power6_profit;					
					$this->webdb->sqlExc($sqlStr,$parameter);
					//echo $sqlStr ;
					array_push($idlist, $row->id);
					$i++ ; 
					if ($i == 200){
						break ;
					}

				}else{ echo '已有紀錄<br>';}
*/

			}




	}
	
	//手動補帳用
	public function index(){
		$this->data["formAction"]=site_url("/report/dgmanual/create");
		//$startDate = '2017-05-31 00:00:00';

/*
		$startDate =date("Y-m-d H:i:s",strtotime("+9 day",strtotime("2017-05-31 00:00:00")));
		//$endDate = '2017-05-31 23:59:59';
		$endDate =date("Y-m-d H:i:s",strtotime("+9 day",strtotime("2017-05-31 23:59:59")));
		$sql = 'select * from dreamgame_report where betTime >= "'.$startDate.'" and betTime <= "'.$endDate.'"';
		$sql2 = "select sum(totalwinLose) as total from dreamgame_report where betTime>='".$startDate."' and betTime <= '".$endDate."' ";
		$output = $this->db->query ($sql);
		$this->data["cal"] = $output->num_rows() ;
		$this->data["sql"] = $sql ;
		$this->data["sql2"] = $sql2 ;
		$a = $this->db->query($sql2) ;
		$b = $a->result_array();
		print_r($b);

					$x = 0.0 ; 
					$y = 300.0 ;
					$this->data['totalWinlose']=(float)$x - (float)$y;

		
		$this->data['num'] = $b[0]['total']; 
*/
		$this -> load -> view("www/dgmanual", $this -> data);

		//$this -> load -> view("www/dgmanual.php", $this -> data);
	}
	
}

?>