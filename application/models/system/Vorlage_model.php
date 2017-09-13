<?php

class Vorlage_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_vorlage';
		$this->pk = 'vorlage_kurzbz';
	}

	/**
	 * Returns mume types
	 */
	public function getMimeTypes()
	{
		// Checks rights
		if (isError($ent = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;
		
		$query = 'SELECT DISTINCT mimetype FROM public.tbl_vorlage ORDER BY mimetype';
		
		return $this->execQuery($query);
	}
}
