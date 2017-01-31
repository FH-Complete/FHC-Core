<?php

class Reihungstest_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_reihungstest';
		$this->pk = 'reihungstest_id';
	}	
}