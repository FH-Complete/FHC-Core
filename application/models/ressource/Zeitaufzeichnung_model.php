<?php
class Zeitaufzeichnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitaufzeichnung';
		$this->pk = 'zeitaufzeichnung_id';
	}
}
