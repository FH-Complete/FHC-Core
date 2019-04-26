<?php
class Bisorgform_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisorgform';
		$this->pk = 'bisorgform_kurzbz';
	}
}
