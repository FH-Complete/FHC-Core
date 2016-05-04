<?php
class Bisio_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisio';
		$this->pk = 'bisio_id';
	}
}
