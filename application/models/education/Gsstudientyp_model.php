<?php
class Gsstudientyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_gsstudientyp';
		$this->pk = 'gsstudientyp_kurzbz';
	}
}