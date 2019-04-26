<?php
class Rolleberechtigung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_rolleberechtigung';
		$this->pk = array('rolle_kurzbz', 'berechtigung_kurzbz');
	}
}
