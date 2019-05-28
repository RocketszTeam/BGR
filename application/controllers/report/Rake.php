<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Rake extends CI_Controller{
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this->load->model('admin/Report_model','report');
	}
	
	public function index(){
		$sTime='2018-06-06 00:00:00';
		$eTime='2018-06-06 23:59:59';
		
		$this->countRake($sTime,$eTime);
	}
	
	public function countRake($sTime=NULL,$eTime=NULL){
		//更新今日
		if($sTime==NULL && $eTime==NULL){
			$sTime=date('Y-m-d',strtotime("-1 day"))." 00:00:00";
			$eTime=date('Y-m-d',strtotime("-1 day"))." 23:59:59";
		}
		
		$rake_mode=tb_sql('rake_mode','company',1);	//是否開啟會員返水
		$rake_winLimit=tb_sql('rake_winLimit','company',1);	//會員返水上限金額
		if(!$rake_mode) exit;	//沒開啟就不執行
		
		$dataList=array();
		$prefix=array();
		$count_date=date('Ymd',strtotime($sTime));

		$this->load->model('admin/Report_model', 'report');
		/*
		$gameList = rakegameList();
		foreach ($gameList as $gameCode => $gameInfo) {
			$rowAll = $this->report->{$gameInfo['gameModel']}($root, $_REQUEST["find7"], $_REQUEST["find8"], '', '', $showself);
			$dataList = $this->report->buildData($dataList, $rowAll, $gameCode, $gameInfo[0]);
			array_push($prefix, array($gameCode, $gameInfo['gameName']));
		}
		*/

		//歐伯
		$rowAll=$this->report->allbet_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'allbet');
		array_push($prefix,array('type'=>'live','makers'=>'allbet'));

//		//歐伯電子
//		$rowAll=$this->report->allbet_egame_rake($sTime,$eTime);
//		$dataList=$this->report->buildRake($dataList,$rowAll,'allbet_egame');
//		array_push($prefix,array('type'=>'egame','makers'=>'allbet_egame'));

        //dg
        $rowAll=$this->report->dg_rake($sTime,$eTime);
        $dataList=$this->report->buildRake($dataList,$rowAll,'dg');
        array_push($prefix,array('type'=>'live','makers'=>'dg'));

		//沙龍
		$rowAll=$this->report->sagame_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'sagame');
		array_push($prefix,array('type'=>'live','makers'=>'sagame'));
		
		//WM真人
		$rowAll=$this->report->wm_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'wm');
		array_push($prefix,array('type'=>'live','makers'=>'wm'));
		
		//super
		$rowAll=$this->report->super_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'super');
		array_push($prefix,array('type'=>'sport','makers'=>'super'));
		/*
		//捕魚機
		$rowAll=$this->report->fish_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'fish');
		array_push($prefix,array('type'=>'egame','makers'=>'fish'));
		
		
		//PG電子
		$rowAll=$this->report->pg_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'pg');
		array_push($prefix,array('type'=>'egame','makers'=>'pg'));
		
		//qt
		$rowAll=$this->report->qt_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'qt');
		array_push($prefix,array('type'=>'egame','makers'=>'qt'));

		//ssb贏家
		$rowAll=$this->report->ssb_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'ssb');
		array_push($prefix,array('type'=>'sport','makers'=>'ssb'));
		*/
		//7pk
		$rowAll=$this->report->s7pk_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'s7pk');
		array_push($prefix,array('type'=>'egame','makers'=>'s7pk'));
        /*
		//賓果賓果
		$rowAll=$this->report->bingo_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'bingo');
		array_push($prefix,array('type'=>'lottery','makers'=>'bingo'));

		//舊北京賽車
		$rowAll=$this->report->s9k168_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'s9k168');
		array_push($prefix,array('type'=>'lottery','makers'=>'s9k168'));

		//新北京賽車
		$rowAll=$this->report->pk10_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'pk10');
		array_push($prefix,array('type'=>'lottery','makers'=>'pk10'));
		
		//AMEBA
		$rowAll=$this->report->ameba_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'ameba');
		array_push($prefix,array('type'=>'egame','makers'=>'ameba'));
		*/
		//泛亞電競
		$rowAll=$this->report->avia_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'avia');
		array_push($prefix,array('type'=>'esport','makers'=>'avia'));
		/*
        //彩播
        $rowAll=$this->report->htpg_rake($sTime,$eTime);
        $dataList=$this->report->buildRake($dataList,$rowAll,'htpg');
        array_push($prefix,array('type'=>'lottery','makers'=>'htpg'));
		*/
		
		//RTG
        $rowAll=$this->report->rtg_rake($sTime,$eTime);
        $dataList=$this->report->buildRake($dataList,$rowAll,'rtg');
		array_push($prefix,array('type'=>'egame','makers'=>'rtg'));
		
		//EG
		$rowAll=$this->report->eg_rake($sTime,$eTime);
		$dataList=$this->report->buildRake($dataList,$rowAll,'eg');
		array_push($prefix,array('type'=>'egame','makers'=>'eg'));

        //新濠
        $rowAll=$this->report->cm_rake($sTime,$eTime);
        $dataList=$this->report->buildRake($dataList,$rowAll,'cm');
        array_push($prefix,array('type'=>'lottery','makers'=>'cm'));

		$profitList=array();
		foreach($dataList as $row){
			print_r($row);
			$m_group=tb_sql('m_group','member',$row['num']);	//取出會員群組
			$live_rake=tb_sql2('live_rake','member_group_rake','m_group',$m_group);	//真人返水%數
			$sport_rake=tb_sql2('sport_rake','member_group_rake','m_group',$m_group);	//體育返水%數
			$egame_rake=tb_sql2('egame_rake','member_group_rake','m_group',$m_group);	//電子返水%數
			$lottery_rake=tb_sql2('lottery_rake','member_group_rake','m_group',$m_group);	//彩票返水%數
			$esport_rake=tb_sql2('esport_rake','member_group_rake','m_group',$m_group);	//電競返水%數
			
			$tmpData['num']=$row["num"];
			$tmpData['total']=0;
			foreach($prefix as $rowP){
				if(array_key_exists($rowP["makers"].'_validAmount',$row)){	//若該筆資料已存在 則加入資料
					switch ($rowP["type"]){
						case 'live':
							$rake_profit=($live_rake/100);
							break;
						case 'sport':
							$rake_profit=($sport_rake/100);
							break;
						case 'egame':
							$rake_profit=($egame_rake/100);
							break;
						case 'lottery':
							$rake_profit=($lottery_rake/100);
							break;
						case 'esport':
							$rake_profit=($esport_rake/100);
							break;
					}
					echo @$rake_profit.':'.round($row[$rowP["makers"].'_validAmount'] * @$rake_profit,0).',';
					
					$tmpData['total']+=round($row[$rowP["makers"].'_validAmount'] * @$rake_profit,0);
					
				}
			}
			$profitList[$row["num"]]=$tmpData;
			echo '<hr>';
		}	
		echo '<hr>';
		print_r($profitList);
		
		//寫入db
		if(count($profitList) > 0){
			foreach($profitList as $row){
				if($row["total"] > 0){	
					$result=json_decode($this->checkRake($row["num"],$count_date));
					if($result->RntCode=='Y'){
						$params['admin_num']=tb_sql('admin_num','member',$row["num"]);
						$params['mem_num']=$row["num"];
						$params['u_id']=tb_sql('u_id','member',$row["num"]);
						$params['rake']=($row["total"] > $rake_winLimit ? $rake_winLimit : $row["total"]);	//超過返水上限金額 就只給最高金額
						$params['buildtime']=$count_date;
						$this->db->insert('member_rake', $params); 
					}elseif($result->RntCode=='W'){	//更新單據
						$params['rake']=($row["total"] > $rake_winLimit ? $rake_winLimit : $row["total"]);	//超過返水上限金額 就只給最高金額
						$this->db->where('num', $result->rake_num);
						$this->db->update('member_rake', $params); 						
					}
				}
			}
		}
		
	}
	
	//比對本次資料是否已存在並且尚未發放返水 如果已發放不更新 未發放才跟新
	private function checkRake($mem_num,$buildtime){
		$sqlStr="select num from `member_rake` where  `mem_num`=? and `buildtime`=?";
		$params["mem_num"]=$mem_num;
		$params["buildtime"]=$buildtime;
		$row=$this->webdb->sqlRow($sqlStr,$params);
		if($row!=NULL){
			if($row["is_received"]==0){		//單據存在但是尚未發放可以更新
				//更新
				return json_encode(array('RntCode'=>'W','rake_num'=>$row["num"]));
			}else{
				return json_encode(array('RntCode'=>'N','rake_num'=>$row["num"]));
				//return false;
			}
		}else{
			return json_encode(array('RntCode'=>'Y'));
		}
	}
	
}

?>