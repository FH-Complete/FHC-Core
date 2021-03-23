<?php


class ZGVPruefungStatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_zgvpruefungstatus_status';
		$this->pk = 'zgv_pruefung_status_id';
		$this->hasSequence = true;
	}

}