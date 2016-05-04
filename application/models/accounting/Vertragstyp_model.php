<?php
class Vertragstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_vertragstyp';
		$this->pk = 'vertragstyp_kurzbz';
	}
}
