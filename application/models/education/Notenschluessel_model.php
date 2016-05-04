<?php
class Notenschluessel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_notenschluessel';
		$this->pk = 'notenschluessel_kurzbz';
	}
}
