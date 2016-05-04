<?php
class Bisverwendung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisverwendung';
		$this->pk = 'bisverwendung_id';
	}
}
