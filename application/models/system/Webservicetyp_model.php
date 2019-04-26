<?php
class Webservicetyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_webservicetyp';
		$this->pk = 'webservicetyp_kurzbz';
	}
}
