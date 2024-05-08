<?php
class Berechtigung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_berechtigung';
		$this->pk = 'berechtigung_kurzbz';
	}
}
