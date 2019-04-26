<?php
class Projekttask_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_projekttask';
		$this->pk = 'projekttask_id';
	}
}
