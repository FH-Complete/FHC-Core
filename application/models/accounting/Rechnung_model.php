<?php
class Rechnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_rechnung';
		$this->pk = 'rechnung_id';
	}
}
