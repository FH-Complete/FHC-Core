<?php
class Standort_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_standort';
		$this->pk = 'standort_id';
	}
}
