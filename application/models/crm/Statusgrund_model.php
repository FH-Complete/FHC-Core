<?php

class Statusgrund_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_status_grund";
		$this->pk = "statusgrund_kurzbz";
	}
}
