<?php
class Gruppe_manager_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_gruppe_manager';
		$this->pk = 'gruppe_manager_id';
	}
}
