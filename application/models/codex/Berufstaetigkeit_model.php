<?php
class Berufstaetigkeit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_berufstaetigkeit';
		$this->pk = 'berufstaetigkeit_code';
	}
}
