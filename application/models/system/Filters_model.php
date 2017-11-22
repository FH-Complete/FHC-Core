<?php

class Filters_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_filters';
		$this->pk = 'filter_id';
	}
}
