<?php

class JobsQueue_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbTable = 'system.tbl_jobsqueue';
		$this->pk = 'jobid';
	}
}
