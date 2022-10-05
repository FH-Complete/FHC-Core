<?php
class Zgvmaster_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_zgvmaster';
		$this->pk = 'zgvmas_code';
	}
}
