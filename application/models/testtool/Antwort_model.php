<?php
class Antwort_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_antwort';
		$this->pk = 'antwort_id';
	}
}
