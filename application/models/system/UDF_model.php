<?php

class UDF_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_udf';
		$this->pk = array('schema', 'table');
		$this->hasSequence = false;
	}
}