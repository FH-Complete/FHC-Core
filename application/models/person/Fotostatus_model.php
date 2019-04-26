<?php
class Fotostatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_fotostatus';
		$this->pk = 'fotostatus_kurzbz';
	}
}
