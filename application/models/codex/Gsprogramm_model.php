<?php
class Gsprogramm_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_gsprogramm';
		$this->pk = 'gsprogramm_id';
	}
	
}
