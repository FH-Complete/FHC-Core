<?php
class Kriterien_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_kriterien';
		$this->pk = 'kategorie_kurzbz';
	}
}
