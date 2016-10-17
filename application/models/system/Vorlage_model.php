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

	public function getMimeTypes()
	{
		// Checks rights
		if ($chkRights = $this->chkRights(PermissionLib::SELECT_RIGHT)) return $chkRights;
		
		$query = 'SELECT DISTINCT mimetype FROM public.tbl_vorlage ORDER BY mimetype';
		
		return $this->execQuery($query);
	}
}