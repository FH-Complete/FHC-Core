<?php
class Lehrfach_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrfach';
		$this->pk = 'lehrfach_id';
	}
}
