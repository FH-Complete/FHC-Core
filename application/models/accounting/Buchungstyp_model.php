<?php
class Buchungstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_buchungstyp';
		$this->pk = 'buchungstyp_kurzbz';
	}
}
