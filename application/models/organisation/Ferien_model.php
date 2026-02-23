<?php
class Ferien_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_ferien';
		$this->pk = 'ferien_id';
	}
}
