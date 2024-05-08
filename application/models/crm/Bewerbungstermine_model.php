<?php
class Bewerbungstermine_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_bewerbungstermine';
		$this->pk = 'bewerbungstermin_id';
	}
}
