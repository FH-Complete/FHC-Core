<?php
class Mobilitaetsprogramm_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_mobilitaetsprogramm';
		$this->pk = 'mobilitaetsprogramm_code';
	}
}
