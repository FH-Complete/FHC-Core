<?php
class Paabgabe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_paabgabe';
		$this->pk = 'paabgabe_id';
	}
}
