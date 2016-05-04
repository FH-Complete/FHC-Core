<?php
class Gemeinde_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_gemeinde';
		$this->pk = 'gemeinde_id';
	}
}
