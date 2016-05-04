<?php
class Benutzerrolle_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_benutzerrolle';
		$this->pk = 'benutzerberechtigung_id';
	}
}
