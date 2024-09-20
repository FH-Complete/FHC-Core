<?php
class Uhstat1daten_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_uhstat1daten';
		$this->pk = 'uhstat1daten_id';
	}
}
