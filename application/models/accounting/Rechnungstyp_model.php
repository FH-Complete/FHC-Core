<?php
class Rechnungstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_rechnungstyp';
		$this->pk = 'rechnungstyp_kurzbz';
	}
}
