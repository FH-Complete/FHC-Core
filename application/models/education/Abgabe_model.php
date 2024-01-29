<?php
class Abgabe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_abgabe';
		$this->pk = 'abgabe_id';
	}
}
