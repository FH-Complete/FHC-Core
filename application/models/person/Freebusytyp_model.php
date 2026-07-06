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

	public function getAllFreeBusyTypes()
	{
		return $this->execReadOnlyQuery("SELECT * FROM " . $this->dbTable);
	}

	public function getActiveFreeBusyTypes()
	{
		return $this->execReadOnlyQuery("SELECT * FROM " . $this->dbTable . " WHERE is_active");
	}
}
