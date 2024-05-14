<?php
class Vertragsstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_vertragsstatus';
		$this->pk = 'vertragsstatus_kurzbz';
	}
}
