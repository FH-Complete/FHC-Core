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
		// Checks if the operation is permitted by the API caller
		if (($chkRights = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $chkRights;
		
		$query = "SELECT *
					FROM bis.tbl_orgform
				   WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS')
				ORDER BY orgform_kurzbz";
		
		$result = $this->db->query($query);
		
		if (is_object($result))
			return success($result->result());
		else
			return error($this->db->error(), FHC_DB_ERROR);
	}
}