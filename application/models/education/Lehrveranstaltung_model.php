<?php
class Lehrveranstaltung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrveranstaltung';
		$this->pk = 'lehrveranstaltung_id';
	}
}
