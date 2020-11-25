<?php

class JobStatuses_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbTable = 'system.tbl_jobstatuses';
		$this->pk = 'status';
		$this->hasSequence = false;
	}
}
