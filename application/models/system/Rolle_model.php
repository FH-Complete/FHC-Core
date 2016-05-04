<?php
class Rolle_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_rolle';
		$this->pk = 'rolle_kurzbz';
	}
}
