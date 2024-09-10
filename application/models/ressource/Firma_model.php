<?php
class Firma_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_firma';
		$this->pk = 'firma_id';
	}

	public function searchFirmen($filter)
	{
		$filter = strtoLower($filter);
		$qry = "
			SELECT 
				f.name, f.firma_id
			FROM 
			    public.tbl_firma f
			WHERE 
			    lower (f.name) LIKE '%". $this->db->escape_like_str($filter)."%'";

		return $this->execQuery($qry);
	}
}
