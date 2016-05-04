<?php
class Bestellstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_bestellstatus';
		$this->pk = 'bestellstatus_kurzbz';
	}
}
