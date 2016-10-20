<?php

class Orgform_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_orgform';
		$this->pk = 'orgform_kurzbz';
	}
	
	public function getOrgformLV()
	{
		// Checks rights
		if (($isEntitled = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
		$query = "SELECT *
					FROM bis.tbl_orgform
				   WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS')
				ORDER BY orgform_kurzbz";
		
		return $this->execQuery($query);
	}
}