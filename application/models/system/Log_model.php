<?php
class Log_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_log';
		$this->pk = 'log_id';
	}
}
