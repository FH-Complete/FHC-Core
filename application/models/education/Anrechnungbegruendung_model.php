<?php


class Anrechnungbegruendung_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_anrechnung_begruendung';
		$this->pk = 'begruendung_id';
	}
}
