<?php
class Freebusytyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_freebusytyp';
		$this->pk = 'freebusytyp_kurzbz';
	}
}
