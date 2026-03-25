<?php
class Kalenderstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender_status';
		$this->pk = 'status_kurzbz';
	}
}
