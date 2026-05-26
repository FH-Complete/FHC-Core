<?php
class FehlerApp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_fehler_app';
		$this->pk = array('fehlercode', 'app');
		$this->hasSequence = false;
	}
}