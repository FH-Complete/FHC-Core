<?php
class Aufnahmetermintyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_aufnahmetermintyp';
		$this->pk = 'aufnahmetermintyp_kurzbz';
	}
}
