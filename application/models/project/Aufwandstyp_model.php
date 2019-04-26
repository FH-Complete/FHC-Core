<?php
class Aufwandstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_aufwandstyp';
		$this->pk = 'aufwandstyp_kurzbz';
	}
}
