<?php
class Lvregel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lvregel';
		$this->pk = 'lvregel_id';
	}
}
