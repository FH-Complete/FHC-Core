<?php
class Veranstaltung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_veranstaltung';
		$this->pk = 'veranstaltung_id';
	}
}
