<?php
class Paabgabetyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_paabgabetyp';
		$this->pk = 'paabgabetyp_kurzbz';
	}
	
	public function getAll() {
		$qry = "SELECT * FROM campus.tbl_paabgabetyp";

		return $this->execReadOnlyQuery($qry);
	}
}
