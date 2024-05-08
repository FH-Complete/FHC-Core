<?php
class Pruefungsstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_pruefungsstatus';
		$this->pk = 'status_kurzbz';
	}
}
