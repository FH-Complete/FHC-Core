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

	/**
	 * getAllForStyled Dropdown
	 */
	public function getAll()
	{
		$allNations = 'SELECT * FROM bis.tbl_nation	ORDER BY bis.tbl_nation.langtext ASC;';

		return $this->execQuery($allNations);
	}
}
