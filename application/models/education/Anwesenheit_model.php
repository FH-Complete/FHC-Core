<?php
class Anwesenheit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_anwesenheit';
		$this->pk = 'anwesenheit_id';
	}
}
