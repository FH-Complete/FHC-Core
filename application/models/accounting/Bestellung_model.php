<?php
class Bestellung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_bestellung';
		$this->pk = 'bestellung_id';
	}
}
