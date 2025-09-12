<?php
class LehrveranstaltungFaktor_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrveranstaltung_faktor';
		$this->pk = 'lehrveranstaltung_faktor_id';
	}
}
