<?php
class Webservicerecht_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_webservicerecht';
		$this->pk = 'webservicerecht_id';
	}
}
