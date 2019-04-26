<?php
class Pruefling_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_pruefling';
		$this->pk = 'pruefling_id';
	}
}
