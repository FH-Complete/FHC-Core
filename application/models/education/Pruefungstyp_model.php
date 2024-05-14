<?php
class Pruefungstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_pruefungstyp';
		$this->pk = 'pruefungstyp_kurzbz';
	}
}
