<?php
class Orgform_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_orgform';
		$this->pk = 'orgform_kurzbz';
	}
}
