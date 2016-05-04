<?php
class Gruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_gruppe';
		$this->pk = 'gruppe_kurzbz';
	}
}
