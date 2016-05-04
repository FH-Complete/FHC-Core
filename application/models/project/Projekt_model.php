<?php
class Projekt_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_projekt';
		$this->pk = 'projekt_kurzbz';
	}
}
