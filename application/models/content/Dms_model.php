<?php

class Dms_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_dms';
		$this->pk = 'dms_id';
	}
	
	protected function insertDmsVersion($data)
	{
		$tableName = 'campus.tbl_dms_version';
		
		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->acl[$tableName], 'i'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl[$tableName], FHC_MODEL_ERROR);

		// DB-INSERT
		if ($this->db->insert($tableName, $data))
			return $this->_success($this->db->insert_id());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	protected function updateDmsVersion($id, $data)
	{
		$tableName = 'campus.tbl_dms_version';
		
		// Check Class-Attributes
		if (is_null($this->pk))
			return $this->_error(lang('fhc_'.FHC_NOPK), FHC_MODEL_ERROR);
		
		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->acl[$tableName], 'u'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl[$tableName], FHC_MODEL_ERROR);

		// DB-UPDATE
		$this->db->where('dms_id', $id);
		
		if ($this->db->update($tableName, $data))
			return $this->_success($id);
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}