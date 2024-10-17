<?php
class Pruefungsfenster_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_pruefungsfenster';
		$this->pk = 'pruefungsfenster_id';
	}
}
