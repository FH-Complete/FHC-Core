<?php
class Statistik_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_statistik';
		$this->pk = 'statistik_kurzbz';
	}
}
