<?php
class Budget_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_budget';
		$this->pk = array('kostenstelle_id', 'geschaeftsjahr_kurzbz');
	}
}
