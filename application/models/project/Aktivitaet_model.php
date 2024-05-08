<?php
class Aktivitaet_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_aktivitaet';
		$this->pk = 'aktivitaet_kurzbz';
	}
}
