<?php
class Kategorie_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_kategorie';
		$this->pk = 'kategorie_kurzbz';
	}
}
