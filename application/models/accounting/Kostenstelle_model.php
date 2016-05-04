<?php
class Kostenstelle_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_kostenstelle';
		$this->pk = 'kostenstelle_id';
	}
}
