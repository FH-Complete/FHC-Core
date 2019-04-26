<?php
class Studienordnungstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_studienordnungstatus';
		$this->pk = 'status_kurzbz';
	}
}
