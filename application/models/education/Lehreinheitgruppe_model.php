<?php
class Lehreinheitgruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehreinheitgruppe';
		$this->pk = 'lehreinheitgruppe_id';
	}
}
