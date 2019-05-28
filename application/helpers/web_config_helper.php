<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Copy a whole Directory
 *
 * Copy a directory recrusively ( all file and directories inside it )
 *
 * @access    public
 * @param    string    path to source dir
 * @param    string    path to destination dir
 * @return    array
 */ 

if(!function_exists('depositCheck')){ 	
	function depositCheck($mem_num){
		$depositType="W";
		$CI = &get_instance();
		$eTime=now();
		//抓出該會員儲值紀錄
		$sqlStr="select mem_num,DATEDIFF('".$eTime."', buildtime) as dayCount from `member_wallet` where (kind=5 or kind=12) and mem_num=".$mem_num." order by buildtime DESC Limit 1";
		$query=$CI->db->query($sqlStr);
		$num_rows=$query->num_rows();
		$row=$query->row_array();
		if($num_rows == 0){	//未曾儲值
			$depositType='W';
		}else{	
			if($row["dayCount"] <=7){	//7天內有儲值
				$depositType='Y';
			}else{	//7天內未儲值
				$depositType='N';
			}
		}
		return $depositType;
	}
}
 
   
if(!function_exists('memAtmCount')){ 
 	function memAtmCount($mem_num){
		$CI = &get_instance();
		$parameter=array();
		$sqlStr="select count(*) as atmCount from orders where mem_num=? and keyin2=1 GROUP BY ATMAccNo";
		$parameter[":mem_num"]=$mem_num;
		$query=$CI->db->query($sqlStr, $parameter);
		$num_rows=$query->num_rows();
		$num_rows=$num_rows > 0 ? round((100/$num_rows),2) : 0;
		return $num_rows;
	}
}
  
if(!function_exists('getAllSave')){
	function getAllSave($mem_num,$sDate=NULL,$eDate=NULL){
		$Amount=0;
		$CI = &get_instance();
		//查詢儲值
		$parameter=array();
		$sqlStr="select SUM(amount) as MYAmount from `orders` where `keyin2`=1 and `mem_num`=?";
		$parameter[":mem_num"]=$mem_num;
		if($sDate!=NULL) $sqlStr.=" and buildtime >='".$sDate."'";
		if($eDate!=NULL) $sqlStr.=" and buildtime < '".$eDate."'";
		$result=$CI->db->query($sqlStr, $parameter);
		$row=$result->row_array();
		if($row){
			if($row["MYAmount"]!=NULL){
				$Amount+=(int)$row["MYAmount"];
			}
		 }
		//銀行匯款
		$parameter=array();
		$sqlStr="select SUM(amount) as MYAmount from `member_bank_transfer` where `keyin2`=1 and `mem_num`=?";
		$parameter[":mem_num"]=$mem_num;
		if($sDate!=NULL) $sqlStr.=" and buildtime >='".$sDate."'";
		if($eDate!=NULL) $sqlStr.=" and buildtime < '".$eDate."'";
		$result=$CI->db->query($sqlStr, $parameter);
		$row=$result->row_array();
		if($row){
			if($row["MYAmount"]!=NULL){
				$Amount+=(int)$row["MYAmount"];
			}
		 }		
		return $Amount;
	 }
}
 
 if(!function_exists('getAllSell')){
	function getAllSell($mem_num){
		$Amount=0;
		$CI = &get_instance();
		$parameter=array();
		$sqlStr="select SUM(amount) as MYAmount from `member_sell` where `keyin1`=1 and `mem_num`=?";
		$parameter[":mem_num"]=$mem_num;
		$result=$CI->db->query($sqlStr, $parameter);
		$row=$result->row_array();
		if($row){
			if($row["MYAmount"]!=NULL){
				$Amount=$row["MYAmount"];
			}
			if($row['MYAmount'] < 0){
				$Amount=0;	
			}
		  }
		  return $Amount;
	 }
}
 

//週拋售次數
//mem_num 會員編號
//bt 從那日起算次數
if(!function_exists('getWeekSell')){
	function getWeekSell($mem_num,$bt=NULL){
		$Amount=0;
		$CI = &get_instance();
		$parameter=array();
		$sqlStr="select Count(mem_num) as MYAmount from `member_sell` where `keyin1`=1 and `mem_num`=? and `buildtime` >= ?";
		$parameter[":mem_num"]=$mem_num;
		$parameter[":buildtime"]=$bt;
		$result=$CI->db->query($sqlStr, $parameter);
		$row=$result->row_array();
		if($row){
			if($row["MYAmount"]!=NULL){
				$Amount=$row["MYAmount"];
			}
			if($row['MYAmount'] < 0){
				$Amount=0;	
			}
		}
		return $Amount;
	 }
}  
  


 
//報表日期回傳
//type=d 代表本日...type=m 代表本月...type=tw 代表本週 ...type=yw 代表上週
if(!function_exists('reportDate')){
	function reportDate($type){
		$date=date('Y-m-d');	//當前日期
		$first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
		$w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
		$now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
		$now_end=date('Y-m-d',strtotime("$now_start +6 days"));  //本周结束日期
		$last_start=date('Y-m-d',strtotime("$now_start - 7 days"));  //上周开始日期
		$last_end=date('Y-m-d',strtotime("$now_start - 1 days"));  //上周结束日期
		switch ($type){
			case 'd':	//本日
				return array('d1'=>$date,'d2'=>$date);
				break;
			case 'm':	//本月
				return array('d1'=>date('Y-m-d', mktime(0,0,0,date('n'),01,date('Y'))),'d2'=>date('Y-m-d', mktime(0,0,0,date('n'),date('t'),date('Y'))));
				break;
			case 'ym':	//上月
				$d1=date('Y-m-d',mktime(0,0,0,date('m')-1,01,date('Y')));
				$d2=date('Y-m-d',strtotime($d1."+1 month -1 day"));
				return array('d1'=>$d1,'d2'=>$d2);
				break;
			case 'tw':	//本週
				return array('d1'=>$now_start,'d2'=>$now_end);
				break;
			case 'yw':	//上周
				return array('d1'=>$last_start,'d2'=>$last_end);
				break;	
		}
	}
}
 
 if(!function_exists('show_agent')){
	 function show_agent($root){
		$CI = &get_instance();
		$parameter=array();
		$sqlStr="select count(num) as newCount from `admin` where `root`=?";
		$parameter[":root"]=$root; 
		$result=$CI->db->query($sqlStr, $parameter);
		$row=$result->row_array();
		if($row){
			return $row["newCount"];	
		}
	 }
 }

 if(!function_exists('show_member')){
	 function show_member($root){
		$CI = &get_instance();
		$parameter=array();
		$sqlStr="select count(num) as newCount from `member` where `admin_num`=?";
		$parameter[":root"]=$root; 
		$result=$CI->db->query($sqlStr, $parameter);
		$row=$result->row_array();
		if($row){
			return $row["newCount"];	
		}
	 }
 }
 
 
if(!function_exists('time_sql')){
	function time_sql(){ //前台回傳時間區間的條件式
		//$CI = get_instance();
		$nowday=date("Y-m-d");
		$tmp=" and ((`selltime1`<='".$nowday."' or `selltime1`='0000-00-00' or `selltime1` is null) and (`selltime2`>='".$nowday."' or `selltime2`='0000-00-00' or `selltime2` is null))";
		return $tmp;
	}
}

if(!function_exists('kind_sql')){
	function kind_sql($root,$tb){
		$tmp="";
		$CI = &get_instance();
		$CI->db->select('num');
		$CI->db->where('root',$root);
		//$CI->db->order_by('range','ASC');
		$CI->db->from($tb);
		$query = $CI->db->get();
		if($query->num_rows() > 0){
			foreach($query->result_array() as $row){
				$tmp.=",".$row["num"].kind_sql($row["num"],$tb);
			}
		}
		return $tmp;
	}
}

//計算下線人數
if(!function_exists('countUpAccount')){
	function countUpAccount($up_account){
		$CI =& get_instance();
		$parameter=array();
		$sqlStr="select num from `member` where `up_account`=?";
		$parameter[':up_account']=trim($up_account);
		return $CI->webdb->sqlRowCount($sqlStr,$parameter);	
	}
}

//取得下線名單
if(!function_exists('getUpAccount')){
	function getUpAccount($up_account){
		$CI =& get_instance();
		$parameter=array();
		$sqlStr="select num,u_id,u_name from `member` where `up_account`=?";
		$parameter[':up_account']=trim($up_account);
		return $CI->webdb->sqlRowList($sqlStr,$parameter);	
	}
}


if(!function_exists('urlQuery')){
	function urlQuery($notNeedParameters){
		$query=$_SERVER['QUERY_STRING'];
		if ($query!=""){
			$q=explode("&",$query);
			$query="";
			for ($i=0;$i<count($q);$i++){
				$s=explode("=",$q[$i]);
				if (is_array($notNeedParameters)){
					$chkOK=true;
					foreach($notNeedParameters as $value){
						if ($s[0]==$value){
							$chkOK=false;
							break;
						}
					}
					if ($chkOK){
						$query.=($query!=""?"&":"?").$q[$i];
					}
				}else{
					if ($s[0]!=$notNeedParameters){
						$query.=($query!=""?"&":"?").$q[$i];
					}
				}			
			}
		}
		return $query;
	}
}



if(!function_exists('getWalletTotal')){
	function getWalletTotal($mem_num){
		$WalletTotal=0;
		$CI = &get_instance();
		if($mem_num!=""){
			$parameter=array();
			$sqlStr="select SUM(points) as WalletTotal from `member_wallet` where mem_num=?";
			$parameter[":mem_num"]=$mem_num;
			$result=$CI->db->query($sqlStr, $parameter);
			$row=$result->row_array();
			if($row){
				if($row["WalletTotal"]!=NULL){
					$WalletTotal=$row["WalletTotal"];
				}
				if($row['WalletTotal'] < 0){
					$WalletTotal=0;	
				}
			}
		}
		return (float)$WalletTotal;
	}
}

//取得妞妞錢包點數
if(!function_exists('getNiuWallet')){
	function getNiuWallet($num){
		$WalletTotal=0;
		$CI = &get_instance();
		if($mem_num!=""){
			$parameter=array();
			$sqlStr="select niu_balance as WalletTotal from `member` where num=?";
			$parameter[":num"]=$num;
			$result=$CI->db->query($sqlStr, $parameter);
			$row=$result->row_array();
			if($row){
				if($row["WalletTotal"]!=NULL){
					$WalletTotal=$row["WalletTotal"];
				}
				if($row['WalletTotal'] < 0){
					$WalletTotal=0;	
				}
			}
		}
		return (float)$WalletTotal;
	}
}


if(!function_exists('br')){
	function br($word){
		$word=str_replace(chr(13),'',$word);
		return str_replace(chr(10),'<br>',$word);
	}
}
 
if(!function_exists('get_mykind')){
	function get_mykind($tb,$num){
		$CI =&get_instance();
		$CI->db->where('num',$num);
		$query=$CI->db->get($tb);
		$row=$query->row_array();
		if($row!=NULL){
			if ($row["root"]>0){
				return get_mykind($tb,$row["root"])."/<br>".$row["kind"];
			}else{ 
				return $row["kind"];
			}	 
		}else{
			return '<span style="color:red">分類不存在</span>';
		}
	}
}


if(!function_exists('tb_sql')){
	function tb_sql($cu,$tb,$num){
		$CI = &get_instance();
		$CI->db->select($cu);
		$CI->db->where("num",$num);
		$query=$CI->db->get($tb);
		$row=$query->row_array();
		if($row!=NULL){
			return $row[$cu];
		}else{
			return "";	
		}		
	}
}

if(!function_exists('tb_sql2')){
	function tb_sql2($cu,$tb,$where,$num){
		$CI = &get_instance();
		$CI->db->select($cu);
		$CI->db->where($where,$num);
		$query=$CI->db->get($tb);
		$row=$query->row_array();
		if($row!=NULL){
			return $row[$cu];
		}else{
			return NULL;	
		}		
	}
}

if(!function_exists('tb_sql3')){
	function tb_sql3($cu,$tb,$where){
		$CI = &get_instance();
		$CI->db->select($cu);
		$CI->db->where($where);
		$query=$CI->db->get($tb);
		$row=$query->row_array();
		if($row!=NULL){
			return $row[$cu];
		}else{
			return NULL;	
		}		
	}
}

if(!function_exists('tb_sql4')){
    function tb_sql4($cu,$tb,$where){
        $CI = &get_instance();
        $CI->db->select($cu);
        $CI->db->where($where);
        $query=$CI->db->get($tb);
        $row=$query->row_array();
        if($row!=NULL){
            return $row[$cu];
        }else{
            return NULL;
        }
    }
}


if(!function_exists('sqlSortAdd')){
	function sqlSortAdd($tb,$nation=NULL){
		$CI = &get_instance();
		$parameter=array();
		$sqlStr="UPDATE ".$tb." SET `range`=`range`+1";
		if($nation!=NULL){
			$sqlStr.=" where `nation`=?";
			$parameter[':nation']=$nation;
		}
		$CI->db->query($sqlStr, $parameter);	
	}
}


if(!function_exists('now')){
	function now(){
		return date('Y-m-d H:i:s');
	}
}

//傳回指定格式的日期
if(!function_exists('datetype')){
	function datetype($date,$type='Y-m-d'){  //傳回指定格式的日期
	if ($type==''){$type='Y-m-d';}
		return date($type,strtotime($date));
	}
}
 
////取得對應的語系名稱
if(!function_exists('languageText')){
	function languageText($lang,$value){
		for ($i=0;$i<count($lang);$i++){
			$nations=explode("|",$lang[$i]);
			if ($value==$nations[1]){
				return $nations[0];
			}
		}
		return "";	
	}

}

//建立多語系下拉
if(!function_exists('buildLanguage')){
	function buildLanguage($lang,$nation=NULL){
		for ($i=0;$i<count($lang);$i++){
			$nations=explode("|",$lang[$i]);
			echo '<option value="'.$nations[1].'" '.($nations[1]==$nation?'selected="selected"':'').'>'.$nations[0].'</option>';
		}
	}
}


//回傳訂單處理情形名稱
if(!function_exists('returnKeyin1')){
	function returnKeyin1($value){
		$CI = &get_instance();
		$CI->load->library('orderclass');	//載入訂單函式庫
		$key1=$CI->orderclass->orderKeyin1();
		return $key1[$value];
	}
}



//回傳訂單收款情形名稱
if(!function_exists('returnKeyin2')){
	function returnKeyin2($value){
		$CI = &get_instance();
		$CI->load->library('orderclass');	//載入訂單函式庫
		$key2=$CI->orderclass->orderKeyin2();
		return $key2[$value];
	}
}



if(!function_exists('getKindOption')){
	function getKindOption($tb,$value=NULL,$nation=NULL,$admin_num=0){
		$CI = &get_instance();
		$CI->load->model('web_config_model');
		$result=$CI->web_config_model->getKindOption($tb,0,$nation,$admin_num);		
		//$result = $this -> web_config_model ->getKindOption($tb);
		if($result!=NULL){
			foreach($result as $row){
				$ck='';
				if($value==$row["num"]){
					$ck=' selected';	
				}
				echo '<option value="'.$row["num"].'" '.$ck.'>'.$row["kind"].'</option>';
				subKindOption($tb,$row["num"],1,$value,$nation,$admin_num);
			}
		}
	}
}
if(!function_exists('subKindOption')){
	function subKindOption($tb,$root,$level,$value=NULL,$nation=NULL,$admin_num=0){
 		$d="";
		for ($i=1;$i<=$level;$i++){
			$d.="…";
		}
		
		$CI = &get_instance();
		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->getKindOption($tb,$root,$nation,$admin_num);
		if($result!=NULL){
			foreach($result as $row){
				$ck='';
				if($value==$row["num"]){
					$ck=' selected';	
				}
				echo '<option value="'.$row["num"].'"'.$ck.'>'.$d.$row["kind"].'</option>';
				subKindOption($tb,$row["num"],$level+1,$value,$nation,$admin_num);
			}
		}
	}
}

//前台套版用
if(!function_exists('getWebKind')){	//抓選單用
	function getWebKind($tb,$root=0,$nation=NULL,$admin_num=0){
		$dataList=array();
		$i=0;
		$CI = &get_instance();
		$CI->load->model('web_config_model');
		$result=$CI->web_config_model->getKindOption($tb,$root,$nation,$admin_num);
		if($result!=NULL){
			foreach($result as $row){
				$data=array();
				$data["num"]=$row["num"];
				$data["kind"]=$row["kind"];	
				$data["nodes"]=getWebKind($tb,$row["num"],$nation,$admin_num);
				$dataList[$i]=$data;
				$i++;
			}
		}
		return $dataList;
	}
}

if(!function_exists('buildCity')){
	function buildCity($num=NULL){  //建立縣市下拉清單
		$CI = get_instance();
		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->buildCity('city');
		if($result!=NULL){
			foreach($result as $row){
				echo '<option value="'.$row->num.'"'.($row->num==$num ? ' selected' : "").'>'.$row->City.'</option>';	
			}
		}
	
	}
}

if(!function_exists('buildArea')){	//建立鄉鎮
	function buildArea($cityNum,$num=NULL){
		$CI = get_instance();
		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->buildArea('area',$cityNum);
		if($result!=NULL){
			foreach($result as $row){
				echo '<option value="'.$row->num.'"'.($row->num==$num ? ' selected' : "").'>'.$row->Area.'</option>';	
			}
		}
		
	}
}


//建立付款方式下拉

if(!function_exists('buildPayment')){	
	function buildPayment($num=NULL){
		$CI = get_instance();
		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->buildPayment('pro_payment');
		if($result!=NULL){
			foreach($result as $row){
				echo '<option value="'.$row->num.'"'.($row->num==$num ? ' selected' : "").'>'.$row->pay_name.'</option>';	
			}
		}
		
	}
}

if(!function_exists('getPayName')){	
	function getPayName($num){
		$CI = get_instance();
		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->getPayName('pro_payment',$num);
		if($result!=NULL){
			return $result->pay_name;
		}
	}
}


//建立 CheckBox Radio 物件
if(!function_exists('inInputCR')){	
	function inInputCR($arrVal,$objType,$objName,$defVal=NULL){
		if (empty($objType)) return "未定義 Type";
		if (empty($objName)) return  "未定義 Name";
		if (!is_array($arrVal))  return  "非正規陣列資料";
		$tmpStr = "";
		for($i=0;$i<count($arrVal);$i++){
			$sys=explode("_",$arrVal[$i]);
			$tmpStr .= "<label><input type='" . $objType . "' class='ace' id='".$objName.$i."' name='" . $objName . "' value='" . $sys[0] . "'";
			if (is_array($defVal)) {
				foreach ($defVal as $value1) {
					if ($defVal!="") {
						if ($sys[0] == $value1)  $tmpStr .= " checked";
					}
				}
			}
			else{
				if ($defVal!="") {
					if ($sys[0] == $defVal)  $tmpStr .=  " checked";
				}
			}
			$tmpStr .=  "><span class='lbl'>" . $sys[1] ."</span></label> " ;
		}
		return $tmpStr;
	}
}



if(!function_exists('inSelectOption')){	
	function inSelectOption($arrVal,$defVal=NULL){
		$tmpStr = "";
		if (is_array($arrVal)) {
			foreach ($arrVal as $value) {
				$sys=explode("_",$value);
				$tmpStr = $tmpStr . "<option value=\"" . $sys[0] . "\"";
				if ($defVal != "") {
					if ($sys[0] == $defVal) { $tmpStr = $tmpStr . " selected";}
				}
				$tmpStr = $tmpStr . ">" .  $sys[1] . "</option>";}
			
			return $tmpStr;}
		else {
			return "非正規陣列資料";
		}
	}
}


if(!function_exists('inSelectOption2')){	
	function inSelectOption2($arrVal,$defVal=NULL){
		$tmpStr = "";
		if (is_array($arrVal)) {
			foreach ($arrVal as $key=>$value) {
				$tmpStr = $tmpStr . "<option value=\"" . $key . "\"";
				if ($defVal != "") {
					if ($key == $defVal) { $tmpStr = $tmpStr . " selected";}
				}
				$tmpStr = $tmpStr . ">" .  $value . "</option>";}
			return $tmpStr;}
		else {
			return "非正規陣列資料";
		}
	}
}


if(!function_exists('inNumberString')){	
	function inNumberString($arr,$v,$ex='，'){
		$tmpStr = "";
		$arrRow = 0;
		if (is_array($arr)) {
			foreach ($arr as $value) {
				$sys=explode("_",$value);
				if(!is_array($v)){
					if ($sys[0] == $v) {
						$tmpStr = $sys[1];
					}
				}else{
					foreach($v as $item){
						if ($sys[0] == $item) {
							if ($tmpStr!="") {
								$tmpStr.=$ex;
							}
							$tmpStr.=inNumberString($arr,$item);
						}
					}
				}
			}
			return $tmpStr;
		}
	}
}


if(!function_exists('inNumberString2')){	
	function inNumberString2($arr,$v,$ex='，'){
		$tmpStr = "";
		$arrRow = 0;
		if (is_array($arr)) {
			foreach ($arr as $key=>$value) {
				//$sys=explode("_",$value);
				if(!is_array($v)){
					if ($key == $v) {
						$tmpStr = $value;
					}
				}else{
					foreach($v as $item){
						if ($key == $item) {
							if ($tmpStr!="") {
								$tmpStr.=$ex;
							}
							$tmpStr.=inNumberString2($arr,$item);
						}
					}
				}
			}
			return $tmpStr;
		}
	}
}


if(!function_exists('getCity')){	
	function getCity($num){
		$CI = get_instance();
		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->getCity($num);
		if($result!=NULL){
			return $result->City;
		}
	}
}

if(!function_exists('getArea')){	
	function getArea($num){
		$CI = get_instance();
		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->getArea($num);
		if($result!=NULL){
			return $result->Area;
		}
	}
}
if(!function_exists('htmlencode')){
	function htmlencode($str){
		return htmlentities($str,ENT_QUOTES,"UTF-8");
	}
}

//廣告用
if(!function_exists('buildBannerKind')){	
	function buildBannerKind($root=0){
		$dataList=array();
		$CI =& get_instance();
		$CI->load->model('web_config_model');
		$CI->db->where('root',$root);
		$CI->db->order_by('num asc');
		$query=$CI->db->get('banner_kind');
		$rowAll=$query->result_array();
		$i=0;
		if($rowAll!=NULL){
			foreach($rowAll as $row){	
				$kind=array();
				$kind["num"]=$row["num"];	
				$kind["kind"]=$row["kind"];
				$kind["root"]=$row["root"];
				$kind["w"]=$row["w"];
				$kind["h"]=$row["h"];
				$kind["area"]=buildBannerKind($row["num"]);
				$dataList[$i]=$kind;
				$i++;
			}
		}
		
		return $dataList;
	}
}

if(!function_exists('sqlUpdateString')){	
	function sqlUpdateString($colSql){	
		$colSql=str_replace("`","",$colSql);
		$cu=explode(",",$colSql);
		$colSql="";
		for ($i=0;$i<count($cu);$i++){
			$colSql.=($i>0?",":"")."`".$cu[$i]."`=?";
		}
		return $colSql;	
	}
}
if(!function_exists('sqlInsertString')){	
	function sqlInsertString($colSql,$mode){	
		$colSql=str_replace("`","",$colSql);
		$cu=explode(",",$colSql);
		$colSql="";
		for ($i=0;$i<count($cu);$i++){
			if ($mode==0){
				$colSql.=($i>0?",":"")."`".$cu[$i]."`";
			}elseif ($mode==1){
				$colSql.=($i>0?",":"")."?";
			}		
		}
		return $colSql;	
	}
}

if(!function_exists('scriptMsg')){
	function scriptMsg($msg="",$url){
		header("Content-Type: text/html; charset=utf-8");
		echo '<script type="text/JavaScript">';
		if($msg!=""){
			echo 'alert("'.$msg.'");';
		}
		echo "window.location = '".site_url($url)."';";
		echo "</script>";
		exit;
	}
}

if(!function_exists('scriptCloseMsg')){
	function scriptCloseMsg($msg=""){
		header("Content-Type: text/html; charset=utf-8");
		echo '<script type="text/JavaScript">';
		if($msg!=""){
			echo 'alert("'.$msg.'");';
		}
		echo 'window.parent.close();';
		echo "</script>";
		exit;
	}
}



if(!function_exists('mailSend')){	//寄信用
	function mailSend($subject,$message,$to){
		$CI =& get_instance();
		$CI->load->library('mailer');
		return $CI->mailer->mailSend($subject,$message,$to);
	}
}


//訂單留言回覆
if(!function_exists('sendRetalkSuccess')){	
	function sendRetalkSuccess($order_no){
		$CI =& get_instance();

		$CI->load->model('web_config_model');
		$result = $CI -> web_config_model ->get_web_info();
		
		$CI->db->select('email,nation');
		$CI->db->where('order_no',$order_no);
		$query = $CI->db->get('pro_order');
		$row=$query->row_array();
		
		/* 主旨 */
		$subject=array();
		$subject['TW']=$result->com_name."-訂單留言回覆通知";
		$subject['CN']=$result->com_name."-订单留言回覆通知";
		$subject['US']=$result->com_name."-Order Message Reply Notification";
		$subject['JP']=$result->com_name."-受注返信通知";
		
		//提問內容
		$CI->db->select('word');
		$CI->db->where('num',@$_POST["root"]);
		$query2 = $CI->db->get('order_talk');
		$row2=$query2->row_array();
		$qWord = $row2["word"];


		/* 載入信件內容 */
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, base_url().'assets/email/orderMessageReply_'.$row["nation"].'.html');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$message=curl_exec($ch);
		curl_close($ch);						
		$message=str_replace('[subject]',$subject[$row["nation"]],$message);
		$message=str_replace('[now]',date('Y-m-d H:i:s'),$message);
		$message=str_replace('[order_no]',$order_no,$message);
		$message=str_replace('[qWord]',nl2br($qWord),$message);
		$message=str_replace('[word]',nl2br(htmlencode(@$_POST["word"])),$message);
		$CI->mailer->mailSend($subject[$row["nation"]],$message,$row["email"]);
		
	}
}



