<?php
class Buchung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_buchung';
		$this->pk = 'buchung_id';
	}
}
