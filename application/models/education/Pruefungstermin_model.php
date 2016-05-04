<?php
class Pruefungstermin_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_pruefungstermin';
		$this->pk = 'pruefungstermin_id';
	}
}
