<?php
class Lehrtyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrtyp';
		$this->pk = 'lehrtyp_kurzbz';
	}
}
