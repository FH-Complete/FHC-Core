<?php
class Zahlungstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_zahlungstyp';
		$this->pk = 'zahlungstyp_kurzbz';
	}
}
