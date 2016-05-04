<?php
class Lvinfo_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_lvinfo';
		$this->pk = array('sprache', 'lehrveranstaltung_id');
	}
}
