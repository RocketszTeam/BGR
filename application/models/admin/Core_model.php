<?php
/**
 * 核心模組 
 * @author Thomas
 *
 */
class Core_model extends CI_Model{
	protected $table;
	protected $CI;
	
	public function __construct(){
		parent::__construct();
		$this->CI =& get_instance();
	}
	
	public function sqlRowList($sqlStr,$parameter=NULL,$tb=NULL){	
		if($tb!=NULL){$this->table=$tb;}		
		$sqlStr=str_replace("[myTable]",$this->db->dbprefix($this->table) ,$sqlStr);
		$query=$this->db->query($sqlStr, $parameter);
		//echo $this->db->last_query();
		$num_rows = $query->num_rows();
		$rowAll=$query->result_array();
		if ($num_rows > 0){
			return $rowAll;
		}else{
			return NULL;	
		}
	}
	
	public function sqlRow($sqlStr,$parameter=NULL,$tb=NULL){	
		if($tb!=NULL){$this->table=$tb;}		
		$sqlStr=str_replace("[myTable]",$this->db->dbprefix($this->table) ,$sqlStr);
		$query=$this->db->query($sqlStr, $parameter);
		//echo $this->db->last_query();
		$num_rows = $query->num_rows();
		$row=$query->row_array();
		$query->free_result();
		if ($num_rows > 0){
			return $row;
		}else{
			return NULL;	
		}
	}
	
	public function sqlExc($sqlStr,$parameter=NULL,$tb=NULL){
		if($tb!=NULL){$this->table=$tb;}
		$sqlStr=str_replace("[myTable]",$this->db->dbprefix($this->table) ,$sqlStr);
		$this->db->simple_query($sqlStr, $parameter);
		return $this->db->insert_id();
	}
	
	public function sqlInsert($tb,$data){
		$this->db->insert($tb, $data); 
		return $this->db->insert_id();	
	}
	public function sqlReplace($tb,$data){
		$this->db->replace($tb, $data); 
		return $this->db->insert_id();	
	}
	
	//插入資料 PK值已存在則忽略
	public function sqlIgnore($tb,$data){
		$colSql=implode(',',array_keys($data));
		$sqlStr="INSERT IGNORE INTO `".$tb."` (".sqlInsertString($colSql,0).") VALUES (".sqlInsertString($colSql,1).")";
		$this->db->simple_query($sqlStr, $data);
	}
	
	public function sqlUpdate($tb,$data,$where=NULL){
		$this->db->update($tb,$data,$where);
		return $this->db->affected_rows();
	}
	
	public function sqlRowCount($sqlStr,$parameter=NULL,$tb=NULL){
		if($tb!=NULL){$this->table=$tb;}
		$sqlStr=str_replace("[myTable]",$this->db->dbprefix($this->table) ,$sqlStr);
		$query=$this->db->query($sqlStr, $parameter);
		return $query->num_rows();
	}
	
	
}