<?php
class Frage_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_frage';
		$this->pk = 'frage_id';
	}
}
