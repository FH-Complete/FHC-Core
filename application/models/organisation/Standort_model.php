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

	public function getByCompanyType($companyType)
	{
		$query = "SELECT s.* FROM public.tbl_standort s
				JOIN public.tbl_firma f ON s.firma_id = f.firma_id
				JOIN public.tbl_adresse a ON s.adresse_id = a.adresse_id
				WHERE f.firmentyp_kurzbz = ?;";

		return $this->execReadOnlyQuery($query, [$companyType]);
	}
}

