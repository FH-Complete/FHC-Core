<?php
class Veranstaltungskategorie_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_veranstaltungskategorie';
		$this->pk = 'veranstaltungskategorie_kurzbz';
	}
}
