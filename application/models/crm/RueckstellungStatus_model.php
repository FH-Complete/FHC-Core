<?php

class RueckstellungStatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_rueckstellung_status';
		$this->pk = 'status_kurzbz';
	}
}