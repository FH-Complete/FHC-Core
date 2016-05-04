<?php
class Adresse_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_adresse';
		$this->pk = 'adresse_id';
	}
}
