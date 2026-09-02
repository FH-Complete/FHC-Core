<?php

class Stundengrenze_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_stundengrenze';
		$this->pk = 'stundengrenze_id';
		$this->hasSequence = true;
	}
}
