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
	
	/**
	 * Returns all the orgform except VBB and ZGS
	 */
	public function getOrgformLV()
	{
		// Checks rights
		if (isError($ent = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;
		
		$query = "SELECT *
					FROM bis.tbl_orgform
				   WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS')
				ORDER BY orgform_kurzbz";
		
		return $this->execQuery($query);
	}
}
