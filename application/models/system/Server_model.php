<?php
class Server_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_server';
		$this->pk = 'server_kurzbz';
	}
}