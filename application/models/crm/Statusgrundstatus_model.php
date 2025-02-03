<?php

class Statusgrundstatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_status_grund_status";
		$this->pk = array('status_kurzbz', 'statusgrund_id');

	}

}
