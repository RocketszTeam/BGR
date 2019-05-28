<?php
include_once (dirname(__FILE__)."/Core_model.php");

class Site_analytic_model extends Core_model {
	protected $table;
	
	function __construct(){ 
        parent::__construct();
    }
	
	//選出儲值最高的
	public function order_max($find7,$find8,$root_num,$kind_web_root_num){
		$order_max = $this->db->query("select mem_num,sum(amount) as sum_amount from orders where keyin2=1 and is_received=1 and buildtime 
		between '".$find7." 00:00:00' and '".$find8." 23:59:59' and admin_num in (".$root_num.$kind_web_root_num.") group BY mem_num order by sum_amount desc limit 0,10"); 
		return $order_max->result_array();
	
		
	}
	
	//選出拋售最高的
	public function member_sell_max($find7,$find8,$root_num,$kind_web_root_num){
		$member_sell_max = $this->db->query("select mem_num,sum(amount) as su_mount from `member_sell` where buildtime 
		BETWEEN '".$find7." 00:00:00' and '".$find8." 23:59:59' and `keyin1`=1 and admin_num in (".$root_num.$kind_web_root_num.") GROUP BY mem_num order by `su_mount` desc LIMIT 0,10");
		return $member_sell_max->result_array();
		
	}
	
	
	//(拋售/儲值)*100
	public function order_sell($find7,$find8,$root_num,$kind_web_root_num){
		$member_num = $this->db->query("select num,u_name,
			IFNULL((
				select sum(amount) from orders 
					where mem_num=m.num and keyin2=1 and is_received=1 
					and buildtime between '".$find7."' and '".$find8."' 
				   
				 and admin_num in (".$root_num.$kind_web_root_num.") 
				   
			group BY mem_num),0) as sord,
			ifnull((
					select sum(amount) from member_sell 
						where mem_num=m.num and keyin1=1 and buildtime between '".$find7."' and '".$find8."' and admin_num in (".$root_num.$kind_web_root_num.")
							group BY mem_num),0) as smem,
			IFNULL((
					select sum(amount) from member_bank_transfer 
						where mem_num=m.num and keyin2=1 and is_received=1 and buildtime between '".$find7."' and '".$find8."' and admin_num in (".$root_num.$kind_web_root_num.")
							group BY mem_num),0) as smbt
			from member as m
			order by sord desc LIMIT 0,10
			");
		
		$member_num_all = $member_num->result_array();
		//var_dump($member_num_all);
		$i=0;
		$order_sell_percent = array();
		foreach($member_num_all as $row){
			$sord_smbt = $row['smbt']+$row['sord'];
			$order_sell_percent[$i]['num'] = $row['num'];
			$order_sell_percent[$i]['u_name'] = $row['u_name'];
			$order_sell_percent[$i]['smem'] = $row['smem'];
			$order_sell_percent[$i]['sord'] = $row['sord'];
			$order_sell_percent[$i]['smbt'] = $sord_smbt;
			//$order_sell_percent[$i]['sell_percent_all_ok'] = ($row['smem']/$sord_smbt)*100;
			$order_sell_percent[$i]['sell_percent_all_ok'] = ($sord_smbt !==0 )? ($row['smem']/$sord_smbt)*100 : 0;
			$i++;
		}
		return $order_sell_percent;
		
		
	}
	
	//全館遊戲點擊報表
	public function game_hit($find7,$find8){
		
		$game_hit_str = $this->db->query("select makers_num,makers_name,count(makers_name) as gameTimes from game_hit where buildtime between '".$find7."' and '".$find8."' group by makers_num;");
		return $game_hit_str->result_array();
		
		
		
	}
	
	//北京賽車 輸贏計算 勝場 輸場 計算
	public function k168_win($find7,$find8,$web_root_num,$web_root_u_power){
		
		$k168_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(winOrLoss) as wOrL,
			(select count(result) from 9k168_report where mem_num=9k.mem_num and Result ='W' and WagerDate Between '".$find7."' and '".$find8."') as w_times,
			(select count(result) from 9k168_report where mem_num=9k.mem_num and Result ='L' and WagerDate Between '".$find7."' and '".$find8."') as L_times,u_power4,u_power5,u_power6 from 9k168_report as 9k
			where WagerDate Between '".$find7."' and '".$find8."' GROUP BY mem_num ORDER BY wOrL desc");
		
		$k168_str_all = $k168_str->result_array();
		//var_dump($k168_str->result_array());
		//return $k168_str->result_array();
		
		//var_dump($k168_str_all);
		$k168_str_345=array();
		$k168_str_1 =array();
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6" ){
			foreach($k168_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$k168_str_345[$i]['mem_num'] = $row['mem_num'];
					$k168_str_345[$i]['m_count'] = $row['m_count'];
					$k168_str_345[$i]['wOrL'] = $row['wOrL'];
					$k168_str_345[$i]['w_times'] = $row['w_times'];
					$k168_str_345[$i]['L_times'] = $row['L_times'];
					$k168_str_345[$i]['u_power4'] = $row['u_power4'];
					$k168_str_345[$i]['u_power5'] = $row['u_power5'];
					$k168_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $k168_str_345;
		}else{
			
			foreach($k168_str_all as $row){
				if($i<15){
					$k168_str_1[$i]['mem_num'] = $row['mem_num'];
					$k168_str_1[$i]['m_count'] = $row['m_count'];
					$k168_str_1[$i]['wOrL'] = $row['wOrL'];
					$k168_str_1[$i]['w_times'] = $row['w_times'];
					$k168_str_1[$i]['L_times'] = $row['L_times'];
					$k168_str_1[$i]['u_power4'] = $row['u_power4'];
					$k168_str_1[$i]['u_power5'] = $row['u_power5'];
					$k168_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $k168_str_1;
		}
		
	}
	
	
	//北京賽車玩家人數計算
	public function k168_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$k168_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from 9k168_report where u_power".$web_root_u_power."='".$web_root_num."' and WagerDate BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$k168_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from 9k168_report where WagerDate BETWEEN '".$find7."' and '".$find8."';");

		}
		return $k168_player_count_str->row_array();
		
	}
	
	
	//super體育 輸贏計算 勝場 輸場 計算
	public function super_win($find7,$find8,$web_root_num,$web_root_u_power){
		$super_win = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(result_gold) as wOrL,
			(select count(result_gold) from super_report where mem_num=su.mem_num and result_gold > 0 and m_date Between '".$find7."' and '".$find8."') as w_times,
			(select count(result_gold) from super_report where mem_num=su.mem_num and result_gold <= 0 and m_date Between '".$find7."' and '".$find8."') as L_times,u_power4,u_power5,u_power6 from super_report as su
			where m_date Between '".$find7."' and '".$find8."' GROUP BY mem_num ORDER BY wOrL desc");
			
		$super_str_all = $super_win->result_array();
		$super_str_345 = array();
		$super_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($super_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$super_str_345[$i]['mem_num'] = $row['mem_num'];
					$super_str_345[$i]['m_count'] = $row['m_count'];
					$super_str_345[$i]['wOrL'] = $row['wOrL'];
					$super_str_345[$i]['w_times'] = $row['w_times'];
					$super_str_345[$i]['L_times'] = $row['L_times'];
					$super_str_345[$i]['u_power4'] = $row['u_power4'];
					$super_str_345[$i]['u_power5'] = $row['u_power5'];
					$super_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $super_str_345;
		}else{
			foreach($super_str_all as $row){
				if($i<15 ){
					$super_str_1[$i]['mem_num'] = $row['mem_num'];
					$super_str_1[$i]['m_count'] = $row['m_count'];
					$super_str_1[$i]['wOrL'] = $row['wOrL'];
					$super_str_1[$i]['w_times'] = $row['w_times'];
					$super_str_1[$i]['L_times'] = $row['L_times'];
					$super_str_1[$i]['u_power4'] = $row['u_power4'];
					$super_str_1[$i]['u_power5'] = $row['u_power5'];
					$super_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $super_str_1;
		}
		
		
	}
	
	//super體育 玩家人數計算
	public function super_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$super_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from super_report where u_power".$web_root_u_power."='".$web_root_num."' and m_date BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$super_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from super_report where m_date BETWEEN '".$find7."' and '".$find8."';");

		}	
		return $super_player_count_str->row_array();
	}


	//贏家體育 輸贏計算 勝場 輸場 計算
	public function ssb_win($find7_s,$find8_s,$web_root_num,$web_root_u_power){
		$find7 = date('Ymd',strtotime($find7_s));
		$find8 = date('Ymd',strtotime($find8_s));
		
		$ssb_win = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(meresult) as wOrL,
(select count(meresult) from ssb_report where mem_num=ssb.mem_num and meresult > 0 and orderdate Between '".$find7."' and '".$find8."') as w_times,
(select count(meresult) from ssb_report where mem_num=ssb.mem_num and meresult <= 0 and orderdate Between '".$find7."' and '".$find8."') as L_times,
u_power4,u_power5,u_power6 from ssb_report as ssb
where orderdate Between '".$find7."' and '".$find8."' 
GROUP BY mem_num ORDER BY wOrL desc");
			
		$ssb_str_all = $ssb_win->result_array();
		$ssb_str_345 = array();
		$ssb_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($ssb_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$ssb_str_345[$i]['mem_num'] = $row['mem_num'];
					$ssb_str_345[$i]['m_count'] = $row['m_count'];
					$ssb_str_345[$i]['wOrL'] = $row['wOrL'];
					$ssb_str_345[$i]['w_times'] = $row['w_times'];
					$ssb_str_345[$i]['L_times'] = $row['L_times'];
					$ssb_str_345[$i]['u_power4'] = $row['u_power4'];
					$ssb_str_345[$i]['u_power5'] = $row['u_power5'];
					$ssb_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $ssb_str_345;
		}else{
			foreach($ssb_str_all as $row){
				if($i<15 ){
					$ssb_str_1[$i]['mem_num'] = $row['mem_num'];
					$ssb_str_1[$i]['m_count'] = $row['m_count'];
					$ssb_str_1[$i]['wOrL'] = $row['wOrL'];
					$ssb_str_1[$i]['w_times'] = $row['w_times'];
					$ssb_str_1[$i]['L_times'] = $row['L_times'];
					$ssb_str_1[$i]['u_power4'] = $row['u_power4'];
					$ssb_str_1[$i]['u_power5'] = $row['u_power5'];
					$ssb_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $ssb_str_1;
		}
		
		
	}
	
	//贏家體育 玩家人數計算
	public function ssb_player_count($find7_s,$find8_s,$web_root_num,$web_root_u_power){
		$find7 = date('Ymd',strtotime($find7_s));
		$find8 = date('Ymd',strtotime($find8_s));
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$super_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from ssb_report where u_power".$web_root_u_power."='".$web_root_num."' and orderdate BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$super_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from ssb_report where  orderdate BETWEEN '".$find7."' and '".$find8."';");
		}	
		return $super_player_count_str->row_array();
	}	




	
	
	//沙龍 輸贏計算 勝場 輸場 計算
	public function sa_win($find7,$find8,$web_root_num,$web_root_u_power){
		$sa_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(ResultAmount) as wOrL,
			(select count(ResultAmount) from sagame_report where mem_num=sa.mem_num and ResultAmount > 0 and PayoutTime Between '".$find7."' and '".$find8."') as w_times,
			(select count(ResultAmount) from sagame_report where mem_num=sa.mem_num and ResultAmount <= 0 and PayoutTime Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from sagame_report as sa
			where PayoutTime Between '".$find7."' and '".$find8."' 
			GROUP BY mem_num ORDER BY wOrL desc");

		$sa_str_all = $sa_win_str->result_array();
		$sa_str_345 = array();
		$sa_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($sa_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$sa_str_345[$i]['mem_num'] = $row['mem_num'];
					$sa_str_345[$i]['m_count'] = $row['m_count'];
					$sa_str_345[$i]['wOrL'] = $row['wOrL'];
					$sa_str_345[$i]['w_times'] = $row['w_times'];
					$sa_str_345[$i]['L_times'] = $row['L_times'];
					$sa_str_345[$i]['u_power4'] = $row['u_power4'];
					$sa_str_345[$i]['u_power5'] = $row['u_power5'];
					$sa_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $sa_str_345;
		}else{
			foreach($sa_str_all as $row){
				if($i<15 ){
					$sa_str_1[$i]['mem_num'] = $row['mem_num'];
					$sa_str_1[$i]['m_count'] = $row['m_count'];
					$sa_str_1[$i]['wOrL'] = $row['wOrL'];
					$sa_str_1[$i]['w_times'] = $row['w_times'];
					$sa_str_1[$i]['L_times'] = $row['L_times'];
					$sa_str_1[$i]['u_power4'] = $row['u_power4'];
					$sa_str_1[$i]['u_power5'] = $row['u_power5'];
					$sa_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $sa_str_1;
			
		}
		
		
	}
	
	//沙龍 玩家人數計算
	public function sa_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$sa_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from sagame_report where u_power".$web_root_u_power."='".$web_root_num."' and PayoutTime BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$sa_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from sagame_report where PayoutTime BETWEEN '".$find7."' and '".$find8."';");
			
		}
		return $sa_player_count_str->row_array();
	}
	
	
	//DG真人 輸贏計算 勝場 輸場 計算
	public function dg_win($find7,$find8,$web_root_num,$web_root_u_power){
		$dg_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(totalWinlose) as wOrL,
			(select count(totalWinlose) from dreamgame_report where mem_num=dg.mem_num and totalWinlose > 0 and betTime Between '".$find7."' and '".$find8."') as w_times,
			(select count(totalWinlose) from dreamgame_report where mem_num=dg.mem_num and totalWinlose <= 0 and betTime Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from dreamgame_report as dg
			where betTime Between '".$find7."' and '".$find8."' 
			GROUP BY mem_num ORDER BY wOrL desc");

		$dg_str_all = $dg_win_str->result_array();
		$dg_str_345 = array();
		$dg_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($dg_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$dg_str_345[$i]['mem_num'] = $row['mem_num'];
					$dg_str_345[$i]['m_count'] = $row['m_count'];
					$dg_str_345[$i]['wOrL'] = $row['wOrL'];
					$dg_str_345[$i]['w_times'] = $row['w_times'];
					$dg_str_345[$i]['L_times'] = $row['L_times'];
					$dg_str_345[$i]['u_power4'] = $row['u_power4'];
					$dg_str_345[$i]['u_power5'] = $row['u_power5'];
					$dg_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $dg_str_345;
		}else{
			foreach($dg_str_all as $row){
				if($i<15 ){
					$dg_str_1[$i]['mem_num'] = $row['mem_num'];
					$dg_str_1[$i]['m_count'] = $row['m_count'];
					$dg_str_1[$i]['wOrL'] = $row['wOrL'];
					$dg_str_1[$i]['w_times'] = $row['w_times'];
					$dg_str_1[$i]['L_times'] = $row['L_times'];
					$dg_str_1[$i]['u_power4'] = $row['u_power4'];
					$dg_str_1[$i]['u_power5'] = $row['u_power5'];
					$dg_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $dg_str_1;
			
		}

	}
	
	
	//DG真人 玩家人數計算
	public function dg_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$dg_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from dreamgame_report where u_power".$web_root_u_power."='".$web_root_num."' and betTime BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$dg_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from dreamgame_report where betTime BETWEEN '".$find7."' and '".$find8."';");
		}
		return $dg_player_count_str->row_array();
		
	}
	
	
	//歐博真人  輸贏計算 勝場 輸場 計算
	public function ab_win($find7,$find8,$web_root_num,$web_root_u_power){
		$ab_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(winOrLoss) as wOrL,
			(select count(winOrLoss) from allbet_report where mem_num=ab.mem_num and winOrLoss > 0 and betTime Between '".$find7."' and '".$find8."') as w_times,
			(select count(winOrLoss) from allbet_report where mem_num=ab.mem_num and winOrLoss <= 0 and betTime Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from allbet_report as ab
			where betTime Between '".$find7."' and '".$find8."' 
			GROUP BY mem_num ORDER BY wOrL desc");
			
		$ab_str_all = $ab_win_str->result_array();
		$ab_str_345 = array();
		$ab_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($ab_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$ab_str_345[$i]['mem_num'] = $row['mem_num'];
					$ab_str_345[$i]['m_count'] = $row['m_count'];
					$ab_str_345[$i]['wOrL'] = $row['wOrL'];
					$ab_str_345[$i]['w_times'] = $row['w_times'];
					$ab_str_345[$i]['L_times'] = $row['L_times'];
					$ab_str_345[$i]['u_power4'] = $row['u_power4'];
					$ab_str_345[$i]['u_power5'] = $row['u_power5'];
					$ab_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $ab_str_345;
		}else{
			foreach($ab_str_all as $row){
				if($i<15 ){
					$ab_str_1[$i]['mem_num'] = $row['mem_num'];
					$ab_str_1[$i]['m_count'] = $row['m_count'];
					$ab_str_1[$i]['wOrL'] = $row['wOrL'];
					$ab_str_1[$i]['w_times'] = $row['w_times'];
					$ab_str_1[$i]['L_times'] = $row['L_times'];
					$ab_str_1[$i]['u_power4'] = $row['u_power4'];
					$ab_str_1[$i]['u_power5'] = $row['u_power5'];
					$ab_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $ab_str_1;
			
		}
		
		
	}
	
	
	//歐博真人 玩家人數計算
	public function ab_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$ab_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from allbet_report where u_power".$web_root_u_power."='".$web_root_num."' and betTime BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$ab_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from allbet_report where betTime BETWEEN '".$find7."' and '".$find8."';");
		}
		return $ab_player_count_str->row_array();
		
	}
	
	
	
	//ameba  輸贏計算 勝場 輸場 計算
	public function ameba_win($find7,$find8,$web_root_num,$web_root_u_power){
		$ameba_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(winOrLoss) as wOrL,
			(select count(winOrLoss) from ameba_report where mem_num=ame.mem_num and winOrLoss > 0 and WagerDate Between '".$find7."' and '".$find8."') as w_times,
			(select count(winOrLoss) from ameba_report where mem_num=ame.mem_num and winOrLoss <= 0 and WagerDate Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from ameba_report as ame
			where WagerDate Between '".$find7."' and '".$find8."' 
			GROUP BY mem_num ORDER BY wOrL desc;");
			
		$ameba_str_all = $ameba_win_str->result_array();
		$ameba_str_345 = array();
		$ameba_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($ameba_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$ameba_str_345[$i]['mem_num'] = $row['mem_num'];
					$ameba_str_345[$i]['m_count'] = $row['m_count'];
					$ameba_str_345[$i]['wOrL'] = $row['wOrL'];
					$ameba_str_345[$i]['w_times'] = $row['w_times'];
					$ameba_str_345[$i]['L_times'] = $row['L_times'];
					$ameba_str_345[$i]['u_power4'] = $row['u_power4'];
					$ameba_str_345[$i]['u_power5'] = $row['u_power5'];
					$ameba_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $ameba_str_345;
		}else{
			foreach($ameba_str_all as $row){
				if($i<15 ){
					$ameba_str_1[$i]['mem_num'] = $row['mem_num'];
					$ameba_str_1[$i]['m_count'] = $row['m_count'];
					$ameba_str_1[$i]['wOrL'] = $row['wOrL'];
					$ameba_str_1[$i]['w_times'] = $row['w_times'];
					$ameba_str_1[$i]['L_times'] = $row['L_times'];
					$ameba_str_1[$i]['u_power4'] = $row['u_power4'];
					$ameba_str_1[$i]['u_power5'] = $row['u_power5'];
					$ameba_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $ameba_str_1;
			
		}
		
		
	}
	
	
	//ameba 玩家人數計算
	public function ameba_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$ameba_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from ameba_report where u_power".$web_root_u_power."='".$web_root_num."' and WagerDate BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$ameba_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from ameba_report where WagerDate BETWEEN '".$find7."' and '".$find8."';");
		}
		return $ameba_player_count_str->row_array();
		
	}
	
	
	
	//7pk  輸贏計算 勝場 輸場 計算
	public function pk_win($find7,$find8,$web_root_num,$web_root_u_power){
		$pk_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(winOrLoss) as wOrL,
			(select count(winOrLoss) from 7pk_report where mem_num=pk.mem_num and winOrLoss > 0 and WagersDate Between '".$find7."' and '".$find8."') as w_times,
			(select count(winOrLoss) from 7pk_report where mem_num=pk.mem_num and winOrLoss <= 0 and WagersDate Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from 7pk_report as pk
			where WagersDate Between '".$find7."' and '".$find8."' 
			GROUP BY mem_num ORDER BY wOrL desc;");
			
		$pk_str_all = $pk_win_str->result_array();
		$pk_str_345 = array();
		$pk_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($pk_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$pk_str_345[$i]['mem_num'] = $row['mem_num'];
					$pk_str_345[$i]['m_count'] = $row['m_count'];
					$pk_str_345[$i]['wOrL'] = $row['wOrL'];
					$pk_str_345[$i]['w_times'] = $row['w_times'];
					$pk_str_345[$i]['L_times'] = $row['L_times'];
					$pk_str_345[$i]['u_power4'] = $row['u_power4'];
					$pk_str_345[$i]['u_power5'] = $row['u_power5'];
					$pk_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $pk_str_345;
		}else{
			foreach($pk_str_all as $row){
				if($i<15 ){
					$pk_str_1[$i]['mem_num'] = $row['mem_num'];
					$pk_str_1[$i]['m_count'] = $row['m_count'];
					$pk_str_1[$i]['wOrL'] = $row['wOrL'];
					$pk_str_1[$i]['w_times'] = $row['w_times'];
					$pk_str_1[$i]['L_times'] = $row['L_times'];
					$pk_str_1[$i]['u_power4'] = $row['u_power4'];
					$pk_str_1[$i]['u_power5'] = $row['u_power5'];
					$pk_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $pk_str_1;
			
		}
		
		
	}
	
	
	//7pk 玩家人數計算
	public function pk_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$pk_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from 7pk_report where u_power".$web_root_u_power."='".$web_root_num."' and WagersDate BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$pk_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from 7pk_report where WagersDate BETWEEN '".$find7."' and '".$find8."';");
		}
		return $pk_player_count_str->row_array();
		
	}
	
	
	
	//qt  輸贏計算 勝場 輸場 計算
	public function qt_win($find7,$find8,$web_root_num,$web_root_u_power){
		$qt_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(totalWinlose) as wOrL,
			(select count(totalWinlose) from qtech_report where mem_num=qt.mem_num and totalWinlose > 0 and initiated Between '".$find7."' and '".$find8."') as w_times,
			(select count(totalWinlose) from qtech_report where mem_num=qt.mem_num and totalWinlose <= 0 and initiated Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from qtech_report as qt
			where initiated Between '".$find7."' and '".$find8."' and status ='COMPLETED'
			GROUP BY mem_num ORDER BY wOrL desc;");
			
		$qt_str_all = $qt_win_str->result_array();
		$qt_str_345 = array();
		$qt_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($qt_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$qt_str_345[$i]['mem_num'] = $row['mem_num'];
					$qt_str_345[$i]['m_count'] = $row['m_count'];
					$qt_str_345[$i]['wOrL'] = $row['wOrL'];
					$qt_str_345[$i]['w_times'] = $row['w_times'];
					$qt_str_345[$i]['L_times'] = $row['L_times'];
					$qt_str_345[$i]['u_power4'] = $row['u_power4'];
					$qt_str_345[$i]['u_power5'] = $row['u_power5'];
					$qt_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $qt_str_345;
		}else{
			foreach($qt_str_all as $row){
				if($i<15 ){
					$qt_str_1[$i]['mem_num'] = $row['mem_num'];
					$qt_str_1[$i]['m_count'] = $row['m_count'];
					$qt_str_1[$i]['wOrL'] = $row['wOrL'];
					$qt_str_1[$i]['w_times'] = $row['w_times'];
					$qt_str_1[$i]['L_times'] = $row['L_times'];
					$qt_str_1[$i]['u_power4'] = $row['u_power4'];
					$qt_str_1[$i]['u_power5'] = $row['u_power5'];
					$qt_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $qt_str_1;
			
		}
		
		
	}
	
	
	//qt 玩家人數計算
	public function qt_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$qt_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from qtech_report where u_power".$web_root_u_power."='".$web_root_num."' and initiated BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$qt_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from qtech_report where initiated BETWEEN '".$find7."' and '".$find8."';");
		}
		return $qt_player_count_str->row_array();
		
	}
	
	
	
	//super彩球  輸贏計算 勝場 輸場 計算
	public function slottery_win($find7,$find8,$web_root_num,$web_root_u_power){
		$slottery_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(m_result) as wOrL,
			(select count(m_result) from slottery_report where mem_num=slottery.mem_num and m_result > 0 and Bet_date Between '".$find7."' and '".$find8."') as w_times,
			(select count(m_result) from slottery_report where mem_num=slottery.mem_num and m_result <= 0 and Bet_date Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from slottery_report as slottery
			where Bet_date Between '".$find7."' and '".$find8."'
			GROUP BY mem_num ORDER BY wOrL desc;");
			
		$slottery_str_all = $slottery_win_str->result_array();
		$slottery_str_345 = array();
		$slottery_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($slottery_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$slottery_str_345[$i]['mem_num'] = $row['mem_num'];
					$slottery_str_345[$i]['m_count'] = $row['m_count'];
					$slottery_str_345[$i]['wOrL'] = $row['wOrL'];
					$slottery_str_345[$i]['w_times'] = $row['w_times'];
					$slottery_str_345[$i]['L_times'] = $row['L_times'];
					$slottery_str_345[$i]['u_power4'] = $row['u_power4'];
					$slottery_str_345[$i]['u_power5'] = $row['u_power5'];
					$slottery_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $slottery_str_345;
		}else{
			foreach($slottery_str_all as $row){
				if($i<15 ){
					$slottery_str_1[$i]['mem_num'] = $row['mem_num'];
					$slottery_str_1[$i]['m_count'] = $row['m_count'];
					$slottery_str_1[$i]['wOrL'] = $row['wOrL'];
					$slottery_str_1[$i]['w_times'] = $row['w_times'];
					$slottery_str_1[$i]['L_times'] = $row['L_times'];
					$slottery_str_1[$i]['u_power4'] = $row['u_power4'];
					$slottery_str_1[$i]['u_power5'] = $row['u_power5'];
					$slottery_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $slottery_str_1;
			
		}
		
		
	}
	
	
	//super彩球 玩家人數計算
	public function slottery_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$slottery_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from slottery_report where u_power".$web_root_u_power."='".$web_root_num."' and Bet_date BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$slottery_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from slottery_report where Bet_date BETWEEN '".$find7."' and '".$find8."';");
		}
		return $slottery_player_count_str->row_array();
		
	}
	
	
	
	//fish捕魚  輸贏計算 勝場 輸場 計算
	public function fish_win($find7,$find8,$web_root_num,$web_root_u_power){
		$fish_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(profit) as wOrL,
			(select count(profit) from fish_report where mem_num=fish.mem_num and profit > 0 and bettimeStr Between '".$find7."' and '".$find8."') as w_times,
			(select count(profit) from fish_report where mem_num=fish.mem_num and profit <= 0 and bettimeStr Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from fish_report as fish
			where bettimeStr Between '".$find7."' and '".$find8."'
			GROUP BY mem_num ORDER BY wOrL desc;");
			
		$fish_str_all = $fish_win_str->result_array();
		$fish_str_345 = array();
		$fish_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($fish_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$fish_str_345[$i]['mem_num'] = $row['mem_num'];
					$fish_str_345[$i]['m_count'] = $row['m_count'];
					$fish_str_345[$i]['wOrL'] = $row['wOrL'];
					$fish_str_345[$i]['w_times'] = $row['w_times'];
					$fish_str_345[$i]['L_times'] = $row['L_times'];
					$fish_str_345[$i]['u_power4'] = $row['u_power4'];
					$fish_str_345[$i]['u_power5'] = $row['u_power5'];
					$fish_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $fish_str_345;
		}else{
			foreach($fish_str_all as $row){
				if($i<15 ){
					$fish_str_1[$i]['mem_num'] = $row['mem_num'];
					$fish_str_1[$i]['m_count'] = $row['m_count'];
					$fish_str_1[$i]['wOrL'] = $row['wOrL'];
					$fish_str_1[$i]['w_times'] = $row['w_times'];
					$fish_str_1[$i]['L_times'] = $row['L_times'];
					$fish_str_1[$i]['u_power4'] = $row['u_power4'];
					$fish_str_1[$i]['u_power5'] = $row['u_power5'];
					$fish_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $fish_str_1;
			
		}
		
		
	}
	
	
	//fish捕魚 玩家人數計算
	public function fish_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$fish_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from fish_report where u_power".$web_root_u_power."='".$web_root_num."' and bettimeStr BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$fish_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from fish_report where bettimeStr BETWEEN '".$find7."' and '".$find8."';");
		}
		return $fish_player_count_str->row_array();
		
	}
	
	
	
	//bingo  輸贏計算 勝場 輸場 計算
	public function bingo_win($find7,$find8,$web_root_num,$web_root_u_power){
		$bingo_win_str = $this->db->query("SELECT mem_num as mem_num,count(mem_num) as m_count,sum(win_lose) as wOrL,
			(select count(win_lose) from bingo_report where mem_num=bingo.mem_num and win_lose > 0 and created_at Between '".$find7."' and '".$find8."') as w_times,
			(select count(win_lose) from bingo_report where mem_num=bingo.mem_num and win_lose <= 0 and created_at Between '".$find7."' and '".$find8."') as L_times,
			u_power4,u_power5,u_power6 from bingo_report as bingo
			where created_at Between '".$find7."' and '".$find8."'
			GROUP BY mem_num ORDER BY wOrL desc;");
			
		$bingo_str_all = $bingo_win_str->result_array();
		$bingo_str_345 = array();
		$bingo_str_1 = array();
		
		$i=0;
		if($web_root_u_power=="4" || $web_root_u_power=="5" || $web_root_u_power=="6"){
			
			foreach($bingo_str_all as $row){
				if($row['u_power'.$web_root_u_power] == $web_root_num && $i<10 ){
					$bingo_str_345[$i]['mem_num'] = $row['mem_num'];
					$bingo_str_345[$i]['m_count'] = $row['m_count'];
					$bingo_str_345[$i]['wOrL'] = $row['wOrL'];
					$bingo_str_345[$i]['w_times'] = $row['w_times'];
					$bingo_str_345[$i]['L_times'] = $row['L_times'];
					$bingo_str_345[$i]['u_power4'] = $row['u_power4'];
					$bingo_str_345[$i]['u_power5'] = $row['u_power5'];
					$bingo_str_345[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $bingo_str_345;
		}else{
			foreach($bingo_str_all as $row){
				if($i<15 ){
					$bingo_str_1[$i]['mem_num'] = $row['mem_num'];
					$bingo_str_1[$i]['m_count'] = $row['m_count'];
					$bingo_str_1[$i]['wOrL'] = $row['wOrL'];
					$bingo_str_1[$i]['w_times'] = $row['w_times'];
					$bingo_str_1[$i]['L_times'] = $row['L_times'];
					$bingo_str_1[$i]['u_power4'] = $row['u_power4'];
					$bingo_str_1[$i]['u_power5'] = $row['u_power5'];
					$bingo_str_1[$i]['u_power6'] = $row['u_power6'];
					$i++;
				}
				
			}
			return $bingo_str_1;
			
		}
		
		
	}
	
	
	//bingo 玩家人數計算
	public function bingo_player_count($find7,$find8,$web_root_num,$web_root_u_power){
		if($web_root_u_power == "4" || $web_root_u_power == "5" || $web_root_u_power == "6"){
			$bingo_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from bingo_report where u_power".$web_root_u_power."='".$web_root_num."' and created_at BETWEEN '".$find7."' and '".$find8."';");
		}else{
			$bingo_player_count_str = $this->db->query("select count(DISTINCT(mem_num)) as user from bingo_report where created_at BETWEEN '".$find7."' and '".$find8."';");
		}
		return $bingo_player_count_str->row_array();
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
