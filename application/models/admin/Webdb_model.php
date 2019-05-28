<?php
include_once (dirname(__FILE__)."/Core_model.php");

class Webdb_model extends Core_model {
	protected $table;
	
	function __construct(){ 
        parent::__construct();
    }
}
