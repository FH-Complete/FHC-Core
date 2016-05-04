<?php
class Konto_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_konto';
		$this->pk = 'konto_id';
	}
}
