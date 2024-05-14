<?php


class ZGVPruefung_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_zgvpruefung';
		$this->pk = 'zgvpruefung_id';
		$this->hasSequence = true;
	}

}