<?php

class Nation_model extends DB_Model
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_nation';
		$this->pk = 'nation_code';
	}
}
