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

	/**
	 * getAllForStyled Dropdown
	 */
	public function getAllZgvmaster()
	{
		$allZgvMaster = 'SELECT * FROM bis.tbl_zgvmaster ORDER BY zgvmas_bez ASC;';

		return $this->execQuery($allZgvMaster);
	}
}
