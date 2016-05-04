<?php
class Coodle_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_coodle';
		$this->pk = 'coodle_id';
	}
}
