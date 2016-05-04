<?php
class Lehreinheitmitarbeiter_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehreinheitmitarbeiter';
		$this->pk = array('mitarbeiter_uid', 'lehreinheit_id');
	}
}
