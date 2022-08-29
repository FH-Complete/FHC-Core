<?php
class Zgv_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_zgv';
		$this->pk = 'zgv_code';
	}

	/**
	 * getAllForStyled Dropdown
	 */
	public function getAllZgv()
	{
		$allZgv = 'SELECT * FROM bis.tbl_zgv;';

		return $this->execQuery($allZgv);
	}


}
