<?php
class Appdaten_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_appdaten';
		$this->pk = 'appdaten_id';
	}
}
