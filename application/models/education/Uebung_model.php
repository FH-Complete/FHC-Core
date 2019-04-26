<?php
class Uebung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_uebung';
		$this->pk = 'uebung_id';
	}
}
