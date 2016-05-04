<?php
class LeNotenschluessel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_notenschluessel';
		$this->pk = array('note', 'lehreinheit_id');
	}
}
