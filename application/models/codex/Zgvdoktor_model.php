<?php
class Zgvdoktor_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_zgvdoktor';
		$this->pk = 'zgvdoktor_code';
	}
}
