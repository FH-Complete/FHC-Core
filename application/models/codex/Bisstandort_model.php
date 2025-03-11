<?php
class Bisstandort_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisstandort';
		$this->pk = 'standort_code';
	}
}
