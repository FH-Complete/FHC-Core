<?php
class Betriebsmittelstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmittelstatus';
		$this->pk = 'betriebsmittelstatus_kurzbz';
	}
}
