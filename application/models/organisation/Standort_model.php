<?php
class Standort_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_standort';
		$this->pk = 'standort_id';
	}

	public function searchStandorte($filter)
	{
		$qry = "
			SELECT 
				s.kurzbz, s.standort_id
			FROM 
			    public.tbl_standort s
			WHERE 
			    lower (s.kurzbz) LIKE '%". $this->db->escape_like_str($filter)."%'";

		return $this->execQuery($qry);
	}
}

