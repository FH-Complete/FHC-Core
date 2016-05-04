<?php
class Gebiet_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_gebiet';
		$this->pk = 'gebiet_id';
	}
}
