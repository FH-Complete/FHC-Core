<?php

class JobTypes_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbTable = 'system.tbl_jobtypes';
		$this->pk = 'type';
		$this->hasSequence = false;
	}
}
