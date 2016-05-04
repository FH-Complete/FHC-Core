<?php
class LePruefung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_pruefung';
		$this->pk = 'pruefung_id';
	}
}
