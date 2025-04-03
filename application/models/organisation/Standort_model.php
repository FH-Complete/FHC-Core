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
		$filter = strtoLower($filter);
		$qry = "
			SELECT 
				s.kurzbz, s.standort_id
			FROM 
			    public.tbl_standort s
			WHERE 
			    lower (s.kurzbz) LIKE '%". $this->db->escape_like_str($filter)."%'
			OR
				lower (s.bezeichnung) LIKE '%". $this->db->escape_like_str($filter)."%'";


		return $this->execQuery($qry);
	}

	public function getStandorteByFirma($firma_id)
	{
		$this->addSelect("DISTINCT ON (standort_id) bezeichnung, standort_id");

		return $this->loadWhere(array("firma_id" => $firma_id));
	}
}

