<?php
class Projekttyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_projekttyp';
		$this->pk = 'projekttyp_kurzbz';
	}
}
