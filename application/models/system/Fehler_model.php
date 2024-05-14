<?php
class Fehler_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_fehler';
		$this->pk = 'fehlercode';
	}
}
