<?php
class Pruefung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_pruefung';
		$this->pk = 'pruefung_id';
	}
}
