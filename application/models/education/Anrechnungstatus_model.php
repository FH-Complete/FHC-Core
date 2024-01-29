<?php


class Anrechnungstatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_anrechnungstatus';
		$this->pk = 'status_kurzbz';
	}
}
