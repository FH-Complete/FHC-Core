<?php
class Cronjob_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_cronjob';
		$this->pk = 'cronjob_id';
	}
}
