<?php
class Zeitsperretyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitsperretyp';
		$this->pk = 'zeitsperretyp_kurzbz';
	}
}
