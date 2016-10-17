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
		if ($chkRights = $this->chkRights(PermissionLib::SELECT_RIGHT)) return $chkRights;
		
		$query = "SELECT *
					FROM bis.tbl_orgform
				   WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS')
				ORDER BY orgform_kurzbz";
		
		return $this->execQuery($query);
	}
}