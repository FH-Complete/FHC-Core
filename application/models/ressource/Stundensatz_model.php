<?php

class Stundensatz_model extends DB_Model
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_stundensatz';
		$this->pk = 'stundensatz_id';
		$this->hasSequence = true;
	}
	
}