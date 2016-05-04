<?php
class Lvregeltyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lvregeltyp';
		$this->pk = 'lvregeltyp_kurzbz';
	}
}
