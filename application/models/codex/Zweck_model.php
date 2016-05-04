<?php
class Zweck_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_zweck';
		$this->pk = 'zweck_code';
	}
}
