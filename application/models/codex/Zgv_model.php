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
}
