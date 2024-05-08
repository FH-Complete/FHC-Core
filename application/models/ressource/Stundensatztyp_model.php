<?php

class Stundensatztyp_model extends DB_Model
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_stundensatztyp';
		$this->pk = 'stundensatztyp';
	}
	
}