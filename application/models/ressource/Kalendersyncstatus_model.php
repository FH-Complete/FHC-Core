<?php
class Kalendersyncstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender_syncstatus';
		$this->pk = 'kalender_syncstatus_id';
	}
}
