<?php
class Verwendung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_verwendung';
		$this->pk = 'verwendung_code';
	}
}
