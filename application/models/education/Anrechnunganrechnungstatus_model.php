<?php

class Anrechnunganrechnungstatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_anrechnung_anrechnungstatus';
		$this->pk = 'anrechnungstatus_id';
	}
}
