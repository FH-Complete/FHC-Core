<?php

class Geschlecht_model extends DB_Model
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_geschlecht';
		$this->pk = 'geschlecht';
	}
}
