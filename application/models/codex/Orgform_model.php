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
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);
		
		$query = "SELECT *
					FROM bis.tbl_orgform
				   WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS')
				ORDER BY orgform_kurzbz";
		
		$result = $this->db->query($query);
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}