<?php
class Service_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_service';
		$this->pk = 'service_id';
	}
}
