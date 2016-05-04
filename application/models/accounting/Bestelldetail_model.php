<?php
class Bestelldetail_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_bestelldetail';
		$this->pk = 'bestelldetail_id';
	}
}
