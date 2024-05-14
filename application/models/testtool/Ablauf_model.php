<?php
class Ablauf_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_ablauf';
		$this->pk = 'ablauf_id';
	}
}
