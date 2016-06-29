<?php
class Phrasentext_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_phrasentext';
		$this->pk = 'phrasentext_id';
	}

}
