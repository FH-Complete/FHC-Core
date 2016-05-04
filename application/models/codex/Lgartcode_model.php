<?php
class Lgartcode_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_lgartcode';
		$this->pk = 'lgartcode';
	}
}
